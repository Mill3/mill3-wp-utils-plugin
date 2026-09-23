# Polylang Pro drops all ACF field values coming from a Clone field displayed "Seamless" when copying/translating ACF blocks

## Summary

When Polylang copies or translates a post containing **ACF Blocks**, every field value that
originates from a **Clone field with `display = seamless`** is silently discarded.

The values never reach the translated post — the corresponding entries are simply absent from
the block's `attrs.data`. No notice, no warning, no error is logged.

This is **not** related to the per-field *Translations* setting: fields set to `copy_once`,
`translate`, `sync` — all of them are lost. Polylang's strategies compute the correct value;
it is discarded one step later, when writing.

Clone fields displayed as **Group** are unaffected, and so are ordinary (non-cloned) fields.
The post meta path is also unaffected — only the ACF **block** path is broken.

## Environment

| | |
|---|---|
| Polylang Pro | 3.8.6 |
| ACF Pro | 6.8.7 |
| WordPress | 6.7.2 |
| PHP | 8.1.32 |

## Impact

Measured on a single real page containing 19 ACF blocks, copying FR → EN:

| | |
|---|---|
| ACF field values in the source post | 591 |
| ACF field values in the copied post | **224** |
| **Values lost** | **367 (62%)** |

All spacing/margin, colour, and general-settings fields — which in our theme are shared
across ~40 block field groups via seamless clones — came through completely empty.

## Steps to reproduce

1. Create field group **A** ("Spacing"), location rule irrelevant (we use an inactive group).
   Add a field, e.g. `padding_top`, and set its Polylang **Translations** setting to `Copy once`.
2. Create field group **B**, with location rule **Block** → some ACF block.
   Add a **Clone** field pointing at group **A**, with **Display** = `Seamless (replaces this field with selected fields)`
   and **Prefix Field Names** = off.
3. Insert that block into a page, set `padding_top` to a non-default value, save.
4. Create a translation of the page (the "+" button in the Languages metabox, or "Copy content from").
5. Inspect the translated post's `post_content`.

**Expected:** the block's `data` contains `padding_top` with the source value.
**Actual:** `padding_top` and `_padding_top` are absent entirely.

The same happens through the Polylang translation editor / import path.

## Root cause

### 1. Polylang keys the values by `$field['key']`

`polylang-pro/src/integrations/ACF/Entity/Blocks.php`, `apply_on_blocks()` (~L205-228):

```php
$values = array();
foreach ( acf_get_block_fields( $block['attrs'] ) as $field ) {
    $value                   = acf_get_value( $block['id'], $field );
    $values[ $field['key'] ] = $strategy->execute( /* … */ );   // ← $field['key']
}

if ( ! empty( $values ) ) {
    $block['attrs']['data'] = acf_get_instance( 'ACF_Local_Meta' )
        ->capture( $values, $block['id'] );
}
```

### 2. For a seamless clone, `$field['key']` is a synthetic key that does not exist

`acf_get_block_fields()` → `acf_get_fields()` → ACF's clone field type removes the clone field
from the group and splices the cloned sub-fields in its place:

`advanced-custom-fields-pro/pro/fields/class-acf-field-clone.php` (~L161):

```php
// replace this clone field with sub fields
array_splice( $fields, $i, 1, $field['sub_fields'] );
```

and rewrites each spliced field's key, keeping the real one in `__key`
(same file, `acf_clone_field()`, ~L264-276):

```php
if ( ! isset( $field['__key'] ) ) {
    $field['__key'] = $field['key'];
}
// seamless
if ( $clone_field['display'] == 'seamless' ) {
    $field['key'] = $clone_field['key'] . '_' . $field['key'];
}
```

So a field that really is `field_60463b036c6b0` is presented to Polylang as
`field_633dcf56940d9_field_60463b036c6b0`.

That synthetic key is **never registered anywhere** — the splice happens inside the
`acf/load_fields` filter, *after* `acf_get_fields()` has stored the real keys. It is not in
ACF's `fields` store, not a local field, and has no `acf-field` post.

### 3. `capture()` silently drops anything whose key does not resolve

`ACF_Local_Meta::capture()` calls `acf_update_values()`:

`advanced-custom-fields-pro/includes/acf-value-functions.php` (~L277-290):

```php
function acf_update_values( $values, $post_id ) {
    foreach ( $values as $key => $value ) {
        $field = acf_get_field( $key );
        if ( $field ) {                 // ← no `else`: unresolved keys vanish
            acf_update_value( $value, $post_id, $field );
        }
    }
}
```

`acf_get_field( 'field_633dcf56940d9_field_60463b036c6b0' )` returns `false`, so the value is
dropped without a trace.

### Verification

Running the real `pll_translate_blocks_with_context` filter over one block, printing
`acf_get_field( $field['key'] )` for each field returned by `acf_get_block_fields()`:

```
KEY (as passed to capture())                  NAME        TRANSL.    RESOLVES?
field_63519c0dea1ab                           row_title   copy_once  YES
field_633dd26294e52                           title       translate  YES
field_633dcf56940d9_field_60463b036c6b0       pt          copy_once  *** NO ***   [__key=field_60463b036c6b0]
field_633dcf56940d9_field_604a25c96d2c2       pb          copy_once  *** NO ***   [__key=field_604a25c96d2c2]
field_633dcf56941b8_field_620fcca9287d0       bg-color    copy_once  *** NO ***   [__key=field_620fcca9287d0]
field_633dcf5694296_field_623bc7d2136af       pb_row_id   translate  *** NO ***   [__key=field_623bc7d2136af]
…
31 fields in block, 4 values survived, 27 lost
```

Note `pb_row_id` is set to `translate` and is lost too — the translation setting is irrelevant.

## Affected code paths

Both entry points into `Blocks::apply_on_blocks()` are affected:

| Filter | Callback | Triggered by |
|---|---|---|
| `pll_translate_blocks_with_context` | `Dispatcher::copy_blocks()` → `Blocks::copy()` | "+" new translation, "Copy content from" / duplicate |
| `pll_filter_translated_post` | `Dispatcher::translate_blocks()` → `Blocks::translate()` | Translation editor / import |

The post meta path is **not** affected: `Entity/Abstract_Object.php` passes the `$field` array
straight to `acf_update_value()` instead of resolving a key.

## Suggested fix

Polylang already accounts for exactly this case elsewhere. `Strategy/Abstract_Strategy.php`
(~L323-332):

```php
protected function get_field_key( array $field ): string {
    /*
     * #1: `pll_key` should be defined most of the time …
     * #2: `__key` ensures to look for the original key in case the field is a seamless clone for instance.
     * #3: `key` the standard field key.
     */
    return $field['pll_key'] ?? $field['__key'] ?? $field['key'];
}
```

`Entity/Blocks.php` just does not apply the same fallback. In `apply_on_blocks()`:

```diff
  foreach ( acf_get_block_fields( $block['attrs'] ) as $field ) {
      $value = acf_get_value( $block['id'], $field );
-     $values[ $field['key'] ] = $strategy->execute(
+     $values[ $field['__key'] ?? $field['key'] ] = $strategy->execute(
          new Post( $id ),
          $value,
          $field,
          array(
              'target_language' => $language,
              'original_value'  => $field['default_value'] ?? null,
          )
      );
  }
```

Using `__key` also makes the `_fieldname` reference that ACF writes identical to the one it
writes on a normal editor save (we verified 503 reference keys, 0 mismatches).

One caveat worth checking on your side: `__key` points at the *original* field, whose `name`
does not include the clone's prefix. If the clone field has **Prefix Field Names** enabled,
resolving by `__key` would write the unprefixed meta name. Our project uses `prefix_name = 0`
everywhere, so we could not exercise that case. Keying by `__key` but writing with the spliced
field's `name` would be robust in both configurations.

## Workaround currently in use

Until a fix ships, we run an mu-plugin that makes the synthetic keys resolvable for the
duration of Polylang's block walk, by registering the spliced field definition in ACF's field
store under its synthetic key with the original key restored. The core of it is:

*(complete source in [Appendix — full workaround source](#appendix--full-workaround-source))*

```php
add_filter( 'acf/load_fields', function ( $fields ) {
    $store = acf_get_store( 'fields' );

    foreach ( $fields as $field ) {
        if ( empty( $field['__key'] ) || $field['key'] === $field['__key'] ) {
            continue;                       // not a seamless clone
        }
        if ( $store->has( $field['key'] ) ) {
            continue;                       // never shadow a real field
        }

        $alias        = $field;
        $alias['key'] = $field['__key'];    // so the "_fieldname" reference matches a native save

        $store->set( $field['key'], $alias );  // set() only — alias() would hijack name lookups
    }

    return $fields;
}, 20 );
```

registered at priority 9 and torn down at priority 11 around `pll_translate_blocks_with_context`
and `pll_filter_translated_post`, so no synthetic key survives into the rest of the request.
Priority 20 is required because ACF applies the deprecated `acf/get_fields` hook — where the
clone splice lives — from a handler on `acf/load_fields` at priority 10.

This leaves every Polylang strategy untouched, so `ignore` fields stay ignored and
media/post/term IDs are still remapped to the target language.

### Results with the workaround

| | Before | After |
|---|---|---|
| Field values copied (of 591) | 224 | 597 * |
| Blocks receiving `pt`/`pb`/`mt`/`mb`/`bg-color` (of 19) | 0 | 19 |
| Reference key mismatches vs native ACF save | — | 0 / 503 |

\* Above 591 because ACF field defaults now materialise for previously-absent seamless clone
fields, exactly as they already do for non-cloned fields.

Verified end-to-end through `PLL()->sync_content->copy_content()`, the method used when
creating a translation.

---

## Appendix — full workaround source

Dropped in as `wp-content/mu-plugins/pll-acf-seamless-clone-fix.php`. Self-contained, no
dependencies beyond Polylang Pro and ACF Pro, and safe to delete once the upstream fix ships.

```php
<?php
/*
Plugin Name: Polylang + ACF — seamless clone block fix
Description: Restores ACF field values that Polylang silently drops when copying or translating ACF blocks whose fields come from a clone field displayed "seamless".
Author: Mill3 Studio
Version: 1.0.0
*/

defined( 'ABSPATH' ) || exit;

/**
 * WHY THIS EXISTS
 * ---------------
 * Polylang Pro's ACF block walker collects the values it wants to write keyed by
 * `$field['key']` and hands them to ACF:
 *
 *     // polylang-pro/src/integrations/ACF/Entity/Blocks.php (~L205-228)
 *     foreach ( acf_get_block_fields( $block['attrs'] ) as $field ) {
 *         $values[ $field['key'] ] = $strategy->execute( ... );
 *     }
 *     $block['attrs']['data'] = acf_get_instance( 'ACF_Local_Meta' )->capture( $values, $block['id'] );
 *
 * `capture()` runs `acf_update_values()`, which is:
 *
 *     // acf/includes/acf-value-functions.php (~L280-289)
 *     $field = acf_get_field( $key );
 *     if ( $field ) { acf_update_value( $value, $post_id, $field ); }   // no else — dropped in silence
 *
 * When a clone field is displayed "seamless", ACF removes the clone field from the group
 * and splices the cloned sub-fields in its place, rewriting each key to
 * "<clone field key>_<original field key>" and stashing the real key in `__key`:
 *
 *     // acf/pro/fields/class-acf-field-clone.php (~L161 and ~L276)
 *     array_splice( $fields, $i, 1, $field['sub_fields'] );
 *     $field['key'] = $clone_field['key'] . '_' . $field['key'];
 *
 * Those synthetic keys are never registered in ACF's field store, are not local fields and
 * do not exist in the database, so `acf_get_field()` returns false for every one of them
 * and *all* seamless-clone values are discarded — regardless of their Polylang translation
 * setting (copy_once, translate, sync…). Clone fields displayed as "Group" are unaffected
 * because they keep a real key.
 *
 * THE FIX
 * -------
 * While Polylang walks the blocks, make those synthetic keys resolvable by registering
 * them in ACF's field store, pointing at the spliced field definition with its original
 * key restored — so the `_fieldname` reference ACF writes is identical to the one it
 * writes on a normal editor save.
 *
 * Nothing else is changed: Polylang's own strategies still decide what each field's value
 * becomes (including media/post/term ID translation), so `ignore` fields stay ignored and
 * relationship fields still get remapped to the target language.
 *
 * The registration is scoped to the two Polylang filters that drive the block walker and
 * is torn down afterwards, so no synthetic key survives into the rest of the request.
 *
 * Remove this mu-plugin once Polylang ships a fix (reported against Polylang Pro 3.8.6 /
 * ACF Pro 6.8.7). The upstream one-liner is to key by `$field['__key'] ?? $field['key']`
 * in Blocks.php, matching what Polylang already documents in
 * `Strategy\Abstract_Strategy::get_field_key()`.
 */
final class Mill3_PLL_ACF_Seamless_Clone_Fix {

	/**
	 * Polylang filters whose priority-10 callback runs the ACF block walker.
	 *
	 * - pll_translate_blocks_with_context : new translation ("+" button), "copy content from" / duplicate.
	 * - pll_filter_translated_post        : Polylang translation editor / import.
	 */
	const HOOKS = array(
		'pll_translate_blocks_with_context',
		'pll_filter_translated_post',
	);

	/** @var string[] Synthetic keys we added to ACF's field store. */
	private static $injected = array();

	/** @var int Re-entrancy guard. */
	private static $depth = 0;

	/**
	 * Registers the hooks.
	 *
	 * @return void
	 */
	public static function boot() {
		foreach ( self::HOOKS as $hook ) {
			add_filter( $hook, array( __CLASS__, 'open' ), 9 );
			add_filter( $hook, array( __CLASS__, 'close' ), 11 );
		}
	}

	/**
	 * Starts intercepting field loading, just before Polylang's block walker runs.
	 *
	 * @param mixed $value Filtered value, passed through untouched.
	 * @return mixed
	 */
	public static function open( $value ) {
		if ( 0 === self::$depth++ ) {
			// Priority 20: ACF applies the deprecated `acf/get_fields` hook — where the clone
			// splice lives — from a handler on `acf/load_fields` at priority 10.
			add_filter( 'acf/load_fields', array( __CLASS__, 'register_seamless_clone_keys' ), 20 );
		}

		return $value;
	}

	/**
	 * Stops intercepting and removes every synthetic key we registered.
	 *
	 * @param mixed $value Filtered value, passed through untouched.
	 * @return mixed
	 */
	public static function close( $value ) {
		if ( self::$depth > 0 && 0 === --self::$depth ) {
			remove_filter( 'acf/load_fields', array( __CLASS__, 'register_seamless_clone_keys' ), 20 );

			$store = function_exists( 'acf_get_store' ) ? acf_get_store( 'fields' ) : null;

			if ( $store ) {
				foreach ( self::$injected as $key ) {
					$store->remove( $key );
				}
			}

			self::$injected = array();
		}

		return $value;
	}

	/**
	 * Makes the synthetic key of every seamless-cloned field resolvable by `acf_get_field()`.
	 *
	 * @param mixed $fields Fields of a group, after ACF spliced seamless clones in.
	 * @return mixed The fields, untouched.
	 */
	public static function register_seamless_clone_keys( $fields ) {
		if ( ! is_array( $fields ) || ! function_exists( 'acf_get_store' ) ) {
			return $fields;
		}

		$store = acf_get_store( 'fields' );

		if ( ! $store ) {
			return $fields;
		}

		foreach ( $fields as $field ) {
			if ( ! is_array( $field ) || empty( $field['key'] ) || empty( $field['__key'] ) ) {
				continue;
			}

			if ( $field['key'] === $field['__key'] ) {
				// Cloned, but not seamless: the key is real and already resolvable.
				continue;
			}

			if ( $store->has( $field['key'] ) ) {
				// Never shadow an existing entry (including one we added earlier in this run).
				continue;
			}

			// Restore the original key so ACF writes the same `_fieldname` reference it
			// writes when the block is saved from the editor. Everything else — crucially
			// `name`, which is what the value is stored under and which already accounts
			// for the clone's `prefix_name` setting — is kept exactly as ACF spliced it.
			$alias        = $field;
			$alias['key'] = $field['__key'];

			// `set()` only, deliberately not `alias()`: aliasing by name or ID would hijack
			// unrelated `acf_get_field( 'some_name' )` lookups elsewhere in the request.
			$store->set( $field['key'], $alias );

			self::$injected[] = $field['key'];
		}

		return $fields;
	}
}

Mill3_PLL_ACF_Seamless_Clone_Fix::boot();
```

<?php

namespace Mill3_Plugins\Utils\Components;
use Mill3_Plugins\Utils\Components\Mill3_Wp_Utils_Component;

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
 * Disable this component once Polylang ships a fix (reported against Polylang Pro 3.8.6 /
 * ACF Pro 6.8.7). The upstream one-liner is to key by `$field['__key'] ?? $field['key']`
 * in Blocks.php, matching what Polylang already documents in
 * `Strategy\Abstract_Strategy::get_field_key()`.
 *
 * See README.md in this directory for the complete report, reproduction steps and measurements.
 */
class Polylang_Acf_Clone_Fix extends Mill3_Wp_Utils_Component
{
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
    private $injected = array();

    /** @var int Re-entrancy guard. */
    private $depth = 0;

    protected function init(): void {
        // Both hooks only exist in Polylang Pro and both callbacks are inert without ACF,
        // so they are registered unconditionally : components boot on `plugins_loaded` at
        // priority 10, too early to reliably test for Polylang Pro's classes.
        foreach(self::HOOKS as $hook) {
            $this->loader->add_filter($hook, $this, 'open', 9);
            $this->loader->add_filter($hook, $this, 'close', 11);
        }
    }

    /**
     * Starts intercepting field loading, just before Polylang's block walker runs.
     *
     * @param mixed $value Filtered value, passed through untouched.
     * @return mixed
     */
    public function open($value) {
        if( !function_exists('acf_get_store') ) return $value;

        if( 0 === $this->depth++ ) {
            // Priority 20: ACF applies the deprecated `acf/get_fields` hook — where the clone
            // splice lives — from a handler on `acf/load_fields` at priority 10.
            add_filter('acf/load_fields', array($this, 'register_seamless_clone_keys'), 20);
        }

        return $value;
    }

    /**
     * Stops intercepting and removes every synthetic key we registered.
     *
     * @param mixed $value Filtered value, passed through untouched.
     * @return mixed
     */
    public function close($value) {
        if( $this->depth > 0 && 0 === --$this->depth ) {
            remove_filter('acf/load_fields', array($this, 'register_seamless_clone_keys'), 20);

            $store = function_exists('acf_get_store') ? acf_get_store('fields') : null;

            if( $store ) {
                foreach($this->injected as $key) $store->remove($key);
            }

            $this->injected = array();
        }

        return $value;
    }

    /**
     * Makes the synthetic key of every seamless-cloned field resolvable by `acf_get_field()`.
     *
     * @param mixed $fields Fields of a group, after ACF spliced seamless clones in.
     * @return mixed The fields, untouched.
     */
    public function register_seamless_clone_keys($fields) {
        if( !is_array($fields) || !function_exists('acf_get_store') ) return $fields;

        $store = acf_get_store('fields');

        if( !$store ) return $fields;

        foreach($fields as $field) {
            if( !is_array($field) || empty($field['key']) || empty($field['__key']) ) continue;

            // Cloned, but not seamless: the key is real and already resolvable.
            if( $field['key'] === $field['__key'] ) continue;

            // Never shadow an existing entry (including one we added earlier in this run).
            if( $store->has($field['key']) ) continue;

            // Restore the original key so ACF writes the same `_fieldname` reference it
            // writes when the block is saved from the editor. Everything else — crucially
            // `name`, which is what the value is stored under and which already accounts
            // for the clone's `prefix_name` setting — is kept exactly as ACF spliced it.
            $alias = $field;
            $alias['key'] = $field['__key'];

            // `set()` only, deliberately not `alias()`: aliasing by name or ID would hijack
            // unrelated `acf_get_field( 'some_name' )` lookups elsewhere in the request.
            $store->set($field['key'], $alias);

            $this->injected[] = $field['key'];
        }

        return $fields;
    }

    // getters
    public static function id() : string { return 'polylang-acf-clone-fix'; }
    public function version() : string { return '1.0.0'; }
    public function title() : string { return __('Polylang + ACF — Seamless Clone Block Fix', 'mill3-wp-utils'); }
    public function description() : string { return __('Restores ACF field values that Polylang silently drops when copying or translating ACF blocks whose fields come from a clone field displayed "seamless". Enable only on projects using Polylang Pro + ACF Pro.', 'mill3-wp-utils'); }
}

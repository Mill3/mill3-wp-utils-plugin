<?php

namespace Mill3_Plugins\Utils\Components;
use Mill3_Plugins\Utils\Components\Mill3_Wp_Utils_Component;

/**
 * Polylang's language switcher wraps the visible label of each link in a
 * <span class="pll-switcher-label">, both in the widget/block output and in the nav menu
 * items it adds (flag + label). That wrapper span is unwanted on projects whose markup/CSS
 * expects a plain text link, so this component strips it while keeping the label text.
 *
 * Timber-rendered menus never go through Polylang's `walker::start_el()` — which is what
 * normally wraps menu item titles — so the nav menu items are fixed separately, by filtering
 * `wp_get_nav_menu_items` after Polylang's own callback (priority 20) has already added its
 * markup to `$item->title`.
 */
class Polylang_Switcher_Output_Fix extends Mill3_Wp_Utils_Component
{
    protected function init(): void {
        $this->loader->add_filter('pll_language_switcher_output', $this, 'strip_switcher_label', 10, 2);
        $this->loader->add_filter('wp_get_nav_menu_items', $this, 'strip_nav_menu_item_label', 30);
    }

    /**
     * Unwraps the label span from the language switcher's HTML output, keeping its text.
     *
     * @param string $html Language switcher markup.
     * @param object $settings Switcher settings, untouched.
     * @return string
     */
    public function strip_switcher_label($html, $settings) {
        return preg_replace('#<span class="pll-switcher-label">(.*?)</span>#s', '$1', $html);
    }

    /**
     * Unwraps the label span from Polylang's language nav menu items, keeping their text.
     *
     * @param array $items Nav menu items, after Polylang added its language items.
     * @return array
     */
    public function strip_nav_menu_item_label($items) {
        foreach($items as $item) {
            if( isset($item->lang) ) {
                $item->title = preg_replace('#<span class="pll-switcher-label"[^>]*>(.*?)</span>#s', '$1', $item->title);
            }
        }

        return $items;
    }

    // getters
    public static function id() : string { return 'polylang-switcher-output-fix'; }
    public function version() : string { return '1.0.0'; }
    public function title() : string { return __('Polylang — Language Switcher Output Fix', 'mill3-wp-utils'); }
    public function description() : string { return __('Strips the <span class="pll-switcher-label"> wrapper Polylang adds around language switcher labels, in both its own output and the nav menu items it creates. Enable only on projects using Polylang\'s language switcher.', 'mill3-wp-utils'); }
}

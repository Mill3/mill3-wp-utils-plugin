<?php

namespace Mill3_Plugins\Utils;

use Mill3_Plugins\Utils\Components\Polylang_Switcher_Output_Fix;

class PolylangSwitcherOutputFixTest extends \WP_UnitTestCase
{
    private $component;

    public function setUp(): void
    {
        parent::setUp();

        // enabled = false : skips init(), so nothing gets hooked and only the string
        // helpers below are exercised directly.
        $this->component = new Polylang_Switcher_Output_Fix(null, null, null, false);
    }

    public function test_strip_switcher_label_unwraps_span()
    {
        $html = '<a href="/fr/"><span class="pll-switcher-label">Français</span></a>';
        $expected = '<a href="/fr/">Français</a>';

        $this->assertSame($expected, $this->component->strip_switcher_label($html, null));
    }

    public function test_strip_nav_menu_item_label_only_touches_language_items()
    {
        $language_item = (object) array('lang' => 'fr', 'title' => '<span class="pll-switcher-label">Français</span>');
        $regular_item = (object) array('title' => 'About');

        $items = $this->component->strip_nav_menu_item_label(array($language_item, $regular_item));

        $this->assertSame('Français', $items[0]->title);
        $this->assertSame('About', $items[1]->title);
    }
}

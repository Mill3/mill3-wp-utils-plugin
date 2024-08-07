<?php

namespace Mill3_Plugins\Utils;

class PluginTest extends \WP_UnitTestCase
{

    public $plugin;

    function set_up()
    {
        parent::set_up();
    }

    function test_it_is_a_singleton()
    {
        $a = Mill3_Wp_Utils::get_instance();
        $b = Mill3_Wp_Utils::get_instance();
        $this->assertSame($a, $b);
    }
}

<?php

namespace Mill3_Plugins\Utils;

class PluginTest extends \WP_UnitTestCase
{

    /**
     * Check if the plugin is a singleton
     */
    function test_it_is_a_singleton() : void
    {
        $a = Mill3_Wp_Utils::get_instance();
        $b = Mill3_Wp_Utils::get_instance();
        $this->assertSame($a, $b);
    }

    /**
     * Test if the plugin version in the header matches the constant
     *
     */
    function test_version() : void
    {
        $data = get_plugin_data(MILL3_WP_UTILS_PLUGIN_FILE, false, false);

        $this->assertSame(MILL3_WP_UTILS_VERSION, $data['Version'], "MILL3_WP_UTILS_VERSION constant number should match the plugin header");
    }
}

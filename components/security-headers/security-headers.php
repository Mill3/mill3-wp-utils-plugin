<?php

namespace Mill3_Plugins\Utils\Admin\Components;

use Mill3_Plugins\Utils\Interfaces\Mill3_Wp_Utils_Admin;

class Security_headers implements Mill3_Wp_Utils_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    0.0.1
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    0.0.1
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    0.0.1
     * @access   protected
     * @var      Mill3_Wp_Utils_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    private $loader;

    public function __construct($plugin_name, $version, $loader)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->loader = $loader;

        $this->loader->add_action('send_headers', $this, 'add_headers');
    }

    public function enqueue_styles() { }

    public function enqueue_scripts() { }

    /**
     * Add security headers to the response : only allow same origin and mill3.studio to frame this site
     */
    public function add_headers()
    {
        // header("Mill3-Wp-Utils-Version: " . $this->version); // add version to headers, for debugging purposes?
        header("X-Frame-Options: SAMEORIGIN");
        header("Content-Security-Policy: frame-ancestors 'self' mill3.studio");
    }
}

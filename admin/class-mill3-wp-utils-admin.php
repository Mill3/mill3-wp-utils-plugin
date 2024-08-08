<?php

namespace Mill3_Plugins\Utils\Admin;

use Mill3_Plugins\Utils\Interfaces\Mill3_Wp_Utils_Admin as Mill3_Wp_Utils_Admin_Interface;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://mill3.studio
 * @since      0.0.1
 *
 * @package    Mill3_Wp_Utils
 * @subpackage Mill3_Wp_Utils/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Mill3_Wp_Utils
 * @subpackage Mill3_Wp_Utils/admin
 * @author     MILL3 Studio <info@mill3.studio>
 */
class Mill3_Wp_Utils_Admin implements Mill3_Wp_Utils_Admin_Interface
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

    /**
     * Initialize the class and set its properties.
     *
     * @since    0.0.1
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     * @param      object    $loader     The class responsible for loading the components of the plugin.
     */
    public function __construct($plugin_name, $version, $loader)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->loader = $loader;
    }

    public function run()
    {
        $this->loader->add_action('admin_enqueue_scripts', $this, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $this, 'enqueue_scripts');
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    0.0.1
     */
    public function enqueue_styles()
    {
        // wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/mill3-wp-utils-admin.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    0.0.1
     */
    public function enqueue_scripts()
    {
        // wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/mill3-wp-utils-admin.js', array( 'jquery' ), $this->version, false );
    }
}

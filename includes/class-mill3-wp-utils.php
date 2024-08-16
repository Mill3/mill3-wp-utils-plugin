<?php

namespace Mill3_Plugins\Utils;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://mill3.studio
 * @since      0.0.1
 *
 * @package    Mill3_Wp_Utils
 * @subpackage Mill3_Wp_Utils/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.0.1
 * @package    Mill3_Wp_Utils
 * @subpackage Mill3_Wp_Utils/includes
 * @author     MILL3 Studio <info@mill3.studio>
 */
class Mill3_Wp_Utils
{
    private static $AVAILABLE_COMPONENTS = array(
        '\Mill3_Plugins\Utils\Components\Ai_Image_Alt',
        '\Mill3_Plugins\Utils\Components\Gutenberg_Sidebar',
        '\Mill3_Plugins\Utils\Components\Security_headers',
    );

    /**
     * @var $instance Plugin Singleton plugin instance
     */
    private static $instance = null;
    private $options;

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    0.0.1
     * @access   protected
     * @var      Mill3_Wp_Utils_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    protected $admin;
    protected $i18n;
    protected $installation;
    protected $updater;

    /**
     * Collection of components
     * 
     * @since   0.0.1
     * @access  protected
     * @var     Array   $components Collection of components
     */
    public $components = [];


    public function __construct() {
        $this->options = array(
            'components' => maybe_unserialize( get_option( self::get_option_name('components'), array() ) ),
        );

        $this->loader = new \Mill3_Plugins\Utils\Loader\Mill3_Wp_Utils_Loader();
        $this->i18n = new \Mill3_Plugins\Utils\I18n\Mill3_Wp_Utils_i18n();
        $this->updater = new \Mill3_Plugins\Utils\Updater\Mill3_Wp_Utils_Updater();

        $this->loader->add_action('plugins_loaded', $this->i18n, 'load_plugin_textdomain');
        $this->loader->add_filter('pre_set_site_transient_update_plugins', $this->updater, 'check_for_update');

        // load admin hooks only in Admin Views
        if( is_admin() ) $this->define_admin_hooks();

        // load components
        $this->load_components();

        // register all actions/filters
        $this->loader->run();
    }

    public static function get_instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new Mill3_Wp_Utils();
        }

        return self::$instance;
    }

    public static function activate() { add_option( self::get_option_name('components'), array(), null, false ); }
    public static function deactivate() {}
    public static function uninstall() { delete_option( self::get_option_name('components') ); }

    /**
     * Load all the components of the plugin
     *
     * @since    0.0.3.7
     * @access   private
     */
    private function load_components()
    {
        // get active components IDs
        $active_components_id = $this->options['components'];

        foreach(self::$AVAILABLE_COMPONENTS as $namespace) {
            // check if component id's is save in database
            $enabled = in_array($namespace::id(), $active_components_id, true);

            // if we are on frontend and component isn't enabled, stop here
            if( !is_admin() && !$enabled ) continue;

            // create component and save in registry
            $this->components[] = new $namespace($this, $this->loader, $this->admin, $enabled);
        }
    }

    public function activate_component($component_id) {
        $component = $this->get_component_by_id($component_id);

        // make sure component exists AND not already enabled before save to database
        if( !$component || $component->enabled() ) return false;

        // update local storage
        $this->options['components'][] = $component_id;

        // set component as enabled
        $component->enabled(true);

        // save to database
        return update_option( self::get_option_name('components'), $this->options['components'] );
    }
    public function deactivate_component($component_id) {
        $component = $this->get_component_by_id($component_id);

        // make sure component exists AND not already enabled before save to database
        if( !$component || !$component->enabled() ) return false;

        // update local storage
        $this->options['components'] = array_filter($this->options['components'], fn($id) => $id !== $component_id);

        // set component as disabled
        $component->enabled(false);

        // save to database
        return update_option( self::get_option_name('components'), maybe_serialize($this->options['components']) );
    }
    public function get_component_by_id($component_id) {
        foreach($this->components as $component) {
            if( $component->id() === $component_id ) return $component;
        }

        return null;
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    0.0.1
     * @access   private
     */
    private function define_admin_hooks() {
        $this->admin = new \Mill3_Plugins\Utils\Admin\Mill3_Wp_Utils_Admin($this, $this->loader);
    }


    public static function get_option_name($name) : string { return 'mill3-wp-utils--' . $name; }
    public static function get_name() : string { return 'mill3-wp-utils'; }
    public static function get_version() : string { return defined('MILL3_WP_UTILS_VERSION') ? MILL3_WP_UTILS_VERSION : '0.0.1'; }
}

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
class Mill3_Wp_Utils {

  /**
   * The loader that's responsible for maintaining and registering all hooks that power
   * the plugin.
   *
   * @since    0.0.1
   * @access   protected
   * @var      Mill3_Wp_Utils_Loader    $loader    Maintains and registers all hooks for the plugin.
   */
  protected $loader;

  /**
   * The unique identifier of this plugin.
   *
   * @since    0.0.1
   * @access   protected
   * @var      string    $plugin_name    The string used to uniquely identify this plugin.
   */
  protected $plugin_name;

  /**
   * The current version of the plugin.
   *
   * @since    0.0.1
   * @access   protected
   * @var      string    $version    The current version of the plugin.
   */
  protected $version;

  /**
   * Define the core functionality of the plugin.
   *
   * Set the plugin name and the plugin version that can be used throughout the plugin.
   * Load the dependencies, define the locale, and set the hooks for the admin area and
   * the public-facing side of the site.
   *
   * @since    0.0.1
   */
  public function __construct() {
    if ( defined( 'MILL3_WP_UTILS_VERSION' ) ) {
      $this->version = MILL3_WP_UTILS_VERSION;
    } else {
      $this->version = '0.0.1';
    }
    $this->plugin_name = 'mill3-wp-utils';

    $this->load_dependencies();
    $this->set_locale();
    $this->set_updates();
    $this->define_admin_hooks();
  }

  /**
   * Load the required dependencies for this plugin.
   *
   * Include the following files that make up the plugin:
   *
   * - Mill3_Wp_Utils_Loader. Orchestrates the hooks of the plugin.
   * - Mill3_Wp_Utils_i18n. Defines internationalization functionality.
   * - Mill3_Wp_Utils_Admin. Defines all hooks for the admin area.
   * - Mill3_Wp_Utils_Public. Defines all hooks for the public side of the site.
   *
   * Create an instance of the loader which will be used to register the hooks
   * with WordPress.
   *
   * @since    0.0.1
   * @access   private
   */
  private function load_dependencies() {

    /**
     * The class responsible for orchestrating the actions and filters of the
     * core plugin.
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mill3-wp-utils-loader.php';

    /**
     * The class responsible for defining internationalization functionality
     * of the plugin.
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mill3-wp-utils-i18n.php';

    /**
     * The class responsible for updating the plugin
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mill3-wp-utils-updater.php';

    /**
     * The class responsible for defining all actions that occur in the admin area.
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-mill3-wp-utils-admin.php';


    // create an instance of the loader
    $this->loader = new \Mill3_Plugins\Utils\Loader\Mill3_Wp_Utils_Loader();
  }

  /**
   * Define the locale for this plugin for internationalization.
   *
   * Uses the Mill3_Wp_Utils_i18n class in order to set the domain and to register the hook
   * with WordPress.
   *
   * @since    0.0.1
   * @access   private
   */
  private function set_locale() {
    $plugin_i18n = new \Mill3_Plugins\Utils\I18n\Mill3_Wp_Utils_i18n();
    $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
  }

  private function set_updates() {
    $plugin_updates = new \Mill3_Plugins\Utils\Updater\Mill3_Wp_Utils_Updater();
    $this->loader->add_filter( 'upgrader_package_options', $plugin_updates, 'upgrader_package_options', 10, 1);
    $this->loader->add_filter( 'pre_set_site_transient_update_plugins', $plugin_updates, 'check_for_update' );
    $this->loader->add_filter( 'plugins_api', $plugin_updates, 'plugins_api', 10, 3 );
  }

  /**
   * Register all of the hooks related to the admin area functionality
   * of the plugin.
   *
   * @since    0.0.1
   * @access   private
   */
  private function define_admin_hooks() {
    // load admin class
    (new \Mill3_Plugins\Utils\Admin\Mill3_Wp_Utils_Admin( $this->get_plugin_name(), $this->get_version(), $this->get_loader()))->run();
  }

  /**
   * Run the loader to execute all of the hooks with WordPress.
   *
   * @since    0.0.1
   */
  public function run() {
    $this->loader->run();
  }

  /**
   * The name of the plugin used to uniquely identify it within the context of
   * WordPress and to define internationalization functionality.
   *
   * @since     0.0.1
   * @return    string    The name of the plugin.
   */
  public function get_plugin_name() {
    return $this->plugin_name;
  }

  /**
   * The reference to the class that orchestrates the hooks with the plugin.
   *
   * @since     0.0.1
   * @return    Mill3_Wp_Utils_Loader    Orchestrates the hooks of the plugin.
   */
  public function get_loader() {
    return $this->loader;
  }

  /**
   * Retrieve the version number of the plugin.
   *
   * @since     0.0.1
   * @return    string    The version number of the plugin.
   */
  public function get_version() {
    return $this->version;
  }

}

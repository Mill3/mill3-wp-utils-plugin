<?php

// namespace Mill3_Plugins\Utils;

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://mill3.studio
 * @since             0.0.2
 * @package           Mill3_Wp_Utils
 *
 * @wordpress-plugin
 * Plugin Name:       MILL3 WP Utils
 * Plugin URI:        https://https://github.com/Mill3
 * Description:       MILL3 WP utils, includes Gutenberg editor sidebar resizer..
 * Version:           0.0.2
 * Author:            MILL3 Studio
 * Author URI:        https://mill3.studio/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mill3-wp-utils
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

/**
 * Currently plugin version.
 * Start at version 0.0.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'MILL3_WP_UTILS_VERSION', '0.0.2' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-mill3-wp-utils-activator.php
 */
function activate_mill3_wp_utils() {
  // // load interfaces
  // require_once plugin_dir_path( __FILE__ ) . 'interfaces/mill3-wp-utils-admin.php';

  // load main class
  require_once plugin_dir_path( __FILE__ ) . 'includes/class-mill3-wp-utils-activator.php';
  \Mill3_Plugins\Utils\Activator\Mill3_Wp_Utils_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-mill3-wp-utils-deactivator.php
 */
function deactivate_mill3_wp_utils() {
  require_once plugin_dir_path( __FILE__ ) . 'includes/class-mill3-wp-utils-deactivator.php';
  \Mill3_Plugins\Utils\Deactivator\Mill3_Wp_Utils_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_mill3_wp_utils' );
register_deactivation_hook( __FILE__, 'deactivate_mill3_wp_utils' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'interfaces/mill3-wp-utils-admin.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-mill3-wp-utils.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.0.1
 */
function run_mill3_wp_utils() {

  $plugin = new \Mill3_Plugins\Utils\Mill3_Wp_Utils();
  $plugin->run();

}

run_mill3_wp_utils();

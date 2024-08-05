<?php
/**
 * Plugin Name:       MILL3 WP Utils
 * Plugin URI:        https://github.com/Mill3/mill3-wp-utils-plugin
 * Description:       MILL3 WP utils, includes Gutenberg editor sidebar resizer.
 * Version:           0.0.3.8.1
 * Author:            MILL3 Studio
 * Author URI:        https://mill3.studio/
 * Tested up to:      6.6.6
 * Requires:          6.2 or higher
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mill3-wp-utils
 * Domain Path:       /languages
 * Update URI:        https://1b8b-24-225-231-201.ngrok-free.app/
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
define( 'MILL3_WP_UTILS_VERSION', '0.0.3.8.1' );


/**
 * Plugins update server endpoint
 *
 * TODO : temporary url for testing
 */

define( 'MILL3_WP_UTILS_PLUGINS_API', 'https://mill3-wp-utils-plugin-api-mill3.vercel.app/api/plugins');

/**
 * The plugin slug, should represent the plugin directory name.
 */
define( 'MILL3_WP_UTILS_PLUGIN_SLUG', 'mill3-wp-utils-plugin' );

/**
 * The name of the plugin directory (expected value).
 */
define( 'MILL3_WP_UTILS_PLUGIN_DIR_NAME', MILL3_WP_UTILS_PLUGIN_SLUG );

/**
 * The path of this installed plugin.
 */
define( 'MILL3_WP_UTILS_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));

/**
 * The name of the plugin file, ie : mill3-wp-utils.php
 */
define( 'MILL3_WP_UTILS_PLUGIN_FILE_NAME', basename(__FILE__) );

/**
 * The name of the plugin file using expected directory structure, should be : mill3-wp-utils-plugin/mill3-wp-utils.php
 */
define( 'MILL3_WP_UTILS_PLUGIN_FILE', join("/", [MILL3_WP_UTILS_PLUGIN_DIR_NAME, MILL3_WP_UTILS_PLUGIN_FILE_NAME]));



/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */

require plugin_dir_path( __FILE__ ) . 'interfaces/mill3-wp-utils-admin.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-mill3-wp-utils.php';


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-mill3-wp-utils-activator.php
 */
function activate_mill3_wp_utils() {
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
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.0.1
 */
function run_mill3_wp_utils() {
  // $update_plugins = get_site_transient( 'update_plugins' );
  // error_log(print_r($update_plugins, true));

  $plugin = new \Mill3_Plugins\Utils\Mill3_Wp_Utils();
  $plugin->run();

}

run_mill3_wp_utils();

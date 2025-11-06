<?php
/**
 * Plugin Name:       MILL3 WP Utils
 * Plugin URI:        https://github.com/Mill3/mill3-wp-utils-plugin
 * Description:       MILL3 WP Utils
 * Version:           0.1.5
 * Author:            MILL3 Studio
 * Author URI:        https://mill3.studio/
 * Tested up to:      6.6.6
 * Requires:          6.2 or higher
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mill3-wp-utils
 * Domain Path:       /languages
 */

require __DIR__ . '/vendor/autoload.php';

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

/**
 * Currently plugin version.
 * Start at version 0.0.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'MILL3_WP_UTILS_VERSION', '0.1.5' );


/**
 * Plugins update server endpoint
 */

define( 'MILL3_WP_UTILS_PLUGINS_API', 'https://github.com/Mill3/mill3-wp-utils-plugin');

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
// define( 'MILL3_WP_UTILS_PLUGIN_FILE', join("/", [MILL3_WP_UTILS_PLUGIN_DIR_NAME, MILL3_WP_UTILS_PLUGIN_FILE_NAME]));
define( 'MILL3_WP_UTILS_PLUGIN_FILE', join("/", [__DIR__, MILL3_WP_UTILS_PLUGIN_FILE_NAME]));



/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-mill3-wp-utils-component.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-mill3-wp-utils-i18n.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-mill3-wp-utils-loader.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-mill3-wp-utils-updater.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-mill3-wp-utils.php';

require plugin_dir_path( __FILE__ ) . 'admin/class-html-helper.php';
require plugin_dir_path( __FILE__ ) . 'admin/class-mill3-wp-utils-admin.php';

require plugin_dir_path( __FILE__ ) . 'core/core.php';

require plugin_dir_path( __FILE__ ) . 'components/ai-image-alt/ai-image-alt.php';
require plugin_dir_path( __FILE__ ) . 'components/avatar/avatar.php';
require plugin_dir_path( __FILE__ ) . 'components/block-visibility/block-visibility.php';
require plugin_dir_path( __FILE__ ) . 'components/gutenberg-sidebar/gutenberg-sidebar.php';
require plugin_dir_path( __FILE__ ) . 'components/ios26-scroll-fix/ios26-scroll-fix.php';
require plugin_dir_path( __FILE__ ) . 'components/live-site-viewer/live-site-viewer.php';
require plugin_dir_path( __FILE__ ) . 'components/module-finder/module-finder.php';
require plugin_dir_path( __FILE__ ) . 'components/robots-indexing/robots-indexing.php';
//require plugin_dir_path( __FILE__ ) . 'components/polylang-utils/polylang-utils.php';
require plugin_dir_path( __FILE__ ) . 'components/security-headers/security-headers.php';
require plugin_dir_path( __FILE__ ) . 'components/svg/svg.php';

// start plugin after all plugins are loaded
add_action('plugins_loaded', function() { Mill3_Plugins\Utils\Mill3_Wp_Utils::get_instance(); });


/**
 * The code that runs during plugin activation.
 */
function activate_mill3_wp_utils() { Mill3_Plugins\Utils\Mill3_Wp_Utils::activate(); }
function deactivate_mill3_wp_utils() { Mill3_Plugins\Utils\Mill3_Wp_Utils::deactivate(); }
function uninstall_mill3_wp_utils() { Mill3_Plugins\Utils\Mill3_Wp_Utils::uninstall(); }

register_activation_hook( __FILE__, 'activate_mill3_wp_utils' );
register_deactivation_hook( __FILE__, 'deactivate_mill3_wp_utils' );
register_uninstall_hook( __FILE__, 'uninstall_mill3_wp_utils' );

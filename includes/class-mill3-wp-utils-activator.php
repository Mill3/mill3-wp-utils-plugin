<?php

namespace Mill3_Plugins\Utils\Activator;

use Mill3_Plugins\Utils\Updater;

/**
 * Fired during plugin activation
 *
 * @link       https://mill3.studio
 * @since      0.0.1
 *
 * @package    Mill3_Wp_Utils
 * @subpackage Mill3_Wp_Utils/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.0.1
 * @package    Mill3_Wp_Utils
 * @subpackage Mill3_Wp_Utils/includes
 * @author     MILL3 Studio <info@mill3.studio>
 */
class Mill3_Wp_Utils_Activator
{

  /**
   * Short Description. (use period)
   *
   * Long Description.
   *
   * @since    0.0.1
   */
  public static function activate()
  {
    // Expected plugin directory
    // $expected_dir = join('/', [WP_PLUGIN_DIR, MILL3_WP_UTILS_PLUGIN_DIR_NAME]);

    // // Current plugin directory
    // $current_dir = MILL3_WP_UTILS_PLUGIN_DIR_PATH;

    // // current plugin name
    // $plugin_name = plugin_basename(join("/", [MILL3_WP_UTILS_PLUGIN_DIR_PATH, MILL3_WP_UTILS_PLUGIN_FILE_NAME]));

    // // Check if the plugin is installed in the correct directory
    // if (trailingslashit($current_dir) !== trailingslashit($expected_dir)) {
    //   // Deactivate the plugin
    //   deactivate_plugins($plugin_name);
    //   // Output an error message
    //   wp_die(__('Plugin must be installed in the <code>' . $expected_dir . '</code> directory.', 'mill3-wp-utils'));
    // }
  }
}

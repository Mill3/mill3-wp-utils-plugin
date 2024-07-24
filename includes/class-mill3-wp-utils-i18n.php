<?php

namespace Mill3_Plugins\Utils\I18n;

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://mill3.studio
 * @since      0.0.1
 *
 * @package    Mill3_Wp_Utils
 * @subpackage Mill3_Wp_Utils/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      0.0.1
 * @package    Mill3_Wp_Utils
 * @subpackage Mill3_Wp_Utils/includes
 * @author     MILL3 Studio <info@mill3.studio>
 */
class Mill3_Wp_Utils_i18n {


  /**
   * Load the plugin text domain for translation.
   *
   * @since    0.0.1
   */
  public function load_plugin_textdomain() {

    load_plugin_textdomain(
      'mill3-wp-utils',
      false,
      dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
    );

  }



}

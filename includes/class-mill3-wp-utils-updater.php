<?php

namespace Mill3_Plugins\Utils\Updater;

class Mill3_Wp_Utils_Updater {

  // TODO : temporary url for testing
  private $api_url = 'https://f644-24-225-231-201.ngrok-free.app/api/plugins';

  private $plugin_version;

  private $plugin_file;

  function __construct() {
    $this->plugin_file = MILL3_WP_UTILS_PLUGIN_FILE;
    $this->plugin_version = MILL3_WP_UTILS_VERSION;
  }

  public function destroy() {
    // if needed, do something here
  }

  /**
   * Force the plugin directory to match existing directory structure, Github releases are structured differently, ie: plugin-name-x.x.x.zip
   */
  public function upgrader_package_options( $options ) {
    $destination = $options['destination'] ?? '';
    $package     = $options['package'] ?? '';
    $dirname     = isset( $options['hook_extra']['plugin'] )
                   ? dirname( $options['hook_extra']['plugin'] )
                   : '';

    if ( empty( $dirname ) || '.' === $dirname ) {
        return $options;
    }

    if ( 'github.com' !== parse_url( $package, PHP_URL_HOST) ) {
       return $options;
    }

    if ( WP_PLUGIN_DIR === $destination ) {
        $options['destination'] = path_join( $destination, $dirname );
    }

    return $options;
  }

  public function check_for_update($transient) {
    // Only proceed if the transient contains the 'checked' array
    if ( empty( $transient->checked ) ) {
      return $transient;
    }

    // get response from the API
    $response = $this->get_remote_version();

    if ( is_wp_error( $response ) ) {
      return $transient;
    }

    // decode the response
    $update_data = json_decode( wp_remote_retrieve_body($response) );

    // compare the versions and check if an update is available
    $update = version_compare( $this->plugin_version, $update_data->new_version, '<' );

    if ($update ) {
      // An update is available.
      error_log("update available");
      $transient->response[$this->plugin_file] = (object) array(
        'slug'        => $update_data->slug,
        'new_version' => $update_data->new_version,
        'version'     => $update_data->version,
        'url'         => $update_data->url,
        'package'     => $update_data->package,
      );

    } else {
      // No update is available.
      error_log("no update available");
      $item = array(
          'theme'        => $this->plugin_file,
          'new_version'  => $this->plugin_version,
          'url'          => '',
          'package'      => '',
          'requires'     => '',
          'requires_php' => '',
      );

      // Adding the "mock" item to the `no_update` property is required
      // for the enable/disable auto-updates links to correctly appear in UI.
      $transient->no_update[$this->plugin_file] = $item;
    }

    return $transient;
  }

  public function plugins_api($false, $action, $args) {
    if ( $action !== 'plugin_information' || $args->slug !== 'mill3-wp-utils-plugin' ) {
      return false;
    }

    // get response from the API
    $response = $this->get_remote_version('/infos');

    // decode the response
    $plugin_info = json_decode( wp_remote_retrieve_body($response), true );

    // Populate the plugin information
    $result = (object) array(
      'name'          => $plugin_info['name'],
      'slug'          => $plugin_info['slug'],
      'version'       => $plugin_info['version'],
      'author'        => $plugin_info['author'],
      'author_profile'=> $plugin_info['author_profile'],
      'homepage'      => $plugin_info['homepage'],
      'download_link' => $plugin_info['download_link'],
      // 'trunk'         => $plugin_info['trunk'],
      // 'requires'      => $plugin_info['requires'],
      // 'tested'        => $plugin_info['tested'],
      'requires_php'  => $plugin_info['requires_php'],
      'sections'      => array(
          'description'  => $plugin_info['sections']['description'],
          'installation' => $plugin_info['sections']['installation'],
          'changelog'    => $plugin_info['sections']['changelog'],
      )
      // 'banners'       => array(
      //     'low'  => $plugin_info['banners']['low'],
      //     'high' => $plugin_info['banners']['high'],
      // ),
    );

    return $result;
  }

  public function get_remote_version($uri = '') {
    $response = wp_remote_get( $this->api_url . $uri );
    return $response;
}

}

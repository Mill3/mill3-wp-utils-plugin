<?php

namespace Mill3_Plugins\Utils\Updater;

class Mill3_Wp_Utils_Updater {

  // TODO : temporary url for testing
  private $api_url = 'https://f644-24-225-231-201.ngrok-free.app/api/plugins/';

  private $plugin_version;

  private $plugin_file;

  function __construct() {
    $this->plugin_file = 'mill3-wp-utils/mill3-wp-utils.php';
    $this->plugin_version = MILL3_WP_UTILS_VERSION;
  }

  public function destroy() {
    // if needed, do something here
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

  public function get_remote_version() {
    $response = wp_remote_get( $this->api_url );
    return $response;
}

}

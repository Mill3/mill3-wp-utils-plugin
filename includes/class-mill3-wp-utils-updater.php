<?php

namespace Mill3_Plugins\Utils\Updater;

class Mill3_Wp_Utils_Updater
{

  private $api_url;

  private $plugin_version;

  private $plugin_file;

  private $plugin_slug;

  function __construct()
  {
    $this->api_url = MILL3_WP_UTILS_PLUGINS_API;
    $this->plugin_file = MILL3_WP_UTILS_PLUGIN_FILE;
    $this->plugin_version = MILL3_WP_UTILS_VERSION;
    $this->plugin_slug = MILL3_WP_UTILS_PLUGIN_SLUG;
  }

  /**
   * Force the plugin directory to match existing directory structure, Github releases are structured differently, ie: plugin-name-x.x.x.zip
   */
  public function upgrader_package_options($options)
  {
    // stop here if the plugin is not the one we want to modify
    if (isset($options['hook_extra']['plugin']) && $options['hook_extra']['plugin'] !== $this->plugin_file) {
      return $options;
    }

    $destination = $options['destination'] ?? '';
    $package     = $options['package'] ?? '';
    $dirname     = isset($options['hook_extra']['plugin'])
      ? dirname($options['hook_extra']['plugin'])
      : '';

    if (empty($dirname) || '.' === $dirname) {
      return $options;
    }

    if ('github.com' !== parse_url($package, PHP_URL_HOST)) {
      return $options;
    }

    if (WP_PLUGIN_DIR === $destination) {
      $options['destination'] = path_join($destination, $dirname);
    }

    return $options;
  }

  public function check_for_update($transient)
  {
    // Only proceed if the transient contains the 'checked' array
    if (empty($transient->checked)) {
      return $transient;
    }

    // get response from the API
    $response = $this->get_remote_version("/");

    if (is_wp_error($response)) {
      return $transient;
    }

    // decode the response
    $update_data = json_decode(wp_remote_retrieve_body($response));

    // compare the versions and check if an update is available
    $update = version_compare($this->plugin_version, $update_data->version, '<');

    if ($update) {
      // An update is available.
      $transient->response[$this->plugin_file] = (object) array(
        'id'          => "mill3.dev/plugins/{$this->plugin_slug}",
        'plugin'      => $this->plugin_file,
        'slug'        => $this->plugin_slug,
        'new_version' => $update_data->version,
        'version'     => $update_data->version,
        'url'         => $update_data->url,
        'package'     => $update_data->package,
      );
    } else {
      // No update is available.
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

    $transient->checked[ $this->plugin_file ] = $this->plugin_version;
    $transient->last_checked = time();

    // error_log(print_r($transient, true));

    // if($save) {
    //   set_site_transient( 'update_plugins', $transient );
    // }

    return $transient;
  }

  public function plugins_api($false, $action, $args)
  {
    if ($action !== 'plugin_information' || $args->slug !== MILL3_WP_UTILS_PLUGIN_SLUG) {
      return false;
    }

    // get response from the API
    $response = $this->get_remote_version('/infos');

    // decode the response
    $plugin_info = json_decode(wp_remote_retrieve_body($response), true);

    // Populate the plugin information
    $result = (object) array(
      'name'          => $plugin_info['name'],
      'slug'          => $plugin_info['slug'],
      'version'       => $plugin_info['version'],
      'author'        => $plugin_info['author'],
      'author_profile' => $plugin_info['author_profile'],
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

  public function get_remote_version($uri = '')
  {
    $response = wp_remote_get($this->api_url . $uri);
    return $response;
  }
}

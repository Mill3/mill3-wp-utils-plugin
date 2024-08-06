<?php


namespace Mill3_Plugins\Utils\Updater;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

class Mill3_Wp_Utils_Updater
{

  private $api_url;

  private $plugin_version;

  private $plugin_file;

  private $plugin_slug;

  private $updater;

  function __construct()
  {
    $this->api_url = MILL3_WP_UTILS_PLUGINS_API;
    $this->plugin_file = MILL3_WP_UTILS_PLUGIN_FILE;
    $this->plugin_version = MILL3_WP_UTILS_VERSION;
    $this->plugin_slug = MILL3_WP_UTILS_PLUGIN_SLUG;

    $this->updater = PucFactory::buildUpdateChecker(
      $this->api_url,
      $this->plugin_file,
      $this->plugin_slug
    );

    $this->updater->setBranch('master');

    // error_log(print_r($this->updater->getUpdate(), true));
  }

  /**
   * Force the plugin directory to match existing directory structure, Github releases are structured differently, ie: plugin-name-x.x.x.zip
   */
  public function upgrader_package_options($options)
  {
    // get data from the updater instance
    $update = $this->updater->getUpdate();

    // stop here if the plugin is not the one we want to modify
    if (isset($options['hook_extra']['plugin']) && $options['hook_extra']['plugin'] !== $update->filename) {
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

    if ('api.github.com' !== parse_url($package, PHP_URL_HOST)) {
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

    $update = $this->updater->getUpdate();

    if ($update) {
      $transient->response[$update->filename] = $update->toWpFormat();
    } else {
      // No update available, get current plugin info.
      $update = $this->updater->getUpdateState()->getUpdate();

      // Adding the plugin info to the `no_update` property is required
      // for the enable/disable auto-update links to appear correctly in the UI.
      if ($update) {
        $transient->no_update[$update->filename] = $update;
      }
    }

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
      'tested'        => $plugin_info['tested'],
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

<?php

namespace Mill3_Plugins\Utils\Admin\Components;

use Mill3_Plugins\Utils\Interfaces\Mill3_Wp_Utils_Admin;

class Gutenberg_Sidebar implements Mill3_Wp_Utils_Admin
{

  /**
   * The ID of this plugin.
   *
   * @since    0.0.1
   * @access   private
   * @var      string    $plugin_name    The ID of this plugin.
   */
  private $plugin_name;

  /**
   * The version of this plugin.
   *
   * @since    0.0.1
   * @access   private
   * @var      string    $version    The current version of this plugin.
   */
  private $version;

  /**
   * The loader that's responsible for maintaining and registering all hooks that power
   * the plugin.
   *
   * @since    0.0.1
   * @access   protected
   * @var      Mill3_Wp_Utils_Loader    $loader    Maintains and registers all hooks for the plugin.
   */
  private $loader;

  public function __construct($plugin_name, $version, $loader)
  {
    $this->plugin_name = $plugin_name;
    $this->version = $version;
    $this->loader = $loader;

    $this->loader->add_action('admin_enqueue_scripts', $this, 'enqueue_scripts');
    $this->loader->add_action('admin_enqueue_scripts', $this, 'enqueue_styles');
  }

  public function enqueue_styles()
  {
    wp_enqueue_style('mill3-wp-utils-gutenberg-sidebar', plugin_dir_url(__FILE__) . 'css/mill3-wp-utils-gutenberg-sidebar.css', array(), $this->version, 'all');
  }

  public function enqueue_scripts()
  {
    wp_enqueue_script('jquery-ui-resizable');
    wp_enqueue_script('mill3-wp-utils-gutenberg-sidebar', plugin_dir_url(__FILE__) . 'js/mill3-wp-utils-gutenberg-sidebar.js', array('jquery-ui-resizable'), $this->version, true);
  }
}

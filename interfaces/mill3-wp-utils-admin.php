<?php

namespace Mill3_Plugins\Utils\Interfaces;

interface Mill3_Wp_Utils_Admin {
  public function __construct($plugin_name, $version, $loader);
  public function enqueue_styles();
  public function enqueue_scripts();
}

<?php

namespace Mill3_Plugins\Utils\Components;
use Mill3_Plugins\Utils\Components\Mill3_Wp_Utils_Component;

class Gutenberg_Sidebar extends Mill3_Wp_Utils_Component
{
    protected function init(): void {
        $this->loader->add_action('admin_enqueue_scripts', $this, 'enqueue_scripts');
        $this->loader->add_action('admin_enqueue_scripts', $this, 'enqueue_styles');
    }

    public function enqueue_styles(): void {
        wp_enqueue_style($this->plugin->get_name() . '-gutenberg-sidebar', plugin_dir_url(__FILE__) . 'css/mill3-wp-utils-gutenberg-sidebar.css', array(), $this->version(), 'all');
    }

    public function enqueue_scripts(): void {
        wp_enqueue_script('jquery-ui-resizable');
        wp_enqueue_script($this->plugin->get_name() . '-gutenberg-sidebar', plugin_dir_url(__FILE__) . 'js/mill3-wp-utils-gutenberg-sidebar.js', array('jquery-ui-resizable'), $this->version(), true);
    }

    // getters
    public static function id() : string { return 'gutenberg-sidebar'; }
    public function version() : string { return '0.0.1'; }
    public function title() : string { return 'Gutenberg Sidebar'; }
    public function description() : string { return 'Ever find that Rich Block Editor sidebar is too small? This module enable you to enlarge the Rich Block Editor sidebar to the size of your dreams.'; }

}

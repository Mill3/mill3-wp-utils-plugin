<?php

namespace Mill3_Plugins\Utils\Components;
use Mill3_Plugins\Utils\Components\Mill3_Wp_Utils_Component;

class iOS26ScrollFix extends Mill3_Wp_Utils_Component
{
    protected function init(): void {
        if( is_admin() ) return;

        $this->loader->add_action('wp_enqueue_scripts', $this, 'enqueue_styles', 200);
        $this->loader->add_action('wp_footer', $this, 'inject_element_to_footer');
    }

    // add css to frontend
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin->get_name() . '-ios26-scroll-fix', plugin_dir_url(__FILE__) . 'css/mill3-wp-utils-ios26-scroll-fix.css', array(), $this->version(), 'all');
    }

    // inject element to DOM
    public function inject_element_to_footer() {
        echo '<div class="ios26-scroll-fix" aria-hidden="true" inert><div class="ios26-scroll-fix-overlay"></div></div>';
    }



    // getters
    public static function id() : string { return 'ios26-scroll-fix'; }
    public function version() : string { return '0.0.1'; }
    public function title() : string { return __('iOS 26 Scroll Fix', 'mill3-wp-utils'); }
    public function description() : ?string { return __('Fix iOS 26 scroll bug that made content visible below Apple\'s island.', 'mill3-wp-utils'); }
}

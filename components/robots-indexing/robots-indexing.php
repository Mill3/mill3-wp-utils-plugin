<?php

namespace Mill3_Plugins\Utils\Components;
use Mill3_Plugins\Utils\Components\Mill3_Wp_Utils_Component;

/**
*
* ! WARNING : TREAT THIS FILE VERY CAREFULLY BECAUSE IT COULD CAUSE CATASTROPHIC SEO HAVOC !
*
*/

class Robots_Indexing extends Mill3_Wp_Utils_Component
{
    protected function init(): void {

        $url = get_option('siteurl');

        // if url contains mill3.dev OR localhost, prevent robots indexing
        if( str_contains($url, 'mill3.dev') || str_contains($url, 'localhost') ) $this->loader->add_filter('pre_option_blog_public', $this, 'filter_option');
    }

    public function filter_option($blog_public) { return 0; }

    // getters
    public static function id() : string { return 'robots-indexing'; }
    public function version() : string { return '0.0.1'; }
    public function title() : string { return __('Robots Indexing', 'mill3-wp-utils'); }
    public function description() : string { return __('Prevent indexing this website if domain contains mill3.dev. Can\'t be disabled.', 'mill3-wp-utils'); }
}

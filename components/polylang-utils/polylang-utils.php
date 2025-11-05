<?php

namespace Mill3_Plugins\Utils\Components;
use Mill3_Plugins\Utils\Components\Mill3_Wp_Utils_Component;

class Polylang_Utils extends Mill3_Wp_Utils_Component
{
    protected $pll_duplicate_action_meta_name;

    protected function init(): void 
    {
        if( function_exists('pll_the_languages') ) {
            $this->pll_duplicate_action_meta_name = class_exists('PLL_Duplicate_Action', false) ? \PLL_Duplicate_Action::META_NAME : 'pll_duplicate_content';

            $this->loader->add_filter('get_user_metadata', $this, 'force_pll_duplicate_action_user_meta', 10, 4);
            $this->loader->add_action('admin_head', $this, 'hide_pll_duplicate_button');
        }
    }

    public function force_pll_duplicate_action_user_meta($value, $object_id, $meta_key, $single) {
        global $post;

        if( $meta_key === $this->pll_duplicate_action_meta_name ) {
            $o = array();
            $o[ $post->post_type ] = 1;
            $value = [ $o ];
        }

        return $value;
    }

    public function hide_pll_duplicate_button() {
        echo '<style>#pll-duplicate { display: none; !important; }</style>';
    }

    // getters
    public static function id() : string { return 'polylang-utils'; }
    public function version() : string { return '0.0.1'; }
    public function title() : string { return __('Polylang Utils', 'mill3-wp-utils'); }
    public function description() : string { return __('Fix various bug inside Polylang. Can\'t be disabled.', 'mill3-wp-utils'); }
}

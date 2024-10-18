<?php

namespace Mill3_Plugins\Utils\Components;
use Mill3_Plugins\Utils\Components\Mill3_Wp_Utils_Component;

class Block_Visibility extends Mill3_Wp_Utils_Component
{
    protected function init(): void {
        $this->loader->add_filter('block_visibility_settings_defaults', $this, 'default_settings');
        $this->loader->add_filter('admin_menu', $this, 'admin_menu', 999);
        $this->loader->add_filter('wp_enqueue_scripts', $this, 'remove_screen_size_inline_css', 1001);
    }

    // set plugin default settings (can be updated in Backend)
    public function default_settings($defaults) {
        $defaults['visibility_controls']['browser_device']['enable'] = false;
        $defaults['visibility_controls']['cookie']['enable'] = false;
        $defaults['visibility_controls']['date_time']['enable'] = false;
        $defaults['visibility_controls']['location']['enable'] = false;
        $defaults['visibility_controls']['metadata']['enable'] = false;
        $defaults['visibility_controls']['query_string']['enable'] = false;
        $defaults['visibility_controls']['referral_source']['enable'] = false;        
        $defaults['visibility_controls']['screen_size']['enable'] = false;
        $defaults['visibility_controls']['screen_size']['breakpoints']['large'] = '1024px';
        $defaults['visibility_controls']['screen_size']['controls']['extra_large'] = false;
        $defaults['visibility_controls']['screen_size']['controls']['large'] = false;
        $defaults['visibility_controls']['screen_size']['controls']['medium'] = false;
        $defaults['visibility_controls']['screen_size']['controls']['small'] = false;
        $defaults['visibility_controls']['screen_size']['controls']['extra_small'] = false;
        $defaults['visibility_controls']['screen_size']['enable_frontend_css'] = false;
        $defaults['visibility_controls']['url_path']['enable'] = false;
        $defaults['visibility_controls']['visibility_by_role']['enable'] = false;
        $defaults['visibility_controls']['visibility_presets']['enable'] = false;
        $defaults['visibility_controls']['acf']['enable'] = false;
        $defaults['visibility_controls']['woocommerce']['enable'] = false;
        $defaults['visibility_controls']['edd']['enable'] = false;
        $defaults['visibility_controls']['wp_fusion']['enable'] = false;

        $defaults['plugin_settings']['default_controls'] = array();
        $defaults['plugin_settings']['contextual_indicator_color'] = '#121212';
        $defaults['plugin_settings']['enable_block_opacity'] = true;
        $defaults['plugin_settings']['block_opacity'] = 20;
        $defaults['plugin_settings']['enable_editor_notices'] = false;
        $defaults['plugin_settings']['remove_on_uninstall'] = true;

        return $defaults;
    }

    // hide Block Visibility Plugin Settings if user is not from @mill3.studio
    public function admin_menu() {
        // for development, show ACF admin menu
        if (defined('THEME_ENV') && THEME_ENV !== 'production') return true;
    
        // get current user
        $user = wp_get_current_user();
    
        // if we can't find this user (it should never happen, but just in case), hide ACF admin menu
        if (!$user) return false;
    
        // get user email
        $email = $user->user_email;
    
        // if email is not defined, hide ACF admin menu
        if (!$email) return false;
    
        // remove settings menu if user email is from @mill3.studio
        if( !str_ends_with($email, '@mill3.studio') ) remove_submenu_page('options-general.php', 'block-visibility-settings');
    }

    // remove inline css injected by screen size visibility options
    public function remove_screen_size_inline_css() {
        wp_dequeue_style('block-visibility-screen-size-styles');
        wp_deregister_style('block-visibility-screen-size-styles');
    }

    // getters
    public static function id() : string { return 'block-visibility'; }
    public function version() : string { return '0.0.2'; }
    public function title() : string { return __('Block Visibility', 'mill3-wp-utils'); }
    public function description() : string { return __('This module set default values for Block Visibility plugin.', 'mill3-wp-utils'); }
}

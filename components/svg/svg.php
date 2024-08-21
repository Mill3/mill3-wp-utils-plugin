<?php

namespace Mill3_Plugins\Utils\Components;
use Mill3_Plugins\Utils\Components\Mill3_Wp_Utils_Component;

class SVG extends Mill3_Wp_Utils_Component
{
    protected function init(): void {
        $this->loader->add_filter('upload_mimes', $this, 'add_mime_type');
        $this->loader->add_filter('wp_check_filetype_and_ext', $this, 'allow_svg_filetype', 10, 4);

        if( !is_admin() ) return;

        $this->loader->add_action('admin_enqueue_scripts', $this, 'enqueue_styles');
    }

    // Enable mime type
    public function add_mime_type($mimes) {
        $mimes['svg'] = 'image/svg+xml';

        return $mimes;
    }

    // Allow upload of SVG (https://codepen.io/chriscoyier/post/wordpress-4-7-1-svg-upload)
    public function allow_svg_filetype($data, $file, $filename, $mimes) {

        /*
        global $wp_version;
        if ( $wp_version !== '4.7.1' ) return $data;
        */
    
        $filetype = wp_check_filetype( $filename, $mimes );
    
        return [
            'ext' => $filetype['ext'],
            'type' => $filetype['type'],
            'proper_filename' => $data['proper_filename']
        ];
    
    }

    // add css to all admin pages
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin->get_name() . '-svg', plugin_dir_url(__FILE__) . 'css/mill3-wp-utils-svg.css', array(), $this->version(), 'all');
    }



    // getters
    public static function id() : string { return 'svg'; }
    public function version() : string { return '0.0.1'; }
    public function title() : string { return __('SVG', 'mill3-wp-utils'); }
    public function description() : ?string { return __('Allow .svg files in Wordpress Media Manager.', 'mill3-wp-utils'); }
}

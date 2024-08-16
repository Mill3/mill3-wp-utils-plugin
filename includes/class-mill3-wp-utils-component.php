<?php

namespace Mill3_Plugins\Utils\Components;


/**
 * The core plugin class.
 *
 * @since      0.0.1
 * @package    Mill3_Wp_Utils
 * @subpackage Mill3_Wp_Utils/includes
 * @author     MILL3 Studio <info@mill3.studio>
 */
class Mill3_Wp_Utils_Component
{
    const NOTICE_SUCCESS = 'success';
    const NOTICE_ERROR = 'error';
    const NOTICE_INFO = 'info';
    const NOTICE_WARNING = 'warning';

    protected $admin;
    protected $loader;
    protected $plugin;

    private $_enabled = false;

    public function __construct($plugin, $loader, $admin = null, $enabled = false) {
        $this->plugin = $plugin;
        $this->loader = $loader;
        $this->admin = $admin;

        $this->_enabled = boolval($enabled);

        if( $this->_enabled ) $this->init();
    }

    protected function init() : void {}
    
    public function enabled($enabled = null) : bool {
        if( $enabled === true || $enabled === false ) $this->_enabled = $enabled;
        return $this->_enabled;
    }

    public function has_admin_page() : bool { return method_exists( $this, 'admin_page' ); }
    public function get_admin_menu_slug() : string { return $this->admin->menu_slug . '-' . $this->id(); }
    public function get_admin_menu_url() : string { return menu_page_url( $this->get_admin_menu_slug(), false ); }
    public function show_access_restricted_page() : void {
        $this->admin->render_template(
            $this->title(),
            'admin/views/404.php',
            array()
        );
    }

    protected function load_view($filepath, $data = array(), $print = true) {
        $output = NULL;

        if( file_exists($filepath) ) {
            // Extract the variables to a local namespace
            extract($data);

            // Start output buffering
            ob_start();

            // Include the template file
            include $filepath;

            // End buffering and return its contents
            $output = ob_get_clean();
        }

        if( $print ) print $output;
        else return $output;
    }
    protected function form_redirect($notice_type = self::NOTICE_SUCCESS) : void {
        $referer = strtolower( wp_get_referer() );
        $url = add_query_arg( array('settings-updated' => $notice_type), $referer );

        wp_safe_redirect( sanitize_url($url), 302, 'MILL3 WP Utils Plugin' );
        exit;
    }

    // getters
    public static function id() : string { return null; }
    public function version() : string { return null; }
    public function title() : string { return null; }
    public function description() : ?string { return null; }
}

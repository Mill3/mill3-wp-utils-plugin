<?php

namespace Mill3_Plugins\Utils\Admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://mill3.studio
 * @since      0.0.1
 *
 * @package    Mill3_Wp_Utils
 * @subpackage Mill3_Wp_Utils/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Mill3_Wp_Utils
 * @subpackage Mill3_Wp_Utils/admin
 * @author     MILL3 Studio <info@mill3.studio>
 */
class Mill3_Wp_Utils_Admin
{
    private $plugin;
    private $loader;
    private $html_helper;
    private $menu_items;

    public $menu_slug = MILL3_WP_UTILS_PLUGIN_SLUG;

    /**
     * Initialize the class and set its properties.
     *
     * @since    0.0.1
     * @param      string    $plugin       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     * @param      object    $loader     The class responsible for loading the components of the plugin.
     */
    public function __construct($plugin, $loader) {
        $this->plugin = $plugin;
        $this->loader = $loader;
        $this->html_helper = new \Mill3_Plugins\Utils\Admin\HTML_Helper();

        $this->loader->add_filter('plugin_action_links_' . MILL3_WP_UTILS_PLUGIN_DIR_NAME . '/' . MILL3_WP_UTILS_PLUGIN_FILE_NAME, $this, 'add_plugin_link');
        $this->loader->add_filter('admin_body_class', $this, 'admin_body_class');
        $this->loader->add_filter('admin_footer_text', $this, 'admin_footer_text');
        $this->loader->add_action('admin_menu', $this, 'admin_menu');
        $this->loader->add_action('admin_notices', $this, 'admin_notices');
        $this->loader->add_action('admin_enqueue_scripts', $this, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $this, 'enqueue_scripts');
        $this->loader->add_action('wp_ajax_mill3_wp_utils_admin_toggle_component', $this, 'toggle_component');
    }

    public function add_plugin_link($actions) {
        $new_actions = array();
        $new_actions[] = '<a href="'. esc_url( menu_page_url($this->menu_slug, false) ) .'">' . __( 'Settings', 'mill3-wp-utils' ) . '</a>';

        return array_merge( $new_actions, $actions );
    }

    public function admin_body_class($classes) {
        if( $this->is_current_screen() ) $classes .= ' mill3-wp-utils-page';
        return $classes;
    }

    public function admin_footer_text($text) {
        if( $this->is_current_screen() ) $text = __('Thank you for your confidence in <a href="https://mill3.studio" target="_blank">MILL3 Studio</a>.', 'mill3-wp-utils');
        return $text;
    }

    public function admin_menu() {
        $svg = file_get_contents( MILL3_WP_UTILS_PLUGIN_DIR_PATH . '/admin/assets/icon.svg' );
        $base64_icon = base64_encode( $svg );

        // top level menu
        add_menu_page(
            __( 'MILL3 Wordpress Utility Plugin', 'mill3-wp-utils' ), 
            __( 'MILL3 Plugin', 'mill3-wp-utils' ), 
            'manage_options', 
            $this->menu_slug, 
            false,
            'data:image/svg+xml;base64,' . $base64_icon,
            null
        );

        /**
         * To add a top-level menu item which resolves to a sub-menu item, the menu slugs of each add_menu_page and add_submenu_page calls need to match. 
         * This is useful for when you need the top-level menu item label to be different to the first sub-level item. 
         * Otherwise the top-level item label is repeated as the first sub-level item.
         */
        add_submenu_page(
            $this->menu_slug,
            __( 'MILL3 Wordpress Utility Plugin', 'mill3-wp-utils' ),
            __( 'Dashboard', 'mill3-wp-utils'),
            'manage_options', 
            $this->menu_slug,
            array( $this, 'admin_page' ),
        );

        // create admin page for each components with admin_page method
        foreach($this->plugin->components as $component) {
            // skip if component has no settings URL
            if( !$component->has_admin_page() ) continue;

            add_submenu_page(
                $this->menu_slug,
                $component->title(),
                $component->title(),
                'manage_options', 
                $component->get_admin_menu_slug(),
                array( $component, 'admin_page' )
            );
        }

        // add "Support" submenu
        add_submenu_page(
            $this->menu_slug,
            __( 'Support', 'mill3-wp-utils' ),
            __( 'Support', 'mill3-wp-utils'),
            'manage_options', 
            'https://mill3.studio',
            false
        );

        // save menu_items to memory before deleting them
        // this way, we are able to save menu_page_url to use in Javascript
        $this->get_menu_items();

        // we are required to add_submenu_page for ALL modules because otherwise,
        // menu_page_url will not work if submenu_slug doesn't exist
        foreach($this->plugin->components as $component) {
            // skip if component has no settings URL OR is enabled
            if( !$component->has_admin_page() || $component->enabled() ) continue;

            // remove admin page for disabled component
            remove_submenu_page( $this->menu_slug, $component->get_admin_menu_slug() );
        }
    }

    public function admin_page() {
        $this->render_template(
            __( 'Modules', 'mill3-wp-utils'),
            'admin/views/dashboard.php', 
            array('admin' => $this, 'components' => $this->plugin->components, 'menu_items' => $this->get_menu_items())
        );
    }

    public function admin_notices() {
        // only handle notices from this plugin
        if( !$this->is_current_screen() ) return;

        if ( isset( $_REQUEST['settings-updated'] ) ) {
            $this->load_view( MILL3_WP_UTILS_PLUGIN_DIR_PATH . 'admin/ui/admin-notices.php', array('type' => $_REQUEST['settings-updated']) );
        }
    }

    public function enqueue_styles() {
        if( !$this->is_current_screen() ) return;

        wp_enqueue_style(
            $this->plugin->get_name(), 
            plugin_dir_url( __FILE__ ) . 'css/mill3-wp-utils-admin.css', 
            array(), 
            $this->plugin->get_version(), 
            'all'
        );
    }

    public function enqueue_scripts() {
        if( !$this->is_current_screen() ) return;

        wp_enqueue_script(
            $this->plugin->get_name(), 
            plugin_dir_url( __FILE__ ) . 'js/mill3-wp-utils-admin.js', 
            array(), 
            $this->plugin->get_version(), 
            true
        );
    }

    public function toggle_component() {
        global $submenu;

        $component_id = $_POST['component'];
        $activated = $_POST['status'] === '1' ? true : false;

        if( !$component_id ) {
            wp_send_json_error( __('Component ID is not defined.', 'mill3wp'), 400 );
            wp_die();
        }

        $component = $this->plugin->get_component_by_id($component_id);

        if( !$component ) {
            wp_send_json_error( __('Can\'t find component.', 'mill3wp'), 400 );
            wp_die();
        }

        if( $activated ) $success = $this->plugin->activate_component($component_id);
        else $success = $this->plugin->deactivate_component($component_id);

        wp_send_json(array('success' => $success));
        wp_die();
    }

    public function render_template($title, $view, $data = array()) {
        // load partials
        $header = $this->load_view(MILL3_WP_UTILS_PLUGIN_DIR_PATH . 'admin/ui/header.php', array(), false);
        $breadcrumb = $this->load_view(MILL3_WP_UTILS_PLUGIN_DIR_PATH . 'admin/ui/breadcrumb.php', array('title' => $title), false);
        $nav = $this->load_view(MILL3_WP_UTILS_PLUGIN_DIR_PATH . 'admin/ui/nav.php', array('menu_items' => $this->get_menu_items()), false);
        $body = $this->load_view(MILL3_WP_UTILS_PLUGIN_DIR_PATH . $view, $data, false);

        // render template
        $this->load_view(MILL3_WP_UTILS_PLUGIN_DIR_PATH . 'admin/template.php', array(
            'header' => $header, 
            'breadcrumb' => $breadcrumb, 
            'nav' => $nav,
            'body' => $body,
        ));
    }



    

    // private methods
    private function load_view($filepath, $data = array(), $print = true) {
        $output = NULL;

        if( file_exists($filepath) ) {
            // add universal data
            $data['admin'] = $this;
            $data['base_url'] = plugin_dir_url(MILL3_WP_UTILS_PLUGIN_FILE);
            $data['html_helper'] = $this->html_helper;

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

    private function get_menu_items() {
        if( $this->menu_items ) return $this->menu_items;

        $current_page_slug = array_key_exists('page', $_GET) ? $_GET['page']: null;
        $this->menu_items = array();

        // always add "Dashboard" menu item
        $this->menu_items[] = array(
            'id' => 'root',
            'href' => menu_page_url( $this->menu_slug, false ),
            'title' => __( 'Dashboard', 'mill3-wp-utils'),
            'is_active' => $current_page_slug === $this->menu_slug,
            'is_enabled' => true,
        );

        // create menu item for each components with admin_page method
        foreach($this->plugin->components as $component) {
            // skip if component has no settings URL
            if( !$component->has_admin_page() ) continue;

            $this->menu_items[] = array(
                'id' => $component->id(),
                'href' => menu_page_url( $component->get_admin_menu_slug(), false ),
                'title' => $component->title(),
                'is_active' => $current_page_slug === $component->get_admin_menu_slug(),
                'is_enabled' => $component->enabled()
            );
        }

        // always add "Support" menu item
        $this->menu_items[] = array(
            'id' => 'support',
            'href' => 'https://mill3.studio',
            'target' => '_blank',
            'title' => __( 'Support', 'mill3-wp-utils'),
            'is_active' => false,
            'is_enabled' => true,
        );

        return $this->menu_items;
    }

    private function is_current_screen() {
        $screen = get_current_screen();
        return ($screen->id === 'toplevel_page_' . $this->menu_slug) || str_contains($screen->id, 'mill3-plugin_page');
    }
}

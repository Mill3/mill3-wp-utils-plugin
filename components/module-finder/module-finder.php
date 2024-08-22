<?php

namespace Mill3_Plugins\Utils\Components;
use Mill3_Plugins\Utils\Components\Mill3_Wp_Utils_Component;

class Module_Finder extends Mill3_Wp_Utils_Component
{
    protected function init() : void {
        if( !is_admin() ) return;

        $this->loader->add_action('admin_enqueue_scripts', $this, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $this, 'enqueue_scripts');
    }

    public function admin_page() : void {
        if( $this->enabled() ) {
            // get all registered blocks
            $blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();

            // filter blocks who start with acf/
            $blocks = array_filter( $blocks, fn($block) => str_starts_with($block->name, 'acf/') );

            // order blocks by title
            usort($blocks, fn($a, $b) => strcmp($a->title, $b->title));

            // get name and title for each filtered blocks
            $blocks = array_map(fn($block) => array($block->name, $block->title), $blocks);

            // add empty values at first
            array_unshift($blocks, array('', __('Select a module', 'mill3-wp-utils')), array('', '---------------'));

            // current block and results are null by default
            $currentBlock = null;
            $results = null;
            $empty = false;

            // if we render page from form submit
            if( isset($_POST['block']) ) {
                // make sure request came from our admin page
                check_admin_referer( $this->id() );

                // sanitize the input
                $currentBlock = sanitize_text_field( $_POST['block'] );

                // get all public post-types except types described below
                $excluded_types = array('attachment');
                $post_types_objects = get_post_types(array('public' => true), 'objects');
                $post_types = array_diff( array_map(fn($post_type) => $post_type->name, $post_types_objects), $excluded_types );

                // get all posts who contain one or more of current block
                $posts = get_posts(array(
                    'post_type' => $post_types, 
                    'post_status' => 'any', 
                    'order' => 'ASC',
                    'orderby' => 'title',
                    's' => $currentBlock
                ));

                // check if posts are empty or not
                $empty = count($posts) === 0;

                if( !$empty ) {
                    // remap posts data to a simpler architecture
                    $posts = array_map(function($post) use($post_types_objects) {
                        $post_type = $post_types_objects[ get_post_type($post) ];

                        return array(
                            'id' => $post->ID,
                            'title' => $post->post_title,
                            'post_type' => $post_type->label,
                            'edit_link' => add_query_arg(array('post' => $post->ID, 'action' => 'edit'), admin_url('post.php')),
                            'view_link' => $post_type->_builtin || $post_type->publicly_queryable ? get_permalink($post) : null,
                        );
                    }, $posts);

                    $results = array();

                    // group posts by post-type
                    foreach($posts as $post) {
                        // if post-type doesn't exists in results, add it
                        if( !array_key_exists($post['post_type'], $results) ) $results[ $post['post_type'] ] = array();

                        // add post to post-type group
                        $results[ $post['post_type'] ][] = $post;
                    }
                }
            }

            // render template
            $this->admin->render_template( $this->title(), 'admin/views/module-finder.php', array(
                'component' => $this,
                'blocks' => $blocks,
                'currentBlock' => $currentBlock,
                'results' => $results,
                'empty' => $empty,
            ));
        } 
        else $this->show_access_restricted_page();
    }

    // add css to admin page
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin->get_name() . '-module-finder', plugin_dir_url(__FILE__) . 'css/mill3-wp-utils-module-finder.css', array($this->plugin->get_name()), $this->version(), 'all');
    }
    
    // add js to admin page
    public function enqueue_scripts() {
        if( !$this->is_current_screen() ) return;

       wp_enqueue_script($this->plugin->get_name() . '-module-finder', plugin_dir_url( __FILE__ ) . 'js/mill3-wp-utils-module-finder.js', array(), $this->plugin->get_version(), true);
    }


    // private methods
    private function is_current_screen() {
        $screen = get_current_screen();
        return str_contains($screen->id, MILL3_WP_UTILS_PLUGIN_SLUG . '-module-finder');
    }


    // getters
    public static function id() : string { return 'module-finder'; }
    public function version() : string { return '0.0.1'; }
    public function title() : string { return __('Module Finder', 'mill3-wp-utils'); }
    public function description() : string { return __('This module allows developers to quickly find posts using a module.', 'mill3-wp-utils'); }
}

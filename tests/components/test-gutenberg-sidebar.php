<?php

namespace Mill3_Plugins\Utils;

class TestGutenbergSidcebar extends \WP_UnitTestCase
{

    protected $admin_user_id;

    public function setUp(): void
    {
        parent::setUp();

        // Create and log in as admin user
        $this->admin_user_id = $this->factory->user->create(array(
            'role' => 'administrator',
        ));

        wp_set_current_user($this->admin_user_id);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->admin_user_id = null;
    }

    /**
     * Test if the JS is enqueued
     *
     */
    function test_js_is_enqueued()
    {
        set_current_screen('edit-post');

        // load the admin scripts
        do_action('admin_enqueue_scripts');

        // check if the js is enqueued
        $this->assertTrue(wp_style_is('mill3-wp-utils-gutenberg-sidebar'), 'JS is not enqueued');
    }

    /**
     * Test if the CSS is enqueued
     *
     */
    function test_css_is_enqueued()
    {
        set_current_screen('edit-post');

        // load the admin scripts
        do_action('admin_enqueue_scripts');

        // check if the css is enqueued
        $this->assertTrue(wp_style_is('mill3-wp-utils-gutenberg-sidebar'), 'CSS is not enqueued');
    }
}

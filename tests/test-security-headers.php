<?php

namespace Mill3_Plugins\Utils;

class SecurityHeadersTest extends \WP_UnitTestCase
{

    public $plugin;

    function set_up()
    {
        parent::set_up();
    }

    // Run this after each test
    // public function tearDown() {
    //     parent::tearDown();
    // }

    function test_http_headers()
    {
        // Simulate a request to the homepage
        echo home_url();
        $response = wp_remote_get( home_url() );
        // var_dump($response);

        // Check if the request was successful
        $this->assertNotWPError( $response );
    }
}

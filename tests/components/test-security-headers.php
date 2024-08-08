<?php

namespace Mill3_Plugins\Utils;

class SecurityHeadersTest extends \WP_UnitTestCase
{

    public $plugin;

    public function setUp(): void
    {
        parent::setUp();
    }

    // Run this after each test
    public function tearDown(): void
    {
        parent::tearDown();
    }

    function test_http_headers()
    {
        // Simulate a request to the homepage
        $response = wp_remote_get(home_url());

        // Check if the request was successful
        $this->assertNotWPError($response);
    }
}

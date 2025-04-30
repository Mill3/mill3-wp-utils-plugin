<?php

// Remove Auto Sizes for Lazy Loaded Image in Wordpress (added in WP 6.7)
// https://make.wordpress.org/core/2024/10/18/auto-sizes-for-lazy-loaded-images-in-wordpress-6-7/
add_filter('wp_img_tag_add_auto_sizes', '__return_false');


// Remove the ACF translation instruction added in Polylang Pro 3.7 displaying Polylang language handling setting
// Priority is set to 11 to run after Dispatcher::append_translation_instructions which uses default priority 10
add_filter('acf/pre_render_fields', function ($fields) {
    // Remove the filter that was just added
    remove_filter('acf/prepare_field', array('WP_Syntex\Polylang_Pro\Integrations\ACF\Dispatcher', 'get_field_instructions'));

    // Return the fields unchanged
    return $fields;
}, 11);

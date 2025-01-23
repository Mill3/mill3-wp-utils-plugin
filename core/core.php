<?php

// Remove Auto Sizes for Lazy Loaded Image in Wordpress (added in WP 6.7)
// https://make.wordpress.org/core/2024/10/18/auto-sizes-for-lazy-loaded-images-in-wordpress-6-7/ 
add_filter('wp_img_tag_add_auto_sizes', '__return_false');

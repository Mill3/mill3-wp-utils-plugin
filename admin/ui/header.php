<?php
/** 
 * @var object $admin : Mill3_Wp_Utils_Admin instance
 * @var string $base_url : Base URL pointing to plugin root.
 */
?>

<div class="mill3-wp-utils-plugin__header">
    <a href="https://mill3.studio" target="_blank" title="MILL3 Studio" class="mill3-wp-utils-plugin__logo">
        <img src="<?php echo esc_url($base_url) ?>admin/assets/logo.png" width="192" height="192" alt="MILL3 Studio">
    </a>

    <h1 class="mill3-wp-utils-plugin__title"><?php esc_html_e('MILL3 Wordpress Utility Plugin', 'mill3-wp-utils'); ?></h1>
</div>

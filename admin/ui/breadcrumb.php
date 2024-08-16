<?php
/** 
 * @var object $admin : Mill3_Wp_Utils_Admin instance
 * @var string $base_url : Base URL pointing to plugin root.
 * @var string $title : Current page title.
 */
?>

<div class="mill3-wp-utils-plugin__breadcrumb">
    <span><?php esc_html_e('Dashboard', 'mill3-wp-utils')  ?></span>
    <span>/</span>
    <span class="active"><?php echo esc_html($title)  ?></span>
</div>

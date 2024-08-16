<?php
/** 
 * @var object $admin : Mill3_Wp_Utils_Admin instance
 * @var string $base_url : Base URL pointing to plugin root.
 * @var string $type : Notice type. (required) [success, error, warning, info]
 */
?>
<div class="notice notice-<?php echo $type ?> <?php if($type !== 'error'): ?>is-dismissible<?php endif; ?>"> 
    <p><strong><?php esc_html_e('admin_notice_' . $type, 'mill3-wp-utils') ?></strong></p>
</div>

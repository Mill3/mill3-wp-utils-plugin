<?php
/** 
 * @var object $admin : Mill3_Wp_Utils_Admin instance
 * @var string $base_url : Base URL pointing to plugin root.
 * @var string $action : Action name. (required)
 * @var string $nonce : WP Nonce name. (optional)
 */
?>

<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>">
<?php if( isset($nonce) and !empty($nonce) ){ wp_nonce_field($nonce); } ?>

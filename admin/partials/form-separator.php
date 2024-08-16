<?php
/** 
 * @var object $admin : Mill3_Wp_Utils_Admin instance
 * @var string $base_url : Base URL pointing to plugin root.
 * @var string $classname : CSS classes to add to module. (optional)
 * @var string $attributes : HTML attributes to add to module. (optional)
 */

$classname = isset($classname) ? $classname : '';
$attributes = isset($attributes) ? $attributes : array();

?>
<hr class="mill3-wp-utils-plugin__form__separator <?php echo esc_attr($classname); ?>" <?php echo implode(' ', $attributes); ?>>

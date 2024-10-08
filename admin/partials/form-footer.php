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
<footer class="mill3-wp-utils-plugin__form__footer <?php echo esc_attr($classname); ?>" <?php echo implode(' ', $attributes); ?>>
    <!--
    <input class="mill3-wp-utils-plugin__form__reset button button-secondary" type="submit" value="<?php esc_attr_e('Reset Options', 'mill3-wp-utils') ?>">
    -->
    <input class="mill3-wp-utils-plugin__form__submit button button-primary" type="submit" value="<?php esc_attr_e('Save Changes', 'mill3-wp-utils') ?>">
</footer>

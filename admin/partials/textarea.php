<?php
/** 
 * @var object $admin : Mill3_Wp_Utils_Admin instance
 * @var string $base_url : Base URL pointing to plugin root.
 * @var string $label : Field label. (required)
 * @var string $name : Input name. (required)
 * @var string $value : Input value. (optional)
 * @var string $description : Form description. (optional)
 * @var array  $input_attrs : Array of HTML attributes to add to input. (optional)
 * @var string $classname : CSS classes to add to module. (optional)
 * @var string $attributes : HTML attributes to add to module. (optional)
 */

$value = isset($value) ? $value : '';
$input_attrs = isset($input_attrs) ? $input_attrs : array();
$classname = isset($classname) ? $classname : '';
$attributes = isset($attributes) ? $attributes : array();

?>
<div class="mill3-wp-utils-plugin__field <?php echo esc_attr($classname); ?>" <?php echo implode(' ', $attributes); ?>>
    <label class="mill3-wp-utils-plugin__field__label" for="<?php echo esc_attr($name) ?>"><?php echo $label ?></label>

    <div class="mill3-wp-utils-plugin__field__meta">
        <textarea class="mill3-wp-utils-plugin__textarea" type="text" name="<?php echo esc_attr($name) ?>" cols="60" rows="10" <?php echo implode(' ', $input_attrs); ?>><?php echo esc_textarea($value) ?></textarea>

        <?php if($description): ?>
        <p class="mill3-wp-utils-plugin__field__description"><?php echo $description ?></p>
        <?php endif; ?>
    </div>
</div>

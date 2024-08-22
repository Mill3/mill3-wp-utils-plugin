<?php
/** 
 * @var object $admin : Mill3_Wp_Utils_Admin instance
 * @var string $base_url : Base URL pointing to plugin root.
 * @var string $label : Field label. (required)
 * @var string $name : Select name. (required)
 * @var array  $options : Select options. (required) example: [ [value, label], [value, label] ]
 * @var string $value : Select value. (optional)
 * @var string $description : Form description. (optional)
 * @var array  $input_attrs : Array of HTML attributes to add to input. (optional)
 * @var string $classname : CSS classes to add to module. (optional)
 * @var string $attributes : HTML attributes to add to module. (optional)
 */

$options = isset($options) && is_array($options) ? $options : array();
$input_attrs = isset($input_attrs) ? $input_attrs : array();
$classname = isset($classname) ? $classname : '';
$attributes = isset($attributes) ? $attributes : array();

?>
<div class="mill3-wp-utils-plugin__field <?php echo esc_attr($classname); ?>" <?php echo implode(' ', $attributes); ?>>
    <label class="mill3-wp-utils-plugin__field__label" for="<?php echo esc_attr($name) ?>"><?php echo $label ?></label>

    <div class="mill3-wp-utils-plugin__field__meta">
        <select class="mill3-wp-utils-plugin__select" name="<?php echo esc_attr($name) ?>" <?php echo implode(' ', $input_attrs); ?>>
            <?php foreach($options as $option): ?>
            <option value="<?php echo esc_attr($option[0]) ?>"<?php if( $option[0] === $value ): ?> selected<?php endif; ?>><?php echo esc_html($option[1]) ?></option>
            <?php endforeach; ?>
        </select>

        <?php if($description): ?>
        <p class="mill3-wp-utils-plugin__field__description"><?php echo $description ?></p>
        <?php endif; ?>
    </div>
</div>

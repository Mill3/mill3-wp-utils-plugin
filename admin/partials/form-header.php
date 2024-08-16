<?php
/** 
 * @var object $admin : Mill3_Wp_Utils_Admin instance
 * @var string $base_url : Base URL pointing to plugin root.
 * @var string $title : Form title. (required)
 * @var string $description : Form description. (optional)
 * @var string $classname : CSS classes to add to module. (optional)
 * @var string $attributes : HTML attributes to add to module. (optional)
 */

$classname = isset($classname) ? $classname : '';
$attributes = isset($attributes) ? $attributes : array();

?>
<header class="mill3-wp-utils-plugin__form__header <?php echo esc_attr($classname); ?>" <?php echo implode(' ', $attributes); ?>>
    <h1 class="mill3-wp-utils-plugin__form__title"><?php echo esc_html($title) ?></h1>

    <?php if($description): ?>
    <p class="mill3-wp-utils-plugin__form__description"><?php echo esc_html($description) ?></p>
    <?php endif; ?>
</header>

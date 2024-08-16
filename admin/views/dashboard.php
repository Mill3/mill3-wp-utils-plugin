<?php
/** 
 * @var object $admin : Mill3_Wp_Utils_Admin instance
 * @var string $base_url : Base URL pointing to plugin root.
 * @var array $components : Collection of \Mill3_Plugins\Utils\Mill3_Wp_Utils_Component 
 * @var array $menu_items : Array of all submenu items (visible or not)
 */
?>

<ol id="mill3-wp-utils-plugin__dashboard" class="mill3-wp-utils-plugin__components">
    <?php foreach($components as $component): ?>
    <li class="mill3-wp-utils-plugin__component">
        <header class="mill3-wp-utils-plugin__component__header">
            <h3 class="mill3-wp-utils-plugin__component__title"><?php echo esc_html( $component->title() ) ?></h3>
            
            <?php if( $component->description() ): ?>
            <p class="mill3-wp-utils-plugin__component__description"><?php echo $component->description() ?></p>
            <?php endif; ?>
        </header>

        <div class="mill3-wp-utils-plugin__component__actions">
            <?php if( $component->has_admin_page() ): ?>
                <a 
                    href="<?php echo esc_url( $component->get_admin_menu_url() ) ?>" 
                    class="mill3-wp-utils-plugin__component__btn button button-secondary"
                ><?php esc_html_e('Settings', 'mill3-wp-utils'); ?></a>
            <?php endif; ?>

            <label class="mill3-wp-utils-plugin__component__toggle">
                <input 
                    type="checkbox"
                    class="mill3-wp-utils-plugin__component__input" 
                    name="<?php echo esc_attr( $component->id() ) ?>" 
                    <?php if( $component->enabled() ): ?>checked<?php endif; ?>
                    value="1"
                >
            </label>
        </div>
    </li>
    <?php endforeach; ?>
</ol>

<script id="mill3-wp-utils-plugin__menuItems" type="application/json">
    <?php echo json_encode($menu_items) ?>
</script>

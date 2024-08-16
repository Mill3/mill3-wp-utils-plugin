<?php
/** 
 * @var object $admin : Mill3_Wp_Utils_Admin instance
 * @var string $base_url : Base URL pointing to plugin root.
 */
?>
<nav class="mill3-wp-utils-plugin__nav">
    <?php foreach($menu_items as $item): ?>
        <?php if( !$item['is_enabled'] ) continue; ?>
        <a 
            href="<?php echo esc_url( $item['href'] ) ?>" 
            target="<?php echo array_key_exists('target', $item) ? esc_attr($item['target']) : '_self' ?>"
            class="mill3-wp-utils-plugin__navItem <?php if( $item['is_active'] ): ?>is-active<?php endif; ?>"
        ><?php echo esc_html( $item['title'] ) ?></a>
    <?php endforeach; ?>
</nav>

<?php
/** 
 * @var object $admin : Mill3_Wp_Utils_Admin instance
 * @var string $base_url : Base URL pointing to plugin root.
 * @var object $html_helper : \Mill3_Plugins\Utils\Admin\HTML_Helper instance.
 * 
 * @var object $component : Instance of Mill3_Plugins\Utils\Components\Mill3_Wp_Utils_Component\Module_Finder
 * @var array  $blocks : Array of blocks (id, name).
 * @var string $currentBlock : ID of selected block. (optional)
 * @var array  $results : Array of posts with selected block in our content. (optional)
 * @var bool   $empty : True if results is empty. Otherwise, false. (optional)
 */
?>
<form 
    id="module-finder" 
    class="mill3-wp-utils-plugin__form"
    action="<?php echo esc_url( $component->get_admin_menu_url() ); ?>"
    method="POST"
>
    <?php $html_helper->form_header($component->title(), $component->description()) ?>
    <?php $html_helper->form_body_open() ?>

        <?php $html_helper->form_action($component->id(), true) ?>
        <?php $html_helper->form_select(
            __('Module', 'mill3-wp-utils'),
            'block',
            $blocks,
            $currentBlock,
            __('Which module are you looking for?', 'mill3-wp-utils'),
            array('required'),
            'mill3-wp-utils-plugin__moduleFinder__blocks'
        ) ?>

    <?php $html_helper->form_body_close() ?>

    <?php if( $results ): ?>
    <ul class="mill3-wp-utils-plugin__moduleFinder__results">
        <?php foreach($results as $post_type => $posts): ?>
        <li class="mill3-wp-utils-plugin__moduleFinder__postType">
            <p class="mill3-wp-utils-plugin__moduleFinder__postTypeLabel"><?php echo esc_html($post_type) ?></p>
            <ul class="mill3-wp-utils-plugin__moduleFinder__posts">
                <?php foreach($posts as $post): ?>
                <li class="mill3-wp-utils-plugin__moduleFinder__post">
                    <p class="mill3-wp-utils-plugin__moduleFinder__postName"><?php echo esc_html($post['title']) ?></p>

                    <div class="mill3-wp-utils-plugin__moduleFinder__postActions">
                        <a href="<?php echo esc_url( $post['edit_link'] )?>"><?php esc_html_e('Edit', 'mill3-wp-utils') ?></a>

                        <?php if( !empty($post['view_link']) ): ?>
                        <span aria-hidden="true">|</span> 
                        <a href="<?php echo esc_url( $post['view_link'] )?>" target="_blank"><?php esc_html_e('View', 'mill3-wp-utils') ?></a>
                        <?php endif; ?>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>

    <?php if( $empty === true ): ?>
        <div class="mill3-wp-utils-plugin__moduleFinder__empty">
            <p><?php esc_html_e('This module is not used by any post.', 'mill3-wp-utils') ?></p>
        </div>
    <?php endif; ?>
</form>

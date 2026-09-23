<?php
/**
 * @var object $admin : Mill3_Wp_Utils_Admin instance
 * @var string $base_url : Base URL pointing to plugin root.
 * @var string $version : Version of the plugin currently installed.
 * @var array $releases : Collection of array('version' => string, 'blocks' => array), newest first.
 */

use Mill3_Plugins\Utils\Admin\Changelog;
?>

<div class="mill3-wp-utils-plugin__changelog">

    <div class="mill3-wp-utils-plugin__changelog__current">
        <span class="mill3-wp-utils-plugin__changelog__currentLabel"><?php esc_html_e('Installed version', 'mill3-wp-utils'); ?></span>
        <strong class="mill3-wp-utils-plugin__changelog__currentVersion"><?php echo esc_html($version) ?></strong>

        <p class="mill3-wp-utils-plugin__changelog__currentNote">
            <?php
            printf(
                /* translators: %s: link to the plugin releases page. */
                esc_html__('Updates are published on %s.', 'mill3-wp-utils'),
                '<a href="' . esc_url(MILL3_WP_UTILS_PLUGINS_API . '/releases') . '" target="_blank" rel="noopener noreferrer">Github</a>'
            );
            ?>
        </p>
    </div>

    <?php if( empty($releases) ): ?>

        <p class="mill3-wp-utils-plugin__changelog__empty"><?php esc_html_e('No changelog available.', 'mill3-wp-utils'); ?></p>

    <?php else: ?>

        <ol class="mill3-wp-utils-plugin__changelog__releases">
            <?php foreach($releases as $release): ?>
                <?php $is_current = $release['version'] === $version; ?>
            <li class="mill3-wp-utils-plugin__changelog__release <?php if( $is_current ): ?>is-current<?php endif; ?>">
                <header class="mill3-wp-utils-plugin__changelog__releaseHeader">
                    <h3 class="mill3-wp-utils-plugin__changelog__releaseVersion"><?php echo esc_html($release['version']) ?></h3>

                    <?php if( $is_current ): ?>
                    <span class="mill3-wp-utils-plugin__changelog__releaseBadge"><?php esc_html_e('Installed', 'mill3-wp-utils'); ?></span>
                    <?php endif; ?>
                </header>

                <div class="mill3-wp-utils-plugin__changelog__releaseBody">
                    <?php
                    // Blocks are rendered in the order they appear in README.txt : consecutive
                    // list items are grouped in a single <ul>, anything else becomes a <p>.
                    $in_list = false;

                    foreach($release['blocks'] as $block):
                        $is_item = $block['type'] === 'item';

                        if( $is_item && !$in_list ) { echo '<ul class="mill3-wp-utils-plugin__changelog__releaseList">'; $in_list = true; }
                        if( !$is_item && $in_list ) { echo '</ul>'; $in_list = false; }

                        // Changelog::format() escapes everything, then re-introduces only
                        // <strong>, <em>, <code> and <a href="http…">.
                        if( $is_item ) echo '<li>' . Changelog::format($block['text']) . '</li>';
                        else echo '<p>' . Changelog::format($block['text']) . '</p>';
                    endforeach;

                    if( $in_list ) echo '</ul>';
                    ?>
                </div>
            </li>
            <?php endforeach; ?>
        </ol>

    <?php endif; ?>

</div>

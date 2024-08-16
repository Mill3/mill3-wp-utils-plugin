<?php

/** 
 * @var object $admin : Mill3_Wp_Utils_Admin instance
 * @var string $header : HTML output from /admin/partials/header.php
 * @var string $breadcrumb : HTML output from /admin/partials/breadcrumb.php
 */
?>

<?php echo $header ?>
<?php echo $breadcrumb ?>
<?php echo $nav ?>

<div class="mill3-wp-utils-plugin__wrap wrap">
    <h1 class="mill3-wp-utils-plugin__adminNoticesTarget" aria-hidden="true"></h1>

    <?php echo $body ?>
</div>

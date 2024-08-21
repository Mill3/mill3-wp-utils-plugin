<?php

namespace Mill3_Plugins\Utils\Components;
use Mill3_Plugins\Utils\Components\Mill3_Wp_Utils_Component;

class Avatar extends Mill3_Wp_Utils_Component
{
    protected function init(): void {
        $this->loader->add_filter('get_avatar_url', $this, 'get_avatar', 10, 3);
        $this->loader->add_action('show_user_profile', $this, 'show_profile_picture_form');
        $this->loader->add_action('edit_user_profile', $this, 'show_profile_picture_form');
        $this->loader->add_action('profile_update', $this, 'save_avatar_meta', 10, 3);

        if( !is_admin() ) return;

        $this->loader->add_action('admin_enqueue_scripts', $this, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $this, 'enqueue_scripts');
    }

    // get avatar url from Wordpress Media Manager if defined
    public function get_avatar($url, $id_or_email, $args) {
        // if $id_or_email is not an integer or a WP_User, stop here
        if( !is_int($id_or_email) && !($id_or_email instanceof \WP_User) ) return $url;

        $id = is_int($id_or_email) ? $id_or_email : $id_or_email->ID;

        // get user_meta
        $avatar_media_id = get_user_meta($id, 'avatar', true);
        if( !$avatar_media_id ) return $url;

        // format sizing
        if( is_int($args['size']) ) $sizes = [$args['width'], $args['height']];
        else $sizes = $args['size'];
        
        // get image url
        $media = wp_get_attachment_image_src($avatar_media_id, $sizes);
        return $media ? $media[0] : $url;
    }

    // add form to preview & update avatar via Wordpress Media Manager
    public function show_profile_picture_form($profile_user) {
        $avatar = get_avatar($profile_user);
        $avatar_media_id = get_user_meta($profile_user->ID, 'avatar', true);

        wp_enqueue_media();
        ?>
        <h2><?php _e( 'Profile Picture', 'mill3-wp-utils' ); ?></h2>
        <table class="form-table" role="presentation">
            <tr class="user-avatar-wrap" data-uploader-title="<?php _e('Select Profile Picture', 'mill3-wp-utils'); ?>">
                <th><label for="avatar"><?php _e( 'Profile Picture', 'mill3-wp-utils' ); ?></label></th>
                <td>
                    <div class="avatar-preview">
                        <?php echo $avatar; ?>
                        <!-- <button type="button" class="avatar-remove-btn">X</button> -->
                    </div>

                    <input id="avatar" name="avatar" type="button" class="button" value="<?php _e('Change Profile Picture', 'mill3-wp-utils'); ?>" />
                    <input id="avatar_media_id" name="avatar_media_id" type="hidden" value="<?php echo $avatar_media_id; ?>">
                    <p class="description"><?php _e('If no profile picture is defined, Wordpress will try to get your profile image from Gravatar.', 'mill3-wp-utils') ?></p>
                </td>
            </tr>
        </table>
        <?php 
    }

    // add "avatar" data to user_meta
    public function save_avatar_meta($user_id, $old_user_data, $userdata) {
        $user_id  = (int) $user_id;
        if( !$user_id || !isset($_POST) || !isset($_POST['avatar_media_id']) ) return;
    
        $avatar_media_id = (int) $_POST['avatar_media_id'];
        if( !$avatar_media_id ) return;
    
        $success = update_user_meta($user_id, 'avatar', $avatar_media_id);
    }

    // add css to admin pages (profile.php & user-edit.php)
    public function enqueue_styles() {
        if( !$this->is_current_screen() ) return;

        wp_enqueue_style($this->plugin->get_name() . '-avatar', plugin_dir_url(__FILE__) . 'css/mill3-wp-utils-avatar.css', array(), $this->version(), 'all');
    }

    // add js to admin pages (profile.php & user-edit.php)
    public function enqueue_scripts() {
        if( !$this->is_current_screen() ) return;

        wp_enqueue_script($this->plugin->get_name() . '-avatar', plugin_dir_url( __FILE__ ) . 'js/mill3-wp-utils-avatar.js', array('jquery'), $this->plugin->get_version(), true);
    }



    // private methods
    private function is_current_screen() {
        global $pagenow;
        return $pagenow == 'profile.php' || $pagenow == 'user-edit.php';
    }



    // getters
    public static function id() : string { return 'avatar'; }
    public function version() : string { return '0.0.1'; }
    public function title() : string { return __('Avatar', 'mill3-wp-utils'); }
    public function description() : string { return __('Choose an avatar from Wordpress Media Manager.', 'mill3-wp-utils'); }
}

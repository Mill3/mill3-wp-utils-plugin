<?php

namespace Mill3_Plugins\Utils\Admin;

class HTML_Helper {
    // form sections
    public function form_header($title, $description = null, $classname = null, $attributes = null) {
        $this->load_view(MILL3_WP_UTILS_PLUGIN_DIR_PATH . 'admin/partials/form-header.php', array(
            'title' => $title, 
            'description' => $description,
            'classname' => $classname,
            'attributes' => $attributes,
        ));
    }

    public function form_action($action, $nonce = false) {
        $this->load_view(MILL3_WP_UTILS_PLUGIN_DIR_PATH . 'admin/partials/form-action.php', array(
            'action' => $action,
            'nonce' => $nonce ? $action : false,
        ));
    }

    public function form_body_open($classname = null, $attributes = null) {
        $classname = isset($classname) ? $classname : '';
        $attributes = isset($attributes) ? $attributes : array();

        echo '<div class="mill3-wp-utils-plugin__form__body ' . $classname . '" ' . implode(' ', $attributes) . '>';
    }

    public function form_body_close() { echo '</div>'; }

    public function form_footer($classname = null, $attributes = null) {
        $this->load_view(MILL3_WP_UTILS_PLUGIN_DIR_PATH . 'admin/partials/form-footer.php', array(
            'classname' => $classname,
            'attributes' => $attributes,
        ));
    }

    public function form_separator($classname = null, $attributes = null) {
        $this->load_view(MILL3_WP_UTILS_PLUGIN_DIR_PATH . 'admin/partials/form-separator.php', array(
            'classname' => $classname,
            'attributes' => $attributes,
        ));
    }


    // form inputs
    public function form_textfield($label, $name, $value = null, $description = null, $input_attrs = null, $classname = null, $attributes = null) {
        $this->load_view(MILL3_WP_UTILS_PLUGIN_DIR_PATH . 'admin/partials/textfield.php', array(
            'label' => $label,
            'name' => $name,
            'value' => $value,
            'description' => $description,
            'input_attrs' => $input_attrs,
            'classname' => $classname,
            'attributes' => $attributes,
        ));
    }

    public function form_textarea($label, $name, $value = null, $description = null, $input_attrs = null, $classname = null, $attributes = null) {
        $this->load_view(MILL3_WP_UTILS_PLUGIN_DIR_PATH . 'admin/partials/textarea.php', array(
            'label' => $label,
            'name' => $name,
            'value' => $value,
            'description' => $description,
            'input_attrs' => $input_attrs,
            'classname' => $classname,
            'attributes' => $attributes,
        ));
    }

    public function form_select($label, $name, $options, $value = null, $description = null, $input_attrs = null, $classname = null, $attributes = null ) {
        $this->load_view(MILL3_WP_UTILS_PLUGIN_DIR_PATH . 'admin/partials/select.php', array(
            'label' => $label,
            'name' => $name,
            'options' => $options,
            'value' => $value,
            'description' => $description,
            'input_attrs' => $input_attrs,
            'classname' => $classname,
            'attributes' => $attributes,
        ));
    }
    

    // private methods
    private function load_view($filepath, $data = array(), $print = true) {
        $output = NULL;

        if( file_exists($filepath) ) {
            // add universal data
            $data['base_url'] = plugin_dir_url(MILL3_WP_UTILS_PLUGIN_FILE);

            // Extract the variables to a local namespace
            extract($data);

            // Start output buffering
            ob_start();

            // Include the template file
            include $filepath;

            // End buffering and return its contents
            $output = ob_get_clean();
        }

        if( $print ) print $output;
        else return $output;
    }
}

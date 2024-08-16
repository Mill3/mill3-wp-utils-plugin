<?php
/** 
 * @var object $admin : Mill3_Wp_Utils_Admin instance
 * @var string $base_url : Base URL pointing to plugin root.
 * @var object $html_helper : \Mill3_Plugins\Utils\Admin\HTML_Helper instance.
 * 
 * @var object $openai_api_key : { value, errors }
 * @var object $chatgpt_prompt : { value, errors }
 */
?>
<form 
    id="ai-image-alt" 
    class="mill3-wp-utils-plugin__form"
    action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
    method="POST"
>

    <?php $html_helper->form_header($component->title(), $component->description()) ?>

    <?php $html_helper->form_body_open() ?>
    
        <?php $html_helper->form_textfield(
            __('OpenAI API Key', 'mill3-wp-utils'), 
            'openai_api_key', 
            $openai_api_key['value'], 
            __('You can find your Secret API key on the <a href="https://platform.openai.com/api-keys" target="_blank">API key page</a>.', 'mill3-wp-utils')
        ) ?>

        <?php $html_helper->form_separator() ?>

        <?php $html_helper->form_textarea(
            'ChatGPT Prompt', 
            'chatgpt_prompt', 
            $chatgpt_prompt['value'], 
            __('Question you would you like to ask ChatGPT about your image. Leave empty if you want to use default value.', 'mill3-wp-utils'),
            array('placeholder="Describe this image in less than 60 words."')
        ) ?>

    <?php $html_helper->form_body_close() ?>
    <?php $html_helper->form_footer( $component->id(), $component->id() ) ?>

</form>

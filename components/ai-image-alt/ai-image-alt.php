<?php

namespace Mill3_Plugins\Utils\Components;
use Mill3_Plugins\Utils\Components\Mill3_Wp_Utils_Component;

class Ai_Image_Alt extends Mill3_Wp_Utils_Component
{
    private static $DEFAULT_OPTIONS = array(
        'openai_api_key' => '', 
        'chatgpt_prompt' => ''
    );
    private static $OPTION_NAME = 'ai-image-alt';

    private $options = array();

    protected function init() : void {
        $this->options = maybe_unserialize( get_option( $this->plugin->get_option_name(self::$OPTION_NAME), self::$DEFAULT_OPTIONS ) );

        $this->loader->add_action('admin_post_' . $this->id(), $this, 'validate_admin_post');

        // if OpenAI API Key isn't defined, stop here
        if( !array_key_exists('openai_api_key', $this->options) || empty($this->options['openai_api_key'])) return;

        $this->loader->add_action('admin_enqueue_scripts', $this, 'enqueue_styles');
        $this->loader->add_action('print_media_templates', $this, 'print_media_templates');
        $this->loader->add_action('wp_ajax_mill3_generate_image_alt', $this, 'generate_image_alt');
    }

    public static function uninstall($plugin) : void {
        delete_option( $plugin::get_option_name(self::$OPTION_NAME) );
    }
    public function enabled($enabled = null) : bool {
        if( $enabled === true ) {
            add_option(
                $this->plugin->get_option_name(self::$OPTION_NAME), 
                self::$DEFAULT_OPTIONS, 
                null, 
                false
            );
        }

        return parent::enabled($enabled);
    }

    public function enqueue_styles() {
      wp_enqueue_style($this->plugin->get_name() . '-ai-image-alt', plugin_dir_url(__FILE__) . 'css/mill3-wp-utils-ai-image-alt.css', array(), $this->version(), 'all');
    }

    public function print_media_templates() {
        ?>
        <script type="text/javascript" id="mill3-images-alt-text-ai">
            jQuery( document ).ready(function() {
                <?php if( function_exists('pll_the_languages') ): ?>
                    const pll_the_languages = JSON.parse('<?php echo json_encode( pll_the_languages(array('hide_if_empty' => false, 'hide_current' => false, 'raw' => true)) ) ?>');
                    const pll_default_language = "<?php echo esc_attr( pll_default_language('slug') ) ?>";
                <?php else: ?>
                    const pll_the_languages = {};
                    const pll_default_language = '<?php echo esc_attr( get_locale() ) ?>';
                <?php endif; ?>

                const labels = {
                    button: "<?php esc_attr_e('Generate Alt with AI', 'mill3-wp-utils') ?>",
                    failed: "<?php esc_attr_e('Failed', 'mill3-wp-utils') ?>",
                    waiting: "<?php esc_attr_e('Generating…', 'mill3-wp-utils') ?>",
                };

                const getButton = function() {
                    return '<button class="mill3-generate-alt-button">' + labels.button + '</button>';
                };
                const getTemplate = function(template, view, isTwoColumn = false) {
                    const html = wp.media.template(template)(view);
                    const dom = document.createElement('div');
                    dom.innerHTML = html;

                    if ( !dom.querySelector('#alt-text-description') ) return html;

                    // Add it to the beginning of #alt-text-description, along with a line break.
                    const altText = dom.querySelector('#alt-text-description');
                    altText.innerHTML = getButton() + '<br />' + altText.innerHTML;

                    return dom.innerHTML;
                };
                const updateButton = function(btn, enabled, label, failure) {
                    if( label ) btn.innerHTML = label;

                    btn.disabled = !enabled;
                    btn.classList[failure ? 'add' : 'remove']('failed');
                };

                const generateAltTextForImage = function(event) {
                    if( event ) {
                        event.preventDefault();
                        event.stopPropagation();
                    }

                    // get media ID
                    const model = this.model;
                    const mediaID = model.attributes.id;
                    const generateAltTextButton = event.currentTarget;

                    // stop here if we can't find Attachment ID
                    if( !mediaID ) return;

                    // Disable the button while generating alt text
                    updateButton(generateAltTextButton, false, labels.waiting);
                    
                    // request alt text generator
                    altTextGenerator(mediaID)
                        .then(function(response) {
                            // if response is not for this media, stop here
                            if( response.mediaID != mediaID ) return;

                            // Successfully generated alt text, now update and save the alt text field
                            const translations = JSON.parse(response.altText);
                            const altText = translations[pll_default_language];

                            // update textfields
                            document
                                .querySelectorAll('#attachment-details-two-column-alt-text, #attachment-details-alt-text, #attachment-details-two-column-alt-text')
                                .forEach(function(textfield) { textfield.value = altText; });

                            // update translations
                            let compatItem;

                            Object.values(pll_the_languages).forEach(function(language) {
                                if( language.slug === pll_default_language ) return;

                                const value = translations[language.slug];
                                const textfield = document.querySelector(`#attachments-${mediaID}-alt_text_${language.slug}`);

                                if( textfield ) {
                                    textfield.value = value;
                                    compatItem = textfield;
                                }
                            });

                            // save model
                            model.set('alt', altText);

                            if( model.save ) model.save();
                            if( compatItem ) jQuery(compatItem).trigger('change');

                            // reset button default label
                            updateButton(generateAltTextButton, true, labels.button);
                        })
                        .catch(function(error) {
                            alert(error);

                            // Update the button to show failure
                            updateButton(generateAltTextButton, false, labels.failed, true);

                            // Wait 3 seconds then reset button to original state
                            setTimeout( function() {
                                updateButton(generateAltTextButton, true, labels.button);
                            }, 3000 );
                        });
                };
                const injectButton = function(destination) {
                    destination.insertAdjacentHTML('afterend', '<br>' + getButton());

                    const button = destination.parentElement.querySelector('button.mill3-generate-alt-button');
                    button.addEventListener('click', function(event) {
                    if( event ) {
                        event.preventDefault();
                        event.stopPropagation();
                    }

                    const generateAltTextButton = event.currentTarget;

                    // Disable the button while generating alt text
                    updateButton(generateAltTextButton, false, labels.waiting);

                    // request alt text generator
                    altTextGenerator(attachmentID)
                        .then(function(response) {
                            // if response is not for this media, stop here
                            if( response.mediaID != attachmentID ) return;

                            // Successfully generated alt text, now update and save the alt text field
                            const translations = JSON.parse(response.altText);
                            const altText = translations[pll_default_language];

                            // update textfields
                            altTextField.value = altText;

                            Object.values(pll_the_languages).forEach(function(language) {
                                if( language.slug === pll_default_language ) return;

                                const value = translations[language.slug];
                                const textfield = document.querySelector(`#attachments-${attachmentID}-alt_text_${language.slug}`);

                                if( textfield ) textfield.value = value;
                            });

                            // reset button default label
                            updateButton(generateAltTextButton, true, labels.button);
                        })
                        .catch(function(error) {
                            alert(error);

                            // Update the button to show failure
                            updateButton(generateAltTextButton, false, labels.failed, true);

                            // Wait 3 seconds then reset button to original state
                            setTimeout( function() {
                                updateButton(generateAltTextButton, true, labels.button);
                            }, 3000 );
                        });
                    });
                };
                const altTextGenerator = function(mediaID) {
                    return new Promise(function(resolve, reject) {
                        
                        const url  = "<?php echo admin_url('admin-ajax.php') ?>";
                        const data = new FormData();
                        data.append('action', 'mill3_generate_image_alt');
                        data.append('mediaID', mediaID);
                        
                        const options = { method: 'post', body: data };

                        fetch(url, options)
                            .then(function(response) {
                                if( !response.ok ) {
                                    return response.json().then(function(body) { throw new Error(body.data); });
                                    throw new Error(response);
                                }
                                else return response.json();
                            })
                            .then(function(response) { resolve(response); })
                            .catch(function(error) { reject(error); });
                    });
                };

                const events = Object.assign(wp.media.view.ImageDetails.prototype.events, { 'click .mill3-generate-alt-button': generateAltTextForImage });
                const altTextField = document.querySelector('#attachment_alt');

                let attachmentID = document.querySelector('input[type="hidden"]#post_ID');
                if( attachmentID ) attachmentID = attachmentID.value;

                // Two Column Attchment Details modal. Add Generate Button in Media library Grid mode.
                if( wp.media.view.Attachment.Details.TwoColumn ) {
                    wp.media.view.Attachment.Details.TwoColumn = wp.media.view.Attachment.Details.TwoColumn.extend({
                        template: function( view ) {
                            //return getTemplate( isTwoColumn ? 'attachment-details-two-column' : 'image-details', view, true );
                            return getTemplate( 'attachment-details-two-column', view, true );
                        },
                        events: events
                    });
                }

                // Attachment Details modal. Add Generate Button in Block Editor Attachment details modal.
                wp.media.view.Attachment.Details = wp.media.view.Attachment.Details.extend({
                    template: function(view) { return getTemplate('attachment-details', view); },
                    events: events
                });

                // If there is no #attachment_alt field OR no #post_ID field, then we don't need to do anything.
                if( altTextField && attachmentID ) injectButton(altTextField);
            });
        </script>
        <?php
    }

    public function generate_image_alt() {
        if( !array_key_exists('openai_api_key', $this->options) ) {
            wp_send_json_error( __('OpenAI API KEY is not defined.', 'mill3-wp-utils'), 401 );
            wp_die();
        }
    
        $mediaID = intval($_POST['mediaID']);
    
        if( !$mediaID ) {
            wp_send_json_error( __('mediaID is not defined.', 'mill3-wp-utils'), 400 );
            wp_die();
        }
    
        // if attachment isn't an image, stop here
        if( !wp_attachment_is_image( $mediaID ) ) {
            wp_send_json_error( __("Attachment is not an image.", 'mill3-wp-utils'), 400 );
            wp_die();
        }
    
        // get 512x512 image source
        $image_url = wp_get_attachment_image_src($mediaID, 'open-ai-vision');
        if( !$image_url ) {
            wp_send_json_error( __("Invalid image ID.", 'mill3-wp-utils'), 400 );
            wp_die();
        }
    
        // get image path
        $original_image = wp_get_original_image_path($mediaID, true);
        $img_infos = pathinfo($image_url[0]);
        $image_url = pathinfo($original_image, PATHINFO_DIRNAME) . "/" . $img_infos['filename'] . "." . $img_infos["extension"];
        $image_url = \Mill3WP\Utils\ImageToBase64($image_url);
    
        // create multilang instructions
        $lang_slugs = array();
        $lang_names = array();
    
        if( function_exists('pll_the_languages') ) {
            $languages = pll_the_languages(array('hide_if_empty' => false, 'hide_current' => false, 'raw' => true));
            foreach($languages as $slug => $language) {
                $lang_slugs[] = $slug;
                $lang_names[] = lcfirst($language['name']);
            }
    
        } else {
            require_once ABSPATH . 'wp-admin/includes/translation-install.php';
    
            $locale = get_locale();
            $translations = wp_get_available_translations();
            $lang_name = array_key_exists($locale, $translations) ? $translations[$locale]["english_name"] : "English";
    
            $lang_slugs[] = $locale;
            $lang_names[] = lcfirst( $lang_name );
        }
    
        if( count($lang_names) > 1 ) {
            $last_lang_name = array_pop($lang_names);
            $lang_names = implode(", ", $lang_names) . " and " . $last_lang_name;
        }
        else $lang_names = implode(", ", $lang_names);
    
        // get ChatGPT prompt from options
        $prompt = trim( array_key_exists('chatgpt_prompt', $this->options) ? $this->options['chatgpt_prompt'] : "" );

        // if prompt is empty, set default value
        if( empty($prompt ) ) $prompt = "Describe this image in less than 60 words.";

        // create instructions set
        $instructions = "Return text in " . $lang_names . " formatted with a JSON structure like this {" . implode(", ", $lang_slugs) . "}.";
    
        $data = array(
            "model" => "gpt-4o-mini",
            "messages" => array(
                array(
                    "role" => "user",
                    "content" => array(
                        array(
                            "type" => "text", 
                            "text" => $prompt . " " . $instructions,
                        ),
                        array(
                            "type" => "image_url",
                            "image_url" => array(
                                "url" => $image_url,
                                "detail" => "low",
                            ),
                        ),
                    ),
                ),
            ),
            "response_format" => array("type" => "json_object"),
            "max_tokens" => 300,
        );
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = "Authorization: Bearer " . $this->options['openai_api_key'];
    
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
        $response = curl_exec($ch);
    
        if( curl_errno($ch) ) {
            wp_send_json_error( curl_error($ch), 500 );
            wp_die();
        }
    
        curl_close($ch);
    
        $response = json_decode($response, true);

        if( array_key_exists('error', $response) ) {
            wp_send_json_error( $response["error"]["message"], 401 );
            wp_die();
        }

        $output = array(
            "mediaID" => $mediaID,
            "altText" => $response["choices"][0]["message"]["content"],
        );
    
        wp_send_json($output);
        wp_die();
    }

    public function admin_page() : void {
        if( $this->enabled() ) {
            $this->admin->render_template( $this->title(), 'admin/views/ai-image-alt.php', array(
                'component' => $this,
                'openai_api_key' => array('value' => $this->options['openai_api_key'], 'errors' => null),
                'chatgpt_prompt' => array('value' => $this->options['chatgpt_prompt'], 'errors' => null),
            ));
        }
        else $this->show_access_restricted_page();      
    }

    public function validate_admin_post() : void {
        // if component is not enabled, stop here
        if( !$this->enabled() ) {
            _doing_it_wrong( __FUNCTION__, __('AI Image Alt is not enabled.', 'mill3-wp-utils'), $this->version() );
            wp_die();
        }

        // make sure request came from our admin page
        check_admin_referer( $this->id() );

        // sanitize the input
        $openai_api_key = sanitize_text_field( $_POST['openai_api_key'] );
        $chatgpt_prompt = sanitize_textarea_field( $_POST['chatgpt_prompt'] );

        $this->options['openai_api_key'] = $openai_api_key;
        $this->options['chatgpt_prompt'] = $chatgpt_prompt;

        // update database
        update_option( $this->plugin->get_option_name(self::$OPTION_NAME), $this->options );

        // redirect to admin page with admin notices
        $this->form_redirect(self::NOTICE_SUCCESS);
    }


    // getters
    public static function id() : string { return 'ai-image-alt'; }
    public function version() : string { return '0.0.1'; }
    public function title() : string { return __('AI Image Alt', 'mill3-wp-utils'); }
    public function description() : ?string { return __('Get sophisticated AI suggestions for image alternative text. Works with all installed languages.', 'mill3-wp-utils'); }
}

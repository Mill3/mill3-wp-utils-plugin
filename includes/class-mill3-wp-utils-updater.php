<?php


namespace Mill3_Plugins\Utils\Updater;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

class Mill3_Wp_Utils_Updater
{

    private $api_url;

    private $plugin_file;

    private $plugin_slug;

    private $updater;

    function __construct()
    {
        $this->api_url = MILL3_WP_UTILS_PLUGINS_API;
        $this->plugin_file = MILL3_WP_UTILS_PLUGIN_FILE;
        $this->plugin_slug = MILL3_WP_UTILS_PLUGIN_SLUG;

        $this->updater = PucFactory::buildUpdateChecker(
            $this->api_url,
            $this->plugin_file,
            $this->plugin_slug
        );
        $this->updater->setBranch('master');
    }

    public function check_for_update($transient)
    {
        // Only proceed if the transient contains the 'checked' array
        if (empty($transient->checked)) {
            return $transient;
        }

        $update = $this->updater->getUpdate();

        if ($update) {
            $transient->response[$update->filename] = $update->toWpFormat();
        } else {
            // No update available, get current plugin info.
            $update = $this->updater->getUpdateState()->getUpdate();

            // Adding the plugin info to the `no_update` property is required
            // for the enable/disable auto-update links to appear correctly in the UI.
            if ($update) {
                $transient->no_update[$update->filename] = $update;
            }
        }

        return $transient;
    }
}

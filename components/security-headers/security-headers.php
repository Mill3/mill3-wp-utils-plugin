<?php

namespace Mill3_Plugins\Utils\Components;
use Mill3_Plugins\Utils\Components\Mill3_Wp_Utils_Component;

class Security_headers extends Mill3_Wp_Utils_Component
{
    protected function init(): void {
        $this->loader->add_action('send_headers', $this, 'add_headers');
    }

    /**
     * Add security headers to the response : only allow same origin and mill3.studio to frame this site
     */
    public function add_headers()
    {
        // header("Mill3-Wp-Utils-Version: " . $this->version()); // add version to headers, for debugging purposes?
        header("X-Frame-Options: SAMEORIGIN");
        header("Content-Security-Policy: frame-ancestors 'self' mill3.studio");
    }

    // getters
    public static function id() : string { return 'security-headers'; }
    public function version() : string { return '0.0.1'; }
    public function title() : string { return 'Security Headers'; }
    public function description() : string { return 'This module allows us to showcase your awesome project in our website. If you ❤️ us, you should keep this active.'; }
}

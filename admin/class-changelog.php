<?php

namespace Mill3_Plugins\Utils\Admin;

/**
 * Reads the release history out of README.txt.
 *
 * README.txt stays the single source of truth for the changelog : it is what gets
 * written when a version is published (see "Publishing a new version of the plugin"),
 * so the Changelog admin page simply renders it instead of duplicating it.
 *
 * Expected format, the WordPress plugin readme standard :
 *
 *     == Changelog ==
 *
 *     = 0.1.7 =
 *
 *     * A change
 *     * Another change
 *
 *     = 0.1.6 =
 *
 *     Some introduction paragraph.
 *
 *     - A change written with a dash
 *
 * Parsing stops at the next "== Section ==" heading.
 */
class Changelog {

    /** @var string Absolute path of the file holding the changelog. */
    private $filepath;

    /** @var array|null Parsed releases, cached for the request. */
    private $releases = null;

    public function __construct($filepath = null) {
        $this->filepath = $filepath ?: MILL3_WP_UTILS_PLUGIN_DIR_PATH . 'README.txt';
    }

    /**
     * Returns every release found, newest first (file order is preserved).
     *
     * @return array List of array('version' => string, 'blocks' => array) where each block
     *               is array('type' => 'item'|'paragraph', 'text' => string).
     */
    public function get_releases() : array {
        if( is_array($this->releases) ) return $this->releases;

        $this->releases = array();

        if( !is_readable($this->filepath) ) return $this->releases;

        $contents = file_get_contents($this->filepath);

        if( $contents === false ) return $this->releases;

        // isolate the "== Changelog ==" section, up to the next "== … ==" heading
        if( !preg_match('/^==\s*Changelog\s*==\s*$(.*?)(?=^==\s*[^=]+\s*==\s*$|\z)/ms', $contents, $matches) ) return $this->releases;

        $current = null;

        foreach( preg_split('/\R/', $matches[1]) as $line ) {
            $line = trim($line);

            if( $line === '' ) continue;

            // "= 0.1.7 =" starts a new release
            if( preg_match('/^=\s*(.+?)\s*=$/', $line, $version) ) {
                if( $current ) $this->releases[] = $current;

                $current = array('version' => $version[1], 'blocks' => array());
                continue;
            }

            // ignore anything appearing before the first version heading
            if( !$current ) continue;

            // "* change" or "- change" is a list item, everything else is a paragraph
            if( preg_match('/^[*-]\s+(.*)$/', $line, $item) ) $current['blocks'][] = array('type' => 'item', 'text' => $item[1]);
            else $current['blocks'][] = array('type' => 'paragraph', 'text' => $line);
        }

        if( $current ) $this->releases[] = $current;

        return $this->releases;
    }

    /**
     * Returns the release matching a version number, or null.
     *
     * @param string $version Version to look for, ie: "0.1.7".
     * @return array|null
     */
    public function get_release($version) : ?array {
        foreach($this->get_releases() as $release) {
            if( $release['version'] === $version ) return $release;
        }

        return null;
    }

    /**
     * Renders the small subset of Markdown used in README.txt as safe HTML.
     *
     * Everything is escaped first, so the only tags that can come out are the ones
     * produced here : <strong>, <em>, <code> and <a> pointing at an http(s) URL.
     *
     * @param string $text Raw line from README.txt.
     * @return string
     */
    public static function format($text) : string {
        $text = esc_html($text);

        // [label](https://example.com)
        $text = preg_replace_callback(
            '/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/',
            function($m) { return '<a href="' . esc_url($m[2]) . '" target="_blank" rel="noopener noreferrer">' . $m[1] . '</a>'; },
            $text
        );

        $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
        $text = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/(?<![*\w])\*([^*]+)\*(?!\w)/', '<em>$1</em>', $text);

        return $text;
    }
}

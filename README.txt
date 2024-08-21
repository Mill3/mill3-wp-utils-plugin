=== MILL3 WP Utils ===
Contributors: MILL3 Studio
Donate link: https://mill3.studio/
Tags: custom
Requires at least: 6.3.0
Tested up to: 6.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

MILL3 WP utils, includes Gutenberg editor sidebar resizer.

== Description ==

MILL3 WP utils, includes Gutenberg editor sidebar resizer.

== How to update translations files (.pot, .mo, .po) ==

To update the main .pot file (.pot = PO Template file), open a Terminal window at the root of your website. (ex: /Desktop/wordpress-installation/).

```sh 
docker-compose run wp-cli i18n make-pot wp-content/plugins/mill3-wp-utils-plugin wp-content/plugins/mill3-wp-utils-plugin/languages/mill3-wp-utils.pot --domain="mill3-wp-utils" --exclude="wp-content/plugins/mill3-wp-utils-plugin/vendor/" 
```

To update a particular language, you need to install POEdit. 
Then, go to */wp-content/plugins/mill3-wp-utils-plugin/languages/* and double-click on the .po file or the language you want to edit translations. 
In POEdit, go to the Translation Menu -> Update from POT file... and choose the .pot file in */wp-content/plugins/mill3-wp-utils-plugin/languages/*.  
Edit your translations and hit Save.  

To translate this plugin into another language, you need to open */wp-content/plugins/mill3-wp-utils-plugin/languages/mill3-wp-utils.pot* in POEdit and 
click on "Create new translation" at the bottom/left of this window. 

== Changelog ==

= 0.0.4 =
* First release with Sidebar utils and security headers

= 0.0.3.x =
* Self-update class era

= 0.0.2 =
* Gutenberg Sidebar : added max-width CSS rule on container

= 0.0.1 =
* Initial tests

== TODO ==

[X] class-mill3-wp-utils-updater.php : use plugin-update-checker library https://github.com/YahnisElsts/plugin-update-checker
[x] Built admin interface for settings and enabling components

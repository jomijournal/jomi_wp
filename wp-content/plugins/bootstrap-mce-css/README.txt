=== Bootstrap MCE CSS ===
Contributors: davewarfel, escapecreative
Tags: Bootstrap, TinyMCE, editor style, CSS
Requires at least: 3.0
Tested up to: 3.8-RC1
Stable tag: 0.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds a modified version of the Bootstrap CSS to the MCE editor, for developers who like to add their own Bootstrap code to the editor.

== Description ==

This plugin adds a modified version of the Bootstrap CSS to the MCE editor, for developers who like to add their own Bootstrap code to the editor, without shortcodes. You still need to add the Bootstrap CSS & JS files to the front-end of your website.

*Using Bootstrap version 3.0.3.*

= Supports the following Bootstrap features =

* Grid Layouts (with some caveats)
* Headings (h1-h6)
* Buttons
* Labels
* Badges
* Alerts
* List Groups
* Panels
* Wells
* Media Groups

Please submit issues & feature requests on [github](https://github.com/davewarfel/Bootstrap-MCE-CSS/issues).

*This plugin might work on WordPress versions back to 2.1, but I couldn't find when the `mce_css` filter was introduced.*

== Installation ==

There are no settings to configure. Just install the plugin & Bootstrap styles will be applied to the MCE editor on all post edit screens.

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'bootstrap mce css'
3. Click 'Install Now'
4. Activate the plugin on the plugin dashboard

= Manually Upload =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `bootstrap-mce-css.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the plugin dashboard

== Frequently Asked Questions ==

= What if I add an editor-style.css file to my theme directory? =

The Bootstrap CSS is loaded after the editor-style.css file in your active theme directory. But since Bootstrap's CSS is not very specific, just add a class of `.mceContentBody` before your selectors, and the editor-style.css rules will take precedence.

= What's up with columns & grids? =

Because Bootstrap 3 is responsive by default, and offers multiple ways to setup the grid system (xs, sm, md, lg), the styles in your MCE editor will vary based on the screen size. Try fullscreen editing mode to see your grid system take effect.

= Why can't I edit buttons when I switch back to "Visual" mode? =

Because of the way browsers handle the `<button>` element, editing in "Visual" mode does not work. Try using an `<a>` tag with a class of `btn`.

= Does this add Bootstrap to my website? =

No. You still need to include the Bootstrap CSS & Javascript files to the front-end of your website. Many themes were built on Bootstrap, and already include these files. There are also [plugins](http://wordpress.org/plugins/wordpress-bootstrap-css/) that will add these files to your site.

This plugin only adds a single, minified Bootstrap CSS file to the pages in your WordPress admin that include a WYSIWYG editor.

= Where are the shortcodes? =

There are no shortcodes included with this plugin. This plugin only adds CSS to the MCE editor, for those who like to code their own Bootstrap elements in the "Text" view of the editor. If you're looking for shortcodes, or MCE buttons to add Bootstrap elements, try one of these plugins:

* [Easy Bootstrap Shortcodes](http://wordpress.org/plugins/easy-bootstrap-shortcodes/)
* [Bootstrap MCE Elements](http://wordpress.org/plugins/bootstrap-mce-elements/)
* [Bootstrap Shortcodes](http://wordpress.org/plugins/bootstrap-shortcodes/)
* [Bootstrap Buttons](http://wordpress.org/plugins/bootstrap-buttons/)

= Why don't Glyphicons works? =

The standard way of using glyphicons is by adding an empty span element with CSS classes. WordPress strips out empty elements, so this plugin does not support Glyphicons. But I'm sure there's a filter you could add that would stop removing empty elements from the MCE editor.

= What parts of the Bootstrap CSS changed? =

* Removed `cursor: pointer;` from several elements (buttons, labels, badges)
* Removed `user-select: none;` from buttons, so users can edit text inside
* Removed `margin: 0;` on `body` element
* Added `max-width` values to `.wp-caption` and `img` elements, to eliminate horizontal scrolling when adding large images to the WYSIWYG

== Screenshots ==

1. Headings & Buttons
2. Alerts & Progress Bars
3. List Groups
4. Panels & Wells
5. Fullscreen editing mode, with the grid system in effect
6. These CSS components are the ones that are included with this plugin. 

== Changelog ==

= 0.1.1 =
* 12/10/2013
* Removed `margin:0;` from body so that default padding persists
* Added `max-width` values for `.wp-caption` and images (eliminate horizontal scrolling when large images are added to the WYSIWYG)

= 0.1 =
* 12/7/2013
* Initial release

== Developers ==

View the code & contribute on [github](https://github.com/davewarfel/Bootstrap-MCE-CSS)

== Upgrade Notice ==

= 0.1.1 =
Minor CSS updates to fix body padding & horizontal scrolling due to large images.
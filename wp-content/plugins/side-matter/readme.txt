=== Side Matter ===
Contributors: setzer
Tags: academic, annotate, annotation, annotations, bibliography, bibliographic, citation, citations, cite, commentary, endnote, endnotes, footnote, footnotes, margin, marginal, matter, note, notes, ref, reference, references, scholar, scholarship, shortcode, side, sidebar, sidenote, sidenotes, widget
Requires at least: 3.0
Tested up to: 3.8
Stable tag: 1.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Turns footnotes into sidenotes, magically aligning each note in the sidebar next to its corresponding reference in the text.

== Description ==

Side Matter turns footnotes into sidenotes, magically aligning each note in the sidebar next to its corresponding reference in the text. Unlike hyperlinked footnotes, sidenotes don't require jumping down the page to follow up on each reference; instead, they perch humbly and accessibly beside the material to which they refer.

To use, place the Side Matter widget in your sidebar, then enclose sidenote text in a page or post using the `[ref]` shortcode, like so:

    Here's the text to annotate.[ref]Note text goes here.[/ref]

To change default settings, use the Side Matter options page linked under the Appearance menu on your admin screen.

== Installation ==

1. Upload the `side-matter` directory to `/wp-content/plugins`.
2. Activate Side Matter using the Plugins screen.
3. Place the Side Matter widget in your sidebar using the Widgets screen.
4. Use the `[ref]` shortcode to generate notes in posts and pages, like so: `[ref]Note text goes here.[/ref]`

To change default settings, use the Side Matter options page linked under the Appearance menu on your admin screen.

For information on changing the appearance of Side Matter elements with CSS, see [Other Notes](http://wordpress.org/extend/plugins/side-matter/other_notes/).

== Frequently Asked Questions ==

= Where is the options menu? =

As of version 1.4, the Side Matter options menu has its own settings page. It can be accessed from the Side Matter link under the Appearance menu on your admin screen.

= My notes don't appear. =

Many themes are one-column by design and do not include a sidebar. Without a sidebar, Side Matter has no place to put your notes. Make sure that your theme includes a sidebar and that you've placed the Side Matter widget within that sidebar.

= My notes just sit at the top of the sidebar. =

This usually happens because of a conflict with your theme or another plugin. Caching/minification plugins are a common culprit; try adding `/wp-content/side-matter/js/side-matter.js` to your caching plugin's exclusion list if it's interfering with sidenote display.

Theme conflicts can be difficult to pin down, but are most often caused by some idiosyncratic bit of theme CSS or JS. (For example, the theme [Twenty Eleven](http://wordpress.org/themes/twentyeleven) can be made to work with Side Matter by adding [a few lines of CSS](http://wordpress.org/support/topic/sidenotes-not-aligning).)

= Why doesn't the plugin work with my theme? =

Not all themes are built to incorporate a plugin like Side Matter. It works well with most base themes that include a sidebar, but it isn't guaranteed to display notes perfectly under all themes. A little tinkering with note offset or CSS will fix most problems; in other cases, consult your theme's developer.

= My notes appear at a vertical offset. =

Some themes mysteriously cause sidenotes to appear at an offset from their corresponding references in the text. As a workaround for this problem, Side Matter's options menu includes a field for arbitrarily adjusting your notes' vertical offset.

= Can sidenotes be displayed without using the widget? =

Yes. Use the custom action `side_matter_list_notes` in your sidebar template, as seen below:

    <?php
        do_action( 'side_matter_list_notes' );
    ?>

== Screenshots ==

1. Side Matter in action.

2. Default settings may be changed using Side Matter's options menu.

== Changelog ==

= 1.4 =
* Fixed a plugin activation error reported under WP 3.8.
* Made improvements to options menu UI.
* Options menu has been moved from the Reading Settings screen. It now has its own settings page under the Appearance menu.
* Plugin now processes shortcodes enclosed within the `[ref]` shortcode, such as `[video]` or `[gallery]`.

= 1.3 =
* A title heading may now be added to the Side Matter widget using the Widgets admin screen.
* Plugin no longer generates an empty `ol` element on pages without notes.
* Plugin now removes its widget options field from the database when uninstalled.
* Widget container markup now follows WordPress convention. This breaks the old `div.side-matter-widget` CSS class selector; use `.widget_side_matter` instead.

= 1.2 =
* Added a `[ref]` quicktag button to the post editor.
* Fixed some odd behavior within the sidenote-positioning loop.
* Fixed two options menu UI bugs from the previous update.
* Sanitization of reference figures' `title` attribute has been improved.
* Using multiple instances of the Side Matter widget no longer causes `id` conflicts between notes.

= 1.1 =
* Added support for Hiragana and Katakana figure sets.
* Added localization domain path to plugin header and appended translation notes to some otherwise cryptic strings.
* Made various minor fixes to improve appearance and performance.

= 1.0 =
* Added support for Armenian, Georgian, Greek, and Hebrew numeral formats, as well as the option to hide numeral figures entirely.
* Fixed two IE-specific bugs related to proper display of `title` attributes and list numerals.
* Made various minor adjustments and fixes for appearance and performance.
* Numbered classes have been removed.
* Plugin is now localization-ready.

= 0.9 =
* Added a preview field to the options menu.
* Added an option to display reference figures in Latin alphabet and Roman numeral formats.
* Added an option to set inline colors for Side Matter elements.
* Reference numeral `a` tags have been given a `title` attribute for accessibility.
* Removed deprecated element classes `side-matter-ol` and `side-matter-li`. Numbered classes, e.g. `side-matter-sup-6`, are now deprecated.
* Responsive positioning and fade effects are now turned off by default to spare inexperienced users the script load.
* Side Matter's options menu is now linked from its entry on the Installed Plugins screen.

= 0.8 =
* Added an option to display sidenotes on selected page types.
* Plugin documentation now includes a guide to [styling Side Matter elements with CSS](http://wordpress.org/extend/plugins/side-matter/other_notes/).
* Reduced specificity for CSS defaults in `side-matter.css`, allowing them to be more easily superseded by user CSS.
* Removed the `a` anchor elements within sidenotes to correct a stubborn layout issue. Reference numerals now link to sidenotes via `li id`.
* Side Matter now erases its options field from the database upon deletion.
* Widget admin panel now links to plugin options menu on the Reading Settings screen.

= 0.7 =
* Added an options menu to the Reading Settings screen.
* Made various tweaks for security, performance, and cross-browser compatibility.
* Paragraphs within sidenotes are now properly wrapped in `p` tags by WordPress.
* Replaced sidenote `span` tags with `div` tags to correct a display problem in some browsers.
* Sidenotes now employ jQuery fade effects for smoother transitions upon window load, resize, and zoom.

= 0.6 =
* Expanded plugin documentation.
* Rewrote `side-matter.php`, simplifying and consolidating code for future development.
* Sidenote numerals can now be styled separately from sidenote text.
* Streamlined CSS classes and jQuery selectors.
* Widget markup now better follows WordPress convention.

= 0.5 =
* Fixed a class instantiation error that was preventing plugin activation for some users.

= 0.4 =
* Initial release.

== Upgrade Notice ==

= 1.4 =
As of this release, Side Matter's options menu has been moved from the Reading Settings screen to its own page under the Appearance menu.

== Styling Side Matter with CSS ==

Changing the appearance of Side Matter elements—for example, editing your notes' typeface or indentation—requires using CSS. The simplest way to go about this is to install a custom CSS plugin that will preserve your rules even when Side Matter or your theme is updated. ([Simple Custom CSS](http://wordpress.org/plugins/simple-custom-css/) is a good example.)

Side Matter comes with a set of built-in class selectors. As an example, all sidenote and reference elements may be styled at once using the class `side-matter`. The following CSS will render all Side Matter elements in blue serif text:

    .side-matter {
        color: blue;
        font-family: serif;
    }

Notes and figures may be formatted with greater precision using element-specific class selectors. For example, the following CSS will render reference and list numerals in green and sidenote text in black:

    a.side-matter-ref, ol.side-matter-list {
        color: green;
    }
    
    div.side-matter-text {
        color: black;
    }

= List of Class Selectors =
Here is a full list of Side Matter class selectors and their uses:

* `a.side-matter-ref` selects the link elements that enclose in-text reference numerals. Use this class to modify the links' colors, underline, etc.
* `sup.side-matter-sup` selects the `sup` (superscript) elements that enclose in-text reference numerals. Use this class to modify the numerals' typographic properties, such as size, offset, and font.
* `.widget_side_matter` (note the underscores) selects the widget container element, which may be an `aside` or a `div`.
* `ol.side-matter-list` selects the `ol` (ordered list) element that encloses notes in the sidebar, including list numerals.
* `li.side-matter-note` selects sidenote `li` (list item) elements, including each note's numeral.
* `div.side-matter-text` selects the `div` elements that enclose sidenote text, but not sidenote list numerals. Use this to style sidenote text separately from numerals.

Each paragraph within a note is further wrapped in a `p` tag. However, as they're generated outside the plugin, these `p` elements cannot be selected directly using the `side-matter` class. Instead, select them indirectly, e.g. `div.side-matter-text > p`.

= Default CSS =
Side Matter sets a few rules by default in the included stylesheet `side-matter.css`. These rules will generally defer to your theme stylesheet in the event of a conflict.

The first rule removes underlines from reference numeral links:

    a.side-matter:link,
    a.side-matter:visited,
    a.side-matter:hover,
    a.side-matter:active {
        text-decoration: none;
    }

The second rule specifies a consistent cross-browser, cross-theme format for superscript figures:
    
    sup.side-matter-sup {
        position: relative;
        top: -0.5em;
        vertical-align: baseline;
        font-size: 0.75em;
        line-height: 0;
    }

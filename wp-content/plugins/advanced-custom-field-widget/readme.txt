=== Advanced Custom Field Widget ===
Contributors: athenaofdelphi
Donate link: http://athena.outer-reaches.com/wiki/doku.php?id=donate
Tags: custom field, custom value, custom key, field, value, key, post meta, meta, get_post_meta, widget, sidebar, multiple widgets
Requires at least: 2.5
Tested up to: 3.5
Stable tag: 0.991

The Advanced Custom Field Widget is an extension of the Custom Field Widget by Scott Wallick, and displays values of custom field keys.

== Description ==

The Advanced Custom Field Widget is an extension of the Custom Field Widget by Scott Wallick, and displays values of custom field keys, allowing post- and page-specific meta sidebar content.

For detailed information about this plugin and how it works, check out it's wiki page at [Athena's Wiki](http://athena.outer-reaches.com/wiki/doku.php?id=projects:acfw:home).

For more information about Scott's orginal Custom Field Widget, check out [plaintxt.org](http://www.plaintxt.org/experiments/custom-field-widget/).

== Installation ==

Installing this plugin, is just like installing any other WordPress plugin.

1. Download Advanced Custom Field Widget
2. Extract the `/adv-custom-field-widget/` folder from the archive
3. Upload this folder to `../wp-contents/plugins/`
4. Activate the plugin in *Dashboard > Plugins*
5. Customize from the *Design > Widgets* menu

In other words, just upload the `/adv-custom-field-widget/` folder and its contents to your plugins folder.

For more information about plugins and installing them, please review the [managing plugins](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins "Installing Plugins - WordPress Codex") section of the WordPress Codex.

== Changelog ==

= 0.991 =
* Updated to reflect compatibility with WordPress 3.5

= 0.99 =
* Added the ability of the widget to pass it's generated content through the WordPress shortcode processes

= 0.98 =
* Fixed an issue with the content generator which was mis-handling line feeds, resulting the 'n' being displayed where a line feed should have been

= 0.97 =
* Removed the version history from plugin file to try and resolve the plugin directory issues

= 0.96 =
* Added Dutch translation - Thanks to Ronald van der Zwan for this translation

= 0.95 =
* Fixed an issue with the 'Load all custom fields' functionality when the load all items option was not enabled

= 0.94 =
* Re-encoded the files to try and resolve the problem with the plugin directory

= 0.93 =
* Added an option to stop the field filters putting the content generator through the 'convert_chars' filter.  When running with different locales, this appears to be converting some chars to entities (& to &#38;) with the consequence that links were being broken
* Added a shortcode for ACFW.  Add [acfw id="<INSTANCEID>"] to a post and the widget will be rendered in the post
* Added a function for ACFW. Add acfw(id) to a theme and the widget will render directly in the theme
* Added a custom siderbar to hold widgets for use by ACFW shortcode and function
* Added a widget instance ID display to the configuration panel (this provides the widget instance ID's for use with the shortcode and theme rendering function)
* Added enhanced separator functionality allowing users to specify a different separator for the last item and a list terminator

= 0.92 =
* Fix to an error in the main loop query replacement code that was preventing the widget from displaying on pages with multiple posts

= 0.91 =
* Added ability for widget to load all custom fields into variables $acfw_<FIELDNAME> for use in the content generator (main key field is still loaded in $acfw_content).
* Removed previous version comments in an effort to tidy up the code base slightly.
* Added ability to load values from multiple instances of specified fields.  The separator used can also be specified.
* Revised control panel layout to try and make it clearer.  This has resulted in some major changes to the translation file.
* Removed a deprecated function from the control panel, please check all configuration values after upgrading to this version to ensure that HTML entities etc. are properly stored.
* Fixed an issue when using the widget within the main loop.  The page would continue on and on as the widget resets the main query resulting in the loop not being able to find the end of the page/post list.
* Added ability to process content generator as PHP script which should populate the variable '$content'.

= 0.83 =
* Updated readme.txt to reflect support for WordPress version 3.0.1

= 0.82 =
* Fixed problem when using widget with WordPress 2.9.  Some widgets were being displayed when they had no content.

= 0.81 =
* Fixed problem when using widget index functionality.  Widgets which didn't have a source page provided by the list were repeating the first item in the list.

= 0.8 =
* Widget index field added to widget control panel. This field allows you to have multiple widgets on the page all linked to the same custom field via the '-linkto' function. For more information, read the user guide.
* Extra additional data fields ($data2 to $data5) added for use in the content generator.
* Page title variable ($pagetitle) added for use in the content generator. The variable is loaded with the title for the page whose data is being displayed by the widget.
* Content generator was leaving slashes in the generated content.

= 0.7 =
* Added additional data field $data1 and the required configuration field to the widget control panel.

= 0.6 =
* Added 'Content Generator' functionality.

= 0.5 =
* Modified widget to reinitialise the $post variable to prevent it becoming 'corrupted' by other widgets which may have been rendered before the ACFW.

= 0.4 =
* First version available via WordPress plugin directory.
* Options are now only removed when the plugin is uninstalled (this is handled by 'uninstall.php').
* Changed text domain for translation to 'acf_widget'.

= 0.3 =
* Added '<KEY>-linkto' processing allowing the content for a specific field to be provided by another page. This takes priority over the 'acfw-linkto' functionality.

= 0.2 =
* Widget was only displaying content for posts (not pages). This was fixed in this version.
* Added 'acfw-linkto' functionality to allow all widgets on a page to link to the same page.

= 0.1 =
* Original version of Advanced Custom Field Widget.  This is a seriously butchered version of the original Custom Field Widget by Scott Wallick.

== License ==

Advanced Custom Field Widget, a plugin for WordPress, (C) 2008-10 by Christina Louise Warne (based on Custom Field Widget, a plugin for WordPress, (C) 2008 by Scott Allan Wallick, licensed under the [GNU General Public License](http://www.gnu.org/licenses/gpl.html "GNU General Public License")), is licensed under the [GNU General Public License](http://www.gnu.org/licenses/gpl.html "GNU General Public License").

Advanced Custom Field Widget is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

Advanced Custom Field Widget is distributed in the hope that it will be useful, but **without any warranty**; without even the implied warranty of **merchantability** or **fitness for a particular purpose**. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Advanced Custom Field Widget. If not, see [http://www.gnu.org/licenses/](http://www.gnu.org/licenses/ "GNU General Public Licenses").

== Frequently Asked Questions ==

= What does this plugin do? =

Advanced Custom Field Widget displays the custom field values of a specified custom field key in your sidebar.

= Isn't this just a rip off of Scott's plugin? =

No, I don't think so, a whole bunch of stuff has been added.

= Aren't you just trying to steal his thunder? =

No, if I were, would I have even mentioned that the plugin is based on another?  Would I have stated quite clearly that it was originally written by a guy called Scott?  Would I have linked to his site?

If I was into plagiarism, then maybe I would have neglected to mention him, but I'm not.  So credit where it's due.  Scott wrote the original plugin and I'm very grateful to him for doing so, because he did a good job... nice code, with plenty of comments.  As a result, I was able to quickly understand the mechanics involved and modify it to suit my needs, and in the spirit of open source, I'm now making my version available for anyone who wants it.

= Why 'Advanced Custom Field Widget' and not 'Custom Field Widget 0.2' ? =

Well, simply...

* It's not my decision what happens with Scott's plugin, so to call it 'Custom Field Widget 0.2' would have been rude
* It's highly likely I'll modify this version further
* Compared to the orginal, it is slightly more advanced and provides you with more control

= Uninstalling The Plugin =

I got tired of the plugin deleting it's configuration when it was deactivated.  Now it's available via WordPress.org, I figured I needed to do something about it given that people will use the automatic update feature and watch their configuration disappear as the plugin is deactivated during the upgrade.

So, the plugin now does not delete it's configuration when it is deactivated.  Instead it uses the 'uninstall.php' feature available in WordPress 2.7+ to clean up the options in the database when it is physically deleted, and then only if the user has the ability to activate plugins.

If you find the plugin works with earlier versions of WordPress and you want to clean up your DB when you uninstall it, the options field you are looking for is 'widget_adv_custom_field'.

This uninstall function is new (as of version 0.4), so if it's slightly buggy, please let me know and I'll see if I can fix it up.  I have just spent quite a while getting it to work and making sure it only deletes the config when it is deleted, but I'm only human and I could have got it wrong, so if you lose your config... apologies... profuse apologies.

== Screenshots ==

1. Widget configuration options
2. In place on my site displaying the Amazon associates links connected to items I've reviewed
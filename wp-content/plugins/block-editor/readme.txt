=== Block Editor by Businessbox.com.au ===
Contributors: Businessbox
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=philippe%40ostral%2ecom%2eau&lc=US&item_name=BusinessBox%20Donnations&no_note=0&currency_code=AUD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHostedGuest
Tags: businessbox, editor, block, blocks, region, regions, editable, edit, multiple, section, sections, html, content, position, picture, text
Requires at least: 3.5.1
Tested up to: 3.5.1
Stable tag: 0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

With Block Editor plugin, slice your page into multiple blocks and position your website content in your page with no Html code in the editable block

== Description ==

A standard Wordpress page comes with only one block editor and positioning your content reveals to be difficult without Html coding.

With Block Editor wordpress plugin, you will be able to slice the page into multiple blocks and position your website content in specific locations in the page without having to write any Html code in the editable block.

= Further Reading =
For more info, please visit the links below:

* Visit our [website](http://www.businessbox.com.au/)
* Follow our [blog](http://www.businessbox.com.au/blogs/blog)
* Follow BusinessBox on [LinkedIn](http://www.linkedin.com/in/philippesoria), [Twitter](https://twitter.com/businessbox1) & [Facebook](https://www.facebook.com/pages/Businessbox/438966746178271)

== Installation ==

1. Download and upload the plugin files in the /wp-content/plugins/ directory of your wordpress website
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Edit the template file of the page where you want to add an editable region.
4. Place '`<?php be_add_editable_block("name_of_region") ?>`' where you would like the content to appear. The name of the region should be unique per page.
5. Open your website at the page where you just added an editable region or refresh it if already opened. This action make sure the region is registered in the admin dashboard.
6. Edit the page through the admin dashboard. Above the edition area, there should be at least one tab for the main region "Main Content", plus the other additional regions that have been created as explained above.
7. Click on the tab where you want to add some new content.
8. Add the content.
9. Save and update the page.
10. Go back to your website. You should be able to see your additional content.

== Screenshots ==

1. Registering / using a region in a template file
2. Region tabs in the admin dashboard
3. Example of two regions in a page: the left side being the custom region and the right side containing the main content of the page

== Changelog ==

= 0.3 =
* Changed the way content is stored, ie, as in textual mode
* Shortcode functions are now applied on the content of each block / region

= 0.2 =
* Fixed bug: content of editable regions were deleted after two consecutive updates on text mode

= 0.1 =
* Initial beta release

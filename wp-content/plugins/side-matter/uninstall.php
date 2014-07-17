<?php
/*
uninstall.php
Version 1.4

Plugin: Side Matter
Author: Christopher Setzer
URI: http://wordpress.org/extend/plugins/side-matter/
License: GPLv2
*/

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit ();
}

delete_option( 'side_matter_options' ); // Remove database field side_matter_options on plugin deletion
delete_option( 'widget_side-matter' ); // Remove database field for Side Matter widget settings on plugin deletion

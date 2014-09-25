<?php
/**
 * Plugin Name:       Bootstrap MCE CSS
 * Plugin URI:        http://wordpress.org/plugins/bootstrap-mce-css/
 * Description:       Adds a modified version of the Bootstrap CSS to the MCE editor, for developers who like to add their own Bootstrap code to the editor, without shortcodes.
 * Version:           0.1.1
 * Author:            Dave Warfel
 * Author URI:        http://wpsmackdown.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Load modified Bootstrap CSS from plugin folder
function plugin_mce_css( $mce_css ) {
	if ( ! empty( $mce_css ) )
		$mce_css .= ',';

	$mce_css .= plugins_url( 'css/bootstrap.min.css', __FILE__ );

	return $mce_css;
}

add_filter( 'mce_css', 'plugin_mce_css' );
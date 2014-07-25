<?php
/*
Plugin Name: Disable Visual Editor WYSIWYG
Version: 1.6.0
License: GPL2
Plugin URI: http://wordpress-themes.pro/
Author: Stanislav Mandulov
Author URI: http://wordpress-themes.pro/
Description: This plugin will disable the visual editor for selected page(s)/post(s)/custom post types. The idea behind this came after i had to keep the html intact by the tinymce editor whenever i switched back to Visual tab in the editor.
* 
    Copyright 2010  DiscordiaDesign.com  (email : office@discordiadesign.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


add_filter( 'wp_default_editor', 'dvew_switch_editor' );
add_filter( 'admin_footer', 'dvew_admin_edit_page_js', 99);
add_action( 'plugins_loaded', 'dvew_plugins_loaded' );

function dvew_plugins_loaded(){
    if(is_super_admin()){
		add_action( 'add_meta_boxes', 'dvew_add_meta_boxes' );
		add_action( 'save_post', 'dvew_save_post' );
	}
}

function dvew_switch_editor($content){
	if( ( $post = dvew_get_post_details() ) === FALSE ){
		return false;
	}
	
	if( ( isset($post['id']) && get_post_meta($post['id'], 'dvew_checkbox') != false ) || ( isset($post['type']) && get_option( 'dvew_post_type_' . $post['type'] ) != false ) ){
		return 'html';
	}
	return $content;
}


function dvew_get_post_details(){
	global $parent_file, $pagenow, $self;
	
	if( strpos( $parent_file, 'edit.php' ) !== 0 ){
		return false;
	}
	
	$post = array();
	
	if( isset($_GET['post']) ){
		$post['id'] = (int)$_GET['post'];
		$post['type'] = get_post_type( $post['id'] );
	}elseif( isset($_GET['post_type']) ){
		$post['type'] = esc_sql( $_GET['post_type'] );
	}elseif( $parent_file == 'edit.php' && $pagenow == 'post-new.php' ){
		$post['type'] = 'post';
	}
	
	if( count($post) == 0 ){
		return false;
	}
	
	return $post;
}

function dvew_admin_edit_page_js(){
	if( ( $post = dvew_get_post_details() ) === FALSE ){
		return false;
	}
	
	if( ( isset($post['id']) && get_post_meta($post['id'], 'dvew_checkbox') != false ) || ( isset($post['type']) && get_option( 'dvew_post_type_' . $post['type'] ) != false ) ){
		echo '  <style type="text/css">
				a#content-tmce, a#content-tmce:hover, #qt_content_fullscreen{
					display:none;
				}
				</style>';
		echo '	<script type="text/javascript">
			 	jQuery(document).ready(function(){
					jQuery("#content-tmce").attr("onclick", null);
			 	});
			 	</script>';
	}
}

function dvew_add_meta_boxes($post_type) {
	add_meta_box( 
		'dvew_sectionid',
		__( 'Visual Editor', 'dvew_plugin' ),
		'dvew_custom_box',
		$post_type,
		'side',
		'default'
	);
}


function dvew_custom_box() {
	if( ( $post = dvew_get_post_details() ) === FALSE ){
		return false;
	}
	
	wp_nonce_field( plugin_basename( __FILE__ ), 'dvew_noncename' );
	
	$checked = "";
	if( isset($post['id']) && get_post_meta($post['id'], 'dvew_checkbox') != false ) {
		 $checked = ' checked="checked" ';
	}
	
	echo '<p>';
	echo '<input type="checkbox" id="dvew_checkbox" name="dvew_checkbox" '.$checked.'/>';
	echo '<label for="dvew_checkbox">';
	   _e(" Disable for current post", 'dvew_plugin' );
	echo '</label> ';
	echo '</p>';
	
	$checked = "";
	if( isset($post['type']) && get_option( 'dvew_post_type_' . $post['type'] ) != false ){
		 $checked = ' checked="checked" ';
	}
	
	echo '<p>';
	echo '<input type="checkbox" id="dvew_post_type" name="dvew_post_type" '.$checked.'/>';
	echo '<label for="dvew_post_type">';
	   _e(" Disable for all posts of this type", 'dvew_plugin' );
	echo '</label> ';
	echo '</p>';
}

function dvew_save_post( $post_id ) {
	if(defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
		return false;
	}
	
	if(!isset($_POST['dvew_noncename']) || !wp_verify_nonce( $_POST['dvew_noncename'], plugin_basename( __FILE__ ) ) ){
		return false;
	}
	
	if(isset($_POST['dvew_checkbox'])){
		add_post_meta($post_id, 'dvew_checkbox', 1, true);
	}else{
		delete_post_meta($post_id, 'dvew_checkbox');
	}
	
	$post_type = get_post_type($post_id);
	
	if(isset($_POST['dvew_post_type'])){
		add_option('dvew_post_type_'.$post_type, 1, 'no');
	}else{
		delete_option('dvew_post_type_'.$post_type);
	}
}

?>
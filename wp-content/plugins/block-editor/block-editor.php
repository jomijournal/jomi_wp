<?php
/*
Plugin Name: Block Editor
Plugin URI: http://wordpress.org/extend/plugins/block-editor/
Description: With Block Editor wordpress plugin, you will be able to slice the page into multiple blocks and position your website content in specific locations in the page without having to write any Html code in the editable block.
Version: 0.2
Author: BusinessBox
Author URI: http://www.businessbox.com.au
License: A "Slug" license name e.g. GPL2
*/

require_once 'config.php';

add_action ('admin_head', 'be_check_meta', 1);
add_action ('admin_init', 'be_add_css_js');
add_action ('edit_form_after_editor', 'be_admin_panel', 1);

function be_add_editable_block( $block, $return = BE_DEFAULT_RETURN )
{
	global $post;
	
	$meta = get_post_custom();
	
	$block = BE_META_PREFIX . be_slugify($block);
	
	add_post_meta($post->ID, $block, '', true);

	if( isset($meta[$block]) )
	{
		if($return)
			return do_shortcode($meta[$block][0]);
		else
			echo do_shortcode($meta[$block][0]);
	}
}

function be_check_meta()
{
	global $post;
	
	$templates = array(
			$post->post_type . "-" . $post->post_name . ".php",
			$post->post_type . "-" . $post->ID . ".php",
			$post->post_type . ".php",
			"index.php",
			);
	
	$post_template_file = locate_template($templates);
	
	if( is_file($post_template_file) )
	{
		$post_template_data = file_get_contents($post_template_file);
		
		$matches = false;
		
		if( preg_match_all("/be_add_editable_block\((.*)\)/", $post_template_data, $matches) )
		{
			while($parameters = array_shift($matches[1]))
			{
				$parameters = explode(',', $parameters);
				$block_name = BE_META_PREFIX . be_slugify(preg_replace('/[\'"]/', '', trim($parameters[0])));
				
				add_post_meta($post->ID, $block_name, '', true);
			}
		}
	}
}

function be_add_css_js()
{
	wp_register_style('block_editor_css', plugins_url('block_editor.css', __FILE__) );
	wp_enqueue_style( 'block_editor_css');
	wp_register_script( 'block_editor_js', plugins_url('block_editor.js', __FILE__), array('jquery') );
	wp_enqueue_script( 'block_editor_js' );
}

function be_admin_panel()
{
	global $post;
	
	$metas = has_meta($post->ID);
	
	while($meta = array_shift($metas))
	{	
		if(preg_match('/^' . BE_META_PREFIX . '/', $meta['meta_key']))
		{
			$block_name = substr($meta['meta_key'], strlen(BE_META_PREFIX));
			
			echo '<div id="postdivmeta-' . $meta['meta_id'] . '" class="postarea metaeditdiv" data-metaid="' . $meta['meta_id'] . '" data-metakey="' . $meta['meta_key'] . '" data-blockname="' . ucwords($block_name) . '">';
				wp_editor($meta['meta_value'], 'metaeditor-' . $meta['meta_id'], array('textarea_rows' => 14));
				echo '<table id="post-status-info" cellspacing="0">';
					echo '<tr><td id="wp-word-count">Word count: ' . strlen($meta['meta_value']) . '</td></tr>';
				echo '</table>';
			echo '</div>';
		}
	}
}

function be_dump( $var )
{
	echo "<pre>";
	var_dump( $var );
	echo "</pre>";
}

function be_slugify($text)
{
	// replace non letter or digits by -
	$text = preg_replace('~[^\\pL\d]+~u', '-', $text);

	// trim
	$text = trim($text, '-');

	// transliterate
	$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

	// lowercase
	$text = strtolower($text);

	// remove unwanted characters
	$text = preg_replace('~[^-\w]+~', '', $text);

	if (empty($text))
	{
		return 'n-a';
	}

	return $text;
}

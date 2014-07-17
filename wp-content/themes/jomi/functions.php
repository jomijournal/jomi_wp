<?php

/* DB SETTINGS */
// stolen from stack_overflow
function get_ip_address(){
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
        if (array_key_exists($key, $_SERVER) === true){
            foreach (explode(',', $_SERVER[$key]) as $ip){
                $ip = trim($ip); // just to be safe

                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                    return $ip;
                }
            }
        }
    }
}
$whitelist = array(
    '127.0.0.1',
    '::1'
);
// if localhost
if(in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
    update_option('siteurl', 'http://localhost/');
} else {
  update_option('siteurl', 'http://squash.jomi.com');
}



/* COMPOSER */
require_once('vendor/autoload.php');

/* USERAPP */

use \UserApp\Widget\User;
User::setAppId("53b5e44372154");

/**
 * Roots includes
 */
$roots_includes = array(
  '/lib/utils.php',           // Utility functions
  '/lib/init.php',            // Initial theme setup and constants
  '/lib/wrapper.php',         // Theme wrapper class
  '/lib/sidebar.php',         // Sidebar class
  '/lib/config.php',          // Configuration
  '/lib/activation.php',      // Theme activation
  '/lib/titles.php',          // Page titles
  '/lib/cleanup.php',         // Cleanup
  '/lib/nav.php',             // Custom nav modifications
  '/lib/gallery.php',         // Custom [gallery] modifications
  '/lib/comments.php',        // Custom comments modifications
  '/lib/relative-urls.php',   // Root relative URLs
  '/lib/widgets.php',         // Sidebars and widgets
  '/lib/scripts.php',         // Scripts and stylesheets
  '/lib/custom.php',          // Custom functions
);

foreach($roots_includes as $file){
  if(!$filepath = locate_template($file)) {
    trigger_error("Error locating `$file` for inclusion!", E_USER_ERROR);
  }

  require_once $filepath;
}
unset($file, $filepath);

// Bug testing only. Not to be used on a production site!!
/*add_action('wp_footer', 'roots_wrap_info');

function roots_wrap_info() {  
  $format = '<h6>The %s template being used is: %s</h6>';
  $main   = Roots_Wrapping::$main_template;
  global $template;

  printf($format, 'Main', $main);
  printf($format, 'Base', $template);
}*/

/*
=================================
POST TYPES
=================================
*/

add_filter('pre_get_posts', 'query_post_type');
function query_post_type($query) {
  if(is_category() || is_tag()) {
    $post_type = get_query_var('post_type');
    if($post_type)
        $post_type = $post_type;
    else
        $post_type = array('post','article',);
    $query->set('post_type',$post_type);
    return $query;
    }
}

add_action('init', 'cptui_register_my_cpt_article');
function cptui_register_my_cpt_article() {
register_post_type('article', array(
'label' => 'Journal',
'description' => '',
'public' => true,
'show_ui' => true,
'show_in_menu' => true,
'capability_type' => 'post',
'map_meta_cap' => true,
'hierarchical' => false,
'rewrite' => array('slug' => 'article', 'with_front' => true),
'query_var' => true,
'menu_icon' => '/wp-content/themes/jomi/assets/img/logo-notext-s.png',
'supports' => array('title','editor','excerpt','trackbacks','custom-fields','comments','revisions','thumbnail','author','page-attributes','post-formats'),
'taxonomies' => array('category','post_tag'),
'labels' => array (
  'name' => 'Journal',
  'singular_name' => 'Article',
  'menu_name' => 'Journal',
  'add_new' => 'Add Article',
  'add_new_item' => 'Add New Article',
  'edit' => 'Edit',
  'edit_item' => 'Edit Article',
  'new_item' => 'New Article',
  'view' => 'View Article',
  'view_item' => 'View Article',
  'search_items' => 'Search Journal',
  'not_found' => 'No Journal Found',
  'not_found_in_trash' => 'No Journal Found in Trash',
  'parent' => 'Parent Article',
)
) ); }

/*
=================================
POST STATUSES
=================================
*/

function unread_post_status(){
  register_post_status( 'preprint', array(
    'label'                     => _x( 'Preprint', 'article' ),
    'public'                    => true,
    'exclude_from_search'       => true,
    'show_in_admin_all_list'    => true,
    'show_in_admin_status_list' => true,
    'label_count'               => _n_noop( 'Preprint <span class="count">(%s)</span>', 'Preprint <span class="count">(%s)</span>' ),
  ) );
}
add_action( 'init', 'unread_post_status' );

add_action('admin_footer-post.php', 'append_post_status_list');
function append_post_status_list(){
  global $post;
  $complete = '';
  $label = '';
  if($post->post_type == 'article')
  {
      if($post->post_status == 'preprint')
      {
           $complete = ' selected=\"selected\"';
           $label = '<span id=\"post-status-display\"> Preprint</span>';
      }
      echo '
      <script>
      jQuery(document).ready(function($){
           $("select#post_status").append("<option value=\"preprint\" '.$complete.'>Preprint</option>");
           $(".misc-pub-section label").append("'.$label.'");
      });
      </script>
      ';
  }
}

function display_archive_state( $states ) {
     global $post;
     $arg = get_query_var( 'post_status' );
     if($arg != 'preprint'){
          if($post->post_status == 'preprint'){
               return array('Preprint');
          }
     }
    return $states;
}
add_filter( 'display_post_states', 'display_archive_state' );

/*
=================================
REWRITE RULES
=================================
*/

function add_article_rewrite_rules() {
    add_rewrite_rule('^article/([^/]*)','index.php?post_type=article&p=$matches[1]','top');
    add_rewrite_rule('^article/([^/]*)/([^/]*)','index.php?post_type=article&p=$matches[1]','top');
    flush_rewrite_rules();
}
add_action( 'init', 'add_article_rewrite_rules' );

add_action('init', 'article_rewrite');
function article_rewrite() {
  global $wp_rewrite;
  $queryarg = 'post_type=article&p=';
  $wp_rewrite->add_rewrite_tag('%article_id%', '([^/]+)', $queryarg);
  $wp_rewrite->add_rewrite_tag('%article_name%', '([^/]+)', $queryarg);
  $wp_rewrite->add_permastruct('article', '/article/%article_id%/%article_name%/', false);
}

add_filter('post_type_link', 'article_permalink', 1, 3);
function article_permalink($post_link, $id = 0, $leavename) {
  global $wp_rewrite;
  $post = &get_post($id);
  if ( is_wp_error( $post ) )
    return $post;
  $newlink = $wp_rewrite->get_extra_permastruct('article');
  $newlink = str_replace("%article_id%", $post->ID, $newlink);
  $newlink = str_replace("%article_name%", $post->post_name, $newlink);
  $newlink = home_url(user_trailingslashit($newlink));
  return $newlink;
}

/*
=================================
CUSTOM SIDEBARS
=================================
*/

register_sidebar(array(
	'name' => __('About Sidebar'),
	'id' => 'sidebar-about',
	'description' => __('Sidebar for the About Page'),
	'before_widget' => '',
	'after_widget' => '',
	'before_title' => '<h3>',
	'after_title' => '</h3>',
) );

register_sidebar(array(
  'name' => __('Article Sidebar'),
  'id' => 'sidebar-article',
  'description' => __('Sidebar for Article Pages'),
  'before_widget' => '',
  'after_widget' => '',
  'before_title' => '<h3>',
  'after_title' => '</h3>',
) );
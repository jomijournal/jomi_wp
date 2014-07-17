<?php

// separate out the title 

class kblog_title{
    
    var $kblog_slug_container_title = "_kblog_container_title";
    
    function __construct(){
        add_action( 'init', array( $this, 'register_event_taxonomy' ) );
    }


    function register_event_taxonomy(){
        
        global $wp_rewrite;
        
        register_taxonomy( 'event', 'post',
                           array( 'hierarchical' => true,
                                  'rewrite' => 
                                  array('slug' => "event"),
                                  'label' => 'Events',
                                  'public' => true,
                                  // have an editor box
                                  'show_ui' => true,
                                  // these are the labels that appear in various parts
                                  'labels' =>
                                  array( 'singular_name' =>"Event",
                                         'search_items' => "Search Events",
                                         'popular_items' => "Popular Events",
                                         'all_items' => "All Events",
                                         'parent_item' => 'Parent Event',
                                         'parent_item_colon' => 'Parent Event:',
                                         'edit_item' => 'Edit Event',
                                         'view_item' => 'View Event',
                                         'update_item' => 'Update Event',
                                         'add_new_item' => 'Add New Event',
                                         'new_item_name' => 'New Event Name' )
                                  )
                           );
        
    }


    function container_title_in_event_defined($postid){
        $terms = get_the_terms( $postid, 'event' );
        if( false == $terms ){ 
            return false;
        }        
        return ! count( $terms ) == 0;        
    }

    function container_title_on_post_defined($postid){
        return  
            (boolean)get_post_meta($postid, $this->kblog_slug_container_title, true);
    }
    
    function get_container_title($postid){
        // defined on post takes priority
        if( $this->container_title_on_post_defined($postid) ){
            return get_post_meta($postid, $this->kblog_slug_container_title, true);
        }

        // then inherit from any events
        if( $this->container_title_in_event_defined($postid) ){
            $terms = get_the_terms( $postid, 'event' );
            foreach( $terms as $term ){
                return $term->name;
            }
        }
        
        // then inherit from blog
        return get_bloginfo('name');
    }

    
}

function kblog_title_init(){
    global $kblog_title;
    $kblog_title = new kblog_title();
}

kblog_title_init();


class kblog_title_admin{
    
    function __construct(){
        add_action( 'add_meta_boxes', array( $this, 'title_meta_box') );
        add_action( 'save_post', array( $this, 'title_save_post' ) );
    }

    function title_meta_box(){
        add_meta_box( "kblog-title-meta-box",
                      "Container Title",
                      array( $this, 'render_title_meta_box' ),
                      'post' );
                      
    }

    function render_title_meta_box(){
        global $kblog_title;
        
        $out = "";
        $current_title="";
        
        $out .= "<p>Container title is currently <b>";
        $out .= $kblog_title->get_container_title( get_the_ID() );
        $out .= "</b>";
        
        if( $kblog_title->container_title_on_post_defined( get_the_ID() ) ){
            $out .= " which is defined for the post";
            
            $current_title = ' value="';
            $current_title .= $kblog_title->get_container_title(get_the_ID());
            $current_title .= '"';
        }
        else{
            if( $kblog_title->container_title_in_event_defined( get_the_ID() ) ){
                $out .= " which is inherited from an event";
            }
            else{
                $out .= " which is the title of the blog";
            }            
        }
        
        $out .= "</p>";

        $nonce = wp_nonce_field( "kblog_set_container_title", 
                                 "kblog_set_container_field",
                                 true, false );

        $out.= <<<EOT
            $nonce
            <input type="text" name="container_title" $current_title/>
EOT;
        
        print( $out );
    }


    function title_save_post(){

        global $kblog_title;
        $post_id = get_the_ID();

        if( !wp_verify_nonce( $_POST["kblog_set_container_field"],
                              "kblog_set_container_title" ) ){

            return $post_id;
        }

        if( $_POST['post_type']=='page'){
            if(!current_user_can('edit_page',$post_id)){
                return $post_id;
            }
        }
        else{
            if( !current_user_can('edit_post',$post_id)){
                return $post_id;                
            }
        }
        
        if( array_key_exists( "container_title", $_POST ) ){
            if( (boolean)$_POST["container_title"]){
                // ensure there is only ever one
                delete_post_meta($post_id, $kblog_title->kblog_slug_container_title );
                add_post_meta($post_id, $kblog_title->kblog_slug_container_title, 
                              $_POST["container_title"] );
            }
            else{
                // if the text box is empty
                delete_post_meta($post_id, $kblog_title->kblog_slug_container_title );
            }
        }
    }

}


function kblog_title_admin_init(){
    global $kblog_title_admin;
    $kblog_title_admin = new kblog_title_admin();
}

// if we are running an admin page, then we need this. Otherwise, we don't. 
if( is_admin() ){
    add_action( 'init', 'kblog_title_admin_init' );
}


// export functionality

// drop in replacement for bloginfo( 'name' ) in the header. When displaying a singular post
// the title will be this, plus
function kblog_title_container_title(){
    echo kblog_title_get_container_title();
}

function kblog_title_get_container_title(){
    global $kblog_title;
    
    if( is_singular() ){
        return $kblog_title->get_container_title( get_the_ID() );
    }
    return get_bloginfo( 'name' );
}

?>
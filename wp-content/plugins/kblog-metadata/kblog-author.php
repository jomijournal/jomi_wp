<?php

// this will handle post related stuff, shortcode handling and the like
class kblog_author{
    
    var $kblog_opt_short_authors = '_kblog_author_short_authors';
    var $kblog_opt_gui_authors = '_kblog_author_gui_authors';
    var $authors = array();
    
    function __construct(){
        add_shortcode( 'author', array( $this, 'author_shortcode' ) );
        // we want to run after the shortcode filter has worked
        add_filter( 'the_content', array( $this, 'process_author_results' ), 12 );
    }

    
    function author_shortcode($atts, $content){
        $author = array();
        
        // store the content as the full name. Use the keys from get_the_author_meta
        $author[ 'display_name' ] = $content;
        
        $this->authors[] = $author;
        return $content;
    }

    
    function process_author_results($content){
        $this->store_short_authors_as_meta();
        // start afresh!
        $this->authors=array();
        return $content;
    }

    function store_gui_authors_as_meta($authors){
        $this->store_authors_as_meta($this->kblog_opt_gui_authors,$authors);
    }

    /* 
     * Store authors defined by short codes
     */
    function store_short_authors_as_meta(){
        $this->store_authors_as_meta($this->kblog_opt_short_authors,$this->authors);
    }

    function store_authors_as_meta($slug, $authors){
        $postid = get_the_id();
        $stored_authors = $this->get_authors_from_meta($slug, $postid);
        
        // all the same, so stop
        if( $this->compare_authors( $stored_authors, $authors ) ){
            return;
        }

        // delete the post meta
        delete_post_meta( $postid,
                          $slug );
        // only if we have more than one author, store it
        if( count( $authors ) > 0 ){
            add_post_meta( $postid, $slug,
                           $authors );
        }
    }
    
    /*
     * Compare author lists
     */
    function compare_authors( $a, $b ){
        if( count( $a ) != count( $b ) ){
            return false;
        }

        for( $i = 0; $i < count( $a ); $i++ ){
            if( 
               array_key_exists( "display_name", $a[$i] ) &&
               array_key_exists( "display_name", $b[$i] ) &&                                 
               strcmp( $a[$i]["display_name"], $b[$i]["display_name"] ) != 0 ){
                return false;
            }
        }

        return true;
    }

    function get_gui_authors_from_meta($postid){
        return $this->get_authors_from_meta($this->kblog_opt_gui_authors,$postid);
    }
    
    function get_short_authors_from_meta($postid){
        return $this->get_authors_from_meta($this->kblog_opt_short_authors,$postid);
    }
    
    function get_coauthors($postid){
        $cuauth_retn = array();
        if( function_exists( 'get_coauthors' ) ){
            $coauthors = get_coauthors($post_id);
            if(count($coauthors) > 0){
                $coauth_retn = array();
                
                foreach($coauthors as $coauthor){
                    $coauth_retn[] = 
                        array( "display_name"=>$coauthor->display_name );
                    
                }
            }
        }
        return $coauth_retn;
    }
    
    function get_authors_from_meta($slug, $postid){
        // true -- single value, which in practice means a deserialized array. 
        $authors = get_post_meta( $postid, $slug, true );

        //get_post_meta returns an empty string, but we want an array
        if( !is_array( $authors ) ){
            return array();
        }
        return $authors;
    }


    function get_authors_display($postid){
        $authors = $this->get_short_authors_from_meta($postid);

        $retn = "";
        foreach( $authors as $author ){
            $retn .= $author["display_name"];
        }
        return $retn;
    }
}



function kblog_author_init(){
    global $kblog_author;
    $kblog_author = new kblog_author();
}

kblog_author_init(); 


// this will add the admin page information which will either display metadata
// set by the post, or allow the it to be changed here. 
class kblog_author_admin{
    function __construct(){
        add_action( 'add_meta_boxes', array( $this, 'author_meta_box' ) );

        add_action( 'save_post', array( $this, 'author_save_box' ) );
    }
    
    function author_meta_box(){
        add_meta_box( "kblog-author-meta-box",
                      "Display Authors",
                      array( $this, 'render_author_meta_box' ),
                      'post' );

        add_meta_box( "kblog-author-meta-box",
                      "Display Authors",
                      array( $this, 'render_author_meta_box' ),
                      'page' );

    }

    function render_author_meta_box(){
        global $kblog_author;
        
        $out = "";
        
        $shortcode_authors = $kblog_author->get_short_authors_from_meta( get_the_ID() );
        // set in short code
        if( count( $shortcode_authors ) > 0 ){
            $out .= "Authors are set within the content, and must be edited there.\n<ol>";
            
            foreach( $shortcode_authors as $auth ){
                $display_name = $auth['display_name'];
                $out .= "<li>$display_name</li>";
            }
            $out .= "</ol>";
            
            print( $out );
            return;
        }

        $gui_authors = $kblog_author->get_gui_authors_from_meta( get_the_ID() );
        
        if( count( $gui_authors ) == 0 ){
            

            $coauthors = $kblog_author->get_coauthors( get_the_ID() );
            if( count($coauthors) > 0 ){
                $out .= "<p>Display authors are currently: </p><strong><ul>";
                foreach( $coauthors as $coauthor ){
                    $out .= "<li>" . $coauthor['display_name'] . "</li>\n";
                }
                $out .= "</ul></strong> <p>defined by co-authors plus.</p>\n";
            }
            else{

                // check for wordpress author
                $authorID = get_post( get_the_ID() )->post_author;
                
                $out .= "<p>Display author is currently <strong>";
                $out .= get_the_author_meta("display_name", $authorID);
                $out .= "</strong> who is the WordPress author</p>\n";
            }
        }
        
        $out .= "<p>Set display authors</p>";
                        
        
        $out .= wp_nonce_field("kblog_set_authors",
             "kblog_set_authors_field",
             true, false );
        $out .= "\n";
        $author_count = 0;
        
        $out .= "<ul>\n";
        foreach( $gui_authors as $auth ){
            $out .= "<li>";
            $out .= '<input type="text" name="gui_author';
            $out .= $author_count++;
            $out .= '" value="';
            $out .= $auth["display_name"];
            $out .= '" />';
            $out .= "</li>\n";
        }
        
        for( $i=0;$i < 3;$i++){
            $out .= '<li><input type="text" name="gui_author';
            $out .= $author_count++;
            $out .= '"/>';
            $out .= "</li>\n";
        }
        
        $out .= "</ul>";
        $out .= "<p>Save post to add more</p>";
                    
        print( $out );
    }

    function author_save_box($post_id){

        if( !wp_verify_nonce( $_POST["kblog_set_authors_field"],
                              "kblog_set_authors" ) ){
            return $post_id;
        }
        
        
        // check user permissions
        if ($_POST['post_type'] == 'page'){
            if (!current_user_can('edit_page', $post_id)){
                return $post_id;
            }
        }
        else{
            if (!current_user_can('edit_post', $post_id)){
                exit();
                return $post_id;
            }
        }
        
        $gui_author_number = 0;
        $gui_authors = array();
        $gui_sentinel = true;

        while( $gui_sentinel ){
            $key = "gui_author" . $gui_author_number++;
            if( array_key_exists( $key, $_POST ) ){
                if( (boolean)$_POST[ $key ] ){
                    $gui_author = array();
                    $gui_author['display_name'] = $_POST[ $key ];
                    $gui_authors[] = $gui_author;
                }
            }
            else{
                $gui_sentinel = false;
            }
        }
        
        
        global $kblog_author;
        $kblog_author->store_gui_authors_as_meta($gui_authors);
        return $post_id;
    }
    
}

function kblog_author_admin_init(){
    global $kblog_authors_admin;
    $kblog_authors_admin = new kblog_author_admin();
}

// if we are running an admin page, then we need this. Otherwise, we don't. 
if( is_admin() ){
    add_action( 'init', 'kblog_author_admin_init' );
}



//export functionality

/*
 * Fetch the display authors for this post. These may or may not be Wordpress
 * users. If used within the loop, $postid is optional. 
 * Returns an array. 
 * The fields of the array are the same as returned by "get_the_author_meta". 
 */
function kblog_author_get_authors($postid=false){
    global $kblog_author;
    
    // ensure we have a post id
    if( !$postid ){
        $postid = get_the_ID();
    }
    
    // meta authors take precedence
    $kblog_meta_authors = $kblog_author->get_short_authors_from_meta( $postid );
    
    if(count($kblog_meta_authors) > 0){
        return $kblog_meta_authors;
    }

    // gui authors next
    $kblog_gui_authors = $kblog_author->get_gui_authors_from_meta($postid);
    if(count($kblog_gui_authors) > 0){
        return $kblog_gui_authors;
    }
    
    // fall back to co-authors plus if it is available
    $coauthors = $kblog_author->get_coauthors($postid);
    if(count($coauthors)>0){
        return $coauthors;
    }
    
    // fall back to wordpress
    $authorID = get_post( $postid )->post_author;
    return array
        ( array( "display_name"=>get_the_author_meta("display_name", $authorID) ) );

}

function kblog_author_author(){
    $arr = array();
    $authors = kblog_author_get_authors();
    foreach( $authors as $author ){
        $arr[] = $author[ "display_name" ];
    }
    return implode( ",", $arr );
}


?>
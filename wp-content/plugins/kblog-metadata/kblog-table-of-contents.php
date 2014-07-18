<?php
/*
   Copyright 2010-12. 
   Phillip Lord (phillip.lord@newcastle.ac.uk)
   Simon Cockell (s.j.cockell@newcastle.ac.uk)
   Newcastle University. 
  
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

class kblog_table_of_contents{
    var $category_slug = "kblog_table_category";
    
    function __construct(){
        add_option( $this->category_slug, "All");
        
        add_shortcode( 'ktoc',
                       array( $this, "table_shortcode" ) );
        add_shortcode( 'kblogtoc',
                       array( $this, "table_shortcode" ) );

        add_filter( "query_vars",
                    array( $this, "toc_query_vars" ) );
        add_action( "template_redirect",
                    array( $this, "toc_template_redirect" ) );

    }

    function toc_query_vars($query_vars){
        $query_vars[] = "kblog-toc";
        return $query_vars;
    }
    
    function toc_template_redirect(){
        global $wp_query;

        if( $wp_query->query_vars["kblog-toc"] == "txt" ){
            $posts = $this->get_posts();
            foreach($posts as $post){
                print( get_permalink( $post ) );
                print( "\n" );
            }

            exit();
        }

        // TODO -- this is entirely untested
        // the greycite call is a bit of a disaster -- this will be 400 calls for russet.
        if( $wp_query->query_vars["kblog-toc"]=="bib"){
            $posts = $this->get_posts();
            foreach($posts as $post){
                $url = "http://greycite.knowledgeblog.org/bib?uri=" . get_permalink( $post );
      
                $wpresponse = wp_remote_get( $url );
                if( is_wp_error( $wpresponse ) ){
                    exit();
                }
      
                $status = wp_remote_retrieve_response_code( $wpresponse );
                
                if( $status != 200 ){ 
                    exit();
                }
      
                $response = wp_remote_retrieve_body( $wpresponse );
                
                print( $response);
                print( "\n" );
            }
            exit();
        }


        if( $wp_query->query_vars["kblog-toc"]=="html"){
            echo <<<EOT
<html>
<head><title>Contents</title><head>
<body>
EOT;

            $posts = $this->get_posts();
            foreach($posts as $post){
                print( '<p><a href="' );
                print( get_permalink( $post ) );
                print( '">' );
                print( get_permalink( $post ) );
                print( "</a></p>\n" );
            }

            echo <<<EOT
</body>
</html>
EOT;
            exit();
        }

    }




    function table_shortcode( $atts, $content ){
        extract(shortcode_atts(array(
                                     'cat' => false,
                                     'fill' => 'by',
                                     ), $atts));
        
        
        $posts = $this->get_posts( $cat );

        $out = "<ul>";
        
        foreach($posts as $post){
            $out .= '<li><a href="' . get_permalink( $post ) .
                '">' . $post->post_title . '</a>';
            $out .= " by";
            $authors = $this->get_authors($post);
            $sep = " ";
            foreach($authors as $author){
                $out .= $sep . $this->concat_name($author);
                $sep = ", ";
            }
            $out .= "</li>\n";
        }
        $out .= "</ul>";
        return $out; 
    }
    
    /*
     * Get the post objects for a given category or the defined category
     */
    function get_posts($category=false){
        if(!$category){
            $category=get_option( $this->category_slug );
        }
        
        $get_post_params = array( "numberposts"=>-1);
        
        // search for the category or return everything
        foreach(get_categories() as $cat){
            if($cat->cat_name == $category){
                $get_post_params["category"]=$cat->cat_ID;
            }
        }
        return get_posts( $get_post_params );
    }


    // this code is very similar to that in kblog-metadata which is a bit
    // unfortunate
    function concat_name( $author ){
        if( array_key_exists( "first_name", $author ) &&
            array_key_exists( "last_name", $author ) ){
            return $author["first_name"] . " " . $author[ "last_name" ];
        }
        return $author["display_name"];
    }


    /*
     *  Fetch the authors for a given ID. 
     */
    function get_authors($post)
    {
        return
            kblog_author_get_authors($post->ID);
    }
    

}

function kblog_table_init(){
    global $kblog_table;
    $kblog_table = new kblog_table_of_contents();
}

kblog_table_init();

class kblog_table_of_contents_admin{

    function __construct(){
        add_action( "kblog_metadata_admin_render", array( $this, "options_render" ) );
        add_action( "kblog_metadata_admin_save", array( $this, "options_save" ) );
    }


    function options_save(){
        global $kblog_table;
        if( array_key_exists( "kblog-table-display-categories", $_POST ) ){
            if( $_POST["kblog-table-display-categories"]=="all_posts"){
                delete_option( $kblog_table->category_slug );
            }
            else{
                update_option( $kblog_table->category_slug,
                               $_POST["kblog-table-display-categories"]
                               );
            }
            // TODO -- check for actual change
            echo "<p><i>Category Options Updated</i></p>";
        }
    }

    function options_render()
    {
        if( !current_user_can('manage_options')){
            wp_die( __('You do not have sufficient permissions to access this page.'));
        }

        global $kblog_table;

        $out .= <<<EOT

<h3>Table of Contents</h3>
<p>What is default category to display?</p>
<select name="kblog-table-display-categories">
EOT;
     
        // main GUI

        $categories = get_categories();
		$selected = get_option( $kblog_table->category_slug );
        if( !$selected ){
            $all_posts_selected='selected="true"';
        }
        $out .= "<option value='all_posts' $all_posts_selected>All Posts</option>\n";

        foreach ($categories as $cat) {
			$name = $cat->cat_name;
            $select_string = "";
            if( strcmp( $name, $selected ) == 0 ){
                $select_string='selected="true"';
            }
            
            $out .= "<option value='$name' $select_string>$name</option>\n";
        }
        $out .= "</select>";

        // and print
        print( $out );
    }

}

function kblog_table_admin_init(){
    global $kblog_table_admin;
    $kblog_table_admin = new kblog_table_of_contents_admin();
}

if( is_admin() ){
    kblog_table_admin_init();
}

?>

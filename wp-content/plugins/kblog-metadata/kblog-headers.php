<?php

require_once( dirname( __FILE__ ) . '/HumanNameParser/Name.php' );
require_once( dirname( __FILE__ ) . '/HumanNameParser/Parser.php' );


class kblog_headers{
    function __construct(){
        add_action( "wp_head", array( $this, "header_action" ) );
        $this->init_coins();
        $this->init_google_scholar();
        $this->init_ogp();
    }

    function header_action(){
        do_action( "kblog_head" );
    }

    /* Begin COINS section */

    function init_coins(){
        add_filter( "the_content", array( $this, "kblog_metadata_coins_content") );
    }

    function kblog_metadata_coins_content($content)
    {
        $authors = $this->kblog_metadata_get_authors();
    
        $first_sentinel = true;
        $author_string = "";
        foreach( $authors as $author ){
            // the insanity that is coins treats the first author and all subsequent authors differently. 
            // The first author can be split into first name/last name. All others can't. 
            if( $first_sentinel && 
                array_key_exists( "last_name", $author ) &&
                array_key_exists( "first_name", $author )            
                ){
                $first_sentinel = false;
                $author_string .= "&amp;rft.aulast=".urlencode($author['last_name']).
                    "&amp;rft.aufirst=".urlencode($author['first_name']);
                
            }
            else{
                $author_string .= "&amp;rft.au=".
                    urlencode( $this->kblog_metadata_concat_name($author) );
            }
            
        }
        $title = urlencode( get_the_title() );
        $blogtitle = urlencode( $this->get_container_title() );
        $time = get_the_time( 'Y-m-d' );
        $permalink = urlencode( get_permalink() );
        
        return
            "<!-- coins metadata inserted by kblog-metadata -->\n" .
            '<span class="Z3988" title="ctx_ver=Z39.88-2004&amp;rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Adc&amp;rfr_id='. 
            urlencode( "kblog-metadata.php" ) .  
            "&amp;rft.title=$title&amp;rft.source=$blogtitle&amp;rft.date=$time&amp;rft.identifier=$permalink" .
            $author_string . 
            '&amp;rft.format=text&amp;rft.language=English"></span>' .
            $content;
    }
    
    // End COINS

    function init_google_scholar(){
        // Google Scholar meta tags
        add_action( "kblog_head", array( $this, "kblog_metadata_header_metatags" ) );
    }

    function kblog_metadata_header_metatags(){
        echo "<!--Google Scholar tags by kblog-metadata.php-->\n";
        echo $this->kblog_metadata_get_metatags();
    }
    
    
    function kblog_metadata_get_metatags(){
        // multidimensional, cause multiple authors
        $metadata_items = array
            (
             array( "resource_type"=>"knowledgeblog" ),
             array( "citation_journal_title"=>$this->get_container_title() ),
             array( "citation_publication_date"=>get_the_time( 'Y/m/d' ) ),
             array( "citation_date"=>get_the_time( 'Y' ) )
             );
        
        
        if( is_single() || is_page() ){
            $authors = $this->kblog_metadata_get_authors();
            $author_array = array();
            foreach( $authors as $author ){
                list($firstname,$lastname) = $this->kblog_metadata_first_name_last_name( $author );
                $author_array[] = "$lastname, $firstname";
                $metadata_items[] = array
                    ( "citation_author"=>$this->kblog_metadata_concat_name($author) );
            }
            
            $metadata_items[] = array
                ("citation_authors"=>implode( ";", $author_array ) );
            
            $metadata_items[] = array
                ("citation_title"=>$this->kblog_metadata_get_the_title() );
        }
        
        return $this->kblog_metadata_generate_metatags( "name", $metadata_items );
    }

    // End Google Scholar meta tags

    // open graph protocol
    function init_ogp(){
        add_filter( "language_attributes", 
                    array( $this, "kblog_metadata_language_attributes_ogp_filter" ) );
        add_action( "kblog_head", 
                    array( $this, "kblog_metadata_header_ogp_metatags" ) );

        add_filter( "query_vars",
                    array( $this, "ogp_query_vars" ) );
        add_action( "template_redirect",
                    array( $this, "ogp_template_redirect" ) );

    }

    function kblog_metadata_language_attributes_ogp_filter( $language_attributes )
    {
        if( is_single() || is_page() ){
            $language_attributes .= ' xmlns:article="http://ogp.me/ns/article#"';
        }
        
        return $language_attributes . ' xmlns:og="http://ogp.me/ns#" ';
    }


    function kblog_metadata_header_ogp_metatags(){
        echo "<!--OGP tags by kblog-metadata.php-->\n";
        echo $this->kblog_metadata_get_ogp_metatags();
    }
    
    function kblog_metadata_get_ogp_metatags(){
        // got to here
        $metadata_items = array();
        $metadata_items[] = array
            ("og:site_name"=>$this->get_container_title() );
        

        if( is_single() || is_page() ){
            $metadata_items[] = array
                ("og:title"=>$this->kblog_metadata_get_the_title() );
            $metadata_items[] = array
                ("og:type"=>"article");
            $metadata_items[] = array
                ("og:url"=>$this->kblog_metadata_get_permalink() );
            
            
            $authors = $this->kblog_metadata_get_authors();
            $author_number = 0;
            
            // return a query string -- there is an obvious race condition here, which is 
            // this query string is only really valid at the time it is produced. 
            foreach( $authors as $author ){
                $metadata_profile = 
                    home_url() . "/" . 
                    "?kblog-header-p=" . $this->kblog_metadata_get_the_ID() . 
                    "&amp;kblog-header-author=" . $author_number++;
                $metadata_items[] = array
                    ( "og:author"=>$metadata_profile );
                                          
            }
            
            $metadata_items[] = array
                ( "article:published_time"=>get_the_time("Y-m-d") );
        }
        else{
            $metadata_items[] = array
                ("og:type"=>"website");
            $metadata_items[] = array
                ("og:url"=>home_url());
        }
        
        return $this->kblog_metadata_generate_metatags( "property", $metadata_items );
    }
    
    function ogp_query_vars($query_vars){
        $query_vars[] = "kblog-header-p";
        $query_vars[] = "kblog-header-author";
        
        return $query_vars;
    }

    function ogp_template_redirect(){
        global $wp_query;

        if( array_key_exists( "kblog-header-p", $wp_query->query_vars ) &&
            array_key_exists( "kblog-header-author", $wp_query->query_vars ) ){

            $postid = $wp_query->query_vars[ "kblog-header-p" ];
            // this is NOT NOT NOT the wordpress author ID. Nor is it a
            // general author ID. It the number of the author for this post. 
            $author = $wp_query->query_vars[ "kblog-header-author" ];

            
            $kblog_authors = kblog_author_get_authors($postid);

            if( $author < count( $kblog_authors ) ){
                $kblog_author_meta = $kblog_authors[$author];
                
                list($firstname,$lastname) = $this->kblog_metadata_first_name_last_name( $kblog_author_meta );
                    

                echo <<< EOT
<html xmlns:profile="http://ogp.me/ns/profile#">
<head>
  <meta property="profile:first_name" content="$firstname"/>
  <meta property="profile:last_name" content="$lastname"/>
</head>
<body> 
<p>This is an automatically generated profile page for 
$firstname $lastname</p>
</html>
EOT;
                
            }
            
            exit();

            
        }
    }
    // end open graph protocol
    
    

    // Generally useful functions
    function kblog_metadata_generate_metatags($meta_key, $metadata_items){
        $metadata = "";
        foreach( $metadata_items as $item ){
            // single element for each
            foreach( $item as $key=>$value ){
                // <meta name="resource_type" content="knowledgeblog"/>
                $metadata .=  $this->kblog_metadata_meta_tag( $meta_key, $key, $value );
            }
        }
        return $metadata;
    }

    function kblog_metadata_meta_tag($meta_key, $key, $value){
        return '<meta ' . $meta_key . '="' . $key . 
            '" content="' . $value . '"/>' . "\n";
    }
    
    function kblog_metadata_first_name_last_name( $author ){
        if( array_key_exists( "first_name", $author ) &&
            array_key_exists( "last_name", $author ) ){
            $firstname = $author[ "first_name" ];
            $lastname = $author[ "last_name" ];
        }
        else{
            // 2. instantiate the parser, passing the (utf8-encoded) name you want to parse
            try{
                $parser = new HumanNameParser_Parser( $author[ "display_name" ] );
                $firstname = $parser->getFirst();
                $lastname = $parser->getLast();
            }
            catch(Exception $e){
                // cannot sanely parse the name, so set the display name to the last name
                // and hope for the best
                $firstname = "";
                $lastname = $author[ "display_name" ];
            }
        }

        return array( $firstname, $lastname );
    }

    function kblog_metadata_concat_name( $author ){
        if( array_key_exists( "first_name", $author ) &&
            array_key_exists( "last_name", $author ) ){
            return $author["first_name"] . " " . $author[ "last_name" ];
        }
        return $author["display_name"];
    }
    
    /*
     * Fetch the post ID. Works outside the loop
     */
    function kblog_metadata_get_the_ID(){
        global $wp_query;
        return $wp_query->post->ID;
    }

    
    /*
     * Fetch the permalink. Works outside the loop. 
     */
    function kblog_metadata_get_permalink(){
        return get_permalink( $this->kblog_metadata_get_the_ID() );
    }



    /* 
     * Fetch the title. Works outside the loop
     */
    function kblog_metadata_get_the_title(){
        return get_the_title( $this->kblog_metadata_get_the_ID() );
    }
    

    /*
     * Fetch the authors, as an array, using field names from 
     * get_the_author_meta.
     */
    function kblog_metadata_get_authors()
    {
        return
            kblog_author_get_authors( $this->kblog_metadata_get_the_ID () );
    }

    
    function get_container_title(){
        return 
            kblog_title_get_container_title( $this->kblog_metadata_get_the_ID() );
    }

    function htmlentities( $string ){
        // ENT_COMPAT | ENT_HTML401 is default argu
        // "UTF-8" may be depending on PHP version, but it might also
        // be iso-8859-1, which will result in some strange things happening. 
        return htmlentities( $string,
                             ENT_COMPAT | ENT_HTML401, 
                             "UTF-8"
                             );
    }

}



function kblog_headers_init(){
    global $kblog_headers;
    $kblog_headers = new kblog_headers();
}

kblog_headers_init();

?>
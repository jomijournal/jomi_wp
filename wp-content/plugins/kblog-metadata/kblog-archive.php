<?php

class kblog_archive{
    
    function __construct(){
        wp_register_sidebar_widget( 'kblog-archive',
                                    "Archived",
                                    array( $this, 'widget'),
                                    array( "description" => "Show location of web archived versions ".
                                           "of posts")
                                    );
    }


    function archive_sites($url){
        $json = $this->get_archive_json($url);
        $sites = json_decode( $json );
        
        return $sites;
        
    }

    function get_archive_json($url){
        // kill the transients if necessary!
        //delete_transient( $this->transient_slug($url) );
        $option = get_transient( $this->transient_slug( $url ) );
        if( !$option ){
            $greycite_url = "http://greycite.knowledgeblog.org/archives?uri=" . $url;
            //$greycite_url = "http://zerg32.ncl.ac.uk/archives.html";
            
            $wp_response = wp_remote_get( $greycite_url );
            
            if( is_wp_error( $wp_response ) ){
                return;
            }
            $status = wp_remote_retrieve_response_code( $wp_response );

            if( $status == 200 ){ 
                $option = wp_remote_retrieve_body( $wp_response );
                set_transient( $this->transient_slug( $url ), $option, 60 * 60 * 24 * 7 );
            }
        }
        
        return $option;        
    }
    

    function transient_slug($url){
        // slug has to be 45 chars or less
        return crc32( "kblog-archive" . $url );
    }


    function widget($args){
        extract($args);
        
        if( is_single() ){
            $archives = $this->archive_sites( get_permalink() );
        }
        else{
            $archives = $this->archive_sites( home_url() );
        }
        
        $archive_string = "";

        if( count( $archives ) > 0 ){
            
            $archive_string .= "<ul>\n";
            foreach( $archives as $archive ){
                $archive_string .=
                    "<li><a href=\"" . $archive[ 1 ] . 
                    "\">". $archive[ 0 ] . "</a></li>\n";
                
            }
            $archive_string .= "</ul>\n";
            
        }
        else{
            $archive_string = "No current archives";
        }

        
        echo <<< EOT
$before_widget
$before_title Archived $after_title
$archive_string
EOT;
    }
    



}




function kblog_archive_init(){
    global $kblog_archive;
    $kblog_archive = new kblog_archive();
}

kblog_archive_init();
?>
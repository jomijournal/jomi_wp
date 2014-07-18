<?php

// Attaches a boiler plate to posts with basic metadata

class kblog_boilerplate{
    function __construct(){
        add_option( "kblog_boilerplate_in_content", false );
        
        // disable by default -- boilerplate in content on an option I think
        add_filter( 'the_content', array( $this, 'add_boilerplate' ) );
        wp_register_sidebar_widget('kblog-boilerplate',
                                   'Citation',
                                   array( $this, 'widget'),
                                   array(
                                         "description" => "Displays Citation information for " .
                                         "either the resource or the individual article",
                                         ) 
                                   );

    }

    function add_boilerplate($content){
        if( get_option( "kblog_boilerplate_in_content" ) ){
                return $content . 
                    "\n<div class=\"kcite-boilerplate\">" .
                    $this->get_cite_single() .
                    "</div>\n";
        }
        return $content;            
    }
    
    function get_cite_multi(){

        $title = kblog_title_get_container_title();
        $permalink = get_home_url();
        
        
        $boilerplate = <<<EOT
Please cite this resource as:
$title
<a href="$permalink">$permalink</a>

EOT;

        return $boilerplate;
    }

    function get_cite_single(){
        $title = kblog_title_get_container_title();
        $article = get_the_title();
        $authors = kblog_author_author();
        $year = get_the_time( 'Y' );
        $permalink = get_permalink();
        
        
        $boilerplate = <<<EOT
Please cite this article as:
$authors ($year) $article. <i>$title</i>.
<a href="$permalink">$permalink</a>

EOT;

        return $boilerplate;
    }


    function widget($args) {
        extract($args);
        // add a content filter or something, so I can get the ID of the first post displayed on a multi page. 
        // or could add different function on multi page?
        $boilerplate = (is_single() || is_page()) ?
            $this->get_cite_single() : $this->get_cite_multi();
        
        
        echo <<< EOT
$before_widget
$before_title Citation $after_title
$boilerplate

EOT;
        if(is_single() || is_page()){
            $permalink = get_permalink();
            echo <<< EOT
<p>
EOT;
        }

        echo $after_widget;

    }



}


function kblog_boilerplate_init(){
    global $kblog_boilerplate;
    $kblog_boilerplate = new kblog_boilerplate();
}

kblog_boilerplate_init();

// export interface

function kblog_the_boilerplate(){
    echo kblog_get_the_boilerplate();
}

function kblog_get_the_boilerplate(){
    global $kblog_boilerplate;
    return $kblog_boilerplate.get_cite_multi();
}

class kblog_boilerplate_admin{
    function __construct(){
        add_action( "kblog_metadata_admin_render", array( $this, "options_render" ) );
        add_action( "kblog_metadata_admin_save", array( $this, "options_save" ) );
    }

    function options_save(){
        if( array_key_exists( "kblog_boilerplate_in_content", $_POST ) ){
            if( !get_option( "kblog_boilerplate_in_content" ) ){
                echo "<p><i>Citation Options Updated</i><p>";
                update_option( "kblog_boilerplate_in_content", true );
            }
        }
        else{
            if( get_option( "kblog_boilerplate_in_content" ) ){
                echo "<p><i>Citation Options Updated</i><p>";
                update_option( "kblog_boilerplate_in_content", false );
            }
        }
    }



    function options_render()
    {
        if( !current_user_can('manage_options')){
            wp_die( __('You do not have sufficient permissions to access this page.'));
        }
        
        $boilerplatep = "";
        if( get_option( "kblog_boilerplate_in_content", false ) ){
            $boilerplatep = 'checked="true"';
        } 
        

        $out .= <<<EOT
            
<h3>Citation</h3>
<p>Include Citation in post content?
<input type="checkbox" name="kblog_boilerplate_in_content" value="true" $boilerplatep/>
</p> 

EOT;
        echo $out;
    }

}

function kblog_boilerplate_admin_init(){
    global $kblog_boilerplate_admin;
    $kblog_boilerplate_admin = new kblog_boilerplate_admin();
}

kblog_boilerplate_admin_init();


?>

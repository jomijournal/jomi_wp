<?php

class kblog_transclude{

    function __construct(){
        add_action( "template_redirect", 
                    array( $this, "template_redirect" ) );
        add_filter( "query_vars", 
                    array( $this, "query_vars" ) );
    }
    
    function query_vars($query_vars){
        $query_vars[] = "kblog-transclude";
        return $query_vars;
    }

    function template_redirect() {
        if( is_single() && get_query_var( "kblog-transclude" ) > 0 ){
            // tell kcite not to use javascript
            if( function_exists( "kcite_no_javascript" ) ){
                kcite_no_javascript();
            }
            
            // get the content with as little else as possible
            if( get_query_var( "kblog-transclude" ) == 1 ){
                // run the loop with nothing else. 
                if (have_posts()){
                    while (have_posts()){
                        the_post();
                        the_content();
                    }
                }
                // and terminate
                exit();
            }
            
            // same as 1 but as valid html
            if( get_query_var( "kblog-transclude" ) == 2 ){
                if( have_posts() ){
                    while (have_posts()){
                        the_post();
                        $this->simple_head();
                        the_content();
                        $this->simple_foot();
                    }
                }
                exit();
            }
        }
    }


    function simple_head(){
        $title = get_the_title();
        print <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head><title>$title</title>
EOF;

        // deliberately don't do wp_head because it does lots of things, but still add kblog metadata
        do_action( "kblog_head" );

        print<<<EOF
</head>
<body>
<h1>$title</h1>
EOF;
    }

    function simple_foot(){
        print <<<EOF
</body>
</html>
EOF;
    }

}


function kblog_transclude_init(){
    global $kblog_transclude;
    $kblog_transclude = new kblog_transclude();
}

kblog_transclude_init();

?>
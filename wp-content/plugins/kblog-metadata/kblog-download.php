<?php

class kblog_download{

  function __construct(){
      wp_register_sidebar_widget( 'kblog-download',
                                'Download',
                                array( $this, 'widget'),
                                array( "description" => "Download options for the article or " .
                                       "the resource as a whole" ) 
                                );
  }

  
  function get_resource(){
    $home_url = urlencode( home_url() . "?kblog-toc=txt" );
    
    $retn = <<<EOT
<a href="http://greycite.knowledgeblog.org/bib?list=$home_url">[bib]</a>

EOT;
      
    return $retn;
  }

  function get_single(){
    $permalink = urlencode( get_permalink() );
    if( strpos( get_permalink(), "?" ) === false ){
        $simple_url = get_permalink() .  "?kblog-transclude=2";
    }
    else{
        $simple_url = get_permalink() . "&kblog-transclude=2";
    }
    
    $retn = <<<EOT
<ul>
<li><a href="http://greycite.knowledgeblog.org/bib?uri=$permalink">[bib]</a></li>
<li><a href="$simple_url">[Simple HTML]</a></li>
</ul>
EOT;
    
    return $retn;
  }

  function widget($args){
    extract($args);

    if(is_single() || is_page()){
      $download = $this->get_single();
      $resource = "article";
    }
    else{
      $download = $this->get_resource();
      $resource = "resource";
    }
    
  
    echo <<< EOT
$before_widget
$before_title Download $after_title 
Download $resource as:
$download
EOT;

  }
}


function kblog_download_init(){
  global $kblog_download_init;
  $kblog_download = new kblog_download();
}

kblog_download_init();

?>
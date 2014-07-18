<?php
/*
  Plugin Name: Kblog Metadata
  Plugin URI: http://www.knowledgeblog.org
  Description: Tools for exposing and editing the bibliographic metadata of academic posts.
  Version: 0.6
  Author: Phillip Lord
  Author URI: http://www.knowledgeblog.org
  Email: knowledgeblog@googlegroups.com

  Copyright 2011 Phillip Lord (phillip.lord@newcastle.ac.uk)
  Newcastle University

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

require_once( dirname( __FILE__ ) . "/kblog-author.php" );
require_once( dirname( __FILE__ ) . "/kblog-table-of-contents.php" );
require_once( dirname( __FILE__ ) . "/kblog-headers.php" );
require_once( dirname( __FILE__ ) . "/kblog-title.php" );
require_once( dirname( __FILE__ ) . "/kblog-boilerplate.php" );
require_once( dirname( __FILE__ ) . "/kblog-transclude.php" );
require_once( dirname( __FILE__ ) . "/kblog-download.php" );
require_once( dirname( __FILE__ ) . "/kblog-archive.php" );

/*
 * A single admin page for all metadata functions
 *
 */
class kblog_metadata_admin{

    function __construct(){
        add_action( "admin_menu", array( $this, "admin_page_init" ) );
    }

    function admin_page_init(){
        add_options_page("Kblog Metadata", "Kblog Metadata",
                         "manage_options", "kblog-metadata",
                         array($this, "plugin_options_menu") );

    }

    function plugin_options_menu(){

        if( !current_user_can('manage_options')){
            wp_die( __('You do not have sufficient permissions to access this page.'));
        }

        echo "<h2>Kblog Metadata</h2>";

        if( wp_verify_nonce( $_POST["kblog_metadata_admin_save_field"],
                             "kblog_metadata_admin_save_action" ) ){
            do_action( "kblog_metadata_admin_save" );
        }


        $nonce = wp_nonce_field( "kblog_metadata_admin_save_action",
                                 "kblog_metadata_admin_save_field",
                                 true, false );

        echo '<form id="kblog-metadata" name="kblog-metadata" action="" method="POST">';
        // let everything else run
        do_action( "kblog_metadata_admin_render" );

        // internationalise for the hell of it.
        $save_changes = __("Save Changes");

        echo <<<EOT
            $nonce
            <p class="submit">
            <input type="submit" class="button-primary" value="$save_changes"/>
            </p>
            </form>
EOT;

    }
}

function kblog_metadata_admin_init(){
    global $kblog_metadata;
    $kblog_metadata = new kblog_metadata_admin();
}


if( is_admin() ){
    kblog_metadata_admin_init();
}

?>
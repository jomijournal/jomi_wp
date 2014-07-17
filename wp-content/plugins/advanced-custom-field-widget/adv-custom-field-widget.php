<?php
/*
Plugin name: Advanced Custom Field Widget
Plugin uri: http://athena.outer-reaches.com/wiki/doku.php?id=projects:acfw:home
Description: Displays the values of specified <a href="http://codex.wordpress.org/Using_Custom_Fields">custom field</a> keys, allowing post- and page-specific meta content in your sidebar. This plugin started life as a plaintxt.org experiment for WordPress by Scott Wallick, but I needed (or wanted) it to do more, so I've created this version which has more functionality than the original.  For some detailed instructions about it's use, check out my <a href="http://athena.outer-reaches.com/wiki/doku.php?id=projects:acfw:home">wiki</a>.  To report bugs or make feature requests, visit the Outer Reaches Studios <a href="http://mantis.outer-reaches.co.uk">issue tracker</a>, you will need to signup an account to report issues.
Author: Christina Louise Warne
Author uri: http://athena.outer-reaches.com/
Version: 0.991
*/

/*
ADVANCED CUSTOM FIELD WIDGET
by Christina Louise Warne (aka AthenaOfDelphi), http://athena.outer-reaches.com/
from The Outer Reaches, http://www.outer-reaches.com/

Based on the original CUSTOM FIELD WIDGET,  by SCOTT ALLAN WALLICK, http://scottwallick.com/
from PLAINTXT.ORG, http://www.plaintxt.org/.

ADVANCED CUSTOM FIELD WIDGET is free software: you can redistribute it
and/or modify it under the terms of the GNU General Public License as
published by the Free Software Foundation, either version 3 of
the License, or (at your option) any later version.

ADVANCED CUSTOM FIELD WIDGET is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty
of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for details.

You should have received a copy of the GNU General Public License
along with ADVANCED CUSTOM FIELD WIDGET.
If not, see www.gnu.org/licenses/
*/

define( 'ACFWBLOGLINK', 'http://athena.outer-reaches.com/wp/index.php/wiki/advanced-custom-field-widget' );
define( 'ACFWWIKILINK', 'http://athena.outer-reaches.com/wiki/doku.php?id=projects:acfw:home' );
define( 'ACFWTEXTDOMAIN', 'adv-custom-field-widget' );
define( 'CODEXCUSTOMFIELDLINK', 'http://codex.wordpress.org/Using_Custom_Fields' );

// Load a list of values for a field
function acfw_loadlist( $values, $acfwsep ) {
    $itemsep = "";
    $lastitemsep = "";
    $ending = "";
    
    // Capture the inter-item separator
    $idx = strpos( $acfwsep, '|' );
    if ( ! ( $idx === false ) ) {
        if ( $idx > 0 ) {
            $itemsep = substr( $acfwsep, 0, $idx );    
        }
        
        $acfwsep = substr( $acfwsep, $idx + 1, strlen( $acfwsep ) );
    } else {
        if ( $acfwsep != "" ) {
            $itemsep = $acfwsep;
            $acfwsep = "";
        }
    }
    
    // Capture the last inter-item separator
    $idx = strpos( $acfwsep, '|' );
    if ( ! ( $idx === false ) ) {
        if ( $idx > 0 ) {
            $lastitemsep = substr ( $acfwsep, 0, $idx );            
        } else {
            $lastitemsep = $itemsep;
        }
        
        $acfwsep = substr( $acfwsep, $idx + 1, strlen( $acfwsep ) );
        
        // Capture the ending
        if ( $acfwsep != "" ) {
            $ending = $acfwsep;
        }
    } else {
        if ( $acfwsep != "" ) {
            $lastitemsep = $acfwsep;
        } else {
            $lastitemsep = $itemsep;
        }
    }

    $itemsleft = count( $values );
    $temp = "";
    
    foreach ( $values as $key => $value ) {
        if ( $temp != '' ) {
            if ( $itemsleft > 1 ) {
                $temp .= $itemsep;
            } else {
                $temp .= $lastitemsep;
            }
        }
                
        $temp .= $value;
        $itemsleft = $itemsleft - 1;
    }
    
    if ( ( $temp != "" ) && ( $ending != "" ) ) {
        $temp .= $ending;
    }
    
    return $temp;
}

// Load a single custom field for the specified post from the system
function acfw_loadsinglefield( $acfwpostid, $acfwkey, $acfwloadall, $acfwsep, &$acfwfield)
{   
    $temp = '';

    if ( $acfwkey != '' ) {
        $values = get_post_meta( $acfwpostid, $acfwkey, false );
        
        if ( ! empty( $values ) ) {
            if ( $acfwloadall ) {
                $temp = acfw_loadlist( $values, $acfwsep );
            } else {
                $temp = $values[0];
            }
        }
    }
    
    $acfwfield = $temp;
}

// Load all custom fields for the specified post from the system
function acfw_loadallfields( $acfwpostid, $acfwloadall, $acfwsep, &$container )
{
    $temp = get_post_custom( $acfwpostid );
    
    if ( $temp ) {
        foreach ( $temp as $key => $value ) {
            if ( is_array($value) ) {
                if ( $acfwloadall ) {
                    $container[ $key ] = acfw_loadlist( $values, $acfwsep );
                } else {
                    $container[ $key ] = $value[0];
                }
            } else {
                $container [ $key ] = $value;
            }
        }
    }
}

// Load a set of configuration data for the specified data/key field
function acfw_loadkeyconfig( $options, $number, $keyid, &$datakey, &$datakeyloadall, &$datakeysep )
{
    $datakey = '';
    $datakeyloadall = false;
    $datakeysep = '';
    
    $temp = $options[$number][$keyid.'key'];
    $datakey = esc_attr( $temp );
    if ( $datakey != '' ) {
        $temp = $options[$number][$keyid.'keyloadall'];
        $datakeyloadall = ! empty( $temp );
        $temp = $options[$number][$keyid.'keysep'];
        $datakeysep = esc_attr( $temp );
    }
}

// Generate the control panel edit section for the specified data/key field
function acfw_editfield( $number, $mainlabel, $keyid, $datakey, $datakeyloadall, $datakeysep )
{
?>
        <label for="adv-custom-field-<?php echo $keyid; ?>key-<?php echo $number; ?>"><?php echo $mainlabel; ?></label><br />
        <input id="adv-custom-field-<?php echo $keyid; ?>key-<?php echo $number; ?>" name="widget-adv-custom-field[<?php echo $number; ?>][<?php echo $keyid; ?>key]" class="code" size="25" type="text" value="<?php echo $datakey; ?>" />
        <label for="adv-custom-field-<?php echo $keyid; ?>keyloadall-<?php echo $number; ?>"><?php _e( 'All items', ACFWTEXTDOMAIN ) ?></label>
        <input type="checkbox" id="adv-custom-field-<?php echo $keyid; ?>keyloadall-<?php echo $number; ?>" name="widget-adv-custom-field[<?php echo $number; ?>][<?php echo $keyid; ?>keyloadall]" <?php if ( $datakeyloadall ) echo "checked"; ?> />
        <label for="adv-custom-field-<?php echo $keyid; ?>keyseparator-<?php echo $number; ?>"><?php _e( 'Separator', ACFWTEXTDOMAIN ) ?></label>
        <input id="adv-custom-field-<?php echo $keyid; ?>keyseparator-<?php echo $number; ?>" name="widget-adv-custom-field[<?php echo $number; ?>][<?php echo $keyid; ?>keyseparator]" class="code" size="25" type="text" value="<?php echo $datakeysep; ?>" />
<?php
}

// Run the provided data through the required filters
function acfw_filterfield( $dontfilter, $dontconvert, &$data, $doreplace = false )
{
    if ( !$dontconvert ) {
        // Convert chars
        $data = apply_filters( 'adv_custom_field_value1', $data );
    }
    
    // Strip slashes
    $data = apply_filters( 'adv_custom_field_value2', $data );
    
    if ( !$dontfilter ) {
        // WP Texturize
        $data = apply_filters( 'adv_custom_field_value3', $data );
    }
    $data = addslashes( $data );
    
    if ( $doreplace ) {
        $data = str_replace( chr(13).chr(10), chr(10), $data );
        $data = str_replace( chr(10).chr(13), chr(10), $data );
        $data = str_replace( chr(10), '\n', $data );
    }
}

// Widget Display Function for the Advanced Custom Field Widget
function wp_widget_adv_custom_field( $args, $widget_args = 1 ) {
	// Get hold of the global WP database object
	global $wpdb;
    
	// Let's begin our widget.
	extract( $args, EXTR_SKIP );
    
	// Our widgets are stored with a numeric ID, process them as such
	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
    
	// We'll need to get our widget data by offsetting for the default widget
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
    
	// Offset for this widget
	extract( $widget_args, EXTR_SKIP );
    
	// We'll get the options and then specific options for our widget further below
	$options = get_option('widget_adv_custom_field');
    
	// If we don't have the widget by its ID, then what are we doing?
	if ( ! isset( $options[$number] ) ) {
		return;
    }
    
	// Load the initial values for our variables
	$ckey  = $options[$number]['key'];
	$skey  = $options[$number]['skey'];

	$data1key = $options[$number]['data1key'];
    $data2key = $options[$number]['data2key'];
    $data3key = $options[$number]['data3key'];
    $data4key = $options[$number]['data4key'];
    $data5key = $options[$number]['data5key'];
    
    $data1keyloadall      = ! empty( $options[$number]['data1keyloadall'] );
    $data1keysep          = apply_filters( 'widget_text', $options[$number]['data1keysep'] );
    $data2keyloadall      = ! empty( $options[$number]['data2keyloadall'] );
    $data2keysep          = apply_filters( 'widget_text', $options[$number]['data2keysep'] );
    $data3keyloadall      = ! empty( $options[$number]['data3keyloadall'] );
    $data3keysep          = apply_filters( 'widget_text', $options[$number]['data3keysep'] );
    $data4keyloadall      = ! empty( $options[$number]['data4keyloadall'] );
    $data4keysep          = apply_filters( 'widget_text', $options[$number]['data4keysep'] );
    $data5keyloadall      = ! empty( $options[$number]['data5keyloadall'] );
    $data5keysep          = apply_filters( 'widget_text', $options[$number]['data5keysep'] );
    $keyloadall           = ! empty( $options[$number]['keyloadall'] );
    $keysep               = apply_filters( 'widget_text', $options[$number]['keysep'] );
    $skeyloadall          = ! empty( $options[$number]['skeyloadall'] );
    $skeysep              = apply_filters( 'widget_text', $options[$number]['skeysep'] );
	$title                = apply_filters( 'widget_title', $options[$number]['title'] );
	$text                 = apply_filters( 'widget_text', $options[$number]['text'] );
	$pretext              = apply_filters( 'widget_text', $options[$number]['pretext'] );
	$posttext             = apply_filters( 'widget_text', $options[$number]['posttext'] );
    $loadallcustom        = ! empty( $options[$number]['loadallcustom'] );
    $loadallcustomloadall = ! empty( $options[$number]['loadallcustomloadall'] );
    $loadallcustomsep     = apply_filters( 'widget_text', $options[$number]['loadallcustomsep'] );
    $contentgenscript     = ! empty( $options[$number]['contentgenscript'] );
    
    $data1='';
    $data2='';
    $data3='';
    $data4='';
    $data5='';
    
	$cvalue = '';
	$fixedtext1 = '';
	$fixedtext2 = '';
	$pagetitle = '';
    $pageurl = '';
    $srcpost = 0;
    $blocked = FALSE;
    
    if ( isset( $loadothers ) ) {
        unset( $loadothers );
    }

    // Version 0.9 - Removed the reliance on the main query.
    //
    // The reason for this is that if the widget is used inside the loop,
    // resetting the main query will result in the page never ending.
    // Loop displays the widget, widget resets the query, loop looks for
    // the last post, it's not found and so the process begins again
    //
 	// Reinitialise the main query and the $post variable
    //global $post,$wp,$wp_the_query;
    //$wp->query_posts();
    //setup_postdata($wp_the_query->post);
    //
    // So, what we do instead is create our own query and initialise
    // it just like the main query and instead of referencing the
    // $post variable we use $ourpost
    
    global $wp;
    $originalquerystring = $wp->query_string;
    $originalqueryvars = $wp->query_vars;
    
    $wp->build_query_string();
    $realpostlist = new WP_Query();
    $realpostlist->query( $wp->query_vars );
    
    $wp->query_string = $originalquerystring;
    $wp->query_vars = $originalqueryvars;
    
    if ( $realpostlist->post_count == 0 ) {
        return;
    }
    
    $ourpost = $realpostlist->post;
	
	// Are we on a single post page (i.e. a blog entry or a page) or not?
	if (is_single()||is_page()) {
		// Look first for say 'externallinks-linkto' allowing us to link up a single
		// field
        $linkto = get_post_meta( $ourpost->ID, $ckey.'-linkto', true);
		
		if ( empty( $linkto ) ) {
		    // If <KEY>-linkto is empty, look for the general acfw-linkto custom field
			$linkto = get_post_meta( $ourpost->ID, 'acfw-linkto', true);	
		}
		
		// If we have a source page ID (from the linkto fields) then set the data source ID to
		// that, otherwise set the date source ID to the current post ID
		if ( !empty( $linkto ) )
		{
			//  Check if the 'linkto' field has more than one page in it
			if ( strpos( $linkto, '|' ) ) {
				$temp = explode( '|', $linkto );
				
				if ( !empty( $options[$number]['widgetindex'] ) ) {
					$sourceindex = $options[$number]['widgetindex']-1;
				} else {
					$sourceindex = 0;
				}
                
                // Check our source index doesn't run past the end of the end of
                // the list of source pages
				if ( $sourceindex >= 0 && $sourceindex < count($temp) ) {
                    if ( isset( $temp[$sourceindex]) ) {
                        $srcpost = $temp[$sourceindex];
                    }
				}	
			} else {
				$srcpost = $linkto;
			}
		}
		else
		{
			$srcpost = $ourpost->ID;

			// Restrict indexed widgets to the first one only if we aren't linked
			if ( isset( $options[$number]['widgetindex'] ) ) {
                if ( $options[$number]['widgetindex'] != '' ) {
                    if ( $options[$number]['widgetindex'] != 1 ) {
				        $srcpost = 0;
                        
                        // Block random content for indexed widgets other than 1
					    $blocked = TRUE;
                    }
				}
			} 
		}
		
		// Load the data from the target page
		if ( isset( $ckey ) ) {
			if ( $ckey != '' ) {
				// $cvalue=get_post_meta( $srcpost, $ckey, true );
                acfw_loadsinglefield( $srcpost, $ckey, $keyloadall, $keysep, $cvalue );
			}
		}
			
	    // Check to see if we read anything, if we didn't
		if ( empty( $cvalue ) ) {
			if ( isset( $skey ) ) {
				if ( $skey != '' ) {
					// Try loading the main content from the secondary field
                    acfw_loadsinglefield( $srcpost, $skey, $skeyloadall, $skeysep, $cvalue );
				}
			}
		}
	
		// We are on a single page, and we have some content, so we need to cancel
        // the randomisation and setup a few more of our variables
		if ( ! empty( $cvalue ) ) {
            $postdata = get_post( $srcpost );
            $pageurl = get_permalink( $srcpost );
            $pagetitle = $postdata->post_title;
            $loadothers = $srcpost;
            $dorandom = false;            
		} else {
            // Read the randomisation settings from the configuration
            $dorandom = !empty( $options[$number]['dorandomsingle'] );
        }
	} else {
		// We are on a multi post page, so get the randomisation setting and clean $cvalue
		$dorandom = !empty( $options[$number]['dorandomother'] );
		$cvalue = '';
	}
			
	// Load our fixedtext1 and 2 with their 'ALWAYS' values
	$fixedtext1 = $options[$number]['fixedtext1a'];
	$fixedtext2 = $options[$number]['fixedtext2a'];
	
	// Load our $contentgen field
	$contentgen = $options[$number]['contentgen'];
	
	// Load the 'don't filter' configuration flag
	$dontfilter = ! empty( $options[$number]['dontfilter'] );
    
    // Load the 'don't convert' configuration flag
    $dontconvert = ! empty( $options[$number]['dontconvert'] );
		
	// If we are loading random content
	if ( $dorandom && ! $blocked ) {
		// Randomise our main content
		$randomlist = $wpdb->get_results(
			"SELECT
			    p.id,
				m.meta_id,
				m.meta_value
			FROM
				$wpdb->postmeta m,
				$wpdb->posts p
			WHERE
				(p.id=m.post_id) and
				(p.post_status='publish') and 
				(m.meta_key='$ckey')
			ORDER BY rand()
			LIMIT 1");
			
		if ( $randomlist ) {
			foreach ( $randomlist as $metarec ) {
				$cvalue = $metarec->meta_value;
                $postdata = get_post( $metarec->id );                
                $pageurl = get_permalink( $metarec->id );
				$pagetitle = $postdata->post_title;
                $loadothers = $metarec->id;
			}
		}
			
		if ( ! empty( $cvalue ) ) {
			// We have some main content, so load our random fixed text if we haven't already been loaded with the 'ALWAYS' option
			if ( empty( $fixedtext1 ) ) {
				$fixedtext1 = $options[$number]['fixedtext1r'];
			}
			if ( empty($fixedtext2 ) ) {
				$fixedtext2 = $options[$number]['fixedtext2r'];
			}
		}
	}
	else
	{	
		if ( empty( $cvalue ) ) {
			// Load our 'no main content' fixed text items if they aren't loaded with the 'ALWAYS' options
			if ( empty( $fixedtext1 ) ) {
				$fixedtext1 = $options[$number]['fixedtext1n'];
			}
			if ( empty( $fixedtext2 ) ) {
				$fixedtext2 = $options[$number]['fixedtext2n'];
			}
		} else {
			// Load our 'main content' fixed text items if they aren't loaded with the 'ALWAYS' options
			if ( empty( $fixedtext1 ) ) {
				$fixedtext1 = $options[$number]['fixedtext1m'];
			}
			if ( empty( $fixedtext2 ) ) {
				$fixedtext2 = $options[$number]['fixedtext2m'];
			}
		}
	}
    
	// Apply the widget text filters to our fixed text fields (if they are present)
	if ( ! empty( $fixedtext1 ) ) {
		$fixedtext1 = apply_filters( 'widget_text', $fixedtext1 );
	}
	if ( ! empty( $fixedtext2 ) ) {
		$fixedtext2 = apply_filters( 'widget_text', $fixedtext2 );
	}

	if ( ! empty( $cvalue ) || ! empty( $fixedtext1 ) || ! empty( $fixedtext2 ) ) {
		// If we have a content generator, then perform the required
        // filtering on the data
		if ( isset( $contentgen ) && $contentgen != '' ) {
            
            // Load the additional data fields for the content generator
            if ( isset( $loadothers ) ) {
                if ( $loadallcustom ) {
                    acfw_loadallfields( $loadothers, $loadallcustomloadall, $loadallcustomsep, $custom );        
                } else {
                    acfw_loadsinglefield( $loadothers, $data1key, $data1keyloadall, $data1keysep, $data1 );
                    acfw_loadsinglefield( $loadothers, $data2key, $data2keyloadall, $data2keysep, $data2 );
                    acfw_loadsinglefield( $loadothers, $data3key, $data3keyloadall, $data3keysep, $data3 );
                    acfw_loadsinglefield( $loadothers, $data4key, $data4keyloadall, $data4keysep, $data4 );
                    acfw_loadsinglefield( $loadothers, $data5key, $data5keyloadall, $data5keysep, $data5 );
                }
            }
            
            // Switch the content and the content generator around
            $acfw_content = $cvalue;
            $cvalue = $contentgen;

            acfw_filterfield( $dontfilter, false, $acfw_content);
			acfw_filterfield( false, false, $pagetitle);
            
            if ( $loadallcustom ) {
                if ( count( $custom ) > 0 ) {
                    foreach ( $custom as $key => $value ) {
                        $safekey=str_replace(' ','',$key);
                        $safekey=str_replace('-','',$safekey);
                        
                        eval( '$acfw_' . $safekey . '=$custom["' . $key . '"];' );
                    }
                }
                
                unset( $custom );
            } else {
                acfw_filterfield( $dontfilter, false, $data1 );
                acfw_filterfield( $dontfilter, false, $data2 );
                acfw_filterfield( $dontfilter, false, $data3 );
                acfw_filterfield( $dontfilter, false, $data4 );
                acfw_filterfield( $dontfilter, false, $data5 );
            }
            
            $pageurl = addslashes( $pageurl );
		} else {
            $contentgenscript = false;
        }
        
        if ( $contentgenscript ) {
            $cvalue=str_replace('\n',chr(13).chr(10),$cvalue);
        } else {
            acfw_filterfield( $dontfilter, $dontconvert, $cvalue, true );
        }
	
		// Yes? Then let's make a widget. Open it.
		echo $before_widget;
		// Our widget title field is optional; if we have some, show it
		if ( $title ) {
			echo "\n$before_title $title $after_title";
		}
		
		// We have some fixed text, so show it
		if ( $fixedtext1 ) {
			echo $fixedtext1;
		}
		
		// We have some main content, so show it and the other related items
		if ( $cvalue ) {
			// Our widget text field is optional; if we have some, show it
			if ( $text ) {
				echo "\n<div class='textwidget'>\n$text\n</div>\n";
			}
		
			// If we have pretext, show it
			if ( $pretext ) {
				echo $pretext;
			}
            	
            if ($contentgenscript) {
                $content='';
                eval( $cvalue );
                $cvalue=$content;
            }
			
			$cvalue = do_shortcode( $cvalue );
			
			eval( '$cvalue="\n<div class=\"advcustomvalue\">\n' . $cvalue . '\n</div>\n";' );
            
			echo urldecode( stripslashes( $cvalue ) );
		
			if ( $posttext ) {
				echo $posttext;
			}
		}
			
		// We have some fixed text, so show it
		if ( $fixedtext2 ) {
			echo $fixedtext2;
		}
			
		// Close our widget.
		echo $after_widget;
	}
	// And we're finished with the actual widget
}

// Function for the Advanced Custom Field Widget options panels
function wp_widget_adv_custom_field_control( $widget_args ) {
	// Establishes what widgets are registered, i.e., in use
	global $wp_registered_widgets;
   
	// We shouldn't update, i.e., process $_POST, if we haven't updated
	static $updated = false;
	// Our widgets are stored with a numeric ID, process them as such
	if ( is_numeric( $widget_args ) )
		$widget_args = array( 'number' => $widget_args );
	// We can process the data by numeric ID, offsetting for the '1' default
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	// Complete the offset with the widget data
	extract( $widget_args, EXTR_SKIP );
	// Get our widget options from the databse
	$options = get_option( 'widget_adv_custom_field' );
	// If our array isn't empty, process the options as an array
	if ( !is_array( $options ) )
		$options = array();
	// If we haven't updated (a global variable) and there's no $_POST data, no need to run this
	if ( !$updated && ! empty( $_POST['sidebar'] ) ) {
		// If this is $_POST data submitted for a sidebar
		$sidebar = (string) $_POST['sidebar'];
		// Let's konw which sidebar we're dealing with so we know if that sidebar has our widget
		$sidebars_widgets = wp_get_sidebars_widgets();
		// Now we'll find its contents
		if ( isset( $sidebars_widgets[$sidebar] ) ) {
			$this_sidebar =& $sidebars_widgets[$sidebar];
		} else {
			$this_sidebar = array();
		}
		// We must store each widget by ID in the sidebar where it was saved
		foreach ( $this_sidebar as $_widget_id ) {
			// Process options only if from a Widgets submenu $_POST
			if ( 'wp_widget_adv_custom_field' == $wp_registered_widgets[$_widget_id]['callback'] && isset( $wp_registered_widgets[$_widget_id]['params'][0]['number'] ) ) {
				// Set the array for the widget ID/options
				$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
				// If we have submitted empty data, don't store it in an array.
				if ( !in_array( "adv-custom-field-$widget_number", $_POST['widget-id'] ) )
					unset( $options[$widget_number] );
			}
		}
		
		// If we are returning data via $_POST for updated widget options, save for each widget by widget ID
		foreach ( (array) $_POST['widget-adv-custom-field'] as $widget_number => $widget_adv_custom_field ) {
			// If the $_POST data has values for our widget, we'll save them
			if ( !isset( $widget_adv_custom_field['key'] ) && isset( $options[$widget_number] ) )
				continue;
                
			// Create variables from $_POST data to save as array below
			$key                  = strip_tags( stripslashes( $widget_adv_custom_field['key'] ) );
			$skey                 = strip_tags( stripslashes( $widget_adv_custom_field['skey'] ) );
			$title                = strip_tags( stripslashes( $widget_adv_custom_field['title'] ) );
            $dorandomsingle       = strip_tags( stripslashes( $widget_adv_custom_field['dorandomsingle'] ) );
            $dorandomother        = strip_tags( stripslashes( $widget_adv_custom_field['dorandomother'] ) );   
            $dontfilter           = strip_tags( stripslashes( $widget_adv_custom_field['dontfilter'] ) );
            $dontconvert          = strip_tags( stripslashes( $widget_adv_custom_field['dontconvert'] ) );
            $widgetindex          = strip_tags( stripslashes( $widget_adv_custom_field['widgetindex'] ) );
            $data1key             = strip_tags( stripslashes( $widget_adv_custom_field['data1key'] ) );
            $data2key             = strip_tags( stripslashes( $widget_adv_custom_field['data2key'] ) );
            $data3key             = strip_tags( stripslashes( $widget_adv_custom_field['data3key'] ) );
            $data4key             = strip_tags( stripslashes( $widget_adv_custom_field['data4key'] ) );
            $data5key             = strip_tags( stripslashes( $widget_adv_custom_field['data5key'] ) );
            $loadallcustom        = strip_tags( stripslashes( $widget_adv_custom_field['loadallcustom'] ) );
            $keyloadall           = strip_tags( stripslashes( $widget_adv_custom_field['keyloadall'] ) );
            $skeyloadall          = strip_tags( stripslashes( $widget_adv_custom_field['skeyloadall'] ) );
            $data1keyloadall      = strip_tags( stripslashes( $widget_adv_custom_field['data1keyloadall'] ) );
            $data2keyloadall      = strip_tags( stripslashes( $widget_adv_custom_field['data2keyloadall'] ) );
            $data3keyloadall      = strip_tags( stripslashes( $widget_adv_custom_field['data3keyloadall'] ) );
            $data4keyloadall      = strip_tags( stripslashes( $widget_adv_custom_field['data4keyloadall'] ) );
            $data5keyloadall      = strip_tags( stripslashes( $widget_adv_custom_field['data5keyloadall'] ) );
            $loadallcustomloadall = strip_tags( stripslashes( $widget_adv_custom_field['loadallcustomloadall'] ) );            
            $contentgenscript     = strip_tags( stripslashes( $widget_adv_custom_field['contentgenscript'] ) );
            
			// For the optional text, let's carefully process submitted data
			if ( current_user_can( 'unfiltered_html' ) ) {
				$text                 = stripslashes( $widget_adv_custom_field['text'] );
				$pretext              = stripslashes( $widget_adv_custom_field['pretext'] );
				$posttext             = stripslashes( $widget_adv_custom_field['posttext'] );
				$fixedtext1r          = stripslashes( $widget_adv_custom_field['fixedtext1r'] );
				$fixedtext1m          = stripslashes( $widget_adv_custom_field['fixedtext1m'] );
				$fixedtext1n          = stripslashes( $widget_adv_custom_field['fixedtext1n'] );
				$fixedtext1a          = stripslashes( $widget_adv_custom_field['fixedtext1a'] );
				$fixedtext2r          = stripslashes( $widget_adv_custom_field['fixedtext2r'] );
				$fixedtext2m          = stripslashes( $widget_adv_custom_field['fixedtext2m'] );
				$fixedtext2n          = stripslashes( $widget_adv_custom_field['fixedtext2n'] );
				$fixedtext2a          = stripslashes( $widget_adv_custom_field['fixedtext2a'] );
				$contentgen           = stripslashes( $widget_adv_custom_field['contentgen'] );
                $keysep               = stripslashes( $widget_adv_custom_field['keyseparator'] );
                $skeysep              = stripslashes( $widget_adv_custom_field['skeyseparator'] );
                $data1keysep          = stripslashes( $widget_adv_custom_field['data1keyseparator'] );
                $data2keysep          = stripslashes( $widget_adv_custom_field['data2keyseparator'] );
                $data3keysep          = stripslashes( $widget_adv_custom_field['data3keyseparator'] );
                $data4keysep          = stripslashes( $widget_adv_custom_field['data4keyseparator'] );
                $data5keysep          = stripslashes( $widget_adv_custom_field['data5keyseparator'] );
                $loadallcustomsep     = stripslashes( $widget_adv_custom_field['loadallcustomsep'] );
			} else {
                $text                 = stripslashes( wp_filter_post_kses( $widget_adv_custom_field['text'] ) );
                $pretext              = stripslashes( wp_filter_post_kses( $widget_adv_custom_field['pretext'] ) );
                $posttext             = stripslashes( wp_filter_post_kses( $widget_adv_custom_field['posttext'] ) );
                $fixedtext1r          = stripslashes( wp_filter_post_kses( $widget_adv_custom_field['fixedtext1r'] ) );
                $fixedtext1m          = stripslashes( wp_filter_post_kses( $widget_adv_custom_field['fixedtext1m'] ) );
                $fixedtext1n          = stripslashes( wp_filter_post_kses( $widget_adv_custom_field['fixedtext1n'] ) );
                $fixedtext1a          = stripslashes( wp_filter_post_kses( $widget_adv_custom_field['fixedtext1a'] ) );
                $fixedtext2r          = stripslashes( wp_filter_post_kses( $widget_adv_custom_field['fixedtext2r'] ) );
                $fixedtext2m          = stripslashes( wp_filter_post_kses( $widget_adv_custom_field['fixedtext2m'] ) );
                $fixedtext2n          = stripslashes( wp_filter_post_kses( $widget_adv_custom_field['fixedtext2n'] ) );
                $fixedtext2a          = stripslashes( wp_filter_post_kses( $widget_adv_custom_field['fixedtext2a'] ) );
                $contentgen           = stripslashes( wp_filter_post_kses( $widget_adv_custom_field['contentgen'] ) );
                $keysep               = stripslashes( wp_filter_post_kses( $widget_adv_custom_field['keyseparator'] ) );
                $skeysep              = stripslashes( wp_filter_post_kses( $widget_adv_custom_field['skeyseparator'] ) );
                $data1keysep          = stripslashes( wp_filter_post_kses( $widget_adv_custom_field['data1keyseparator'] ) );
                $data2keysep          = stripslashes( wp_filter_post_kses( $widget_adv_custom_field['data2keyseparator'] ) );
                $data3keysep          = stripslashes( wp_filter_post_kses( $widget_adv_custom_field['data3keyseparator'] ) );
                $data4keysep          = stripslashes( wp_filter_post_kses( $widget_adv_custom_field['data4keyseparator'] ) );
                $data5keysep          = stripslashes( wp_filter_post_kses( $widget_adv_custom_field['data5keyseparator'] ) );
                $loadallcustomsep     = stripslashes( wp_filter_post_kses( $widget_adv_custom_field['loadallcustomsep'] ) );                
			}
			
			// We're saving as an array, so save the options as such
			$options[$widget_number] = compact( 
				'key', 'title', 'text', 'skey', 'pretext', 'posttext', 'dorandomsingle', 
                'dorandomother', 'fixedtext1r', 'fixedtext1m', 'fixedtext1n', 'fixedtext1a',
				'fixedtext2r', 'fixedtext2m', 'fixedtext2n', 'fixedtext2a', 'contentgen',
				'data1key', 'dontfilter', 'widgetindex', 'data2key', 'data3key',
				'data4key', 'data5key', 'loadallcustom', 'keyloadall', 'keysep',
                'skeyloadall', 'skeysep', 'data1keyloadall', 'data1keysep',
                'data2keyloadall', 'data2keysep', 'data3keyloadall', 'data3keysep',
                'data4keyloadall','data4keysep', 'data5keyloadall', 'data5keysep',
                'loadallcustomloadall', 'loadallcustomsep', 'contentgenscript',
                'dontconvert'
				);
		}
		// Update our options in the database
		update_option( 'widget_adv_custom_field', $options );
		// Now we have updated, let's set the variable to show the 'Saved' message
		$updated = true;
	}
    
	// Variables to return options in widget menu below
	if ( -1 == $number ) {
        // This is a new widget instance, so load the defaults
		$key                  = '';
        $keyloadall           = false;
        $keysep               = '';
		$skey                 = '';
        $skeyloadall          = false;
        $skeysep              = '';
		$title  		      = '';
		$text   		      = '';
		$pretext 		      = '';
		$posttext             = '';		
		$fixedtext1n          = '';
		$fixedtext1m          = '';
		$fixedtext1r          = '';
		$fixedtext1a          = '';
		$fixedtext2n          = '';
		$fixedtext2m          = '';
		$fixedtext2r          = '';
		$fixedtext2a          = '';
		$dorandomsingle       = '';
		$dorandomother        = '';
		$contentgen           = '';
		$dontfilter		      = '';
        $dontconvert          = '';
		$widgetindex          = '';
        $data1key             = '';
        $data1keyloadall      = false;
        $data1keysep          = '';
        $data2key             = '';
        $data2keyloadall      = false;
        $data2keysep          = '';
        $data3key             = '';
        $data3keyloadall      = false;
        $data3keysep          = '';
        $data4key             = '';
        $data4keyloadall      = false;
        $data4keysep          = '';
        $data5key             = '';
        $data5keyloadall      = false;
        $data5keysep          = '';
        $loadallcustom        = '';
        $loadallcustomloadall = false;
        $loadallcustomsep     = '';
		$number               = '%i%';
        $contentgenscript     = false;
	} else {
        // Otherwise, this widget has stored options to return
		$title  		      = esc_attr( $options[$number]['title'] );
		$text                 = esc_attr( $options[$number]['text'] );
		$pretext  		      = esc_attr( $options[$number]['pretext'] );
		$posttext 		      = esc_attr( $options[$number]['posttext'] );
		$fixedtext1n          = esc_attr( $options[$number]['fixedtext1n'] );
		$fixedtext1m          = esc_attr( $options[$number]['fixedtext1m'] );
		$fixedtext1r          = esc_attr( $options[$number]['fixedtext1r'] );
		$fixedtext1a          = esc_attr( $options[$number]['fixedtext1a'] );
		$fixedtext2n          = esc_attr( $options[$number]['fixedtext2n'] );
		$fixedtext2m          = esc_attr( $options[$number]['fixedtext2m'] );
		$fixedtext2r          = esc_attr( $options[$number]['fixedtext2r'] );
		$fixedtext2a          = esc_attr( $options[$number]['fixedtext2a'] );
		$dorandomsingle       = ! empty( $options[$number]['dorandomsingle'] );
		$dorandomother        = ! empty( $options[$number]['dorandomother'] );
		$contentgen           = esc_attr( $options[$number]['contentgen'] );
        $dontfilter           = ! empty( $options[$number]['dontfilter'] );
        $dontconvert          = ! empty( $options[$number]['dontconvert'] );
        
        $widgetindex          = esc_attr( $options[$number]['widgetindex'] );
        $loadallcustom        = ! empty( $options[$number]['loadallcustom'] );
        $loadallcustomloadall = ! empty( $options[$number]['loadallcustomloadall'] );
        $loadallcustomsep     = esc_attr( $options[$number]['loadallcustomsep'] );
        $contentgenscript     = ! empty( $options[$number]['contentgenscript'] );
    
        acfw_loadKeyConfig( $options, $number, '', $key, $keyloadall, $keysep );
        acfw_loadKeyConfig( $options, $number, 's', $skey, $skeyloadall, $skeysep );
        acfw_loadKeyConfig( $options, $number, 'data1', $data1key, $data1keyloadall, $data1keysep );
        acfw_loadKeyConfig( $options, $number, 'data2', $data2key, $data2keyloadall, $data2keysep );
        acfw_loadKeyConfig( $options, $number, 'data3', $data3key, $data3keyloadall, $data3keysep );
        acfw_loadKeyConfig( $options, $number, 'data4', $data4key, $data4keyloadall, $data4keysep );
        acfw_loadKeyConfig( $options, $number, 'data5', $data5key, $data5keyloadall, $data5keysep );
	}
    
	// Generate the widget control panel
?>
    <div style="width:700px">
    <p><?php _e( 'ACFW instance ID (use this with shortcode and theme function):', ACFWTEXTDOMAIN ); ?> <b></i><?php if (is_numeric( $number )) { echo $number; } else { _e( '(Unknown - Save the configuration)', ACFWTEXTDOMAIN ); } ?></b></i></p>
    <h3>Key Data Source</h3>
	<p>
        <?php printf( __( 'Enter the custom field key <a href="%s">[?]</a>  to locate in single posts/pages. When found, the corresponding value is displayed along with widget title and text (if provided).', ACFWTEXTDOMAIN ), CODECUSTOMFIELDLINK ) ?>
    </p>
	<p>
        <?php acfw_editfield( $number, __( 'Primary Custom Field Key (required - Used for randomised content):', ACFWTEXTDOMAIN ), '', $key, $keyloadall, $keysep ); ?><br />
		<?php _e( 'The <strong>key</strong> must match <em>exactly</em> as in posts/pages.', ACFWTEXTDOMAIN ) ?>
	</p>
	<p>
        <?php acfw_editfield( $number, __( 'Secondary Custom Field Key (optional - Used if no content for primary on single post pages):', ACFWTEXTDOMAIN ), 's', $skey, $skeyloadall, $skeysep ); ?><br />
		<?php _e( 'The <strong>key</strong> must match <em>exactly</em> as in posts/pages.', ACFWTEXTDOMAIN ) ?>
	</p>
    <hr />
    <h3>Widget Title (Optional)</h3>
	<p>
		<label for="adv-custom-field-title-<?php echo $number; ?>"><?php _e( 'Widget Title:', ACFWTEXTDOMAIN ) ?></label>
		<input id="adv-custom-field-title-<?php echo $number; ?>" name="widget-adv-custom-field[<?php echo $number; ?>][title]" class="widefat" type="text" value="<?php echo $title; ?>" />
	</p>
    <hr />
    <h3>Content Wrapping (Optional)</h3>
	<p>
		<label for="adv-custom-field-text-<?php echo $number; ?>"><?php _e( 'Widget Text:', ACFWTEXTDOMAIN ) ?></label>
		<textarea id="adv-custom-field-text-<?php echo $number; ?>" name="widget-adv-custom-field[<?php echo $number; ?>][text]" class="code widefat" rows="5" cols="20"><?php echo $text; ?></textarea>
	</p>
	
	<table border="0" align="center">
	<tr><td>
	<p>
		<label for="adv-custom-field-pretext-<?php echo $number; ?>"><?php _e( 'Widget Pretext:', ACFWTEXTDOMAIN ) ?></label>
		<textarea id="adv-custom-field-pretext-<?php echo $number; ?>" name="widget-adv-custom-field[<?php echo $number; ?>][pretext]" class="code widefat" rows="5" cols="20"><?php echo $pretext; ?></textarea>
	</p>
	</td><td>
	<p>
		<label for="adv-custom-field-posttext-<?php echo $number; ?>"><?php _e( 'Widget Posttext:', ACFWTEXTDOMAIN ) ?></label>
		<textarea id="adv-custom-field-posttext-<?php echo $number; ?>" name="widget-adv-custom-field[<?php echo $number; ?>][posttext]" class="code widefat" rows="5" cols="20"><?php echo $posttext; ?></textarea>
	</p>
	</td></tr>
	<tr><td>
	<p>
		<label for="adv-custom-field-fixedtext1a-<?php echo $number; ?>"><?php _e( 'Fixed Text 1 Always:', ACFWTEXTDOMAIN ) ?></label>
		<textarea id="adv-custom-field-fixedtext1a-<?php echo $number; ?>" name="widget-adv-custom-field[<?php echo $number; ?>][fixedtext1a]" class="code widefat" rows="5" cols="20"><?php echo $fixedtext1a; ?></textarea>
	</p>
	</td><td>
	<p>
		<label for="adv-custom-field-fixedtext1m-<?php echo $number; ?>"><?php _e( 'Fixed Text 1 Content Found:', ACFWTEXTDOMAIN ) ?></label>
		<textarea id="adv-custom-field-fixedtext1m-<?php echo $number; ?>" name="widget-adv-custom-field[<?php echo $number; ?>][fixedtext1m]" class="code widefat" rows="5" cols="20"><?php echo $fixedtext1m; ?></textarea>
	</p>
	</td></tr>
	<tr><td>
	<p>
		<label for="adv-custom-field-fixedtext1n-<?php echo $number; ?>"><?php _e( 'Fixed Text 1 No Content:', ACFWTEXTDOMAIN ) ?></label>
		<textarea id="adv-custom-field-fixedtext1n-<?php echo $number; ?>" name="widget-adv-custom-field[<?php echo $number; ?>][fixedtext1n]" class="code widefat" rows="5" cols="20"><?php echo $fixedtext1n; ?></textarea>
	</p>
	</td><td>
	<p>
		<label for="adv-custom-field-fixedtext1r-<?php echo $number; ?>"><?php _e( 'Fixed Text 1 Random Content:', ACFWTEXTDOMAIN ) ?></label>
		<textarea id="adv-custom-field-fixedtext1r-<?php echo $number; ?>" name="widget-adv-custom-field[<?php echo $number; ?>][fixedtext1r]" class="code widefat" rows="5" cols="20"><?php echo $fixedtext1r; ?></textarea>
	</p>
	</td></tr>
	<tr><td>
	<p>
		<label for="adv-custom-field-fixedtext2a-<?php echo $number; ?>"><?php _e( 'Fixed Text 2 Always:', ACFWTEXTDOMAIN ) ?></label>
		<textarea id="adv-custom-field-fixedtext2a-<?php echo $number; ?>" name="widget-adv-custom-field[<?php echo $number; ?>][fixedtext2a]" class="code widefat" rows="5" cols="20"><?php echo $fixedtext2a; ?></textarea>
	</p>
	</td><td>
	<p>
		<label for="adv-custom-field-fixedtext2m-<?php echo $number; ?>"><?php _e( 'Fixed Text 2 Content Found:', ACFWTEXTDOMAIN ) ?></label>
		<textarea id="adv-custom-field-fixedtext2m-<?php echo $number; ?>" name="widget-adv-custom-field[<?php echo $number; ?>][fixedtext2m]" class="code widefat" rows="5" cols="20"><?php echo $fixedtext2m; ?></textarea>
	</p>
	</td></tr>
	<tr><td>
	<p>
		<label for="adv-custom-field-fixedtext2n-<?php echo $number; ?>"><?php _e( 'Fixed Text 2 No Content:', ACFWTEXTDOMAIN ) ?></label>
		<textarea id="adv-custom-field-fixedtext2n-<?php echo $number; ?>" name="widget-adv-custom-field[<?php echo $number; ?>][fixedtext2n]" class="code widefat" rows="5" cols="20"><?php echo $fixedtext2n; ?></textarea>
	</p>
	</td><td>
	<p>
		<label for="adv-custom-field-fixedtext2r-<?php echo $number; ?>"><?php _e( 'Fixed Text 2 Random Content:', ACFWTEXTDOMAIN ) ?></label>
		<textarea id="adv-custom-field-fixedtext2r-<?php echo $number; ?>" name="widget-adv-custom-field[<?php echo $number; ?>][fixedtext2r]" class="code widefat" rows="5" cols="20"><?php echo $fixedtext2r; ?></textarea>
	</p>
	</td></tr>
    </table>
    
    <hr />
    <h3>Content Generator (Optional)</h3>
    <p>
        <?php _e( 'When displaying the content of a custom field, the widget evals an string building command that builds main content.  If the Content Generator field is present, the custom field content is loaded into the variable \$acfw_content and then the evald code uses the string you put in here as the basis for the widget content instead.  This allows you to generate URL\'s and other content as the string $acfw_content in the Content Generator field below is replaced by the actual content from the post.  $data1-$data5 are loaded with the values from the custom key specified by Additional Data Field 1 through 5, if values exist in the post used as the data source.  You can also use $pageurl which contains the URL of the post which was the source for the rest of the widget content.', ACFWTEXTDOMAIN ) ?>
    </p>
    <p>
        <?php _e( 'Additional data fields are optional.  They are used to specify custom fields, the values of which will be loaded into the variables $data1-$data5 which can be used in the content generator.', ACFWTEXTDOMAIN ) ?>
        <?php _e( 'Select <i>Load all custom fields</i> or specify the custom fields you wish to load.  When selecting <i>Load all custom fields</i>, the data is loaded into fields with names $acfw_<fieldname> where <fieldname> is the custom field names with spaces and hyphens removed.', ACFWTEXTDOMAIN ) ?>
    </p>
    
    <p>
        <label for="adv-custom-field-loadallcustom<? echo $number; ?>"><?php _e( 'Load all custom fields:', ACFWTEXTDOMAIN ) ?></label>
        <input type="checkbox" id="adv-custom-field-loadallcustom<? echo $number; ?>" name="widget-adv-custom-field[<?php echo $number; ?>][loadallcustom]"  <?php if ( $loadallcustom ) echo "checked"; ?>>
        <label for="adv-custom-field-loadallcustomloadall-<?php echo $number; ?>"><?php _e( 'All items', ACFWTEXTDOMAIN ) ?></label>
        <input type="checkbox" id="adv-custom-field-loadallcustomloadall-<?php echo $number; ?>" name="widget-adv-custom-field[<?php echo $number; ?>][loadallcustomloadall]" <?php if ( $loadallcustomloadall ) echo "checked"; ?> />
        <label for="adv-custom-field-loadallcustomsep-<?php echo $number; ?>"><?php _e( 'Separator', ACFWTEXTDOMAIN ) ?></label>
        <input id="adv-custom-field-loadallcustomsep-<?php echo $number; ?>" name="widget-adv-custom-field[<?php echo $number; ?>][loadallcustomsep]" class="code" size="25" type="text" value="<?php echo $loadallcustomsep; ?>" />
    </p>
    <p>
        <?php acfw_editfield( $number, __( 'Additional Data Field 1:', ACFWTEXTDOMAIN ), 'data1', $data1key, $data1keyloadall, $data1keysep ); ?>
    </p>
    <p>
        <?php acfw_editfield( $number, __( 'Additional Data Field 2:', ACFWTEXTDOMAIN ), 'data2', $data2key, $data2keyloadall, $data2keysep ); ?>
    </p>
    <p>
        <?php acfw_editfield( $number, __( 'Additional Data Field 3:', ACFWTEXTDOMAIN ), 'data3', $data3key, $data3keyloadall, $data3keysep ); ?>
    </p>
    <p>
        <?php acfw_editfield( $number, __( 'Additional Data Field 4:', ACFWTEXTDOMAIN ), 'data4', $data4key, $data4keyloadall, $data4keysep ); ?>
    </p>
    <p>
        <?php acfw_editfield( $number, __( 'Additional Data Field 5:', ACFWTEXTDOMAIN ), 'data5', $data5key, $data5keyloadall, $data5keysep ); ?>
    </p>
    
	<p>
		<label for="adv-custom-field-contentgen-<?php echo $number; ?>"><?php _e( 'Content Generator:' , ACFWTEXTDOMAIN ) ?></label>
		<textarea id="adv-custom-field-contentgen-<?php echo $number; ?>" name="widget-adv-custom-field[<?php echo $number; ?>][contentgen]" rows="5" cols="40" class="code widefat"><?php echo $contentgen; ?></textarea>
	</p>
    <p>
        <label for="adv-custom-field-contentgenscript-<?php echo $number; ?>"><?php _e( 'Process content generator as script:', ACFWTEXTDOMAIN ) ?></label>
        <input type="checkbox" id="adv-custom-field-contentgenscript-<?php echo $number; ?>" name="widget-adv-custom-field[<?php echo $number; ?>][contentgenscript]" <?php if ( $contentgenscript ) echo "checked"; ?>>
    </p>
    <p>
        <label for="adv-custom-field-dontconvert-<?php echo $number; ?>"><?php _e( 'Do not run content generator through \'convert_chars\' filter:', ACFWTEXTDOMAIN ) ?></label>
        <input type="checkbox" id="adv-custom-field-dontconvert-<?php echo $number; ?>" name="widget-adv-custom-field[<?php echo $number; ?>][dontconvert]" <?php if ( $dontconvert ) echo "checked"; ?>>
    </p>
    
    <hr />
    <h3>Miscellaneous</h3>		
	<p>
		<label for="adv-custom-field-dorandomsingle-<?php echo $number; ?>"><?php _e( 'Show random on single post pages:', ACFWTEXTDOMAIN ) ?></label>
		<input type="checkbox" id="adv-custom-field-dorandomsingle-<?php echo $number; ?>" name="widget-adv-custom-field[<?php echo $number; ?>][dorandomsingle]" <?php if ( $dorandomsingle ) echo "checked"; ?>>
	</p>
	<p>
		<label for="adv-custom-field-dorandomother-<?php echo $number; ?>"><?php _e( 'Show random on other pages:', ACFWTEXTDOMAIN ) ?></label>
		<input type="checkbox" id="adv-custom-field-dorandomother-<?php echo $number; ?>" name="widget-adv-custom-field[<?php echo $number; ?>][dorandomother]" <?php if ( $dorandomother ) echo "checked"; ?>>
		<input type="hidden" name="widget-adv-custom-field[<?php echo $number; ?>][submit]" value="1" />
	</p>	
    <p>
        <label for="adv-custom-field-dontfilter-<?php echo $number; ?>"><?php _e( 'Do not filter content:', ACFWTEXTDOMAIN ) ?></label>
        <input type="checkbox" id="adv-custom-field-dontfilter-<?php echo $number; ?>" name="widget-adv-custom-field[<?php echo $number; ?>][dontfilter]" <?php if ( $dontfilter ) echo "checked"; ?>>
    </p>
	<p><?php _e( 'Filtering beautifies some of the HTML output by the widget.  For example if you have picture dimensions WWWxHHH, the x will be converted to a nicer looking character.  This can result in the failure of links etc.  If this is occuring, check this box, it will turn off filtering.', ACFWTEXTDOMAIN ) ?></p>
	<p>
		<label for="adv-custom-field-widgetindex-<?php echo $number; ?>"><?php _e( 'Widget index:', ACFWTEXTDOMAIN ) ?></label>
		<input maxlength="5" size="5" id="adv-custom-field-widgetindex-<?php echo $number; ?>" name="widget-adv-custom-field[<?php echo $number; ?>][widgetindex]" class="code" type="text" value="<?php echo $widgetindex; ?>" />
	</p>
    <hr />
    <h3>Help and Assistance</h3>
    <p>
    
        <?php printf( __( 'For assistance with ACFW, post your comments on <a href="%1$s" target="_BLANK">my blog</a>, and to read the on-line user manual visit <a href="%2$s" target="_BLANK">my wiki</a>.  <i>Thanks, AthenaOfDelphi</i>', ACFWTEXTDOMAIN ), ACFWBLOGLINK, ACFWWIKILINK ) ?>
    </p>
    <hr />
    </div>
	
<?php
	// And we're finished with our widget options panel
}

// Shortcode to allow rendering of widgets in a post
function acfw_getwidget( $atts ) {
    $content = '';
    
    extract( shortcode_atts( array(
        'id' => '0'
    ), $atts ) );

    $acfwid = $atts['id'];
    
    if ( $acfwid != 0 ) {
        ob_start();
        wp_widget_adv_custom_field( array( 'number' => $acfwid ), 1 );
        $content = ob_get_contents();
        ob_end_clean();
    }
    
    return $content;
}
add_shortcode( 'acfw', 'acfw_getwidget' );

// Function to allow rendering of widgets directly in a theme
function acfw( $number ) {
    wp_widget_adv_custom_field( array( 'number' => $number ), 1 );
}

// Function to add widget option table when activating this plugin
function wp_widget_adv_custom_field_activation() {
	add_option( 'widget_adv_custom_field', '', '', 'yes' );
}

// Function to initialize the Custom Field Widget: the widget and widget options panel
function wp_widget_adv_custom_field_register() {
	// Do we have options? If so, get info as array
	if ( !$options = get_option( 'widget_adv_custom_field' ) )
		$options = array();
	// Variables for our widget
	$widget_ops = array(
			'classname'   => 'widget_adv_custom_field',
			'description' => __( 'Display page/post custom field value for a set key', ACFWTEXTDOMAIN )
		);
	// Variables for our widget options panel
	$control_ops = array(
			'width'   => 700,
			'height'  => 450,
			'id_base' => 'adv-custom-field'
		);
	// Variable for out widget name
	$name = __( 'Adv. Custom Field', ACFWTEXTDOMAIN );
	// Assume we have no widgets in play.
	$id = false;
	// Since we're dealing with multiple widgets, we much register each accordingly
	foreach ( array_keys( $options ) as $o ) {
		// Per Automattic: "Old widgets can have null values for some reason"
		if ( ! isset( $options[$o]['title'] ) || ! isset( $options[$o]['text'] ) || ! isset( $options[$o]['pretext'] ) || ! isset( $options[$o]['posttext'] ) )
			continue;
			
		// Automattic told me not to translate an ID. Ever.
		$id = "adv-custom-field-$o"; // "Never never never translate an id" See?
		// Register the widget and then the widget options menu
		wp_register_sidebar_widget( $id, $name, 'wp_widget_adv_custom_field', $widget_ops, array( 'number' => $o ) );
		wp_register_widget_control( $id, $name, 'wp_widget_adv_custom_field_control', $control_ops, array( 'number' => $o ) );
	}
	// Create a generic widget if none are in use
	if ( !$id ) {
		// Register the widget and then the widget options menu
		wp_register_sidebar_widget( 'adv-custom-field-1', $name, 'wp_widget_adv_custom_field', $widget_ops, array( 'number' => -1 ) );
		wp_register_widget_control( 'adv-custom-field-1', $name, 'wp_widget_adv_custom_field_control', $control_ops, array( 'number' => -1 ) );
	}
    
    // Register our holding sidebar
    register_sidebar( array(
        'id'            => 'acfw01',
        'name'          => 'ACFW Holding Area',
        'description'   => 'Holding area for invisible instances of ACFW that can be added using the [acfw id="<acfwid>"] shortcode or the acfw(<acfwid>); function in themes.',
        'before_widget' => '',
        'after_widget'  => ''
        )
    );
}

// Adds filters to custom field values to prettify like other content
add_filter( 'adv_custom_field_value1', 'convert_chars' );
add_filter( 'adv_custom_field_value2', 'stripslashes' );
add_filter( 'adv_custom_field_value3', 'wptexturize' );

// When activating, run the appropriate function
register_activation_hook( __FILE__, 'wp_widget_adv_custom_field_activation' );

// Allow localization, if applicable
$plugin_dir = dirname( plugin_basename( __FILE__ ) );
load_plugin_textdomain( ACFWTEXTDOMAIN, 'wp-content/plugins/' . $plugin_dir, $plugin_dir );

// Initializes the function to make our widget(s) available
add_action( 'init', 'wp_widget_adv_custom_field_register' );

?>
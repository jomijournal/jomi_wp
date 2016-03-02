<?php
/**
 * @package KISSmetrics
 */
/*
Plugin Name: KISSmetrics
Plugin URI: http://support.kissmetrics.com/integrations/wordpress
Description: Using KISSmetrics, automagically track pageviews / blog post views and add properties for title, category / categories, tags, and comments.
Version: 0.0.2
Author: KISSmetrics
Author URI: http://www.kissmetrics.com
*/

define('KISSMETRICS_VERSION', '0.0.2');
define('KISSMETRICS_PLUGIN_URL', plugin_dir_url( __FILE__ ));

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo "Hi there! I'm just a plugin, not much I can do when called directly.";
	exit;
}

$km_key = '';

if ( is_admin() )
	require_once dirname( __FILE__ ) . '/admin.php';

if( !class_exists( 'KM_Filter' ) ) {
	class KM_Filter {
		static $link_regex = '/<a (.*?)href="(.*?)"(.*?)>(.*?)<\/a>/i';

		/**
		 * Outputs the KISSmetrics analytics script block.
		 */
		function output_analytics() {
			global $km_key;

			// As long as the API key is set, output the analytics. Also add code to catch if the user is viewing the homepage.
			if( $km_key != '' ) {
?><script type="text/javascript">
  var _kmq = _kmq || [];
  var _kmk = _kmk || '<?php echo $km_key; ?>';
  function _kms(u){
    setTimeout(function(){
    var s = document.createElement('script'); var f = document.getElementsByTagName('script')[0]; s.type = 'text/javascript'; s.async = true;
    s.src = u; f.parentNode.insertBefore(s, f);
    }, 1);
  }
  _kms('//i.kissmetrics.com/i.js');_kms('//doug1izaerwt3.cloudfront.net/' + _kmk + '.1.js');
  _kmq.push(function() {
    if(document.getElementsByTagName('body')[0].className.match('home')) {
    	_kmq.push(['record', 'Viewed Blog Homepage']);
    }
  });

<?php
				// Identify authenticated users
				if( get_option( 'kissmetrics_identify_users' ) && is_user_logged_in() ) {
					global $current_user;
					get_currentuserinfo();
?>_kmq.push(['identify', '<?php echo $current_user->user_login ?>']);
<?php
				}

				// Track social button interactions (tweet, like/unlike, FB connect)
				if( get_option( 'kissmetrics_track_social' ) ) {
?>_kmq.push(function() {
	if(window.twttr) {
	  window.twttr.events.bind('tweet', function (event) {
	    var url = KM.uprts(decodeURIComponent(event.target.src).replace('#', '?')).params.url;
	    _kmq.push(['record', 'Tweet', { 'Shared URL': url }]);
	  });

	  window.twttr.events.bind('follow', function (event) {
	  	_kmq.push(['record', 'Twitter Follow', { 'Username': event.data.screen_name }]);
	  });
	}

	if(window.FB) {
	  window.FB.Event.subscribe('edge.create', function (url) {
	    _kmq.push(['record', 'Like', { 'Shared URL': url }]);
	  });

	  window.FB.Event.subscribe('edge.remove', function (url) {
	    _kmq.push(['record', 'Unlike', { 'Shared URL': url }]);
	  });

	  window.FB.Event.subscribe('auth.login', function (url) {
	    _kmq.push(['record', 'Facebook Connect\'d']);
	  });

	  window.FB.Event.subscribe('auth.logout', function (url) {
	    _kmq.push(['record', 'Facebook Logout']);
	  });
	}
});
<?php
				}
				// Track search queries
				if( get_option( 'kissmetrics_track_search' ) ) {
?>_kmq.push(function() {
		if(document.getElementsByTagName('body')[0].className.match('search-results')) {
			try {
				var query = KM.uprts(decodeURIComponent(window.location.href)).params.s;
				_kmq.push(['record', 'Searched Site', {'WordPress Search Query': query}]);
			} catch(e) {}
		}
	});
<?php
				}
?></script>
				<?php
			}
		}


		/**
		 * Creates a "View post" event for single post views when the_content is called.
		 *
		 * @param string $text The content text.
		 */
		function the_content( $text ) {
			$is_post = is_single();

			if( ( $is_post || is_page() ) && get_option( 'kissmetrics_track_views' ) ) {
				global $post;

				$categories = array();
				foreach( (get_the_category()) as $category ) {
					$categories[] = $category->cat_name;
				}

				$is_post = is_single();

				$kmq = array(
					'record',
					'Viewed ' . ( $is_post ? 'post' : 'page' )
				);

				if( $is_post ) {
					// Posts (have categories)
					$kmq[] = array(
						'ID' => $post->ID,
						'Title' => get_the_title(),
						'Categories' => $categories
					);
				} else {
					// Pages
					$kmq[] = array(
						'ID' => $post->ID,
						'Title' => get_the_title()
					);
				}

				$text .= "<script>_kmq.push(" . json_encode( $kmq ) . ");</script>";
			}

			// Track all links
			if( get_option( 'kissmetrics_track_links' ) )
				$text = preg_replace_callback( KM_Filter::$link_regex, array( 'KM_Filter', 'parse_content_link' ), $text );

			return $text;
		}


		/**
		 * Parse comments for links and add tracking code.
		 *
		 * @param string $text The comment text.
		 */
		function comment_text( $text ) {
			if( get_option( 'kissmetrics_track_comment_links' ) )
				$text = preg_replace_callback( KM_Filter::$link_regex, array( 'KM_Filter', 'parse_comment_link' ), $text );

			return $text;
		}


		/**
		 * Based on the provided host/uri string, returns the domain and the host in a hash.
		 *
		 * @param string $uri The URI/host string.
		 * @return object The associative array with two keys: "domain" (e.g., google.com) and "host" (e.g., mail.google.com)
		 */
		function get_domain( $uri ) {
			$parsed_uri = parse_url( $uri );
			if( isset( $parsed_uri['host'] ) )
				$host = $parsed_uri['host'];
			else
				$host = '';

			preg_match( '/[^\.\/]+\.[^\.\/]+$/', $host, $domain );

			if( !count( $domain ) )
				$domain = array( '' );

			return array( 'domain' => $domain[0], 'host' => $host );
		}


		/**
		 * Parses a link, adds an ID, and tracks a link click. Automatically detects if the link is
		 * to an internal or external resource.
		 *
		 * @param string $page_title The title of the page the link is found on.
		 * @param string $type The type of tracking code to add ("Comment", "Article")
		 * @param array $link_matches The group of matches (from preg_match) on the link.
		 * @return string A link as an HTML string.
		 */
		function parse_link( $page_title, $type, $link_matches ) {
			static $id_attr_regex = '/id\s*=\s*[\'"](.+?)[\'"]/i';

			$target = KM_Filter::get_domain( $link_matches[2] );
			$id = '';

			// Attempt to find the link's "id" attribute, if it has one (if not, we'll give it one)
			preg_match( $id_attr_regex, $link_matches[1], $id_attr );
			if( !$id_attr ) {
				preg_match( $id_attr_regex, $link_matches[3], $id_attr );

				// Still no link id?! Ok.
				if( !$id_attr ) {
					$id = uniqid( 'link_' );
				} else {
					$id = $id_attr[1];
				}
			} else {
				$id = $id_attr[1];
			}

			$kmq = array(
				( $target['domain'] !== $origin['domain'] ) ? 'trackClickOnOutboundLink' : 'trackClick',
				$id,
				$type . ' link clicked',
				array(
					'Title' => $link_matches[4],
					'Page' => $page_title
				)
			);

			return '<a ' . $link_matches[1] . 'href="' . $link_matches[2] . '"' . $link_matches[3]
			        . ( !$id_attr ? ' id="' . $id . '"' : '' ) . '>'
			        . $link_matches[4]
			        . '</a>'
			        . '<script>_kmq.push(' . json_encode($kmq) . ');</script>';
		}


		/**
		 * Parse links in the content.
		 *
		 * @param array $matches The preg_replace_callback matches for links.
		 * @return string The modified text.
		 */
		function parse_content_link( $matches ) {
			return KM_Filter::parse_link( get_the_title(), 'Article', $matches );
		}


		/**
		 * Parse links in the comments.
		 *
		 * @param array $matches The preg_replace_callback matches for links.
		 * @return string The modified text.
		 */
		function parse_comment_link( $matches ) {
			return KM_Filter::parse_link( get_the_title(), 'Comment', $matches );
		}


		/**
		 * Track when a user signs in.
		 */
		function track_login() {
			if( get_option( 'kissmetrics_track_login' ) ) {
?><script type="text/javascript">
	_kmq.push(['trackSubmit', 'loginform', 'Signed in']);
</script><?php
			}
		}


		/**
		 * Track when a user registers.
		 */
		function track_register() {
			if( get_option( 'kissmetrics_track_signup' ) ) {
?><script type="text/javascript">
	_kmq.push(['trackSubmit', 'registerform', 'Created account / registered']);
</script><?php
			}
		}


		/**
		 * Track when a user views the registration page.
		 */
		function track_register_view() {
			if( get_option( 'kissmetrics_track_signup_view' ) ) {
?><script type="text/javascript">
  _kmq.push(function() {
    if(document.getElementById('registerform')) {
    	_kmq.push(['record', 'Viewed signup page']);
    }
  });
</script><?php
			}
		}


		/**
		 * Track comment form submissions.
		 */
		function track_comment_form() {
			if( ( is_single() || is_page() ) && get_option( 'kissmetrics_track_comment' ) ) {
?><script type="text/javascript">
  _kmq.push(function() {
  		var commented = function() {
<?php 			if( get_option( 'kissmetrics_identify_unregistered' ) ) { ?>
	  			_kmq.push(['identify', document.getElementById('email').value]);
<?php 			} ?>
  				_kmq.push(['record', 'Commented', {
					name: document.getElementById( 'author' ).value,
					email: document.getElementById( 'email' ).value,
					comment: document.getElementById( 'comment' ).value
				}]);
  			},
			el = document.getElementById('submit');

		if(el.addEventListener) {
			el.addEventListener('mousedown', commented, false);
		} else if(el.attachEvent)  {
			el.attachEvent('onmousedown', commented);
		}
  });
</script><?php
			}
		}


		/**
		 * Indentify users by email address when they submit a comment.
		 */
		function identify_comment_user() {
			// Get this comment author's email address.
			$comment_author_email = $_COOKIE['comment_author_email_' . COOKIEHASH];

			// Only track the commenter by email address if they're not signed in already.
			if( !is_user_logged_in() && ( is_single() || is_page() ) && $comment_author_email && get_option( 'kissmetrics_identify_unregistered' ) ) {
?><script type="text/javascript">
	_kmq.push(['identify', '<?php echo $comment_author_email; ?>']);
</script><?php
			}
		}
	}
}

// If the JS URL is defined in the options, set the variable
if( function_exists( 'get_option' ) ) {
	$km_key = get_option( 'kissmetrics_key' );
}

// Current site domain and host
$origin = KM_Filter::get_domain( $_SERVER['HTTP_HOST'] );

// Output analytics to all pages and the login page
add_action( 'wp_head', array( 'KM_Filter', 'output_analytics' ) );
add_action( 'login_head', array( 'KM_Filter', 'output_analytics' ) );

// On all pages (if enabled), identify comments by user
add_action( 'wp_head', array( 'KM_Filter', 'identify_comment_user' ) );

if( $km_key != '' && function_exists( 'get_option' ) ) {
	// Filter links for tracking
	add_filter( 'the_content', array( 'KM_Filter', 'the_content' ), 99 );
	add_filter( 'comment_text', array( 'KM_Filter', 'comment_text' ), 99 );

	// Login form tracking
	add_action( 'login_footer', array( 'KM_Filter', 'track_login' ) );

	// Register form tracking
	add_action( 'login_head', array( 'KM_Filter', 'track_register_view' ) );
	add_action( 'login_footer', array( 'KM_Filter', 'track_register' ) );

	// Comment form tracking
	add_action( 'comment_form', array( 'KM_Filter', 'track_comment_form' ) );
}

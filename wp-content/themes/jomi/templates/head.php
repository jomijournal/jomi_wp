<?php
global $user;

/* USERAPP */
use \UserApp\Widget\User;

$auth = false;
if (User::authenticated()) {
	$auth = true;
}
else
{
	if(isset($_COOKIE["ua_session_token"]))
	{
		$token = $_COOKIE["ua_session_token"];
		try
		{
			$auth = User::loginWithToken($token);
		}
		catch(Exception $e)
		{
			//
		}
	}
}
if($auth)
{
	try{
		$user = User::current();
	}
	catch(Exception $e){
		echo $e;
	}
}
elseif(!is_front_page())
{
	//header("Location: /");
}

/* LOGOUT */
if($user && isset($_GET["logout"]))
{
	$user->logout();
	$user = null;
	if(isset($_COOKIE['ua_session_token'])) {
		unset($_COOKIE['ua_session_token']);
		setcookie('ua_session_token', '', time() - 3600, "/"); // empty value and old timestamp
	}
}
?>
<!DOCTYPE html>
<html class="no-js" <?php language_attributes(); ?>>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title><?php wp_title('|', true, 'right'); ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=2">

	<!-- TYPEKIT -->
	<script type="text/javascript" src="//use.typekit.net/juj1iti.js"></script>
	<script type="text/javascript">try{Typekit.load();}catch(e){}</script>

	<!-- FONT AWESOME -->
	<link href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">

	<?php wp_head(); ?>

	<link rel="alternate" type="application/rss+xml" title="<?php echo get_bloginfo('name'); ?> Feed" href="<?php echo esc_url(get_feed_link()); ?>">
</head>

<script>
 var user = '<?php echo $user->user_id; ?>';
</script>
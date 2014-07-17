<?php
global $user;
?>
<?php if(is_front_page()) : ?>
	<video autoplay="" loop="" class='background hidden-xs' poster="/wp-content/themes/jomi/assets/img/vid_poster.png">
		<source src="/wp-content/themes/jomi/assets/video/background.mp4" type="video/mp4">
	</video>
	<img src='/wp-content/themes/jomi/assets/img/background_mobile.png' class='background visible-xs'/>
	<div class='blackbox background'></div>

<?php endif; ?>
<div class="container" role="document">
  <div class="content row">
    <main class="col-sm-12" role="main">
      <header class='site-header'>
      	<div class='row'>
	      	<div class='col-xs-12'>
	        	<div class="logo"><a href="/"><img src="/wp-content/themes/jomi/assets/img/logo.png" alt="Journal of Medical Insight"></a></div>
	       	 	<input placeholder="Search articles" style="margin-top: -10px;" type="text" name="login" size="30" class="border hidden-xs" id="search-field"/>
	       	 	<span class="glyphicon glyphicon-search search-icon hidden-xs"></span>
	       	<!--/div-->
	        <!-- Navbar buttons for desktop -->
	        <!--div class='col-xs-7'-->
		        <nav class="nav-top">
		          <ul>
		          	<?php if(!$user): ?>
		            <li class="dropdown">
						<a class="dropdown-toggle border" href="#" data-toggle="dropdown" id="login-btn">Sign&nbsp;in</a>
						<div class="dropdown-menu pull-right" style="padding: 15px;">
							<div id="login-form">
								<span class="label label-danger" id="error-login" style="display:none;">User does not exist</span>
								<input placeholder="Username" id="user_username" style="margin-bottom: 15px;" type="text" name="login" size="30" />
								<span class="label label-danger" id="error-password" style="display:none;">Invalid password</span>
								<input placeholder="Password" id="user_password" style="margin-bottom: 15px;" type="password" name="password" size="30" />
								<input class="btn fat" style="clear: left; width: 100%;" type="submit" name="commit" value="Sign In" />
							</div>
						</div>
					</li>
					<?php else: ?>
					<li><a href="/?logout" id="logout-btn">Sign&nbsp;out</a></li>
					<?php endif; ?>

		            <li><a href='/subscribers/' class="hidden-xs<?php 	if( is_page( 'subscribers') ) echo " active"; ?>">Subscribers</a></li>
		            <li><a href="/pricing" class="hidden-xs<?php 		if( is_page( 'pricing') ) echo " active"; ?>">Pricing</a></li>
		            <li><a href="/contact" class="hidden-xs<?php 		if( is_page( 'contact') ) echo " active"; ?>">Contact</a></li>
		            <li><a href="/about" class="hidden-xs<?php 			if( is_page( 'about') ) echo " active"; ?>">About</a></li>
		            
		          </ul>
		        </nav>
		    </div>
	    </div>
      </header>
    </main>
  </div>
</div>

<script>
	/* SIGNUP & LOGIN */
	$(function() {
		// Setup drop down menu
		$('.dropdown-toggle').dropdown();

		// Fix input element click problem
		$('.dropdown input, .dropdown label').click(function(e) {
			e.stopPropagation();
		});
	});
	$(document).ready(function(){
		UserApp.initialize({ appId: "53b5e44372154" });

		function onLoginSuccessful(token){
			Cookies.set('ua_session_token', token);
			window.location.href = "/articles";
		}

		function emailLogin(){
			UserApp.User.login({ "login": $('#login-form input[name="login"]').val(), "password": $('#login-form input[name="password"]').val() }, function(error, result) {
			    if (error) {
			        // Something went wrong...
			        // Check error.name. Might just be a wrong password?
			        $('#login-form .label').hide();
			        $('#login-form input').removeClass('error');
			        if($('#login-form input[name="password"]').val() == '')
			        {
			        	$('#login-form input[name="password"]').addClass('error');
			        	$('#error-password').show();
			    	}
			        else if(error.name == 'INVALID_ARGUMENT_LOGIN'){
			        	$('#login-form input[name="login"]').addClass('error');
			        	$('#error-login').show();
			        }
			        else if(error.name == 'INVALID_ARGUMENT_PASSWORD' || error.name == 'INVALID_ARGUMENT_LOGIN'){
			        	$('#login-form input[name="password"]').addClass('error');
			        	$('#error-password').show();
			        }
			    } else if (result.locks && result.locks.length > 0) {
			        // This user is locked
			    } else {
			        // User is logged in, save result.token in a cookie called 'ua_session_token'	        
			        onLoginSuccessful(result.token);
			    }
			});
		}

		var matches = window.location.href.match(/ua_token=([a-z0-9_-]+)/i);
		if (matches && matches.length == 2) {
			var token = matches[1];
			UserApp.setToken(token);
			onLoginSuccessful(token);
		}
		$('#login-form input[type=submit]').click(function(){
			emailLogin();
		});
		$('#login-form input[name="password"]').keypress(function (event) {
			if ( event.which == 13 || event.which == 10) {
		      emailLogin();
		    }
		});
		$('#search-field').keydown(function(event){
			if(event.which == 13)
			{
				event.preventDefault();
				window.location.href = "/?s="+$(this).val();
			}
		});
	});
</script>
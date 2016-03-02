<?php
/**
 * @package KISSmetrics
 */

if( !class_exists( 'KM_Admin' ) ) {
	class KM_Admin {

		/**
		 * Add the configuration page to the "Settings" menu.
		 */
		function add_config_page() {
			global $wpdb;

			if( function_exists( 'add_submenu_page' ) ) {
				add_submenu_page( 'options-general.php', 'KISSmetrics Configuration', 'KISSmetrics', 1, 'kissmetrics', array( __CLASS__, 'config_page' ) );
			}
		}


		/**
		 * Create the configuration page. Also handle updating the configuration.
		 */
		function config_page() {
			if( isset( $_POST['submit'] ) ) {
				if( !current_user_can( 'manage_options' ) )
					die( __('You cannot edit the UA string.') );

				check_admin_referer();
				$km_key = $_POST['km_key'];
				update_option( 'kissmetrics_key', $km_key );

				$km_identify = $_POST['km_identify'];
				update_option( 'kissmetrics_identify_users', $km_identify );

				$km_login = $_POST['km_login'];
				update_option( 'kissmetrics_track_login', $km_login );

				$km_signup_view = $_POST['km_signup_view'];
				update_option( 'kissmetrics_track_signup_view', $km_signup_view );

				$km_signup = $_POST['km_signup'];
				update_option( 'kissmetrics_track_signup', $km_signup );

				$km_views = $_POST['km_views'];
				update_option( 'kissmetrics_track_views', $km_views );

				$km_links = $_POST['km_links'];
				update_option( 'kissmetrics_track_links', $km_links );

				$km_comment_links = $_POST['km_comment_links'];
				update_option( 'kissmetrics_track_comment_links', $km_comment_links );

				$km_social_buttons = $_POST['km_social_buttons'];
				update_option( 'kissmetrics_track_social', $km_social_buttons );

				$km_search = $_POST['km_search'];
				update_option( 'kissmetrics_track_search', $km_search );

				$km_comment = $_POST['km_comment'];
				update_option( 'kissmetrics_track_comment', $km_comment );

				$km_identify_unregistered = $_POST['km_identify_unregistered'];
				update_option( 'kissmetrics_identify_unregistered', $km_identify_unregistered );
			}
			?>
			<div class="wrap">
				<div id="icon-options-general" class="icon32"></div>
				<h2>KISSmetrics</h2>

				<?php if( isset( $_POST['submit'] ) ) { ?>
				<div id="saved" style="width:40%;height:40px;line-height:40px;margin:20px auto;background:#85b84d;color:#fff;border:1px solid #608537;text-align:center;font-weight:700;">Your settings have been saved.</div>
				<script type="text/javascript">
					setTimeout( function() {
						var saved = document.getElementById( 'saved' );
						saved.parentNode.removeChild( saved );
					}, 5000 );
				</script>
				<?php } ?>

				<p>Description goes here.</p>

				<form action="" method="post" id="kissmetrics-config">
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row">
									<label for="km_key">API Key</label>
								</th>
								<td>
									<input id="km_key" name="km_key" type="text" class="regular-text" value="<?php echo get_option( 'kissmetrics_key' ); ?>" style="font-family: 'Courier New', Courier, mono; font-size: 1em;" />
									<span class="description">Get your API key from the <a href="https://app.kissmetrics.com/settings">KISSmetrics Settings</a> page.</span>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row">
									Identity
								</th>
								<td>
									<label for="km_identify">
										<input id="km_identify" name="km_identify" type="checkbox"<?php if( get_option( 'kissmetrics_identify_users' ) ) echo 'checked="checked"'; ?>>
										<span>Identify authenticated users</span>
									</label>
									<br>
									<label for="km_login">
										<input id="km_login" name="km_login" type="checkbox"<?php if( get_option( 'kissmetrics_track_login' ) ) echo 'checked="checked"'; ?>>
										<span>Track login event</span>
									</label>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row">
									Registration
								</th>
								<td>
									<label for="km_signup_view">
										<input id="km_signup_view" name="km_signup_view" type="checkbox"<?php if( get_option( 'kissmetrics_track_signup_view' ) ) echo 'checked="checked"'; ?>>
										<span>Track sign up page view</span>
									</label>
									<br>
									<label for="km_signup">
										<input id="km_signup" name="km_signup" type="checkbox"<?php if( get_option( 'kissmetrics_track_signup' ) ) echo 'checked="checked"'; ?>>
										<span>Track registration event</span>
									</label>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row">
									General tracking
								</th>
								<td>
									<label for="km_views">
										<input id="km_views" name="km_views" type="checkbox"<?php if( get_option( 'kissmetrics_track_views' ) ) echo 'checked="checked"'; ?>>
										<span>Post/page views</span>
									</label>
									<br>
									<label for="km_links">
										<input id="km_links" name="km_links" type="checkbox"<?php if( get_option( 'kissmetrics_track_links' ) ) echo 'checked="checked"'; ?>>
										<span>Links in posts/pages</span>
									</label>
									<br>
									<label for="km_comment_links">
										<input id="km_comment_links" name="km_comment_links" type="checkbox"<?php if( get_option( 'kissmetrics_track_comment_links' ) ) echo 'checked="checked"'; ?>>
										Links in comments
									</label>
									<br>
									<label for="km_social_buttons">
										<input id="km_social_buttons" name="km_social_buttons" type="checkbox"<?php if( get_option( 'kissmetrics_track_social' ) ) echo 'checked="checked"'; ?>>
										Social buttons (Facebook, Twitter)
									</label>
									<br>
									<label for="km_search">
										<input id="km_search" name="km_search" type="checkbox"<?php if( get_option( 'kissmetrics_track_search' ) ) echo 'checked="checked"'; ?>>
										Search queries
									</label>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row">
									Comments
								</th>
								<td>
									<label for="km_comment">
										<input id="km_comment" name="km_comment" type="checkbox"<?php if( get_option( 'kissmetrics_track_comment' ) ) echo 'checked="checked"'; ?>>
										<span>Track comment submission</span>
									</label>
									<br>
									<label for="km_identify_unregistered">
										<input id="km_identify_unregistered" name="km_identify_unregistered" type="checkbox"<?php if( get_option( 'kissmetrics_identify_unregistered' ) ) echo 'checked="checked"'; ?>>
										<span>Identify unregistered users by comment email address</span>
										<span class="description">(A user can only be identified if they have submitted a comment.)</span>
									</label>
								</td>
							</tr>
						</tbody>
					</table>

					<p class="submit">
						<input type="submit" name="submit" class="button-primary" value="Save Changes" style="margin:0 0 0 223px;" />
					</p>
				</form>
			</div>
			<?php
		}

	}
}

// Add the menu item
add_action( 'admin_menu', array( 'KM_Admin', 'add_config_page' ) );

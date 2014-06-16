<?php

/*
Plugin Name:   Network Privacy Fix
Version:       0.1.2
Description:   Allow Network Privacy plugin work with Domain Mapping plugin
Author:        Compass
Author URI:    http://ctlt.ubc.ca/
Plugin URI:    
*/

add_action( 'plugins_loaded', 'ra_network_privacy_action_fix' );

function ra_network_privacy_action_fix()
{

	global $ra_network_privacy;
	
	if( class_exists( 'RA_Network_Privacy' ) ){

		remove_action( 'template_redirect', array( &$ra_network_privacy, 'authenticator' ) );
		add_action( 'wp_head', 'ra_network_privacy_fix_network_privacy_authenticator', 9999 ); // 9999 will allow domain mapping hook runs first

	}

}/* ra_network_privacy_action_fix() */

/**
 * ra_network_privacy_fix_network_privacy_authenticator this function is copied from Network Privacy 
 * plugin with modifications to allow it to work with Domain Mapping plugin
 * 
 * @access public
 * @return void
 */

function ra_network_privacy_fix_network_privacy_authenticator (){

	global $ra_network_privacy;
	$privacy = get_option( 'blog_public' );
	if( $privacy > -1 ){
		return;
	}

	global $privacyMeta;

	if ( is_user_logged_in() ) {

		if( $privacy > -2 || current_user_can( $privacyMeta[$privacy]['cap'] ) ){
			return;
		}

		$ra_network_privacy->login_header();
	?>
	
		<form name="loginform" id="loginform">
			<p>Wait 5 seconds or 
			<a href="<?php echo get_option('siteurl'); ?>/wp-login.php">click</a> to continue.</p>
			<?php $ra_network_privacy->privacy_login_message (); ?>
		</form>
		</div></body></html>

		<?php } else {
		//nocache_headers();
		//header("HTTP/1.1 302 Moved Temporarily");
		//header('Location: ' . get_option('siteurl') . '/wp-login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));
		//header("Status: 302 Moved Temporarily");
		if( SUBDOMAIN_INSTALL ){
			$url = site_url().'/wp-login.php?redirect_to=' . urlencode(site_url().$_SERVER['REQUEST_URI']);
		}else{
			$url = site_url().'/wp-login.php?redirect_to=' . urlencode( $_SERVER['REQUEST_URI'] );
		}
		
		echo "<script type='text/javascript'>\nwindow.location = '$url'</script>";

	}

	exit();

}/* ra_network_privacy_fix_network_privacy_authenticator() */


add_filter( 'ra_network_privacy_caps', 'RANetWorkPrivacyFixgetPrivacyMeta' );

/**
 * Ensure we have access to the privacy meta from the main plugin (this)
 * is no longer an accessible property as it's private. Instead we have to
 * gain access through the filter provided. 
 *
 * @since 0.1.2
 *
 * @param array $options an array of capabilities
 * @return array $options an array of capabilities
 */
function RANetWorkPrivacyFixgetPrivacyMeta( $options ){

	// Ensure we have access to this elsewhere
	global $privacyMeta;
	$privacyMeta = $options;

	// Make no adjustments, just return it as is.
	return $options;

}/* RANetWorkPrivacyFixgetPrivacyMeta() */
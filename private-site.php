<?php
/*
Plugin Name: Private Site
Plugin URI: https://aurooba.com
Description: Keep your website private and hidden from public eyes.
Version: 1.0.0
Author: Aurooba Ahmed
Author URI: https://aurooba.com

Text Domain: am-private-site
Domain Path: /languages

License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

//DEV NOTE: GOTTA MAKE THIS PLUGIN PROPER


/* Check to see if the current page is the login/register page.
*
* Use this in conjunction with is_admin() to separate the front-end
* from the back-end of your theme.
*
* @return bool
*/
if ( ! function_exists( 'is_login_page' ) ) {
	function is_login_page() {
		return in_array(
			$GLOBALS['pagenow'],
			array( 'wp-login.php', 'wp-register.php' ),
			true
		);
	}
}


function am_private_site() {

	// Ignore AJAX, Cron, and WP-CLI Requests. Also ignore if accessing admin or login page
	if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( defined( 'WP_CLI' ) && WP_CLI ) || is_admin() || is_login_page() ) {
		return;
	}
	// Redirect unauthorized visitors
	if ( ! is_user_logged_in() ) {
		// Get visited URL
		$schema = isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https://' : 'http://';

		// Set up the redirect URL and clean it up
		$redirect_url = preg_replace( '/\?.*/', '', $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

		//Don't cache this
		nocache_headers();

		// Redirect to login
		wp_safe_redirect( wp_login_url( $redirect_url ), 302 );
	}
}
add_action( 'init', 'am_private_site' );

/**
 * Restrict REST API for authorized users only
 *
 * @since 1.0.0
 * @param WP_Error|null|bool $result WP_Error if authentication error, null if
 * authentication method wasn't used, true if authentication succeeded.
 *
 * @return WP_Error|null|bool
 */
function am_restrict_rest_access( $result ) {
	if ( null === $result && ! is_user_logged_in() ) {
		return new WP_Error( 'rest_unauthorized', __( 'Only authenticated users can access the REST API.', 'wp-force-login' ), array( 'status' => rest_authorization_required_code() ) );
	}
	return $result;
}
add_filter( 'rest_authentication_errors', 'am_restrict_rest_access', 99 );

/*
 * Localization
 */
function am_private_site_load_textdomain() {
	load_plugin_textdomain( 'am-private-site', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'am_private_site_load_textdomain' );

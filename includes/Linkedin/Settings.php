<?php
/**
 * The stored LinkedIn settings and connection, and what every LinkedIn
 * screen needs to know about them.
 *
 * @package NHRRob\SocialShare
 */

namespace NHRRob\SocialShare\Linkedin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings Class
 */
class Settings {

	const OPTION = 'nhrrob_social_share_linkedin';

	/**
	 * Stored settings and the LinkedIn connection.
	 *
	 * @return array
	 */
	public static function get() {
		return wp_parse_args(
			get_option( self::OPTION, array() ),
			array(
				'client_id'     => '',
				'client_secret' => '',
				'mode'          => 'manual',
				'access_token'  => '',
				'expires_at'    => 0,
				'person_id'     => '',
				'person_name'   => '',
			)
		);
	}

	/**
	 * Saves settings without autoloading them, since they hold the secret and token.
	 *
	 * @param array $settings Settings to store.
	 * @return void
	 */
	public static function update( $settings ) {
		update_option( self::OPTION, $settings, false );
	}

	/**
	 * The redirect URL to register in the LinkedIn app.
	 *
	 * @return string
	 */
	public static function redirect_uri() {
		/**
		 * The redirect URL registered in the LinkedIn app. A site that moved
		 * from older code can keep the address its app already has, and send
		 * that action to `Connect::handle_callback()`.
		 *
		 * @param string $url Address.
		 */
		return (string) apply_filters( 'nhrrob_social_share_linkedin_redirect_uri', admin_url( 'admin-post.php?action=nhrrob_social_share_linkedin_callback' ) );
	}

	/**
	 * URL of the settings page, optionally carrying a result message code.
	 *
	 * @param string $message Message code.
	 * @return string
	 */
	public static function page_url( $message = '' ) {
		$url = admin_url( 'options-general.php?page=nhrrob-social-share&tab=linkedin' );

		return $message ? add_query_arg( 'nhrrob_social_share', $message, $url ) : $url;
	}

	/**
	 * Whether a usable LinkedIn token is stored.
	 *
	 * @return bool
	 */
	public static function is_connected() {
		$settings = self::get();

		return $settings['access_token'] && $settings['person_id'] && $settings['expires_at'] > time();
	}

	/**
	 * Stops anyone who may not manage the LinkedIn connection. Every settings
	 * action checks its nonce first, then calls this.
	 *
	 * @return void
	 */
	public static function require_manager() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to manage the LinkedIn connection.', 'nhrrob-social-share' ), 403 );
		}
	}
}

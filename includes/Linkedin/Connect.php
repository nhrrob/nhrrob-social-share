<?php
/**
 * Connects the site to a LinkedIn account with OAuth, and disconnects it.
 *
 * @package NHRRob\SocialShare
 */

namespace NHRRob\SocialShare\Linkedin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Connect Class
 */
class Connect {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_post_nhrrob_social_share_linkedin_connect', array( $this, 'handle_connect' ) );
		add_action( 'admin_post_nhrrob_social_share_linkedin_callback', array( $this, 'handle_callback' ) );
		add_action( 'admin_post_nhrrob_social_share_linkedin_disconnect', array( $this, 'handle_disconnect' ) );
	}

	/**
	 * Send the browser to LinkedIn to authorize the connection.
	 *
	 * @return void
	 */
	public function handle_connect() {
		check_admin_referer( 'nhrrob_social_share_linkedin_connect' );
		Settings::require_manager();

		// The redirect leaves the site, so wp_safe_redirect() would refuse it.
		// phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
		wp_redirect(
			add_query_arg(
				array(
					'response_type' => 'code',
					'client_id'     => rawurlencode( Settings::get()['client_id'] ),
					'redirect_uri'  => rawurlencode( Settings::redirect_uri() ),
					'state'         => wp_create_nonce( 'nhrrob_social_share_linkedin_oauth' ),
					'scope'         => rawurlencode( 'openid profile w_member_social' ),
				),
				'https://www.linkedin.com/oauth/v2/authorization'
			)
		);
		exit;
	}

	/**
	 * LinkedIn sends the browser back here with a code to exchange for a token.
	 *
	 * @return void
	 */
	public function handle_callback() {
		// The state parameter is the nonce.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$state = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : '';
		$code  = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : '';
		// phpcs:enable

		if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $state, 'nhrrob_social_share_linkedin_oauth' ) ) {
			wp_die( esc_html__( 'You are not allowed to manage the LinkedIn connection.', 'nhrrob-social-share' ), 403 );
		}

		if ( ! $code ) {
			wp_safe_redirect( Settings::page_url( 'denied' ) );
			exit;
		}

		$settings = Settings::get();
		$token    = wp_remote_post(
			'https://www.linkedin.com/oauth/v2/accessToken',
			array(
				'timeout' => 15,
				'body'    => array(
					'grant_type'    => 'authorization_code',
					'code'          => $code,
					'redirect_uri'  => Settings::redirect_uri(),
					'client_id'     => $settings['client_id'],
					'client_secret' => $settings['client_secret'],
				),
			)
		);
		$token    = is_wp_error( $token ) ? array() : json_decode( wp_remote_retrieve_body( $token ), true );

		if ( empty( $token['access_token'] ) || empty( $token['expires_in'] ) ) {
			wp_safe_redirect( Settings::page_url( 'error' ) );
			exit;
		}

		$profile = wp_remote_get(
			'https://api.linkedin.com/v2/userinfo',
			array(
				'timeout' => 15,
				'headers' => array( 'Authorization' => 'Bearer ' . $token['access_token'] ),
			)
		);
		$profile = is_wp_error( $profile ) ? array() : json_decode( wp_remote_retrieve_body( $profile ), true );

		if ( empty( $profile['sub'] ) ) {
			wp_safe_redirect( Settings::page_url( 'error' ) );
			exit;
		}

		$settings['access_token'] = sanitize_text_field( $token['access_token'] );
		$settings['expires_at']   = time() + absint( $token['expires_in'] );
		$settings['person_id']    = sanitize_text_field( $profile['sub'] );
		$settings['person_name']  = isset( $profile['name'] ) ? sanitize_text_field( $profile['name'] ) : '';

		Settings::update( $settings );

		wp_safe_redirect( Settings::page_url( 'connected' ) );
		exit;
	}

	/**
	 * Forget the LinkedIn connection.
	 *
	 * @return void
	 */
	public function handle_disconnect() {
		check_admin_referer( 'nhrrob_social_share_linkedin_disconnect' );
		Settings::require_manager();

		$settings                 = Settings::get();
		$settings['access_token'] = '';
		$settings['expires_at']   = 0;
		$settings['person_id']    = '';
		$settings['person_name']  = '';

		Settings::update( $settings );

		wp_safe_redirect( Settings::page_url( 'disconnected' ) );
		exit;
	}
}

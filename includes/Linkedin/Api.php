<?php
/**
 * Sends requests to the LinkedIn API as the connected member.
 *
 * @package NHRRob\SocialShare
 */

namespace NHRRob\SocialShare\Linkedin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Api Class
 */
class Api {

	/**
	 * Sends data as JSON.
	 *
	 * @param string $url  Address.
	 * @param array  $data Data.
	 * @return array|\WP_Error The answer.
	 */
	public static function post( $url, $data ) {
		return self::send(
			'POST',
			$url,
			wp_json_encode( $data ),
			array(
				'Content-Type'              => 'application/json',
				'X-Restli-Protocol-Version' => '2.0.0',
			)
		);
	}

	/**
	 * Sends one request with the access token.
	 *
	 * @param string $method  HTTP method.
	 * @param string $url     Address.
	 * @param string $body    Request body.
	 * @param array  $headers Headers besides the token.
	 * @return array|\WP_Error The answer, or an error for any status but 2xx.
	 */
	public static function send( $method, $url, $body, $headers = array() ) {
		$response = wp_remote_request(
			$url,
			array(
				'method'  => $method,
				'timeout' => 15,
				'headers' => array( 'Authorization' => 'Bearer ' . Settings::get()['access_token'] ) + $headers,
				'body'    => $body,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		$data = is_array( $data ) ? $data : array();

		if ( $code < 200 || $code > 299 ) {
			return new \WP_Error(
				'nhrrob_social_share_linkedin_api',
				sprintf(
					/* translators: 1: HTTP status code, 2: error message from LinkedIn */
					__( 'LinkedIn answered %1$d: %2$s', 'nhrrob-social-share' ),
					$code,
					isset( $data['message'] ) ? sanitize_text_field( $data['message'] ) : __( 'no details', 'nhrrob-social-share' )
				)
			);
		}

		return $data;
	}
}

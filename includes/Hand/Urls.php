<?php
/**
 * A box on the post edit screen where the author pastes the address of the
 * post on X, Facebook, Reddit or Medium after sharing it by hand. The Posts
 * list then links to it (`RowActions`). Emptying a field makes its link a
 * share link again.
 *
 * @package NHRRob\SocialShare
 */

namespace NHRRob\SocialShare\Hand;

use NHRRob\SocialShare\Keys;

defined( 'ABSPATH' ) || exit;

/**
 * Urls Class
 */
class Urls {

	const NONCE = 'nhrrob_social_share_hand_url';

	/**
	 * The host each network's posts live on, by the start of the network's key.
	 */
	const HOSTS = array(
		'x'        => array( 'x.com', 'twitter.com' ),
		'facebook' => array( 'facebook.com', 'fb.com' ),
		'r'        => array( 'reddit.com' ),
		'u'        => array( 'reddit.com' ),
		'reddit'   => array( 'reddit.com' ),
		'medium'   => array( 'medium.com' ),
	);

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'add_meta_boxes_post', array( $this, 'add_box' ) );
		add_action( 'save_post_post', array( $this, 'save' ) );
	}

	/**
	 * Add the box to a published post.
	 *
	 * @param \WP_Post $post Post.
	 * @return void
	 */
	public function add_box( $post ) {
		if ( 'publish' === $post->post_status && '' === $post->post_password ) {
			add_meta_box( 'nhrrob-social-share-hand', __( 'Shared on', 'nhrrob-social-share' ), array( $this, 'render' ), 'post', 'side', 'low' );
		}
	}

	/**
	 * Show one address field per share link.
	 *
	 * @param \WP_Post $post Post.
	 * @return void
	 */
	public function render( $post ) {
		$posted = (array) get_post_meta( $post->ID, Keys::HAND_URL, true );

		wp_nonce_field( self::NONCE, self::NONCE );

		foreach ( Links::all( $post ) as $network => $link ) {
			$id = 'nhrrob-social-share-url-' . $network;

			printf(
				'<p><label for="%1$s">%2$s</label><input type="url" class="widefat" id="%1$s" name="nhrrob_social_share_url[%3$s]" value="%4$s" placeholder="https://"></p>',
				esc_attr( $id ),
				esc_html( $link['done'] ),
				esc_attr( $network ),
				esc_attr( isset( $posted[ $network ] ) ? $posted[ $network ] : '' )
			);
		}
	}

	/**
	 * Keep the addresses that are on their network's site. Only runs when the box was sent.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save( $post_id ) {
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( sanitize_key( $_POST[ self::NONCE ] ), self::NONCE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$urls = isset( $_POST['nhrrob_social_share_url'] ) ? (array) wp_unslash( $_POST['nhrrob_social_share_url'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each address is checked below.
		// A network the box no longer shows keeps its address.
		$posted = array_diff_key( array_filter( (array) get_post_meta( $post_id, Keys::HAND_URL, true ) ), $urls );

		foreach ( $urls as $network => $url ) {
			$network = sanitize_key( $network );

			unset( $posted[ $network ] );
			$url = esc_url_raw( trim( (string) $url ), array( 'https' ) );

			if ( $url && self::on_network( $network, $url ) ) {
				$posted[ $network ] = $url;
			}
		}

		update_post_meta( $post_id, Keys::HAND_URL, $posted );
	}

	/**
	 * Whether an address is on the site of the network its key names.
	 *
	 * @param string $network Key, such as `x`, `facebook`, `reddit`, `r_wordpress`, `u_nhrrob` or `medium`.
	 * @param string $url     Address.
	 * @return bool
	 */
	public static function on_network( $network, $url ) {
		$prefix = strtok( $network, '_' );
		$host   = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );

		foreach ( isset( self::HOSTS[ $prefix ] ) ? self::HOSTS[ $prefix ] : array() as $allowed ) {
			if ( $host === $allowed || substr( $host, -strlen( '.' . $allowed ) ) === '.' . $allowed ) {
				return true;
			}
		}

		return false;
	}
}

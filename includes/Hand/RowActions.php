<?php
/**
 * The share links under each published post in the Posts list: they open X,
 * Facebook, Reddit or Medium with the post filled in, and remember that the
 * author used them. The address of the post on the network, once pasted back
 * (`Urls`), turns the link into a link to that post.
 *
 * @package NHRRob\SocialShare
 */

namespace NHRRob\SocialShare\Hand;

use NHRRob\SocialShare\Keys;
use NHRRob\SocialShare\Linkedin\Image;

defined( 'ABSPATH' ) || exit;

/**
 * RowActions Class
 */
class RowActions {

	const ACTION = 'nhrrob_social_share_hand';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'post_row_actions', array( $this, 'row_actions' ), 11, 2 );
		add_action( 'admin_post_' . self::ACTION, array( $this, 'open' ) );
	}

	/**
	 * Add the links under a published post.
	 *
	 * @param array    $actions Row actions.
	 * @param \WP_Post $post    Post.
	 * @return array
	 */
	public function row_actions( $actions, $post ) {
		if ( 'post' !== $post->post_type || 'publish' !== $post->post_status || '' !== $post->post_password || ! current_user_can( 'edit_post', $post->ID ) ) {
			return $actions;
		}

		$used    = (array) get_post_meta( $post->ID, Keys::HAND_USED, true );
		$posted  = (array) get_post_meta( $post->ID, Keys::HAND_URL, true );
		$picture = false;

		foreach ( Links::all( $post ) as $network => $link ) {
			$key = 'nhrrob_social_share_' . $network;

			// Once its address is pasted back, the link opens the post on the network, and layout C gets its follow-up.
			if ( ! empty( $posted[ $network ] ) ) {
				$actions[ $key ] = self::anchor( $posted[ $network ], $link['done'] );

				if ( 'c' === $link['layout'] ) {
					$actions[ $key . '_link' ] = self::follow_up( $network, $posted[ $network ], get_permalink( $post ) );
				}

				continue;
			}

			$url = wp_nonce_url( admin_url( 'admin-post.php?action=' . self::ACTION . '&network=' . $network . '&post=' . $post->ID ), self::ACTION . '_' . $post->ID );

			// A used link stays a link: the page may have been closed without posting.
			$actions[ $key ] = self::anchor( $url, empty( $used[ $network ] ) ? $link['label'] : $link['again'], $link['copy'], $link['layout'] ? Layout::hint( $network, $link['layout'] ) : '' );

			$picture = $picture || ( 'b' === $link['layout'] || ( 'facebook' === $network && 'c' === $link['layout'] ) );
		}

		$image = $picture ? Image::url( $post ) : '';

		if ( $image ) {
			$actions['nhrrob_social_share_picture'] = sprintf( '<a href="%s" download>%s</a>', esc_url( $image ), esc_html__( 'Download picture', 'nhrrob-social-share' ) );
		}

		return $actions;
	}

	/**
	 * The link that follows a layout C post: on X a reply with the post's
	 * link filled in, on Facebook the post itself, with the link copied for
	 * its first comment.
	 *
	 * @param string $network   `x` or `facebook`.
	 * @param string $posted    The post's address on the network.
	 * @param string $permalink The post's address on the site.
	 * @return string
	 */
	private static function follow_up( $network, $posted, $permalink ) {
		if ( 'x' === $network && preg_match( '#/status/(\d+)#', $posted, $match ) ) {
			return self::anchor( 'https://x.com/intent/post?in_reply_to=' . $match[1] . '&text=' . rawurlencode( $permalink ), __( 'Reply with the link on X', 'nhrrob-social-share' ) );
		}

		return self::anchor( $posted, __( 'Copy the link for the first comment', 'nhrrob-social-share' ), $permalink );
	}

	/**
	 * A link that opens in a new tab. With `$copy`, the click also puts that
	 * text on the clipboard (`assets/hand.js`).
	 *
	 * @param string $url   Address.
	 * @param string $text  Link text.
	 * @param string $copy  Text to copy, or empty.
	 * @param string $title Hover text, or empty.
	 * @return string
	 */
	private static function anchor( $url, $text, $copy = '', $title = '' ) {
		$attributes = '';

		if ( '' !== $copy ) {
			$attributes .= sprintf( ' data-nhrrob-social-share-copy="%s" data-nhrrob-social-share-copied="%s"', esc_attr( $copy ), esc_attr__( 'copied', 'nhrrob-social-share' ) );
		}

		if ( '' !== $title ) {
			$attributes .= sprintf( ' title="%s"', esc_attr( $title ) );
		}

		return sprintf( '<a href="%s" target="_blank" rel="noopener"%s>%s</a>', esc_url( $url ), $attributes, esc_html( $text ) );
	}

	/**
	 * Remember that the link was used, and with which layout, then go to the network's page.
	 *
	 * @return void
	 */
	public function open() {
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		$network = isset( $_GET['network'] ) ? sanitize_key( $_GET['network'] ) : '';

		check_admin_referer( self::ACTION . '_' . $post_id );

		$post  = get_post( $post_id );
		$links = $post ? Links::all( $post ) : array();

		if ( ! isset( $links[ $network ] ) || 'publish' !== $post->post_status || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'You are not allowed to share this post.', 'nhrrob-social-share' ), 403 );
		}

		$used             = (array) get_post_meta( $post_id, Keys::HAND_USED, true );
		$used[ $network ] = time();

		update_post_meta( $post_id, Keys::HAND_USED, array_filter( $used ) );

		if ( $links[ $network ]['layout'] ) {
			Layout::save( $post_id, $network, $links[ $network ]['layout'] );
		}

		add_filter( 'allowed_redirect_hosts', array( $this, 'allow_networks' ) );
		wp_safe_redirect( $links[ $network ]['url'] );
		exit;
	}

	/**
	 * Let the redirect leave the site for the networks.
	 *
	 * @param array $hosts Hosts a safe redirect may go to.
	 * @return array
	 */
	public function allow_networks( $hosts ) {
		return array_merge( $hosts, array( 'x.com', 'www.facebook.com', 'www.reddit.com', 'medium.com' ) );
	}
}

<?php
/**
 * Shares one post on LinkedIn and remembers the result with the post.
 *
 * @package NHRRob\SocialShare
 */

namespace NHRRob\SocialShare\Linkedin;

use NHRRob\SocialShare\Keys;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Share Class
 */
class Share {

	/**
	 * Shares a published post on LinkedIn in one of the three patterns (see
	 * Pattern), and saves the share with the pattern and picture it used.
	 *
	 * @param int $post_id Post ID.
	 * @return true|\WP_Error
	 */
	public static function post( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post || 'post' !== $post->post_type || 'publish' !== $post->post_status || '' !== $post->post_password ) {
			return new \WP_Error( 'nhrrob_social_share_linkedin_post', __( 'Only published, public posts can be shared.', 'nhrrob-social-share' ) );
		}

		$shared = get_post_meta( $post_id, Keys::LINKEDIN_SHARE, true );

		if ( ! empty( $shared['id'] ) ) {
			return new \WP_Error( 'nhrrob_social_share_linkedin_shared', __( 'This post is already shared on LinkedIn.', 'nhrrob-social-share' ) );
		}

		$text     = self::text( $post );
		$own_link = false !== stripos( $text, 'https://' ) || false !== stripos( $text, 'http://' );
		$pattern  = Pattern::choose( $post_id, $own_link );

		if ( ! Settings::is_connected() ) {
			$result = new \WP_Error( 'nhrrob_social_share_linkedin_connection', __( 'LinkedIn is not connected, or the connection has expired.', 'nhrrob-social-share' ) );
		} else {
			$result = self::send( $post, $text, $own_link, $pattern );
		}

		if ( is_wp_error( $result ) ) {
			update_post_meta( $post_id, Keys::LINKEDIN_SHARE, array( 'error' => $result->get_error_message() ) );

			return $result;
		}

		$shared = array(
			'id'      => $result,
			'time'    => time(),
			'pattern' => $pattern,
			'image'   => Image::source( $post ),
		);

		$comment = 'c' === $pattern ? self::comment( $post, $result ) : true;

		if ( is_wp_error( $comment ) ) {
			$shared['error'] = sprintf(
				/* translators: %s: error message */
				__( 'Shared on LinkedIn, but the comment with the link failed, so add it by hand. %s', 'nhrrob-social-share' ),
				$comment->get_error_message()
			);
		}

		update_post_meta( $post_id, Keys::LINKEDIN_SHARE, $shared );

		/** This action is documented in includes/Hand/RowActions.php */
		do_action( 'nhrrob_social_share_shared', $post_id, 'linkedin' );

		return is_wp_error( $comment ) ? $comment : true;
	}

	/**
	 * The share's text: the post's LinkedIn text, or its title and excerpt.
	 * Facebook, shared by hand, uses its own text when the post has one, and
	 * the same text otherwise.
	 *
	 * @param \WP_Post $post    Post.
	 * @param string   $network `linkedin` or `facebook`.
	 * @return string
	 */
	public static function text( $post, $network = 'linkedin' ) {
		$text = 'facebook' === $network ? (string) get_post_meta( $post->ID, Keys::FACEBOOK_TEXT, true ) : '';
		$text = '' === $text ? (string) get_post_meta( $post->ID, Keys::LINKEDIN_TEXT, true ) : $text;

		if ( '' === $text ) {
			$text = html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ) . "\n\n" . html_entity_decode( wp_strip_all_tags( get_the_excerpt( $post ) ), ENT_QUOTES, 'UTF-8' );
		}

		/**
		 * A share's text, before the layout adds the link or a line pointing to it.
		 *
		 * @param string   $text    The text.
		 * @param \WP_Post $post    The post.
		 * @param string   $network `linkedin`, `facebook` or `x`.
		 */
		return trim( (string) apply_filters( 'nhrrob_social_share_text', trim( $text ), $post, $network ) );
	}

	/**
	 * Posts the share. A: the text with a link card. B: the text, then the
	 * post's link unless the text has a link of its own, with the picture.
	 * C: the text and a line pointing to the first comment, with the picture.
	 *
	 * @param \WP_Post $post     Post.
	 * @param string   $text     The share's text.
	 * @param bool     $own_link Whether the text already has a link.
	 * @param string   $pattern  a, b or c.
	 * @return string|\WP_Error The share's URN.
	 */
	private static function send( $post, $text, $own_link, $pattern ) {
		$title = html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' );

		if ( 'a' === $pattern ) {
			$media = array(
				'status'      => 'READY',
				'originalUrl' => get_permalink( $post ),
				'title'       => array( 'text' => $title ),
				'description' => array( 'text' => html_entity_decode( wp_strip_all_tags( get_the_excerpt( $post ) ), ENT_QUOTES, 'UTF-8' ) ),
			);
			$image = Image::url( $post );

			// LinkedIn does not read the page's og:image for a share made through the API, so the picture goes with it.
			if ( $image ) {
				$media['thumbnails'] = array( array( 'url' => $image ) );
			}

			$content = array(
				'shareCommentary'    => array( 'text' => $text ),
				'shareMediaCategory' => 'ARTICLE',
				'media'              => array( $media ),
			);
		} else {
			$asset = Image::upload( $post );

			if ( is_wp_error( $asset ) ) {
				return $asset;
			}

			if ( 'c' === $pattern ) {
				$text .= "\n\n" . __( 'Link in the first comment.', 'nhrrob-social-share' );
			} elseif ( ! $own_link ) {
				$text .= "\n\n" . get_permalink( $post );
			}

			$content = array(
				'shareCommentary'    => array( 'text' => $text ),
				'shareMediaCategory' => $asset ? 'IMAGE' : 'NONE',
			);

			if ( $asset ) {
				$content['media'] = array(
					array(
						'status' => 'READY',
						'media'  => $asset,
						'title'  => array( 'text' => $title ),
					),
				);
			}
		}

		$body = Api::post(
			'https://api.linkedin.com/v2/ugcPosts',
			array(
				'author'          => 'urn:li:person:' . Settings::get()['person_id'],
				'lifecycleState'  => 'PUBLISHED',
				'specificContent' => array( 'com.linkedin.ugc.ShareContent' => $content ),
				'visibility'      => array( 'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC' ),
			)
		);

		if ( is_wp_error( $body ) ) {
			return $body;
		}

		return empty( $body['id'] ) ? new \WP_Error( 'nhrrob_social_share_linkedin_api', __( 'LinkedIn did not return the share.', 'nhrrob-social-share' ) ) : sanitize_text_field( $body['id'] );
	}

	/**
	 * Pattern C: the post's link as the share's first comment.
	 *
	 * @param \WP_Post $post  Post.
	 * @param string   $share The share's URN.
	 * @return array|\WP_Error
	 */
	private static function comment( $post, $share ) {
		return Api::post(
			'https://api.linkedin.com/v2/socialActions/' . rawurlencode( $share ) . '/comments',
			array(
				'actor'   => 'urn:li:person:' . Settings::get()['person_id'],
				'object'  => $share,
				'message' => array(
					/* translators: %s: post URL */
					'text' => sprintf( __( 'The full post: %s', 'nhrrob-social-share' ), get_permalink( $post ) ),
				),
			)
		);
	}
}

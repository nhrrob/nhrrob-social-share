<?php
/**
 * Which of three layouts a share on X or Facebook uses, so they take turns
 * and can be compared, like the LinkedIn shares (`Linkedin\Pattern`).
 *
 * @package NHRRob\SocialShare
 */

namespace NHRRob\SocialShare\Hand;

use NHRRob\SocialShare\Keys;
use NHRRob\SocialShare\Linkedin\Pattern;
use NHRRob\SocialShare\Options;

defined( 'ABSPATH' ) || exit;

/**
 * Layout Class
 */
class Layout {

	/**
	 * The networks that take turns between layouts.
	 */
	const NETWORKS = array( 'x', 'facebook' );

	/**
	 * Shares so far per network and layout, read once per request.
	 *
	 * @var array
	 */
	private static $counts = array();

	/**
	 * What each layout is on a network, as shown on screen.
	 *
	 * @param string $network `x` or `facebook`.
	 * @return array Layout => label.
	 */
	public static function labels( $network ) {
		if ( 'x' === $network ) {
			return array(
				'a' => __( 'A: link card', 'nhrrob-social-share' ),
				'b' => __( 'B: picture, link at the end of the text', 'nhrrob-social-share' ),
				'c' => __( 'C: text only, link in the reply', 'nhrrob-social-share' ),
			);
		}

		return Pattern::labels();
	}

	/**
	 * What to do by hand for a layout, shown when hovering its link.
	 *
	 * @param string $network `x` or `facebook`.
	 * @param string $layout  a, b or c.
	 * @return string
	 */
	public static function hint( $network, $layout ) {
		$hints = array(
			'x_a'        => __( 'Layout A: the text and the link are filled in.', 'nhrrob-social-share' ),
			'x_b'        => __( 'Layout B: attach the picture (Download picture), then post.', 'nhrrob-social-share' ),
			'x_c'        => __( 'Layout C: post the text, paste its address into "Shared on", then use "Reply with the link on X".', 'nhrrob-social-share' ),
			'facebook_a' => __( 'Layout A: the text is copied; paste it above the link card.', 'nhrrob-social-share' ),
			'facebook_b' => __( 'Layout B: the text with the link is copied; paste it in a new post and add the picture (Download picture).', 'nhrrob-social-share' ),
			'facebook_c' => __( 'Layout C: the text is copied; paste it in a new post with the picture, then comment the link.', 'nhrrob-social-share' ),
		);

		return isset( $hints[ $network . '_' . $layout ] ) ? $hints[ $network . '_' . $layout ] : '';
	}

	/**
	 * A post's layout on a network: the one saved when its link was first
	 * used, else B when the text has a link of its own (so the share never
	 * carries two), else the layout used least so far, or A when the layouts
	 * do not take turns.
	 *
	 * @param int    $post_id  Post ID.
	 * @param string $network  `x` or `facebook`.
	 * @param bool   $own_link Whether the text already has a link.
	 * @return string
	 */
	public static function choose( $post_id, $network, $own_link ) {
		$saved = (array) get_post_meta( $post_id, Keys::HAND_LAYOUT, true );

		if ( isset( $saved[ $network ][0] ) && in_array( $saved[ $network ][0], Pattern::ALL, true ) ) {
			return $saved[ $network ][0];
		}

		if ( $own_link ) {
			return 'b';
		}

		return Options::get()['rotate'] ? Pattern::pick( self::counts( $network ) ) : 'a';
	}

	/**
	 * Keep the layout a post's link used the first time.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $network `x` or `facebook`.
	 * @param string $layout  a, b or c.
	 * @return void
	 */
	public static function save( $post_id, $network, $layout ) {
		$saved = (array) get_post_meta( $post_id, Keys::HAND_LAYOUT, true );

		if ( empty( $saved[ $network ] ) && in_array( $layout, Pattern::ALL, true ) ) {
			$saved[ $network ] = array( $layout, time() );
			update_post_meta( $post_id, Keys::HAND_LAYOUT, $saved );
		}
	}

	/**
	 * Every share on a network with its layout.
	 *
	 * @param string $network `x` or `facebook`.
	 * @return array Post ID => [ layout, time ].
	 */
	public static function shares( $network ) {
		$shares = array();
		$ids    = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'any',
				'fields'         => 'ids',
				'posts_per_page' => -1,
				'meta_key'       => Keys::HAND_LAYOUT, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- a few dozen posts, once per request.
			)
		);

		foreach ( $ids as $id ) {
			$saved = (array) get_post_meta( $id, Keys::HAND_LAYOUT, true );

			if ( isset( $saved[ $network ][0], $saved[ $network ][1] ) ) {
				$shares[ (int) $id ] = array( (string) $saved[ $network ][0], (int) $saved[ $network ][1] );
			}
		}

		return $shares;
	}

	/**
	 * How many shares on a network have used each layout.
	 *
	 * @param string $network `x` or `facebook`.
	 * @return array Layout => shares.
	 */
	private static function counts( $network ) {
		if ( ! isset( self::$counts[ $network ] ) ) {
			self::$counts[ $network ] = array_fill_keys( Pattern::ALL, 0 );

			foreach ( self::shares( $network ) as $share ) {
				if ( isset( self::$counts[ $network ][ $share[0] ] ) ) {
					++self::$counts[ $network ][ $share[0] ];
				}
			}
		}

		return self::$counts[ $network ];
	}
}

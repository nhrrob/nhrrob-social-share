<?php
/**
 * Which of three layouts a LinkedIn share uses, so they can be compared:
 * A, a link card; B, an image with the link at the end of the text; C, an
 * image with the link in the first comment.
 *
 * @package NHRRob\SocialShare
 */

namespace NHRRob\SocialShare\Linkedin;

use NHRRob\SocialShare\Keys;
use NHRRob\SocialShare\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pattern Class
 */
class Pattern {

	/**
	 * The patterns, in the order they take turns.
	 */
	const ALL = array( 'a', 'b', 'c' );

	/**
	 * What each pattern is called on screen.
	 *
	 * @return array Pattern => label.
	 */
	public static function labels() {
		return array(
			'a' => __( 'A: link card', 'nhrrob-social-share' ),
			'b' => __( 'B: image, link at the end of the text', 'nhrrob-social-share' ),
			'c' => __( 'C: image, link in the first comment', 'nhrrob-social-share' ),
		);
	}

	/**
	 * The pattern for a post's share: the post's own choice, else B when its
	 * text has a link of its own (so the share never carries two), else the
	 * pattern used least so far, or A when the layouts do not take turns.
	 *
	 * @param int  $post_id  Post ID.
	 * @param bool $own_link Whether the share's text already has a link.
	 * @return string
	 */
	public static function choose( $post_id, $own_link ) {
		$chosen = strtolower( trim( (string) get_post_meta( $post_id, Keys::LINKEDIN_LAYOUT, true ) ) );

		if ( in_array( $chosen, self::ALL, true ) ) {
			return $chosen;
		}

		if ( $own_link ) {
			return 'b';
		}

		return Options::get()['rotate'] ? self::pick( self::counts() ) : 'a';
	}

	/**
	 * The pattern used least, the earliest in ALL on a tie, so they take turns.
	 *
	 * @param array $counts Pattern => shares so far.
	 * @return string
	 */
	public static function pick( $counts ) {
		$least = self::ALL[0];

		foreach ( self::ALL as $pattern ) {
			if ( ( isset( $counts[ $pattern ] ) ? (int) $counts[ $pattern ] : 0 ) < ( isset( $counts[ $least ] ) ? (int) $counts[ $least ] : 0 ) ) {
				$least = $pattern;
			}
		}

		return $least;
	}

	/**
	 * The pattern a share used: the one saved with it, or for a share made
	 * before patterns were saved, A, which a site can change with the filter
	 * `nhrrob_social_share_unsaved_pattern` (the blog this came from knows
	 * when it changed layouts).
	 *
	 * @param array $shared The LinkedIn share meta.
	 * @return string Empty for a post that was not shared.
	 */
	public static function of( $shared ) {
		if ( empty( $shared['id'] ) ) {
			return '';
		}

		if ( isset( $shared['pattern'] ) && in_array( $shared['pattern'], self::ALL, true ) ) {
			return $shared['pattern'];
		}

		/**
		 * The pattern of a share saved without one.
		 *
		 * @param string $pattern Pattern; A by default.
		 * @param int    $time    When the share was made.
		 */
		$pattern = (string) apply_filters( 'nhrrob_social_share_unsaved_pattern', 'a', isset( $shared['time'] ) ? (int) $shared['time'] : 0 );

		return in_array( $pattern, self::ALL, true ) ? $pattern : 'a';
	}

	/**
	 * Every shared post's share meta.
	 *
	 * @return array Post ID => share meta.
	 */
	public static function shares() {
		$shares = array();
		$ids    = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'any',
				'fields'         => 'ids',
				'posts_per_page' => -1,
				'meta_key'       => Keys::LINKEDIN_SHARE, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- a few dozen posts, on a share or the settings page.
			)
		);

		foreach ( $ids as $id ) {
			$shared = get_post_meta( $id, Keys::LINKEDIN_SHARE, true );

			if ( ! empty( $shared['id'] ) ) {
				$shares[ (int) $id ] = $shared;
			}
		}

		return $shares;
	}

	/**
	 * How many shares have used each pattern since it was first saved with them.
	 *
	 * @return array Pattern => shares.
	 */
	private static function counts() {
		$counts = array_fill_keys( self::ALL, 0 );

		foreach ( self::shares() as $shared ) {
			if ( isset( $shared['pattern'] ) && isset( $counts[ $shared['pattern'] ] ) ) {
				++$counts[ $shared['pattern'] ];
			}
		}

		return $counts;
	}
}

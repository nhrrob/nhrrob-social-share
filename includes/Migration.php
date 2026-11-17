<?php
/**
 * Brings over what the code this plugin grew out of saved (the site plugin of
 * nazmulrobin.com/blog), so a site that ran it keeps its LinkedIn connection
 * and every post's share history. Runs once, on activation or the first load
 * after it; never overwrites what the plugin has saved itself.
 *
 * @package NHRRob\SocialShare
 */

namespace NHRRob\SocialShare;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Migration Class
 */
class Migration {

	/**
	 * Option: when the migration ran, and what it copied.
	 */
	const DONE = 'nhrrob_social_share_migrated';

	/**
	 * Old option => new option.
	 */
	const OPTIONS = [
		'nazmulrobin_linkedin' => Linkedin\Settings::OPTION,
	];

	/**
	 * Old post meta => new post meta.
	 */
	const META = [
		'_nazmulrobin_linkedin_share'      => Keys::LINKEDIN_SHARE,
		'_nazmulrobin_linkedin_text'       => Keys::LINKEDIN_TEXT,
		'_nazmulrobin_linkedin_pattern'    => Keys::LINKEDIN_LAYOUT,
		'_nazmulrobin_author_share'        => Keys::HAND_USED,
		'_nazmulrobin_author_share_url'    => Keys::HAND_URL,
		'_nazmulrobin_author_share_layout' => Keys::HAND_LAYOUT,
		'_nazmulrobin_x_text'              => Keys::X_TEXT,
		'_nazmulrobin_reddit'              => Keys::REDDIT,
	];

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_init', [ __CLASS__, 'run' ] );
	}

	/**
	 * Copy the old option and post meta to the plugin's keys, once.
	 *
	 * @return void
	 */
	public static function run() {
		if ( get_option( self::DONE ) ) {
			return;
		}

		$copied = [
			'options' => 0,
			'meta'    => 0,
		];

		foreach ( self::OPTIONS as $old => $new ) {
			$value = get_option( $old, null );

			if ( null !== $value && null === get_option( $new, null ) ) {
				update_option( $new, $value, false );
				++$copied['options'];
			}
		}

		foreach ( self::META as $old => $new ) {
			$copied['meta'] += self::copy_meta( $old, $new );
		}

		update_option(
			self::DONE,
			[
				'time'   => time(),
				'copied' => $copied,
			],
			false
		);
	}

	/**
	 * Copy one post meta key to another on every post that has the old one and not the new one.
	 *
	 * @param string $from Old key.
	 * @param string $to   New key.
	 * @return int Posts copied.
	 */
	private static function copy_meta( $from, $to ) {
		$ids   = get_posts(
			[
				'post_type'      => 'any',
				'post_status'    => 'any',
				'fields'         => 'ids',
				'posts_per_page' => -1,
				'meta_key'       => $from, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- once, on activation.
			]
		);
		$count = 0;

		foreach ( $ids as $id ) {
			if ( metadata_exists( 'post', $id, $to ) ) {
				continue;
			}

			$value = get_post_meta( $id, $from, true );

			if ( '' !== $value && [] !== $value ) {
				update_post_meta( $id, $to, $value );
				++$count;
			}
		}

		return $count;
	}
}

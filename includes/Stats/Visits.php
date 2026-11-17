<?php
/**
 * Shares and the visits they brought, per layout, for LinkedIn, X and
 * Facebook, so the layouts can be compared. Read from NHR Post Insights'
 * referrer table when that plugin is active.
 *
 * @package NHRRob\SocialShare
 */

namespace NHRRob\SocialShare\Stats;

use NHRRob\PostInsights\Installer;
use NHRRob\SocialShare\Hand\Layout;
use NHRRob\SocialShare\Linkedin\Pattern;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Visits Class
 *
 * A share counts the views Post Insights saw from its network on the day of
 * the share and the six after it, so old and new shares compare fairly.
 */
class Visits {

	/**
	 * Days a share's visits are counted for, from the day it was made.
	 */
	const DAYS = 7;

	/**
	 * Every network's table: its labels and rows.
	 *
	 * @return array Network => [ name, labels, rows ], rows as layout => [ shares, visits, counting ].
	 */
	public static function all() {
		$linkedin = [];

		foreach ( Pattern::shares() as $id => $shared ) {
			$linkedin[ $id ] = [ Pattern::of( $shared ), (int) $shared['time'] ];
		}

		return [
			'linkedin' => [ 'LinkedIn', Pattern::labels(), self::rows( $linkedin, 'nhrrob_social_share_from_linkedin' ) ],
			'x'        => [ 'X', Layout::labels( 'x' ), self::rows( Layout::shares( 'x' ), 'nhrrob_social_share_from_x' ) ],
			'facebook' => [ 'Facebook', Layout::labels( 'facebook' ), self::rows( Layout::shares( 'facebook' ), 'nhrrob_social_share_from_facebook' ) ],
		];
	}

	/**
	 * Whether visits can be counted: NHR Post Insights is active.
	 *
	 * @return bool
	 */
	public static function available() {
		return class_exists( Installer::class );
	}

	/**
	 * Shares and the visits from one network per layout, in each share's first DAYS.
	 *
	 * @param array  $shares  Post ID => [ layout, time of the share ].
	 * @param string $network The network's key in `Networks::NETWORKS`.
	 * @return array Layout => [ shares, visits, counting ], where counting is the shares still in their first DAYS.
	 */
	public static function rows( $shares, $network ) {
		global $wpdb;

		$rows = array_fill_keys( Pattern::ALL, [ 0, 0, 0 ] );

		if ( ! $shares ) {
			return $rows;
		}

		$hosts = [];

		if ( self::available() ) {
			$ids = implode( ',', array_map( 'intval', array_keys( $shares ) ) );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- the settings screen only, from Post Insights' own table; $ids are integers.
			$hosts = (array) $wpdb->get_results( $wpdb->prepare( "SELECT post_id, day, host, views FROM %i WHERE post_id IN ($ids)", Installer::referrers_table() ) );
		}

		$today = wp_date( 'Y-m-d' );
		$range = [];

		foreach ( $shares as $id => $share ) {
			list( $layout, $time ) = $share;

			if ( ! isset( $rows[ $layout ] ) ) {
				continue;
			}

			$range[ $id ] = [ $layout, wp_date( 'Y-m-d', $time ), wp_date( 'Y-m-d', $time + ( self::DAYS - 1 ) * DAY_IN_SECONDS ) ];
			++$rows[ $layout ][0];

			if ( $range[ $id ][2] >= $today ) {
				++$rows[ $layout ][2];
			}
		}

		foreach ( $hosts as $row ) {
			$id = (int) $row->post_id;

			if ( isset( $range[ $id ] ) && $row->day >= $range[ $id ][1] && $row->day <= $range[ $id ][2] && Networks::network( (string) $row->host ) === $network ) {
				$rows[ $range[ $id ][0] ][1] += (int) $row->views;
			}
		}

		return $rows;
	}
}

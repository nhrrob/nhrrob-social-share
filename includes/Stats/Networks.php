<?php
/**
 * Visits from each social network on the Post Insights Overview: LinkedIn, X,
 * Facebook, Reddit and Medium, grouped from the referring hosts Post Insights keeps.
 *
 * @package NHRRob\SocialShare
 */

namespace NHRRob\SocialShare\Stats;

use NHRRob\PostInsights\Installer;
use NHRRob\PostInsights\Store;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Networks Class
 *
 * One tile per network, through Post Insights' filters (0.2.0 and later). The
 * count is the views whose referring host belongs to the network, in the
 * range Overview shows. Without Post Insights the filters never run.
 */
class Networks {

	/**
	 * Tile key => [ label, hosts ]. A host also matches its subdomains.
	 */
	const NETWORKS = array(
		'nhrrob_social_share_from_linkedin' => array( 'LinkedIn', array( 'linkedin.com', 'lnkd.in' ) ),
		'nhrrob_social_share_from_x'        => array( 'X', array( 'x.com', 't.co', 'twitter.com' ) ),
		'nhrrob_social_share_from_facebook' => array( 'Facebook', array( 'facebook.com', 'fb.com', 'fb.me' ) ),
		'nhrrob_social_share_from_reddit'   => array( 'Reddit', array( 'reddit.com', 'redd.it' ) ),
		'nhrrob_social_share_from_medium'   => array( 'Medium', array( 'medium.com' ) ),
	);

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'nhrrob_post_insights_columns', array( $this, 'declare' ) );
		add_filter( 'nhrrob_post_insights_stats', array( $this, 'fill_tiles' ), 10, 4 );
	}

	/**
	 * The tiles.
	 *
	 * @param array $declared Columns and tiles declared so far.
	 * @return array
	 */
	public function declare( $declared ) {
		foreach ( self::NETWORKS as $key => $network ) {
			$declared['tiles'][] = array(
				'key'   => $key,
				/* translators: %s: name of a social network. */
				'label' => sprintf( __( 'From %s', 'nhrrob-social-share' ), $network[0] ),
				'kind'  => 'number',
			);
		}

		return $declared;
	}

	/**
	 * Fill the tiles from every referring host in the range.
	 *
	 * @param array $stats   Numbers for the dashboard.
	 * @param int   $days    Number of days, or 0 for all time.
	 * @param int   $post    Post ID, or 0 for the whole site.
	 * @param array $filters The range's first and last day (`from`, `to`), Post Insights 0.4.0 and later.
	 * @return array
	 */
	public function fill_tiles( $stats, $days, $post = 0, $filters = array() ) {
		global $wpdb;

		if ( ! class_exists( Store::class ) || ! class_exists( Installer::class ) ) {
			return $stats;
		}

		$stats['extra'] = isset( $stats['extra'] ) ? (array) $stats['extra'] : array();

		foreach ( self::NETWORKS as $key => $network ) {
			$stats['extra'][ $key ] = 0;
		}

		$from = ! empty( $filters['from'] ) ? (string) $filters['from'] : Store::from( $days );
		$to   = ! empty( $filters['to'] ) ? (string) $filters['to'] : current_time( 'Y-m-d' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- one read for the dashboard, from Post Insights' own table.
		$hosts = $wpdb->get_results( $wpdb->prepare( 'SELECT host, SUM(views) AS views FROM %i WHERE day BETWEEN %s AND %s GROUP BY host', Installer::referrers_table(), $from, $to ) );

		foreach ( (array) $hosts as $row ) {
			$key = self::network( (string) $row->host );

			if ( $key ) {
				$stats['extra'][ $key ] += (int) $row->views;
			}
		}

		return $stats;
	}

	/**
	 * The tile key of the network a host belongs to.
	 *
	 * @param string $host Host name, lower case, without "www.".
	 * @return string Empty for a host of no network.
	 */
	public static function network( $host ) {
		foreach ( self::NETWORKS as $key => $network ) {
			foreach ( $network[1] as $domain ) {
				if ( $host === $domain || substr( $host, -strlen( '.' . $domain ) ) === '.' . $domain ) {
					return $key;
				}
			}
		}

		return '';
	}
}

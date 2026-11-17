<?php
/**
 * The plugin's settings, kept in one option, with their defaults and how each
 * value is cleaned before it is saved.
 *
 * @package NHRRob\SocialShare
 */

namespace NHRRob\SocialShare;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Options Class
 */
class Options {

	const OPTION = 'nhrrob_social_share_settings';

	/**
	 * The share buttons a reader sees, in this order.
	 */
	const BUTTONS = [ 'linkedin', 'x', 'reddit', 'facebook', 'whatsapp', 'email', 'copy' ];

	/**
	 * The networks shared to by hand from the Posts list.
	 */
	const HAND = [ 'x', 'facebook', 'reddit', 'medium' ];

	/**
	 * Colours of the drawn card, as #rrggbb.
	 */
	const CARD = [
		'background' => '#11161C',
		'text'       => '#E8EDF2',
		'muted'      => '#A3AFBB',
		'accent'     => '#7BDB4F',
		'dot'        => '#62CB31',
	];

	/**
	 * The settings, with defaults for anything not saved.
	 *
	 * @return array
	 */
	public static function get() {
		$saved = get_option( self::OPTION, [] );

		return self::clean( is_array( $saved ) ? $saved : [] );
	}

	/**
	 * Save settings, cleaned. Values not given keep what is saved.
	 *
	 * @param array $values New values.
	 * @return array What is saved now.
	 */
	public static function update( $values ) {
		$settings = self::clean( array_merge( self::get(), (array) $values ) );

		update_option( self::OPTION, $settings, false );

		return $settings;
	}

	/**
	 * Every value checked against what it may be; an unknown one falls back to its default.
	 *
	 * @param array $values Values.
	 * @return array
	 */
	public static function clean( $values ) {
		$card = isset( $values['card'] ) && is_array( $values['card'] ) ? $values['card'] : [];

		foreach ( self::CARD as $key => $default ) {
			$color        = isset( $card[ $key ] ) ? sanitize_hex_color( (string) $card[ $key ] ) : '';
			$card[ $key ] = $color ? strtoupper( $color ) : $default;
		}

		return [
			'buttons'  => self::subset( isset( $values['buttons'] ) ? $values['buttons'] : self::BUTTONS, self::BUTTONS ),
			'previews' => isset( $values['previews'] ) && in_array( $values['previews'], [ 'auto', 'on', 'off' ], true ) ? $values['previews'] : 'auto',
			'card'     => array_intersect_key( $card, self::CARD ),
			'hand'     => self::subset( isset( $values['hand'] ) ? $values['hand'] : self::HAND, self::HAND ),
			'reddit'   => self::reddit_targets( isset( $values['reddit'] ) ? $values['reddit'] : [] ),
			'rotate'   => ! isset( $values['rotate'] ) || (bool) $values['rotate'],
		];
	}

	/**
	 * The known names from a list, in the list's order, each once.
	 *
	 * @param mixed $names Names.
	 * @param array $known Allowed names.
	 * @return array
	 */
	private static function subset( $names, $known ) {
		return array_values( array_unique( array_intersect( array_map( 'strval', (array) $names ), $known ) ) );
	}

	/**
	 * Valid Reddit targets: subreddits as `r/Name` (a bare name is a
	 * subreddit) and profiles as `u/name`. A string is split on commas.
	 *
	 * @param mixed $names Names.
	 * @return string[]
	 */
	public static function reddit_targets( $names ) {
		$targets = [];
		$names   = is_string( $names ) ? explode( ',', $names ) : (array) $names;

		foreach ( $names as $name ) {
			$name = trim( (string) $name );

			if ( preg_match( '#^/?u(?:ser)?/([A-Za-z0-9_-]{3,20})$#', $name, $match ) ) {
				$targets[] = 'u/' . $match[1];
			} elseif ( preg_match( '#^(?:/?r/)?([A-Za-z0-9_]{2,21})$#', $name, $match ) ) {
				$targets[] = 'r/' . $match[1];
			}
		}

		return array_values( array_unique( $targets ) );
	}
}

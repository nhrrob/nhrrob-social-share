<?php
/**
 * The small line icons beside the share buttons, as inline SVG.
 *
 * @package NHRRob\SocialShare
 */

namespace NHRRob\SocialShare\Buttons;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Icons Class
 *
 * Drawn on a 24 by 24 grid, one stroke width, in the text's colour, so
 * they look alike on every device and follow the theme.
 */
class Icons {

	/**
	 * The paths of each icon.
	 */
	const PATHS = array(
		'linkedin' => array( 'M4 9v11M4 4.5v.01M9 20v-6.5a3.5 3.5 0 0 1 7 0V20M9 9v11' ),
		'x'        => array( 'M4 4l16 16M20 4 4 20' ),
		'reddit'   => array( 'M12 8c-4.4 0-8 2.5-8 5.5S7.6 19 12 19s8-2.5 8-5.5S16.4 8 12 8Z', 'M9 13h.01M15 13h.01M9.5 16c1.5 1 3.5 1 5 0', 'M12 8l1-4 4 1' ),
		'facebook' => array( 'M14 8h3V4h-3a4 4 0 0 0-4 4v3H7v4h3v7h4v-7h3l1-4h-4V8.5A.5.5 0 0 1 14 8Z' ),
		'whatsapp' => array( 'M3 21l1.5-4.5A8.5 8.5 0 1 1 8 20Z', 'M9 9.5c0 3 2.5 5.5 5.5 5.5l1-1.5-2-1-1 .8c-.8-.4-1.4-1-1.8-1.8l.8-1-1-2Z' ),
		'email'    => array( 'M3 6h18v12H3Z', 'M3 7l9 6 9-6' ),
		'link'     => array( 'M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1', 'M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1' ),
	);

	/**
	 * One icon as markup. It is decoration: the word beside it carries the meaning.
	 *
	 * @param string $name Name of the icon.
	 * @return string Empty for an unknown name.
	 */
	public static function svg( $name ) {
		if ( ! isset( self::PATHS[ $name ] ) ) {
			return '';
		}

		$paths = '';

		foreach ( self::PATHS[ $name ] as $path ) {
			$paths .= sprintf( '<path d="%s"/>', esc_attr( $path ) );
		}

		return '<svg class="nhrrob-social-share-icon" aria-hidden="true" focusable="false" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $paths . '</svg>';
	}
}

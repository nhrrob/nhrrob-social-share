<?php
/**
 * Draws the picture a shared post shows when it has no featured image: a card
 * with its title, its category and the site's name, kept as a PNG in the
 * uploads folder.
 *
 * @package NHRRob\SocialShare
 */

namespace NHRRob\SocialShare\Preview;

use NHRRob\SocialShare\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Card Class
 */
class Card {

	const WIDTH  = 1200;
	const HEIGHT = 630;
	const FOLDER = 'nhrrob-social-share';

	/**
	 * The card is drawn at twice its size and scaled down, which smooths the dot's edge.
	 */
	const SCALE = 2;

	/**
	 * Title sizes in pixels, largest first. The first one that fits in three lines is used.
	 */
	const TITLE_SIZES = array( 68, 58, 50, 44 );

	/**
	 * Address of a post's card, drawing it first when it is missing or its text has changed.
	 *
	 * @param \WP_Post $post Post.
	 * @return string Empty when the card cannot be drawn.
	 */
	public static function url( $post ) {
		if ( ! function_exists( 'imagettftext' ) ) {
			return '';
		}

		$categories = get_the_category( $post->ID );
		$colors     = Options::get()['card'];

		/**
		 * The small line at the bottom left of the card: the post's first
		 * category by default.
		 *
		 * @param string   $label Label.
		 * @param \WP_Post $post  Post.
		 */
		$label = (string) apply_filters( 'nhrrob_social_share_card_label', $categories ? self::plain( $categories[0]->name ) : '', $post );
		$text  = array(
			'name'  => self::plain( get_bloginfo( 'name' ) ),
			'title' => self::plain( get_the_title( $post ) ),
			'label' => $label,
			'site'  => preg_replace( '#^https?://#', '', untrailingslashit( home_url() ) ),
		);

		// A new release or a changed text gives the card a new name, so no network keeps the old picture.
		$uploads = wp_get_upload_dir();
		$folder  = '/' . self::FOLDER;
		$file    = sprintf( '%s/%d-%s.png', $folder, $post->ID, substr( md5( NHRROB_SOCIAL_SHARE_VERSION . implode( '|', $text ) . implode( '|', $colors ) ), 0, 12 ) );

		if ( ! file_exists( $uploads['basedir'] . $file ) ) {
			if ( ! wp_mkdir_p( $uploads['basedir'] . $folder ) ) {
				return '';
			}

			array_map( 'wp_delete_file', (array) glob( sprintf( '%s%s/%d-*.png', $uploads['basedir'], $folder, $post->ID ) ) );

			if ( ! self::draw( $text, $colors, $uploads['basedir'] . $file ) ) {
				return '';
			}
		}

		return $uploads['baseurl'] . $file;
	}

	/**
	 * Text as it is written into the picture: WordPress hands it over with HTML entities.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	private static function plain( $text ) {
		return html_entity_decode( wp_strip_all_tags( $text ), ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Draw the card and save it.
	 *
	 * @param array  $text   Name of the site, title, label and address.
	 * @param array  $colors The card's colours, as #rrggbb (`Options::CARD`).
	 * @param string $path   Where to save the PNG.
	 * @return bool Whether it was saved.
	 */
	private static function draw( $text, $colors, $path ) {
		$fonts = NHRROB_SOCIAL_SHARE_PATH . '/assets/fonts/';
		$sans  = $fonts . 'IBMPlexSans-SemiBold.ttf';
		$mono  = $fonts . 'IBMPlexMono-Regular.ttf';
		$scale = self::SCALE;
		$large = imagecreatetruecolor( self::WIDTH * $scale, self::HEIGHT * $scale );

		if ( ! $large ) {
			return false;
		}

		$ink   = self::color( $large, $colors['text'] );
		$muted = self::color( $large, $colors['muted'] );
		$green = self::color( $large, $colors['accent'] );

		imagefill( $large, 0, 0, self::color( $large, $colors['background'] ) );

		// Top: a dot and the site's name.
		imagefilledellipse( $large, 89 * $scale, 86 * $scale, 18 * $scale, 18 * $scale, self::color( $large, $colors['dot'] ) );
		self::write( $large, $text['name'], $sans, 28, 112, 96, $ink );

		// Middle: the title, in the largest size that fits in three lines.
		foreach ( self::TITLE_SIZES as $size ) {
			$lines = self::wrap( $text['title'], $sans, $size, self::WIDTH - 200 );

			if ( count( $lines ) <= 3 ) {
				break;
			}
		}

		$height = $size * 1.14;
		$top    = 318 - count( $lines ) * $height / 2;

		foreach ( $lines as $number => $line ) {
			self::write( $large, $line, $sans, $size, 80, $top + $number * $height + $height / 2 + $size * 0.35, $ink );
		}

		// Bottom: the label on the left, the site's address on the right.
		self::write( $large, $text['label'], $mono, 24, 80, 552, $green );
		self::write( $large, $text['site'], $mono, 24, self::WIDTH - 80 - self::width( $text['site'], $mono, 24 ), 552, $muted );

		$card = imagecreatetruecolor( self::WIDTH, self::HEIGHT );

		imagecopyresampled( $card, $large, 0, 0, 0, 0, self::WIDTH, self::HEIGHT, self::WIDTH * $scale, self::HEIGHT * $scale );

		$saved = imagepng( $card, $path, 9 );

		// PHP 8 frees a GdImage by itself, and 8.5 deprecates imagedestroy().
		if ( PHP_VERSION_ID < 80000 ) {
			imagedestroy( $large ); // phpcs:ignore PHPCompatibility.FunctionUse.RemovedFunctions -- PHP 7 only.
			imagedestroy( $card ); // phpcs:ignore PHPCompatibility.FunctionUse.RemovedFunctions -- PHP 7 only.
		}

		return $saved;
	}

	/**
	 * A colour of the image from #rrggbb.
	 *
	 * @param resource|\GdImage $image Image.
	 * @param string            $hex   Colour.
	 * @return int
	 */
	private static function color( $image, $hex ) {
		$hex = ltrim( $hex, '#' );

		return (int) imagecolorallocate( $image, hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ) );
	}

	/**
	 * Write one line of text. Positions and sizes are in pixels of the finished card.
	 *
	 * @param resource|\GdImage $image Image, at the larger scale.
	 * @param string            $text  Text.
	 * @param string            $font  Path of the font file.
	 * @param int               $size  Font size.
	 * @param float             $x     Left edge.
	 * @param float             $y     Baseline.
	 * @param int               $color Colour.
	 * @return void
	 */
	private static function write( $image, $text, $font, $size, $x, $y, $color ) {
		// The library takes points, of which there are 72 to 96 pixels.
		imagettftext( $image, $size * self::SCALE * 0.75, 0, (int) round( $x * self::SCALE ), (int) round( $y * self::SCALE ), $color, $font, $text );
	}

	/**
	 * Width of a line of text, in pixels of the finished card.
	 *
	 * @param string $text Text.
	 * @param string $font Path of the font file.
	 * @param int    $size Font size.
	 * @return float
	 */
	private static function width( $text, $font, $size ) {
		$box = imagettfbbox( $size * 0.75, 0, $font, $text );

		return $box ? $box[2] - $box[0] : 0;
	}

	/**
	 * Break a text into lines no wider than the given width.
	 *
	 * @param string $text  Text.
	 * @param string $font  Path of the font file.
	 * @param int    $size  Font size.
	 * @param int    $width Width of a line.
	 * @return array
	 */
	private static function wrap( $text, $font, $size, $width ) {
		$lines = array();
		$line  = '';

		foreach ( preg_split( '/\s+/', trim( $text ) ) as $word ) {
			$longer = '' === $line ? $word : $line . ' ' . $word;

			if ( '' !== $line && self::width( $longer, $font, $size ) > $width ) {
				$lines[] = $line;
				$line    = $word;
			} else {
				$line = $longer;
			}
		}

		$lines[] = $line;

		return $lines;
	}
}

<?php
/**
 * Uploads a post's picture to LinkedIn, so the share is an image post.
 *
 * @package NHRRob\SocialShare
 */

namespace NHRRob\SocialShare\Linkedin;

use NHRRob\SocialShare\Preview\Card;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Class
 */
class Image {

	/**
	 * Which picture the post's shares and link preview carry: its featured
	 * image, or the card drawn from its title. A post without a featured
	 * image always gets the card.
	 *
	 * @param \WP_Post $post Post.
	 * @return string `featured` or `card`.
	 */
	public static function source( $post ) {
		if ( ! has_post_thumbnail( $post ) ) {
			return 'card';
		}

		/**
		 * The picture of a post that has a featured image: `featured` (the
		 * default) or `card`, the card drawn from its title.
		 *
		 * @param string   $source `featured` or `card`.
		 * @param \WP_Post $post   The post.
		 */
		return 'card' === apply_filters( 'nhrrob_social_share_picture', 'featured', $post ) ? 'card' : 'featured';
	}

	/**
	 * Address of the post's picture. The featured image stands in when the
	 * card cannot be drawn.
	 *
	 * @param \WP_Post $post Post.
	 * @return string Empty when there is none.
	 */
	public static function url( $post ) {
		$featured = get_the_post_thumbnail_url( $post, 'large' );
		$card     = $featured && 'featured' === self::source( $post ) ? '' : (string) Card::url( $post );

		return $card ? $card : (string) $featured;
	}

	/**
	 * Uploads the post's picture.
	 *
	 * @param \WP_Post $post Post.
	 * @return string|\WP_Error The image's asset URN, empty when the post has no picture.
	 */
	public static function upload( $post ) {
		$uploads = wp_get_upload_dir();
		$url     = self::url( $post );
		$file    = 0 === strpos( (string) $url, $uploads['baseurl'] . '/' ) ? $uploads['basedir'] . substr( $url, strlen( $uploads['baseurl'] ) ) : '';

		if ( ! $file || false !== strpos( $file, '..' ) || ! is_readable( $file ) ) {
			return '';
		}

		$register = Api::post(
			'https://api.linkedin.com/v2/assets?action=registerUpload',
			array(
				'registerUploadRequest' => array(
					'recipes'              => array( 'urn:li:digitalmediaRecipe:feedshare-image' ),
					'owner'                => 'urn:li:person:' . Settings::get()['person_id'],
					'serviceRelationships' => array(
						array(
							'relationshipType' => 'OWNER',
							'identifier'       => 'urn:li:userGeneratedContent',
						),
					),
				),
			)
		);

		if ( is_wp_error( $register ) ) {
			return $register;
		}

		$mechanism = 'com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest';
		$upload    = isset( $register['value']['uploadMechanism'][ $mechanism ]['uploadUrl'] ) ? $register['value']['uploadMechanism'][ $mechanism ]['uploadUrl'] : '';
		$asset     = isset( $register['value']['asset'] ) ? sanitize_text_field( $register['value']['asset'] ) : '';
		$host      = (string) wp_parse_url( $upload, PHP_URL_HOST );

		// The token goes with the file, so it is only sent to LinkedIn.
		if ( ! $asset || 'https' !== wp_parse_url( $upload, PHP_URL_SCHEME ) || ! preg_match( '/(^|\.)linkedin\.com$/', $host ) ) {
			return new \WP_Error( 'nhrrob_social_share_linkedin_image', __( 'LinkedIn did not give an address to upload the picture to.', 'nhrrob-social-share' ) );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- A local file in the uploads folder.
		$sent = Api::send( 'PUT', $upload, file_get_contents( $file ), array( 'Content-Type' => 'application/octet-stream' ) );

		return is_wp_error( $sent ) ? $sent : $asset;
	}
}

<?php
/**
 * The per-post texts and choices: registered as post meta so the block
 * editor's Social Share panel (admin/src/editor.js) and the REST API can read
 * and write them, for those who may edit the post.
 *
 * @package NHRRob\SocialShare
 */

namespace NHRRob\SocialShare;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PostFields Class
 */
class PostFields {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', [ $this, 'register' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue' ] );
	}

	/**
	 * Register the meta on posts.
	 *
	 * @return void
	 */
	public function register() {
		foreach ( Keys::EDITABLE as $key => $type ) {
			$schema = 'array' === $type ? [
				'schema' => [
					'type'  => 'array',
					'items' => [ 'type' => 'string' ],
				],
			] : true;

			register_post_meta(
				'post',
				$key,
				[
					'type'              => $type,
					'single'            => true,
					'default'           => 'array' === $type ? [] : '',
					'show_in_rest'      => $schema,
					'sanitize_callback' => [ $this, 'sanitize_' . $type ],
					'auth_callback'     => [ $this, 'can_edit' ],
				]
			);
		}
	}

	/**
	 * Only someone who may edit the post reads or writes its share texts.
	 *
	 * @param bool   $allowed  Allowed so far.
	 * @param string $meta_key Meta key.
	 * @param int    $post_id  Post ID.
	 * @return bool
	 */
	public function can_edit( $allowed, $meta_key, $post_id ) {
		return current_user_can( 'edit_post', $post_id );
	}

	/**
	 * A text, with its line breaks.
	 *
	 * @param mixed $value Value.
	 * @return string
	 */
	public function sanitize_string( $value ) {
		return sanitize_textarea_field( (string) $value );
	}

	/**
	 * A list of Reddit targets.
	 *
	 * @param mixed $value Value.
	 * @return array
	 */
	public function sanitize_array( $value ) {
		return Options::reddit_targets( $value );
	}

	/**
	 * The panel in the block editor's sidebar, on posts.
	 *
	 * @return void
	 */
	public function enqueue() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		$file   = NHRROB_SOCIAL_SHARE_PATH . '/admin/build/editor.asset.php';

		if ( ! $screen || 'post' !== $screen->post_type || ! file_exists( $file ) ) {
			return;
		}

		$asset = require $file;

		wp_enqueue_script( 'nhrrob-social-share-editor', plugins_url( 'admin/build/editor.js', NHRROB_SOCIAL_SHARE_FILE ), $asset['dependencies'], $asset['version'], true );
		wp_set_script_translations( 'nhrrob-social-share-editor', 'nhrrob-social-share' );
		wp_localize_script(
			'nhrrob-social-share-editor',
			'nhrrobSocialShareEditor',
			[
				'keys'     => [
					'linkedinText'   => Keys::LINKEDIN_TEXT,
					'linkedinLayout' => Keys::LINKEDIN_LAYOUT,
					'xText'          => Keys::X_TEXT,
					'reddit'         => Keys::REDDIT,
				],
				'reddit'   => Options::get()['reddit'],
				'rotate'   => Options::get()['rotate'],
				'linkedin' => Linkedin\Settings::is_connected(),
			]
		);
	}
}

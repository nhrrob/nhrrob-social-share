<?php
/**
 * Shares posts on LinkedIn: automatically when one is first published, or
 * from the "Share on LinkedIn" link in the Posts list. Shows the result.
 *
 * @package NHRRob\SocialShare
 */

namespace NHRRob\SocialShare\Linkedin;

use NHRRob\SocialShare\Keys;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PostActions Class
 */
class PostActions {

	/**
	 * Register hooks.
	 *
	 * The notices are hooked to all_admin_notices because
	 * nhrrob-hide-admin-notice clears admin_notices.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp_after_insert_post', array( $this, 'share_on_publish' ), 10, 4 );
		add_filter( 'post_row_actions', array( $this, 'row_action' ), 10, 2 );
		add_action( 'admin_post_nhrrob_social_share_linkedin_share', array( $this, 'handle_share' ) );
		add_action( 'all_admin_notices', array( $this, 'notices' ) );
	}

	/**
	 * Auto mode: share a post the first time it is published. Only on a site
	 * whose environment type is `production`, so a staging or local copy that
	 * holds the live site's connection never shares; the filter
	 * `nhrrob_social_share_auto_share` can say otherwise.
	 *
	 * @param int           $post_id     Post ID.
	 * @param \WP_Post      $post        Post.
	 * @param bool          $update      Whether an existing post was updated.
	 * @param \WP_Post|null $post_before The post before the update.
	 * @return void
	 */
	public function share_on_publish( $post_id, $post, $update, $post_before ) {
		if ( 'publish' !== $post->post_status || ( $post_before && 'publish' === $post_before->post_status ) ) {
			return;
		}

		if ( 'post' !== $post->post_type || 'auto' !== Settings::get()['mode'] ) {
			return;
		}

		/**
		 * Whether a post going live is shared on LinkedIn by itself.
		 *
		 * @param bool     $share Whether to share; true on a production site.
		 * @param \WP_Post $post  The post.
		 */
		if ( apply_filters( 'nhrrob_social_share_auto_share', 'production' === wp_get_environment_type(), $post ) ) {
			Share::post( $post_id );
		}
	}

	/**
	 * Manual mode: a "Share on LinkedIn" link under each published post.
	 *
	 * @param array    $actions Row actions.
	 * @param \WP_Post $post    Post.
	 * @return array
	 */
	public function row_action( $actions, $post ) {
		if ( 'post' !== $post->post_type || 'publish' !== $post->post_status || ! current_user_can( 'edit_post', $post->ID ) ) {
			return $actions;
		}

		$shared = get_post_meta( $post->ID, Keys::LINKEDIN_SHARE, true );

		if ( ! empty( $shared['id'] ) ) {
			$actions['nhrrob_social_share_linkedin'] = sprintf(
				'<a href="%s" target="_blank" rel="noopener">%s</a>',
				esc_url( 'https://www.linkedin.com/feed/update/' . rawurlencode( $shared['id'] ) . '/' ),
				esc_html__( 'Shared on LinkedIn', 'nhrrob-social-share' )
			);
		} else {
			$actions['nhrrob_social_share_linkedin'] = sprintf(
				'<a href="%s" title="%s">%s</a>',
				esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=nhrrob_social_share_linkedin_share&post=' . $post->ID ), 'nhrrob_social_share_linkedin_share_' . $post->ID ) ),
				esc_attr( empty( $shared['error'] ) ? '' : $shared['error'] ),
				empty( $shared['error'] ) ? esc_html__( 'Share on LinkedIn', 'nhrrob-social-share' ) : esc_html__( 'Retry LinkedIn share', 'nhrrob-social-share' )
			);
		}

		return $actions;
	}

	/**
	 * Share the post named in the request, then go back to the Posts list.
	 *
	 * @return void
	 */
	public function handle_share() {
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;

		check_admin_referer( 'nhrrob_social_share_linkedin_share_' . $post_id );

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'You are not allowed to share this post.', 'nhrrob-social-share' ), 403 );
		}

		$result = Share::post( $post_id );

		wp_safe_redirect(
			add_query_arg(
				array(
					'nhrrob_social_share' => is_wp_error( $result ) ? 'failed' : 'shared',
					'shared_post'         => $post_id,
				),
				admin_url( 'edit.php' )
			)
		);
		exit;
	}

	/**
	 * Result notices, and a warning before the LinkedIn token runs out.
	 *
	 * @return void
	 */
	public function notices() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only display of a result code.
		$message = isset( $_GET['nhrrob_social_share'] ) ? sanitize_key( $_GET['nhrrob_social_share'] ) : '';
		$post_id = isset( $_GET['shared_post'] ) ? absint( $_GET['shared_post'] ) : 0;
		// phpcs:enable

		$notices = array(
			'shared'       => array( 'success', __( 'Post shared on LinkedIn.', 'nhrrob-social-share' ) ),
			'saved'        => array( 'success', __( 'Settings saved.', 'nhrrob-social-share' ) ),
			'connected'    => array( 'success', __( 'LinkedIn connected.', 'nhrrob-social-share' ) ),
			'disconnected' => array( 'success', __( 'LinkedIn disconnected.', 'nhrrob-social-share' ) ),
			'denied'       => array( 'error', __( 'LinkedIn did not authorize the connection.', 'nhrrob-social-share' ) ),
			'error'        => array( 'error', __( 'Could not connect to LinkedIn. Check the Client ID, Client Secret and redirect URL.', 'nhrrob-social-share' ) ),
		);

		if ( 'failed' === $message && current_user_can( 'edit_post', $post_id ) ) {
			$shared            = get_post_meta( $post_id, Keys::LINKEDIN_SHARE, true );
			$notices['failed'] = array( 'error', empty( $shared['error'] ) ? __( 'The LinkedIn share failed.', 'nhrrob-social-share' ) : $shared['error'] );
		}

		if ( isset( $notices[ $message ] ) ) {
			printf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>', esc_attr( $notices[ $message ][0] ), esc_html( $notices[ $message ][1] ) );
		}

		$settings = Settings::get();

		if ( $settings['access_token'] && $settings['expires_at'] < time() + WEEK_IN_SECONDS && current_user_can( 'manage_options' ) ) {
			printf(
				'<div class="notice notice-warning"><p>%s <a href="%s">%s</a></p></div>',
				esc_html( $settings['expires_at'] > time() ? __( 'The LinkedIn connection expires within a week.', 'nhrrob-social-share' ) : __( 'The LinkedIn connection has expired, so posts are not being shared.', 'nhrrob-social-share' ) ),
				esc_url( Settings::page_url() ),
				esc_html__( 'Reconnect', 'nhrrob-social-share' )
			);
		}
	}
}

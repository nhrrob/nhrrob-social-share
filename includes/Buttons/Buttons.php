<?php
/**
 * The share buttons under a post. They are the networks' own share pages, so
 * no script, pixel or count from any network is loaded.
 *
 * @package NHRRob\SocialShare
 */

namespace NHRRob\SocialShare\Buttons;

use NHRRob\SocialShare\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Buttons Class
 */
class Buttons {

	/**
	 * Register hooks. Priority 25 puts the buttons after most other additions
	 * to the content, such as reactions.
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'the_content', [ $this, 'add_buttons' ], 25 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );
	}

	/**
	 * Where a post can be shared, in the order of the settings.
	 *
	 * @param \WP_Post $post Post to share.
	 * @return array Label, then share URL, keyed by the network (also the icon's name). `copy` has no URL.
	 */
	public static function links( $post ) {
		$url   = rawurlencode( get_permalink( $post ) );
		$title = rawurlencode( html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ) );
		$all   = [
			'linkedin' => [ __( 'LinkedIn', 'nhrrob-social-share' ), 'https://www.linkedin.com/sharing/share-offsite/?url=' . $url ],
			'x'        => [ __( 'X', 'nhrrob-social-share' ), 'https://x.com/intent/post?url=' . $url . '&text=' . $title ],
			'reddit'   => [ __( 'Reddit', 'nhrrob-social-share' ), 'https://www.reddit.com/submit?url=' . $url . '&title=' . $title ],
			'facebook' => [ __( 'Facebook', 'nhrrob-social-share' ), 'https://www.facebook.com/sharer/sharer.php?u=' . $url ],
			'whatsapp' => [ __( 'WhatsApp', 'nhrrob-social-share' ), 'https://wa.me/?text=' . $title . '%20' . $url ],
			'email'    => [ __( 'Email', 'nhrrob-social-share' ), 'mailto:?subject=' . $title . '&body=' . $url ],
			'copy'     => [ __( 'Copy link', 'nhrrob-social-share' ), '' ],
		];
		$links = [];

		foreach ( Options::get()['buttons'] as $network ) {
			$links[ $network ] = $all[ $network ];
		}

		return $links;
	}

	/**
	 * Whether the buttons go under this content: the main post of a single post page.
	 *
	 * @return bool
	 */
	private static function here() {
		return is_singular( 'post' ) && in_the_loop() && get_the_ID() === get_queried_object_id() && ! post_password_required();
	}

	/**
	 * The stylesheet and the copy script, on a single post.
	 *
	 * @return void
	 */
	public function enqueue() {
		if ( ! is_singular( 'post' ) || ! Options::get()['buttons'] ) {
			return;
		}

		wp_enqueue_style( 'nhrrob-social-share', plugins_url( 'assets/buttons.css', NHRROB_SOCIAL_SHARE_FILE ), [], NHRROB_SOCIAL_SHARE_VERSION );

		if ( in_array( 'copy', Options::get()['buttons'], true ) ) {
			wp_enqueue_script(
				'nhrrob-social-share',
				plugins_url( 'assets/buttons.js', NHRROB_SOCIAL_SHARE_FILE ),
				[],
				NHRROB_SOCIAL_SHARE_VERSION,
				[
					'in_footer' => true,
					'strategy'  => 'defer',
				]
			);
		}
	}

	/**
	 * Add the buttons under a post's content.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function add_buttons( $content ) {
		if ( ! self::here() ) {
			return $content;
		}

		$links = '';

		foreach ( self::links( get_post() ) as $network => $link ) {
			// The script shows this button; without it there is nothing to copy with.
			if ( 'copy' === $network ) {
				$links .= sprintf(
					'<button type="button" hidden data-url="%1$s" data-copied="%2$s">%4$s<span>%3$s</span></button>',
					esc_url( get_permalink() ),
					esc_attr__( 'Copied', 'nhrrob-social-share' ),
					esc_html( $link[0] ),
					Icons::svg( 'link' )
				);
				continue;
			}

			$links .= sprintf(
				'<a href="%s"%s>%s<span>%s</span></a>',
				esc_url( $link[1], [ 'https', 'mailto' ] ),
				0 === strpos( $link[1], 'mailto:' ) ? '' : ' target="_blank" rel="noopener noreferrer"',
				Icons::svg( $network ),
				esc_html( $link[0] )
			);
		}

		if ( '' === $links ) {
			return $content;
		}

		return $content . sprintf(
			'<div class="nhrrob-social-share" role="group" aria-label="%1$s"><span class="nhrrob-social-share__title">%2$s</span>%3$s</div>',
			esc_attr__( 'Share this post', 'nhrrob-social-share' ),
			esc_html__( 'Share', 'nhrrob-social-share' ),
			$links
		);
	}
}

<?php
/**
 * Describes a post, and the home page, to the networks it is shared on and to
 * search engines: a meta description, Open Graph tags and an X card in the
 * page head.
 *
 * @package NHRRob\SocialShare
 */

namespace NHRRob\SocialShare\Preview;

use NHRRob\SocialShare\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta Class
 */
class Meta {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp_head', [ $this, 'print_tags' ], 5 );
	}

	/**
	 * Whether this plugin prints the tags: always, never, or (automatic) only
	 * when no SEO plugin that prints its own is active.
	 *
	 * @return bool
	 */
	public static function enabled() {
		$mode = Options::get()['previews'];

		if ( 'auto' !== $mode ) {
			return 'on' === $mode;
		}

		return ! self::seo_plugin();
	}

	/**
	 * The name of an active SEO plugin that prints Open Graph tags, if any.
	 *
	 * @return string Empty when there is none.
	 */
	public static function seo_plugin() {
		$plugins = [
			'WPSEO_VERSION'             => 'Yoast SEO',
			'RANK_MATH_VERSION'         => 'Rank Math',
			'SEOPRESS_VERSION'          => 'SEOPress',
			'AIOSEO_VERSION'            => 'All in One SEO',
			'THE_SEO_FRAMEWORK_VERSION' => 'The SEO Framework',
		];

		foreach ( $plugins as $constant => $name ) {
			if ( defined( $constant ) ) {
				return $name;
			}
		}

		return '';
	}

	/**
	 * Print the tags in the head of a single post and of the home page.
	 *
	 * @return void
	 */
	public function print_tags() {
		if ( ! self::enabled() ) {
			return;
		}

		if ( ( is_home() || is_front_page() ) && ! is_paged() && ! is_singular() ) {
			$this->print_list(
				get_bloginfo( 'description' ),
				'summary',
				[
					'og:type'        => 'website',
					'og:site_name'   => get_bloginfo( 'name' ),
					'og:title'       => get_bloginfo( 'name' ),
					'og:description' => get_bloginfo( 'description' ),
					'og:url'         => home_url( '/' ),
					'og:image'       => get_site_icon_url( 512 ),
				]
			);

			return;
		}

		if ( ! is_singular( 'post' ) || post_password_required() ) {
			return;
		}

		$post        = get_queried_object();
		$description = wp_strip_all_tags( get_the_excerpt( $post ), true );
		$image       = get_the_post_thumbnail_url( $post, 'large' );

		// A post without its own image gets the card drawn from its title.
		$drawn = $image ? '' : Card::url( $post );
		$image = $image ? $image : $drawn;
		$card  = $image ? 'summary_large_image' : 'summary';
		$image = $image ? $image : get_site_icon_url( 512 );

		$this->print_list(
			$description,
			$card,
			[
				'og:type'                => 'article',
				'og:site_name'           => get_bloginfo( 'name' ),
				'og:title'               => get_the_title( $post ),
				'og:description'         => $description,
				'og:url'                 => get_permalink( $post ),
				'og:image'               => $image,
				// A network that knows the size shows the card on the first share, before it has fetched it.
				'og:image:width'         => $drawn ? Card::WIDTH : '',
				'og:image:height'        => $drawn ? Card::HEIGHT : '',
				'article:published_time' => get_post_time( 'c', true, $post ),
				'article:modified_time'  => get_post_modified_time( 'c', true, $post ),
			]
		);
	}

	/**
	 * Print a meta description, an X card and Open Graph tags, leaving out the empty ones.
	 *
	 * @param string $description Text of the meta description.
	 * @param string $card        X card type.
	 * @param array  $tags        Open Graph property => content.
	 * @return void
	 */
	private function print_list( $description, $card, $tags ) {
		if ( $description ) {
			printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
		}

		printf( '<meta name="twitter:card" content="%s">' . "\n", esc_attr( $card ) );

		foreach ( array_filter( $tags ) as $property => $content ) {
			printf( '<meta property="%s" content="%s">' . "\n", esc_attr( $property ), esc_attr( $content ) );
		}
	}
}

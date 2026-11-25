<?php
/**
 * The links that open X, Facebook, Reddit or Medium with a post filled in,
 * shared by hand from the Posts list (`RowActions`).
 *
 * @package NHRRob\SocialShare
 */

namespace NHRRob\SocialShare\Hand;

use NHRRob\SocialShare\Keys;
use NHRRob\SocialShare\Linkedin\Share;
use NHRRob\SocialShare\Options;

defined( 'ABSPATH' ) || exit;

/**
 * Links Class
 *
 * Nothing is posted through an API: X charges for it, Facebook does not let
 * an app post to a personal profile, Medium takes no new integrations and
 * Reddit approves each app by hand. Each link opens the network's own page,
 * where the author presses Post. What a page cannot be given in its address
 * (Facebook's text, Medium's link) is copied to the clipboard on the click.
 */
class Links {

	/**
	 * Every link for a post, keyed by the network's name in the admin address.
	 * Each has `url` (the network's page), `label`, `again` (once used),
	 * `done` (once the post's address on the network is pasted back), `copy`
	 * (the text the click puts on the clipboard, or empty) and `layout` (a, b
	 * or c for X and Facebook, see `Layout`; empty for the others).
	 *
	 * @param \WP_Post $post Post.
	 * @return array
	 */
	public static function all( $post ) {
		$permalink = get_permalink( $post );
		$title     = html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' );

		$networks = Options::get()['hand'];
		$links    = array();

		if ( in_array( 'x', $networks, true ) ) {
			$links['x'] = self::x( $post, $title, $permalink );
		}

		if ( in_array( 'facebook', $networks, true ) ) {
			$links['facebook'] = self::facebook( $post, $permalink );
		}

		$targets = in_array( 'reddit', $networks, true ) ? self::reddit_targets( $post->ID ) : array();

		foreach ( $targets ? $targets : ( in_array( 'reddit', $networks, true ) ? array( '' ) : array() ) as $target ) {
			$name = $target ? $target : 'Reddit';
			$page = $target ? ( 'u' === $target[0] ? 'user/' . substr( $target, 2 ) : $target ) . '/' : '';

			$links[ $target ? str_replace( '/', '_', strtolower( $target ) ) : 'reddit' ] = self::link(
				'https://www.reddit.com/' . $page . 'submit?url=' . rawurlencode( $permalink ) . '&title=' . rawurlencode( $title ),
				/* translators: %s: subreddit as r/name, profile as u/name, or Reddit */
				sprintf( __( 'Share on %s', 'nhrrob-social-share' ), $name ),
				/* translators: %s: subreddit as r/name, profile as u/name, or Reddit */
				sprintf( __( 'Share on %s again', 'nhrrob-social-share' ), $name ),
				/* translators: %s: subreddit as r/name, profile as u/name, or Reddit */
				sprintf( __( 'Shared on %s', 'nhrrob-social-share' ), $name )
			);
		}

		if ( ! in_array( 'medium', $networks, true ) ) {
			return $links;
		}

		// Medium copies the whole post and names this site as the original. Its page asks for the link, so the click copies it.
		$links['medium'] = self::link(
			'https://medium.com/p/import?url=' . rawurlencode( $permalink ),
			__( 'Import to Medium', 'nhrrob-social-share' ),
			__( 'Import to Medium again', 'nhrrob-social-share' ),
			__( 'Imported to Medium', 'nhrrob-social-share' ),
			$permalink
		);

		return $links;
	}

	/**
	 * X: the post file's `x` text, or the title, kept short since a post over
	 * 280 characters needs Premium. A: with the link, which X shows as a card.
	 * B: the same, with the picture attached by hand. C: no link, which goes
	 * in a reply once the post is up.
	 *
	 * @param \WP_Post $post      Post.
	 * @param string   $title     The post's title.
	 * @param string   $permalink The post's address.
	 * @return array
	 */
	private static function x( $post, $title, $permalink ) {
		$text = (string) get_post_meta( $post->ID, Keys::X_TEXT, true );
		$text = '' === $text ? $title : $text;

		/** This filter is documented in includes/Linkedin/Share.php */
		$text   = trim( (string) apply_filters( 'nhrrob_social_share_text', $text, $post, 'x' ) );
		$own    = (bool) preg_match( '#https?://#i', $text );
		$layout = Layout::choose( $post->ID, 'x', $own );

		if ( 'c' === $layout ) {
			$text .= "\n\n" . __( 'Link in the reply.', 'nhrrob-social-share' );
		}

		// Like the LinkedIn share, a text with its own link is posted as written.
		$url = 'https://x.com/intent/post?text=' . rawurlencode( $text ) . ( $own || 'c' === $layout ? '' : '&url=' . rawurlencode( $permalink ) );

		return self::link( $url, __( 'Share on X', 'nhrrob-social-share' ), __( 'Share on X again', 'nhrrob-social-share' ), __( 'Shared on X', 'nhrrob-social-share' ), '', $layout );
	}

	/**
	 * Facebook: the post's Facebook text, or the LinkedIn text, copied on the click, since Facebook lets no
	 * link fill in a post's text. A: the share dialog, which shows the link as
	 * a card. B: the link at the end of the text, and C a line pointing to the
	 * first comment, both posted with the picture from Facebook's home page.
	 *
	 * @param \WP_Post $post      Post.
	 * @param string   $permalink The post's address.
	 * @return array
	 */
	private static function facebook( $post, $permalink ) {
		$text   = Share::text( $post, 'facebook' );
		$own    = (bool) preg_match( '#https?://#i', $text );
		$layout = Layout::choose( $post->ID, 'facebook', $own );
		$url    = 'https://www.facebook.com/';

		if ( 'a' === $layout ) {
			$url = 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $permalink );
		} elseif ( 'c' === $layout ) {
			$text .= "\n\n" . __( 'Link in the first comment.', 'nhrrob-social-share' );
		} elseif ( ! $own ) {
			$text .= "\n\n" . $permalink;
		}

		return self::link( $url, __( 'Share on Facebook', 'nhrrob-social-share' ), __( 'Share on Facebook again', 'nhrrob-social-share' ), __( 'Shared on Facebook', 'nhrrob-social-share' ), $text, $layout );
	}

	/**
	 * One link.
	 *
	 * @param string $url    The network's page.
	 * @param string $label  Text before it was used.
	 * @param string $again  Text after it was used.
	 * @param string $done   Text once the address on the network is pasted back.
	 * @param string $copy   Text the click copies.
	 * @param string $layout a, b, c or empty.
	 * @return array
	 */
	private static function link( $url, $label, $again, $done, $copy = '', $layout = '' ) {
		return array(
			'url'    => $url,
			'label'  => $label,
			'again'  => $again,
			'done'   => $done,
			'copy'   => $copy,
			'layout' => $layout,
		);
	}

	/**
	 * Where a post goes on Reddit: its own list (subreddits as `r/Name`,
	 * profiles as `u/name`), else the default targets of the settings.
	 *
	 * @param int $post_id Post ID.
	 * @return string[]
	 */
	private static function reddit_targets( $post_id ) {
		$own = Options::reddit_targets( get_post_meta( $post_id, Keys::REDDIT, true ) );

		return $own ? $own : Options::get()['reddit'];
	}
}

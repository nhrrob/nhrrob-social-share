<?php
/**
 * The post meta the plugin keeps, in one place, so the editor panel, the
 * migration and a site that writes posts from files all use the same names.
 *
 * @package NHRRob\SocialShare
 */

namespace NHRRob\SocialShare;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keys Class
 */
class Keys {

	/**
	 * Per-post texts and choices, set in the editor or from a post file.
	 */
	const LINKEDIN_TEXT   = '_nhrrob_social_share_linkedin_text';
	const LINKEDIN_LAYOUT = '_nhrrob_social_share_linkedin_layout';
	const X_TEXT          = '_nhrrob_social_share_x_text';
	const FACEBOOK_TEXT   = '_nhrrob_social_share_facebook_text';
	const REDDIT          = '_nhrrob_social_share_reddit';

	/**
	 * What happened: the LinkedIn share, the links used by hand, where the post
	 * was posted by hand, and the layout each network used.
	 */
	const LINKEDIN_SHARE = '_nhrrob_social_share_linkedin';
	const HAND_USED      = '_nhrrob_social_share_hand';
	const HAND_URL       = '_nhrrob_social_share_hand_url';
	const HAND_LAYOUT    = '_nhrrob_social_share_hand_layout';

	/**
	 * The texts and choices the editor panel shows, with their type.
	 */
	const EDITABLE = [
		self::LINKEDIN_TEXT   => 'string',
		self::LINKEDIN_LAYOUT => 'string',
		self::X_TEXT          => 'string',
		self::FACEBOOK_TEXT   => 'string',
		self::REDDIT          => 'array',
	];
}

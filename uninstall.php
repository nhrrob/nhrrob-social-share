<?php
/**
 * Removes the plugin's settings and post meta when it is deleted, on every
 * site of a network.
 *
 * @package NHRRob\SocialShare
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete what the plugin saved on the current site.
 *
 * @return void
 */
function nhrrob_social_share_uninstall_site() {
	foreach ( [ 'nhrrob_social_share_settings', 'nhrrob_social_share_linkedin', 'nhrrob_social_share_migrated' ] as $nhrrob_social_share_option ) {
		delete_option( $nhrrob_social_share_option );
	}

	foreach ( [ 'linkedin_text', 'linkedin_layout', 'x_text', 'reddit', 'linkedin', 'hand', 'hand_url', 'hand_layout' ] as $nhrrob_social_share_key ) {
		delete_post_meta_by_key( '_nhrrob_social_share_' . $nhrrob_social_share_key );
	}
}

if ( is_multisite() ) {
	foreach ( get_sites( [ 'fields' => 'ids' ] ) as $nhrrob_social_share_site ) {
		switch_to_blog( $nhrrob_social_share_site );
		nhrrob_social_share_uninstall_site();
		restore_current_blog();
	}
} else {
	nhrrob_social_share_uninstall_site();
}

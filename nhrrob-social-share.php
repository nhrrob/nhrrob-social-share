<?php
/**
 * Plugin Name: NHR Social Share
 * Plugin URI: https://github.com/nhrrob/nhrrob-social-share
 * Description: Share each post where its readers are: share buttons, link previews with a drawn card, LinkedIn sharing from your own app, and X, Facebook, Reddit and Medium filled in by hand. No service in between and nothing that tracks readers.
 * Author: Nazmul Hasan Robin
 * Author URI: https://profiles.wordpress.org/nhrrob/
 * Version: 0.1.1
 * Requires at least: 6.6
 * Requires PHP: 7.4
 * Text Domain: nhrrob-social-share
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package NHRRob\SocialShare
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * The main plugin class
 */
final class NHRRob_Social_Share {

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	const VERSION = '0.1.1';

	/**
	 * The classes that register hooks, each in its own init().
	 */
	const FEATURES = [
		\NHRRob\SocialShare\Migration::class,
		\NHRRob\SocialShare\Rest::class,
		\NHRRob\SocialShare\Admin::class,
		\NHRRob\SocialShare\PostFields::class,
		\NHRRob\SocialShare\Buttons\Buttons::class,
		\NHRRob\SocialShare\Preview\Meta::class,
		\NHRRob\SocialShare\Linkedin\Connect::class,
		\NHRRob\SocialShare\Linkedin\PostActions::class,
		\NHRRob\SocialShare\Hand\RowActions::class,
		\NHRRob\SocialShare\Hand\Urls::class,
		\NHRRob\SocialShare\Stats\Networks::class,
	];

	/**
	 * Class constructor
	 */
	private function __construct() {
		define( 'NHRROB_SOCIAL_SHARE_VERSION', self::VERSION );
		define( 'NHRROB_SOCIAL_SHARE_FILE', __FILE__ );
		define( 'NHRROB_SOCIAL_SHARE_PATH', __DIR__ );

		register_activation_hook( __FILE__, [ \NHRRob\SocialShare\Migration::class, 'run' ] );
		add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );
	}

	/**
	 * Initialize a singleton instance
	 *
	 * @return self
	 */
	public static function init(): self {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Initialize the plugin.
	 *
	 * @return void
	 */
	public function init_plugin(): void {
		foreach ( self::FEATURES as $feature ) {
			( new $feature() )->init();
		}
	}
}

/**
 * Initializes the main plugin
 *
 * @return NHRRob_Social_Share
 */
function nhrrob_social_share(): NHRRob_Social_Share {
	return NHRRob_Social_Share::init();
}

// Call the plugin.
nhrrob_social_share();

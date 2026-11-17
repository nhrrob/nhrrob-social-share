<?php
/**
 * The settings screen, Settings → Social Share, and the script of the share
 * links in the Posts list.
 *
 * @package NHRRob\SocialShare
 */

namespace NHRRob\SocialShare;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Class
 *
 * Prints the mount point of the app in admin/build.
 */
class Admin {

	const SLUG = 'nhrrob-social-share';

	/**
	 * Hook suffix of the screen.
	 *
	 * @var string|false
	 */
	private $hook = false;

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_menu', [ $this, 'menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
		add_filter( 'plugin_action_links_' . plugin_basename( NHRROB_SOCIAL_SHARE_FILE ), [ $this, 'action_links' ] );
	}

	/**
	 * Add the screen under Settings.
	 *
	 * @return void
	 */
	public function menu() {
		$this->hook = add_options_page( __( 'Social Share', 'nhrrob-social-share' ), __( 'Social Share', 'nhrrob-social-share' ), 'manage_options', self::SLUG, [ $this, 'render' ] );
	}

	/**
	 * A "Settings" link on the plugin's row of the Plugins screen.
	 *
	 * @param array $links Action links.
	 * @return array
	 */
	public function action_links( $links ) {
		if ( current_user_can( 'manage_options' ) ) {
			array_unshift( $links, sprintf( '<a href="%s">%s</a>', esc_url( self::url() ), esc_html__( 'Settings', 'nhrrob-social-share' ) ) );
		}

		return $links;
	}

	/**
	 * Address of the screen.
	 *
	 * @param string $tab Tab to open.
	 * @return string
	 */
	public static function url( $tab = '' ) {
		return admin_url( 'options-general.php?page=' . self::SLUG . ( $tab ? '&tab=' . $tab : '' ) );
	}

	/**
	 * Print the mount point.
	 *
	 * @return void
	 */
	public function render() {
		// The heading and wp-header-end marker make core move admin notices
		// inside .wrap; the app draws its own title bar.
		?>
		<div class="wrap nhrrob-social-share-wrap">
			<h1 class="screen-reader-text"><?php esc_html_e( 'Social Share', 'nhrrob-social-share' ); ?></h1>
			<hr class="wp-header-end">
			<div id="nhrrob-social-share-app"></div>
		</div>
		<?php
	}

	/**
	 * The app on its own screen, and the copy script in the Posts list.
	 *
	 * @param string $hook Hook suffix of the current screen.
	 * @return void
	 */
	public function enqueue( $hook ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( 'edit.php' === $hook && $screen && 'post' === $screen->post_type ) {
			wp_enqueue_script( 'nhrrob-social-share-hand', plugins_url( 'assets/hand.js', NHRROB_SOCIAL_SHARE_FILE ), [], NHRROB_SOCIAL_SHARE_VERSION, true );
			return;
		}

		$dir = NHRROB_SOCIAL_SHARE_PATH . '/admin/build';

		if ( ! $this->hook || $hook !== $this->hook || ! file_exists( $dir . '/index.asset.php' ) ) {
			return; // Another screen, or not built yet: run `npm run build`.
		}

		$asset  = require $dir . '/index.asset.php';
		$url    = plugins_url( 'admin/build', NHRROB_SOCIAL_SHARE_FILE );
		$handle = 'nhrrob-social-share-index';

		wp_enqueue_script( $handle, $url . '/index.js', $asset['dependencies'], $asset['version'], true );
		wp_set_script_translations( $handle, 'nhrrob-social-share' );
		wp_localize_script(
			$handle,
			'nhrrobSocialShareApp',
			[
				'restRoot'   => esc_url_raw( rest_url() ),
				'nonce'      => wp_create_nonce( 'wp_rest' ),
				'pluginName' => __( 'Social Share', 'nhrrob-social-share' ),
				'version'    => NHRROB_SOCIAL_SHARE_VERSION,
				'connect'    => wp_nonce_url( admin_url( 'admin-post.php?action=nhrrob_social_share_linkedin_connect' ), 'nhrrob_social_share_linkedin_connect' ),
				'disconnect' => wp_nonce_url( admin_url( 'admin-post.php?action=nhrrob_social_share_linkedin_disconnect' ), 'nhrrob_social_share_linkedin_disconnect' ),
			]
		);

		if ( file_exists( $dir . '/style-index.css' ) ) {
			// Own version: the asset hash covers the script only.
			wp_enqueue_style( $handle, $url . '/style-index.css', [], (string) filemtime( $dir . '/style-index.css' ) );
			wp_style_add_data( $handle, 'rtl', 'replace' );
		}
	}
}

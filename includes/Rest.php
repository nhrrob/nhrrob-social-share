<?php
/**
 * The REST routes of the settings screen, all behind `manage_options` and the
 * REST nonce. Secrets go in and never come back out: the screen only learns
 * whether one is saved.
 *
 * @package NHRRob\SocialShare
 */

namespace NHRRob\SocialShare;

use NHRRob\SocialShare\Linkedin\Settings as Linkedin;
use NHRRob\SocialShare\Preview\Card;
use NHRRob\SocialShare\Preview\Meta;
use NHRRob\SocialShare\Stats\Visits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rest Class
 */
class Rest {

	const REST_NAMESPACE = 'nhrrob-social-share/v1';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'rest_api_init', [ $this, 'routes' ] );
	}

	/**
	 * The routes.
	 *
	 * @return void
	 */
	public function routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/settings',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_settings' ],
					'permission_callback' => [ __CLASS__, 'can_manage' ],
				],
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'save_settings' ],
					'permission_callback' => [ __CLASS__, 'can_manage' ],
				],
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/results',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_results' ],
				'permission_callback' => [ __CLASS__, 'can_manage' ],
			]
		);
	}

	/**
	 * Who may change the settings and see the results.
	 *
	 * @return bool
	 */
	public static function can_manage() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Everything the screen shows.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_settings() {
		return rest_ensure_response( self::state() );
	}

	/**
	 * Save what the screen sends: the settings, and the LinkedIn app's keys
	 * and mode. An empty secret keeps the saved one.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function save_settings( $request ) {
		$params = (array) $request->get_json_params();

		Options::update( array_intersect_key( $params, array_flip( [ 'buttons', 'previews', 'card', 'hand', 'reddit', 'rotate' ] ) ) );

		if ( isset( $params['linkedin'] ) && is_array( $params['linkedin'] ) ) {
			$linkedin = Linkedin::get();
			$sent     = $params['linkedin'];

			if ( isset( $sent['mode'] ) ) {
				$linkedin['mode'] = 'auto' === $sent['mode'] ? 'auto' : 'manual';
			}

			if ( isset( $sent['client_id'] ) ) {
				$linkedin['client_id'] = sanitize_text_field( (string) $sent['client_id'] );
			}

			if ( ! empty( $sent['client_secret'] ) ) {
				$linkedin['client_secret'] = sanitize_text_field( (string) $sent['client_secret'] );
			}

			Linkedin::update( $linkedin );
		}

		return rest_ensure_response( self::state() );
	}

	/**
	 * Shares and visits per layout.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_results() {
		$networks = [];

		foreach ( Visits::all() as $key => $network ) {
			$rows = [];

			foreach ( $network[2] as $layout => $row ) {
				$rows[] = [
					'layout'   => $layout,
					'label'    => $network[1][ $layout ],
					'shares'   => $row[0],
					'visits'   => $row[1],
					'counting' => $row[2],
				];
			}

			$networks[] = [
				'key'  => $key,
				'name' => $network[0],
				'rows' => $rows,
			];
		}

		return rest_ensure_response(
			[
				'available' => Visits::available(),
				'days'      => Visits::DAYS,
				'networks'  => $networks,
			]
		);
	}

	/**
	 * The settings, what the site can do, and the LinkedIn connection without its secrets.
	 *
	 * @return array
	 */
	public static function state() {
		$linkedin = Linkedin::get();
		$latest   = get_posts(
			[
				'numberposts' => 1,
				'post_status' => 'publish',
			]
		);

		return [
			'settings' => Options::get(),
			'linkedin' => [
				'mode'        => $linkedin['mode'],
				'client_id'   => $linkedin['client_id'],
				'secret'      => '' !== $linkedin['client_secret'],
				'connected'   => Linkedin::is_connected(),
				'name'        => $linkedin['person_name'],
				'expires'     => $linkedin['expires_at'] ? wp_date( get_option( 'date_format' ), (int) $linkedin['expires_at'] ) : '',
				'expiring'    => $linkedin['access_token'] && (int) $linkedin['expires_at'] < time() + WEEK_IN_SECONDS,
				'redirect'    => Linkedin::redirect_uri(),
				'environment' => wp_get_environment_type(),
			],
			'site'     => [
				'seo'      => Meta::seo_plugin(),
				'previews' => Meta::enabled(),
				'card'     => function_exists( 'imagettftext' ),
				'preview'  => $latest ? Card::url( $latest[0] ) : '',
				'insights' => Visits::available(),
			],
		];
	}
}

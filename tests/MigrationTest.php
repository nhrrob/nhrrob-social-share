<?php
namespace NHRRob\SocialShare\Tests;

use NHRRob\SocialShare\Keys;
use NHRRob\SocialShare\Migration;
use PHPUnit\Framework\TestCase;
use WP_Mock;

/**
 * The migration copies the old keys once, and never over what the plugin saved.
 */
class MigrationTest extends TestCase {

	private $options = [];

	private $meta = [];

	protected function setUp(): void {
		WP_Mock::setUp();

		WP_Mock::userFunction( 'get_option' )->andReturnUsing(
			function ( $name, $fallback = false ) {
				return array_key_exists( $name, $this->options ) ? $this->options[ $name ] : $fallback;
			}
		);
		WP_Mock::userFunction( 'update_option' )->andReturnUsing(
			function ( $name, $value ) {
				$this->options[ $name ] = $value;
				return true;
			}
		);
		WP_Mock::userFunction( 'get_posts' )->andReturnUsing(
			function ( $args ) {
				$ids = [];
				foreach ( $this->meta as $id => $keys ) {
					if ( isset( $keys[ $args['meta_key'] ] ) ) {
						$ids[] = $id;
					}
				}
				return $ids;
			}
		);
		WP_Mock::userFunction( 'metadata_exists' )->andReturnUsing(
			function ( $type, $id, $key ) {
				return isset( $this->meta[ $id ][ $key ] );
			}
		);
		WP_Mock::userFunction( 'get_post_meta' )->andReturnUsing(
			function ( $id, $key ) {
				return isset( $this->meta[ $id ][ $key ] ) ? $this->meta[ $id ][ $key ] : '';
			}
		);
		WP_Mock::userFunction( 'update_post_meta' )->andReturnUsing(
			function ( $id, $key, $value ) {
				$this->meta[ $id ][ $key ] = $value;
				return true;
			}
		);
	}

	protected function tearDown(): void {
		WP_Mock::tearDown();
	}

	public function test_old_keys_are_copied_once_and_new_ones_are_kept() {
		$this->options['nazmulrobin_linkedin'] = [ 'access_token' => 'old' ];
		$this->meta                            = [
			1 => [
				'_nazmulrobin_linkedin_share' => [ 'id' => 'urn:li:share:1' ],
				'_nazmulrobin_reddit'         => [ 'r/Wordpress' ],
			],
			2 => [
				'_nazmulrobin_x_text' => 'Old text',
				Keys::X_TEXT          => 'New text',
			],
		];

		Migration::run();

		$this->assertSame( [ 'access_token' => 'old' ], $this->options['nhrrob_social_share_linkedin'] );
		$this->assertSame( [ 'id' => 'urn:li:share:1' ], $this->meta[1][ Keys::LINKEDIN_SHARE ] );
		$this->assertSame( [ 'r/Wordpress' ], $this->meta[1][ Keys::REDDIT ] );
		$this->assertSame( 'New text', $this->meta[2][ Keys::X_TEXT ] );
		$this->assertSame( 2, $this->options[ Migration::DONE ]['copied']['meta'] );

		// A second run does nothing.
		$this->meta[3] = [ '_nazmulrobin_x_text' => 'Later' ];
		Migration::run();
		$this->assertArrayNotHasKey( Keys::X_TEXT, $this->meta[3] );
	}

	public function test_a_saved_connection_is_not_overwritten() {
		$this->options['nazmulrobin_linkedin']         = [ 'access_token' => 'old' ];
		$this->options['nhrrob_social_share_linkedin'] = [ 'access_token' => 'new' ];

		Migration::run();

		$this->assertSame( [ 'access_token' => 'new' ], $this->options['nhrrob_social_share_linkedin'] );
	}
}

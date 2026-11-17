<?php
namespace NHRRob\SocialShare\Tests;

use NHRRob\SocialShare\Options;
use PHPUnit\Framework\TestCase;
use WP_Mock;

/**
 * The settings fall back to their defaults and keep only what each may be.
 */
class OptionsTest extends TestCase {

	protected function setUp(): void {
		WP_Mock::setUp();
		WP_Mock::userFunction( 'sanitize_hex_color' )->andReturnUsing(
			function ( $color ) {
				return preg_match( '/^#[0-9a-fA-F]{6}$/', $color ) ? $color : null;
			}
		);
	}

	protected function tearDown(): void {
		WP_Mock::tearDown();
	}

	public function test_nothing_saved_gives_the_defaults() {
		$settings = Options::clean( [] );

		$this->assertSame( Options::BUTTONS, $settings['buttons'] );
		$this->assertSame( 'auto', $settings['previews'] );
		$this->assertSame( Options::CARD, $settings['card'] );
		$this->assertSame( Options::HAND, $settings['hand'] );
		$this->assertSame( [], $settings['reddit'] );
		$this->assertTrue( $settings['rotate'] );
	}

	public function test_unknown_values_are_dropped_and_order_is_kept() {
		$settings = Options::clean(
			[
				'buttons'  => [ 'email', 'myspace', 'x', 'email' ],
				'previews' => 'sometimes',
				'card'     => [ 'background' => '#ffffff', 'text' => 'red', 'extra' => '#000000' ],
				'hand'     => [],
				'rotate'   => false,
			]
		);

		$this->assertSame( [ 'email', 'x' ], $settings['buttons'] );
		$this->assertSame( 'auto', $settings['previews'] );
		$this->assertSame( '#FFFFFF', $settings['card']['background'] );
		$this->assertSame( Options::CARD['text'], $settings['card']['text'] );
		$this->assertArrayNotHasKey( 'extra', $settings['card'] );
		$this->assertSame( [], $settings['hand'] );
		$this->assertFalse( $settings['rotate'] );
	}

	public function test_reddit_targets_are_subreddits_and_profiles() {
		$this->assertSame(
			[ 'r/Wordpress', 'r/ClaudeAI', 'u/nhrrob' ],
			Options::reddit_targets( [ 'r/Wordpress', 'ClaudeAI', 'u/nhrrob', '/user/nhrrob', 'r/not valid', 'r/Wordpress', 'u/x' ] )
		);
		$this->assertSame( [ 'u/someone', 'r/laravel' ], Options::reddit_targets( 'u/someone, r/laravel' ) );
	}
}

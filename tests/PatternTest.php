<?php
namespace NHRRob\SocialShare\Tests;

use NHRRob\SocialShare\Linkedin\Pattern;
use PHPUnit\Framework\TestCase;
use WP_Mock;

/**
 * Which share layout comes next, and which one an older share used.
 */
class PatternTest extends TestCase {

	protected function setUp(): void {
		WP_Mock::setUp();
	}

	protected function tearDown(): void {
		WP_Mock::tearDown();
	}

	public function test_patterns_take_turns_from_a() {
		$this->assertSame( 'a', Pattern::pick( [] ) );
		$this->assertSame( 'b', Pattern::pick( [ 'a' => 1 ] ) );
		$this->assertSame( 'c', Pattern::pick( [ 'a' => 1, 'b' => 1 ] ) );
		$this->assertSame( 'a', Pattern::pick( [ 'a' => 1, 'b' => 1, 'c' => 1 ] ) );
		$this->assertSame( 'b', Pattern::pick( [ 'a' => 2, 'b' => 1, 'c' => 2 ] ) );
	}

	public function test_a_saved_pattern_wins() {
		$this->assertSame( 'c', Pattern::of( [ 'id' => 'urn:li:share:1', 'time' => 5, 'pattern' => 'c' ] ) );
	}

	public function test_a_share_saved_without_a_pattern_is_a_unless_the_site_says_otherwise() {
		$this->assertSame( 'a', Pattern::of( [ 'id' => 'urn:li:share:1', 'time' => 5 ] ) );

		WP_Mock::onFilter( 'nhrrob_social_share_unsaved_pattern' )->with( 'a', 7 )->reply( 'c' );
		$this->assertSame( 'c', Pattern::of( [ 'id' => 'urn:li:share:1', 'time' => 7 ] ) );

		WP_Mock::onFilter( 'nhrrob_social_share_unsaved_pattern' )->with( 'a', 9 )->reply( 'z' );
		$this->assertSame( 'a', Pattern::of( [ 'id' => 'urn:li:share:1', 'time' => 9 ] ) );
	}

	public function test_a_post_that_was_not_shared_has_no_pattern() {
		$this->assertSame( '', Pattern::of( [ 'error' => 'failed' ] ) );
		$this->assertSame( '', Pattern::of( '' ) );
	}
}

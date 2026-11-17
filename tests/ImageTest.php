<?php
namespace NHRRob\SocialShare\Tests;

use NHRRob\SocialShare\Linkedin\Image;
use PHPUnit\Framework\TestCase;
use WP_Mock;

/**
 * A post with a featured image carries it unless an add-on picks the card;
 * a post without one always carries the card.
 */
class ImageTest extends TestCase {

	protected function setUp(): void {
		WP_Mock::setUp();
	}

	protected function tearDown(): void {
		WP_Mock::tearDown();
	}

	public function test_featured_image_by_default_and_card_without_one() {
		$post = (object) array( 'ID' => 1 );

		WP_Mock::userFunction( 'has_post_thumbnail' )->andReturn( true, false );

		$this->assertSame( 'featured', Image::source( $post ) );
		$this->assertSame( 'card', Image::source( $post ) );
	}

	public function test_an_add_on_can_pick_the_card_but_not_a_missing_image() {
		$post = (object) array( 'ID' => 1 );

		WP_Mock::userFunction( 'has_post_thumbnail' )->andReturn( true, true, false );
		WP_Mock::onFilter( 'nhrrob_social_share_picture' )->with( 'featured', $post )->reply( 'card' );

		$this->assertSame( 'card', Image::source( $post ) );
		$this->assertSame( 'card', Image::source( $post ) );
		$this->assertSame( 'card', Image::source( $post ) );
	}
}

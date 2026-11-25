<?php
namespace NHRRob\SocialShare\Tests;

use NHRRob\SocialShare\Hand\Links;
use NHRRob\SocialShare\Hand\Urls;
use NHRRob\SocialShare\Keys;
use PHPUnit\Framework\TestCase;
use WP_Mock;

/**
 * The share links fill in the post file's texts, subreddits and profiles,
 * take the layout of X and Facebook into account, and an address pasted back
 * is only kept when it is on that network's site.
 */
class LinksTest extends TestCase {

	private $meta = array();

	private $settings = array();

	protected function setUp(): void {
		WP_Mock::setUp();

		WP_Mock::userFunction( 'get_permalink' )->andReturn( 'https://nazmulrobin.com/blog/a-post/' );
		WP_Mock::userFunction( 'get_the_title' )->andReturn( 'A post &amp; more' );
		WP_Mock::userFunction( 'get_the_excerpt' )->andReturn( 'The excerpt.' );
		WP_Mock::userFunction( 'wp_strip_all_tags' )->andReturnArg( 0 );
		WP_Mock::userFunction( 'get_posts' )->andReturn( array() );
		WP_Mock::userFunction( 'get_post_meta' )->andReturnUsing(
			function ( $id, $key ) {
				return isset( $this->meta[ $key ] ) ? $this->meta[ $key ] : '';
			}
		);
		WP_Mock::userFunction( 'wp_parse_url' )->andReturnUsing( 'parse_url' );
		WP_Mock::userFunction( 'sanitize_hex_color' )->andReturnArg( 0 );
		WP_Mock::userFunction( 'get_option' )->andReturnUsing(
			function () {
				return $this->settings;
			}
		);
	}

	protected function tearDown(): void {
		WP_Mock::tearDown();
	}

	private function links() {
		return Links::all( (object) array( 'ID' => 1 ) );
	}

	private function layouts( $x, $facebook ) {
		$this->meta[ Keys::HAND_LAYOUT ] = array(
			'x'        => array( $x, 1 ),
			'facebook' => array( $facebook, 1 ),
		);
	}

	public function test_without_front_matter_x_gets_the_title_and_reddit_one_link() {
		$links = $this->links();

		$this->assertSame( array( 'x', 'facebook', 'reddit', 'medium' ), array_keys( $links ) );
		$this->assertSame( 'https://x.com/intent/post?text=A%20post%20%26%20more&url=https%3A%2F%2Fnazmulrobin.com%2Fblog%2Fa-post%2F', $links['x']['url'] );
		$this->assertSame( 'a', $links['x']['layout'] );
		$this->assertSame( 'https://www.reddit.com/submit?url=https%3A%2F%2Fnazmulrobin.com%2Fblog%2Fa-post%2F&title=A%20post%20%26%20more', $links['reddit']['url'] );
	}

	public function test_front_matter_sets_x_text_and_one_link_per_valid_subreddit_or_profile() {
		$this->meta = array(
			Keys::X_TEXT      => 'Read this',
			Keys::REDDIT => array( 'r/Wordpress', 'ClaudeAI', 'u/nhrrob', 'r/not valid', 'r/Wordpress' ),
		);

		$links = $this->links();

		$this->assertSame( array( 'x', 'facebook', 'r_wordpress', 'r_claudeai', 'u_nhrrob', 'medium' ), array_keys( $links ) );
		$this->assertStringStartsWith( 'https://x.com/intent/post?text=Read%20this&url=', $links['x']['url'] );
		$this->assertStringStartsWith( 'https://www.reddit.com/r/Wordpress/submit?url=', $links['r_wordpress']['url'] );
		$this->assertStringStartsWith( 'https://www.reddit.com/user/nhrrob/submit?url=', $links['u_nhrrob']['url'] );
		$this->assertSame( 'Share on r/ClaudeAI', $links['r_claudeai']['label'] );
		$this->assertSame( 'Share on u/nhrrob again', $links['u_nhrrob']['again'] );
	}

	public function test_x_text_with_its_own_link_is_posted_as_written_in_layout_b() {
		$this->meta = array( Keys::X_TEXT => 'Try it https://wordpress.org/plugins/x/' );

		$x = $this->links()['x'];

		$this->assertStringNotContainsString( '&url=', $x['url'] );
		$this->assertSame( 'b', $x['layout'] );
	}

	public function test_x_layout_c_leaves_the_link_for_the_reply() {
		$this->layouts( 'c', 'a' );

		$url = $this->links()['x']['url'];

		$this->assertStringNotContainsString( '&url=', $url );
		$this->assertStringContainsString( rawurlencode( 'Link in the reply.' ), $url );
	}

	public function test_facebook_copies_the_linkedin_text_for_each_layout() {
		$this->meta[ Keys::LINKEDIN_TEXT ] = 'A long text.';

		$this->layouts( 'a', 'a' );
		$facebook = $this->links()['facebook'];
		$this->assertSame( 'https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fnazmulrobin.com%2Fblog%2Fa-post%2F', $facebook['url'] );
		$this->assertSame( 'A long text.', $facebook['copy'] );

		$this->layouts( 'a', 'b' );
		$facebook = $this->links()['facebook'];
		$this->assertSame( 'https://www.facebook.com/', $facebook['url'] );
		$this->assertSame( "A long text.\n\nhttps://nazmulrobin.com/blog/a-post/", $facebook['copy'] );

		$this->layouts( 'a', 'c' );
		$this->assertSame( "A long text.\n\nLink in the first comment.", $this->links()['facebook']['copy'] );
	}

	public function test_facebook_text_of_its_own_is_copied_instead_of_the_linkedin_text() {
		$this->meta[ Keys::LINKEDIN_TEXT ] = 'The LinkedIn text';
		$this->meta[ Keys::FACEBOOK_TEXT ] = 'ফেসবুকের লেখা';

		$this->layouts( 'a', 'b' );
		$this->assertSame( "ফেসবুকের লেখা\n\nhttps://nazmulrobin.com/blog/a-post/", $this->links()['facebook']['copy'] );
	}

	public function test_facebook_text_does_not_change_the_linkedin_text() {
		$this->meta[ Keys::LINKEDIN_TEXT ] = 'The LinkedIn text';
		$this->meta[ Keys::FACEBOOK_TEXT ] = 'Facebook only';

		$this->assertSame( 'The LinkedIn text', \NHRRob\SocialShare\Linkedin\Share::text( (object) [ 'ID' => 7 ] ) );
	}

	public function test_facebook_without_a_linkedin_text_uses_the_title_and_excerpt() {
		$this->assertSame( "A post & more\n\nThe excerpt.", $this->links()['facebook']['copy'] );
	}

	public function test_medium_copies_the_post_link() {
		$medium = $this->links()['medium'];

		$this->assertSame( 'https://nazmulrobin.com/blog/a-post/', $medium['copy'] );
		$this->assertSame( 'Import to Medium again', $medium['again'] );
	}

	public function test_pasted_address_must_be_on_the_network() {
		$this->assertTrue( Urls::on_network( 'x', 'https://x.com/nhrrob/status/1' ) );
		$this->assertTrue( Urls::on_network( 'facebook', 'https://www.facebook.com/nhrrob/posts/1' ) );
		$this->assertTrue( Urls::on_network( 'r_wordpress', 'https://www.reddit.com/r/Wordpress/comments/1/' ) );
		$this->assertTrue( Urls::on_network( 'u_nhrrob', 'https://www.reddit.com/user/nhrrob/comments/1/' ) );
		$this->assertTrue( Urls::on_network( 'medium', 'https://nhrrob.medium.com/a-post-1' ) );
		$this->assertFalse( Urls::on_network( 'x', 'https://evil-x.com/1' ) );
		$this->assertFalse( Urls::on_network( 'facebook', 'https://facebook.com.evil.test/1' ) );
		$this->assertFalse( Urls::on_network( 'reddit', 'https://reddit.com.evil.test/1' ) );
		$this->assertFalse( Urls::on_network( 'unknown', 'https://x.com/1' ) );
	}

	public function test_the_settings_give_reddit_targets_and_leave_out_networks() {
		$this->settings = array(
			'hand'   => array( 'reddit', 'medium' ),
			'reddit' => array( 'u/nhrrob', 'r/Wordpress' ),
		);

		$links = $this->links();

		$this->assertSame( array( 'u_nhrrob', 'r_wordpress', 'medium' ), array_keys( $links ) );

		// A post's own list replaces the settings' one.
		$this->meta = array( Keys::REDDIT => array( 'r/laravel' ) );
		$this->assertSame( array( 'r_laravel', 'medium' ), array_keys( $this->links() ) );
	}

	public function test_without_rotation_x_and_facebook_use_layout_a() {
		$this->settings = array( 'rotate' => false );

		$links = $this->links();

		$this->assertSame( 'a', $links['x']['layout'] );
		$this->assertSame( 'a', $links['facebook']['layout'] );
	}

	public function test_an_add_on_can_change_the_text_of_each_network() {
		$this->meta = array( Keys::LINKEDIN_TEXT => 'The LinkedIn text' );
		$this->layouts( 'a', 'a' );

		$post = (object) array( 'ID' => 1 );

		WP_Mock::onFilter( 'nhrrob_social_share_text' )->with( 'A post & more', $post, 'x' )->reply( 'Second X text' );
		WP_Mock::onFilter( 'nhrrob_social_share_text' )->with( 'The LinkedIn text', $post, 'facebook' )->reply( 'Second text' );

		$links = Links::all( $post );

		$this->assertStringStartsWith( 'https://x.com/intent/post?text=Second%20X%20text&url=', $links['x']['url'] );
		$this->assertSame( 'Second text', $links['facebook']['copy'] );
	}
}

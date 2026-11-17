<?php
namespace NHRRob\SocialShare\Tests;

use NHRRob\SocialShare\Stats\Networks;
use PHPUnit\Framework\TestCase;

/**
 * Which social network a referring host belongs to.
 */
class NetworksTest extends TestCase {

	public function test_hosts_and_their_subdomains_belong_to_their_network() {
		$this->assertSame( 'nhrrob_social_share_from_linkedin', Networks::network( 'linkedin.com' ) );
		$this->assertSame( 'nhrrob_social_share_from_linkedin', Networks::network( 'lnkd.in' ) );
		$this->assertSame( 'nhrrob_social_share_from_linkedin', Networks::network( 'm.linkedin.com' ) );
		$this->assertSame( 'nhrrob_social_share_from_x', Networks::network( 't.co' ) );
		$this->assertSame( 'nhrrob_social_share_from_x', Networks::network( 'x.com' ) );
		$this->assertSame( 'nhrrob_social_share_from_facebook', Networks::network( 'l.facebook.com' ) );
		$this->assertSame( 'nhrrob_social_share_from_facebook', Networks::network( 'm.facebook.com' ) );
		$this->assertSame( 'nhrrob_social_share_from_reddit', Networks::network( 'old.reddit.com' ) );
		$this->assertSame( 'nhrrob_social_share_from_medium', Networks::network( 'nhrrob.medium.com' ) );
	}

	public function test_other_hosts_and_lookalikes_belong_to_none() {
		$this->assertSame( '', Networks::network( 'google.com' ) );
		$this->assertSame( '', Networks::network( '' ) );
		$this->assertSame( '', Networks::network( 'notlinkedin.com' ) );
		$this->assertSame( '', Networks::network( 'linkedin.com.example.org' ) );
		$this->assertSame( '', Networks::network( 'tt.co' ) );
	}
}

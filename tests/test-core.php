<?php
/**
 * Tests for the plugin core.
 *
 * @package LiquidWeb\TrafficReport
 * @author  Liquid Web
 */

use LiquidWeb\TrafficReport\Core as Core;
use LiquidWeb\TrafficReport\TestTools\MonsterInsights;
use LiquidWeb\TrafficReport\TestTools\MonsterInsights_GA;

class CoreTest extends WP_UnitTestCase {

	public function tearDown() {
		MonsterInsights::reset();

		parent::tearDown();
	}

	public function test_add_post_type_support() {
		Core\add_post_type_support();

		$this->assertTrue( post_type_supports( 'post', 'traffic-report' ) );
		$this->assertTrue( post_type_supports( 'page', 'traffic-report' ) );
	}

	public function test_get_page_view_counts() {
		$client = MonsterInsights()->ga;
		$client->total_results = 3;

		$results = $client->do_request();

		$this->assertEquals( 3, $results['body']['totalResults'] );
		$this->assertCount( 3, $results['body']['rows'] );
	}

	public function test_get_counts() {
		$client = MonsterInsights()->ga;
		$row    = $client->generate_row();
		$client->results = [ $row ];

		$this->assertEquals( [
			$row[0] => $row[1],
		], Core\get_counts() );
	}

	public function test_format_results() {
		$results = [
			[ '/foo/', 123 ],
			[ '/bar/', 456 ],
			[ '/baz/', 789 ],
		];

		$this->assertEquals( [
			'/foo/' => 123,
			'/bar/' => 456,
			'/baz/' => 789,
		], Core\format_results( $results ) );
	}
}

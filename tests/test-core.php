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

	public function setUp() {
		parent::setUp();

		$this->set_permalink_structure( '/%year%/%monthnum%/%day%/%postname%/' );
	}
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

	public function test_refresh_counts() {
		$post = $this->factory()->post->create();

		MonsterInsights()->ga->results = [
			[ get_permalink_path( $post ), 123 ],
		];

		$this->assertEquals( [
			$post => 123,
		], Core\refresh_counts() );
		$this->assertEquals( 123, (int) get_post_meta( $post, '_traffic_report_views', true ) );
	}

	public function test_refresh_counts_clears_old_values() {
		$post1 = $this->factory()->post->create();
		$post2 = $this->factory()->post->create();

		add_post_meta( $post1, '_traffic_report_views', 2 );
		add_post_meta( $post2, '_traffic_report_views', 3 );

		MonsterInsights()->ga->results = [
			[ get_permalink_path( $post1 ), 5 ],
		];

		Core\refresh_counts();

		$this->assertEquals( 5, (int) get_post_meta( $post1, '_traffic_report_views', true ) );
		$this->assertEmpty( get_post_meta( $post2, '_traffic_report_views', true ) );
	}

	public function test_prepare_results() {
		$post = $this->factory()->post->create();

		$this->assertEquals( [
			$post => 42,
		], Core\prepare_results( [], [ get_permalink_path( $post ), 42 ] ) );
	}

	public function test_prepare_results_sums_multiple_entries() {
		$post   = $this->factory()->post->create();
		$before = [
			$post => 3,
		];
		$after  = [
			$post => 5,
		];

		$this->assertEquals( $after, Core\prepare_results( $before, [ get_permalink_path( $post ), 2 ] ) );
	}

	public function test_prepare_results_drops_empty_paths() {
		$this->assertEmpty( Core\prepare_results( [], [ '/this-path-does-not-exist/', 42 ] ) );
	}

	public function test_delete_stored_page_views() {
		global $wpdb;

		$values = [];
		for ( $i = 1; $i <= 10; $i++ ) {
			$values[] = sprintf( '(%d, \'_traffic_report_views\', %d)', $i, $i );
		}

		// Using GTE as there may be rows left over from other tests.
		$this->assertGreaterThanOrEqual( 10, $wpdb->query( sprintf( // WPCS: unprepared SQL ok.
			"INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES %s",
			implode( ',', $values )
		) ) );

		$this->assertGreaterThanOrEqual( 10, Core\delete_stored_page_views() );

		$this->assertEmpty( $wpdb->get_var( "SELECT COUNT(post_id) FROM $wpdb->postmeta WHERE meta_key = '_traffic_report_views'" ) );
	}

	public function test_delete_postmeta_cache() {
		$post = $this->factory()->post->create();
		wp_cache_add( $post, true, 'post_meta' );

		$this->assertTrue( wp_cache_get( $post, 'post_meta' ) );

		Core\delete_postmeta_cache( $post );

		$this->assertEmpty( wp_cache_get( $post, 'post_meta' ) );
	}
}

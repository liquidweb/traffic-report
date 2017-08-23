<?php
/**
 * Tests for admin integration.
 *
 * @package LiquidWeb\TrafficReport
 * @author  Liquid Web
 */

class AdminTest extends WP_UnitTestCase {

	public function test_post_column_exists() {
		$this->factory()->post->create_many( 3 );

		$table = _get_list_table( 'WP_Posts_List_Table', [
			'screen' => 'edit-post',
		] );
		$this->assertTrue( post_type_supports( 'post', 'traffic-report' ) );
		ob_start();
		$table->print_column_headers();
		$contents = ob_get_clean();

		$this->assertContains(
			'<th scope="col" id=\'traffic-report\'',
			$contents,
			'The Traffic Report column does not appear on the post list table.'
		);
	}

	public function test_make_pageviews_column_sortable() {
		$this->factory()->post->create();

		$table = _get_list_table( 'WP_Posts_List_Table', [
			'screen' => 'edit-post',
		] );

		$this->assertArrayHasKey( 'traffic-report', $table->get_column_info()[2] );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_can_sort_post_list_by_pageviews() {
		$top_post = $this->factory()->post->create( [
			'post_title' => 'A popular post',
		]);
		$sad_post = $this->factory()->post->create( [
			'post_title' => 'A not-so-popular post',
		]);
		$new_post = $this->factory()->post->create( [
			'post_title' => 'A brand new post',
		]);

		add_post_meta( $top_post, '_traffic_report_views', 100 );
		add_post_meta( $sad_post, '_traffic_report_views', 2 );

		set_current_screen( 'edit.php' );
		$this->go_to( admin_url( 'edit.php?orderby=traffic-report&order=desc' ) );

		global $wp_query;

		$this->assertCount( 3, $wp_query->posts );
		$this->assertEquals( $top_post, $wp_query->posts[0]->ID, 'Posts with high counts should be at the top.' );
		$this->assertEquals( $sad_post, $wp_query->posts[1]->ID, 'Posts with low counts should be at the bottom.' );
		$this->assertEquals( $new_post, $wp_query->posts[2]->ID, 'Posts without counts should be treated as 0.' );
	}

	public function test_can_sort_post_list_by_pageviews_in_ascending_order() {
		$top_post = $this->factory()->post->create( [
			'post_title' => 'A popular post',
		]);
		$sad_post = $this->factory()->post->create( [
			'post_title' => 'A not-so-popular post',
		]);
		$new_post = $this->factory()->post->create( [
			'post_title' => 'A brand new post',
		]);

		add_post_meta( $top_post, '_traffic_report_views', 100 );
		add_post_meta( $sad_post, '_traffic_report_views', 2 );

		set_current_screen( 'edit.php' );
		$this->go_to( admin_url( 'edit.php?orderby=traffic-report&order=asc' ) );

		global $wp_query;

		$this->assertEquals( $top_post, $wp_query->posts[2]->ID, 'Posts with high counts should be at the bottom.' );
		$this->assertEquals( $sad_post, $wp_query->posts[1]->ID, 'Posts with low counts should be at the top.' );
		$this->assertEquals( $new_post, $wp_query->posts[0]->ID, 'Can\'t get any lower than 0, huh?' );
	}

	public function test_post_column_shows_page_view_count() {
		$post  = $this->factory()->post->create_and_get();
		$views = mt_rand( 0, 1500 );

		add_post_meta( $post->ID, '_traffic_report_views', $views, true );

		$table = _get_list_table( 'WP_Posts_List_Table', [
			'screen' => 'edit-post',
		] );

		ob_start();
		$table->single_row_columns( $post );
		$contents = ob_get_clean();

		$this->assertContains(
			'<td class=\'traffic-report column-traffic-report\' data-colname="Views">' . $views . '</td>',
			$contents
		);
	}
}

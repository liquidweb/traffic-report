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
			'<th scope="col" id=\'traffic-report\' class=\'manage-column column-traffic-report\'>',
			$contents,
			'The Traffic Report column does not appear on the post list table.'
		);
	}

	public function test_post_column_shows_page_view_count() {
		$this->markTestSkipped( 'Table rows are not yet displaying real values.' );
		$post  = $this->factory()->post->create_and_get();
		$views = mt_rand( 0, 1500 );

		add_post_meta( $post->ID, '_traffic_report_page_views', $views, true );

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

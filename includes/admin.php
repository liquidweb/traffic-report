<?php
/**
 * Handle wp-admin integration for Traffic Report.
 *
 * @package LiquidWeb\TrafficReport
 * @author  Liquid Web
 */

namespace LiquidWeb\TrafficReport\Settings;

/**
 * Register the Traffic Report column for admin post lists.
 *
 * @param array $columns Currently-registered columns.
 * @return array The filtered $columns array.
 */
function add_pageviews_column( $columns ) {
	$columns['traffic-report'] = _x( 'Views', 'admin post list column title', 'traffic-report' );

	return $columns;
}
add_filter( 'manage_posts_columns', __NAMESPACE__ . '\add_pageviews_column' );

/**
 * Display the view count in the post column.
 *
 * @param string $column  The column being populated.
 * @param int    $post_id The post ID.
 *
 * @todo Pull live data.
 */
function populate_column( $column, $post_id ) {
	if ( 'traffic-report' !== $column ) {
		return;
	}

	echo '123';
}
add_action( 'manage_posts_custom_column', __NAMESPACE__ . '\populate_column', 10, 2 );

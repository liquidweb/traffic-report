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
 * Enable posts to be sorted by page views.
 *
 * @param array $columns An array of sortable columns.
 * @return array The filtered $columns array.
 */
function make_pageviews_column_sortable( $columns ) {
	$columns['traffic-report'] = 'traffic-report';

	return $columns;
}
add_filter( 'manage_edit-post_sortable_columns', __NAMESPACE__ . '\make_pageviews_column_sortable' );

/**
 * Handle sorting results by page views.
 *
 * @param WP_Query $query The current WP_Query instance, passed by reference.
 */
function maybe_sort_by_page_views( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || 'traffic-report' !== $query->get( 'orderby' ) ) {
		return;
	}

	$order = $query->get( 'order' );

	$query->set( 'meta_query', array_merge(
		(array) $query->get( 'meta_query' ),
		array(
			'relation'            => 'OR',
			'traffic_report_meta' => array(
				'key'     => '_traffic_report_views',
				'compare' => 'EXISTS',
				'type'    => 'numeric',
			),
			'traffic_report_none' => array(
				'key'     => '_traffic_report_views',
				'compare' => 'NOT EXISTS',
				'type'    => 'numeric',
			),
		)
	) );
	$query->set( 'orderby', array(
		'traffic_report_meta' => $order,
		'traffic_report_none' => $order,
	) );
}
add_action( 'pre_get_posts', __NAMESPACE__ . '\maybe_sort_by_page_views' );

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

	global $wp_query;

	echo esc_html( get_post_meta( $post_id, '_traffic_report_views', true ) );
}
add_action( 'manage_posts_custom_column', __NAMESPACE__ . '\populate_column', 10, 2 );

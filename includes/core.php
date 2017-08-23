<?php
/**
 * Core plugin functionality.
 *
 * @package LiquidWeb\TrafficReport
 * @author  Liquid Web
 */

namespace LiquidWeb\TrafficReport\Core;

use LiquidWeb\TrafficReport\MonsterInsights;
use WP_Post;

/**
 * Add traffic-report post-type support for core post types.
 */
function add_post_type_support() {
	\add_post_type_support( 'post', 'traffic-report' );
	\add_post_type_support( 'page', 'traffic-report' );
}
add_action( 'init', __NAMESPACE__ . '\add_post_type_support' );

/**
 * Retrieve counts for all pages on the site.
 *
 * @global $wpdb
 *
 * @param array $args  {
 *   Optional. Arguments to pass to Google Analytics.
 *
 *   @type string $start-date The start date for the results. Default is pulled from settings.
 *   @type string $end-date   The end date for the results. Default is today.
 * }
 *
 * @todo Add support for multiple pages of results.
 */
function refresh_counts( $args = array() ) {
	global $wpdb;

	$base    = 'https://www.googleapis.com/analytics/v3/data/ga';
	$client  = MonsterInsights()->ga;
	$values  = [];
	$args    = wp_parse_args( $args, array(
		'ids'        => 'ga:' . $client->profile,
		'start-date' => date( 'Y-m-d', strtotime( '-7 days' ) ),
		'end-date'   => date( 'Y-m-d' ),
		'metrics'    => 'ga:pageviews',
		'dimensions' => 'ga:pagePath',
	) );

	// Query Google Analytics.
	$response = $client->do_request( add_query_arg( $args, $base ) );

	// Iterate over the results to map IDs to totals.
	$results = array_reduce( $response['body']['rows'], __NAMESPACE__ . '\prepare_results', array() );

	// Build the values for the database INSERT statement.
	foreach ( $results as $post_id => $count ) {
		$values[] = $wpdb->prepare( '(%d, \'_traffic_report_views\', %d)', $post_id, $count );
	}

	// Bulk-update the post meta.
	$wpdb->query( 'START TRANSACTION' );
	$deleted  = $wpdb->delete( $wpdb->postmeta, array(
		'meta_key' => '_traffic_report_views',
	) );
	$inserted = $wpdb->query( sprintf( // WPCS: unprepared SQL ok.
		"INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES %s",
		implode( ', ', $values )
	) );

	// Finish the transaction.
	if ( false === $deleted || false === $inserted ) {
		$wpdb->query( 'ROLLBACK' );
	} else {
		$wpdb->query( 'COMMIT' );

		// Update the post meta caches for the given posts.
		$post_ids = array_keys( $results );
		array_map( __NAMESPACE__ . '\delete_postmeta_cache', $post_ids );
		update_postmeta_cache( $post_ids );
	}

	return $results;
}

/**
 * Given a row of results, find the corresponding post ID and add the page view count to it.
 *
 * @param array $results The current results array.
 * @param array $row     The row from Google Analytics, which will contain two keys: the page path and
 *                       the pageview count.
 * @return array The filtered $results array.
 */
function prepare_results( $results, $row ) {
	$post_id = url_to_postid( $row[0] );

	if ( 0 === $post_id ) {
		return $results;
	}

	if ( isset( $results[ $post_id ] ) ) {
		$results[ $post_id ] += (int) $row[1];
	} else {
		$results[ $post_id ] = (int) $row[1];
	}

	return $results;
}

/**
 * Delete post meta for a single post ID.
 *
 * @param int $post_id The post ID for which the postmeta cache should be cleared.
 */
function delete_postmeta_cache( $post_id ) {
	wp_cache_delete( $post_id, 'post_meta' );
}

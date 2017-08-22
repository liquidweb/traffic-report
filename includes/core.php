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
 * @param array $args  {
 *   Optional. Arguments to pass to Google Analytics.
 *
 *   @type string $start-date The start date for the results. Default is pulled from settings.
 *   @type string $end-date   The end date for the results. Default is today.
 * }
 * @param bool  $force Optional. Force a refresh of the cache? Default is false.
 * @return A flat array of post slugs => page views.
 *
 * @todo Add support for multiple pages of results.
 */
function get_counts( $args = array(), $force = false ) {
	$base    = 'https://www.googleapis.com/analytics/v3/data/ga';
	$client  = MonsterInsights()->ga;
	$args    = wp_parse_args( $args, array(
		'ids'        => 'ga:' . $client->profile,
		'start-date' => date( 'Y-m-d', strtotime( '-7 days' ) ),
		'end-date'   => date( 'Y-m-d' ),
		'metrics'    => 'ga:pageviews',
		'dimensions' => 'ga:pagePath',
	) );
	$cache   = 'trafficreport_cache_' . substr( md5( wp_json_encode( $args ) ), 0, 10 );
	$cached  = get_transient( $cache );
	$timeout = HOUR_IN_SECONDS;

	// Return the cached value.
	if ( $cached && ! $force ) {
		return $cached;
	}

	// Query Google Analytics.
	$response = $client->do_request( add_query_arg( $args, $base ) );

	// Format the results.
	$results  = format_results( $response['body']['rows'] );

	// Save the results in the cache.
	set_transient( $cache, $results, $timeout );

	return $results;
}

/**
 * Given a list of results, return a simple array of path => count.
 *
 * @param array $rows The array of rows returned from Google Analytics. It's expected that each
 *                    value in the array will be an array with two values: the path and the count.
 * @return A simple array in the form of $path => $count.
 */
function format_results( $rows ) {
	return array_reduce( (array) $rows, function ( $carry, $item ) {
		$carry[ $item[0] ] = $item[1];

		return $carry;
	}, array() );
}

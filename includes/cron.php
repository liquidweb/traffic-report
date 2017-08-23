<?php
/**
 * WP-Cron integration.
 *
 * @package LiquidWeb\TrafficReport
 * @author  Liquid Web
 */

namespace LiquidWeb\TrafficReport\Cron;

/**
 * Register the Traffic Report cron events.
 */
function register_cron_events() {
	if ( ! wp_next_scheduled( 'traffic_report_refresh' ) ) {
		wp_schedule_event( time(), 'hourly', 'traffic_report_refresh' );
	}
}
register_activation_hook( TRAFFIC_REPORT_MAIN_FILE, __NAMESPACE__ . '\register_cron_events' );

/**
 * Remove cron events when the plugin is deactivated.
 */
function deregister_cron_events() {
	$timestamp = wp_next_scheduled( 'traffic_report_refresh' );
	wp_unschedule_event( $timestamp, 'traffic_report_refresh' );
}
register_deactivation_hook( TRAFFIC_REPORT_MAIN_FILE, __NAMESPACE__ . '\deregister_cron_events' );

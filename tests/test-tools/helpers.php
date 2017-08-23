<?php
/**
 * Test helper functions.
 */

use LiquidWeb\TrafficReport\TestTools\MonsterInsights;

if ( ! function_exists( 'MonsterInsights' ) ) {
	function MonsterInsights() {
		return MonsterInsights::get_instance();
	}
}

if ( ! function_exists( 'get_permalink_path' ) ) {
	function get_permalink_path( $id ) {
		return wp_parse_url( get_permalink( $id ), PHP_URL_PATH );
	}
}

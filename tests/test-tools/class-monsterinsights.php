<?php
/**
 * Test double for the MonsterInsights class.
 *
 * @package LiquidWeb\TrafficReport
 * @author  Liquid Web
 */

namespace LiquidWeb\TrafficReport\TestTools;

class MonsterInsights {

	public static $instance;

	protected function __construct() {}
	protected function __clone() {}
	protected function __wakeup() {}

	public static function get_instance() {
		if ( self::$instance ) {
			return self::$instance;
		}

		self::$instance = new MonsterInsights();
		self::$instance->ga = new MonsterInsights_GA();

		return self::$instance;
	}

	public static function reset() {
		self::$instance = null;
	}
}

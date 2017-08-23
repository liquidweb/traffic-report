<?php
/**
 * Tests for the plugin's cron functionality.
 *
 * @package LiquidWeb\TrafficReport
 * @author  Liquid Web
 */

use LiquidWeb\TrafficReport\Cron as Cron;

class CronTest extends WP_UnitTestCase {

	public function test_register_cron_events() {
		$this->assertFalse( wp_next_scheduled( 'traffic_report_refresh' ) );

		Cron\register_cron_events();

		$this->assertNotEmpty( wp_next_scheduled( 'traffic_report_refresh' ) );
	}

	public function test_cron_job_is_registered_on_plugin_activation() {
		$this->assertFalse( wp_next_scheduled( 'traffic_report_refresh' ) );

		do_action( 'activate_' . plugin_basename( TRAFFIC_REPORT_MAIN_FILE ) );

		$this->assertNotEmpty( wp_next_scheduled( 'traffic_report_refresh' ) );
	}

	public function test_deregister_cron_events() {
		Cron\register_cron_events();

		$this->assertNotEmpty( wp_next_scheduled( 'traffic_report_refresh' ) );

		Cron\deregister_cron_events();

		$this->assertFalse( wp_next_scheduled( 'traffic_report_refresh' ) );
	}

	public function test_cron_job_is_deregistered_on_plugin_deactivation() {
		Cron\register_cron_events();

		do_action( 'deactivate_' . plugin_basename( TRAFFIC_REPORT_MAIN_FILE ) );

		$this->assertFalse( wp_next_scheduled( 'traffic_report_refresh' ) );
	}

	public function test_hook_has_callbacks() {
		$this->assertTrue( has_action( 'traffic_report_refresh' ), 'Nothing is hooked to the `traffic_report_refresh` action!' );
	}
}

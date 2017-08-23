<?php
/**
 * Tests for the plugin's cron functionality.
 *
 * @package LiquidWeb\TrafficReport
 * @author  Liquid Web
 */

use LiquidWeb\TrafficReport\Cron as Cron;

use LiquidWeb\TrafficReport\MonsterInsights as Plugin;

class CronTest extends WP_UnitTestCase {

	public function test_maybe_register_cron_events() {
		$this->assertTrue( Plugin\is_plugin_active() );
		$this->assertFalse( wp_next_scheduled( 'traffic_report_refresh' ) );

		Cron\maybe_register_cron_events();

		$this->assertNotEmpty( wp_next_scheduled( 'traffic_report_refresh' ) );
	}

	public function test_maybe_register_cron_events_does_nothing_if_monster_insights_is_inactive() {
		$this->disable_monster_insights();

		$this->assertFalse( Plugin\is_plugin_active() );

		Cron\maybe_register_cron_events();

		$this->assertFalse( wp_next_scheduled( 'traffic_report_refresh' ) );
	}

	public function test_cron_job_is_registered_on_plugin_activation_if_monster_insights_is_present() {
		$this->assertTrue( (bool) has_action(
			'activate_' . plugin_basename( TRAFFIC_REPORT_MAIN_FILE ),
			'LiquidWeb\TrafficReport\Cron\maybe_register_cron_events'
		) );
	}

	public function test_register_cron_events() {
		$this->assertFalse( wp_next_scheduled( 'traffic_report_refresh' ) );

		Cron\register_cron_events();

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

	/**
	 * Emulates disabling the Monster Insights plugin.
	 */
	public function disable_monster_insights() {
		MonsterInsights()->ga = false;
	}
}

<?php
/**
 * Integration with Monster Insights.
 *
 * @package LiquidWeb\TrafficReport
 * @author  Liquid Web
 */

namespace LiquidWeb\TrafficReport\MonsterInsights;

use LiquidWeb\TrafficReport\Cron as Cron;

/**
 * Determine if Monster Insights is active.
 *
 * Note that this check includes verifies that MonsterInsights()->ga is true, which lets this
 * plugin's unit tests "deactivate" the plugin.
 *
 * @return bool True if the plugin exists, false otherwise.
 */
function is_plugin_active() {
	return function_exists( 'MonsterInsights' ) && MonsterInsights()->ga;
}


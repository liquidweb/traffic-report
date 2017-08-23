<?php
/**
 * Plugin Name: Traffic Report
 * Plugin URI:  https://github.com/liquidweb/traffic-report
 * Description: Get details about your site's performance from right within WordPress
 * Author:      Liquid Web
 * Author URI:  https://www.liquidweb.com
 * Text Domain: traffic-report
 * Domain Path: /languages
 * Version:     1.0.0
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 *
 * @package LiquidWeb\TrafficReport
 * @author  Liquid Web
 */

define( 'TRAFFIC_REPORT_MAIN_FILE', __FILE__ );

require_once __DIR__ . '/includes/admin.php';
require_once __DIR__ . '/includes/core.php';
require_once __DIR__ . '/includes/cron.php';

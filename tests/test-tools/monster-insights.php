<?php

use LiquidWeb\TrafficReport\TestTools\MonsterInsights;

if ( ! function_exists( 'MonsterInsights' ) ) {
	function MonsterInsights() {
		return MonsterInsights::get_instance();
	}
}


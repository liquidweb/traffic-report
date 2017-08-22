<?php
/**
 * Test double for the MonsterInsights_GA class.
 *
 * @package LiquidWeb\TrafficReport
 * @author  Liquid Web
 */

namespace LiquidWeb\TrafficReport\TestTools;

use Faker\Factory;

class MonsterInsights_GA {

	public $profile;

	public $status_code = 200;

	public $results = array();

	public $total_results = 0;

	protected $faker;

	public function __construct() {
		$this->faker = Factory::create();
		$this->profile = $this->faker->randomNumber;
	}

	public function do_request( $url = null ) {
		if ( $this->total_results && empty( $this->results ) ) {
			for ( $i = 0; $i < $this->total_results; $i++ ) {
				$this->results[] = $this->generate_row();
			}
		} elseif ( 0 === $this->total_results && ! empty( $this->results ) ) {
			$this->total_results = count( $this->results );
		}

		return [
			'response' => [
				'code' => $this->status_code,
			],
			'body'     => [
				'profileInfo'  => [
					'profileId' => $this->profile,
				],
				'totalResults' => $this->total_results,
				'rows'         => (array) $this->results,
			],
		];
	}

	public function generate_row() {
		return [
			'/' . $this->faker->unique()->slug . '/',
			$this->faker->randomNumber,
		];
	}
}

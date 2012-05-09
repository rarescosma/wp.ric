<?php

class ricTime {
	/**
	 * @param $date - date in mm/dd/yyyy format
	 */
	function datepicker_to_mysql($date) {
		$time = datepicker_to_time($date);
		$mysqldate = date( 'Y-m-d H:i:s', $time );

		return $mysqldate;
	}

	/**
	 * @param $date - date in mm/dd/yyyy format
	 */
	function datepicker_to_time($date) {
		$parts = explode('/', $date);
		$time = mktime(0,0,0,$parts[0],$parts[1],$parts[2]);

		return $time;
	}

	/**
	 * @param $date - date in mm/dd/yyyy format
	 * @param $start_date - start date in mm/dd/yyyy format
	 * @param $end_date - end date in mm/dd/yyyy format
	 */
	function datepicker_is_in_interval($date, $start_date, $end_date) {
		$time = self::datepicker_to_time($date);
		$start_time = self::datepicker_to_time($start_date);
		$end_time = self::datepicker_to_time($end_date);

		return ($time >= $start_time and $time <= $end_time);
	}

	/**
	 * @param $date - date in mm/dd/yyyy format
	 */
	function days_until( $date ) {
		$now = time();
		$then = self::datepicker_to_time( $date );
		$full_days = floor( ($then - $now) / 86400 );

		return ( $full_days > 0 ) ? $full_days : 0;
	}

}


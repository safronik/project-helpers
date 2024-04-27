<?php

namespace Safronik\Helpers;

class TimeHelper{
    
    /**
	 * Get timestamp of the current time interval start with determined $range
	 *
	 * @param int $interval in seconds
	 *
	 * @return int
	 */
	public static function getIntervalStart( int $interval ): int
    {
		return time() - ( ( time() - strtotime( date( 'd F Y' ) ) ) % $interval );
	}

}
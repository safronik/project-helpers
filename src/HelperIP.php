<?php

namespace Safronik\Helpers;

use Safronik\Globals\Server;
use Safronik\Globals\Request;

class HelperIP{
    
    public static function get(): string
    {
		// X-Forwarded-For
        $headers = Request::getHTTPHeaders();
        if( isset( $headers['X-Forwarded-For'] ) ){
	        $tmp = explode( ',', trim( $headers['X-Forwarded-For'] ) );
	        $ip = trim( $tmp[0] );
        }
		
		// Remote address
		if( ! isset( $ip ) ){
            $ip = Server::get( 'REMOTE_ADDR' );
		}
		
		return self::validate( $ip )
            ? $ip
            : '0.0.0.0';
	}
	
    public static function getDecimal( ?string $ip = null ): int
    {
        $ip = $ip ?: self::get();
        return ip2long( $ip ) ?: 0;
    }
    
	public static function validate( $ip ): false|string
    {
		if( ! $ip ){
			return false;
		}
		
		if( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ){
			return 'v4';
		}
		
		return false;
	}
}
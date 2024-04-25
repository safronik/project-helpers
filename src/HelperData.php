<?php

namespace Safronik\Helpers;

class HelperData{
	/**
	 * Check if the given string is a valid JSON
	 *
	 * @param $json
	 *
	 * @return bool
	 */
	public static function unpackIfJSON( $json ) {
		if ( is_string( $json ) && strlen( $json ) > 8 && ( $json[0] === '[' || $json[0] === '{' ) ) {
			return json_decode( $json, true );
		}
		
		return false;
	}
    
    /**
     * Generates UUID
     *
     * @param string $type
     * @param int    $length
     *
     * @return string
     */
    public static function createToken( string $type = 'guid', int $length = 128 ): string
    {
        $token = match ( $type ){
            default => trim( com_create_guid() ),
        };
        
        return substr( $token, 0, $length );
    }
}
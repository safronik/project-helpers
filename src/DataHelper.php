<?php

namespace Safronik\Helpers;

class DataHelper{
    
    /**
     * Checks if the string is JSON type
     *
     * @param mixed $value
     *
     * @return bool
     */
	public static function isJson( mixed $value )
	{
        return is_string( $value )
                && strlen( $value ) > 8
                && ( $value[0] === '[' || $value[0] === '{' )
                && json_decode( $value );
	}
	
	/**
	 * Checks if given string is valid regular expression
	 *
	 * @param string $regexp
	 *
	 * @return bool
	 */
	public static function isRegexp( $regexp){
		return @preg_match('/' . $regexp . '/', null) !== false;
	}

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
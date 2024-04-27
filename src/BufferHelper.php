<?php

namespace Safronik\Helpers;

class BufferHelper{
    
    /**
     * Pops line from the csv buffer and format it by map to array
     * The very first line of CSV file should contain fields names
     *
     * @param string $csv
     *
     * @return array
     */
    public static function getCSVMap( string &$csv ): array
    {
        return explode( ',', static::popCSVLine( $csv ) );
    }
    
    /**
     * Parse CSV
     *
     * @param string $scv_string
     *
     * @return array
     */
    public static function parseCSV( string &$scv_string ): array
    {
        $scv_array = explode( "\n", $scv_string );
        $scv_array = self::cleanUpCSV( $scv_array );
        
        foreach( $scv_array as &$line ){
            $line = str_getcsv( $line, ',', '\'' );
        }
        
        return $scv_array;
    }
	
	/**
	 * Parse NSV (newline separated values)
	 *
	 * @param string $nsv_string
	 *
	 * @return string[]
	 */
	public static function parseNSV( string $nsv_string ): array
    {
		$nsv_string = str_replace( array( "\r\n", "\n\r", "\r", "\n" ), "\n", $nsv_string );
		$nsv_array  = explode( "\n", $nsv_string );
        
        // Clean up
        foreach( $nsv_array as $key => &$value ){
            if( $value === '' ){
                unset( $nsv_array[ $key ] );
            }
        }
        
		return $nsv_array;
	}
    
    /**
     * Create an array from csv string according to map
     *
     * @param string $csv
     * @param array  $map
     *
     * @return array
     */
	public static function convertCSVToArray( string &$csv, array $map = [] ): array
    {
        $out = [];
        while( $csv !== '' ){
            $out[] = static::convertCSVLineToArray( $csv, $map );
        }
        
        return $out;
    }
    
    /**
     * Pops line from the csv buffer and format it by map to array
     *
     * @param string $csv
     * @param array  $map
     * @param bool   $stripslashes
     *
     * @return array
     */
    private static function convertCSVLineToArray( string &$csv, array $map = [], bool $stripslashes = false ): array
    {
        $line = trim( static::popCSVLine( $csv ) );
        $line = str_starts_with( $line, '\'' )
            ? str_getcsv( $line, ',', '\'' )
            : explode( ',', $line );
        
        if( $stripslashes ){
            $line = array_map(
                static fn( $elem ) => stripslashes( $elem ),
                $line
            );
        }
        
        if( $map ){
            $line = array_combine( $map, $line );
        }
        
        return $line;
    }
    
    /**
     * Clears CSV array from:
     *  - clear lines itself from useless symbols
     *  - empty lines
     *
     * @param array $buffer
     *
     * @return array
     */
    private static function cleanUpCSV( array &$buffer ): array
    {
        foreach( $buffer as $key => &$line ){
            $line = trim( $line );
            if( $line === '' ){
                unset( $buffer[ $key ] );
            }
        }
        
        return $buffer;
    }
    
    /**
     * Pops line from buffer without formatting
     *
     * @param string $csv
     *
     * @return string
     */
	private static function popCSVLine( string &$csv ): string
    {
		$pos  = strpos( $csv, "\n" );
		$line = substr( $csv, 0, $pos );
		$csv  = substr_replace( $csv, '', 0, $pos + 1 );
  
		return $line;
	}
}
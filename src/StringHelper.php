<?php

namespace Safronik\Helpers;

class StringHelper{
    
    /**
	 * Function removing non UTF8 characters from array|string|object
	 *
	 * @param array|object|string $data
	 *
	 * @return array|object|string
	 */
	public static function removeNonUTF8( array|object|string $data): array|object|string
	{
		// Array || object
		if(is_array($data) || is_object($data)){
			foreach($data as $key => &$val){
				$val = self::removeNonUTF8($val);
			}
			unset($key, $val);
			
			//String
		}elseif(!preg_match('//u', $data)){
            $data = 'Nulled. Not UTF8 encoded or malformed.';
        }
        
		return $data;
	}
	
	/**
	 * Function convert anything to UTF8 and removes non UTF8 characters
	 *
	 * @param array|object|string $data
	 * @param string|null         $data_codepage
	 *
	 * @return array|object|string
	 */
	public static function toUTF8( array|object|string $data, string $data_codepage = null): array|object|string
	{
		// Array || object
		if( is_array($data) || is_object($data)){
			foreach($data as $key => &$val){
				$val = self::toUTF8($val, $data_codepage);
			}
			unset($key, $val);
			
        //String
		}elseif(
            SurroundingHelper::isExtensionLoaded( 'mbstring' ) &&
            !preg_match('//u', $data)
        ){
            $encoding = mb_detect_encoding($data) ?: $data_codepage;
            $data     = $encoding
                ? mb_convert_encoding( $data, 'UTF-8', $encoding )
                : $data;
        }
		return $data;
	}
	
	/**
	 * Function convert from UTF8
	 *
	 * @param object|array|string $obj
	 * @param string|null         $data_codepage
	 *
	 * @return mixed (array|object|string)
	 */
	public static function fromUTF8( object|array|string $obj, string $data_codepage = null ): mixed
    {
		// Array || object
		if(is_array($obj) || is_object($obj)){
			foreach($obj as $key => &$val){
				$val = self::fromUTF8($val, $data_codepage);
			}
			unset($key, $val);
			
        //String
		}elseif(
            $data_codepage !== null &&
            SurroundingHelper::isExtensionLoaded( 'mbstring') &&
            preg_match('u', $obj)
        ){
            $obj = mb_convert_encoding( $obj, $data_codepage, 'UTF-8' );
        }
        
		return $obj;
	}
 
}
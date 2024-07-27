<?php

namespace Safronik\Helpers;

class ArrayHelper
{
    /**
     * Modifies the array $array. Paste $insert on $key
     * If $key is integer, array should be sorted, otherwise behaviour is unpredictable
     *
     * Prepends insertion for string keys
     *
     * @param array      $array
     * @param int|string $key
     * @param mixed      $insert
     */
    public static function insert( array &$array, int|string $key, mixed $insert ): void
    {
        $insert = (array) $insert;
        
        // For numeric keys
        if (is_int($key)) {
            array_splice($array, $key, 0, $insert);
            
        // For string keys
        } else {
            $pos   = array_search( $key, array_keys( $array ), true );
            $array = array_merge(
                array_slice($array, 0, $pos),
                $insert,
                array_slice($array, $pos)
            );
        }
    }

    /**
	 * Merging arrays without resetting numeric keys
     *
     * Array keys will override key/value pairs in case key coincidence
	 *
	 * @param array $arr1 One-dimensional array
	 * @param array $arr2 One-dimensional array
	 *
	 * @return array Merged array
	 */
	public static function mergeSavingNumericKeys( array $arr1, array $arr2 ): array
    {
		foreach($arr2 as $key => $val){
			$arr1[$key] = $val;
		}
		return $arr1;
	}
	
	/**
	 * Merging arrays without resetting numeric keys recursive
     *
     * Array keys will override key/value pairs in case key coincidence
	 *
	 * @param array $arr1 One-dimensional array
	 * @param array $arr2 One-dimensional array
	 *
	 * @return array Merged array
	 */
    public static function mergeSavingNumericKeysRecursive( array $arr1, array $arr2 ): array
    {
        foreach( $arr2 as $key => $val ){
            
            // Array | array => array
            if( isset( $arr1[ $key ] ) && is_array( $arr1[ $key ] ) && is_array( $val ) ){
                $arr1[ $key ] = self::mergeSavingNumericKeysRecursive( $arr1[ $key ], $val );
                
            // Scalar | array => array
            }elseif( isset( $arr1[ $key ] ) && ! is_array( $arr1[ $key ] ) && is_array( $val ) ){
                $arr1[ $key ][] = $val;
                
            // Array  | scalar => array
            }elseif( isset( $arr1[ $key ] ) && is_array( $arr1[ $key ] ) && ! is_array( $val ) ){
                $arr1[ $key ][] = $val;
                
            // Scalar | scalar => scalar
            }else{
                $arr1[ $key ] = $val;
            }
        }
        
        return $arr1;
    }
}
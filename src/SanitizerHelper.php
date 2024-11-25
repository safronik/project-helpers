<?php

namespace Safronik\Helpers;

class SanitizerHelper
{
    
    /**
     * Sanitize data in different ways
     *
     * @param array $data
     * @param array $rules
     *
     * @return void
     */
    public static function sanitize( array &$data, array $rules ): void
    {
        foreach( $rules as $field => $rule ){
            static::setMissingOptionalToNull( $data, $field, $rule );
            static::setEmptyFieldsToDefault( $data, $field, $rule );
        }
    }
    
    /**
     * Set missing required fields to null
     *
     * @param $data
     * @param $field
     * @param $rule
     *
     * @return void
     */
    private static function setMissingOptionalToNull( &$data, $field, $rule ): void
    {
        $data[ $field ] = ! in_array( 'required', $rule, true ) && ! isset( $data[ $field ] )
            ? null
            : $data[ $field ];
    }
    
    /**
     * Set field to default if the default is given and field is missing
     *
     * @param array  $data
     * @param string $field
     * @param        $rule
     *
     * @return void
     */
    private static function setEmptyFieldsToDefault( array &$data, string $field, $rule ): void
    {
        $data[ $field ] = ! isset( $data[ $field ] ) && isset( $rule['default'] )
            ? $rule['default']
            : $data[ $field ];
    }

}
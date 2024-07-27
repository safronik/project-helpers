<?php

namespace Safronik\Helpers;

class ValidationHelper
{
    /**
     * @param array   $data        Data to validate
     * @param array[] ...$rule_set Set of validation rules
     *      Validation rules should in the following format:
     *      [
     *          'field_1' => [
     *              'type'    => 'string' | 'integer' | 'boolean',
     *              'content' => ['possible_value_1','possible_value_2','possible_value_n'] | '/reg_exp_to_match/',
     *              'length'   => 10 | 20 | [ 10, 20 ] | [ 10, null ]
     *              'required' // This exact value is used as a flag
     *          ],
     *          'field_n' => [ ... ],
     *          ...
     *      ]
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    public static function validate( array $data, ...$rule_set ): void
    {
        foreach( $rule_set as $rules ){
            
            self::validateRequired( $data, $rules );
            
            foreach( $data as $field => $value ){
                if( isset( $rules[ $field ] ) ){
                    ! empty( $rules[ $field ]['type'] )    && self::validateType(    $value, $field, $rules[ $field ]['type'] );
                    ! empty( $rules[ $field ]['content'] ) && self::validateContent( $value, $field, $rules[ $field ]['content'] );
                    ! empty( $rules[ $field ]['length'] )  && self::validateLength( $value, $field, $rules[ $field ]['length'] );
                }
            }
        }
    }
    
    /**
     * If a field is marked as required by rule (has 'required' or '!' element)
     *  and is missing in data
     *  throws an exception
     *
     * @param mixed $data
     * @param array $rules
     *
     * @return void
     */
    private static function validateRequired( mixed $data, array $rules ): void
    {
        $require_markers = [ 'required', '!' ];
        
        foreach( $rules as $field => $rule ){
            count( array_intersect( $rule, $require_markers ) ) > 0
            && ! isset( $data[ $field ] )
                && throw new \InvalidArgumentException( "Field '$field' is missing" );
        }
    }
    
    /**
     * Check given value for expected type
     *
     * @param mixed  $value
     * @param string $field         Field name
     * @param string $required_type string representation of the expected type
     *
     * @return void
     */
    private static function validateType( mixed $value, string $field, string $required_type ): void
    {
        // Crutch for entities
        if( class_exists( $required_type ) ){
            $required_type = $required_type::$rules['id']['type'];
        }
        
        // Crutch. Cast value to expected type and compare it
        $casted_value = $value;
        settype( $casted_value, $required_type );
        if( $casted_value == $value ){
            return;
        }
        
        gettype( $value ) !== $required_type &&
            throw new \InvalidArgumentException( "Field '$field' should be a {$required_type}, " . gettype( $value ) . ' given.');
    }
    
    /**
     * Validates content by rule, which could be two types:
     *   - strict value
     *   - regexp
     *
     * @param mixed        $value
     * @param string       $field Field name
     * @param array|string $rule  Validation rule content for this specific field
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    private static function validateContent( mixed $value, string $field, array|string $rule ): void
    {
        // Direct match. Set of variant
        is_array( $rule ) &&
            ! in_array( $value, $rule ) &&
            throw new \InvalidArgumentException("Field $field content '$value' should be one of the set (" . implode( ', ', $rule ) . ')' );
        
        // Regular expression match
        // @todo make isRegExp function somewhere somehow someday
        is_string( $rule ) &&
            preg_match( '@^[/#+\@%({\[<].+[/#+\@%)}\]>]$@', $rule ) &&
            ! preg_match( $rule, $value ) &&
            throw new \InvalidArgumentException( "Field $field content '$value' is not match pattern, " . $rule );
    }
    
    /**
     * Validates length
     *
     * @param mixed     $value
     * @param string    $field
     * @param int|array $length_rule
     *
     * @return void
     */
    private static function validateLength( mixed $value, string $field, int|array $length_rule )
    {
        $length_rule  = (array)$length_rule;
        $value_length = strlen( $value );
        
        if( count( $length_rule ) === 1 ){
            [ $max ] = $length_rule;
        }
        
        if( count( $length_rule ) === 2 ){
            [ $min, $max ] = $length_rule;
        }
        
        isset( $min ) && $value_length < $min
            && throw new \InvalidArgumentException( "Field $field content '$value' is lower than length " . $min );
        
        isset( $max ) && $value_length > $max
            && throw new \InvalidArgumentException( "Field $field content '$value' is exceeded available length " . $max );
    }

}
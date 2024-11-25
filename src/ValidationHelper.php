<?php

namespace Safronik\Helpers;

use InvalidArgumentException;

class ValidationHelper
{
    /**
     * @param array   $data        Data to validate
     * @param array[] $rules Set of validation rules
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
     * @throws InvalidArgumentException
     */
    public static function validate( array $data, array $rules ): void
    {
        static::validateRequired( $data, $rules );

        foreach( $data as $field => $value ){
            if( isset( $rules[ $field ] ) ){
                ! empty( $rules[ $field ]['type'] )    && static::validateType(    $value, $field, $rules[ $field ]['type'] );
                ! empty( $rules[ $field ]['length'] )  && static::validateLength(  $value, $field, $rules[ $field ]['length'] );
                ! empty( $rules[ $field ]['content'] ) && static::validateContent( $value, $field, $rules[ $field ]['content'] );
            }
        }
    }

    /**
     * Throw an exception if there are redundant (not existent) fields in data
     *
     * @param array   $data
     * @param array[] $rules
     *
     * @return void
     */
    public static function validateRedundant( array $data, array $rules ): void
    {
        foreach( $data as $field => $value ){
            if( ! isset( $rules[ $field ] ) ){
                throw new InvalidArgumentException( "Data '$field' is redundant");
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
        foreach( $rules as $field => $rule ){
            ( in_array( 'required', $rule, true ) || in_array( '!', $rule, true ) )
                && ! isset( $data[ $field ] )
                && throw new InvalidArgumentException( "Field '$field' is missing" );
        }
    }

    /**
     * Check given value for expected type
     *
     * @param mixed $value
     * @param string $field Field name
     * @param string|array $required_types string representation of the expected type
     *
     * @return bool
     */
    private static function validateType( mixed $value, string $field, string|array $required_types ): bool
    {
        $required_types = str_contains($required_types, '|' )
            ? explode( '|', $required_types )
            : $required_types;

        // Recursion
        if( is_array( $required_types ) ){

            $validation_result = array_reduce(
                $required_types,
                static fn($result, $required_type) => $result || static::validateType( $value, $field, $required_types ),
                false
            );

            $validation_result
                || throw new InvalidArgumentException( "Field '$field' should be one of the types (" . implode( ', ', $required_types ) . "), " . gettype( $value ) . ' given.' );

            return true;
        }

        // Base case
        // Crutch for entities
        if( class_exists( $required_types ) ){
            return true;
        }

        // Crutch. Cast value to expected type and compare it
        $casted_value = $value;
        settype( $casted_value, $required_types );
        if( $casted_value == $value ){
            return true;
        }

        return gettype( $value ) === $required_types;
    }

    /**
     * Validates content by rule, which could be two types:
     *   - strict value
     *   - regexp
     *   - array of strict contents or regexps
     *
     * @param mixed        $value
     * @param string       $field Field name
     * @param array|string $rule  Validation rule content for this specific field
     *
     * @recirsive
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    private static function validateContent( mixed $value, string $field, array|string $rule ): bool
    {
        // Recursion
        if( is_array( $rule ) ){
            $validation_result = array_reduce(
                $rule,
                static fn( $result, $rule_item ) => $result || static::validateContent( $value, $field, $rule_item ),
                false
            );
            $validation_result
                || throw new InvalidArgumentException( "Field '$field' content '$value' is not match: (" . implode( ', ', $rule ) . "), $value given." );

            return true;
        }

        // Base case
        $rule_is_regexp = StringHelper::isRegexp( $rule );

        // Regular expression match
        if( $rule_is_regexp && preg_match( $rule, $value ) ){
            return true;
        }

        // Direct match
        if( ! $rule_is_regexp && $rule === $value ){
            return true;
        }

        return false;
    }

    /**
     * Validates length
     *
     * @param mixed            $value
     * @param string           $field
     * @param int|array|string $length_rule
     *
     * @return void
     */
    private static function validateLength( mixed $value, string $field, int|array|string $length_rule ): void
    {
        $length_rule  = (array)$length_rule;
        $value_length = is_array( $value )
            ? count( $value )
            : strlen( $value );

        if( count( $length_rule ) === 1 ){
            [ $max ] = $length_rule;
        }

        if( count( $length_rule ) === 2 ){
            [ $min, $max ] = $length_rule;
        }

        isset( $min ) && $value_length < $min
            && throw new InvalidArgumentException( "Field $field content '$value' is lower than length " . $min );

        isset( $max ) && $value_length > $max
            && throw new InvalidArgumentException( "Field $field content '$value' is exceeded available length " . $max );
    }

}
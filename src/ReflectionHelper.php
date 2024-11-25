<?php

namespace Safronik\Helpers;

class ReflectionHelper{
    
    /**
     * Returns an array of found classes in the directory corresponding conditions
     *
     * @param string    $directory           Directory to scan
     * @param string    $namespace           Directory namespace
     * @param string[]  $exclusions          Array of strings with exclusions. Full strict comparison
     * @param string    $filter              Positive filter (only string contains will be present in the result set)
     * @param bool      $recursive           Scan subdirectories
     * @param bool      $skip_infrastructure Skip classes starts with '_' (underscore symbol)
     * @param ?callable $filter_callback     Any callback you want to filter the result set
     *
     * @return array of classnames
     */
    public static function getClassesFromDirectory(
        string $directory,
        string $namespace,
        array  $exclusions = [],
        string $filter = '',
        bool   $recursive = false,
        bool   $skip_infrastructure = true,
        callable $filter_callback = null
    ): array
    {
        $found = [];
        
        $iterator = new \RecursiveDirectoryIterator($directory );
        $iterator = $recursive
            ? new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST)
            : $iterator;
        
        foreach( $iterator as $file ){
            
            if( ! $file->isFile() || $file->getExtension() !== 'php' ){
                continue;
            }
            
            $file_basename = $file->getBasename('.php');
            
            // Filter by name
            if( $filter && ! str_contains( $file_basename, $filter ) ){
                continue;
            }
            
            //Skip infrastructure, which starts with _
            if( $skip_infrastructure && str_starts_with( $file_basename, '_' ) ){
                continue;
            }
            
            // Skip exclusions
            if( in_array( $file_basename, $exclusions, true ) ){
                continue;
            }
            
            $classname = $namespace . $file->getPath() . '/' . $file->getBasename( '.php' );
            $classname = str_replace(
                [ $directory, '/' ],
                [ '', '\\' ],
                $classname
            );
            
            if( class_exists( $classname ) ){
                $found[] = $classname;
            }
            
        }
        
        return $filter_callback
            ? $filter_callback( $found )
            : $found;
    }
    
    /**
     * Filter everything except final classes from the given set
     *
     * @param $classes
     *
     * @return array
     * @throws \ReflectionException
     */
    public static function filterFinal( array $classes ): array
    {
        return array_filter(
            $classes,
            static fn( $class ) => ( new \ReflectionClass( $class) )->isFinal()
        );
    }
    
    /**
     * Filter everything except interfaces from the given set
     *
     * @param $classes
     *
     * @return array
     * @throws \ReflectionException
     */
    public static function filterInterfaces( $classes ): array
    {
        return array_filter(
            $classes,
            static fn( $class ) => ( new \ReflectionClass( $class) )->isInterface()
        );
    }
    
    /**
     * Filter everything except traits from the given set
     *
     * @param $classes
     *
     * @return array
     * @throws \ReflectionException
     */
    public static function filterTraits( $classes ): array
    {
        return array_filter(
            $classes,
            static fn( $class ) => ( new \ReflectionClass( $class) )->isTrait()
        );
    }

    
    /**
     * Check if the given class use specific trait
     *
     * @param string $classname
     * @param string $trait
     *
     * @return bool
     */
    public static function isClassUseTrait( string $classname, string $trait ): bool
    {
        return in_array( $trait, self::getClassTraits( $classname ), true );
    }
    
    /**
     * Gets class traits
     *
     * @param string $classname
     *
     * @return array
     */
    public static function getClassTraits( string $classname ): array
    {
        $classes_to_check = class_parents( $classname );
        $classes_to_check[] = $classname;
        
        $traits = [];
        foreach( $classes_to_check as $class_to_check ){
            $traits = array_merge( $traits, class_uses( $class_to_check ) ); // @todo refactor array_merge in a loop
        }
        
        return $traits;
    }
    
    /**
     * Filter everything except classes which are implement specific interface from the given set
     *
     * @param array  $classes
     * @param string $interface
     *
     * @return array
     */
    public static function filterClassesByInterface( array $classes, string $interface ): array
    {
        return array_filter(
            $classes,
            static fn( $service ) => in_array( $interface, class_implements( $service ), true )
        );
    }
    
    /**
     * Check if the class or object implements interface
     *
     * @param object|string $class
     * @param string        $interface
     *
     * @return bool
     */
    public static function isClassImplementsInterface( object|string $class, string $interface ): bool
    {
        return in_array(
            $interface,
            (array) class_implements( $class ),
            true
        );
    }
    
    /**
     * Returns a first-level namespace from full namespace
     *
     * @param object|string $class
     *
     * @return string
     */
    public static function getNamespaceFromClassname( object|string $class ): string
    {
        return preg_replace(
            '/^(.*)?(\\\\.*)$/',
            '$1',
            is_string( $class ) ? $class : $class::class
        );
    }
    
    public static function isTypeScalar( $type ): bool
    {
        return in_array( $type, [ 'integer', 'int', 'string', 'bool', 'float', 'mixed' ], true );
    }
}
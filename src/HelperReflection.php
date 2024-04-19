<?php

namespace Safronik\Core\Helpers;

class HelperReflection{
    
    /**
     * Returns an array of found classes in the directory corresponding conditions
     *
     * @param string    $directory  Directory to scan
     * @param string    $namespace  Namespace of the directory
     * @param string[]  $exclusions Array of strings with exclusions. Strict match
     * @param string    $filter
     * @param bool      $recursive  Recursive search
     * @param bool      $skip_infrastructure
     * @param ?callable $filter_callback
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
    )
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
    
    public static function filterFinalClasses( $classes ): array
    {
        return array_filter(
            $classes,
            static fn( $class ) => ( new \ReflectionClass( $class) )->isFinal()
        );
    }
    
    public static function getInterfacesFromDirectory( $classes ): array
    {
        return array_filter(
            $classes,
            static fn( $class ) => ( new \ReflectionClass( $class) )->isInterface()
        );
    }
    
    /**
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
    public static function isClassHasInterface( object|string $class, string $interface ): bool
    {
        return in_array(
            $interface,
            (array) class_implements( $class ),
            true
        );
    }
}
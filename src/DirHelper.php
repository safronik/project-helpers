<?php

namespace Safronik\Helpers;

class DirHelper
{
    
    /**
     * Checks if the directory exits
     *
     * @param $path
     *
     * @return bool
     */
    public static function isExist( $path ): bool
    {
        return file_exists( $path ) && ! is_file( $path ) && ! is_link( $path );
    }
    
    /**
     * Check is the directory is empty
     *
     * @param $path
     *
     * @return bool
     */
    public static function isEmpty( $path ): bool
    {
        $handle = opendir( $path );
        while( false !== ( $entry = readdir( $handle ) ) ){
            if( $entry !== '.' && $entry !== '..' ){
                closedir( $handle );
                
                return false;
            }
        }
        closedir( $handle );
        
        return true;
    }
    
    /**
     * Safely creates directory
     *
     * @param $path
     *
     * @return void
     */
    public static function create( $path ): void
    {
        ! mkdir( $path ) && ! is_dir( $path )
             && throw new \RuntimeException( "Directory '$path' was not created" );
    }
}
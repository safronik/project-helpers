<?php

namespace Safronik\Helpers;

class SurroundingHelper{
    
    /**
	 * Checks if the system is windows
	 *
	 * @return bool
	 */
	public static function isWindows(): bool
    {
		return str_contains( strtolower( php_uname( 's' ) ), 'windows' );
	}
    
    /**
     * Checks if the PHP-extension loaded
     *
     * @param $extension_name
     *
     * @return bool
     */
    public static function isExtensionLoaded( $extension_name )
    {
        return extension_loaded( $extension_name );
    }
}
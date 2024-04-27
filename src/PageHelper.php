<?php

namespace Safronik\Helpers;

class PageHelper{
    
    /**
     * Checks if the page has PHP error of any kind
     *
     * @param $string_page
     *
     * @return bool
     */
	public static function hasError( $string_page): bool
    {
		return (
            empty($string_page)
            || str_contains( $string_page, 'PHP Notice' )
            || str_contains( $string_page, 'PHP Warning' )
            || str_contains( $string_page, 'PHP Fatal error' )
            || str_contains( $string_page, 'PHP Parse error' )
			|| stripos($string_page, 'internal server error') !== false
			|| stripos($string_page, 'there has been a critical error on your website') !== false
		);
	}
}
<?php

namespace Safronik\Helpers;

class DNSHelper
{
    /**
     * Resolve DNS to IP
     *
     * @param string $host
     *
     * @return string
     */
	public static function resolveHost( string $host ): string
    {
		// Get DNS records about URL
		if(function_exists('dns_get_record')){
			$records = dns_get_record($host, DNS_A);
			if($records !== false){
				$out = $records[0]['ip'];
			}
		}
		
		// Another try if first failed
		if( ! isset( $out ) && function_exists('gethostbynamel')){
			$records = gethostbynamel($host);
			if($records !== false){
				$out = $records[0];
			}
		}
		
		return $out ?? '';
	}
}
<?php

namespace Safronik\Helpers;

use Safronik\Globals\Server;
use Safronik\Globals\Request;

class IPHelper{
    
    /**
	 * @var array Set of predefined private networks IPv4 and IPv6
	 */
	private static array $private_networks = array(
		'v4' => array(
			'10.0.0.0/8',
			'100.64.0.0/10',
			'172.16.0.0/12',
			'192.168.0.0/16',
			'127.0.0.1/32',
		),
		'v6' => array(
			'0:0:0:0:0:0:0:1/128', // localhost
			'0:0:0:0:0:0:a:1/128', // ::ffff:127.0.0.1
		),
	);
    
    /**
     * Getting arrays of IP (REMOTE_ADDR, X-Forwarded-For, X-Real-Ip, Cf_Connecting_Ip)
     *
     * @param array $ip_types Type of IP you want to receive
     * @param bool  $v4_only
     *
     * @return array|mixed|null
     */
    public static function get( array $ip_types = [ 'real', 'remote_addr', 'x_forwarded_for', 'x_real_ip', 'cloud_flare' ], bool $v4_only = true )
    {
        $ips     = array_flip( $ip_types ); // Result array with IPs
        $headers = Request::getHTTPHeaders();
        
        // REMOTE_ADDR
        if( isset( $ips['remote_addr'] ) ){
            $ip_type = self::validate( Server::get( 'REMOTE_ADDR' ) );
            if( $ip_type ){
                $ips['remote_addr'] = $ip_type === 'v6'
                    ? self::normalizeIPv6( Server::get( 'REMOTE_ADDR' ) )
                    : Server::get( 'REMOTE_ADDR' );
            }
        }
        
        // X-Forwarded-For
        if( isset( $ips['x_forwarded_for'] ) ){
            if( isset( $headers['X-Forwarded-For'] ) ){
                $tmp     = explode( ",", trim( $headers['X-Forwarded-For'] ) );
                $tmp     = trim( $tmp[0] );
                $ip_type = self::validate( $tmp );
                if( $ip_type ){
                    $ips['x_forwarded_for'] = $ip_type === 'v6' ? self::normalizeIPv6( $tmp ) : $tmp;
                }
            }
        }
        
        // X-Real-Ip
        if( isset( $ips['x_real_ip'] ) ){
            if( isset( $headers['X-Real-Ip'] ) ){
                $tmp     = explode( ",", trim( $headers['X-Real-Ip'] ) );
                $tmp     = trim( $tmp[0] );
                $ip_type = self::validate( $tmp );
                if( $ip_type ){
                    $ips['x_forwarded_for'] = $ip_type === 'v6'
                        ? self::normalizeIPv6( $tmp )
                        : $tmp;
                }
            }
        }
        
        // Cloud Flare
        if( isset( $ips['cloud_flare'] ) ){
            if( isset( $headers['CF-Connecting-IP'], $headers['CF-IPCountry'], $headers['CF-RAY'] ) || isset( $headers['Cf-Connecting-Ip'], $headers['Cf-Ipcountry'], $headers['Cf-Ray'] ) ){
                $tmp     = $headers['CF-Connecting-IP'] ?? $headers['Cf-Connecting-Ip'];
                $tmp     = str_contains( $tmp, ',' )
                    ? explode( ',', $tmp )
                    : (array)$tmp;
                $ip_type = self::validate( trim( $tmp[0] ) );
                if( $ip_type ){
                    $ips['real'] = $ip_type === 'v6'
                        ? self::normalizeIPv6( trim( $tmp[0] ) )
                        : trim( $tmp[0] );
                }
            }
        }
        
        // Getting real IP from REMOTE_ADDR or Cf_Connecting_Ip if set or from (X-Forwarded-For, X-Real-Ip) if REMOTE_ADDR is local.
        if( isset( $ips['real'] ) ){
            // Detect IP type
            $ip_type = self::validate( Server::get( 'REMOTE_ADDR' ) );
            if( $ip_type ){
                $ips['real'] = $ip_type == 'v6'
                    ? self::normalizeIPv6( Server::get( 'REMOTE_ADDR' ) )
                    : Server::get( 'REMOTE_ADDR' );
            }
            
            // Cloud Flare
            if( isset( $headers['CF-Connecting-IP'], $headers['CF-IPCountry'], $headers['CF-RAY'] ) || isset( $headers['Cf-Connecting-Ip'], $headers['Cf-Ipcountry'], $headers['Cf-Ray'] ) ){
                $tmp     = $headers['CF-Connecting-IP'] ?? $headers['Cf-Connecting-Ip'];
                $tmp     = str_contains( $tmp, ',' )
                    ? explode( ',', $tmp )
                    : (array)$tmp;
                $ip_type = self::validate( trim( $tmp[0] ) );
                if( $ip_type ){
                    $ips['real'] = $ip_type === 'v6'
                        ? self::normalizeIPv6( trim( $tmp[0] ) )
                        : trim( $tmp[0] );
                }
                // Sucury
            }elseif( isset( $headers['X-Sucuri-Clientip'], $headers['X-Sucuri-Country'] ) ){
                $ip_type = self::validate( $headers['X-Sucuri-Clientip'] );
                if( $ip_type ){
                    $ips['real'] = $ip_type === 'v6'
                        ? self::normalizeIPv6( $headers['X-Sucuri-Clientip'] )
                        : $headers['X-Sucuri-Clientip'];
                }
                // OVH
            }elseif( isset( $headers['X-Cdn-Any-Ip'], $headers['Remote-Ip'] ) ){
                $ip_type = self::validate( $headers['X-Cdn-Any-Ip'] );
                if( $ip_type ){
                    $ips['real'] = $ip_type === 'v6'
                        ? self::normalizeIPv6( $headers['X-Cdn-Any-Ip'] )
                        : $headers['X-Cdn-Any-Ip'];
                }
                // Incapsula proxy
            }elseif( isset( $headers['Incap-Client-Ip'] ) ){
                $ip_type = self::validate( $headers['Incap-Client-Ip'] );
                if( $ip_type ){
                    $ips['real'] = $ip_type === 'v6'
                        ? self::normalizeIPv6( $headers['Incap-Client-Ip'] )
                        : $headers['Incap-Client-Ip'];
                }
            }
            
            // Is private network
            if( $ip_type === false || ( $ip_type && ( self::isPrivateNetwork( $ips['real'], $ip_type ) || self::isIPInMask(
                            $ips['real'],
                            $_SERVER['SERVER_ADDR'] . '/24',
                            $ip_type
                        ) ) ) ){
                // X-Forwarded-For
                if( isset( $headers['X-Forwarded-For'] ) ){
                    $tmp     = explode( ',', trim( $headers['X-Forwarded-For'] ) );
                    $tmp     = trim( $tmp[0] );
                    $ip_type = self::validate( $tmp );
                    if( $ip_type ){
                        $ips['real'] = $ip_type === 'v6'
                            ? self::normalizeIPv6( $tmp )
                            : $tmp;
                    }
                    // X-Real-Ip
                }elseif( isset( $headers['X-Real-Ip'] ) ){
                    $tmp     = explode( ',', trim( $headers['X-Real-Ip'] ) );
                    $tmp     = trim( $tmp[0] );
                    $ip_type = self::validate( $tmp );
                    if( $ip_type ){
                        $ips['real'] = $ip_type === 'v6'
                            ? self::normalizeIPv6( $tmp )
                            : $tmp;
                    }
                }
            }
        }
        
        // Validating IPs
        $result = [];
        foreach( $ips as $key => $ip ){
            $ip_version = self::validate( $ip );
            if( $ip && ( ( $v4_only && $ip_version === 'v4' ) || ! $v4_only ) ){
                $result[ $key ] = $ip;
            }
        }
        
        $result = array_unique( $result );
        
        return count( $result ) > 1
            ? $result
            : ( reset( $result ) !== false
                ? reset( $result )
                : null );
    }
    
    public static function getDecimal( ?string $ip = null ): int
    {
        $ip = $ip ?: self::get();
        
        return ip2long( $ip ) ?: 0;
    }
    
    /**
	 * Checks if the IP is in private range
	 *
	 * @param string $ip
	 * @param string $ip_type
	 *
	 * @return bool
	 */
	public static function isPrivateNetwork( string $ip, string $ip_type = 'v4'): bool
    {
		return self::isIPInMask($ip, self::$private_networks[$ip_type], $ip_type);
	}
    
    /**
     * Check if the IP belong to mask.  Recursive.
     * Octet by octet for IPv4
     * Hextet by hextet for IPv6
     *
     * @param string       $ip
     * @param string|array $cidr       work to compare with
     * @param string       $ip_type    IPv6 or IPv4
     * @param int          $xtet_count Recursive counter. Determs current part of address to check.
     *
     * @return bool
     */
    public static function isIPInMask( string $ip, string|array $cidr, string $ip_type = 'v4', int $xtet_count = 0 ): bool
    {
        if( is_array( $cidr ) ){
            foreach( $cidr as $curr_mask ){
                if( self::isIPInMask( $ip, $curr_mask, $ip_type ) ){
                    return true;
                }
            }
            
            return false;
        }
        
        if( ! self::validate( $ip ) || ! self::cidrValidate( $cidr ) ){
            return false;
        }
        
        $xtet_base = ( $ip_type === 'v4' ) ? 8 : 16;
        
        // Calculate mask
        $exploded = explode( '/', $cidr );
        $net_ip   = $exploded[0];
        $mask     = (int)$exploded[1];
        
        // Exit condition
        $xtet_end = ceil( $mask / $xtet_base );
        if( $xtet_count == $xtet_end ){
            return true;
        }
        
        // Length of bits for comparison
        $mask = $mask - $xtet_base * $xtet_count >= $xtet_base ? $xtet_base : $mask - $xtet_base * $xtet_count;
        
        // Explode by octets/hextets from IP and Net
        $net_ip_xtets = explode( $ip_type === 'v4' ? '.' : ':', $net_ip );
        $ip_xtets     = explode( $ip_type === 'v4' ? '.' : ':', $ip );
        
        // Standartizing. Getting current octets/hextets. Adding leading zeros.
        $net_xtet = str_pad(
            decbin(
                ( $ip_type === 'v4' && (int)$net_ip_xtets[ $xtet_count ] ) ? $net_ip_xtets[ $xtet_count ] : @hexdec(
                    $net_ip_xtets[ $xtet_count ]
                )
            ),
            $xtet_base,
            0,
            STR_PAD_LEFT
        );
        $ip_xtet  = str_pad(
            decbin(
                ( $ip_type === 'v4' && (int)$ip_xtets[ $xtet_count ] ) ? $ip_xtets[ $xtet_count ] : @hexdec(
                    $ip_xtets[ $xtet_count ]
                )
            ),
            $xtet_base,
            0,
            STR_PAD_LEFT
        );
        
        // Comparing bit by bit
        for( $i = 0, $result = true; $mask != 0; $mask--, $i++ ){
            if( $ip_xtet[ $i ] != $net_xtet[ $i ] ){
                $result = false;
                break;
            }
        }
        
        // Recursion. Moving to next octet/hextet.
        if( $result ){
            $result = self::isIPInMask( $ip, $cidr, $ip_type, $xtet_count + 1 );
        }
        
        return $result;
    }
    
    /**
     * Converts long mask like 4294967295 to number like 32
     *
     * @param int $long_mask
     *
     * @return int
     */
    public static function convertLongMaskToNumber( int $long_mask ): int
    {
        $num_mask = strpos( decbin( $long_mask ), '0' );
        
        return $num_mask === false ? 32 : $num_mask;
    }
	
	/**
	 * Validating IPv4, IPv6
	 *
	 * @param string $ip
	 *
	 * @return string|bool
	 */
    public static function validate( string $ip ): bool|string
    {
        // NULL || FALSE || '' || so on...
        if( ! $ip ){
            return false;
        }
        
        // IPv4
        if( $ip !== '0.0.0.0' && filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ){
            return 'v4';
        }
        
        // IPv6
        if( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) && self::reduceIPv6( $ip ) !== '0::0' ){
            return 'v6';
        }
        
        return false; // Unknown
    }
    
    /**
     * Validate CIDR
     *
     * @param string $cidr expects string like 1.1.1.1/32
     *
     * @return bool
     */
    public static function cidrValidate( string $cidr ): bool
    {
        [ $ip, $mask ] = explode( '/', $cidr );
        
        return isset( $ip, $mask )
               && self::validate( $ip )
               && preg_match( '@\d{1,2}@', $mask );
    }
	
	/**
	 * Expand IPv6
	 *
	 * @param string $ip
	 *
	 * @return string IPv6
	 */
    public static function normalizeIPv6( string $ip ): string
    {
        $ip = trim( $ip );
        
        // Searching for ::ffff:xx.xx.xx.xx patterns and turn it to IPv6
        if( preg_match( '/^::ffff:(\d{1,3}\.?){4}$/', $ip ) ){
            $ip = dechex( sprintf( "%u", ip2long( substr( $ip, 7 ) ) ) );
            $ip = '0:0:0:0:0:0:' . ( strlen( $ip ) > 4 ? substr( 'abcde', 0, -4 ) : '0' ) . ':' . substr( $ip, -4, 4 );
            // Normalizing hextets number
        }elseif( strpos( $ip, '::' ) !== false ){
            $ip = str_replace( '::', str_repeat( ':0', 8 - substr_count( $ip, ':' ) ) . ':', $ip );
            $ip = str_starts_with( $ip, ':' )
                ? '0' . $ip
                : $ip;
            $ip = str_starts_with( strrev( $ip ), ':' )
                ? $ip . '0'
                : $ip;
        }
        
        // Simplifying hextets
        if( preg_match( '/:0(?=[a-z0-9]+)/', $ip ) ){
            $ip = preg_replace( '/:0(?=[a-z0-9]+)/', ':', strtolower( $ip ) );
            $ip = self::normalizeIPv6( $ip );
        }
        
        return $ip;
    }
	
	/**
	 * Reduce IPv6
	 *
	 * @param string $ip
	 *
	 * @return string IPv6
	 */
    public static function reduceIPv6( string $ip ): string
    {
        if( str_contains( $ip, ':' ) ){
            $ip = preg_replace( '/:0{1,4}/', ':', $ip );
            $ip = preg_replace( '/:{2,}/', '::', $ip );
            $ip = str_starts_with( $ip, '0' )
                ? substr( $ip, 1 )
                : $ip;
        }
        
        return $ip;
    }
    
    /**
     * Get URL form IP
     *
     * @param $ip
     *
     * @return string
     */
    public static function resolveIP( $ip ): string
    {
        if( self::validate( $ip ) ){
            $url = gethostbyaddr( $ip );
            if( $url ){
                return $url;
            }
        }
        
        return $ip;
    }
}
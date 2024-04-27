<?php

namespace Safronik\Helpers;

class HTTPHelper{
 
	/**
	 * Function sends raw http request
	 *
	 * May use 4 presets(combining possible):
	 * get_code - getting only HTTP response code
	 * async    - async requests
	 * get      - GET-request
	 * ssl      - use SSL
	 *
	 * @param string       $url     URL
	 * @param array        $data    POST|GET indexed array with data to send
	 * @param string|array $presets String or Array with presets: get_code, async, get, ssl, dont_split_to_array
	 * @param array        $opts    Optional option for CURL connection
	 *
	 * @return array|bool (array || array('error' => true))
	 */
	static public function http__request($url, $data = array(), $presets = null, $opts = array())
	{
		// For debug purposes
		if( defined( 'CLEANTALK_DEBUG' ) && CLEANTALK_DEBUG ){
			global $apbct_debug;
			$apbct_debug['data'] = $data;
		}
		
		// Preparing presets
		$presets = is_array($presets) ? $presets : explode(' ', $presets);
		$curl_only = in_array( 'async', $presets ) ||
		             in_array( 'dont_follow_redirects', $presets ) ||
		             in_array( 'ssl', $presets ) ||
		             in_array( 'split_to_array', $presets )
			? true : false;
		
		if(function_exists('curl_init')){
			
			$ch = curl_init();
			
			// Set data if it's not empty
			if(!empty($data)){
				// If $data scalar converting it to array
				$opts[CURLOPT_POSTFIELDS] = $data;
			}
			
			// Merging OBLIGATORY options with GIVEN options
			// Using POST method by default
			$opts = static::array_merge__save_numeric_keys(
				array(
					CURLOPT_URL => $url,
					CURLOPT_TIMEOUT => 5,
					CURLOPT_FORBID_REUSE => true,
					CURLOPT_POST => true,
					CURLOPT_SSL_VERIFYPEER => false,
					CURLOPT_SSL_VERIFYHOST => 0,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_MAXREDIRS => 5,
					CURLOPT_USERAGENT => static::http__user_agent() . '; ' . ( ! empty( Server::get( 'SERVER_NAME' ) ) ? Server::get( 'SERVER_NAME' ) : 'UNKNOWN_HOST' ),
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_0, // see http://stackoverflow.com/a/23322368
					CURLOPT_RETURNTRANSFER => true, // receive server response ...
					CURLOPT_HTTPHEADER => array('Expect:'), // Fix for large data and old servers http://php.net/manual/ru/function.curl-setopt.php#82418
				),
				$opts
			);

			foreach($presets as $preset){

				switch($preset){
					
					// Do not follow redirects
					case 'dont_follow_redirects':
						$opts[CURLOPT_FOLLOWLOCATION] = false;
						$opts[CURLOPT_MAXREDIRS] = 0;
						break;
					
					// Get headers only
					case 'get_code':
						$opts[CURLOPT_HEADER] = true;
						$opts[CURLOPT_NOBODY] = true;
						break;
					
					// Make a request, don't wait for an answer
					case 'async':
						$opts[CURLOPT_CONNECTTIMEOUT_MS] = 1000;
						$opts[CURLOPT_TIMEOUT_MS] = 500;
						break;
					
					case 'get':
						$opts[CURLOPT_URL] .= $data ? '?' . str_replace( "&amp;", "&", http_build_query( $data ) ) : '';
						$opts[CURLOPT_CUSTOMREQUEST] = 'GET';
						$opts[CURLOPT_POST] = false;
						$opts[CURLOPT_POSTFIELDS] = null;
						break;
					
					case 'ssl':
						$opts[CURLOPT_SSL_VERIFYPEER] = true;
						$opts[CURLOPT_SSL_VERIFYHOST] = 2;
						if(defined('CLEANTALK_CASERT_PATH') && CLEANTALK_CASERT_PATH)
							$opts[CURLOPT_CAINFO] = CLEANTALK_CASERT_PATH;
						break;
					
					case 'get_file':
						$opts[CURLOPT_CUSTOMREQUEST] = 'GET';
						$opts[CURLOPT_POST] = false;
						$opts[CURLOPT_POSTFIELDS] = null;
						$opts[CURLOPT_HEADER] = false;
						break;
                    case 'http_20':
						$opts[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_2_0;
						break;
						
					default:
						
						break;
				}
			}

			curl_setopt_array($ch, $opts);
			$result = curl_exec($ch);
			
			// RETURN if async request
			if(in_array('async', $presets))
				return true;
			
			if( ! $result ){
				
				$out = array( 'error' => curl_error( $ch ) );
				
			}elseif( strpos( $result, 'error_message' ) && ! in_array( 'get_file', $presets ) ){
				
				$out_tmp = json_decode( $result, true);
				$out = array(
					'error' => $out_tmp['error_message'],
					'error_original' => $out_tmp,
				);
				
			}else{
				
				// Split to array by lines if such preset given
				if( strpos( $result, PHP_EOL ) !== false && in_array( 'split_to_array', $presets ) )
					$result = explode(PHP_EOL, $result);
				
				// Get code crossPHP method
				if(in_array('get_code', $presets)){
					$curl_info = curl_getinfo($ch);
					$result = $curl_info['http_code'];
				}
				
				$out = $result;
				
			}
			
			
			curl_close($ch);
			
		// Curl not installed. Trying file_get_contents()
		}elseif( ini_get( 'allow_url_fopen' ) && ! $curl_only ){
			
			// Trying to get code via get_headers()
			if( in_array( 'get_code', $presets ) ){
				$headers = get_headers( $url );
				$result  = (int) preg_replace( '/.*(\d{3}).*/', '$1', $headers[0] );
				
			// Making common request
			}else{
				$opts    = array(
					'http' => array(
						'method'  => in_array( 'get', $presets ) ? 'GET' : 'POST',
						'timeout' => 5,
						'content' => str_replace( "&amp;", "&", http_build_query( $data ) ),
					),
				);
				$context = stream_context_create( $opts );
				$result  = @file_get_contents( $url, 0, $context );
			}
			
			$out = $result === false
				? 'FAILED_TO_USE_FILE_GET_CONTENTS'
				: $result;
			
		}else
			$out = array('error' => 'CURL not installed and allow_url_fopen is disabled');
		
		return $out;
	}

	/**
	 * Wrapper for http_request
	 * Requesting data via HTTP request with GET method
	 *
	 * @param string $url
	 *
	 * @return array|mixed|string
	 */
	static public function http__request__get_content( $url ){
		return static::http__request( $url, array(), 'get dont_split_to_array');
	}

	/**
	 * Wrapper for http_request
	 * Requesting HTTP response code for $url
	 *
	 * @param string $url
	 *
	 * @return array|mixed|string
	 */
	static public function http__request__get_response_code( $url ){
		return static::http__request( $url, array(), 'get_code');
	}

	/**
	 * Wrapper for http_request
	 * Requesting HTTP response code for $url
	 *
	 * @param string $url
	 *
	 * @return array|mixed|string
	 */
	static public function http__get_data_from_remote_gz( $url ){

		$data = false;
		$res = static::http__request( $url, array(), 'get_code' );
		// @todo fix API. Should return 200 for files.
		if ( $res === 200 || $res === 501 ) { // Check if it's there
			$result = static::http__request__get_content( $url );
			if ( empty( $result['error'] ) ){
				if(function_exists('gzdecode')) {
					$data = @gzdecode( $result );
					if ( $data !== false ){
						return $data;
					}else
						Err::add( 'Can not unpack datafile');
				}else
					Err::add( 'Function gzdecode not exists. Please update your PHP at least to version 5.4', $result['error'] );
			}else
				Err::add( 'Getting datafile', $result['error'] );
		}else
			Err::add( 'Bad HTTP response from file location' );

		return $data;
	}
    
    /**
     * Wrapper for http_request
     * Requesting HTTP response code for $url
     *
     * @param string $path
     *
     * @return array|mixed|string
     */
    public static function get_data_from_local_gz( $path ){
        
        if ( file_exists( $path ) ) {
            
            if ( is_readable( $path ) ) {
                
                $data = file_get_contents( $path );
                
                if ( $data !== false ){
                    
                    if( static::get_mime_type( $data, 'application/x-gzip' ) ){
                        
                        if( function_exists('gzdecode') ) {
                            
                            $data = gzdecode( $data );
                            
                            if ( $data !== false ){
                                return $data;
                            }else
                                return array( 'error' => 'Can not unpack datafile');
                            
                        }else
                            return array( 'error' => 'Function gzdecode not exists. Please update your PHP at least to version 5.4 ' . $data['error'] );
                    }else
                        return array('error' => 'WRONG_REMOTE_FILE_MIME_TYPE');
                }else
                    return array( 'error' => 'Couldn\'t get data' );
            }else
                return array( 'error' => 'File is not readable: ' . $path );
        }else
            return array( 'error' => 'File doesn\'t exists: ' . $path );
    }
    
    public static function http__download_remote_file( $url, $tmp_folder ){
		
		$result = self::http__request( $url, array(), 'get_file' );
		
		if( empty( $result['error'] ) ){
			
			$file_name = basename( $url );
			
			if( ! is_dir( $tmp_folder ) )
				mkdir( $tmp_folder );
			
			if( ! file_exists( $tmp_folder . $file_name ) ){
				file_put_contents( $tmp_folder . $file_name, $result );
				return $tmp_folder . $file_name;
			}else
				return array( 'error' => 'File already downloaded');
		}else
			return $result;
	}
    
    /**
     * Do multi curl requests.
     *
     * @param array $urls      Array of URLs to requests
     * @param string $write_to Path to the writing files dir
     *
     * @return array
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function http__download_remote_file__multi( $urls, $write_to = '' )
    {
        if( ! is_array( $urls ) || empty( $urls ) ) {
            return array( 'error' => 'CURL_MULTI: Parameter is not an array.' );
        }
        
        foreach( $urls as $url ) {
            if( ! is_string( $url ) ) {
                return array( 'error' => 'CURL_MULTI: Parameter elements must be strings.' );
            }
        }
        
        $urls_count = count( $urls );
        $curl_arr = array();
        $master = curl_multi_init();
        
        for($i = 0; $i < $urls_count; $i++)
        {
            $url =$urls[$i];
            $curl_arr[$i] = curl_init($url);
            $opts = array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT_MS => 10000,
                CURLOPT_FORBID_REUSE => true,
                CURLOPT_USERAGENT => self::DEFAULT_USER_AGENT . '; ' . ( isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : 'UNKNOWN_HOST' ),
                CURLOPT_HTTPHEADER => array('Expect:'), // Fix for large data and old servers http://php.net/manual/ru/function.curl-setopt.php#82418
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
            );
            curl_setopt_array($curl_arr[$i], $opts);
            curl_multi_add_handle($master, $curl_arr[$i]);
        }
        
        do {
            curl_multi_exec($master,$running);
            // @ToDo place here sleep(500) to avoid possible CPU overusing
        } while($running > 0);
        
        $results = array();
        
        for($i = 0; $i < $urls_count; $i++)
        {
            $info = curl_getinfo($curl_arr[$i], CURLINFO_HTTP_CODE);
            if( 200 == $info ) {
                if( ! empty( $write_to ) && is_dir( $write_to ) && is_writable( $write_to ) ) {
                    $results[] = file_put_contents(  $write_to . DS . self::getFilenameFromUrl( $urls[$i] ), curl_multi_getcontent( $curl_arr[$i] ) )
                        ? 'success'
                        : 'error';
                } else {
                    $results[] = curl_multi_getcontent( $curl_arr[$i] );
                }
                
            } else {
                $results[] = 'error';
            }
        }
        return $results;
    }
}
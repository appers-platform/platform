<?php
class httpq
{
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';

    public static function request(
		$url,
		$params = null,
		$method = self::GET,
		array $headers = [],
		$timeout = 10,
		$return_with_headers = false,
		$follow_location = true,
		array $cookie = [],
		array $curl_options = []
	)
    {
        $ch = curl_init();

        if ($params) {
            $params = (is_array($params) ? http_build_query($params) : $params);

            if (self::GET == $method) {
                $url .= (strpos($url, '?') ? '&' : '?').$params;
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                curl_setopt($ch, CURLOPT_POST, 1);
            }
        }

        if ($headers) {
            $prepared_headers = [];
            foreach ($headers as $header => $value) {
                $prepared_headers[] = (is_numeric($header) ? $value : $header.': '.$value);
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $prepared_headers);
        }

		if($cookie) {
			$cookie_str = '';
			foreach ($cookie as $name => $value) {
				if ($cookie_str) {
					$cookie_str  .= ';';
				}
				$cookie_str .= $name . '=' . addslashes($value);
			}
			curl_setopt ($ch, CURLOPT_COOKIE, $cookie_str );
		}

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FAILONERROR, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, (int) $follow_location);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);

		if($return_with_headers) {
			curl_setopt($ch, CURLOPT_HEADER, 1);
		}

		foreach ($curl_options as $key => $value) {
			curl_setopt($ch, $key, $value);
		}

        $result = curl_exec($ch);
        $error = curl_error($ch);
        $errorno = curl_errno($ch);
		curl_close($ch);

        if (!$result && $error) {
            throw new Exception($error, $errorno);
        }

		if($return_with_headers) {
			$body = '';
			$headers = '';
			while ( strpos( $result, 'HTTP' ) === 0 ) {
				$header_divider = strpos( $result, "\r\n\r\n" );
				$body = substr( $result, $header_divider + 4 );
				$headers = self::parseHeaders( substr( $result, 0, $header_divider ) );
				$result = $body;
			}

			$result = [
				'body' => $body,
				'headers' => $headers,
			];
		}

        return $result;
    }

	static protected function parseHeaders( $http ) {
		$headers = array();
		if ( $http_lines = explode( "\r\n", $http ) ) {
			foreach ( $http_lines as $line ) {
				$header[0] = substr( $line, 0, strpos( $line, ':' ) );
				$header[1] = substr( $line, strpos( $line, ':' ) + 1 );
				if ( $headers[$header[0]] ) {
					$headers[$header[0]] = (array) $headers[$header[0]];
					$headers[$header[0]][] = ltrim($header[1]);
				} else {
					$headers[$header[0]] = ltrim($header[1]);
				}
			}
		}
		return $headers;
	}

    public static function GET($url, $params = null, array $headers = [], array $curl_options = [], $timeout = 10, $retry = false) {
		try {
			return self::request($url, $params, self::GET, $headers, $timeout, false, true, [], $curl_options);
		} catch (Exception $e) {
			if(!$retry) throw $e;
			return self::request($url, $params, self::GET, $headers, $timeout, false, true, [], $curl_options);
		}
    }

    public static function POST($url, $params = null, array $headers = [], array $curl_options = [], $timeout = 10, $retry = false) {
		try {
        	return self::request($url, $params, self::POST, $headers, $timeout, false, true, [], $curl_options);
		} catch (Exception $e) {
			if(!$retry) throw $e;
			return self::request($url, $params, self::POST, $headers, $timeout, false, true, [], $curl_options);
		}
    }

    public static function PUT($url, $params = null, array $headers = [], array $curl_options = [], $timeout = 10, $retry = false) {
		try {
        	return self::request($url, $params, self::PUT, $headers, $timeout, false, true, [], $curl_options);
		} catch (Exception $e) {
			if(!$retry) throw $e;
			return self::request($url, $params, self::PUT, $headers, $timeout, false, true, [], $curl_options);
		}
    }
}
<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Contains the most low-level helpers methods in Kohana:
 *
 * - Environment initialization
 * - Locating files within the cascading filesystem
 * - Auto-loading and transparent extension of classes
 * - Variable and path debugging
 *
 * @package    Kohana
 * @category   HTTP
 * @author     Kohana Team
 * @copyright  (c) 2008-2014 Kohana Team
 * @license    http://kohanaframework.org/license
 */
abstract class Kohana_HTTP {

	/**
	 * @var  string  The default protocol to use if it cannot be detected
	 */
	public static $protocol = 'HTTP/1.1';

	/**
	 * @var  array  HTTP request headers, uses in [HTTP::request_headers()]
	 */
	protected static $_request_header = array();

	/**
	 * Issues a HTTP redirect.
	 *
	 * @param   string  $uri   URI to redirect to
	 * @param   int     $code  HTTP status code to use for the redirect
	 * @return  void
	 * @throws  HTTP_Exception
	 * @throws  Kohana_Exception
	 */
	public static function redirect($uri = '', $code = 302)
	{
		$e = HTTP_Exception::factory($code);

		if ( ! $e instanceof HTTP_Exception_Redirect)
			throw new Kohana_Exception("Invalid redirect code ':code'", array(':code' => $code));

		throw $e->location($uri);
	}

	/**
	 * Checks the browser cache to see the response needs to be returned,
	 * execution will halt and a "304 Not Modified" will be sent if the
	 * browser cache is up to date.
	 * See [HTTP ETag](http://wikipedia.org/wiki/HTTP_ETag) for more information.
	 *
	 * @param   Request      $request   Request
	 * @param   Response     $response  Response
	 * @param   string|NULL  $etag      Resource ETag
	 * @return  Response
	 * @throws  HTTP_Exception_304
	 */
	public static function check_cache(Request $request, Response $response, $etag = NULL)
	{
		// Generate an ETag if necessary
		if ($etag === NULL)
		{
			$etag = $response->generate_etag();
		}

		// Set the ETag header
		$response->headers('etag', $etag);

		// Add the "Cache-Control" header if it's not already set.
		// This allows ETags to be used with max-age, etc.
		if ($response->headers('cache-control'))
		{
			$response->headers('cache-control', $response->headers('cache-control').', must-revalidate');
		}
		else
		{
			$response->headers('cache-control', 'must-revalidate');
		}

		// Check if we have a matching etag
		if ($request->headers('if-none-match') === $etag)
		{
			// No need to send data again
			throw HTTP_Exception::factory(304)->headers('etag', $etag);
		}

		return $response;
	}

	/**
	 * Parses a HTTP header string into an associative array.
	 *
	 * @param   string  $header_string  Header string to parse
	 * @return  HTTP_Header
	 */
	public static function parse_header_string($header_string)
	{
		// If the PECL HTTP extension is loaded
		if (extension_loaded('http'))
		{
			// Use the fast method to parse header string
			return new HTTP_Header(http_parse_headers($header_string));
		}

		// Otherwise we use the slower PHP parsing
		$headers = array();

		// Match all HTTP headers
		if (preg_match_all('/(\w[^\s:]*):[ ]*([^\r\n]*(?:\r\n[ \t][^\r\n]*)*)/', $header_string, $matches))
		{
			// Parse each matched header
			foreach ($matches[0] as $key => $value)
			{
				// If the header has not already been set
				if ( ! isset($headers[$matches[1][$key]]))
				{
					// Apply the header directly
					$headers[$matches[1][$key]] = $matches[2][$key];
				}
				// Otherwise there is an existing entry
				else
				{
					// If the entry is an array
					if (is_array($headers[$matches[1][$key]]))
					{
						// Apply the new entry to the array
						$headers[$matches[1][$key]][] = $matches[2][$key];
					}
					// Otherwise create a new array with the entries
					else
					{
						$headers[$matches[1][$key]] = array(
							$headers[$matches[1][$key]],
							$matches[2][$key],
						);
					}
				}
			}
		}

		// Return the headers
		return new HTTP_Header($headers);
	}

	/**
	 * Parses the the HTTP request headers and returns an array containing
	 * key value pairs. This method is slow, but provides an accurate
	 * representation of the HTTP request.
	 *
	 *      // Get http headers into the request
	 *      $request->headers = HTTP::request_headers();
	 *
	 * @return  HTTP_Header
	 */
	public static function request_headers()
	{
		// If headers already parsed
		if ( ! empty(HTTP::$_request_headers))
		{
			return new HTTP_Header(HTTP::$_request_headers);
		}

		// If running on Apache server
		if (function_exists('apache_request_headers'))
		{
			HTTP::$_request_headers = apache_request_headers();
		}
		// If the PECL HTTP tools are installed
		elseif (extension_loaded('http'))
		{
			HTTP::$_request_headers = http_get_request_headers();
		}
		// Native (slow) parsing
		else 
		{
			// Parse the content type
			if ( ! empty($_SERVER['CONTENT_TYPE']))
			{
				HTTP::$_request_headers['content-type'] = $_SERVER['CONTENT_TYPE'];
			}

			// Parse the content length
			if ( ! empty($_SERVER['CONTENT_LENGTH']))
			{
				HTTP::$_request_headers['content-length'] = $_SERVER['CONTENT_LENGTH'];
			}

			foreach ($_SERVER as $key => $value)
			{
				if (strpos($key, 'HTTP_') === 0)
				{
					// It's a dirty hack to ensure HTTP_X_FOO_BAR becomes x-foo-bar
					$key = str_replace('_', '-', substr($key, 5));
					$key = strtolower($key);

					HTTP::$_request_headers[$key] = $value;
				}
			}
		}

		return new HTTP_Header(HTTP::$_request_headers);
	}

	/**
	 * Processes an array of key value pairs and encodes
	 * the values to meet [RFC 3986](http://faqs.org/rfcs/rfc3986).
	 *
	 * @param   array|object  $params  An array or traversable object, contain parameters
	 * @return  string
	 */
	public static function www_form_urlencode($params = array())
	{
		if (empty($params))
			return '';

		if (version_compare(PHP_VERSION, '5.4.0') >= 0)
		{
			// The enc_type parameter was added in PHP 5.4
			return http_build_query($params, '', '&', PHP_QUERY_RFC3986);
		}

		foreach ($params as $key => $value)
		{
			$params[$key] = $key.'='.rawurlencode($value);
		}

		return implode('&', $params);
	}

}

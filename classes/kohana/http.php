<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Contains the most low-level helpers methods in Kohana:
 *
 * - Environment initialization
 * - Locating files within the cascading filesystem
 * - Auto-loading and transparent extension of classes
 * - Variable and path debugging
 *
 * @package    Kohana
 * @category   Http
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
abstract class Kohana_Http {

	/**
	 * @var  The default protocol to use if it cannot be detected
	 */
	public static $protocal = 'HTTP/1.1';

	/**
	 * Parses a HTTP header string into an associative array
	 *
	 * @param   string   header string to parse
	 * @return  [Kohana_Http_Header]
	 */
	public static function parse_header_string($header_string)
	{
		// If the PECL Http extension is loaded
		if (extension_loaded('http'))
		{
			// Use the fast method to parse header string
			return new Http_Header(http_parse_headers($header_string));
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
		return new Http_Header($headers);
	}

	/**
	 * Parses the the HTTP request headers and returns an array containing
	 * key value pairs. This method is slow, but provides an accurate
	 * representation of the HTTP request.
	 * 
	 *      // Get http headers into the request
	 *      $request->headers = Http::request_headers();
	 *
	 * @return  [Kohana_Http_Header]
	 * @since   3.1.0
	 */
	public static function request_headers()
	{
		// If running on apache server
		if (function_exists('apache_request_headers'))
		{
			// Return the much faster method
			return new Http_Header(apache_request_headers());
		}
		// If the PECL HTTP tools are installed
		else if (extension_loaded('http'))
		{
			// Return the much faster method
			return new Http_Header(http_get_request_headers());
		}

		// Setup the output
		$headers = array();

		// Parse the content type
		if( ! empty($_SERVER['CONTENT_TYPE']))
		{
			$headers['content-type'] = $_SERVER['CONTENT_TYPE'];
		}

		// Parse the content length
		if ( ! empty($_SERVER['CONTENT_LENGTH']))
		{
			$headers['content-length'] = $_SERVER['CONTENT_LENGTH'];
		}

		foreach ($_SERVER as $key => $value)
		{
			// If there is no HTTP header here, skip
			if (strpos($key, 'HTTP_') !== 0)
			{
				continue;
			}

			// This is a dirty hack to ensure HTTP_X_FOO_BAR becomes x-foo-bar
			$headers[str_replace(array('HTTP_', '_'), array('', '-'), $key)] = $value;
		}

		return new Http_Header($headers);
	}

	/**
	 * This is a static class
	 */
	final private function __construct() {}

} // End Kohana_Http
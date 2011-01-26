<?php defined('SYSPATH') or die('No direct script access.');
/**
 * URL helper class.
 *
 * @package    Kohana
 * @category   Helpers
 * @author     Kohana Team
 * @copyright  (c) 2007-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_URL {

	/**
	 * Gets the base URL to the application. To include the current protocol,
	 * use TRUE. To specify a protocol, provide the protocol as a string.
	 * If a protocol is used, a complete URL will be generated using the
	 * `$_SERVER['HTTP_HOST']` variable.
	 *
	 *     // Absolute relative, no host or protocol
	 *     echo URL::base();
	 *
	 *     // Complete relative, with host and protocol
	 *     echo URL::base(TRUE, TRUE);
	 *
	 *     // Complete relative, with host and "https" protocol
	 *     echo URL::base(TRUE, 'https');
	 *
	 * @param   boolean  add index file to URL?
	 * @param   mixed    protocol string or boolean, add protocol and domain?
	 * @return  string
	 * @uses    Kohana::$index_file
	 * @uses    Request::$protocol
	 */
	public static function base($index = FALSE, $protocol = FALSE)
	{
		// Start with the configured base URL
		$base_url = Kohana::$base_url;

		if ($protocol === TRUE)
		{
			// Use the current protocol
			$protocol = Request::$protocol;
		}
		elseif ($protocol === FALSE AND $scheme = parse_url($base_url, PHP_URL_SCHEME))
		{
			// Use the configured default protocol
			$protocol = $scheme;
		}

		if ($index === TRUE AND ! empty(Kohana::$index_file))
		{
			// Add the index file to the URL
			$base_url .= Kohana::$index_file.'/';
		}

		if (is_string($protocol))
		{
			if ($port = parse_url($base_url, PHP_URL_PORT))
			{
				// Found a port, make it usable for the URL
				$port = ':'.$port;
			}

			if ($domain = parse_url($base_url, PHP_URL_HOST))
			{
				// Remove everything but the path from the URL
				$base_url = parse_url($base_url, PHP_URL_PATH);
			}
			else
			{
				// Attempt to use HTTP_HOST and fallback to SERVER_NAME
				$domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
			}

			// Add the protocol and domain to the base URL
			$base_url = $protocol.'://'.$domain.$port.$base_url;
		}

		return $base_url;
	}

	/**
	 * Fetches an absolute site URL based on a URI segment.
	 *
	 *     echo URL::site('foo/bar');
	 *
	 * @param   string  site URI to convert
	 * @param   mixed   protocol string or boolean, add protocol and domain?
	 * @return  string
	 * @uses    URL::base
	 */
	public static function site($uri = '', $protocol = FALSE)
	{
		// Chop off possible scheme, host, port, user and pass parts
		$path = preg_replace('~^[-a-z0-9+.]++://[^/]++/?~', '', trim($uri, '/'));

		if ( ! UTF8::is_ascii($path))
		{
			// Encode all non-ASCII characters, as per RFC 1738
			$path = preg_replace('~([^/]+)~e', 'rawurlencode("$1")', $path);
		}

		// Concat the URL
		return URL::base(TRUE, $protocol).$path;
	}

	/**
	 * Merges the current GET parameters with an array of new or overloaded
	 * parameters and returns the resulting query string.
	 *
	 *     // Returns "?sort=title&limit=10" combined with any existing GET values
	 *     $query = URL::query(array('sort' => 'title', 'limit' => 10));
	 *
	 * Typically you would use this when you are sorting query results,
	 * or something similar.
	 *
	 * [!!] Parameters with a NULL value are left out.
	 *
	 * @param   array    array of GET parameters
	 * @param   boolean  include current request GET parameters
	 * @return  string
	 */
	public static function query(array $params = NULL, $use_get = TRUE)
	{
		if ($use_get)
		{
			if ($params === NULL)
			{
				// Use only the current parameters
				$params = $_GET;
			}
			else
			{
				// Merge the current and new parameters
				$params = array_merge($_GET, $params);
			}
		}

		if (empty($params))
		{
			// No query parameters
			return '';
		}

		// Note: http_build_query returns an empty string for a params array with only NULL values
		$query = http_build_query($params, '', '&');

		// Don't prepend '?' to an empty string
		return ($query === '') ? '' : ('?'.$query);
	}

	/**
	 * Convert a phrase to a URL-safe title.
	 *
	 *     echo URL::title('My Blog Post'); // "my-blog-post"
	 *
	 * @param   string   phrase to convert
	 * @param   string   word separator (any single character)
	 * @param   boolean  transliterate to ASCII?
	 * @return  string
	 * @uses    UTF8::transliterate_to_ascii
	 */
	public static function title($title, $separator = '-', $ascii_only = FALSE)
	{
		if ($ascii_only === TRUE)
		{
			// Transliterate non-ASCII characters
			$title = UTF8::transliterate_to_ascii($title);

			// Remove all characters that are not the separator, a-z, 0-9, or whitespace
			$title = preg_replace('![^'.preg_quote($separator).'a-z0-9\s]+!', '', strtolower($title));
		}
		else
		{
			// Remove all characters that are not the separator, letters, numbers, or whitespace
			$title = preg_replace('![^'.preg_quote($separator).'\pL\pN\s]+!u', '', UTF8::strtolower($title));
		}

		// Replace all separator characters and whitespace by a single separator
		$title = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $title);

		// Trim separators from the beginning and end
		return trim($title, $separator);
	}

} // End url
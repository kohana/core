<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Provides remote server communications options using [curl](http://php.net/curl).
 *
 * @package    Kohana
 * @category   Helpers
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Remote {

	// Default curl options
	public static $default_options = array
	(
		CURLOPT_CONNECTTIMEOUT => 5,
		CURLOPT_TIMEOUT        => 5,
		CURLOPT_HEADERFUNCTION => array('Remote', '_parse_headers'),
		CURLOPT_HEADER         => FALSE,
	);

	/**
	 * @var     array  Headers from the request
	 */
	protected static $_headers = array();

	/**
	 * Parses the returned headers from the remote
	 * request
	 *
	 * @param   resource the curl resource
	 * @param   string   the full header string
	 * @return  int
	 */
	protected static function _parse_headers($remote, $header)
	{
		$headers = array();

		if (preg_match_all('/(\w[^\s:]*):[ ]*([^\r\n]*(?:\r\n[ \t][^\r\n]*)*)/', $header, $matches))
		{
			foreach ($matches[0] as $key => $value)
				$headers[$matches[1][$key]] = $matches[2][$key];
		}

		// If there are headers to apply
		if ($headers)
		{
			Remote::$_headers += $headers;
		}

		return strlen($header);
	}

	/**
	 * Returns the output of a remote URL.
	 * Any [curl option](http://php.net/curl_setopt) may be used.
	 *
	 *     // Do a simple GET request
	 *     $data = Remote::get($url);
	 *
	 *     // Do a POST request
	 *     $data = Remote::get($url, array(
	 *         CURLOPT_POST       => TRUE,
	 *         CURLOPT_POSTFIELDS => http_build_query($array),
	 *     ));
	 *
	 * @param   string   remote URL
	 * @param   array    curl options
	 * @return  [Kohana_Response]
	 * @throws  [Kohana_Exception]
	 */
	public static function get($url, array $options = NULL)
	{
		// Reset the headers
		Remote::$_headers = array();

		if ($options === NULL)
		{
			// Use default options
			$options = Remote::$default_options;
		}
		else
		{
			// Add default options
			$options = $options + Remote::$default_options;
		}

		// The transfer must always be returned
		$options[CURLOPT_RETURNTRANSFER] = TRUE;
		$options[CURLOPT_USERAGENT]      = 'Mozilla/5.0 (compatible; Kohana v'.Kohana::VERSION.' +http://kohanaphp.com/)';

		// Open a new remote connection
		$remote = curl_init($url);

		// Set connection options
		if ( ! curl_setopt_array($remote, $options))
		{
			throw new Kohana_Exception('Failed to set CURL options, check CURL documentation: :url',
				array(':url' => 'http://php.net/curl_setopt_array'));
		}

		// Get the response
		$response = curl_exec($remote);

		// Get the response information
		$code = curl_getinfo($remote, CURLINFO_HTTP_CODE);

		if ($code < 200 OR $code > 299)
		{
			$error = $response;
		}
		elseif ($response === FALSE)
		{
			$error = curl_error($remote);
		}

		// Close the connection
		curl_close($remote);

		if (isset($error))
		{
			throw new Kohana_Exception('Error fetching remote :url [ status :code ] :error',
				array(':url' => $url, ':code' => $code, ':error' => $error));
		}

		return new Response(array(
			'status'     => $code,
			'headers'    => Remote::$_headers,
			'body'       => $response,
		));
	}

	/**
	 * Returns the status code (200, 500, etc) for a URL.
	 *
	 *     $status = Remote::status($url);
	 *
	 * @param   string  URL to check
	 * @param   array   HTTP Headers to include
	 * @return  integer
	 */
	public static function status($url, array $http_headers = array())
	{
		// Get the hostname and path
		$url = parse_url($url);

		if (empty($url['path']))
		{
			// Request the root document
			$url['path'] = '/';
		}

		// Open a remote connection
		$port = isset($url['port']) ? $url['port'] : 80;
		$remote = fsockopen($url['host'], $port, $errno, $errstr, 5);

		if ( ! is_resource($remote))
			return FALSE;

		// Set CRLF
		$CRLF = "\r\n";

		// Send request
		fwrite($remote, 'HEAD '.$url['path'].' HTTP/1.0'.$CRLF);
		fwrite($remote, 'Host: '.$url['host'].$CRLF);
		fwrite($remote, 'Connection: close'.$CRLF);
		fwrite($remote, 'User-Agent: Kohana Framework (+http://kohanaphp.com/)'.$CRLF);

		foreach ($http_headers as $name => $value)
			fwrite($remote, $name.': '.$value.$CRLF);

		// Send one more CRLF to terminate the headers
		fwrite($remote, $CRLF);

		// Remote is offline
		$response = FALSE;

		while ( ! feof($remote))
		{
			// Get the line
			$line = trim(fgets($remote, 512));

			if ($line !== '' AND preg_match('#^HTTP/1\.[01] (\d{3})#', $line, $matches))
			{
				// Response code found
				$response = (int) $matches[1];
				break;
			}
		}

		// Close the connection
		fclose($remote);

		return $response;
	}

	final private function __construct()
	{
		// This is a static class
	}

} // End remote

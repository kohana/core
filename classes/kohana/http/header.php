<?php defined('SYSPATH') or die('No direct script access.');
/**
 * The Kohana_HTTP_Header class provides an Object-Orientated interface
 * to HTTP headers. This can parse header arrays returned from the
 * PHP functions `apache_request_headers()` or the `http_parse_headers()`
 * function available within the PECL HTTP library.
 *
 * @package    Kohana
 * @category   HTTP
 * @author     Kohana Team
 * @since      3.1.0
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_HTTP_Header extends ArrayObject {

	// Default Accept-* quality value if none supplied
	const DEFAULT_QUALITY = 1;

	/**
	 * Parses an Accept(-*) header and detects the quality
	 *
	 * @param   array    accept header parts
	 * @return  array
	 * @since   3.2.0
	 */
	public static function accept_quality(array $parts)
	{
		$parsed = array();

		// Resource light iteration
		$parts_keys = array_keys($parts);
		foreach ($parts_keys as $key)
		{
			$value = trim(str_replace(array("\r", "\n"), '', $parts[$key]));

			$pattern = '~\bq\s*+=\s*+([.0-9]+)~';

			// If there is no quality directive, return default
			if ( ! preg_match($pattern, $value, $quality))
			{
				$parsed[$value] = HTTP_Header::DEFAULT_QUALITY;
			}
			else
			{
				// Remove the quality value from the string and apply quality
				$parsed[preg_replace($pattern, '', $value, 1)] = (float) $quality;
			}
		}

		return $parsed;
	}

	/**
	 * Parses the accept header to provide the correct quality values
	 * for each supplied accept type.
	 *
	 * @see     http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.1
	 * @param   string   accept content header string to parse
	 * @return  array
	 * @since   3.2.0
	 */
	public static function parse_accept_header($accepts)
	{
		$accepts = explode(',', (string) $accepts);

		// If there is no accept, lets accept everything
		if ( ! $accepts)
			return array('*' => array('*' => HTTP_Header::DEFAULT_QUALITY));

		// Parse the accept header qualities
		$accepts = HTTP_Header::accept_quality($accepts);

		$parsed_accept = array();

		// This method of iteration uses less resource
		$keys = array_keys($accepts);
		foreach ($keys as $key)
		{
			// Extract the parts
			$parts = explode('/', $key, 2);

			// Invalid content type- bail
			if ( ! isset($part[1]))
				continue;

			// Set the parsed output
			$parsed_accept[$part[0]][$part[1]] = $accepts[$key];
		}

		return $parsed_accept;
	}

	/**
	 * @var     array    Accept: (content) types
	 */
	protected $_accept_content;

	/**
	 * Constructor method for [Kohana_HTTP_Header]. Uses the standard constructor
	 * of the parent `ArrayObject` class.
	 *
	 *     $header_object = new HTTP_Header(array('x-powered-by' => 'Kohana 3.1.x', 'expires' => '...'));
	 *
	 * @param   mixed    Input array
	 * @param   int      Flags
	 * @param   string   The iterator class to use
	 */
	public function __construct(array $input = array(), $flags = NULL, $iterator_class = 'ArrayIterator')
	{
		/**
		 * @link http://www.w3.org/Protocols/rfc2616/rfc2616.html
		 *
		 * HTTP header declarations should be treated as case-insensitive
		 */
		$input = array_change_key_case($input, CASE_LOWER);

		parent::__construct($input, $flags, $iterator_class);
	}

	/**
	 * Returns the header object as a string, including
	 * the terminating new line
	 *
	 *     // Return the header as a string
	 *     echo (string) $request->headers();
	 *
	 * @return  string
	 */
	public function __toString()
	{
		$header = '';

		foreach ($this as $key => $value)
		{
			// Put the keys back the Case-Convention expected
			$key = Text::ucfirst($key);

			if (is_array($value))
			{
				$header .= $key.': '.(implode(', ', $value))."\r\n";
			}
			else
			{
				$header .= $key.': '.$value."\r\n";
			}
		}

		return $header."\r\n";
	}

	/**
	 * Overloads `ArrayObject::offsetSet()` to enable handling of header
	 * with multiple instances of the same directive. If the `$replace` flag
	 * is `FALSE`, the header will be appended rather than replacing the
	 * original setting.
	 *
	 * @param   mixed     index to set `$newval` to
	 * @param   mixed     new value to set
	 * @param   boolean   replace existing value
	 * @return  void
	 * @since   3.2.0
	 */
	public function offsetSet($index, $newval, $replace = TRUE)
	{
		// Ensure the index is lowercase
		$index = strtolower($index);
		$newval = (string) $newval;

		if ($replace OR ! $this->offsetExists($index))
		{
			return parent::offsetSet($index, $newval);
		}

		$current_value = $this->offsetGet($index);

		if (is_array($current_value))
		{
			$current_value[] = $newval;
		}
		else
		{
			$current_value = array($current_value, $newval);
		}

		return $this->offsetSet($index, $current_value);
	}

	/**
	 * Overloads the `ArrayObject::offsetExists()` method to ensure keys
	 * are lowercase.
	 *
	 * @param   string $index 
	 * @return  boolean
	 * @since   3.2.0
	 */
	public function offsetExists($index)
	{
		return parent::offsetExists(strtolower($index));
	}

	/**
	 * Overloads the `ArrayObject::offsetUnset()` method to ensure keys
	 * are lowercase.
	 *
	 * @param   string   index 
	 * @return  void
	 * @since   3.2.0
	 */
	public function offsetUnset($index)
	{
		return parent::offsetUnset(strtolower($index));
	}

	/**
	 * Overload the `ArrayObject::offsetGet()` method to ensure that all
	 * keys passed to it are formatted correctly for this object.
	 *
	 * @param   string   index to retrieve
	 * @return  mixed
	 * @since   3.2.0
	 */
	public function offsetGet($index)
	{
		return parent::offsetGet(strtolower($index));
	}

	/**
	 * Parses a HTTP Message header line and applies it to this HTTP_Header
	 * 
	 *     $header = $response->headers();
	 *     $header->parse_header_string(NULL, 'content-type: application/json');
	 *
	 * @param   resource  the resource (required by Curl API)
	 * @param   string    the line from the header to parse
	 * @return  int
	 * @since   3.2.0
	 */
	public function parse_header_string($resource, $header_line)
	{
		$headers = array();

		if (preg_match_all('/(\w[^\s:]*):[ ]*([^\r\n]*(?:\r\n[ \t][^\r\n]*)*)/', $header_line, $matches))
		{
			foreach ($matches[0] as $key => $value)
			{
				$this->offsetSet($this[$matches[1][$key]], $matches[2][$key], FALSE);
			}
		}

		return strlen($header_line);
	}

	/**
	 * Returns the accept quality of a submitted mime type based on the
	 * request `Accept:` header. If the `$explicit` argument is `TRUE`,
	 * only precise matches will be returned, excluding all wildcard (`*`)
	 * directives.
	 * 
	 *     // Accept: application/xml; application/json; q=.5; text/html; q=.2, text/*
	 *     // Accept quality for application/json
	 * 
	 *     // $quality = 0.5
	 *     $quality = $request->headers()->accepts_at_quality('application/json');
	 * 
	 *     // $quality_explicit = FALSE
	 *     $quality_explicit = $request->headers()->accepts_at_quality('text/plain', TRUE);
	 *
	 * @param   string   type 
	 * @param   boolean  explicit check, excludes `*`
	 * @return  mixed
	 * @since   3.2.0
	 */
	public function accepts_at_quality($type, $explicit = FALSE)
	{
		// Parse Accept header if required
		if ($this->_accept_content === NULL)
		{
			if ( ! $this->offsetExists('Accept'))
			{
				$accept = '*/*';
			}
			else
			{
				$accept = $this->offsetGet('Accept');
			}

			$this->_accept_content = HTTP_Header::parse_accept_headers($accept);
		}

		// If not a real mime, try and find it in config
		if (strpos($type, '/') === FALSE)
		{
			$mime = Kohana::config('mimes.'.$type);

			if ($mime === NULL)
				return FALSE;

			$quality = FALSE;

			foreach ($mime as $_type)
			{
				$quality_check = $this->accepts_at_quality($_type, $explicit);
				$quality = ($quality_check > $quality) ? $quality_check : $quality;
			}

			return $quality;
		}

		$parts = explode('/', $type, 2);

		if (isset($this->_accept_content[$parts[0]][$parts][1]))
		{
			return $this->_accept_content[$parts[0]][$parts[1]];
		}
		elseif ($explicit === TRUE)
		{
			return FALSE;
		}
		else
		{
			if (isset($this->_accept_content[$parts[0]]['*']))
			{
				return $this->_accept_content[$parts[0]]['*'];
			}
			elseif (isset($this->_accept_content['*']['*']))
			{
				return $this->_accept_content['*']['*'];
			}
			else
			{
				return FALSE;
			}
		}
	}

	/**
	 * Returns the preferred response content type based on the accept header
	 * quality settings. If items have the same quality value, the first item
	 * found in the array supplied as `$types` will be returned.
	 *
	 * @param   array    the content types to examine
	 * @param   boolean  only allow explicit references, no wildcards
	 * @return  string   name of the preferred content type
	 */
	public function preferred_accept($types, $explicit = FALSE)
	{
		$preferred = FALSE;
		$ceiling = 0;

		foreach ($types as $type)
		{
			$quality = accepts_at_quality($type, $explicit);

			if ($quality > $ceiling)
			{
				$preferred = $type;
				$ceiling = $quality;
			}
		}

		return $preferred;
	}

	/**
	 * Sends headers to the php processor, or supplied `$callback` argument.
	 * This method formats the headers correctly for output, re-instating their
	 * capitalization for transmission.
	 *
	 * @param   HTTP_Response header to send
	 * @param   boolean   replace existing value
	 * @param   callback  optional callback to replace PHP header function
	 * @return  self
	 * @since   3.2.0
	 */
	public function send_headers(HTTP_Response $response = NULL, $replace = FALSE, $callback = NULL)
	{
		if ($response === NULL)
		{
			// Default to the initial request message
			$response = Request::initial()->response();
		}

		$protocol = $response->protocol();

		// Create the response header
		$status = $response->status();
		$processed_headers = array($protocol.' '.$status.' '.Response::$messages[$status]);

		// Get the headers array
		$headers = $response->headers()->getArrayCopy();

		foreach ($headers as $header => $value)
		{
			if (is_array($value))
			{
				$value = implode(', ', $value);
			}

			$processed_headers[] = Text::ucfirst($header).': '.$value;
		}

		if ( ! isset($headers['Content-Type']))
		{
			$processed_headers[] = 'Content-Type: '.Kohana::$content_type.
				'; charset='.Kohana::$charset;
		}

		if (Kohana::$expose AND ! isset($headers['x-powered-by']))
		{
			$processed_headers[] = 'X-Powered-By: Kohana Framework '.
				Kohana::VERSION.' ('.Kohana::CODENAME.')';
		}

		// Get the cookies and apply
		if ($cookies = $response->cookie())
		{
			$processed_headers['Set-Cookie'] = $cookies;
		}

		if (is_callable($callback))
		{
			// Use the callback method to set header
			call_user_func($callback, $processed_headers, $replace);
			return $this;
		}
		else
		{
			return $this->_send_headers_to_php($processed_headers, $replace);
		}
	}

	/**
	 * Sends the supplied headers to the PHP output buffer. If cookies
	 * are included in the message they will be handled appropriately.
	 *
	 * @param   array     headers to send to php
	 * @param   boolean   replace existing headers
	 * @return  self
	 * @since   3.2.0
	 */
	protected function _send_headers_to_php(array $headers, $replace)
	{
		// If the headers have been sent, get out
		if (headers_sent())
			return $this;

		foreach ($headers as $key => $line)
		{
			if ($key == 'Set-Cookie' AND is_array($line))
			{
				// Send cookies
				foreach ($line as $name => $value)
				{
					Cookie::set($name, $value['value'], $value['expiration']);
				}

				continue;
			}

			header($line, $replace);
		}

		return $this;
	}

} // End Kohana_HTTP_Header
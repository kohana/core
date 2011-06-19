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
		$headers_out = array($protocol.' '.$status.' '.Response::$messages[$status]);

		// Get the headers array
		$headers = $response->headers()->getArrayCopy();

		if ( ! isset($headers['content-type']))
		{
			$headers['content-type'] = Kohana::$content_type.'; charset='.Kohana::$charset;
		}

		if (Kohana::$expose AND ! isset($headers['x-powered-by']))
		{
			$headers['x-powered-by'] = 'Kohana Framework '.Kohana::VERSION.' ('.Kohana::CODENAME.')';
		}

		array_walk($headers, array($this, '_format_headers'));
		$headers_out = array_values($headers);

		// Add the cookies
		$headers_out['Set-Cookie'] = $response->cookie();

		if (is_callable($callback))
		{
			// Use the callback method to set header
			call_user_func($callback, $headers_out, $replace);
			return $this;
		}
		else
		{
			return $this->_send_headers_to_php($headers_out, $replace);
		}
	}

	/**
	 * Formats the header output back to what is expected by most users,
	 * also handles multiple header directives. This should only be used as
	 * a callback method within [HTTP_Header::send_headers()].
	 *
	 * @param   mixed    value to format for output
	 * @param   string   header key 
	 * @return  void
	 * @since   3.2.0
	 */
	protected function _format_headers( & $value, $key)
	{
		$buffer = Text::ucfirst($key).': ';

		if (is_array($value))
		{
			$value = implode(', ', $value);
		}

		$value = $buffer.$value;
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
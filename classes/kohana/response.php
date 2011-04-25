<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Response wrapper. Created as the result of any [Request] execution
 * or utility method (i.e. Redirect). Implements standard HTTP
 * response format.
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaphp.com/license
 * @since      3.1.0
 */
class Kohana_Response implements HTTP_Response, Serializable {

	/**
	 * Factory method to create a new [Response]. Pass properties
	 * in using an associative array.
	 *
	 *      // Create a new response
	 *      $response = Response::factory();
	 *
	 *      // Create a new response with headers
	 *      $response = Response::factory(array('status' => 200));
	 *
	 * @param   array    $config Setup the response object
	 * @return  Response
	 */
	public static function factory(array $config = array())
	{
		return new Response($config);
	}

	/**
	 * Generates a [Cache-Control HTTP](http://en.wikipedia.org/wiki/List_of_HTTP_headers)
	 * header based on the supplied array.
	 *
	 *     // Set the cache control headers you want to use
	 *     $cache_control = array(
	 *         'max-age'          => 3600,
	 *         'must-revalidate'  => NULL,
	 *         'public'           => NULL
	 *     );
	 *
	 *     // Create the cache control header, creates :
	 *     // cache-control: max-age=3600, must-revalidate, public
	 *     $response->header['cache-control'] = Response::create_cache_control($cache_control);
	 *
	 * @param   array    $cache_control Cache_control parts to render
	 * @return  string
	 */
	public static function create_cache_control(array $cache_control)
	{
		// Create a buffer
		$parts = array();

		// Foreach cache control entry
		foreach ($cache_control as $key => $value)
		{
			// Create a cache control fragment
			$parts[] = empty($value) ? $key : ($key.'='.$value);
		}
		// Return the rendered parts
		return implode(', ', $parts);
	}

	/**
	 * Parses the Cache-Control header and returning an array representation of the Cache-Control
	 * header.
	 *
	 *     // Create the cache control header
	 *     $response->header['cache-control'] = 'max-age=3600, must-revalidate, public';
	 *
	 *     // Parse the cache control header
	 *     if ($cache_control = Request::parse_cache_control($response->header['cache-control']))
	 *     {
	 *          // Cache-Control header was found
	 *          $maxage = $cache_control['max-age'];
	 *     }
	 *
	 * @param   array   $cache_control Array of headers
	 * @return  mixed
	 */
	public static function parse_cache_control($cache_control)
	{
		// If no Cache-Control parts are detected
		if ( (bool) preg_match_all('/(?<key>[a-z\-]+)=?(?<value>\w+)?/', $cache_control, $matches))
		{
			// Return combined cache-control key/value pairs
			return array_combine($matches['key'], $matches['value']);
		}
		else
		{
			// Return
			return FALSE;
		}
	}

	// HTTP status codes and messages
	public static $messages = array(
		// Informational 1xx
		100 => 'Continue',
		101 => 'Switching Protocols',

		// Success 2xx
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',

		// Redirection 3xx
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found', // 1.1
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		// 306 is deprecated but reserved
		307 => 'Temporary Redirect',

		// Client Error 4xx
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		418 => 'I\'m a teapot',
		422 => 'Unprocessable Entity',
		444 => 'No Response',
		499 => 'Client Closed Request',

		// Server Error 5xx
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		509 => 'Bandwidth Limit Exceeded'
	);

	/**
	 * @var  integer     The response http status
	 */
	protected $_status = 200;

	/**
	 * @var  HTTP_Header  Headers returned in the response
	 */
	protected $_header;

	/**
	 * @var  string      The response body
	 */
	protected $_body = '';

	/**
	 * @var  array       Cookies to be returned in the response
	 */
	protected $_cookies = array();

	/**
	 * @var  string      The response protocol
	 */
	protected $_protocol;

	/**
	 * Sets up the response object
	 *
	 * @param   array $config Setup the response object
	 * @return  void
	 */
	public function __construct(array $config = array())
	{
		$this->_header = new HTTP_Header(array());

		foreach ($config as $key => $value)
		{
			if (property_exists($this, $key))
			{
				if ($key == '_header')
				{
					$this->headers($value);
				}
				else
				{
					$this->$key = $value;
				}
			}
		}
	}

	/**
	 * Outputs the body when cast to string
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->_body;
	}

	/**
	 * Gets or sets the body of the response
	 *
	 * @return  mixed
	 */
	public function body($content = NULL)
	{
		if ($content === NULL)
			return $this->_body;

		$this->_body = (string) $content;
		return $this;
	}

	/**
	 * Gets or sets the HTTP protocol. The standard protocol to use
	 * is `HTTP/1.1`.
	 *
	 * @param   string   $protocol Protocol to set to the request/response
	 * @return  mixed
	 */
	public function protocol($protocol = NULL)
	{
		if ($protocol)
		{
			$this->_protocol = $protocol;
			return $this;
		}

		return $this->_protocol;
	}

	/**
	 * Sets or gets the HTTP status from this response.
	 *
	 *      // Set the HTTP status to 404 Not Found
	 *      $response = Response::factory()
	 *              ->status(404);
	 *
	 *      // Get the current status
	 *      $status = $response->status();
	 *
	 * @param   integer  $status Status to set to this response
	 * @return  mixed
	 */
	public function status($status = NULL)
	{
		if ($status === NULL)
		{
			return $this->_status;
		}
		elseif (array_key_exists($status, Response::$messages))
		{
			$this->_status = (int) $status;
			return $this;
		}
		else
		{
			throw new Kohana_Exception(__METHOD__.' unknown status value : :value', array(':value' => $status));
		}
	}

	/**
	 * Gets and sets headers to the [Response], allowing chaining
	 * of response methods. If chaining isn't required, direct
	 * access to the property should be used instead.
	 *
	 *       // Get a header
	 *       $accept = $response->headers('Content-Type');
	 *
	 *       // Set a header
	 *       $response->headers('Content-Type', 'text/html');
	 *
	 *       // Get all headers
	 *       $headers = $response->headers();
	 *
	 *       // Set multiple headers
	 *       $response->headers(array('Content-Type' => 'text/html', 'Cache-Control' => 'no-cache'));
	 *
	 * @param mixed $key
	 * @param string $value
	 * @return mixed
	 */
	public function headers($key = NULL, $value = NULL)
	{
		if ($key === NULL)
		{
			return $this->_header;
		}
		elseif (is_array($key))
		{
			$this->_header->exchangeArray($key);
			return $this;
		}
		elseif ($value === NULL)
		{
			return Arr::get($this->_header, $key);
		}
		else
		{
			$this->_header[$key] = $value;
			return $this;
		}
	}

	/**
	 * Returns the length of the body for use with
	 * content header
	 *
	 * @return  integer
	 */
	public function content_length()
	{
		return strlen($this->_body);
	}

	/**
	 * Set and get cookies values for this response.
	 * 
	 *     // Get the cookies set to the response
	 *     $cookies = $response->cookie();
	 *     
	 *     // Set a cookie to the response
	 *     $response->cookie('session', array(
	 *          'value' => $value,
	 *          'expiration' => 12352234
	 *     ));
	 *
	 * @param   mixed     cookie name, or array of cookie values
	 * @param   string    value to set to cookie
	 * @return  string
	 * @return  void
	 * @return  [Response]
	 */
	public function cookie($key = NULL, $value = NULL)
	{
		// Handle the get cookie calls
		if ($key === NULL)
			return $this->_cookies;
		elseif ( ! is_array($key) AND ! $value)
			return Arr::get($this->_cookies, $key);

		// Handle the set cookie calls
		if (is_array($key))
		{
			reset($key);
			while (list($_key, $_value) = each($key))
			{
				$this->cookie($_key, $_value);
			}
		}
		else
		{
			if ( ! is_array($value))
			{
				$value = array(
					'value' => $value,
					'expiration' => Cookie::$expiration
				);
			}
			elseif ( ! isset($value['expiration']))
			{
				$value['expiration'] = Cookie::$expiration;
			}

			$this->_cookies[$key] = $value;
		}

		return $this;
	}

	/**
	 * Deletes a cookie set to the response
	 *
	 * @param   string   name
	 * @return  Response
	 */
	public function delete_cookie($name)
	{
		unset($this->_cookies[$name]);
		return $this;
	}

	/**
	 * Deletes all cookies from this response
	 *
	 * @return  Response
	 */
	public function delete_cookies()
	{
		$this->_cookies = array();
		return $this;
	}

	/**
	 * Sends the response status and all set headers.
	 *
	 * @return  Response
	 */
	public function send_headers()
	{
		if ( ! headers_sent())
		{
			if (isset($_SERVER['SERVER_PROTOCOL']))
			{
				// Use the default server protocol
				$protocol = $_SERVER['SERVER_PROTOCOL'];
			}
			else
			{
				// Default to using newer protocol
				$protocol = strtoupper(HTTP::$protocol).'/'.HTTP::$version;
			}

			// Default to text/html; charset=utf8 if no content type set
			if ( ! $this->_header->offsetExists('content-type'))
			{
				$this->_header['content-type'] = Kohana::$content_type.'; charset='.Kohana::$charset;
			}

			// Add the X-Powered-By header
			if (Kohana::$expose)
			{
				$this->_header['x-powered-by'] = 'Kohana Framework '.Kohana::VERSION.' ('.Kohana::CODENAME.')';
			}

			if ( ! Kohana::$is_cli)
			{
				// HTTP status line
				header($protocol.' '.$this->_status.' '.Response::$messages[$this->_status]);

				foreach ($this->_header as $name => $value)
				{
					if (is_string($name))
					{
						// Combine the name and value to make a raw header
						$value = $name.': '.$value;
					}

					// Send the raw header
					header($value, TRUE);
				}
			}

			// Send cookies
			foreach ($this->_cookies as $name => $value)
			{
				Cookie::set($name, $value['value'], $value['expiration']);
			}
		}

		return $this;
	}

	/**
	 * Send file download as the response. All execution will be halted when
	 * this method is called! Use TRUE for the filename to send the current
	 * response as the file content. The third parameter allows the following
	 * options to be set:
	 *
	 * Type      | Option    | Description                        | Default Value
	 * ----------|-----------|------------------------------------|--------------
	 * `boolean` | inline    | Display inline instead of download | `FALSE`
	 * `string`  | mime_type | Manual mime type                   | Automatic
	 * `boolean` | delete    | Delete the file after sending      | `FALSE`
	 *
	 * Download a file that already exists:
	 *
	 *     $request->send_file('media/packages/kohana.zip');
	 *
	 * Download generated content as a file:
	 *
	 *     $request->response($content);
	 *     $request->send_file(TRUE, $filename);
	 *
	 * [!!] No further processing can be done after this method is called!
	 *
	 * @param   string   filename with path, or TRUE for the current response
	 * @param   string   downloaded file name
	 * @param   array    additional options
	 * @return  void
	 * @throws  Kohana_Exception
	 * @uses    File::mime_by_ext
	 * @uses    File::mime
	 * @uses    Request::send_headers
	 */
	public function send_file($filename, $download = NULL, array $options = NULL)
	{
		if ( ! empty($options['mime_type']))
		{
			// The mime-type has been manually set
			$mime = $options['mime_type'];
		}

		if ($filename === TRUE)
		{
			if (empty($download))
			{
				throw new Kohana_Exception('Download name must be provided for streaming files');
			}

			// Temporary files will automatically be deleted
			$options['delete'] = FALSE;

			if ( ! isset($mime))
			{
				// Guess the mime using the file extension
				$mime = File::mime_by_ext(strtolower(pathinfo($download, PATHINFO_EXTENSION)));
			}

			// Force the data to be rendered if
			$file_data = (string) $this->_body;

			// Get the content size
			$size = strlen($file_data);

			// Create a temporary file to hold the current response
			$file = tmpfile();

			// Write the current response into the file
			fwrite($file, $file_data);

			// File data is no longer needed
			unset($file_data);
		}
		else
		{
			// Get the complete file path
			$filename = realpath($filename);

			if (empty($download))
			{
				// Use the file name as the download file name
				$download = pathinfo($filename, PATHINFO_BASENAME);
			}

			// Get the file size
			$size = filesize($filename);

			if ( ! isset($mime))
			{
				// Get the mime type
				$mime = File::mime($filename);
			}

			// Open the file for reading
			$file = fopen($filename, 'rb');
		}

		if ( ! is_resource($file))
		{
			throw new Kohana_Exception('Could not read file to send: :file', array(
				':file' => $download,
			));
		}

		// Inline or download?
		$disposition = empty($options['inline']) ? 'attachment' : 'inline';

		// Calculate byte range to download.
		list($start, $end) = $this->_calculate_byte_range($size);

		if ( ! empty($options['resumable']))
		{
			if ($start > 0 OR $end < ($size - 1))
			{
				// Partial Content
				$this->_status = 206;
			}

			// Range of bytes being sent
			$this->_header['content-range'] = 'bytes '.$start.'-'.$end.'/'.$size;
			$this->_header['accept-ranges'] = 'bytes';
		}

		// Set the headers for a download
		$this->_header['content-disposition'] = $disposition.'; filename="'.$download.'"';
		$this->_header['content-type']        = $mime;
		$this->_header['content-length']      = (string) (($end - $start) + 1);

		if (Request::user_agent('browser') === 'Internet Explorer')
		{
			// Naturally, IE does not act like a real browser...
			if (Request::$initial->protocol() === 'https')
			{
				// http://support.microsoft.com/kb/316431
				$this->_header['pragma'] = $this->_header['cache-control'] = 'public';
			}

			if (version_compare(Request::user_agent('version'), '8.0', '>='))
			{
				// http://ajaxian.com/archives/ie-8-security
				$this->_header['x-content-type-options'] = 'nosniff';
			}
		}

		// Send all headers now
		$this->send_headers();

		while (ob_get_level())
		{
			// Flush all output buffers
			ob_end_flush();
		}

		// Manually stop execution
		ignore_user_abort(TRUE);

		if ( ! Kohana::$safe_mode)
		{
			// Keep the script running forever
			set_time_limit(0);
		}

		// Send data in 16kb blocks
		$block = 1024 * 16;

		fseek($file, $start);

		while ( ! feof($file) AND ($pos = ftell($file)) <= $end)
		{
			if (connection_aborted())
				break;

			if ($pos + $block > $end)
			{
				// Don't read past the buffer.
				$block = $end - $pos + 1;
			}

			// Output a block of the file
			echo fread($file, $block);

			// Send the data now
			flush();
		}

		// Close the file
		fclose($file);

		if ( ! empty($options['delete']))
		{
			try
			{
				// Attempt to remove the file
				unlink($filename);
			}
			catch (Exception $e)
			{
				// Create a text version of the exception
				$error = Kohana_Exception::text($e);

				if (is_object(Kohana::$log))
				{
					// Add this exception to the log
					Kohana::$log->add(Log::ERROR, $error);

					// Make sure the logs are written
					Kohana::$log->write();
				}

				// Do NOT display the exception, it will corrupt the output!
			}
		}

		// Stop execution
		exit;
	}

	/**
	 * Renders the HTTP_Interaction to a string, producing
	 *
	 *  - Protocol
	 *  - Headers
	 *  - Body
	 *
	 * @return  string
	 */
	public function render()
	{
		if ( ! $this->_header->offsetExists('content-type'))
		{
			// Add the default Content-Type header if required
			$this->_header['content-type'] = Kohana::$content_type.'; charset='.Kohana::$charset;
		}

		$content_length = $this->content_length();

		// Set the content length for the body if required
		if ($content_length > 0)
		{
			$this->_header['content-length'] = (string) $content_length;
		}

		// Prepare cookies
		if ($this->_cookies)
		{
			if (extension_loaded('http'))
			{
				$this->_header['set-cookie'] = http_build_cookie($this->_cookies);
			}
			else
			{
				$cookies = array();

				// Parse each
				foreach ($this->_cookies as $key => $value)
				{
					$string = $key.'='.$value['value'].'; expires='.date('l, d M Y H:i:s T', $value['expiration']);
					$cookies[] = $string;
				}

				// Create the cookie string
				$this->_header['set-cookie'] = $cookies;
			}
		}

		$output = $this->_protocol.' '.$this->_status.' '.Response::$messages[$this->_status]."\n";
		$output .= (string) $this->_header;
		$output .= $this->_body;

		return $output;
	}

	/**
	 * Generate ETag
	 * Generates an ETag from the response ready to be returned
	 *
	 * @throws Kohana_Request_Exception
	 * @return String Generated ETag
	 */
	public function generate_etag()
	{
	    if ($this->_body === NULL)
		{
			throw new Kohana_Request_Exception('No response yet associated with request - cannot auto generate resource ETag');
		}

		// Generate a unique hash for the response
		return '"'.sha1($this->render()).'"';
	}

	/**
	 * Check Cache
	 * Checks the browser cache to see the response needs to be returned
	 *
	 * @param   string   $etag Resource ETag
	 * @param   Request  $request The request to test against
	 * @return  Response
	 * @throws  Kohana_Request_Exception
	 */
	public function check_cache($etag = NULL, Request $request = NULL)
	{
		if ( ! $etag)
		{
			$etag = $this->generate_etag();
		}

		if ( ! $request)
			throw new Kohana_Request_Exception('A Request object must be supplied with an etag for evaluation');

		// Set the ETag header
		$this->_header['etag'] = $etag;

		// Add the Cache-Control header if it is not already set
		// This allows etags to be used with max-age, etc
		if ($this->_header->offsetExists('cache-control'))
		{
			if (is_array($this->_header['cache-control']))
			{
				$this->_header['cache-control'][] = new HTTP_Header_Value('must-revalidate');
			}
			else
			{
				$this->_header['cache-control'] = $this->_header['cache-control'].', must-revalidate';
			}
		}
		else
		{
			$this->_header['cache-control'] = 'must-revalidate';
		}

		if ($request->headers('if-none-match') AND (string) $request->headers('if-none-match') === $etag)
		{
			// No need to send data again
			$this->_status = 304;
			$this->send_headers();

			// Stop execution
			exit;
		}

		return $this;
	}

	/**
	 * Serializes the object to json - handy if you
	 * need to pass the response data to other
	 * systems
	 *
	 * @param   array    array of data to serialize
	 * @return  string
	 * @throws  Kohana_Exception
	 */
	public function serialize(array $to_serialize = array())
	{
		// Serialize the class properties
		$to_serialize += array
		(
			'_status'  => $this->_status,
			'_header'  => $this->_header,
			'_cookies' => $this->_cookies,
			'_body'    => $this->_body
		);

		$serialized = serialize($to_serialize);

		if (is_string($serialized))
		{
			return $serialized;
		}
		else
		{
			throw new Kohana_Exception('Unable to serialize object');
		}
	}

	/**
	 * JSON encoded object
	 *
	 * @param   string   json encoded object
	 * @return  bool
	 * @throws  Kohana_Exception
	 */
	public function unserialize($string)
	{
		// Unserialise object
		$unserialized = unserialize($string);

		// If failed
		if ($unserialized === NULL)
		{
			// Throw exception
			throw new Kohana_Exception('Unable to correctly unserialize string: :string', array(':string' => $string));
		}

		// Foreach key/value pair
		foreach ($unserialized as $key => $value)
		{
			$this->$key = $value;
		}

		return TRUE;
	}

	/**
	 * Parse the byte ranges from the HTTP_RANGE header used for
	 * resumable downloads.
	 *
	 * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.35
	 * @return array|FALSE
	 */
	protected function _parse_byte_range()
	{
		if ( ! isset($_SERVER['HTTP_RANGE']))
		{
			return FALSE;
		}

		// TODO, speed this up with the use of string functions.
		preg_match_all('/(-?[0-9]++(?:-(?![0-9]++))?)(?:-?([0-9]++))?/', $_SERVER['HTTP_RANGE'], $matches, PREG_SET_ORDER);

		return $matches[0];
	}

	/**
	 * Calculates the byte range to use with send_file. If HTTP_RANGE doesn't
	 * exist then the complete byte range is returned
	 *
	 * @param  integer $size
	 * @return array
	 */
	protected function _calculate_byte_range($size)
	{
		// Defaults to start with when the HTTP_RANGE header doesn't exist.
		$start = 0;
		$end = $size - 1;

		if ($range = $this->_parse_byte_range())
		{
			// We have a byte range from HTTP_RANGE
			$start = $range[1];

			if ($start[0] === '-')
			{
				// A negative value means we start from the end, so -500 would be the
				// last 500 bytes.
				$start = $size - abs($start);
			}

			if (isset($range[2]))
			{
				// Set the end range
				$end = $range[2];
			}
		}

		// Normalize values.
		$start = abs(intval($start));

		// Keep the the end value in bounds and normalize it.
		$end = min(abs(intval($end)), $size - 1);

		// Keep the start in bounds.
		$start = ($end < $start) ? 0 : max($start, 0);

		return array($start, $end);
	}
} // End Kohana_Response

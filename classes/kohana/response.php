<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Response implements Serializable {

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
	 * @param   array    setup the response object
	 * @return  [Kohana_Response]
	 */
	public static function factory(array $config = array())
	{
		return new Response($config);
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
	 * @var  integer     the response http status
	 */
	public $status = 200;

	/**
	 * @var  array       headers returned in the response
	 */
	public $headers = array();

	/**
	 * @var  string      the response body
	 */
	public $body = NULL;

	/**
	 * @var  array       cookies to be returned in the response
	 */
	protected $_cookies = array();

	/**
	 * Sets up the response object
	 *
	 * @param   array $config 
	 * @return  void
	 */
	public function __construct(array $config = array())
	{
		foreach ($config as $key => $value)
		{
			if (property_exists($this, $key))
			{
				$this->$key = $value;
			}
		}

		// Add the default Content-Type header if required
		$this->headers['Content-Type'] += array('Content-Type' => 'text/html; charset='.Kohana::$charset);
	}

	/**
	 * Outputs the body when cast to string
	 *
	 * @return void
	 */
	public function __toString()
	{
		return (string) $this->body;
	}

	/**
	 * Returns the body of the response
	 *
	 * @return  string
	 */
	public function body()
	{
		return (string) $this->body;
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
	 * @param   integer  status to set to this response
	 * @return  integer|self
	 */
	public function status($status = NULL)
	{
		if ($status === NULL)
		{
			return $this->status;
		}
		else if (array_key_exists($status, Response::$messages))
		{
			$this->status = (int) $status;
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
	 * @param string $key 
	 * @param string $value 
	 * @return void
	 */
	public function headers($key = NULL, $value = NULL)
	{
		if ($key === NULL)
		{
			return $this->headers;
		}
		else if (is_array($key))
		{
			$this->headers = $key;
			return $this;
		}
		else if ($value === NULL)
		{
			return $this->headers[$key];
		}
		else
		{
			$this->headers[$key] = $value;
			return $this;
		}
	}

	/**
	 * Sets a cookie to the response
	 *
	 * @param   string   name 
	 * @param   string   value 
	 * @param   int      expiration 
	 * @return  self
	 */
	public function set_cookie($name, $value, $expiration = NULL)
	{
		if ($expiration === NULL)
		{
			$expiration = Cookie::$expiration;
		}
		else if ($expiration !== 0)
		{
			$expiration += time();
		}

		$this->_cookies[$name] = array(
			'value'      => $value,
			'expiration' => $expiration
		);

		return $this;
	}

	/**
	 * Returns a cookie by name
	 *
	 * @param   string $name 
	 * @param   string $default 
	 * @return  mixed
	 */
	public function get_cookie($name, $default = NULL)
	{
		return isset($this->_cookies[$name]) ? $this->_cookies[$name]['value'] : $default;
	}

	/**
	 * Get all the cookies
	 *
	 * @return  array
	 */
	public function get_cookies()
	{
		return $this->_cookies;
	}

	/**
	 * Deletes a cookie set to the response
	 *
	 * @param   string   name 
	 * @return  self
	 */
	public function delete_cookie($name)
	{
		if (isset($this->_cookies[$name]))
		{
			unset($this->_cookies[$name]);
		}

		return $this;
	}

	/**
	 * Deletes all cookies from this response
	 *
	 * @return  self
	 */
	public function delete_cookies()
	{
		$this->_cookies = array();
		return $this;
	}

	/**
	 * Sends the response status and all set headers.
	 *
	 * @return  $this
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
				$protocol = 'HTTP/1.1';
			}

			// HTTP status line
			header($protocol.' '.$this->status.' '.Response::$messages[$this->status]);

			foreach ($this->headers as $name => $value)
			{
				if (is_string($name))
				{
					// Combine the name and value to make a raw header
					$value = "{$name}: {$value}";
				}

				// Send the raw header
				header($value, TRUE);
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
	 *
	 * @param   string   filename with path, or TRUE for the current response
	 * @param   string   download file name
	 * @param   array    additional options
	 * @return  void
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

			if ( ! isset($mime))
			{
				// Guess the mime using the file extension
				$mime = File::mime_by_ext($download);
			}

			// Get the content size
			$size = strlen($this->response);

			// Create a temporary file to hold the current response
			$file = tmpfile();

			// Write the current response into the file
			fwrite($file, $this->response);

			// Prepare the file for reading
			fseek($file, 0);
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

		// Inline or download?
		$disposition = empty($options['inline']) ? 'attachment' : 'inline';

		// Set the headers for a download
		$this->headers['Content-Disposition'] = $disposition.'; filename="'.$download.'"';
		$this->headers['Content-Type']        = $mime;
		$this->headers['Content-Length']      = $size;

		if ( ! empty($options['resumable']))
		{
			// @todo: ranged download processing
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

		// Keep the script running forever
		set_time_limit(0);

		// Send data in 16kb blocks
		$block = 1024 * 16;

		while ( ! feof($file))
		{
			if (connection_aborted())
				break;

			// Output a block of the file
			echo fread($file, $block);

			// Send the data now
			flush();
		}

		// Close the file
		fclose($file);

		// Stop execution
		exit;
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
	    if ($this->response === NULL)
		{
			throw new Kohana_Request_Exception('No response yet associated with request - cannot auto generate resource ETag');
		}

		// Generate a unique hash for the response
		return '"'.sha1($this->body).'"';
	}

	/**
	 * Check Cache
	 * Checks the browser cache to see the response needs to be returned
	 *
	 * @param String Resource ETag
	 * @throws Kohana_Request_Exception
	 * @chainable
	 */
	public function check_cache($etag = null)
	{
		if (empty($etag))
		{
			$etag = $this->generate_etag();
		}

		// Set the ETag header
		$this->headers['ETag'] = $etag;

		// Add the Cache-Control header if it is not already set
		// This allows etags to be used with Max-Age, etc
		$this->headers += array(
			'Cache-Control' => 'must-revalidate',
		);

		if (isset($_SERVER['HTTP_IF_NONE_MATCH']) AND $_SERVER['HTTP_IF_NONE_MATCH'] === $etag)
		{
			// No need to send data again
			$this->status = 304;
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
	public function serialize(array $toSerialize = array())
	{
		// Serialize the class properties
		$toSerialize += array
		(
			'status'  => $this->status,
			'headers' => $this->headers,
			'cookies' => $this->_cookies,
			'body'    => $this->body
		);

		$string = json_encode($toSerialize);

		if (is_string($string))
		{
			return $string;
		}
		else
		{
			throw new Kohana_Exception('Unable to correctly encode object to json');
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
		$unserialized = json_decode($string);

		// If failed
		if ($unserialized === NULL)
		{
			// Throw exception
			throw new Kohana_Exception('Unable to correctly decode object from json');
		}

		// Foreach key/value pair
		foreach ($unserialized as $key => $value)
		{
			// If it belongs here
			if (property_exists($this, $key))
			{
				// Apply it
				$this->$key = $value;
			}
		}

		return TRUE;
	}
}
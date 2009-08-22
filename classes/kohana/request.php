<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Request and response wrapper.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Kohana_Request {

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
	 * @var  string  method: GET, POST, PUT, DELETE, etc
	 */
	public static $method = 'GET';

	/**
	 * @var  string  protocol: http, https, ftp, cli, etc
	 */
	public static $protocol = 'http';

	/**
	 * @var  string  referring URL
	 */
	public static $referrer;

	/**
	 * @var  string  client user agent
	 */
	public static $user_agent = '';

	/**
	 * @var  string  client IP address
	 */
	public static $client_ip = '0.0.0.0';

	/**
	 * @var  boolean  AJAX-generated request
	 */
	public static $is_ajax = FALSE;

	/**
	 * Main request singleton instance. If no URI is provided, the URI will
	 * be automatically detected using PATH_INFO, REQUEST_URI, or PHP_SELF.
	 *
	 * @param   string   URI of the request
	 * @return  Request
	 */
	public static function instance( & $uri = TRUE)
	{
		static $instance;

		if ($instance === NULL)
		{
			if (Kohana::$is_cli)
			{
				// Default protocol for command line is cli://
				Request::$protocol = 'cli';

				// Get the command line options
				$options = CLI::options('uri', 'method', 'get', 'post');

				if (isset($options['uri']))
				{
					// Use the specified URI
					$uri = $options['uri'];
				}

				if (isset($options['method']))
				{
					// Use the specified method
					Request::$method = strtoupper($options['method']);
				}

				if (isset($options['get']))
				{
					// Overload the global GET data
					parse_str($options['get'], $_GET);
				}

				if (isset($options['post']))
				{
					// Overload the global POST data
					parse_str($options['post'], $_POST);
				}
			}
			else
			{
				if (isset($_SERVER['REQUEST_METHOD']))
				{
					// Use the server request method
					Request::$method = $_SERVER['REQUEST_METHOD'];
				}

				if ( ! empty($_SERVER['HTTPS']) AND filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN))
				{
					// This request is secure
					Request::$protocol = 'https';
				}

				if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
				{
					// This request is an AJAX request
					Request::$is_ajax = TRUE;
				}

				if (isset($_SERVER['HTTP_REFERER']))
				{
					// There is a referrer for this request
					Request::$referrer = $_SERVER['HTTP_REFERER'];
				}

				if (isset($_SERVER['HTTP_USER_AGENT']))
				{
					// Set the client user agent
					Request::$user_agent = $_SERVER['HTTP_USER_AGENT'];
				}

				if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
				{
					// Use the forwarded IP address, typically set when the
					// client is using a proxy server.
					Request::$client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
				}
				elseif (isset($_SERVER['HTTP_CLIENT_IP']))
				{
					// Use the forwarded IP address, typically set when the
					// client is using a proxy server.
					Request::$client_ip = $_SERVER['HTTP_CLIENT_IP'];
				}
				elseif (isset($_SERVER['REMOTE_ADDR']))
				{
					// The remote IP address
					Request::$client_ip = $_SERVER['REMOTE_ADDR'];
				}

				if (Request::$method !== 'GET' AND Request::$method !== 'POST')
				{
					// Methods besides GET and POST do not properly parse the form-encoded
					// query string into the $_POST array, so we overload it manually.
					parse_str(file_get_contents('php://input'), $_POST);
				}

				if ($uri === TRUE)
				{
					if (isset($_SERVER['PATH_INFO']))
					{
						// PATH_INFO does not contain the docroot or index
						$uri = $_SERVER['PATH_INFO'];
					}
					else
					{
						// REQUEST_URI and PHP_SELF include the docroot and index

						if (isset($_SERVER['REQUEST_URI']))
						{
							// REQUEST_URI includes the query string, remove it
							$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
						}
						elseif (isset($_SERVER['PHP_SELF']))
						{
							$uri = $_SERVER['PHP_SELF'];
						}
						else
						{
							// If you ever see this error, please report an issue at and include a dump of $_SERVER
							// http://dev.kohanaphp.com/projects/kohana3/issues
							throw new Kohana_Exception('Unable to detect the URI using PATH_INFO, REQUEST_URI, or PHP_SELF');
						}

						// Get the path from the base URL, including the index file
						$base_url = parse_url(Kohana::$base_url, PHP_URL_PATH);

						if (strpos($uri, $base_url) === 0)
						{
							// Remove the base URL from the URI
							$uri = substr($uri, strlen($base_url));
						}

						if (Kohana::$index_file AND strpos($uri, Kohana::$index_file) === 0)
						{
							// Remove the index file from the URI
							$uri = substr($uri, strlen(Kohana::$index_file));
						}
					}
				}
			}

			// Reduce multiple slashes to a single slash
			$uri = preg_replace('#//+#', '/', $uri);

			// Remove all dot-paths from the URI, they are not valid
			$uri = preg_replace('#\.[\s./]*/#', '', $uri);

			// Create the instance singleton
			$instance = new Request($uri);

			// Add the Content-Type header
			$instance->headers['Content-Type'] = 'text/html; charset='.Kohana::$charset;
		}

		return $instance;
	}

	/**
	 * Creates a new request object for the given URI.
	 *
	 * @param   string  URI of the request
	 * @return  Request
	 */
	public static function factory($uri)
	{
		return new Request($uri);
	}

	/**
	 * Returns information about the client user agent.
	 *
	 * @param   string  value to return: browser, version, robot, mobile, platform
	 * @return  string  requested information
	 * @return  FALSE   no information found
	 */
	public static function user_agent($value)
	{
		static $info;

		if (isset($info[$value]))
		{
			// This value has already been found
			return $info[$value];
		}

		if ($value === 'browser' OR $value == 'version')
		{
			// Load browsers
			$browsers = Kohana::config('user_agents')->browser;

			foreach ($browsers as $search => $name)
			{
				if (stripos(Request::$user_agent, $search) !== FALSE)
				{
					// Set the browser name
					$info['browser'] = $name;

					if (preg_match('#'.preg_quote($search).'[^0-9.]*+([0-9.][0-9.a-z]*)#i', Request::$user_agent, $matches))
					{
						// Set the version number
						$info['version'] = $matches[1];
					}
					else
					{
						// No version number found
						$info['version'] = FALSE;
					}

					return $info[$value];
				}
			}
		}
		else
		{
			// Load the search group for this type
			$group = Kohana::config('user_agents')->$value;

			foreach ($group as $search => $name)
			{
				if (stripos(Request::$user_agent, $search) !== FALSE)
				{
					// Set the value name
					return $info[$value] = $name;
				}
			}
		}

		// The value requested could not be found
		return $info[$value] = FALSE;
	}

	/**
	 * Returns the accepted content types. If a specific type is defined,
	 * the quality of that type will be returned.
	 *
	 * @param   string  content MIME type
	 * @return  float   when checking a specific type
	 * @return  array
	 */
	public static function accept_type($type = NULL)
	{
		static $accepts;

		if ($accepts === NULL)
		{
			// Parse the HTTP_ACCEPT header
			$accepts = Request::_parse_accept($_SERVER['HTTP_ACCEPT'], array('*/*' => 1.0));
		}

		if (isset($type))
		{
			// Return the quality setting for this type
			return isset($accepts[$type]) ? $accepts[$type] : $accepts['*/*'];
		}

		return $accepts;
	}

	/**
	 * Returns the accepted languages. If a specific language is defined,
	 * the quality of that language will be returned. If the language is not
	 * accepted, FALSE will be returned.
	 *
	 * @param   string  language code
	 * @return  float   when checking a specific language
	 * @return  array
	 */
	public static function accept_lang($lang = NULL)
	{
		static $accepts;

		if ($accepts === NULL)
		{
			// Parse the HTTP_ACCEPT_LANGUAGE header
			$accepts = Request::_parse_accept($_SERVER['HTTP_ACCEPT_LANGUAGE']);
		}

		if (isset($lang))
		{
			// Return the quality setting for this lang
			return isset($accepts[$lang]) ? $accepts[$lang] : FALSE;
		}

		return $accepts;
	}

	/**
	 * Returns the accepted encodings. If a specific encoding is defined,
	 * the quality of that encoding will be returned. If the encoding is not
	 * accepted, FALSE will be returned.
	 *
	 * @param   string  encoding type
	 * @return  float   when checking a specific encoding
	 * @return  array
	 */
	public static function accept_encoding($type = NULL)
	{
		static $accepts;

		if ($accepts === NULL)
		{
			// Parse the HTTP_ACCEPT_LANGUAGE header
			$accepts = Request::_parse_accept($_SERVER['HTTP_ACCEPT_ENCODING']);
		}

		if (isset($type))
		{
			// Return the quality setting for this type
			return isset($accepts[$type]) ? $accepts[$type] : FALSE;
		}

		return $accepts;
	}

	/**
	 * Parses an accept header and returns an array (type => quality) of the
	 * accepted types, ordered by quality.
	 *
	 * @param   string   header to parse
	 * @param   array    default values
	 * @return  array
	 */
	protected static function _parse_accept( & $header, array $accepts = NULL)
	{
		if ( ! empty($header))
		{
			// Get all of the types
			$types = explode(',', $header);

			foreach ($types as $type)
			{
				// Split the type into parts
				$parts = explode(';', $type);

				// Make the type only the MIME
				$type = trim(array_shift($parts));

				// Default quality is 1.0
				$quality = 1.0;

				foreach ($parts as $part)
				{
					// Prevent undefined $value notice below
					if (strpos($part, '=') === FALSE)
						continue;

					// Separate the key and value
					list ($key, $value) = explode('=', trim($part));

					if ($key === 'q')
					{
						// There is a quality for this type
						$quality = (float) trim($value);
					}
				}

				// Add the accept type and quality
				$accepts[$type] = $quality;
			}
		}

		// Make sure that accepts is an array
		$accepts = (array) $accepts;

		// Order by quality
		arsort($accepts);

		return $accepts;
	}

	/**
	 * @var  object  route matched for this request
	 */
	public $route;

	/**
	 * @var  integer  HTTP response code: 200, 404, 500, etc
	 */
	public $status = 200;

	/**
	 * @var  string  response body
	 */
	public $response = '';

	/**
	 * @var  array  headers to send with the response body
	 */
	public $headers = array();

	/**
	 * @var  string  controller directory
	 */
	public $directory = '';

	/**
	 * @var  string  controller to be executed
	 */
	public $controller;

	/**
	 * @var  string  action to be executed in the controller
	 */
	public $action;

	/**
	 * @var  string  the URI of the request
	 */
	public $uri;

	// Parameters extracted from the route
	protected $_params;

	/**
	 * Creates a new request object for the given URI. Global GET and POST data
	 * can be overloaded by setting "get" and "post" in the parameters.
	 * Throws an exception when no route can be found for the URI.
	 *
	 * @throws  Kohana_Request_Exception
	 * @param   string  URI of the request
	 * @return  void
	 */
	public function __construct($uri)
	{
		// Remove trailing slashes from the URI
		$uri = trim($uri, '/');

		// Load routes
		$routes = Route::all();

		foreach ($routes as $name => $route)
		{
			if ($params = $route->matches($uri))
			{
				// Store the URI
				$this->uri = $uri;

				// Store the matching route
				$this->route = $route;

				if (isset($params['directory']))
				{
					// Controllers are in a sub-directory
					$this->directory = $params['directory'];
				}

				// Store the controller
				$this->controller = $params['controller'];

				if (isset($params['action']))
				{
					// Store the action
					$this->action = $params['action'];
				}

				// These are accessible as public vars and can be overloaded
				unset($params['controller'], $params['action'], $params['directory']);

				// Params cannot be changed once matched
				$this->_params = $params;

				return;
			}
		}

		// No matching route for this URI
		$this->status = 404;

		throw new Kohana_Request_Exception('Unable to find a route to match the URI: :uri',
			array(':uri' => $uri));
	}

	/**
	 * Returns the response as the string representation of a request.
	 *
	 * @return  string
	 */
	public function __toString()
	{
		return (string) $this->response;
	}

	/**
	 * Generates a relative URI for the current route.
	 *
	 * @param   array   additional route parameters
	 * @return  string
	 */
	public function uri(array $params = NULL)
	{
		if ( ! isset($params['controller']))
		{
			// Add the current controller
			$params['controller'] = $this->controller;
		}

		if ( ! isset($params['action']))
		{
			// Add the current action
			$params['action'] = $this->action;
		}

		// Add the current parameters
		$params += $this->_params;

		return $this->route->uri($params);
	}

	/**
	 * Retrieves a value from the route parameters.
	 *
	 * @param   string   key of the value
	 * @param   mixed    default value if the key is not set
	 * @return  mixed
	 */
	public function param($key = NULL, $default = NULL)
	{
		if ($key === NULL)
		{
			// Return the full array
			return $this->_params;
		}

		return isset($this->_params[$key]) ? $this->_params[$key] : $default;
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
			foreach ($this->headers as $name => $value)
			{
				if (is_string($name))
				{
					// Combine the name and value to make a raw header
					$value = "{$name}: {$value}";
				}

				// Send the raw header
				header($value, TRUE, $this->status);
			}
		}

		return $this;
	}

	/**
	 * Redirects as the request response.
	 *
	 * @param   string   redirect location
	 * @param   integer  status code
	 * @return  void
	 */
	public function redirect($url, $code = 302)
	{
		if (strpos($url, '://') === FALSE)
		{
			// Make the URI into a URL
			$url = URL::site($url);
		}

		// Set the response status
		$this->status = $code;

		// Set the location header
		$this->headers['Location'] = $url;

		// Send headers
		$this->send_headers();

		// Stop execution
		exit;
	}

	/**
	 * Send file download as the response. All execution will be halted when
	 * this method is called! Use TRUE for the filename to send the current
	 * response as the file content.
	 *
	 * @param   string   filename with path, or TRUE for the current response
	 * @param   string   download file name
	 * @param   boolean  allow the download to be resumed
	 * @return  void
	 */
	public function send_file($filename, $download = NULL, $resumable = FALSE)
	{
		if ($filename === TRUE)
		{
			if (empty($download))
			{
				throw new Kohana_Exception('Download name must be provided for streaming files');
			}

			// Get the content size
			$size = strlen($this->response);

			// Get the extension of the download
			$extension = strtolower(pathinfo($download, PATHINFO_EXTENSION));

			// Guess the mime using the file extension
			$mime = Kohana::config('mimes');
			$mime = $mime[$extension][0];

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

			// Get the mime type
			$mime = File::mime($filename);

			// Open the file for reading
			$file = fopen($filename, 'rb');
		}

		// Set the headers for a download
		$this->headers['Content-Disposition'] = 'attachment; filename="'.$download.'"';
		$this->headers['Content-Type']        = $mime;
		$this->headers['Content-Length']      = $size;

		// Set the starting offset and length to send
		$ranges = NULL;

		if ($resumable === TRUE)
		{
			if (isset($_SERVER['HTTP_RANGE']))
			{
				// @todo: ranged download processing
			}

			// Accept accepted range type
			$this->headers['Accept-Ranges'] = 'bytes';
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
	 * Processes the request, executing the controller. Before the routed action
	 * is run, the before() method will be called, which allows the controller
	 * to overload the action based on the request parameters. After the action
	 * is run, the after() method will be called, for post-processing.
	 *
	 * By default, the output from the controller is captured and returned, and
	 * no headers are sent.
	 *
	 * @return  $this
	 */
	public function execute()
	{
		// Create the class prefix
		$prefix = 'controller_';

		if ( ! empty($this->directory))
		{
			// Add the directory name to the class prefix
			$prefix .= str_replace(array('\\', '/'), '_', trim($this->directory, '/')).'_';
		}

		if (Kohana::$profiling === TRUE)
		{
			// Start benchmarking
			$benchmark = Profiler::start('Requests', $this->uri);
		}

		try
		{
			// Load the controller using reflection
			$class = new ReflectionClass($prefix.$this->controller);

			if ($class->isAbstract())
			{
				throw new Kohana_Exception('Cannot create instances of abstract :controller',
					array(':controller' => $prefix.$controller));
			}

			// Create a new instance of the controller
			$controller = $class->newInstance($this);

			// Execute the "before action" method
			$class->getMethod('before')->invoke($controller);

			// Determine the action to use
			$action = empty($this->action) ? Route::$default_action : $this->action;

			// Execute the main action with the parameters
			$class->getMethod('action_'.$action)->invokeArgs($controller, $this->_params);

			// Execute the "after action" method
			$class->getMethod('after')->invoke($controller);
		}
		catch (Exception $e)
		{
			if (isset($benchmark))
			{
				// Delete the benchmark, it is invalid
				Profiler::delete($benchmark);
			}

			if ($e instanceof ReflectionException)
			{
				// Reflection will throw exceptions for missing classes or actions
				$this->status = 404;
			}
			else
			{
				// All other exceptions are PHP/server errors
				$this->status = 500;
			}

			// Re-throw the exception
			throw $e;
		}

		if (isset($benchmark))
		{
			// Stop the benchmark
			Profiler::stop($benchmark);
		}

		return $this;
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
		return '"'.sha1($this->response).'"';
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

} // End Request

<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Request_Client_External extends Request_Client {

	/**
	 * @var     array     internal header cache for curl processing
	 * @todo    remove in PHP 5.3, use Lambda instead
	 */
	protected static $_processed_headers = array();

	/**
	 * Parses the returned headers from the remote
	 * request
	 *
	 * @param   resource $remote  The curl resource
	 * @param   string   $header  The full header string
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
			Request_Client_External::$_processed_headers += $headers;
		}

		return strlen($header);
	}

	/**
	 * @var     array     additional curl options to use on execution
	 */
	protected $_options = array();

	/**
	 * Processes the request, executing the controller action that handles this
	 * request, determined by the [Route].
	 *
	 * 1. Before the controller action is called, the [Controller::before] method
	 * will be called.
	 * 2. Next the controller action will be called.
	 * 3. After the controller action is called, the [Controller::after] method
	 * will be called.
	 *
	 * By default, the output from the controller is captured and returned, and
	 * no headers are sent.
	 *
	 *     $request->execute();
	 *
	 * @param   Request $request A request object
	 * @return  Response
	 * @throws  Kohana_Exception
	 * @uses    [Kohana::$profiling]
	 * @uses    [Profiler]
	 */
	public function execute(Request $request)
	{
		// Check for cache existance
		if ($this->_cache instanceof Cache AND ($response = $this->_cache->cache_response($request)) instanceof Response)
			return $response;

		$previous = Request::$current;
		Request::$current = $request;

		if (Kohana::$profiling)
		{
			// Set the benchmark name
			$benchmark = '"'.$request->uri().'"';

			if ($request !== Request::$initial AND Request::$current)
			{
				// Add the parent request uri
				$benchmark .= ' « "'.Request::$current->uri().'"';
			}

			// Start benchmarking
			$benchmark = Profiler::start('Requests', $benchmark);
		}

		// If PECL_HTTP is present, use extension to complete request
		if (extension_loaded('http'))
		{
			$this->_http_execute($request);
		}
		// Else if CURL is present, use extension to complete request
		elseif (extension_loaded('curl'))
		{
			$this->_curl_execute($request);
		}
		// Else use the sloooow method
		else
		{
			$this->_native_execute($request);
		}

		if (isset($benchmark))
		{
			// Stop the benchmark
			Profiler::stop($benchmark);
		}

		Request::$current = $previous;

		// Cache the response if cache is available
		if ($this->_cache instanceof Cache)
		{
			$this->cache_response($request, $request->response());
		}

		// Return the response
		return $request->response();
	}

	/**
	 * Execute the request using the PECL HTTP extension. (recommended)
	 *
	 * @param   Request   $request Request to execute
	 * @return  Response
	 */
	protected function _http_execute(Request $request)
	{
		$http_method_mapping = array(
			Http_Request::GET     => HttpRequest::METH_GET,
			Http_Request::HEAD    => HttpRequest::METH_HEAD,
			Http_Request::POST    => HttpRequest::METH_POST,
			Http_Request::PUT     => HttpRequest::METH_PUT,
			Http_Request::DELETE  => HttpRequest::METH_DELETE,
			Http_Request::OPTIONS => HttpRequest::METH_OPTIONS,
			Http_Request::TRACE   => HttpRequest::METH_TRACE,
			Http_Request::CONNECT => HttpRequest::METH_CONNECT,
		);

		// Create an http request object
		$http_request = new HttpRequest($request->uri(), $http_method_mapping[$request->method()]);

		// Set headers
		$http_request->setHeaders($request->headers()->getArrayCopy());

		// Set cookies
		$http_request->setCookies($request->cookie());

		// Set body
		$http_request->setBody($request->body());

		try
		{
			$http_request->send();
		}
		catch (HttpRequestException $e)
		{
			throw new Kohana_Request_Exception($e->getMessage());
		}
		catch (HttpMalformedHeaderException $e)
		{
			throw new Kohana_Request_Exception($e->getMessage());
		}
		catch (HttpEncodingException $e)
		{
			throw new Kohana_Request_Exception($e->getMessage());
		}

		// Create the response
		$response = $request->create_response();

		// Build the response
		$response->status($http_request->getResponseCode())
			->headers($http_request->getResponseHeader())
			->cookie($http_request->getResponseCookies())
			->body($http_request->getResponseBody());

		return $response;
	}

	/**
	 * Execute the request using the CURL extension. (recommended)
	 *
	 * @param   Request   $request  Request to execute
	 * @return  Response
	 */
	protected function _curl_execute(Request $request)
	{
		// Reset the headers
		Request_Client_External::$_processed_headers = array();

		// Load the default remote settings
		$defaults = Kohana::config('remote')->as_array();

		if ( ! $this->_options)
		{
			// Use default options
			$options = $defaults;
		}
		else
		{
			// Add default options
			$options = $options + $defaults;
		}

		// Process cookies
		if ($cookies = $request->cookie())
		{
			$options[CURLOPT_COOKIE] = http_build_query($cookies, NULL, '; ');
		}

		// The transfer must always be returned
		$options[CURLOPT_RETURNTRANSFER] = TRUE;

		// Open a new remote connection
		$curl = curl_init($request->uri());

		// Set connection options
		if ( ! curl_setopt_array($curl, $options))
		{
			throw new Kohana_Request_Exception('Failed to set CURL options, check CURL documentation: :url',
				array(':url' => 'http://php.net/curl_setopt_array'));
		}

		// Get the response body
		$body = curl_exec($curl);

		// Get the response information
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if ($body === FALSE)
		{
			$error = curl_error($curl);
		}

		// Close the connection
		curl_close($curl);

		if (isset($error))
		{
			throw new Kohana_Request_Exception('Error fetching remote :url [ status :code ] :error',
				array(':url' => $request->url(), ':code' => $code, ':error' => $error));
		}

		// Create response
		$response = $request->create_response();

		$response->status($code)
			->headers(Request_Client_External::$_processed_headers)
			->body($body);

		return $response;
	}

	/**
	 * Execute the request using PHP stream. (not recommended)
	 *
	 * @param   Request   $request  Request to execute
	 * @return  Response
	 */
	protected function _native_execute(Request $request)
	{
		// Reset the headers
		Request_Client_External::$_processed_headers = array();

		// Calculate stream mode
		$mode = ($request->method() === Http_Request::GET) ? 'r' : 'r+';

		// Process cookies
		if ($cookies = $request->cookie())
		{
			$request->headers('cookie', http_build_query($cookies, NULL, '; '));
		}

		// Create the context
		$options = array(
			$request->protocol() => array(
				'method'     => $request->method(),
				'header'     => (string) $request->headers(),
				'content'    => $request->body(),
				'user-agent' => 'Kohana Framework '.Kohana::VERSION.' ('.Kohana::CODENAME.')'
			)
		);

		// Create the context stream
		$context = stream_context_create($options);

		$stream = fopen($request->uri(), $mode, FALSE, $context);

		$meta_data = stream_get_meta_data($stream);

		// Get the HTTP response code
		$http_response = array_shift($meta_data['wrapper_data']);

		if (preg_match_all('/(\w+\/\d\.\d) (\d{3})/', $http_response, $matches) !== FALSE)
		{
			$protocol = $matches[1][0];
			$status   = (int) $matches[2][0];
		}
		else
		{
			$protocol = NULL;
			$status   = NULL;
		}

		// Process headers
		array_map(array('Request_Client_External', '_parse_headers'), array(), $meta_data['wrapper_data']);

		// Create a response
		$response = $request->create_response();

		$response->status($status)
			->protocol($protocol)
			->headers(Request_Client_External::$_processed_headers)
			->body(stream_get_contents($stream));

		// Close the stream after use
		fclose($stream);

		return $response;
	}
} // End Kohana_Request_Client_External
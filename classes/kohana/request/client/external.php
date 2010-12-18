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
	 * @param   Request
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

		// If PECL_HTTP is present, use extension to complete request
		if (extension_loaded('http'))
		{
			$this->_http_execute($request);
		}
		// Else if CURL is present, use extension to complete request
		else if (extension_loaded('curl'))
		{
			$this->_curl_execute($request);
		}
		// Else use the sloooow method
		else
		{
			
		}

		// Cache the response if cache is available
		if ($this->_cache instanceof Cache)
			$this->cache_response($request, $request->response());

		// Return the response
		return $request->response();
	}

	/**
	 * Execute the request using the PECL HTTP extension. (recommended)
	 *
	 * @param   Request   request to execute
	 * @return  void
	 */
	protected function _http_execute(Request $request)
	{
		// Create an http request object
		$http_request = new HttpRequest($request->uri(), $request->method());

		// Set headers
		$http_request->setHeaders($request->headers()->getArrayCopy());

		// Set cookies
		$http_request->setCookies($request->cookies());

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
	}

	/**
	 * Execute the request using the CURL extension. (recommended)
	 *
	 * @param   Request   request to execute
	 * @return  void
	 */
	protected function _curl_execute(Request $request)
	{
		/**
		 * @todo Port Kohana_Remote into this class and
		 * remove Kohana_Remote
		 */
	}

	/**
	 * Execute the request using PHP stream. (not recommended)
	 *
	 * @param   Request   request to execute
	 * @return  void
	 */
	protected function _native_execute(Request $request)
	{
		/**
		 * @todo Use streams to implement remote execution natively
		 */
	}
} // End Kohana_Request_Client_External
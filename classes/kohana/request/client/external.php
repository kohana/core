<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
abstract class Kohana_Request_Client_External extends Request_Client {

	/**
	 * Use:
	 *  - Request_Client_Curl (default)
	 *  - Request_Client_HTTP
	 *  - Request_Client_Stream
	 * 
	 * @var     string    defines the external client to use by default
	 */
	public static $client = 'Request_Client_Curl';

	/**
	 * Factory method to create a new Request_Client_External object based on
	 * the client name passed, or defaulting to Request_Client_External::$client
	 * by default.
	 * 
	 * Request_Client_External::$client can be set in the application bootstrap.
	 *
	 * @param   array     parameters to pass to the client
	 * @param   string    external client to use
	 * @return  Request_Client_External
	 * @throws  Request_Exception
	 */
	public static function factory(array $params = array(), $client = NULL)
	{
		if ($client === NULL)
		{
			$client = Request_Client_External::$client;
		}

		$client = new $client($params);

		if ( ! $client instanceof Request_Client_External)
		{
			throw new Request_Exception('Selected client is not a Request_Client_External object.');
		}

		return $client;
	}

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
		if ($this->_cache instanceof Cache AND ($response = $this->cache_response($request)) instanceof Response)
			return $response;

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

		// Store the current active request and replace current with new request
		$previous = Request::$current;
		Request::$current = $request;

		// Resolve the POST fields
		if ($post = $request->post())
		{
			$request->body(http_build_query($post, NULL, '&'))
				->headers('content-type', 'application/x-www-form-urlencoded');
		}

		try
		{
			$response = $this->_send_message($request);
		}
		catch (Exception $e)
		{
			// Restore the previous request
			Request::$current = $previous;

			if (isset($benchmark))
			{
				// Delete the benchmark, it is invalid
				Profiler::delete($benchmark);
			}

			// Re-throw the exception
			throw $e;
		}

		// Restore the previous request
		Request::$current = $previous;

		if (isset($benchmark))
		{
			// Stop the benchmark
			Profiler::stop($benchmark);
		}

		// Cache the response if cache is available
		if ($this->_cache instanceof Cache)
		{
			$this->cache_response($request, $request->response());
		}

		// Return the response
		return $response;
	}

	/**
	 * Sends the HTTP message [Request] to a remote server and processes
	 * the response.
	 *
	 * @param   Request   request to send
	 * @return  Response
	 */
	abstract protected function _send_message(Request $request);

} // End Kohana_Request_Client_External
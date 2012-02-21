<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Request Client. Processes a [Request] and handles [HTTP_Caching] if 
 * available. Will usually return a [Response] object as a result of the
 * request unless an unexpected error occurs.
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 * @since      3.1.0
 */
abstract class Kohana_Request_Client {

	/**
	 * @var    Cache  Caching library for request caching
	 */
	protected $_cache;

	/**
	 * @var  bool  Should redirects be followed?
	 */
	protected $_follow = FALSE;

	/**
	 * @var  array  Headers to preserve when following a redirect
	 */
	protected $_follow_headers = array('Authorization');

	/**
	 * Creates a new `Request_Client` object,
	 * allows for dependency injection.
	 *
	 * @param   array    $params Params
	 */
	public function __construct(array $params = array())
	{
		foreach ($params as $key => $value)
		{
			if (method_exists($this, $key))
			{
				$this->$key($value);
			}
		}
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
	 * @param   Request   $request
	 * @param   Response  $response
	 * @return  Response
	 * @throws  Kohana_Exception
	 * @uses    [Kohana::$profiling]
	 * @uses    [Profiler]
	 */
	public function execute(Request $request)
	{
		$response = Response::factory();

		if ($this->_cache instanceof HTTP_Cache)
			return $this->_cache->execute($this, $request, $response);

		$response = $this->execute_request($request, $response);

		// Do we need to follow a Location header ?
		if ($this->_follow AND in_array($response->status(), array(201, 301, 302, 303, 307))
			AND $response->headers('Location'))
		{
			// Figure out which method to use for the follow request
			switch ($response->status())
			{
				default:
				case 301:
				case 302:
				case 307:
					$follow_method = $request->method();
					break;
				case 201:
				case 303:
					$follow_method = Request::GET;
					break;
			}

			// Prepare the additional request
			$follow_request = Request::factory($response->headers('Location'))
			                         ->method($follow_method)
			                         ->headers(Arr::extract($request->headers(), $this->_follow_headers));

			// Execute the additional request
			$response = $follow_request->execute();
		}

		return $response;
	}

	/**
	 * Processes the request passed to it and returns the response from
	 * the URI resource identified.
	 * 
	 * This method must be implemented by all clients.
	 *
	 * @param   Request   $request   request to execute by client
	 * @param   Response  $response
	 * @return  Response
	 * @since   3.2.0
	 */
	abstract public function execute_request(Request $request, Response $response);

	/**
	 * Getter and setter for the internal caching engine,
	 * used to cache responses if available and valid.
	 *
	 * @param   HTTP_Cache  $cache  engine to use for caching
	 * @return  HTTP_Cache
	 * @return  Request_Client
	 */
	public function cache(HTTP_Cache $cache = NULL)
	{
		if ($cache === NULL)
			return $this->_cache;

		$this->_cache = $cache;
		return $this;
	}

	/**
	 * Getter and setter for the follow redirects
	 * setting.
	 *
	 * @param   bool  $follow  Boolean indicating if redirects should be followed
	 * @return  bool
	 * @return  Request_Client
	 */
	public function follow($follow = FALSE)
	{
		if ($follow === NULL)
			return $this->_follow;

		$this->_follow = $follow;

		return $this;
	}

	/**
	 * Getter and setter for the follow redirects
	 * headers array.
	 *
	 * @param   array  $follow_headers  Array of headers to be re-used when following a Location header
	 * @return  array
	 * @return  Request_Client
	 */
	public function follow_headers($follow_headers = NULL)
	{
		if ($follow === NULL)
			return $this->_follow_headers;

		$this->_follow_headers = $follow_headers;

		return $this;
	}
}
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
	 * @var  bool  Follow 302 redirect with original request method?
	 */
	protected $_strict_redirect = TRUE;

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

		if (($cache = $this->cache()) instanceof HTTP_Cache)
			return $cache->execute($this, $request, $response);

		$response = $this->execute_request($request, $response);

		// Do we need to follow a Location header ?
		if ($this->follow() AND in_array($response->status(), array(201, 301, 302, 303, 307))
			AND $response->headers('Location'))
		{
			// Figure out which method to use for the follow request
			switch ($response->status())
			{
				default:
				case 301:
				case 307:
					$follow_method = $request->method();
					break;
				case 201:
				case 303:
					$follow_method = Request::GET;
					break;
				case 302:
					// Cater for sites with broken HTTP redirect implementations
					if ($this->strict_redirect())
					{
						$follow_method = $request->method();
					}
					else
					{
						$follow_method = Request::GET;
					}
					break;
			}

			// Prepare the additional request
			$follow_request = $this->_create_request($response->headers('Location'))
			                         ->method($follow_method)
			                         ->headers(Arr::extract($request->headers(), $this->follow_headers()));

			if ($follow_method !== Request::GET)
			{
				$follow_request->body($request->body());
			}

			// Execute the additional request
			$response = $follow_request->execute();
		}

		return $response;
	}

	/**
	 * Creates a new request object to follow a redirect (separated to allow
	 * mock injection in tests).
	 *
	 * @param string $url The URL to pass to Request::factory
	 * @return Request
	 */
	protected function _create_request($url)
	{
		return Request::factory($url);
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
	public function follow($follow = NULL)
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
		if ($follow_headers === NULL)
			return $this->_follow_headers;

		$this->_follow_headers = $follow_headers;

		return $this;
	}

	/**
	 * Getter and setter for the strict redirects setting
	 *
	 * [!!] HTTP/1.1 specifies that a 302 redirect should be followed using the
	 * original request method. However, the vast majority of clients and servers
	 * get this wrong, with 302 widely used for 'POST - 302 redirect - GET' patterns.
	 * By default, Kohana's client is fully compliant with the HTTP spec. Some
	 * non-compliant third party sites may require that strict_redirect is set
	 * FALSE to force the client to switch to GET following a 302 response.
	 *
	 * @param  bool  $strict_redirect  Boolean indicating if 302 redirects should be followed with the original method
	 * @return Request_Client
	 */
	public function strict_redirect($strict_redirect = NULL)
	{
		if ($strict_redirect === NULL)
			return $this->_strict_redirect;

		$this->_strict_redirect = $strict_redirect;

		return $this;
	}
}
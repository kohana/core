<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
abstract class Kohana_Request_Client {

	/**
	 * @var    Kohana_Cache  caching library for request caching
	 */
	protected $_cache;

	/**
	 * @var    boolean   defines whether this client should cache `private` cache directives
	 * @see    http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9
	 */
	protected $_allow_private_cache = FALSE;

	/**
	 * @var    int       the timestamp of the request
	 */
	protected $_request_time;

	/**
	 * @var    int       the timestamp of the response
	 */
	protected $_response_time;

	/**
	 * Creates a new `Kohana_Request_Client` object,
	 * allows for dependency injection.
	 *
	 * @param   array    params 
	 */
	public function __construct(array $params = NULL)
	{
		if ($params)
		{
			foreach ($params as $key => $value)
			{
				if (method_exists($this, $key))
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
	 * @param   Kohana_Request
	 * @return  Kohana_Response
	 * @throws  Kohana_Exception
	 * @uses    [Kohana::$profiling]
	 * @uses    [Profiler]
	 */
	abstract public function execute(Kohana_Request $request);

	/**
	 * Invalidate a cached response for the [Request] supplied.
	 * This has the effect of deleting the response from the
	 * [Cache] entry.
	 *
	 * @param   Kohana_Request  [Response] to remove from cache
	 * @return  void
	 */
	public function invalidate_cache(Kohana_Request $request)
	{
		if ( ! $this->_cache instanceof Kohana_Cache)
			return;

		$this->_cache->delete($this->_create_cache_key($request));

		return;
	}

	/**
	 * Getter and setter for the internal caching engine,
	 * used to cache responses if available and valid.
	 *
	 * @param   Kohana_Cache  cache engine to use for caching
	 * @return  Kohana_Cache
	 * @return  Kohana_Request_Client
	 */
	public function cache(Kohana_Cache $cache = NULL)
	{
		if ($cache === NULL)
			return $this->_cache;

		$this->_cache = $cache;
		return $this;
	}

	/**
	 * Gets or sets the [Request_Client::allow_private_cache] setting.
	 * If set to `TRUE`, the client will also cache cache-control directives
	 * that have the `private` setting.
	 *
	 * @see     http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9
	 * @param   boolean  allow caching of privately marked responses
	 * @return  boolean
	 * @return  [Kohana_Request_Client]
	 */
	public function allow_private_cache($setting = NULL)
	{
		if ($setting === NULL)
			return $this->_allow_private_cache;

		$this->_allow_private_cache = (bool) $setting;
		return $this;
	}

	/**
	 * Creates a cache key for the request to use for caching
	 * [Kohana_Response] returned by [Kohana_Request::execute].
	 *
	 * @param   Kohana_Request  request 
	 * @return  string
	 * @return  boolean
	 */
	public function create_cache_key(Kohana_Request $request)
	{
		return sha1($request->url());
	}

	/**
	 * Controls whether the response can be cached. Uses HTTP
	 * protocol to determine whether the response can be cached.
	 *
	 * @see     RFC 2616 http://www.w3.org/Protocols/rfc2616/
	 * @return  boolean
	 */
	public function set_cache(Kohana_Response $response)
	{
		if ($response->header->offsetExists('cache-control'))
		{
			// Parse the cache control
			$cache_control = Response::parse_cache_control((string) $response->header['cache-control']);

			// If the no-cache or no-store directive is set, return
			if (array_intersect_key($cache_control, array('no-cache' => NULL, 'no-store' => NULL)))
				return FALSE;

			// Get the directives
			$directives = array_keys($cache_control);

			// Check for private cache and get out of here if invalid
			if ( ! $this->_allow_private_cache and in_array('private', $directives))
			{
				if ( ! isset($cache_control['s-maxage']))
					return FALSE;

				// If there is a s-maxage directive we can use that
				$cache_control['max-age'] = $cache_control['s-maxage'];
			}

			// Check that max-age has been set and if it is valid for caching
			if (isset($cache_control['max-age']) and (int) $cache_control['max-age'] < 1)
				return FALSE;
		}

		if ($response->header->offsetExists('expires') and ! isset($cache_control['max-age']))
		{
			if (strtotime($response->header['expires']) >= time())
				return FALSE;
		}

		return TRUE;
	}

	/**
	 * Caches a [Kohana_Response] using the supplied [Kohana_Cache]
	 * and the key generated by [Kohana_Request_Client::_create_cache_key].
	 * 
	 * If not response is supplied, the cache will be checked for an existing
	 * one that is available.
	 *
	 * @param   Kohana_Request   the request
	 * @param   Kohana_Response  response 
	 * @return  boolean
	 * @return  Kohana_Response
	 */
	public function cache_response(Kohana_Request $request, Kohana_Response $response = NULL)
	{
		if ( ! $this->_cache instanceof Kohana_Cache)
			return FALSE;

		if ($response === NULL)
		{
			$response = $this->_cache->get($this->create_cache_key($request));
			return ($response !== NULL) ? $response : FALSE;
		}
		else
		{
			if (($ttl = $this->cache_lifetime($response)) === FALSE)
				return FALSE;

			return $this->_cache->set($this->create_cache_key($request), $response, $ttl);
		}
	}

	/**
	 * Calculates the total Time To Live based on the specification
	 * RFC 2616 cache lifetime rules.
	 *
	 * @param   Kohana_Response  response to evaluate
	 * @return  integer  ttl value
	 * @return  boolean  response should not be cached
	 */
	public function cache_lifetime(Kohana_Response $response)
	{
		// Get out of here if this cannot be cached
		if ( ! $this->set_cache($response))
			return FALSE;

		// Calculate apparent age
		if ($response->header->offsetExits('date'))
			$apparent_age = max(0, $this->_response_time - strtotime((string) $response->header['date']));
		else
			$apparent_age = max(0, $this->_response_time);

		// Calculate corrected received age
		if ($response->header->offsetExits('age'))
			$corrected_received_age = max($apparent_age, intval((string) $response->header['age']));
		else
			$corrected_received_age = $apparent_age;

		// Corrected initial age
		$corrected_initial_age = $corrected_received_age + $this->request_execution_time();

		// Resident time
		$resident_time = time() - $this->_response_time();

		// Current age
		$current_age = $corrected_initial_age + $resident_time;

		// Prepare the cache freshness lifetime
		$ttl = NULL;

		// Cache control overrides 
		if ($response->header->offsetExists('cache-control'))
		{
			// Parse the cache control header
			$cache_control = Response::parse_cache_control((string) $response->header['cache-control']);

			if (isset($cache_control['max-age']))
				$ttl = (int) $cache_control['max_age'];

			if (isset($cache_control['s-maxage']) and isset($cache_control['private']) and $this->_allow_private_cache)
				$ttl = (int) $cache_control['s-maxage'];

			if (isset($cache_control['max-stale']) and ! isset($cache_control['must-revalidate']))
				$ttl = $current_age + (int) $cache_control['max-stale'];
		}

		// If we have a TTL at this point, return
		if ($ttl !== NULL)
			return $ttl;

		if ($response->header->offsetExists('expires'))
			return strtotime($response->header['expires']) - $current_age;

		return FALSE;
	}

	/**
	 * Returns the duration of the last request execution.
	 * Either returns the time of completed requests or
	 * `FALSE` if the request hasn't finished executing, or
	 * is yet to be run.
	 *
	 * @return  integer
	 * @return  bool
	 */
	public function request_execution_time()
	{
		if ($this->_request_time === NULL or $this->_response_time === NULL)
			return FALSE;

		return $this->_response_time - $this->_request_time;
	}
}
<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Request Client
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 * @since      3.1.0
 */
abstract class Kohana_Request_Client {

	/**
	 * @var    Cache  Caching library for request caching
	 */
	protected $_cache;

	/**
	 * @var    boolean   Defines whether this client should cache `private` cache directives
	 * @see    http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9
	 */
	protected $_allow_private_cache = FALSE;

	/**
	 * @var    int       The timestamp of the request
	 */
	protected $_request_time;

	/**
	 * @var    int       The timestamp of the response
	 */
	protected $_response_time;

	/**
	 * Creates a new `Request_Client` object,
	 * allows for dependency injection.
	 *
	 * @param   array    $params Params
	 */
	public function __construct(array $params = array())
	{
		if ($params)
		{
			foreach ($params as $key => $value)
			{
				if (method_exists($this, $key))
				{
					if (property_exists($this, $key) OR property_exists($this, '_'.$key))
					{
						$method = trim($key, '_');
						$this->$method($value);
					}
				}
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
	 * @param   Request $request
	 * @return  Response
	 * @throws  Kohana_Exception
	 * @uses    [Kohana::$profiling]
	 * @uses    [Profiler]
	 */
	abstract public function execute(Request $request);

	/**
	 * Invalidate a cached response for the [Request] supplied.
	 * This has the effect of deleting the response from the
	 * [Cache] entry.
	 *
	 * @param   Request  $request Response to remove from cache
	 * @return  void
	 */
	public function invalidate_cache(Request $request)
	{
		if ( ! $this->_cache instanceof Cache)
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
	public function cache(Cache $cache = NULL)
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
	 * @return  [Request_Client]
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
	 * [Kohana_Response] returned by [Request::execute].
	 *
	 * @param   Request  request
	 * @return  string
	 * @return  boolean
	 */
	public function create_cache_key(Request $request)
	{
		return sha1($request->url());
	}

	/**
	 * Controls whether the response can be cached. Uses HTTP
	 * protocol to determine whether the response can be cached.
	 *
	 * @see     RFC 2616 http://www.w3.org/Protocols/rfc2616/
	 * @param   Response  $response The Response
	 * @return  boolean
	 */
	public function set_cache(Response $response)
	{
		$headers = (array) $response->headers();
		if ($cache_control = arr::get($headers, 'cache-control'))
		{
			// Parse the cache control
			$cache_control = Response::parse_cache_control( (string) $cache_control);

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

		if ($expires = arr::get($headers, 'expires') and ! isset($cache_control['max-age']))
		{
			// Can't cache things that have expired already
			if (strtotime( (string) $expires) <= time())
				return FALSE;
		}

		return TRUE;
	}

	/**
	 * Caches a [Response] using the supplied [Cache]
	 * and the key generated by [Request_Client::_create_cache_key].
	 *
	 * If not response is supplied, the cache will be checked for an existing
	 * one that is available.
	 *
	 * @param   Request   $request  The request
	 * @param   Response  $response Response
	 * @return  mixed
	 */
	public function cache_response(Request $request, Response $response = NULL)
	{
		if ( ! $this->_cache instanceof Cache)
			return FALSE;

		// Check for Pragma: no-cache
		if ($pragma = $request->headers('pragma'))
		{
			if ($pragma instanceof HTTP_Header_Value and $pragma->key == 'no-cache')
				return FALSE;
			elseif (is_array($pragma) and isset($pragma['no-cache']))
				return FALSE;
		}

		if ( ! $response)
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
	 * @param   Response  $response  Response to evaluate
	 * @return  mixed  TTL value or false if the response should not be cached
	 */
	public function cache_lifetime(Response $response)
	{
		// Get out of here if this cannot be cached
		if ( ! $this->set_cache($response))
			return FALSE;

		// Calculate apparent age
		if ($date = $response->headers('date'))
		{
			$apparent_age = max(0, $this->_response_time - strtotime( (string) $date));
		}
		else
		{
			$apparent_age = max(0, $this->_response_time);
		}

		// Calculate corrected received age
		if ($age = $response->headers('age'))
		{
			$corrected_received_age = max($apparent_age, intval( (string) $age));
		}
		else
		{
			$corrected_received_age = $apparent_age;
		}

		// Corrected initial age
		$corrected_initial_age = $corrected_received_age + $this->request_execution_time();

		// Resident time
		$resident_time = time() - $this->_response_time;

		// Current age
		$current_age = $corrected_initial_age + $resident_time;

		// Prepare the cache freshness lifetime
		$ttl = NULL;

		// Cache control overrides
		if ($cache_control = $response->headers('cache-control'))
		{
			// Parse the cache control header
			$cache_control = Response::parse_cache_control( (string) $cache_control);

			if (isset($cache_control['max-age']))
			{
				$ttl = (int) $cache_control['max-age'];
			}

			if (isset($cache_control['s-maxage']) AND isset($cache_control['private']) AND $this->_allow_private_cache)
			{
				$ttl = (int) $cache_control['s-maxage'];
			}

			if (isset($cache_control['max-stale']) AND ! isset($cache_control['must-revalidate']))
			{
				$ttl = $current_age + (int) $cache_control['max-stale'];
			}
		}

		// If we have a TTL at this point, return
		if ($ttl !== NULL)
			return $ttl;

		if ($expires = $response->headers('expires'))
			return strtotime( (string) $expires) - $current_age;

		return FALSE;
	}

	/**
	 * Returns the duration of the last request execution.
	 * Either returns the time of completed requests or
	 * `FALSE` if the request hasn't finished executing, or
	 * is yet to be run.
	 *
	 * @return  mixed
	 */
	public function request_execution_time()
	{
		if ($this->_request_time === NULL OR $this->_response_time === NULL)
			return FALSE;

		return $this->_response_time - $this->_request_time;
	}
}
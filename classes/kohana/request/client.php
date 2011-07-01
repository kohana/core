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

	const CACHE_STATUS_KEY    = 'x-cache-status';
	const CACHE_STATUS_SAVED  = 'SAVED';
	const CACHE_STATUS_HIT    = 'HIT';
	const CACHE_STATUS_MISS   = 'MISS';

	/**
	 * @var    Cache  Caching library for request caching
	 */
	protected $_cache;


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
	 * @param   Request $request
	 * @return  Response
	 * @throws  Kohana_Exception
	 * @uses    [Kohana::$profiling]
	 * @uses    [Profiler]
	 */
	public function execute(Request $request)
	{
		if ($this->_cache instanceof HTTP_Cache)
			return $this->_cache->execute($this);

		return $this->execute_client($request);
	}

	/**
	 * undocumented function
	 *
	 * @param   Request   request to execute by client
	 * @return  Response
	 * @since   3.2.0
	 */
	abstract public function execute_client(Request $request)

	/**
	 * Getter and setter for the internal caching engine,
	 * used to cache responses if available and valid.
	 *
	 * @param   [HTTP_Cache] cache engine to use for caching
	 * @return  [HTTP_Cache]
	 * @return  [Request_Client]
	 */
	public function cache(HTTP_Cache $cache = NULL)
	{
		if ($cache === NULL)
			return $this->_cache;

		$this->_cache = $cache;
		return $this;
	}
}
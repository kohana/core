<?php

namespace Kohana\Core\Request\Client;

use Exception;
use Kohana\Core\Core;
use Kohana\Core\HTTP\HttpException;
use Kohana\Core\Kohana\KohanaException;
use Kohana\Core\Request\Client;
use Kohana\Core\Profiler;
use ReflectionClass;
use Kohana\Core\Request;
use Kohana\Core\Response;

/**
 * Request Client for internal execution
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 * @since      3.1.0
 */
class Internal extends Client {

	/**
	 * @var    array
	 */
	protected $_previous_environment;

	/**
	 * Processes the request, executing the controller action that handles this
	 * request, determined by the [Route].
	 *
	 *     $request->execute();
	 *
	 * @param   Request $request
	 * @return  Response
	 * @throws  KohanaException
	 * @uses    [Core::$profiling]
	 * @uses    [Profiler]
	 */
	public function execute_request(Request $request, Response $response)
	{
		// Create the class prefix
		$prefix = 'Controller_';

		// Directory
		$directory = $request->directory();

		// Controller
		$controller = $request->controller();

		if ($directory)
		{
			// Add the directory name to the class prefix
			$prefix .= str_replace(array('\\', '/'), '_', trim($directory, '/')).'_';
		}

		// Use the FQCN to load the Controller
		if (substr($controller, 0, 1) === '\\')
		{
			$prefix = '';
		}

		if (Core::$profiling)
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

		// Store the currently active request
		$previous = Request::$current;

		// Change the current request to this request
		Request::$current = $request;

		// Is this the initial request
		$initial_request = ($request === Request::$initial);

		try
		{
			if ( ! class_exists($prefix.$controller))
			{
				throw HTTPException::factory(404,
					'The requested URL :uri was not found on this server.',
					array(':uri' => $request->uri())
				)->request($request);
			}

			// Load the controller using reflection
			$class = new ReflectionClass($prefix.$controller);

			if ($class->isAbstract())
			{
				throw new KohanaException(
					'Cannot create instances of abstract :controller',
					array(':controller' => $prefix.$controller)
				);
			}

			// Create a new instance of the controller
			$controller = $class->newInstance($request, $response);

			// Run the controller's execute() method
			$response = $class->getMethod('execute')->invoke($controller);

			if ( ! $response instanceof Response)
			{
				// Controller failed to return a Response.
				throw new KohanaException('Controller failed to return a Response');
			}
		}
		catch (HttpException $e)
		{
			// Store the request context in the Exception
			if ($e->request() === NULL)
			{
				$e->request($request);
			}

			// Get the response via the Exception
			$response = $e->get_response();
		}
		catch (Exception $e)
		{
			// Generate an appropriate Response object
			$response = KohanaException::_handler($e);
		}

		// Restore the previous request
		Request::$current = $previous;

		if (isset($benchmark))
		{
			// Stop the benchmark
			Profiler::stop($benchmark);
		}

		// Return the response
		return $response;
	}

}

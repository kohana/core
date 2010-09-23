Changes that should happen when you deploy. (Production)

Security settings from: <http://kohanaframework.org/guide/using.configuration>

<http://kerkness.ca/wiki/doku.php?id=setting_up_production_environment>


## Setting up a production environment

There are a few things you'll want to do with your application before moving into production.

1. See the [Bootstrap page](bootstrap) in the docs.
   This covers most of the global settings that would change between environments.
   As a general rule, you should enable caching and disable profiling ([Kohana::init] settings) for production sites.
   [Route::cache] can also help if you have a lot of routes.
2. Catch all exceptions in `application/bootstrap.php`, so that sensitive data is cannot be leaked by stack traces.
   See the example below which was taken from Shadowhand's [wingsc.com source](http://github.com/shadowhand/wingsc).
3. Turn on APC or some kind of opcode caching.
   This is the single easiest performance boost you can make to PHP itself. The more complex your application, the bigger the benefit of using opcode caching.

		/**
		 * Set the environment string by the domain (defaults to Kohana::DEVELOPMENT).
		 */
		Kohana::$environment = ($_SERVER['SERVER_NAME'] !== 'localhost') ? Kohana::PRODUCTION : Kohana::DEVELOPMENT;
		/**
		 * Initialise Kohana based on environment
		 */
		Kohana::init(array(
			'base_url'   => '/',
			'index_file' => FALSE,
			'profile'    => Kohana::$environment !== Kohana::PRODUCTION,
			'caching'    => Kohana::$environment === Kohana::PRODUCTION,
		));

		/**
		 * Execute the main request using PATH_INFO. If no URI source is specified,
		 * the URI will be automatically detected.
		 */
		$request = Request::instance($_SERVER['PATH_INFO']);

		try
		{
			// Attempt to execute the response
			$request->execute();
		}
		catch (Exception $e)
		{
			if (Kohana::$environment === Kohana::DEVELOPMENT)
			{
				// Just re-throw the exception
				throw $e;
			}

			// Log the error
			Kohana::$log->add(Kohana::ERROR, Kohana::exception_text($e));

			// Create a 404 response
			$request->status = 404;
			$request->response = View::factory('template')
			  ->set('title', '404')
			  ->set('content', View::factory('errors/404'));
		}

		if ($request->send_headers()->response)
		{
			// Get the total memory and execution time
			$total = array(
			  '{memory_usage}' => number_format((memory_get_peak_usage() - KOHANA_START_MEMORY) / 1024, 2).'KB',
			  '{execution_time}' => number_format(microtime(TRUE) - KOHANA_START_TIME, 5).' seconds');

			// Insert the totals into the response
			$request->response = str_replace(array_keys($total), $total, $request->response);
		}


		/**
		 * Display the request response.
		 */
		echo $request->response;



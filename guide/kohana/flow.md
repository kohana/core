# Request Flow

Every application follows the same flow:

1. Application starts from `index.php`.
	1. The application, module, and system paths are set. (`APPPATH`, `MODPATH`, and `SYSPATH`)
	2. Error reporting levels are set.
	3. Install file is loaded, if it exists.
	4. The bootstrap file, `APPPATH/bootstrap.php`, is included.
2. Once we are in `bootstrap.php`:
	5. The [Kohana] class is loaded.
	6. [Kohana::init] is called, which sets up error handling, caching, and logging.
	7. [Kohana_Config] readers and [Kohana_Log] writers are attached.
	8. [Kohana::modules] is called to enable additional modules.
	    * Module paths are added to the [cascading filesystem](files).
		* Includes each module's `init.php` file, if it exists. 
	    * The `init.php` file can perform additional environment setup, including adding routes.
	9. [Route::set] is called multiple times to define the [application routes](routing).
3. Application flow returns to `index.php`
	10. [Request::factory] is called to start processing the request.
		1. Checks each route that has been set until a match is found.
		2. Creates the controller instance and passes the request to it.
		3. Calls the [Controller::before] method.
		4. Calls the controller action, which generates the request response.
		5. Calls the [Controller::after] method.
		    * The above 5 steps can be repeated multiple times when using [HMVC sub-requests](requests).
	11. The main [Request] response is displayed
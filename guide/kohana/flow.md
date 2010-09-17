# Request Flow

Every application follows the same flow:

1. Application starts from `index.php`.
	1. The application, module, and system paths are set. (`APPPATH`, `MODPATH`, and `SYSPATH`)
	2. Error reporting levels are set.
	3. Install file is loaded, if it exists.
	4. The [Kohana] class is loaded.
	5. The bootstrap file, `APPPATH/bootstrap.php`, is included.
2. Once we are in `bootstrap.php`:
	7. [Kohana::init] is called, which sets up error handling, caching, and logging.
	8. [Kohana_Config] readers and [Kohana_Log] writers are attached.
	9. [Kohana::modules] is called to enable additional modules.
	    * Module paths are added to the [cascading filesystem](files).
		* Includes each module's `init.php` file, if it exists. 
	    * The `init.php` file can perform additional environment setup, including adding routes.
	10. [Route::set] is called multiple times to define the [application routes](routing).
	11. [Request::instance] is called to start processing the request.
		1. Checks each route that has been set until a match is found.
		2. Creates the controller instance and passes the request to it.
		3. Calls the [Controller::before] method.
		4. Calls the controller action, which generates the request response.
		5. Calls the [Controller::after] method.
		    * The above 5 steps can be repeated multiple times when using [HMVC sub-requests](requests).
	12. The main [Request] response is displayed

## index.php

**Perhaps this should be a tutorial?**

Kohana follows a [front controller] pattern, which means that all requests are sent to `index.php`. This allows a very clean [filesystem](about.filesystem) design. In `index.php`, there are some very basic configuration options available. You can change the `$application`, `$modules`, and `$system` paths and set the error reporting level.

The `$application` variable lets you set the directory that contains your application files. By default, this is `application`. The `$modules` variable lets you set the directory that contains module files. The `$system` variable lets you set the directory that contains the default Kohana files.

You can move these three directories anywhere. For instance, if your directories are set up like this:

    www/
        index.php
        application/
        modules/
        system/

You could move the directories out of the web root:

    application/
    modules/
    system/
    www/
        index.php

Then you would change the settings in `index.php` to be:

    $application = '../application';
    $modules     = '../modules';
    $system      = '../system';

Now none of the directories can be accessed by the web server. It is not necessary to make this change, but does make it possible to share the directories with multiple applications, among other things.

[!!] There is a security check at the top of every Kohana file to prevent it from being accessed without using the front controller. However, it is more secure to move the application, modules, and system directories to a location that cannot be accessed via the web.
# Error/Exception Handling

Kohana provides both an exception handler and an error handler that transforms errors into exceptions using PHP's [ErrorException](http://php.net/errorexception) class. Many details of the error and the internal state of the application is displayed by the handler:

1. Exception class
2. Error level
3. Error message
4. Source of the error, with the error line highlighted
5. A [debug backtrace](http://php.net/debug_backtrace) of the execution flow
6. Included files, loaded extensions, and global variables

## Example

Click any of the links to toggle the display of additional information:

<div>{{userguide/examples/error}}</div>

## Disabling Error/Exception Handling

If you do not want to use the internal error handling, you can disable it when calling [Kohana::init]:

    Kohana::init(array('errors' => FALSE));

## Error Reporting

By default, Kohana displays all errors, including strict mode warnings. This is set using [error_reporting](http://php.net/error_reporting):

    error_reporting(E_ALL | E_STRICT);

When you application is live and in production, a more conservative setting is recommended, such as ignoring notices:

    error_reporting(E_ALL & ~E_NOTICE);

If you get a white screen when an error is triggered, your host probably has disabled displaying errors. You can turn it on again by adding this line just after your `error_reporting` call:

    ini_set('display_errors', TRUE);

Errors should **always** be displayed, even in production, because it allows you to use [exception and error handling](debugging.errors) to serve a nice error page rather than a blank white screen when an error happens.


## Last thoughts

In production, **your application should never have any uncaught exceptions**, as this can expose sensitive information (via the stack trace).  In the previous example we make the assumption that there is actually a view called 'views/errors/404', which is fairly safe to assume.  One solution is to turn 'errors' off in Kohana::init for your production machine, so it displays the normal php errors rather than a stack trace.

~~~
// snippet from bootstrap.php 
Kohana::init(array('
    ...
    'errors' => false,
));
~~~

So rather than displaying the Kohana error page with the stack trace, it will display the default php error. Something like:

**Fatal error: Uncaught Kohana_View_Exception [ 0 ]: The requested view errors/404 could not be found ~ SYSPATH/classes/kohana/view.php [ 215 ] thrown in /var/www/kohanut/docs.kohanaphp.com/3.0/system/classes/kohana/view.php on line 215**

Keep in mind what I said earlier though: **your application should never have any uncaught exceptions**, so this should not be necesarry, though it is a good idea, simply because stack traces on a production environment are a *very* bad idea.

Another solution is to always have a `catch` statement that can't fail, something like an `echo` and an `exit` or a `die()`.  This should almost never be necesarry, but it makes some people feel better at night.  You can either wrap your entire bootstrap in a try catch, or simply wrap the contents of the catch in another try catch.  For example:

~~~
try
{
	// Execute the main request
	$request->execute();
}
catch (Exception $e)
{
	try
	{
		// Be sure to log the error
		Kohana::$log->add(Kohana::ERROR, Kohana::exception_text($e));
		
		// If there was an error, send a 404 response and display an error
		$request->status   = 404;
		$request->response = View::factory('errors/404');
	}
	catch
	{
		// This is completely overkill, but helps some people sleep at night
		echo "Something went terribly wrong. Try again in a few minutes.";
		exit;
	}
}
~~~
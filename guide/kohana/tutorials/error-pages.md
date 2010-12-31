# Friendly Error Pages <small>written by <a href="http://mathew-davies.co.uk./">Mathew Davies</a></small>

By default Kohana 3 doesn't have a method to display friendly error pages like that 
seen in Kohana 2; In this short guide I will teach you how it is done.

## Prerequisites 

You will need `'errors' => TRUE` passed to `Kohana::init`. This will convert PHP 
errors into exceptions which are easier to handle.

## 1. A Custom Exception

First off, we are going to need a custom exception class. This is so we can perform different
actions based on it in the exception handler. I will talk more about this later.

_classes/http\_response\_exception.php_

	<?php defined('SYSPATH') or die('No direct access');

	class HTTP_Response_Exception extends Kohana_Exception {}

## 2. An Improved Exception Handler

Our custom exception handler is self explanatory.

	public static function exception_handler(Exception $e)
	{
		if (Kohana::DEVELOPMENT === Kohana::$environment)
		{
			Kohana_Core::exception_handler($e);
		}
		else
		{
			Kohana::$log->add(Kohana::ERROR, Kohana::exception_text($e));

			$attributes = array
			(
				'action'  => 500,
				'message' => rawurlencode($e->getMessage())
			);

			if ($e instanceof HTTP_Response_Exception)
			{
				$attributes['action'] = $e->getCode();
			}

			// Error sub-request.
			echo Request::factory(Route::url('error', $attributes))
				->execute()
				->send_headers()
				->response;
		}
	}

If we are in the development environment then pass it off to Kohana otherwise:

* Log the error
* Set the route action and message attributes.
* If a `HTTP_Response_Exception` was thrown, then override the action with the error code.
* Fire off an internal sub-request.

The action will be used as the HTTP response code. By default this is: 500 (internal
server error) unless a `HTTP_Response_Exception` was thrown.

So this:

	throw new HTTP_Response_Exception(':file does not exist', array(':file' => 'Gaia'), 404);

would display a nice 404 error page, where:

	throw new Kohana_Exception('Directory :dir must be writable',
				array(':dir' => Kohana::debug_path(Kohana::$cache_dir)));

would display an error 500 page.

**The Route**

	Route::set('error', 'error/<action>(/<message>)', array('action' => '[0-9]++', 'message' => '.+'))
	->defaults(array(
		'controller' => 'error_handler'
	));

## 3. The Error Page Controller

	public function  before()
	{
		parent::before();

		$this->template->page = URL::site(rawurldecode(Request::$instance->uri));

		// Internal request only!
		if (Request::$instance !== Request::$current)
		{
			if ($message = rawurldecode($this->request->param('message')))
			{
				$this->template->message = $message;
			}
		}
		else
		{
			$this->request->action = 404;
		}
	}

1. Set a template variable "page" so the user can see what they requested. This 
   is for display purposes only.
2. If an internal request, then set a template variable "message" to be shown to 
   the user.
3. Otherwise use the 404 action. Users could otherwise craft their own error messages, eg:
   `error/404/email%20your%20login%20information%20to%20hacker%40google.com`


~~~
public function action_404()
{
	$this->template->title = '404 Not Found';
	
	// Here we check to see if a 404 came from our website. This allows the
	// webmaster to find broken links and update them in a shorter amount of time.
	if (isset ($_SERVER['HTTP_REFERER']) AND strstr($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME']) !== FALSE)
	{
		// Set a local flag so we can display different messages in our template.
		$this->template->local = TRUE;
	}
	
	// HTTP Status code.
	$this->request->status = 404;
}

public function action_503()
{
	$this->template->title = 'Maintenance Mode';
	$this->request->status = 503;
}

public function action_500()
{
	$this->template->title = 'Internal Server Error';
	$this->request->status = 500;
}
~~~

You will notice that each example method is named after the HTTP response code 
and sets the request response code.

## 4. Handling 3rd Party Modules.

Some Kohana modules will make calls to `Kohana::exception_handler`. We can redirect
calls made to it by extending the Kohana class and passing the exception to our handler.

	<?php defined('SYSPATH') or die('No direct script access.');

	class Kohana extends Kohana_Core
	{
		/**
		 * Redirect to custom exception_handler
		 */
		public static function exception_handler(Exception $e)
		{
			Error::exception_handler($e);
		}
	}

## 5. Conclusion

So that's it. Now displaying a nice error page is as easy as:

	throw new HTTP_Response_Exception('The website is down', NULL, 503);
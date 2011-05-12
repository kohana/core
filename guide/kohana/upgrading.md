# Migrating from 3.0.x

## Request/Response

The request class has been split into a request and response class. To set the response body, you used to do:

	$this->request->response = 'foo';

This has changed to:

	$this->response->body('foo');

Some properties that existed in the request class have been converted into methods:

	- Request::$controller -> Request::controller()
	- Request::$action -> Request::action()
	- Request::$directory -> Request::directory()
	- Request::$uri -> Request::uri()

Request::instance() has been replaced by Request::current() and Request::initial(). Normally you'll want to use Request::current(), but if you are sure you want the *original* request (when running hmvc requests), use Request::initial().

## Validation

The validation class has been improved to include "context" support. Because of this, the api has changed. Also, The class has been split: core validation logic is now separate from built-in validation rules. The new core class is called `Validation` and the rules are located in the `Valid` class.

### Context

The validation class now contains "context" support. This allowed us to merge the rule() and callback() methods, and there is now simply a rule() method that handles both cases.

Old usage:

	rule('password', 'matches', array('repeat_password'))

New usage:

	rule('password', 'matches', array(':validation', 'password', 'repeat_password'))

The third parameter now contains all parameters that get passed to the rule. If you look at Valid::matches(), you'll see:

	public static function matches($array, $field, $match)

:validation is the first parameter, 'password' is the second (the field we want to check) and 'repeat_password' is the third (what we want to match)

:validation is a special "context" variable that tells the Validation class to replace with the actual validation class. You can see in this way, the matches rule is no different than how callbacks used to work, yet are more powerful. There are other context variables:

 - :validation - The validation object
 - :field - The field being compared (rule('username', 'min_length', array(':field', 4)))
 - :value - The value of the field

You can use any php function as a rule if it returns a boolean value.

### Filters

Filters have been removed from the validation class. There is no specific replacement. If you were using it with ORM, there is a new mechanism for filtering in that module.

## Cookie salts

The cookie class now throws an exception if there isn't a salt set, and no salt is the now the default. You'll need to make sure you set the salt in your bootstrap:

	Cookie::$salt = 'foobar';

Or define an extended cookie class in your application:

	class Cookie extends Kohana_Cookie
	{
		public static $salt = 'foobar';
	}

## Controller constructor

If for some reason you are overloading your controller's constructor, it has changed to:

	public function __construct(Request $request, Response $response)

## index.php / bootstrap.php changes

The main change here is that the request execution has been removed from bootstrap.php and moved to index.php. This allows you to use one bootstrap when doing testing. The reason for this change is that the bootstrap should only setup the environment. It shouldn't run it.

## 404 Handling

Kohana now has built in exception support for 404 and other http status codes. If you were using ReflectionException to detect 404s, you should be using HTTP_Exception_404 instead. For details on how to handle 404s, see [error handling](errors).

## Form Class

If you used Form::open(), the default behavior has changed. It used to default to the current URI, but now an empty parameter will default to "/" (your home page).

## Logging

The log message level constants now belong to the Log class.  If you are referencing those constants to invoke Kohana::$log->add( ... ) you will need to change the following:

    - Kohana::ERROR -> Log::ERROR
    - Kohana::DEBUG -> Log::DEBUG
    - Kohana::INFO  -> Log::INFO

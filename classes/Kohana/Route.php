<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Routes are used to determine the controller and action for a requested URI.
 * Every route generates a regular expression which is used to match a URI
 * and a route. Routes may also contain keys which can be used to set the
 * controller, action, and parameters.
 *
 * Each <key> will be translated to a regular expression using a default
 * regular expression pattern. You can override the default pattern by providing
 * a pattern for the key:
 *
 *     // This route will only match when <id> is a digit
 *     Route::set('user', 'user/<action>/<id>', array('id' => '\d+'));
 *
 *     // This route will match when <path> is anything
 *     Route::set('file', '<path>', array('path' => '.*'));
 *
 * It is also possible to create optional segments by using parentheses in
 * the URI definition:
 *
 *     // This is the standard default route, and no keys are required
 *     Route::set('default', '(<controller>(/<action>(/<id>)))');
 *
 *     // This route only requires the <file> key
 *     Route::set('file', '(<path>/)<file>(.<format>)', array('path' => '.*', 'format' => '\w+'));
 *
 * Routes also provide a way to generate URIs (called "reverse routing"), which
 * makes them an extremely powerful and flexible way to generate internal links.
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Route {

	// Matches a URI group and captures the contents
	const REGEX_GROUP   = '\(((?:(?>[^()]+)|(?R))*)\)';

	// Defines the pattern of a <segment>
	const REGEX_KEY     = '<([a-zA-Z0-9_]++)>';

	// What can be part of a <segment> value
	const REGEX_SEGMENT = '[^/.,;?\n]++';

	// What must be escaped in the route regex
	const REGEX_ESCAPE  = '[.\\+*?[^\\]${}=!|]';

	/**
	 * @var  string  default protocol for all routes
	 *
	 * @example  'http://'
	 */
	public static $default_protocol = 'http://';

	/**
	 * @var  array   list of valid localhost entries
	 */
	public static $localhosts = array(FALSE, '', 'local', 'localhost');

	/**
	 * @var  string  default action for all routes
	 */
	public static $default_action = 'index';

	/**
	 * @var  bool Indicates whether routes are cached
	 */
	public static $cache = FALSE;

	/**
	 * @var  array
	 */
	protected static $_routes = array();

	/**
	 * Stores a named route and returns it. The "action" will always be set to
	 * "index" if it is not defined.
	 *
	 *     Route::set('default', '(<controller>(/<action>(/<id>)))')
	 *         ->defaults(array(
	 *             'controller' => 'welcome',
	 *         ));
	 *
	 * @param   string  $name           route name
	 * @param   string  $uri            URI pattern
	 * @param   array   $regex          regex patterns for route keys
	 * @return  Route
	 */
	public static function set($name, $uri = NULL, $regex = NULL)
	{
		return Route::$_routes[$name] = new Route($uri, $regex);
	}

	/**
	 * Retrieves a named route.
	 *
	 *     $route = Route::get('default');
	 *
	 * @param   string  $name   route name
	 * @return  Route
	 * @throws  Kohana_Exception
	 */
	public static function get($name)
	{
		if ( ! isset(Route::$_routes[$name]))
		{
			throw new Kohana_Exception('The requested route does not exist: :route',
				array(':route' => $name));
		}

		return Route::$_routes[$name];
	}

	/**
	 * Retrieves all named routes.
	 *
	 *     $routes = Route::all();
	 *
	 * @return  array  routes by name
	 */
	public static function all()
	{
		return Route::$_routes;
	}

	/**
	 * Get the name of a route.
	 *
	 *     $name = Route::name($route)
	 *
	 * @param   Route   $route  instance
	 * @return  string
	 */
	public static function name(Route $route)
	{
		return array_search($route, Route::$_routes);
	}

	/**
	 * Saves or loads the route cache. If your routes will remain the same for
	 * a long period of time, use this to reload the routes from the cache
	 * rather than redefining them on every page load.
	 *
	 *     if ( ! Route::cache())
	 *     {
	 *         // Set routes here
	 *         Route::cache(TRUE);
	 *     }
	 *
	 * @param   boolean $save   cache the current routes
	 * @param   boolean $append append, rather than replace, cached routes when loading
	 * @return  void    when saving routes
	 * @return  boolean when loading routes
	 * @uses    Kohana::cache
	 */
	public static function cache($save = FALSE, $append = FALSE)
	{
		if ($save === TRUE)
		{
			try
			{
				// Cache all defined routes
				Kohana::cache('Route::cache()', Route::$_routes);
			}
			catch (Exception $e)
			{
				// We most likely have a lambda in a route, which cannot be cached
				throw new Kohana_Exception('One or more routes could not be cached (:message)', array(
						':message' => $e->getMessage(),
					), 0, $e);
			}
		}
		else
		{
			if ($routes = Kohana::cache('Route::cache()'))
			{
				if ($append)
				{
					// Append cached routes
					Route::$_routes += $routes;
				}
				else
				{
					// Replace existing routes
					Route::$_routes = $routes;
				}

				// Routes were cached
				return Route::$cache = TRUE;
			}
			else
			{
				// Routes were not cached
				return Route::$cache = FALSE;
			}
		}
	}

	/**
	 * Create a URL from a route name. This is a shortcut for:
	 *
	 *     echo URL::site(Route::get($name)->uri($params), $protocol);
	 *
	 * @param   string  $name       route name
	 * @param   array   $params     URI parameters
	 * @param   mixed   $protocol   protocol string or boolean, adds protocol and domain
	 * @return  string
	 * @since   3.0.7
	 * @uses    URL::site
	 */
	public static function url($name, array $params = NULL, $protocol = NULL)
	{
		$route = Route::get($name);

		// Create a URI with the route and convert it to a URL
		if ($route->is_external())
			return $route->uri($params);
		else
			return URL::site($route->uri($params), $protocol);
	}

	/**
	 * Returns the compiled regular expression for the route. This translates
	 * keys and optional groups to a proper PCRE regular expression.
	 *
	 *     $compiled = Route::compile(
	 *        '<controller>(/<action>(/<id>))',
	 *         array(
	 *           'controller' => '[a-z]+',
	 *           'id' => '\d+',
	 *         )
	 *     );
	 *
	 * @return  string
	 * @uses    Route::REGEX_ESCAPE
	 * @uses    Route::REGEX_SEGMENT
	 */
	public static function compile($uri, array $regex = NULL)
	{
		// The URI should be considered literal except for keys and optional parts
		// Escape everything preg_quote would escape except for : ( ) < >
		$expression = preg_replace('#'.Route::REGEX_ESCAPE.'#', '\\\\$0', $uri);

		if (strpos($expression, '(') !== FALSE)
		{
			// Make optional parts of the URI non-capturing and optional
			$expression = str_replace(array('(', ')'), array('(?:', ')?'), $expression);
		}

		// Insert default regex for keys
		$expression = str_replace(array('<', '>'), array('(?P<', '>'.Route::REGEX_SEGMENT.')'), $expression);

		if ($regex)
		{
			$search = $replace = array();
			foreach ($regex as $key => $value)
			{
				$search[]  = "<$key>".Route::REGEX_SEGMENT;
				$replace[] = "<$key>$value";
			}

			// Replace the default regex with the user-specified regex
			$expression = str_replace($search, $replace, $expression);
		}

		return '#^'.$expression.'$#uD';
	}

	/**
	 * @var  array  route filters
	 */
	protected $_filters = array();

	/**
	 * @var  string  route URI
	 */
	protected $_uri = '';

	/**
	 * @var  array
	 */
	protected $_regex = array();

	/**
	 * @var  array
	 */
	protected $_defaults = array('action' => 'index', 'host' => FALSE);

	/**
	 * @var  string
	 */
	protected $_route_regex;

	/**
	 * Creates a new route. Sets the URI and regular expressions for keys.
	 * Routes should always be created with [Route::set] or they will not
	 * be properly stored.
	 *
	 *     $route = new Route($uri, $regex);
	 *
	 * The $uri parameter should be a string for basic regex matching.
	 *
	 *
	 * @param   string  $uri    route URI pattern
	 * @param   array   $regex  key patterns
	 * @return  void
	 * @uses    Route::_compile
	 */
	public function __construct($uri = NULL, $regex = NULL)
	{
		if ($uri === NULL)
		{
			// Assume the route is from cache
			return;
		}

		if ( ! empty($uri))
		{
			$this->_uri = $uri;
		}

		if ( ! empty($regex))
		{
			$this->_regex = $regex;
		}

		// Store the compiled regex locally
		$this->_route_regex = Route::compile($uri, $regex);
	}

	/**
	 * Provides default values for keys when they are not present. The default
	 * action will always be "index" unless it is overloaded here.
	 *
	 *     $route->defaults(array(
	 *         'controller' => 'welcome',
	 *         'action'     => 'index'
	 *     ));
	 *
	 * If no parameter is passed, this method will act as a getter.
	 *
	 * @param   array   $defaults   key values
	 * @return  $this or array
	 */
	public function defaults(array $defaults = NULL)
	{
		if ($defaults === NULL)
		{
			return $this->_defaults;
		}

		$this->_defaults = $defaults;

		return $this;
	}

	/**
	 * Filters to be run before route parameters are returned:
	 *
	 *     $route->filter(
	 *         function(Route $route, $params, Request $request)
	 *         {
	 *             if ($request->method() !== HTTP_Request::POST)
	 *             {
	 *                 return FALSE; // This route only matches POST requests
	 *             }
	 *             if ($params AND $params['controller'] === 'welcome')
	 *             {
	 *                 $params['controller'] = 'home';
	 *             }
	 *
	 *             return $params;
	 *         }
	 *     );
	 *
	 * To prevent a route from matching, return `FALSE`. To replace the route
	 * parameters, return an array.
	 *
	 * [!!] Default parameters are added before filters are called!
	 *
	 * @throws  Kohana_Exception
	 * @param   array   $callback   callback string, array, or closure
	 * @return  $this
	 */
	public function filter($callback)
	{
		if ( ! is_callable($callback))
		{
			throw new Kohana_Exception('Invalid Route::callback specified');
		}

		$this->_filters[] = $callback;

		return $this;
	}

	/**
	 * Tests if the route matches a given Request. A successful match will return
	 * all of the routed parameters as an array. A failed match will return
	 * boolean FALSE.
	 *
	 *     // Params: controller = users, action = edit, id = 10
	 *     $params = $route->matches(Request::factory('users/edit/10'));
	 *
	 * This method should almost always be used within an if/else block:
	 *
	 *     if ($params = $route->matches($request))
	 *     {
	 *         // Parse the parameters
	 *     }
	 *
	 * @param   Request $request  Request object to match
	 * @return  array             on success
	 * @return  FALSE             on failure
	 */
	public function matches(Request $request)
	{
		// Get the URI from the Request
		$uri = trim($request->uri(), '/');

		if ( ! preg_match($this->_route_regex, $uri, $matches))
			return FALSE;

		$params = array();
		foreach ($matches as $key => $value)
		{
			if (is_int($key))
			{
				// Skip all unnamed keys
				continue;
			}

			// Set the value for all matched keys
			$params[$key] = $value;
		}

		foreach ($this->_defaults as $key => $value)
		{
			if ( ! isset($params[$key]) OR $params[$key] === '')
			{
				// Set default values for any key that was not matched
				$params[$key] = $value;
			}
		}

		if ( ! empty($params['controller']))
		{
			// PSR-0: Replace underscores with spaces, run ucwords, then replace underscore
			$params['controller'] = str_replace(' ', '_', ucwords(str_replace('_', ' ', $params['controller'])));
		}

		if ( ! empty($params['directory']))
		{
			// PSR-0: Replace underscores with spaces, run ucwords, then replace underscore
			$params['directory'] = str_replace(' ', '_', ucwords(str_replace('_', ' ', $params['directory'])));
		}

		if ($this->_filters)
		{
			foreach ($this->_filters as $callback)
			{
				// Execute the filter giving it the route, params, and request
				$return = call_user_func($callback, $this, $params, $request);

				if ($return === FALSE)
				{
					// Filter has aborted the match
					return FALSE;
				}
				elseif (is_array($return))
				{
					// Filter has modified the parameters
					$params = $return;
				}
			}
		}

		return $params;
	}

	/**
	 * Returns whether this route is an external route
	 * to a remote controller.
	 *
	 * @return  boolean
	 */
	public function is_external()
	{
		return ! in_array(Arr::get($this->_defaults, 'host', FALSE), Route::$localhosts);
	}

	/**
	 * Generates a URI for the current route based on the parameters given.
	 *
	 *     // Using the "default" route: "users/profile/10"
	 *     $route->uri(array(
	 *         'controller' => 'users',
	 *         'action'     => 'profile',
	 *         'id'         => '10'
	 *     ));
	 *
	 * @param   array   $params URI parameters
	 * @return  string
	 * @throws  Kohana_Exception
	 * @uses    Route::REGEX_GROUP
	 * @uses    Route::REGEX_KEY
	 */
	public function uri(array $params = NULL)
	{
		$defaults = $this->_defaults;

		/**
		 * Recursively compiles a portion of a URI specification by replacing
		 * the specified parameters and any optional parameters that are needed.
		 *
		 * @param   string  $portion    Part of the URI specification
		 * @param   boolean $required   Whether or not parameters are required (initially)
		 * @return  array   Tuple of the compiled portion and whether or not it contained specified parameters
		 */
		$compile = function ($portion, $required) use (&$compile, $defaults, $params)
		{
			$missing = array();

			$pattern = '#(?:'.Route::REGEX_KEY.'|'.Route::REGEX_GROUP.')#';
			$result = preg_replace_callback($pattern, function ($matches) use (&$compile, $defaults, &$missing, $params, &$required)
			{
				if ($matches[0][0] === '<')
				{
					// Parameter, unwrapped
					$param = $matches[1];

					if (isset($params[$param]))
					{
						// This portion is required when a specified
						// parameter does not match the default
						$required = ($required OR ! isset($defaults[$param]) OR $params[$param] !== $defaults[$param]);

						// Add specified parameter to this result
						return $params[$param];
					}

					// Add default parameter to this result
					if (isset($defaults[$param]))
						return $defaults[$param];

					// This portion is missing a parameter
					$missing[] = $param;
				}
				else
				{
					// Group, unwrapped
					$result = $compile($matches[2], FALSE);

					if ($result[1])
					{
						// This portion is required when it contains a group
						// that is required
						$required = TRUE;

						// Add required groups to this result
						return $result[0];
					}

					// Do not add optional groups to this result
				}
			}, $portion);

			if ($required AND $missing)
			{
				throw new Kohana_Exception(
					'Required route parameter not passed: :param',
					array(':param' => reset($missing))
				);
			}

			return array($result, $required);
		};

		list($uri) = $compile($this->_uri, TRUE);

		// Trim all extra slashes from the URI
		$uri = preg_replace('#//+#', '/', rtrim($uri, '/'));

		if ($this->is_external())
		{
			// Need to add the host to the URI
			$host = $this->_defaults['host'];

			if (strpos($host, '://') === FALSE)
			{
				// Use the default defined protocol
				$host = Route::$default_protocol.$host;
			}

			// Clean up the host and prepend it to the URI
			$uri = rtrim($host, '/').'/'.$uri;
		}

		return $uri;
	}

}

<?php

namespace Kohana\Core;

use Exception;
use Kohana\Core\Kohana\KohanaException;
use Kohana\Core\View\ViewException;
use Traversable;

/**
 * Acts as an object wrapper for HTML pages with embedded PHP, called "views".
 * Variables can be assigned with the view object and referenced locally within
 * the view.
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class View {

	/**
	 * @param mixed[] Array of global variables
	 * @deprecated
	 */
	protected static $_global_data = array();

	/**
	 * Returns a new View object. If you do not define the "file" parameter,
	 * you must call [View::set_filename].
	 *
	 *     $view = View::factory($file);
	 *
	 * @param   string  $file   view filename
	 * @param   array   $data   array of values
	 * @return  View
	 */
	public static function factory($file = NULL, array $data = NULL)
	{
		return new View($file, $data);
	}

	/**
	 * Captures the output that is generated when a view is included.
	 * The view data will be extracted to make local variables. This method
	 * is static to prevent object scope resolution.
	 *
	 *     $output = View::capture($file, $data);
	 *
	 * @param   string  $kohana_view_filename   filename
	 * @param   array   $kohana_view_data       variables
	 * @return  string
	 * @throws  Exception
	 */
	protected static function capture($kohana_view_filename, array $kohana_view_data)
	{
		// Import the view variables to local namespace
		extract($kohana_view_data, EXTR_SKIP);

		if (View::$_global_data)
		{
			// Import the global view variables to local namespace
			extract(View::$_global_data, EXTR_SKIP | EXTR_REFS);
		}

		// Capture the view output
		ob_start();

		try
		{
			// Load the view within the current scope
			include $kohana_view_filename;
		}
		catch (Exception $e)
		{
			// Delete the output buffer
			ob_end_clean();

			// Re-throw the exception
			throw $e;
		}

		// Get the captured output and close the buffer
		return ob_get_clean();
	}

	/**
	 * Sets a global variable, similar to [View::set], except that the
	 * variable will be accessible to all views.
	 *
	 *     View::set_global($name, $value);
	 * 
	 * [!!] Use of global view variables is deprecated and strongly discouraged
	 *
	 * You can also use an array or Traversable object to set several values at once:
	 *
	 *     // Create the values $food and $beverage in the view
	 *     View::set_global(array('food' => 'bread', 'beverage' => 'water'));
	 *
	 * [!!] Note: When setting with using Traversable object we're not attaching the whole object to the view,
	 * i.e. the object's standard properties will not be available in the view context.
	 *
	 * @deprecated in favour of setting relevant variables on each specific view
	 * @param   string|array|Traversable  $key    variable name or an array of variables
	 * @param   mixed                     $value  value
	 * @return  void
	 */
	public static function set_global($key, $value = NULL)
	{
		if (is_array($key) OR $key instanceof Traversable)
		{
			foreach ($key as $name => $value)
			{
				View::$_global_data[$name] = $value;
			}
		}
		else
		{
			View::$_global_data[$key] = $value;
		}
	}

	/**
	 * Assigns a global variable by reference, similar to [View::bind], except
	 * that the variable will be accessible to all views.
	 *
	 *     View::bind_global($key, $value);
	 *
	 * [!!] Use of global view variables is deprecated and strongly discouraged
	 *
	 * @deprecated in favour of setting relevant variables on each specific view
	 * @param   string  $key    variable name
	 * @param   mixed   $value  referenced variable
	 * @return  void
	 */
	public static function bind_global($key, & $value)
	{
		View::$_global_data[$key] =& $value;
	}

	// View filename
	protected $_file;

	// Array of local variables
	protected $_data = array();

	/**
	 * Sets the initial view filename and local data. Views should almost
	 * always only be created using [View::factory].
	 *
	 *     $view = new View($file);
	 *
	 * @param   string  $file   view filename
	 * @param   array   $data   array of values
	 * @return  void
	 * @uses    View::set_filename
	 */
	public function __construct($file = NULL, array $data = NULL)
	{
		if ($file !== NULL)
		{
			$this->set_filename($file);
		}

		if ($data !== NULL)
		{
			// Add the values to the current data
			$this->_data = $data + $this->_data;
		}
	}

	/**
	 * Magic method, searches for the given variable and returns its value.
	 * Local variables will be returned before global variables.
	 *
	 *     $value = $view->foo;
	 *
	 * [!!] If the variable has not yet been set, an exception will be thrown.
	 *
	 * @param   string  $key    variable name
	 * @return  mixed
	 * @throws  KohanaException
	 */
	public function & __get($key)
	{
		if (array_key_exists($key, $this->_data))
		{
			return $this->_data[$key];
		}
		elseif (array_key_exists($key, View::$_global_data))
		{
			return View::$_global_data[$key];
		}
		else
		{
			throw new KohanaException('View variable is not set: :var',
				array(':var' => $key));
		}
	}

	/**
	 * Magic method, calls [View::set] with the same parameters.
	 *
	 *     $view->foo = 'something';
	 *
	 * @param   string  $key    variable name
	 * @param   mixed   $value  value
	 * @return  void
	 */
	public function __set($key, $value)
	{
		$this->set($key, $value);
	}

	/**
	 * Magic method, determines if a variable is set.
	 *
	 *     isset($view->foo);
	 *
	 * [!!] `NULL` variables are not considered to be set by [isset](http://php.net/isset).
	 *
	 * @param   string  $key    variable name
	 * @return  boolean
	 */
	public function __isset($key)
	{
		return (isset($this->_data[$key]) OR isset(View::$_global_data[$key]));
	}

	/**
	 * Magic method, unsets a given variable.
	 *
	 *     unset($view->foo);
	 *
	 * @param   string  $key    variable name
	 * @return  void
	 */
	public function __unset($key)
	{
		unset($this->_data[$key], View::$_global_data[$key]);
	}

	/**
	 * Magic method, returns the output of [View::render].
	 *
	 * @return  string
	 * @uses    View::render
	 */
	public function __toString()
	{
		try
		{
			return $this->render();
		}
		catch (Exception $e)
		{
			/**
			 * Display the exception message.
			 *
			 * We use this method here because it's impossible to throw an
			 * exception from __toString().
			 */
			$error_response = KohanaException::_handler($e);

			return $error_response->body();
		}
	}

	/**
	 * Sets the view filename.
	 *
	 *     $view->set_filename($file);
	 *
	 * @param   string  $file   view filename
	 * @return  View
	 * @throws  ViewException
	 */
	public function set_filename($file)
	{
		if (($path = Core::find_file('views', $file)) === FALSE)
		{
			throw new ViewException('The requested view :file could not be found', array(
				':file' => $file,
			));
		}

		// Store the file path locally
		$this->_file = $path;

		return $this;
	}

	/**
	 * Assigns a variable by name. Assigned values will be available as a
	 * variable within the view file:
	 *
	 *     // This value can be accessed as $foo within the view
	 *     $view->set('foo', 'my value');
	 *
	 * You can also use an array or Traversable object to set several values at once:
	 *
	 *     // Create the values $food and $beverage in the view
	 *     $view->set(array('food' => 'bread', 'beverage' => 'water'));
	 *
	 * [!!] Note: When setting with using Traversable object we're not attaching the whole object to the view,
	 * i.e. the object's standard properties will not be available in the view context.
	 *
	 * @param   string|array|Traversable  $key    variable name or an array of variables
	 * @param   mixed                     $value  value
	 * @return  $this
	 */
	public function set($key, $value = NULL)
	{
		if (is_array($key) OR $key instanceof Traversable)
		{
			foreach ($key as $name => $value)
			{
				$this->_data[$name] = $value;
			}
		}
		else
		{
			$this->_data[$key] = $value;
		}

		return $this;
	}

	/**
	 * Assigns a value by reference. The benefit of binding is that values can
	 * be altered without re-setting them. It is also possible to bind variables
	 * before they have values. Assigned values will be available as a
	 * variable within the view file:
	 *
	 *     // This reference can be accessed as $ref within the view
	 *     $view->bind('ref', $bar);
	 *
	 * @param   string  $key    variable name
	 * @param   mixed   $value  referenced variable
	 * @return  $this
	 */
	public function bind($key, & $value)
	{
		$this->_data[$key] =& $value;

		return $this;
	}

	/**
	 * Renders the view object to a string. Global and local data are merged
	 * and extracted to create local variables within the view file.
	 *
	 *     $output = $view->render();
	 *
	 * [!!] Global variables with the same key name as local variables will be
	 * overwritten by the local variable.
	 *
	 * @param   string  $file   view filename
	 * @return  string
	 * @throws  ViewException
	 * @uses    View::capture
	 */
	public function render($file = NULL)
	{
		if ($file !== NULL)
		{
			$this->set_filename($file);
		}

		if (empty($this->_file))
		{
			throw new ViewException('You must set the file to use within your view before rendering');
		}

		// Combine local and global data and capture the output
		return View::capture($this->_file, $this->_data);
	}

}

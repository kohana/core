<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Base session class.
 *
 * @package    Kohana
 * @category   Session
 * @author     Kohana Team
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
abstract class Kohana_Session {

	/**
	 * @var  string  default session adapter
	 */
	public static $default = null;

	/**
	 * @var  array  session instances
	 */
	protected static $instances = array();

	
	
	/**
	 * 
	 * Function used to create a singleton instance of a session. This functionality changed in 3.0 so the function has
	 * been renamed to conform with the  Lazy Initialization pattern as opposed to Singleton.
	 * The result is that this function will return the same session of a given type.
	 * 
	 * @deprecated deprecated as it doesn't do what it says on the tin. Use getSession instead. Should be removed in next major release.
	 * @param   string   type of session (native, cookie, etc)
	 * @param   string   session identifier
	 * @return  Session
	 * @uses    self::getSession()
	 */
	public static function instance($type = NULL, $id = NULL)
	{
		return Session::getSession($type,$id);
	}
	
	/**
	 * Creates a Lazy Initialization (singleton of a given type) session of the given type. Some session types
	 * (native, database) also support restarting a session by passing a
	 * session id as the second parameter.
	 *
	 *     $session = Session::instance();
	 *
	 * [!!] [Session::write] will automatically be called when the request ends.
	 *
	 * @param   string   type of session (native, cookie, etc)
	 * @param   string   session identifier
	 * @return  Session
	 * @uses    Kohana::config
	 * @throws Kohana_Exception if session cookie name isn't defined in configuration or if there is a name collision.
	 */
	public static function getSession($type = NULL, $id = NULL)
	{
		
		
		if ($type === NULL)
		{
			// Use the default type
			// Either from the config, or from Session::$default if it is present
			// If both are NULL, then it will set it to native.
			$type = Session::$default===NULL?Kohana::config("session")->get('default','native'):Session::$default;
					
		}
		
		if ( ! isset(Session::$instances[$type]))
		{
			//Check for collisions of driver cookie names.
			$driver_names = array();
			foreach(($config = Kohana::config("session")) as $driver => $driver_config)
			{
				if(!isset($driver_config['name'])||array_key_exists($driver_config['name'],$driver_names))
					throw new Kohana_Exception("Session 'name' missing or collision error in configuration for :driver driver",array(":driver"=>$driver));
				$driver_names[$driver_config['name']]=true;
			}
			
			// Load the configuration for this type
			$config = $config[$type];
			
			// Set the session class name
			$class = 'Session_'.ucfirst($type);

			// Create a new session instance
			Session::$instances[$type] = $session = new $class($config, $id);

			// Write the session at shutdown
			register_shutdown_function(array($session, 'write'));
		}
		else if(isset($id)&&Session::$instances[$type]->id()!==$id)
			throw Kohana_Exception("You can't change session IDs half way through execution");

		return Session::$instances[$type];
	}

	/**
	 * @var  string  cookie name
	 */
	protected $_name = 'session';

	/**
	 * @var  int  cookie lifetime
	 */
	protected $_lifetime = 0;

	/**
	 * @var  bool  encrypt session data?
	 */
	protected $_encrypted = FALSE;

	/**
	 * @var  array  session data
	 */
	protected $_data = array();

	/**
	 * @var  bool  session destroyed?
	 */
	protected $_destroyed = FALSE;

	/**
	 * Overloads the name, lifetime, and encrypted session settings.
	 *
	 * [!!] Sessions can only be created using the [Session::instance] method.
	 *
	 * @param   array   configuration
	 * @param   string  session id
	 * @return  void
	 * @uses    Session::read
	 */
	public function __construct(array $config = NULL, $id = NULL)
	{
		if (isset($config['name']))
		{
			// Cookie name to store the session id in
			$this->_name = (string) $config['name'];
		}

		if (isset($config['lifetime']))
		{
			// Cookie lifetime
			$this->_lifetime = (int) $config['lifetime'];
		}

		if (isset($config['encrypted']))
		{
			if ($config['encrypted'] === TRUE)
			{
				// Use the default Encrypt instance
				$config['encrypted'] = 'default';
			}

			// Enable or disable encryption of data
			$this->_encrypted = $config['encrypted'];
		}

		// Load the session
		$this->read($id);
	}

	/**
	 * Session object is rendered to a serialized string. If encryption is
	 * enabled, the session will be encrypted. If not, the output string will
	 * be encoded using [base64_encode].
	 *
	 *     echo $session;
	 *
	 * @return  string
	 * @uses    Encrypt::encode
	 */
	public function __toString()
	{
		// Serialize the data array
		$data = serialize($this->_data);

		if ($this->_encrypted)
		{
			// Encrypt the data using the default key
			$data = Encrypt::instance($this->_encrypted)->encode($data);
		}
		else
		{
			// Obfuscate the data with base64 encoding
			$data = base64_encode($data);
		}

		return $data;
	}

	/**
	 * Returns the current session array. The returned array can also be
	 * assigned by reference.
	 *
	 *     // Get a copy of the current session data
	 *     $data = $session->as_array();
	 *
	 *     // Assign by reference for modification
	 *     $data =& $session->as_array();
	 *
	 * @return  array
	 */
	public function & as_array()
	{
		return $this->_data;
	}

	/**
	 * Get the current session id, if the session supports it.
	 *
	 *     $id = $session->id();
	 *
	 * [!!] Not all session types have ids.
	 *
	 * @return  string
	 * @since   3.0.8
	 */
	public function id()
	{
		return NULL;
	}

	/**
	 * Get the current session cookie name.
	 *
	 *     $name = $session->name();
	 *
	 * @return  string
	 * @since   3.0.8
	 */
	public function name()
	{
		return $this->_name;
	}

	/**
	 * Get a variable from the session array.
	 *
	 *     $foo = $session->get('foo');
	 *
	 * @param   string   variable name
	 * @param   mixed    default value to return
	 * @return  mixed
	 */
	public function get($key, $default = NULL)
	{
		return array_key_exists($key, $this->_data) ? $this->_data[$key] : $default;
	}

	/**
	 * Get and delete a variable from the session array.
	 *
	 *     $bar = $session->get_once('bar');
	 *
	 * @param   string  variable name
	 * @param   mixed   default value to return
	 * @return  mixed
	 */
	public function get_once($key, $default = NULL)
	{
		$value = $this->get($key, $default);

		unset($this->_data[$key]);

		return $value;
	}

	/**
	 * Set a variable in the session array.
	 *
	 *     $session->set('foo', 'bar');
	 *
	 * @param   string   variable name
	 * @param   mixed    value
	 * @return  $this
	 */
	public function set($key, $value)
	{
		$this->_data[$key] = $value;

		return $this;
	}

	/**
	 * Set a variable by reference.
	 *
	 *     $session->bind('foo', $foo);
	 *
	 * @param   string  variable name
	 * @param   mixed   referenced value
	 * @return  $this
	 */
	public function bind($key, & $value)
	{
		$this->_data[$key] =& $value;

		return $this;
	}

	/**
	 * Removes a variable in the session array.
	 *
	 *     $session->delete('foo');
	 *
	 * @param   string  variable name
	 * @param   ...
	 * @return  $this
	 */
	public function delete($key)
	{
		$args = func_get_args();

		foreach ($args as $key)
		{
			unset($this->_data[$key]);
		}

		return $this;
	}

	/**
	 * Loads existing session data.
	 *
	 *     $session->read();
	 *
	 * @param   string   session id
	 * @return  void
	 */
	public function read($id = NULL)
	{
		if (is_string($data = $this->_read($id)))
		{
			try
			{
				if ($this->_encrypted)
				{
					// Decrypt the data using the default key
					$data = Encrypt::instance($this->_encrypted)->decode($data);
				}
				else
				{
					// Decode the base64 encoded data
					$data = base64_decode($data);
				}

				// Unserialize the data
				$data = unserialize($data);
			}
			catch (Exception $e)
			{
				// Ignore all reading errors
			}
		}

		if (is_array($data))
		{
			// Load the data locally
			$this->_data = $data;
		}
	}

	/**
	 * Generates a new session id and returns it.
	 *
	 *     $id = $session->regenerate();
	 *
	 * @return  string
	 */
	public function regenerate()
	{
		return $this->_regenerate();
	}

	/**
	 * Sets the last_active timestamp and saves the session.
	 *
	 *     $session->write();
	 *
	 * [!!] Any errors that occur during session writing will be logged,
	 * but not displayed, because sessions are written after output has
	 * been sent.
	 *
	 * @return  boolean
	 * @uses    Kohana::$log
	 */
	public function write()
	{
		if (headers_sent() OR $this->_destroyed)
		{
			// Session cannot be written when the headers are sent or when
			// the session has been destroyed
			return FALSE;
		}

		// Set the last active timestamp
		$this->_data['last_active'] = time();

		try
		{
			return $this->_write();
		}
		catch (Exception $e)
		{
			// Log & ignore all errors when a write fails
			Kohana::$log->add(Kohana::ERROR, Kohana::exception_text($e))->write();

			return FALSE;
		}
	}

	/**
	 * Completely destroy the current session.
	 *
	 *     $success = $session->destroy();
	 *
	 * @return  boolean
	 */
	public function destroy()
	{
		if ($this->_destroyed === FALSE)
		{
			if ($this->_destroyed = $this->_destroy())
			{
				// The session has been destroyed, clear all data
				$this->_data = array();
			}
		}

		return $this->_destroyed;
	}

	/**
	 * Loads the raw session data string and returns it.
	 *
	 * @param   string   session id
	 * @return  string
	 */
	abstract protected function _read($id = NULL);

	/**
	 * Generate a new session id and return it.
	 *
	 * @return  string
	 */
	abstract protected function _regenerate();

	/**
	 * Writes the current session.
	 *
	 * @return  boolean
	 */
	abstract protected function _write();

	/**
	 * Destroys the current session.
	 *
	 * @return  boolean
	 */
	abstract protected function _destroy();

} // End Session

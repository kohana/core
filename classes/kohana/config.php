<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Wrapper for configuration arrays.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Kohana_Config {

	// Singleton static instance
	protected static $_instance;

	/**
	 * Get the singleton instance of Kohana_Config.
	 *
	 * @return  Kohana_Config
	 */
	public static function instance()
	{
		if (self::$_instance === NULL)
		{
			// Create a new instance
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	// Configuration readers
	protected $_readers = array();

	/**
	 * Attach a configuration reader.
	 *
	 * @param   object   Kohana_Config_Reader instance
	 * @param   boolean  add the reader as the first used object
	 * @return  $this
	 */
	public function attach(Kohana_Config_Reader $reader, $first = TRUE)
	{
		if ($first === TRUE)
		{
			// Place the log reader at the top of the stack
			array_unshift($this->_readers, $reader);
		}
		else
		{
			// Place the reader at the bottom of the stack
			$this->_readers[] = $reader;
		}

		return $this;
	}

	/**
	 * Detaches a configuration reader.
	 *
	 * @param   object  Kohana_Config_Reader instance
	 * @return  $this
	 */
	public function detach(Kohana_Config_Reader $reader)
	{
		if (($key = array_search($reader, $this->_readers)))
		{
			// Remove the writer
			unset($this->_readers[$key]);
		}

		return $this;
	}

	/**
	 * Load a configuration group. Searches the readers in order until the
	 * group is found. If the group does not exist, an empty configuration
	 * array will be loaded using the first reader.
	 *
	 * @param   string  configuration group
	 * @return  object  Kohana_Config_Reader
	 */
	public function load($group)
	{
		foreach ($this->_readers as $reader)
		{
			if ($config = $reader->load($group))
			{
				// Found a reader for this configuration group
				return $config;
			}
		}

		// Reset the iterator
		reset($this->_readers);

		if ( ! is_object($config = current($this->_readers)))
		{
			throw new Kohana_Exception('No configuration readers attached');
		}

		// Load the reader as an empty array
		return $config->load($group, array());
	}

	/**
	 * Copy one configuration group to all of the other readers.
	 *
	 * @param   string   group name
	 * @return  $this
	 */
	public function copy($group)
	{
		// Load the configuration group
		$config = $this->load($group);

		foreach ($this->_readers as $reader)
		{
			if ($config instanceof $reader)
			{
				// Do not copy the config to the same group
				continue;
			}

			// Load the configuration object
			$object = $reader->load($group, array());

			foreach ($config as $key => $value)
			{
				// Copy each value in the config
				$object->offsetSet($key, $value);
			}
		}

		return $this;
	}

	final private function __construct()
	{
		// Enforce singleton behavior
	}

	final private function __clone()
	{
		// Enforce singleton behavior
	}

} // End Kohana_Config

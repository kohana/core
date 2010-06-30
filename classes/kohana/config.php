<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Wrapper for configuration arrays. Multiple configuration readers can be
 * attached to allow loading configuration from files, database, etc.
 *
 * @package    Kohana
 * @category   Configuration
 * @author     Kohana Team
 * @copyright  (c) 2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Config {

	// Singleton static instance
	protected static $_instance;

	/**
	 * Get the singleton instance of Kohana_Config.
	 *
	 *     $config = Kohana_Config::instance();
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
	 * Attach a configuration reader. By default, the reader will be added as
	 * the first used reader. However, if the reader should be used only when
	 * all other readers fail, use `FALSE` for the second parameter.
	 *
	 *     $config->attach($reader);        // Try first
	 *     $config->attach($reader, FALSE); // Try last
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
	 * Detach a configuration reader.
	 *
	 *     $config->detach($reader);
	 *
	 * @param   object  Kohana_Config_Reader instance
	 * @return  $this
	 */
	public function detach(Kohana_Config_Reader $reader)
	{
		if (($key = array_search($reader, $this->_readers)) !== FALSE)
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
	 *     $array = $config->load($name);
	 *
	 * @param   string  configuration group name
	 * @return  object  Kohana_Config_Reader
	 * @throws  Kohana_Exception
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
	 *     $config->copy($name);
	 *
	 * @param   string   configuration group name
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

} // End Kohana_Config

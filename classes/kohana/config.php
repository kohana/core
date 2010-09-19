<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Wrapper for configuration arrays. Multiple configuration readers can be
 * attached to allow loading configuration from files, database, etc.
 *
 * Configuration directives cascade across config sources in the same way that 
 * files cascade across the filesystem.
 *
 * Directives from sources high in the sources list will override ones from those
 * below them.
 *
 * @package    Kohana
 * @category   Configuration
 * @author     Kohana Team
 * @copyright  (c) 2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Config {

	/**
	 * Singleton instance
	 * @var Kohana_Config
	 */
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
	protected $_sources = array();

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
	public function attach(Kohana_Config_Source $source, $first = TRUE)
	{
		if ($first === TRUE)
		{
			// Place the log reader at the top of the stack
			array_unshift($this->_sources, $source);
		}
		else
		{
			// Place the reader at the bottom of the stack
			$this->_sources[] = $source;
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
	public function detach(Kohana_Config_Source $source)
	{
		if (($key = array_search($source, $this->_sources)) !== FALSE)
		{
			// Remove the writer
			unset($this->_sources[$key]);
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
		if( ! count($this->_sources))
		{
			throw new Kohana_Exception('No configuration sources attached');
		}

		if(isset($this->_groups[$group]))
		{
			return $this->_groups[$group];
		}

		$config = array();

		// We search with the "lowest" source and work our way up
		$sources = array_reverse($this->_sources);

		foreach ($sources as $source)
		{
			if ($source instanceof Kohana_Config_Reader)
			{
				$config = $source->load($group) + $config;
			}
		}

		return $this->_groups[$group] = new Kohana_Config_Group($this, $group, $config);
	}

	/**
	 * Copy one configuration group to all of the other writers.
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

		foreach ($this->_sources as $source)
		{
			if ( ! ($source instanceof Kohana_Config_Writer))
			{
				continue;
			}

			foreach ($config as $key => $value)
			{
				// Copy each value in the config
				$source->write($group, $key, $config);
			}
		}

		return $this;
	}

} // End Kohana_Config

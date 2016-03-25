<?php

namespace Kohana\Core;

use Kohana\Core\Config\Group;
use Kohana\Core\Config\Reader;
use Kohana\Core\Config\Source;
use Kohana\Core\Config\Writer;
use Kohana\Core\Kohana\KohanaException;

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
 * @copyright  (c) 2009-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Config {

	// Configuration readers
	protected $_sources = array();

	// Array of config groups
	protected $_groups = array();

	/**
	 * Attach a configuration reader. By default, the reader will be added as
	 * the first used reader. However, if the reader should be used only when
	 * all other readers fail, use `FALSE` for the second parameter.
	 *
	 *     $config->attach($reader);        // Try first
	 *     $config->attach($reader, FALSE); // Try last
	 *
	 * @param   Source    $source instance
	 * @param   boolean   $first  add the reader as the first used object
	 * @return  $this
	 */
	public function attach(Source $source, $first = TRUE)
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

		// Clear any cached _groups
		$this->_groups = array();

		return $this;
	}

	/**
	 * Detach a configuration reader.
	 *
	 *     $config->detach($reader);
	 *
	 * @param   Source    $source instance
	 * @return  $this
	 */
	public function detach(Source $source)
	{
		if (($key = array_search($source, $this->_sources)) !== FALSE)
		{
			// Remove the writer
			unset($this->_sources[$key]);
		}

		return $this;
	}

	/**
	 * Load a configuration group. Searches all the config sources, merging all the
	 * directives found into a single config group.  Any changes made to the config
	 * in this group will be mirrored across all writable sources.
	 *
	 *     $array = $config->load($name);
	 *
	 * See [Group] for more info
	 *
	 * @param   string  $group  configuration group name
	 * @return  Group
	 * @throws  KohanaException
	 */
	public function load($group)
	{
		if ( ! count($this->_sources))
		{
			throw new KohanaException('No configuration sources attached');
		}

		if (empty($group))
		{
			throw new KohanaException("Need to specify a config group");
		}

		if ( ! is_string($group))
		{
			throw new KohanaException("Config group must be a string");
		}

		if (strpos($group, '.') !== FALSE)
		{
			// Split the config group and path
			list($group, $path) = explode('.', $group, 2);
		}

		if (isset($this->_groups[$group]))
		{
			if (isset($path))
			{
				return Arr::path($this->_groups[$group], $path, NULL, '.');
			}
			return $this->_groups[$group];
		}

		$config = array();

		// We search from the "lowest" source and work our way up
		$sources = array_reverse($this->_sources);

		foreach ($sources as $source)
		{
			if ($source instanceof Reader)
			{
				if ($source_config = $source->load($group))
				{
					$config = Arr::merge($config, $source_config);
				}
			}
		}

		$this->_groups[$group] = new Group($this, $group, $config);

		if (isset($path))
		{
			return Arr::path($config, $path, NULL, '.');
		}

		return $this->_groups[$group];
	}

	/**
	 * Copy one configuration group to all of the other writers.
	 *
	 *     $config->copy($name);
	 *
	 * @param   string  $group  configuration group name
	 * @return  $this
	 */
	public function copy($group)
	{
		// Load the configuration group
		$config = $this->load($group);

		foreach ($config->as_array() as $key => $value)
		{
			$this->_write_config($group, $key, $value);
		}

		return $this;
	}

	/**
	 * Callback used by the config group to store changes made to configuration
	 *
	 * @param string    $group  Group name
	 * @param string    $key    Variable name
	 * @param mixed     $value  The new value
	 * @return Config Chainable instance
	 */
	public function _write_config($group, $key, $value)
	{
		foreach ($this->_sources as $source)
		{
			if ( ! ($source instanceof Writer))
			{
				continue;
			}

			// Copy each value in the config
			$source->write($group, $key, $value);
		}

		return $this;
	}

}

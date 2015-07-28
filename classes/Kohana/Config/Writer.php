<?php

namespace Kohana\Core\Config;

/**
 * Interface for config writers
 *
 * Specifies the methods that a config writer must implement
 *
 * @package Kohana
 * @author  Kohana Team
 * @copyright  (c) 2008-2014 Kohana Team
 * @license    http://kohanaframework.org/license
 */
interface Writer extends Source
{
	/**
	 * Writes the passed config for $group
	 *
	 * Returns chainable instance on success or throws
	 * Kohana_Config_Exception on failure
	 *
	 * @param string      $group  The config group
	 * @param string      $key    The config key to write to
	 * @param array       $config The configuration to write
	 * @return boolean
	 */
	public function write($group, $key, $config);

}

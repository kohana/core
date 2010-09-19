<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Interface for config readers
 *
 * Specifies the methods that a config reader must implement 
 * 
 * @package Kohana
 * @author  Kohana Team
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
Interface Kohana_Config_Reader extends Kohana_Config_Source
{
	
	/**
	 * Tries to load the specificed configuration group
	 *
	 * Returns FALSE if group does not exist or an array if it does
	 *
	 * @param  string $group Configuration group
	 * @return boolean|array
	 */
	public function load($group);
	
}
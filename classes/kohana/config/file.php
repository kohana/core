<?php defined('SYSPATH') or die('No direct script access.');
/**
 * File-based configuration reader. Multiple configuration directories can be
 * used by attaching multiple instances of this class to [Config].
 *
 * @package    Kohana
 * @category   Configuration
 * @author     Kohana Team
 * @copyright  (c) 2009-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Config_File extends Config_Reader {

	/**
	 * @var  string  Configuration group name
	 */
	protected $_configuration_group;

	/**
	 * @var  bool  Has the config group changed?
	 */
	protected $_configuration_modified = FALSE;

	public function __construct($directory = 'config')
	{
		// Set the configuration directory name
		$this->_directory = trim($directory, '/');

		// Load the empty array
		parent::__construct();
	}

	/**
	 * Load and merge all of the configuration files in this group.
	 *
	 *     $config->load($name);
	 *
	 * @param   string  configuration group name
	 * @param   array   configuration array
	 * @return  $this   clone of the current object
	 * @uses    Kohana::load
	 */
	public function load($group, array $config = NULL)
	{
		if ($files = Kohana::find_file($this->_directory, $group, NULL, TRUE))
		{
			// Initialize the config array
			$config = array();

			foreach ($files as $file)
			{
				// Merge each file to the configuration array
				$config = Arr::merge($config, Kohana::load($file));
			}
		}

		return parent::load($group, $config);
	}

} // End Kohana_Config_File

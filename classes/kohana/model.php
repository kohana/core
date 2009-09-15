<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model base class.
 *
 * @package    Model
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
abstract class Kohana_Model {

	/**
	 * Create a new model instance.
	 *
	 * @param   string   model name
	 * @param   mixed    Database instance object or string
	 * @return  Model
	 */
	public static function factory($name, $db = NULL)
	{
		// Add the model prefix
		$class = 'Model_'.$name;

		return new $class($db);
	}

	// Database instance
	protected $_db = 'default';

	/**
	 * Loads the database.
	 *
	 * @param   mixed  Database instance object or string
	 * @return  void
	 */
	public function __construct($db = NULL)
	{
		if ($db !== NULL)
		{
			// Set the database instance name
			$this->_db = $db;
		}

		if (is_string($this->_db))
		{
			// Load the database
			$this->_db = Database::instance($this->_db);
		}
	}

} // End Model

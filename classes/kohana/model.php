<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model base class. All models should extend this class.
 *
 * @package    Kohana
 * @category   Models
 * @author     Kohana Team
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
abstract class Kohana_Model {

	/**
	 * Create a new model instance. A [Database] instance or configuration
	 * group name can be passed to the model. If no database is defined, the
	 * "default" database group will be used.
	 *
	 *     $model = Model::factory($name);
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

	/**
	 * @var  Database  database instance
	 */
	protected $_db;

	/**
	 * Loads the database.
	 *
	 *     $model = new Foo_Model($db);
	 *
	 * @param   mixed  Database instance object or string
	 * @return  void
	 */
	public function __construct($db = NULL)
	{
		if ($db)
		{
			// Set the database instance to use
			$this->_db = $db;
		}
		elseif ( ! $this->_db)
		{
			// Use the global database
			$this->_db = Database::$default;
		}

		if (is_string($this->_db))
		{
			// Load the database
			$this->_db = Database::instance($this->_db);
		}
	}

} // End Model

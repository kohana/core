<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database-based configuration loader.
 *
 * Schema for configuration table:
 *
 *     group_name    varchar(128)
 *     config_key    varchar(128)
 *     config_value  text
 *     primary key   (group_name, config_key)
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Kohana_Config_Database extends Kohana_Config_Reader {

	protected $_database_instance = 'default';

	protected $_database_table = 'config';

	public function __construct(array $config = NULL)
	{
		if (isset($config['instance']))
		{
			$this->_database_instance = $config['instance'];
		}

		if (isset($config['table']))
		{
			$this->_database_table = $config['table'];
		}

		parent::__construct();
	}

	/**
	 * Query the configuration table for all values for this group and
	 * unserialize each of the values.
	 *
	 * @param   string  group name
	 * @param   array   configuration array
	 * @return  $this   clone of the current object
	 */
	public function load($group, array $config = NULL)
	{
		if ($config === NULL AND $group !== 'database')
		{
			// Load all of the configuration values for this group
			$query = DB::select('config_key', 'config_value')
				->from($this->_database_table)
				->where('group_name', '=', $group)
				->execute($this->_database_instance);

			if (count($query) > 0)
			{
				// Unserialize the configuration values
				$config = array_map('unserialize', $query->as_array('config_key', 'config_value'));
			}
		}

		return parent::load($group, $config);
	}

	/**
	 * Overload setting offsets to insert or update the database values as
	 * changes occur.
	 *
	 * @param   string   array key
	 * @param   mixed    new value
	 * @return  mixed
	 */
	public function offsetSet($key, $value)
	{
		if ( ! $this->offsetExists($key))
		{
			// Insert a new value
			DB::insert($this->_database_table, array('group_name', 'config_key', 'config_value'))
				->values(array($this->_configuration_group, $key, serialize($value)))
				->execute($this->_database_instance);
		}
		elseif ($this->offsetGet($key) !== $value)
		{
			// Update the value
			DB::update($this->_database_table)
				->value('config_value', serialize($value))
				->where('group_name', '=', $this->_configuration_group)
				->where('config_key', '=', $key)
				->execute($this->_database_instance);
		}

		return parent::offsetSet($key, $value);
	}

} // End Kohana_Config_Database

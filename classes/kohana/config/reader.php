<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Abstract configuration reader. All configuration readers must extend
 * this class.
 *
 * @package    Kohana
 * @category   Configuration
 * @author     Kohana Team
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
abstract class Kohana_Config_Reader extends ArrayObject {

	/**
	 * @var  string  Configuration group name
	 */
	protected $_configuration_group;

	/**
	 * Loads an empty array as the initial configuration and enables array
	 * keys to be used as properties.
	 *
	 * @return  void
	 */
	public function __construct()
	{
		parent::__construct(array(), ArrayObject::ARRAY_AS_PROPS);
	}

	/**
	 * Return the current group in serialized form.
	 *
	 *     echo $config;
	 *
	 * @return  string
	 */
	public function __toString()
	{
		return serialize($this->getArrayCopy());
	}

	/**
	 * Loads a configuration group.
	 *
	 *     $config->load($name, $array);
	 *
	 * This method must be extended by all readers. After the group has been
	 * loaded, call `parent::load($group, $config)` for final preparation.
	 *
	 * @param   string  configuration group name
	 * @param   array   configuration array
	 * @return  $this   a clone of this object
	 */
	public function load($group, array $config = NULL)
	{
		if ($config === NULL)
		{
			return FALSE;
		}

		// Clone the current object
		$object = clone $this;

		// Set the group name
		$object->_configuration_group = $group;

		// Swap the array with the actual configuration
		$object->exchangeArray($config);

		return $object;
	}

	/**
	 * Return the raw array that is being used for this object.
	 *
	 *     $array = $config->as_array();
	 *
	 * @return  array
	 */
	public function as_array()
	{
		return $this->getArrayCopy();
	}

	/**
	 * Get a variable from the configuration or return the default value.
	 *
	 *     $value = $config->get($key);
	 *
	 * @param   string   array key
	 * @param   mixed    default value
	 * @return  mixed
	 */
	public function get($key, $default = NULL)
	{
		return $this->offsetExists($key) ? $this->offsetGet($key) : $default;
	}

	/**
	 * Sets a value in the configuration array.
	 *
	 *     $config->set($key, $new_value);
	 *
	 * @param   string   array key
	 * @param   mixed    array value
	 * @return  $this
	 */
	public function set($key, $value)
	{
		$this->offsetSet($key, $value);

		return $this;
	}

} // End Kohana_Config_Reader

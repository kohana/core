<?php
/**
 * The Encrypt does not implement crypto ops by itself, rather provides a
 * factory method to instantiate objects implementing Kohana_Crypto using
 * config settings loaded via Kohana_Config.
 *
 * @package    Kohana
 * @category   Security
 * @author     Kohana Team
 * @copyright  (c) 2007-2016 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Encrypt {

	/**
	 * @var  string  default instance name
	 */
	public static $default = 'default';

	/**
	 * @var  array  Encrypt class instances
	 */
	public static $instances = array();

	/**
	 * Returns a singleton instance of Kohana_Crypto
	 *
	 *     $encrypt = Encrypt::instance();
	 *
	 * @param   string  $name   configuration group name
	 * @return  Kohana_Crypto
	 */
	public static function instance($name = NULL)
	{
		$name = $name ?: Encrypt::$default;

		if ( ! isset(Encrypt::$instances[$name]))
		{
			Encrypt::$instances[$name] = static::factory($name);
		}

		return Encrypt::$instances[$name];
	}

	/**
	 * Factory method to return instances of Kohana_Crypto
	 * Class names should be prefixed by "Encrypt_"
	 *
	 * @param   string  $name   configuration group name
	 * @return  Kohana_Crypto
	 */
	public static function factory($name = NULL)
	{
		$name = $name ?: Encrypt::$default;

		// Load the configuration data
		$config = Kohana::$config->load('encrypt')->$name;

		// read `driver` and `settings` from config
		$driver = $config['driver'];
		$settings = $config['settings'];

		// Add the "Encrypt" prefix
		$class = 'Encrypt_'.ucfirst($driver);

		// Create a new instance
		return new $class($settings);
	}
}

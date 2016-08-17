<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests the encrypt class
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.encrypt
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     Samuel Demirdjian <sam@enov.ws>
 * @copyright  (c) 2014 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_EncryptTest extends Unittest_TestCase
{

	/**
	 * Provider for test_instance_returns_singleton
	 *
	 * @return array of $instance_name, $missing_config
	 */
	public function provider_instance_returns_singleton()
	{
		return array(
			array(
				'default',
				'legacy',
				array(
					'key' => 'trwQwVXX96TIJoKxyBHB9AJkwAOHixuV1ENZmIWyanI0j1zNgSVvqywy044Agaj',
				)
			),
			array(
				'blowfish',
				'legacy',
				array(
					'key' => '7bZJJkmNrelj5NaKoY6h6rMSRSmeUlJuTeOd5HHka5XknyMX4uGSfeVolTz4IYy',
					'cipher' => MCRYPT_BLOWFISH,
					'mode' => MCRYPT_MODE_ECB,
				)
			),
			array(
				'tripledes',
				'legacy',
				array(
					'key' => 'a9hcSLRvA3LkFc7EJgxXIKQuz1ec91J7P6WNq1IaxMZp4CTj5m31gZLARLxI1jD',
					'cipher' => MCRYPT_3DES,
					'mode' => MCRYPT_MODE_CBC,
				)
			),
		);
	}

	/**
	 * Test to multiple calls to the instance() method returns same instance
	 * also test if the instances are appropriately configured.
	 *
	 * @param string $instance_name instance name
	 * @param string $driver_name   driver name
	 * @param array  $config_array  array of config variables missing from config
	 *
	 * @dataProvider provider_instance_returns_singleton
	 */
	public function notest_instance_returns_singleton($instance_name, array $config_array)
	{
		// load config
		$config = Kohana::$config->load('encrypt');
		// if instance name is NULL the config group should be the default
		$config_group = $instance_name ? : Encrypt::$default;
		// if config group does not exists, create
		if (!array_key_exists($config_group, $config))
		{
			$config[$config_group] = array();
		}
		// fill in the missing config variables
		$config[$config_group] = $config[$config_group] + $config_array;

		// call instance twice
		$e = Encrypt::instance($instance_name);
		$e2 = Encrypt::instance($instance_name);

		// assert instances
		$this->assertInstanceOf('Encrypt', $e);
		$this->assertInstanceOf('Encrypt', $e2);
		$this->assertSame($e, $e2);

		// test if instances are well configured
		// prepare expected variables
		$expected_cipher = $config[$config_group]['cipher'];
		$expected_mode = $config[$config_group]['mode'];
		$expected_key_size = mcrypt_get_key_size($expected_cipher, $expected_mode);
		$expected_key = substr($config[$config_group]['key'], 0, $expected_key_size);

		// assert
		$this->assertSameProtectedProperty($expected_key, $e, '_key');
		$this->assertSameProtectedProperty($expected_cipher, $e, '_cipher');
		$this->assertSameProtectedProperty($expected_mode, $e, '_mode');
	}

	/**
	 * Helper method to test for private/protected properties
	 *
	 * @param mixed $expect Expected value
	 * @param mixed $object object that holds the private/protected property
	 * @param string $name the name of the private/protected property
	 */
	protected function assertSameProtectedProperty($expect, $object, $name)
	{
		$refl = new ReflectionClass($object);
		$property = $refl->getProperty($name);
		$property->setAccessible(TRUE);
		$this->assertSame($expect, $property->getValue($object));
	}

}

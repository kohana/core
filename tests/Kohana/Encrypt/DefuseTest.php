<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

use \Defuse\Crypto\Key;

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
 * @copyright  (c) 2016 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Encrypt_DefuseTest extends Unittest_TestCase
{

	/**
	 * Provider for test_valid_key
	 *
	 * @return array of $key, $exception
	 */
	public function provider_valid_key()
	{
		return array(
			array(
				// key
				Key::createNewRandomKey()->saveToAsciiSafeString(),
				// exception
				NULL
			),
			array(
				// No key
				NULL,
				// exception
				"Kohana_Exception",
			),
			array(
				// invalid key for the Defuse driver
				"De finibus bonorum et malorum",
				// exception
				"Defuse\Crypto\Exception\BadFormatException",
			),
		);
	}

	/**
	 * Tests creation of Encrypt_Defuse
	 * with valid key
	 *
	 * @dataProvider provider_valid_key
	 * @covers Encrypt_Defuse::__construct
	 */
	public function test_valid_key($key, $exception)
	{
		if ( ! empty($exception))
		{
			$this->setExpectedException($exception);
		}
		$this->assertInstanceOf('Encrypt_Defuse', new Encrypt_Defuse(['key' => $key]));
	}

	/**
	 * Provider for test_encode_decode, test_consecutive_encode_different_results
	 *
	 * @return array of $key, $mode, $cipher, $txt_plain
	 */
	public function provider_encode_decode()
	{
		return array(
			array(
				// key
				Key::createNewRandomKey()->saveToAsciiSafeString(),
				// txt_plain
				"The quick brown fox jumps over the lazy dog",
			),
			array(
				// invalid key for the Defuse driver
				Key::createNewRandomKey()->saveToAsciiSafeString(),
				// txt_plain
				"Lorem ipsum dolor sit amet, consectetur adipisicing elit",
			),
		);
	}

	/**
	 * @param type $key Encryption Key
	 * @param type $txt_plain Plain text to encode and then decode back
	 *
	 * @dataProvider provider_encode_decode
	 * @covers Encrypt_Defuse::encode
	 * @covers Encrypt_Defuse::decode
	 */
	public function test_encode_decode($key, $txt_plain)
	{
		// initialize, encode
		$settings = [
			'key' => $key,
		];
		$e = new Encrypt_Defuse($settings);
		$txt_encoded = $e->encode($txt_plain);

		// prepare data
		$expected = $txt_plain;
		$actual = $e->decode($txt_encoded);

		// assert
		$this->assertSame($expected, $actual);
	}

	/**
	 * Test if proper padding have been implemented
	 * @see Github issue https://github.com/kohana/kohana/issues/99
	 */
	public function test_proper_padding()
	{
		$settings = [
			'key' => Key::createNewRandomKey()->saveToAsciiSafeString()
		];
		$e = new Encrypt_Defuse($settings);
		$crypto = $e->encode(gzencode(serialize(array())));
		$obj = unserialize(gzdecode($e->decode($crypto)));
		$this->assertEquals(array(), $obj);
	}

	/**
	 * @param type $key Encryption Key
	 * @param type $mode Encryption Mode
	 * @param type $cipher Encryption Cipher
	 * @param type $txt_plain Plain text to encode and then decode back
	 *
	 * @dataProvider provider_encode_decode
	 * @covers Encrypt::encode
	 */
	public function test_consecutive_encode_produce_different_results($key, $txt_plain)
	{
		// initialize, encode twice
		$settings = [
			'key' => $key,
		];
		$e = new Encrypt_Defuse($settings);
		$txt_encoded_first = $e->encode($txt_plain);
		$txt_encoded_second = $e->encode($txt_plain);

		// assert
		$this->assertNotEquals($txt_encoded_first, $txt_encoded_second);
	}


}

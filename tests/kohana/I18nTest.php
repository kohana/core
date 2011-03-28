<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana i18n class
 *
 * @group kohana
 * @group kohana.i18n
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_I18nTest extends Unittest_TestCase
{

	/**
	 * Provides test data for test_lang()
	 * 
	 * @return array
	 */
	public function provider_lang()
	{
		return array(
			// $value, $result
			array(NULL, 'en-us'),
			array('es-es', 'es-es'),
			array(NULL, 'es-es'),
		);
	}

	/**
	 * Tests i18n::lang()
	 *
	 * @test
	 * @dataProvider provider_lang
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	public function test_open($input, $expected)
	{
		$this->assertSame($expected, I18n::lang($input));
	}

	/**
	 * Provides test data for test_get()
	 * 
	 * @return array
	 */
	public function provider_get()
	{
		return array(
			// $value, $result
			array('en-us', 'Hello, world!', 'Hello, world!'),
			array('es-es', 'Hello, world!', '¡Hola, mundo!'),
			array('fr-fr', 'Hello, world!', 'Bonjour, monde!'),
		);
	}

	/**
	 * Tests i18n::get()
	 *
	 * @test
	 * @dataProvider provider_get
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	public function test_get($lang, $input, $expected)
	{
		I18n::lang($lang);
		$this->assertSame($expected, I18n::get($input));

		// Test immediate translation, issue #3085
		I18n::lang('en-us');
		$this->assertSame($expected, I18n::get($input, $lang));
	}

}

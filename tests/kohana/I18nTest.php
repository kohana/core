<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana i18n class
 *
 * @group kohana
 * @group kohana.i18n
 *
 * @package    Unittest
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_I18nTest extends Kohana_Unittest_TestCase
{
	/**
	 * Provides test data for testLang()
	 * 
	 * @return array
	 */
	function providerLang()
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
	 * @dataProvider providerLang
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	function testOpen($input, $expected)
	{
		$this->assertSame($expected, I18n::lang($input));
	}

	/**
	 * Provides test data for testGet()
	 * 
	 * @return array
	 */
	function providerGet()
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
	 * @dataProvider providerGet
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	function testGet($lang, $input, $expected)
	{
		I18n::lang($lang);
		$this->assertSame($expected, I18n::get($input));
	}
}

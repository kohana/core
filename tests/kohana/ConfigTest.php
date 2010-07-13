<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests the Config lib that's shipped with kohana
 *
 * @group kohana
 *
 * @package    Unittest
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
Class Kohana_ConfigTest extends Kohana_Unittest_TestCase
{
	/**
	 * Tests Arr::callback()
	 *
	 * @test
	 * @param string $str       String to parse
	 * @param array  $expected  Callback and its parameters
	 */
	function testReaders()
	{
		$config = Kohana_Config::instance();
		$config->attach(new Kohana_Config_File, FALSE);
		$config->detach(new Kohana_Config_File);

		$encrypt = $config->load('encrypt');
		$this->assertEquals(TRUE, $encrypt instanceof Kohana_Config_File);

		$foo = $config->load('foo');
		$this->assertEquals(0, count($foo));

		$config->detach(new Kohana_Config_File);

		try
		{
			$foo = $config->load('foo');
		}
		catch (Exception $e)
		{
			$this->assertEquals('No configuration readers attached', $e->getMessage());
		}

		$config->attach(new Kohana_Config_File);
	}

	function testCopy()
	{
		$config = Kohana_Config::instance();

		$config->copy('encrypt');
	}
}

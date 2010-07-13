<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana upload class
 *
 * @group kohana
 * @group kohana.upload
 *
 * @package    Unittest
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_UploadTest extends Kohana_Unittest_TestCase
{
	/**
	 * Provides test data for test_size()
	 * 
	 * @return array
	 */
	function provider_size()
	{
		return array(
			// $field, $bytes, $environment, $expected
			array('unit_test', 5, array('_FILES' => array('unit_test' => array('error' => UPLOAD_ERR_INI_SIZE))), FALSE),
			array('unit_test', 5, array('_FILES' => array('unit_test' => array('error' => UPLOAD_ERR_NO_FILE))), TRUE),
			array('unit_test', '6K', array('_FILES' => array(
				'unit_test' => array(
					'error' => UPLOAD_ERR_OK,
					'name' => 'Unit_Test File',
					'type' => 'image/png',
					'tmp_name' => Kohana::find_file('tests', 'test_data/github', 'png'),
					'size' => filesize(Kohana::find_file('tests', 'test_data/github', 'png')),
				)
			)), TRUE),
			array('unit_test', '1B', array('_FILES' => array(
				'unit_test' => array(
					'error' => UPLOAD_ERR_OK,
					'name' => 'Unit_Test File',
					'type' => 'image/png',
					'tmp_name' => Kohana::find_file('tests', 'test_data/github', 'png'),
					'size' => filesize(Kohana::find_file('tests', 'test_data/github', 'png')),
				)
			)), FALSE),
		);
	}

	/**
	 * Tests Upload::size
	 *
	 * @test
	 * @dataProvider provider_size
	 * @covers upload::size
	 * @param string $field the files field to test
	 * @param string $bytes valid bite size
	 * @param array $environment set the $_FILES array
	 * @param $expected what to expect
	 */
	function test_size($field, $bytes, $environment, $expected)
	{
		$this->setEnvironment($environment);

		$this->assertSame($expected, Upload::size($_FILES[$field], $bytes));
	}
}

<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana remote class
 *
 * @group kohana
 * @group kohana.remote
 *
 * @package    Unittest
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_RemoteTest extends Kohana_Unittest_TestCase
{
	/**
	 * Provides test data for test_get()
	 * 
	 * @return array
	 */
	public function provider_get()
	{
		return array(
			// $value, $result
			array('', TRUE),
			array('cat', FALSE),
		);
	}

	/**
	 * Tests Remote::get
	 *
	 * @test
	 * @dataProvider provider_get
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	public function test_get($input, $expected)
	{
		if ( ! $this->hasInternet())
			$this->markTestSkipped('An internet connection is required for this test');

		#$this->assertSame($expected, Remote::get($input));
	}

	/**
	 * Provides test data for test_status()
	 * 
	 * @return array
	 */
	public function provider_status()
	{
		return array(
			// $value, $result
			array('http://kohanaframework.org/', 200),
			array('http://kohanaframework.org', 200),
			array('http://kohanaframework.org/foobar', 500),
		);
	}

	/**
	 * Tests Remote::status
	 *
	 * @test
	 * @dataProvider provider_status
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	public function test_status($input, $expected)
	{
		if ( ! $this->hasInternet())
			$this->markTestSkipped('An internet connection is required for this test');
		
		$this->assertSame($expected, Remote::status($input));
	}
}

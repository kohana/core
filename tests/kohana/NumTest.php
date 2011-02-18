<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Num
 *
 * @group      kohana
 * @package    Unittest
 * @author     Kohana Team
 * @author     BRMatt <matthew@sigswitch.com>
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_NumTest extends Kohana_Unittest_TestCase
{
	protected $default_locale;

	/**
	 * SetUp test enviroment
	 */
	public function setUp()
	{
		parent::setUp();

		setlocale(LC_ALL, 'English');
	}

	/**
	 * Tear down environment
	 */
	public function tearDown()
	{
		parent::tearDown();

		setlocale(LC_ALL, $this->default_locale);
	}
	
	/**
	 * Provides test data for test_ordinal()
	 * @return array
	 */
	public function provider_ordinal()
	{
		return array(
			array(0, 'th'),
			array(1, 'st'),
			array(21, 'st'),
			array(112, 'th'),
			array(23, 'rd'),
			array(42, 'nd'),
		);
	}

	/**
	 *
	 * @test
	 * @dataProvider provider_ordinal
	 * @param integer $number
	 * @param <type> $expected
	 */
	public function test_ordinal($number, $expected)
	{
		$this->assertSame($expected, Num::ordinal($number));
	}

	/**
	 * Provides test data for test_format()
	 * @return array
	 */
	public function provider_format()
	{
		return array(
			// English
			array(10000, 2, FALSE, '10,000.00'),
			array(10000, 2, TRUE, '10,000.00'),

			// Additional dp's should be removed
			array(123.456, 2, FALSE, '123.46'),
			array(123.456, 2, TRUE, '123.46'),
		);
	}

	/**
	 * @todo test locales
	 * @test
	 * @dataProvider provider_format
	 * @param integer $number
	 * @param integer $places
	 * @param boolean $monetary
	 * @param string $expected
	 */
	public function test_format($number, $places, $monetary, $expected)
	{
		$this->assertSame($expected, Num::format($number, $places, $monetary));
	}
}

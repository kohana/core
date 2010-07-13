<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana inflector class
 *
 * @group kohana
 * @group kohana.inflector
 *
 * @package    Unittest
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_InflectorTest extends Kohana_Unittest_TestCase
{
	/**
	 * Provides test data for testLang()
	 * 
	 * @return array
	 */
	function providerUncountable()
	{
		return array(
			// $value, $result
			array('fish', TRUE),
			array('cat', FALSE),
		);
	}

	/**
	 * Tests Inflector::uncountable
	 *
	 * @test
	 * @dataProvider providerUncountable
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	function testUncountable($input, $expected)
	{
		$this->assertSame($expected, Inflector::uncountable($input));
	}

	/**
	 * Provides test data for testLang()
	 * 
	 * @return array
	 */
	function providerSingular()
	{
		return array(
			// $value, $result
			array('fish', NULL, 'fish'),
			array('cats', NULL, 'cat'),
			array('cats', 2, 'cats'),
			array('cats', '2', 'cats'),
			array('children', NULL, 'child'),
		);
	}

	/**
	 * Tests Inflector::singular
	 *
	 * @test
	 * @dataProvider providerSingular
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	function testSingular($input, $count, $expected)
	{
		$this->assertSame($expected, Inflector::singular($input, $count));
	}

	/**
	 * Provides test data for testLang()
	 * 
	 * @return array
	 */
	function providerPlural()
	{
		return array(
			// $value, $result
			array('fish', NULL, 'fish'),
			array('cat', NULL, 'cats'),
			array('cats', 1, 'cats'),
			array('cats', '1', 'cats'),
			array('movie', NULL, 'movies'),
		);
	}

	/**
	 * Tests Inflector::plural
	 *
	 * @test
	 * @dataProvider providerPlural
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	function testPlural($input, $count, $expected)
	{
		$this->assertSame($expected, Inflector::plural($input, $count));
	}

	/**
	 * Provides test data for testCamelize()
	 * 
	 * @return array
	 */
	function providerCamelize()
	{
		return array(
			// $value, $result
			array('mother cat', 'camelize', 'motherCat'),
			array('kittens in bed', 'camelize', 'kittensInBed'),
			array('mother cat', 'underscore', 'mother_cat'),
			array('kittens in bed', 'underscore', 'kittens_in_bed'),
			array('kittens-are-cats', 'humanize', 'kittens are cats'),
			array('dogs_as_well', 'humanize', 'dogs as well'),
		);
	}

	/**
	 * Tests Inflector::camelize
	 *
	 * @test
	 * @dataProvider providerCamelize
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	function testCamelize($input, $method, $expected)
	{
		$this->assertSame($expected, Inflector::$method($input));
	}
}

<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * This test only really exists for code coverage. No tests really apply to base model.
 * We can't even test Model because database doesn't exist!
 *
 * @group kohana
 *
 * @package    Unittest
 * @author     Kohana Team
 * @author     BRMatt <matthew@sigswitch.com>
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_ModelTest extends Kohana_Unittest_TestCase
{
	/**
	 * @test
	 */
	function test_construct()
	{
		#$model = new Model_Foobar('foo');
		#$model = Model::factory('Foobar', 'foo');
	}
}

class Model_Foobar extends Model
{
	
}

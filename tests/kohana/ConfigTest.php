<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests the Config lib that's shipped with kohana
 *
 * @group kohana
 *
 * @package    Unittest
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @author     Matt Button <matthew@sigswitch.com>
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
Class Kohana_ConfigTest extends Kohana_Unittest_TestCase
{

	/**
	 * Calling Kohana_Config::instance() should return the global singleton
	 * which should persist
	 *
	 * @test
	 * @covers Kohana_Config::instance
	 */
	public function test_instance_returns_singleton_instance()
	{
		$this->assertSame(Kohana_Config::instance(), Kohana_Config::instance());
		$this->assertNotSame(new Kohana_Config, Kohana_Config::instance());
	}

	/**
	 * When a config object is initially created there should be
	 * no readers attached
	 *
	 * @test
	 * @covers Kohana_Config
	 */
	public function test_initially_there_are_no_readers()
	{
		$config = new Kohana_Config;

		$this->assertAttributeSame(array(), '_readers', $config);
	}

	/**
	 * Test that calling attach() on a kohana config object
	 * adds the specified reader to the config object
	 *
	 * @test
	 * @covers Kohana_Config::attach
	 */
	public function test_attach_adds_reader_and_returns_this()
	{
		$config = new Kohana_Config;
		$reader = $this->getMock('Kohana_Config_Reader');

		$this->assertSame($config, $config->attach($reader));

		$this->assertAttributeContains($reader, '_readers', $config);
	}

	/**
	 * By default (or by passing TRUE as the second parameter) the config object
	 * should prepend the reader to the front of the readers queue
	 *
	 * @test
	 * @covers Kohana_Config::attach
	 */
	public function test_attach_adds_reader_to_front_of_queue()
	{
		$config  = new Kohana_Config;

		$reader1 = $this->getMock('Kohana_Config_Reader');
		$reader2 = $this->getMock('Kohana_Config_Reader');

		$config->attach($reader1);
		$config->attach($reader2);

		// Rather than do two assertContains we'll do an assertSame to assert
		// the order of the readers
		$this->assertAttributeSame(array($reader2, $reader1), '_readers', $config);

		// Now we test using the second parameter
		$config = new Kohana_Config;

		$config->attach($reader1);
		$config->attach($reader2, TRUE);

		$this->assertAttributeSame(array($reader2, $reader1), '_readers', $config);
	}

	/**
	 * Test that attaching a new reader (and passing FALSE as second param) causes
	 * phpunit to append the reader rather than prepend
	 *
	 * @test
	 * @covers Kohana_Config::attach
	 */
	public function test_attach_can_add_reader_to_end_of_queue()
	{
		$config  = new Kohana_Config;
		$reader1 = $this->getMock('Kohana_Config_Reader');
		$reader2 = $this->getMock('Kohana_Config_Reader');

		$config->attach($reader1);
		$config->attach($reader2, FALSE);

		$this->assertAttributeSame(array($reader1, $reader2), '_readers', $config);
	}

	/**
	 * Calling detach() on a config object should remove it from the queue of readers
	 *
	 * @test
	 * @covers Kohana_Config::detach
	 */
	public function test_detach_removes_reader_and_returns_this()
	{
		$config  = new Kohana_Config;

		// Due to the way phpunit mock generator works if you try and mock a class
		// that has already been used then it just re-uses the first's name
		//
		// To get around this we have to specify a totally random name for the second mock object
		$reader1 = $this->getMock('Kohana_Config_Reader');
		$reader2 = $this->getMock('Kohana_Config_Reader', array(), array(), 'MY_AWESOME_READER');
		
		$config->attach($reader1);
		$config->attach($reader2);

		$this->assertSame($config, $config->detach($reader1));

		$this->assertAttributeNotContains($reader1, '_readers', $config);
		$this->assertAttributeContains($reader2, '_readers', $config);

		$this->assertSame($config, $config->detach($reader2));

		$this->assertAttributeNotContains($reader2, '_readers', $config);
	}

	/**
	 * detach() should return $this even if the specified reader does not exist
	 *
	 * @test
	 * @covers Kohana_Config::detach
	 */
	public function test_detach_returns_this_even_when_reader_dnx()
	{
		$config = new Kohana_Config;
		$reader = $this->getMock('Kohana_Config_Reader');

		$this->assertSame($config, $config->detach($reader));
	}

	/**
	 * If load() is called and there are no readers present then it should throw
	 * a kohana exception
	 * 
	 * @test
	 * @covers Kohana_Config::load
	 * @expectedException Kohana_Exception
	 */
	public function test_load_throws_exception_if_there_are_no_readers()
	{
		// The following code should throw an exception and phpunit will catch / handle it
		// (see the @expectedException doccomment)
		$config = new Kohana_config;

		$config->load('random');
	}

	/**
	 * When load() is called it should interrogate each reader in turn until a match
	 * is found
	 *
	 * @test
	 * @covers Kohana_Config::load
	 */
	public function test_load_interrogates_each_reader_until_group_found()
	{
		$config       = new Kohana_Config;
		$config_group = 'groupy';

		$reader1 = $this->getMock('Kohana_Config_Reader', array('load'));
		$reader1
			->expects($this->once())
			->method('load')
			->with($config_group)
			->will($this->returnValue(FALSE));
		
		$reader2 = $this->getMock('Kohana_Config_Reader', array('load'));
		$reader2
			->expects($this->once())
			->method('load')
			->with($config_group)
			->will($this->returnValue($reader2));

		$reader3 = $this->getMock('Kohana_Config_Reader', array('load'));
		$reader3->expects($this->never())->method('load');

		$config->attach($reader1, FALSE);
		$config->attach($reader2, FALSE);
		$config->attach($reader3, FALSE);

		// By asserting a return type we're making the test a little less brittle / less likely
		// to break due to minor modifications
		$this->assertInstanceOf('Kohana_Config_Reader', $config->load($config_group));
	}

	/**
	 * Calling load() with a group that doesn't exist, should get it to use the last reader
	 * to create a new config group
	 *
	 * @test
	 * @covers Kohana_Config::load
	 */
	public function test_load_returns_new_config_group_if_one_dnx()
	{
		$config  = new Kohana_Config;
		$group   = 'my_group';

		$reader1 = $this->getMock('Kohana_Config_Reader');
		$reader2 = $this->getMock('Kohana_Config_Reader', array('load'), array(), 'Kohana_Config_Waffles');

		// This is a slightly hacky way of doing it, but it works
		$reader2
			->expects($this->exactly(2))
			->method('load')
			->with($group)
			->will($this->onConsecutiveCalls(
				$this->returnValue(FALSE), 
				$this->returnValue(clone $reader2)
			));

		$config->attach($reader1)->attach($reader2);

		$new_config = $config->load('my_group');

		$this->assertInstanceOf('Kohana_Config_Waffles', $new_config);

		// Slightly taboo, testing a different api!!
		$this->assertSame(array(), $new_config->as_array());
	}
}

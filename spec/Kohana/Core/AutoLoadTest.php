<?php

namespace Kohana\Core;

class AutoLoadTest extends \PHPUnit_Framework_TestCase
{
	public function test_it_autoloads_a_file()
	{
		$filesystem = \Mockery::mock('\Kohana\Core\Filesystem');
		$filesystem->shouldReceive('find_file')->with('classes', 'Foobar/Class')->andReturn(__FILE__);

		$autoloader = new AutoLoad($filesystem);
		$this->assertTrue($autoloader->load('Foobar_Class'));
	}

	public function test_it_fails_to_autoload_a_file()
	{
		$filesystem = \Mockery::mock('\Kohana\Core\Filesystem');
		$filesystem->shouldReceive('find_file')->with('classes', 'Foobar/Class')->andReturn(FALSE);

		$autoloader = new AutoLoad($filesystem);
		$this->assertFalse($autoloader->load('Foobar_Class'));
	}
}

<?php

namespace Kohana\Core;

class MessageTest extends \PHPUnit_Framework_TestCase
{
	public function teardown()
	{
		\Mockery::close();
	}

	public function test_it_returns_a_single_message()
	{
		$filesystem = \Mockery::mock('alias:Kohana\Core\Filesystem');
		$filesystem->shouldReceive('find_all_files')->with('messages', 'testing')->andReturn(array('file'));
		$filesystem->shouldReceive('load')->with('file')->andReturn(array('path' => 'the message'));
		$m = new Message($filesystem);

		$this->assertSame(
			'the message',
			$m->message('testing', 'path')
		);
	}

	public function test_it_returns_multiple_messages()
	{
		$filesystem = \Mockery::mock('alias:Kohana\Core\Filesystem');
		$filesystem->shouldReceive('find_all_files')->with('messages', 'testing')->andReturn(array('file'));
		$filesystem->shouldReceive('load')->with('file')->andReturn(array('path' => 'the message'));
		$m = new Message($filesystem);

		$this->assertSame(
			array('path' => 'the message'),
			$m->messages('testing')
		);
	}
}

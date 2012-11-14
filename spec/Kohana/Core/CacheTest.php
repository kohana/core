<?php

namespace Kohana\Core;

use \org\bovigo\vfs\vfsStreamWrapper;
use \org\bovigo\vfs\vfsStream;

class CacheTest extends \PHPUnit_Framework_Testcase
{
	protected function setUp()
	{
		vfsStreamWrapper::register();
		$this->fs_root = vfsStream::create(
			array(
				'APPPATH' => array(),
			),
			vfsStream::newDirectory('/')
		);
		vfsStreamWrapper::setRoot($this->fs_root);

		$this->_cache_dir = vfsStream::url('/APPPATH/');
		$this->_cache = new Cache($this->_cache_dir);
	}

	public function test_it_caches_the_filename_as_sha()
	{
		$this->_cache->save('testing', 'the data');
		$this->assertTrue($this->fs_root->hasChild('/APPPATH/dc/dc724af18fbdd4e59189f5fe768a5f8311527050.txt'));
	}

	public function test_it_serializes_the_data()
	{
		$this->_cache->save('testing', 'the data');
		$this->assertSame(
			's:8:"the data";',
			file_get_contents(vfsStream::url('/APPPATH/dc/dc724af18fbdd4e59189f5fe768a5f8311527050.txt'))
		);
	}

	public function test_it_reads_a_cache_file()
	{
		$this->_cache->save('testing', 'the data');

		$this->assertSame(
			'the data',
			$this->_cache->read('testing')
		);
	}

	public function test_it_returns_null_when_cache_does_not_exist()
	{
		$this->assertSame(
			NULL,
			$this->_cache->read('foobar')
		);
	}

	public function test_it_returns_null_when_cache_is_expired()
	{
		$this->_cache->save('testing', 'the data');

		$this->assertSame(
			NULL,
			$this->_cache->read('testing', -1) // Always expired
		);
	}

	public function test_it_deletes_cache_file_when_cache_is_expired()
	{
		$this->_cache->save('testing', 'the data');
		$this->_cache->read('testing', -1);
		$this->assertFalse(file_exists(vfsStream::url('/APPPATH/dc/dc724af18fbdd4e59189f5fe768a5f8311527050.txt')));
	}
}

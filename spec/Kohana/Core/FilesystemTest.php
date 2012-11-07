<?php

namespace Kohana\Core;

use \org\bovigo\vfs\vfsStreamWrapper;
use \org\bovigo\vfs\vfsStream;

class FilesystemTest extends \PHPUnit_Framework_TestCase
{
	protected function setUp()
	{
		vfsStreamWrapper::register();
		$this->fs_root = vfsStream::create(
			array(
				'APPPATH' => array(
					'classes' => array(
						'.' => array(),
						'Foobar.php' => 'the content',
						'subfolder' => array(
							'Foobar.php' => 'the content',
						),
					),
					'vendor' => array(
						'foobar.png' => 'content',
					),
				),
				'module1' => array(),
				'module2' => array(),
				'SYSPATH' => array(
					'classes' => array(
						'Foobar.php' => 'other content',
						'Foo.php' => 'other content',
					)
				),
			),
			vfsStream::newDirectory('/')
		);
		vfsStreamWrapper::setRoot($this->fs_root);

		$this->filesystem = new Filesystem(
			array(
				vfsStream::url('APPPATH/'),
				vfsStream::url('module1/'),
				vfsStream::url('module2/'),
				vfsStream::url('SYSPATH/'),
			)
		);
	}

	/*
	 * @test
	 */
	public function test_include_paths_return_properly()
	{
		$filesystem = new Filesystem(array('/APPPATH', '/module1', '/module2', '/SYSPATH'));
		$this->assertEquals(
			array(
				'/APPPATH',
				'/module1',
				'/module2',
				'/SYSPATH',
			),
			$filesystem->include_paths()
		);
	}

	public function test_it_finds_files()
	{
		$this->assertEquals('vfs://APPPATH/classes/Foobar.php', $this->filesystem->find_file('classes', 'Foobar'));
	}

	public function test_it_returns_false_when_not_found()
	{
		$this->assertEquals(FALSE, (new Filesystem(array()))->find_file('classes', 'Foobar'));
	}

	public function test_it_finds_files_with_other_extensions()
	{
		$this->assertEquals('vfs://APPPATH/vendor/foobar.png', $this->filesystem->find_file('vendor', 'foobar', 'png'));
	}

	public function test_it_finds_all_files()
	{
		$this->assertEquals(
			array(
				'vfs://APPPATH/classes/Foobar.php',
				'vfs://SYSPATH/classes/Foobar.php',
			),
			$this->filesystem->find_all_files('classes', 'Foobar')
		);
	}

	public function test_it_returns_an_array_when_no_files_are_found()
	{
		$this->assertEquals(
			array(),
			$this->filesystem->find_all_files('classes', 'Test')
		);
	}

	/**
	 * realpath() doesn't work with vfsStream, so we have false as the keys. How to fix?
	 */
	public function test_it_lists_all_files()
	{
		$this->assertEquals(
			array(
				'classes/Foobar.php' => 'vfs://APPPATH/classes/Foobar.php',
				'classes/Foo.php' => 'vfs://SYSPATH/classes/Foo.php',
				'classes/subfolder' => array(
					'classes/subfolder/Foobar.php' => 'vfs://APPPATH/classes/subfolder/Foobar.php',
				)
			),
			$this->filesystem->list_files('classes')
		);
	}
}

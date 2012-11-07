<?php

namespace Kohana;

class FilesystemTest extends \PHPUnit_Framework_TestCase
{
	protected function setUp()
	{
		\org\bovigo\vfs\vfsStreamWrapper::register();
		$this->fs_root = \org\bovigo\vfs\vfsStream::create(
			array(
				'APPPATH' => array(
					'classes' => array(
						'Foobar.php' => 'the content',
					)
				),
				'module1' => array(),
				'module2' => array(),
				'SYSPATH' => array(
					'classes' => array(
						'Foobar.php' => 'other content',
					)
				),
			),
			\org\bovigo\vfs\vfsStream::newDirectory('/')
		);
		\org\bovigo\vfs\vfsStreamWrapper::setRoot($this->fs_root);
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
		$filesystem = new Filesystem(
			array(
				\org\bovigo\vfs\vfsStream::url('APPPATH/'),
				\org\bovigo\vfs\vfsStream::url('module1/'),
				\org\bovigo\vfs\vfsStream::url('module2/'),
				\org\bovigo\vfs\vfsStream::url('SYSPATH/'),
			)
		);

		$this->assertEquals('vfs://APPPATH/classes/Foobar.php', $filesystem->find_file('classes', 'Foobar'));
	}
}

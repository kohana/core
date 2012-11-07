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
					)
				),
			),
			\org\bovigo\vfs\vfsStream::newDirectory('/')
		);
		\org\bovigo\vfs\vfsStreamWrapper::setRoot($this->fs_root);

		$this->filesystem = new Filesystem(
			array(
				\org\bovigo\vfs\vfsStream::url('APPPATH/'),
				\org\bovigo\vfs\vfsStream::url('module1/'),
				\org\bovigo\vfs\vfsStream::url('module2/'),
				\org\bovigo\vfs\vfsStream::url('SYSPATH/'),
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
}

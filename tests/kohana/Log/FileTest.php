<?php
use org\bovigo\vfs\vfsStream;

/**
 * Tests Kohana Logging API - writing to file
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.logging
 * @group kohana.core.logging.file
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Log_FileTest extends Kohana_Log_AbstractWriterTest {

	private $vfs_root;

	private $writer;

	/**
	 * Sets up the test enviroment
	 */
	// @codingStandardsIgnoreStart
	function setUp()
	// @codingStandardsIgnoreEnd
	{
		$this->vfs_root = vfsStream::setup('root');
		parent::setUp();
	}

	/**
	 * Provider for test_file
	 *
	 */
	public function provider_file()
	{
		// a sample exception used in data sets
		$exception = new Exception('dummy exception text');

		return [
			// data set #0
			[
				// raw log messages
				[
					[
						'time' => strtotime('2015-11-19 10:05:48'),
						'level' => \Psr\Log\LogLevel::DEBUG,
						'body' => 'dummy text',
						'line' => 10,
						'file' => '/path/to/file.php',
					]
				],
				// expected file content
<<<'LOG'
<?php exit; ?>

2015-11-19 10:05:48 --- DEBUG: dummy text in /path/to/file.php:10

LOG

			],
			// data set #1
			[
				// raw log messages
				[
					[
						'time' => strtotime('2015-11-19 10:05:48'),
						'level' => \Psr\Log\LogLevel::ERROR,
						'body' => 'another dummy text',
						'file' => '/path/to/another/file.php',
						'line' => 20,
						'exception' => $exception,
					]
				],
				// expected file content
<<<'LOG'
<?php exit; ?>

2015-11-19 10:05:48 --- ERROR: another dummy text in /path/to/another/file.php:20
2015-11-19 10:05:48 --- DEBUG: 
LOG
				. $exception->getTraceAsString()
				. ' in /path/to/another/file.php:20'
				. PHP_EOL
			]
		];
	}

	/**
	 * Tests logging with the file writer
	 *
	 * @test
	 * @dataProvider provider_file
	 */
	public function test_file(array $log_entries, $expected)
	{
		$writer = new Kohana_Log_FileTest_Testable_Log_File($this->vfs_root->url());

		$writer->write($log_entries);

		// assertions
		$this->assertSame($expected, $writer->get_written_logs());
	}

	/**
	 * Provider for test_writer_does_not_create_existing_file_but_only_appends
	 *
	 */
	public function provider_writer_does_not_create_existing_file_but_only_appends()
	{
		return [
			// data set #0
			[
				// raw log messages
				[
					[
						'time' => strtotime('2015-11-19 10:05:48'),
						'level' => \Psr\Log\LogLevel::DEBUG,
						'body' => 'dummy text',
						'line' => 10,
						'file' => '/path/to/file.php',
					]
				],
				// existing file content
<<<'EXISTING'
<?php exit; ?>

2015-11-19 09:01:30 --- INFO: dummy text that already existed in /path/to/some/file.php:10

EXISTING
				,
				// expected file contents
<<<'EXPECTED'
<?php exit; ?>

2015-11-19 09:01:30 --- INFO: dummy text that already existed in /path/to/some/file.php:10
2015-11-19 10:05:48 --- DEBUG: dummy text in /path/to/file.php:10

EXPECTED

			],
		];
	}

	/**
	 * Tests that writer does not create a new log file, when one exists, but
	 * only appends to it
	 *
	 * @test
	 * @dataProvider provider_writer_does_not_create_existing_file_but_only_appends
	 */
	public function test_writer_does_not_create_existing_file_but_only_appends(array $log_entries, $existing_file_contents, $expected_file_contents)
	{
		$writer = new Kohana_Log_FileTest_Testable_Log_File($this->vfs_root->url());

		// prepare environment: create log file with some contents
		mkdir($writer->get_directory(), 0777, TRUE);
		file_put_contents($writer->get_directory() . $writer->get_filename(), $existing_file_contents);

		// write some logs, writer should only append to existing log file
		$writer->write($log_entries);

		// assert
		$this->assertSame($expected_file_contents, $writer->get_written_logs());
	}

	/**
	 * Provider for test_created_files_folders_permissions
	 */
	public function provider_created_files_folders_permissions()
	{
		return [
			[0777, 0666, TRUE],
			[0700, 0600, TRUE],
			[0000, 0666, FALSE],
			[0777, 0000, FALSE],
			[0000, 0000, FALSE],
		];
	}

	/**
	 * Tests that different file/folder modes affect the writers ability to
	 * write
	 *
	 * @test
	 * @dataProvider provider_created_files_folders_permissions
	 */
	public function test_created_files_folders_permissions($dir_mode, $file_mode, $should_write)
	{
		if ( ! $should_write)
		{
			$this->setExpectedException('PHPUnit_Framework_Error_Warning');
		}

		$writer = new Kohana_Log_FileTest_Testable_Log_File($this->vfs_root->url(), $dir_mode, $file_mode);
		$writer->write([$this->dummy_log]);

		$this->assertSame($dir_mode, fileperms($writer->get_directory()) & $dir_mode);
		$this->assertSame($file_mode, fileperms($writer->get_directory() . $writer->get_filename()) & $file_mode);
	}

	/**
	 * Provider for test_writer_does_not_create_or_change_mode_existing_file_folder
	 */
	public function provider_writer_does_not_create_or_change_mode_existing_file_folder()
	{
		return [
			[
				0700, // mode of existing folder
				0600, // mode of existing file
				0777, // mode for newly created folders
				0666, // mode for newly created files
			],
		];
	}

	/**
	 * Tests that writer does not create a folder when it exists and does not
	 * change its mode
	 *
	 * @test
	 * @dataProvider provider_writer_does_not_create_or_change_mode_existing_file_folder
	 */
	public function test_writer_does_not_create_or_change_mode_existing_file_folder($existing_dir_mode, $existing_file_mode, $init_dir_mode, $init_file_mode)
	{
		// setup existing directory
		$dir = $this->vfs_root->url() . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m');
		mkdir($dir, $existing_dir_mode, TRUE);
		chmod($dir, $existing_dir_mode);

		// setup existing file
		$file = $dir . DIRECTORY_SEPARATOR . date('d') . EXT;
		file_put_contents($file, '<?php exit; ?>' . PHP_EOL . PHP_EOL);
		chmod($file, $existing_file_mode);

		// initialize writer a write a dummy log
		$writer = new Kohana_Log_FileTest_Testable_Log_File($this->vfs_root->url(), $init_dir_mode, $init_file_mode);
		$writer->write([$this->dummy_log]);

		// assert the writer did not change modes of existing file and folder
		$actual_file = $writer->get_directory() . $writer->get_filename();
		$this->assertSame($file, $actual_file);
		$this->assertSame($existing_dir_mode, fileperms($writer->get_directory()) & $existing_dir_mode);
		$this->assertSame($existing_file_mode, fileperms($writer->get_directory() . $writer->get_filename()) & $existing_file_mode);
	}

	protected function get_writer()
	{
		return $this->writer = new Kohana_Log_FileTest_Testable_Log_File($this->vfs_root->url());
	}

	protected function make_dummy_written_logs(array $levels)
	{
		$file_header = '<?php exit; ?>' . PHP_EOL . PHP_EOL;
		
		if (empty($levels)) {
			return NULL;
		}

		$logs = array();
		foreach ($levels as $level)
		{
			$level = strtoupper($level);
			$logs[] = "2015-10-19 10:16:24 --- $level: dummy text in /path/to/file.php:10";
		}
		
		return $file_header . implode(PHP_EOL, $logs) . PHP_EOL;
	}

	protected function get_written_logs()
	{
		return $this->writer->get_written_logs();
	}

}

/**
 * A File writer with a method that returns written logs
 */
class Kohana_Log_FileTest_Testable_Log_File extends Log_File {

	public function get_directory()
	{
		return $this->_directory
		  . date('Y')
		  . DIRECTORY_SEPARATOR
		  . date('m')
		  . DIRECTORY_SEPARATOR;
	}
	
	public function get_filename()
	{
		return date('d') . EXT;
	}

	public function get_written_logs()
	{
		$file = $this->get_directory() . $this->get_filename();

		if ( ! is_file($file))
		{
			return	NULL;
		}

		return file_get_contents($file);
	}

}

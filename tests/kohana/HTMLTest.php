<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests HTML
 * 
 * @group kohana
 *
 * @package    Unittest
 * @author     Kohana Team
 * @author     BRMatt <matthew@sigswitch.com>
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
Class Kohana_HTMLTest extends Kohana_Unittest_TestCase
{	
	protected $environmentDefault = array(
		'Kohana::$base_url' => '/kohana/',
		'HTTP_HOST'	=> 'www.kohanaframework.org',
	);

	/**
	 * Provides test data for test_attributes()
	 * 
	 * @return array
	 */
	public function provider_attributes()
	{
		return array(
			array(
				array('name' => 'field', 'random' => 'not_quite', 'id' => 'unique_field'),
				' id="unique_field" name="field" random="not_quite"'
			),
			array(
				array('invalid' => NULL),
				''
			),
			array(
				array(),
				''
			),
			array(
				array('name' => 'field', 'checked'),
				' name="field" checked="checked"',
			),
		);
	}

	/**
	 * Tests HTML::attributes()
	 *
	 * @test
	 * @dataProvider provider_attributes
	 * @param array  $attributes  Attributes to use
	 * @param string $expected    Expected output
	 */
	public function test_attributes($attributes, $expected)
	{
		$this->assertSame(
			$expected,
			HTML::attributes($attributes)
		);
	}

	/**
	 * Provides test data for test_script
	 *
	 * @return array Array of test data
	 */
	public function provider_script()
	{
		return array(
			array(
				'<script type="text/javascript" src="http://google.com/script.js"></script>',
				'http://google.com/script.js',
			),
		);
	}

	/**
	 * Tests HTML::script()
	 *
	 * @test
	 * @dataProvider  provider_script
	 * @param string  $expected       Expected output
	 * @param string  $file           URL to script
	 * @param array   $attributes     HTML attributes for the anchor
	 * @param bool    $index          Should the index file be included in url?
	 */
	public function test_script($expected, $file, array $attributes = NULL, $index = FALSE)
	{
		$this->assertSame(
			$expected,
			HTML::script($file, $attributes, $index)
		);
	}

	/**
	 * Data provider for the style test
	 *
	 * @return array Array of test data
	 */
	public function provider_style()
	{
		return array(
			array(
				'<link type="text/css" href="http://google.com/style.css" rel="stylesheet" />',
				'http://google.com/style.css',
				array(),
				FALSE
			),
		);
	}

	/**
	 * Tests HTML::style()
	 *
	 * @test
	 * @dataProvider  provider_style
	 * @param string  $expected     The expected output
	 * @param string  $file         The file to link to
	 * @param array   $attributes   Any extra attributes for the link
	 * @param bool    $index        Whether the index file should be added to the link
	 */
	public function test_style($expected, $file, array $attributes = NULL, $index = FALSE)
	{
		$this->assertSame(
			$expected,
			HTML::style($file, $attributes, $index)
		);
	}

	/**
	 * Provides test data for test_obfuscate
	 *
	 * @return array Array of test data
	 */
	public function provider_obfuscate()
	{
		return array(
			array('something crazy'),
			array('me@google.com'),
		);
	}

	/**
	 * Tests HTML::obfuscate
	 *
	 * @test
	 * @dataProvider   provider_obfuscate
	 * @param string   $string            The string to obfuscate
	 */
	public function test_obfuscate($string)
	{
		$this->assertNotSame(
			$string,
			HTML::obfuscate($string)
		);
	}

	/**
	 * Provides test data for test_anchor
	 *
	 * @return array Test data
	 */
	public function provider_anchor()
	{
		return array(
			array(
				'<a href="http://kohanaframework.org">Kohana</a>',
				array(),
				'http://kohanaframework.org',
				'Kohana',
			),
			array(
				'<a href="http://google.com" target="_blank">GOOGLE</a>',
				array(),
				'http://google.com',
				'GOOGLE',
				array('target' => '_blank'),
			),
		);
	}

	/**
	 * Tests HTML::anchor
	 *
	 * @test
	 * @dataProvider provider_anchor
	 */
	public function test_anchor($expected, array $options, $uri, $title = NULL, array $attributes = NULL, $protocol = NULL)
	{
		//$this->setEnvironment($options);

		$this->assertSame(
			$expected,
			HTML::anchor($uri, $title, $attributes, $protocol)
		);
	}

	/**
	 * Data provider for test_file_anchor
	 *
	 * @return array
	 */
	public function provider_file_anchor()
	{
		return array(
			array(
				'<a href="/kohana/mypic.png">My picture file</a>',
				array(),
				'mypic.png',
				'My picture file',
			)
		);
	}

	/**
	 * Test for HTML::file_anchor()
	 *
	 * @test
	 * @covers HTML::file_anchor
	 * @dataProvider provider_file_anchor
	 */
	public function test_file_anchor($expected, array $options, $file, $title = NULL, array $attributes = NULL, $protocol = NULL)
	{
		$this->assertSame(
			$expected,
			HTML::file_anchor($file, $title, $attributes, $protocol)
		);
	}
}

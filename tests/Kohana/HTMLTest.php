<?php
/**
 * Tests HTML
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.html
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     BRMatt <matthew@sigswitch.com>
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_HTMLTest extends Unittest_TestCase {

	/**
	 * Defaults for this test
	 * @var array
	 */
	// @codingStandardsIgnoreStart
	protected $environmentDefault = [
		'Kohana::$base_url' => '/kohana/',
		'Kohana::$index_file' => 'index.php',
		'HTML::$strict' => TRUE,
		'HTTP_HOST'	=> 'www.kohanaframework.org',
	];
	// @codingStandardsIgnoreEnd

	/**
	 * Sets up the environment
	 */
	// @codingStandardsIgnoreStart
	public function setUp()
	// @codingStandardsIgnoreEnd
	{
		parent::setUp();
		Kohana::$config->load('url')->set('trusted_hosts', ['www\.kohanaframework\.org']);
	}

	/**
	 * Provides test data for test_attributes()
	 *
	 * @return array
	 */
	public function provider_attributes()
	{
		return [
			[
				['name' => 'field', 'random' => 'not_quite', 'id' => 'unique_field'],
				[],
				' id="unique_field" name="field" random="not_quite"'
			],
			[
				['invalid' => NULL],
				[],
				''
			],
			[
				[],
				[],
				''
			],
			[
				['name' => 'field', 'checked'],
				[],
				' name="field" checked="checked"',
			],
			[
				['id' => 'disabled_field', 'disabled'],
				['HTML::$strict' => FALSE],
				' id="disabled_field" disabled',
			],
		];
	}

	/**
	 * Tests HTML::attributes()
	 *
	 * @test
	 * @dataProvider provider_attributes
	 * @param array  $attributes  Attributes to use
	 * @param array  $options     Environment options to use
	 * @param string $expected    Expected output
	 */
	public function test_attributes(array $attributes, array $options, $expected)
	{
		$this->setEnvironment($options);

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
		return [
			[
				'<script type="text/javascript" src="http://google.com/script.js"></script>',
				'http://google.com/script.js',
			],
			[
				'<script type="text/javascript" src="http://www.kohanaframework.org/kohana/index.php/my/script.js"></script>',
				'my/script.js',
				NULL,
				'http',
				TRUE
			],
			[
				'<script type="text/javascript" src="https://www.kohanaframework.org/kohana/my/script.js"></script>',
				'my/script.js',
				NULL,
				'https',
				FALSE
			],
			[
				'<script type="text/javascript" src="https://www.kohanaframework.org/kohana/my/script.js"></script>',
				'/my/script.js', // Test absolute paths
				NULL,
				'https',
				FALSE
			],
			[
				'<script type="text/javascript" src="//google.com/script.js"></script>',
				'//google.com/script.js',
			],
		];
	}

	/**
	 * Tests HTML::script()
	 *
	 * @test
	 * @dataProvider  provider_script
	 * @param string  $expected       Expected output
	 * @param string  $file           URL to script
	 * @param array   $attributes     HTML attributes for the anchor
	 * @param string  $protocol       Protocol to use
	 * @param bool    $index          Should the index file be included in url?
	 */
	public function test_script($expected, $file, array $attributes = NULL, $protocol = NULL, $index = FALSE)
	{
		$this->assertSame(
			$expected,
			HTML::script($file, $attributes, $protocol, $index)
		);
	}

	/**
	 * Data provider for the style test
	 *
	 * @return array Array of test data
	 */
	public function provider_style()
	{
		return [
			[
				'<link type="text/css" href="http://google.com/style.css" rel="stylesheet" />',
				'http://google.com/style.css',
				[],
				NULL,
				FALSE
			],
			[
				'<link type="text/css" href="/kohana/my/style.css" rel="stylesheet" />',
				'my/style.css',
				[],
				NULL,
				FALSE
			],
			[
				'<link type="text/css" href="https://www.kohanaframework.org/kohana/my/style.css" rel="stylesheet" />',
				'my/style.css',
				[],
				'https',
				FALSE
			],
			[
				'<link type="text/css" href="https://www.kohanaframework.org/kohana/index.php/my/style.css" rel="stylesheet" />',
				'my/style.css',
				[],
				'https',
				TRUE
			],
			[
				'<link type="text/css" href="https://www.kohanaframework.org/kohana/index.php/my/style.css" rel="stylesheet" />',
				'/my/style.css',
				[],
				'https',
				TRUE
			],
			[
				// #4283: http://dev.kohanaframework.org/issues/4283
				'<link type="text/css" href="https://www.kohanaframework.org/kohana/index.php/my/style.css" rel="stylesheet/less" />',
				'my/style.css',
				['rel' => 'stylesheet/less'],
				'https',
				TRUE
			],
			[
				'<link type="text/css" href="//google.com/style.css" rel="stylesheet" />',
				'//google.com/style.css',
				[],
				NULL,
				FALSE
			],
		];
	}

	/**
	 * Tests HTML::style()
	 *
	 * @test
	 * @dataProvider  provider_style
	 * @param string  $expected     The expected output
	 * @param string  $file         The file to link to
	 * @param array   $attributes   Any extra attributes for the link
	 * @param string  $protocol     Protocol to use
	 * @param bool    $index        Whether the index file should be added to the link
	 */
	public function test_style($expected, $file, array $attributes = NULL, $protocol = NULL, $index = FALSE)
	{
		$this->assertSame(
			$expected,
			HTML::style($file, $attributes, $protocol, $index)
		);
	}

	/**
	 * Provides test data for test_anchor
	 *
	 * @return array Test data
	 */
	public function provider_anchor()
	{
		return [
			// a fragment-only anchor
			[
				'<a href="#go-to-section-kohana">Kohana</a>',
				[],
				'#go-to-section-kohana',
				'Kohana',
			],
			// a query-only anchor
			[
				'<a href="?cat=a">Category A</a>',
				[],
				'?cat=a',
				'Category A',
			],
			[
				'<a href="http://kohanaframework.org">Kohana</a>',
				[],
				'http://kohanaframework.org',
				'Kohana',
			],
			[
				'<a href="http://google.com" target="_blank">GOOGLE</a>',
				[],
				'http://google.com',
				'GOOGLE',
				['target' => '_blank'],
				'http',
			],
			[
				'<a href="//google.com/">GOOGLE</a>',
				[],
				'//google.com/',
				'GOOGLE',
			],
			[
				'<a href="https://www.kohanaframework.org/kohana/users/example">Kohana</a>',
				[],
				'users/example',
				'Kohana',
				NULL,
				'https',
				FALSE,
			],
			[
				'<a href="https://www.kohanaframework.org/kohana/index.php/users/example">Kohana</a>',
				[],
				'users/example',
				'Kohana',
				NULL,
				'https',
				TRUE,
			],
			[
				'<a href="https://www.kohanaframework.org/kohana/index.php/users/example">Kohana</a>',
				[],
				'users/example',
				'Kohana',
				NULL,
				'https',
			],
			[
				'<a href="https://www.kohanaframework.org/kohana/index.php/users/example">Kohana</a>',
				[],
				'users/example',
				'Kohana',
				NULL,
				'https',
				TRUE,
			],
			[
				'<a href="https://www.kohanaframework.org/kohana/users/example">Kohana</a>',
				[],
				'users/example',
				'Kohana',
				NULL,
				'https',
				FALSE,
			],
			[
				'<a href="https://www.kohanaframework.org/kohana/users/example">Kohana</a>',
				[],
				'/users/example',
				'Kohana',
				NULL,
				'https',
				FALSE,
			],
		];
	}

	/**
	 * Tests HTML::anchor
	 *
	 * @test
	 * @dataProvider provider_anchor
	 */
	public function test_anchor($expected, array $options, $uri, $title = NULL, array $attributes = NULL, $protocol = NULL, $index = TRUE)
	{
		// $this->setEnvironment($options);

		$this->assertSame(
			$expected,
			HTML::anchor($uri, $title, $attributes, $protocol, $index)
		);
	}

	/**
	 * Data provider for test_file_anchor
	 *
	 * @return array
	 */
	public function provider_file_anchor()
	{
		return [
			[
				'<a href="/kohana/mypic.png">My picture file</a>',
				[],
				'mypic.png',
				'My picture file',
			],
			[
				'<a href="https://www.kohanaframework.org/kohana/index.php/mypic.png" attr="value">My picture file</a>',
				['attr' => 'value'],
				'mypic.png',
				'My picture file',
				'https',
				TRUE
			],
			[
				'<a href="ftp://www.kohanaframework.org/kohana/mypic.png">My picture file</a>',
				[],
				'mypic.png',
				'My picture file',
				'ftp',
				FALSE
			],
			[
				'<a href="ftp://www.kohanaframework.org/kohana/mypic.png">My picture file</a>',
				[],
				'/mypic.png',
				'My picture file',
				'ftp',
				FALSE
			],
		];
	}

	/**
	 * Test for HTML::file_anchor()
	 *
	 * @test
	 * @covers HTML::file_anchor
	 * @dataProvider provider_file_anchor
	 */
	public function test_file_anchor($expected, array $attributes, $file, $title = NULL, $protocol = NULL, $index = FALSE)
	{
		$this->assertSame(
			$expected,
			HTML::file_anchor($file, $title, $attributes, $protocol, $index)
		);
	}

	/**
	 * Provides test data for test_image
	 *
	 * @return array Array of test data
	 */
	public function provider_image()
	{
		return [
			[
				'<img src="http://google.com/image.png" />',
				'http://google.com/image.png',
			],
			[
				'<img src="//google.com/image.png" />',
				'//google.com/image.png',
			],
			[
				'<img src="/kohana/img/image.png" />',
				'img/image.png',
			],
			[
				'<img src="https://www.kohanaframework.org/kohana/index.php/img/image.png" alt="..." />',
				'img/image.png',
				['alt' => '...',],
				'https',
				TRUE
			],
			[
				'<img src="data:image/gif;base64,R0lGODlhBQAFAIAAAHx8fP///yH5BAEAAAEALAAAAAAFAAUAAAIIBGKGF72rTAEAOw==" />',
				'data:image/gif;base64,R0lGODlhBQAFAIAAAHx8fP///yH5BAEAAAEALAAAAAAFAAUAAAIIBGKGF72rTAEAOw==',
			],
		];
	}

	/**
	 * Tests HTML::image()
	 *
	 * @test
	 * @dataProvider  provider_image
	 * @param string  $expected       Expected output
	 * @param string  $file           file name
	 * @param array   $attributes     HTML attributes for the image
	 * @param string  $protocol       Protocol to use
	 * @param bool    $index          Should the index file be included in url?
	 */
	public function test_image($expected, $file, array $attributes = NULL, $protocol = NULL, $index = FALSE)
	{
		$this->assertSame(
			$expected,
			HTML::image($file, $attributes, $protocol, $index)
		);
	}

}

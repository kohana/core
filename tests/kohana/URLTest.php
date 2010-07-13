<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests URL
 *
 * @group kohana
 * @group kohana.url
 *
 * @package    Unittest
 * @author     Kohana Team
 * @author     BRMatt <matthew@sigswitch.com>
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
Class Kohana_URLTest extends Kohana_Unittest_TestCase
{
	/**
	 * Default values for the environment, see setEnvironment
	 * @var array
	 */
	protected $environmentDefault =	array(
		'Kohana::$base_url'	=> '/kohana/',
		'Kohana::$index_file'=> 'index.php',
		'Request::$protocol'	=> 'http',
		'HTTP_HOST' => 'example.com',
		'_GET'		=> array(),
	);

	/**
	 * Provides test data for testBase()
	 * 
	 * @return array
	 */
	function provider_base()
	{
		return array(
			// $index, $protocol, $expected, $enviroment
			//
			// Test with different combinations of parameters for max code coverage
			array(FALSE, FALSE,  '/kohana/'),
			array(FALSE, TRUE,   'http://example.com/kohana/'),
			array(TRUE,  FALSE,  '/kohana/index.php/'),
			array(TRUE,  FALSE,  '/kohana/index.php/'),
			array(TRUE,  TRUE,   'http://example.com/kohana/index.php/'),
			array(TRUE,  'http', 'http://example.com/kohana/index.php/'),
			array(TRUE,  'https','https://example.com/kohana/index.php/'),
			array(TRUE,  'ftp',  'ftp://example.com/kohana/index.php/'),

			//
			// These tests make sure that the protocol changes when the global setting changes
			array(TRUE,   TRUE,   'https://example.com/kohana/index.php/', array('Request::$protocol' => 'https')),
			array(FALSE,  TRUE,   'https://example.com/kohana/', array('Request::$protocol' => 'https')),

			// Change base url
			array(FALSE, 'https', 'https://example.com/kohana/', array('Kohana::$base_url' => 'omglol://example.com/kohana/'))
		);
	}

	/**
	 * Tests URL::base()
	 *
	 * @test
	 * @dataProvider provider_base
	 * @param boolean $index       Parameter for Url::base()
	 * @param boolean $protocol    Parameter for Url::base()
	 * @param string  $expected    Expected url
	 * @param array   $enviroment  Array of enviroment vars to change @see Kohana_URLTest::setEnvironment()
	 */
	function test_base($index, $protocol, $expected, array $enviroment = array())
	{
		$this->setEnvironment($enviroment);

		$this->assertSame(
			$expected,
			URL::base($index, $protocol)
		);
	}

	/**
	 * Provides test data for test_site()
	 * 
	 * @return array
	 */
	function provider_site()
	{
		return array(
			array('', FALSE,		'/kohana/index.php/'),
			array('', TRUE,			'http://example.com/kohana/index.php/'),

			array('my/site', FALSE, '/kohana/index.php/my/site'),
			array('my/site', TRUE,  'http://example.com/kohana/index.php/my/site'),

			array('my/site?var=asd&kohana=awesome', FALSE,  '/kohana/index.php/my/site?var=asd&kohana=awesome'),
			array('my/site?var=asd&kohana=awesome', TRUE,  'http://example.com/kohana/index.php/my/site?var=asd&kohana=awesome'),

			array('?kohana=awesome&life=good', FALSE, '/kohana/index.php/?kohana=awesome&life=good'),
			array('?kohana=awesome&life=good', TRUE, 'http://example.com/kohana/index.php/?kohana=awesome&life=good'),

			array('?kohana=awesome&life=good#fact', FALSE, '/kohana/index.php/?kohana=awesome&life=good#fact'),
			array('?kohana=awesome&life=good#fact', TRUE, 'http://example.com/kohana/index.php/?kohana=awesome&life=good#fact'),

			array('some/long/route/goes/here?kohana=awesome&life=good#fact', FALSE, '/kohana/index.php/some/long/route/goes/here?kohana=awesome&life=good#fact'),
			array('some/long/route/goes/here?kohana=awesome&life=good#fact', TRUE, 'http://example.com/kohana/index.php/some/long/route/goes/here?kohana=awesome&life=good#fact'),

			array('/route/goes/here?kohana=awesome&life=good#fact', 'https', 'https://example.com/kohana/index.php/route/goes/here?kohana=awesome&life=good#fact'),
			array('/route/goes/here?kohana=awesome&life=good#fact', 'ftp', 'ftp://example.com/kohana/index.php/route/goes/here?kohana=awesome&life=good#fact'),
		);
	}

	/**
	 * Tests URL::site()
	 *
	 * @test
	 * @dataProvider provider_site
	 * @param string          $uri         URI to use
	 * @param boolean|string  $protocol    Protocol to use
	 * @param string          $expected    Expected result
	 * @param array           $enviroment  Array of enviroment vars to set
	 */
	function test_site($uri, $protocol, $expected, array $enviroment = array())
	{
		$this->setEnvironment($enviroment);

		$this->assertSame(
			$expected,
			URL::site($uri, $protocol)
		);
	}

	/**
	 * Provides test data for test_title()
	 * @return array
	 */
	function provider_title()
	{
		return array(
			// Tests that..
			// Title is converted to lowercase
			array('we-shall-not-be-moved', 'WE SHALL NOT BE MOVED', '-'),
			// Excessive white space is removed and replaced with 1 char
			array('thissssss-is-it', 'THISSSSSS         IS       IT  ', '-'),
			// separator is either - (dash) or _ (underscore) & others are converted to underscores
			array('some-title', 'some title', '-'),
			array('some_title', 'some title', '_'),
			array('some!title', 'some title', '!'),
			array('some:title', 'some title', ':'),
			// Numbers are preserved
			array('99-ways-to-beat-apple', '99 Ways to beat apple', '-'),
			// ... with lots of spaces & caps
			array('99_ways_to_beat_apple', '99    ways   TO beat      APPLE', '_'),
			array('99-ways-to-beat-apple', '99    ways   TO beat      APPLE', '-'),
			// Invalid characters are removed
			array('each-gbp-is-now-worth-32-usd', 'Each GBP(£) is now worth 32 USD($)', '-'),
			// ... inc. separator
			array('is-it-reusable-or-re-usable', 'Is it reusable or re-usable?', '-'),
			// Doing some crazy UTF8 tests
			array('espana-wins', 'España-wins', '-', TRUE),
		);
	}

	/**
	 * Tests URL::title()
	 *
	 * @test
	 * @dataProvider provider_title
	 * @param string $title        Input to convert
	 * @param string $separator    Seperate to replace invalid characters with
	 * @param string $expected     Expected result
	 */
	function testTitle($expected, $title, $separator, $ascii_only = FALSE)
	{
		$this->assertSame(
			$expected,
			URL::title($title, $separator, $ascii_only)
		);
	}

	/**
	 * Provides test data for URL::query()
	 * @return array
	 */
	public function providerQuery()
	{
		return array(
			array(NULL, '', array()),
			array(NULL, '?test=data', array('_GET' => array('test' => 'data'))),
			array(array('test' => 'data'), '?test=data', array()),
			array(array('test' => 'data'), '?more=data&test=data', array('_GET' => array('more' => 'data')))
		);
	}

	/**
	 * Tests URL::query()
	 *
	 * @test
	 * @dataProvider providerQuery
	 * @param array $params Query string
	 * @param string $expected Expected result
	 * @param array $enviroment Set environment
	 */
	function testQuery($params, $expected, $enviroment)
	{
		$this->setEnvironment($enviroment);

		$this->assertSame(
			$expected,
			URL::query($params)
		);
	}
}

<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests HTTP
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.http
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_HTTPTest extends Unittest_TestCase {

	protected $_inital_request;

	/**
	 * Sets up the environment
	 */
	// @codingStandardsIgnoreStart
	public function setUp()
	// @codingStandardsIgnoreEnd
	{
		parent::setUp();
		Kohana::$config->load('url')->set('trusted_hosts', array('www\.example\.com'));
		$this->_initial_request = Request::$initial;
		Request::$initial = new Request('/');
	}

	/**
	 * Tears down whatever is setUp
	 */
	// @codingStandardsIgnoreStart
	public function tearDown()
	// @codingStandardsIgnoreEnd
	{
		Request::$initial = $this->_initial_request;
		parent::tearDown();
	}
	// @codingStandardsIgnoreStart

	/**
	 * Defaults for this test
	 * @var array
	 */
	// @codingStandardsIgnoreStart
	protected $environmentDefault = array(
		'Kohana::$base_url'    => '/kohana/',
		'Kohana::$index_file'  => 'index.php',
		'HTTP_HOST'	           => 'www.example.com',
	);
	// @codingStandardsIgnoreEnd

	/**
	 * Provides test data for test_attributes()
	 *
	 * @return array
	 */
	public function provider_redirect()
	{
		return array(
			array(
				'http://www.example.org/',
				301,
				'HTTP_Exception_301',
				'http://www.example.org/'
			),
			array(
				'/page_one',
				302,
				'HTTP_Exception_302',
				'http://www.example.com/kohana/index.php/page_one'
			),
			array(
				'page_two',
				303,
				'HTTP_Exception_303',
				'http://www.example.com/kohana/index.php/page_two'
			),
		);
	}

	/**
	 * Tests HTTP::redirect()
	 *
	 * @test
	 * @dataProvider provider_redirect
	 * @param array  $location            Location to redirect to
	 * @param array  $code                HTTP Code to use for the redirect
	 * @param string $expected_exception  Expected exception
	 * @param string $expected_location   Expected exception
	 */
	public function test_redirect($location, $code, $expected_exception, $expected_location)
	{
		try
		{
			HTTP::redirect($location, $code);
		}
		catch (HTTP_Exception_Redirect $e)
		{
			$response = $e->get_response();

			$this->assertInstanceOf($expected_exception, $e);
			$this->assertEquals($expected_location, $response->headers('Location'));
			
			return;
		}

		$this->fail('HTTP_Exception_Redirect not thrown');
	}

	/**
	 * Provides test data for test_request_headers
	 *
	 * @return array
	 */
	public function provider_request_headers()
	{
		return array(
			array(
				array(
					'CONTENT_TYPE' => 'text/html; charset=utf-8',
					'CONTENT_LENGTH' => '3547',
					'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
					'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, sdch',
					'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8,fr;q=0.6,hy;q=0.4',
				),
				array(
					'content-type' => 'text/html; charset=utf-8',
					'content-length' => '3547',
					'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
					'accept-encoding'=>'gzip, deflate, sdch',
					'accept-language'=>'en-US,en;q=0.8,fr;q=0.6,hy;q=0.4',
				)
			),
			array(
				array(
					'HTTP_WEIRD_HTTP_HEADER' => 'A weird value for a weird header',
				),
				array(
					'weird-http-header' => 'A weird value for a weird header',
				)
			),
		);
	}

	/**
	 * Tests HTTP::request_headers()
	 *
	 * HTTP::request_headers relies on the $_SERVER superglobal if the function
	 * `apache_request_headers` or the PECL `http` extension are not available.
	 *
	 * The test feeds the $_SERVER superglobal with the test cases' datasets
	 * and then restores the $_SERVER superglobal so that it does not affect
	 * other tests.
	 *
	 * @test
	 * @dataProvider provider_request_headers
	 * @param array  $server_globals      globals to feed $_SERVER
	 * @param array  $expected_headers    Expected, cleaned HTTP headers
	 */
	public function test_request_headers(array $server_globals, array $expected_headers)
	{
		// save the $_SERVER super-global into temporary local var
		$tmp_server = $_SERVER;

		$_SERVER = array_replace_recursive($_SERVER, $server_globals);
		$headers = HTTP::request_headers();

		$actual_headers = array_intersect_key($headers->getArrayCopy(), $expected_headers);

		$this->assertSame($expected_headers, $actual_headers);

		// revert the super-global to its previous state
		$_SERVER = $tmp_server;
	}
}

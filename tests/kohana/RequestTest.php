<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Unit tests for request class
 *
 * @group kohana
 * @group kohana.request
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     BRMatt <matthew@sigswitch.com>
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_RequestTest extends Unittest_TestCase
{
	/**
	 * Provides the data for test_create()
	 * @return  array
	 */
	public function provider_create()
	{
		return array(
			array('foo/bar', 'Request_Client_Internal'),
			array('http://google.com', 'Request_Client_External'),
		);
	}

	/**
	 * Ensures the create class is created with the correct client
	 *
	 * @test
	 * @dataProvider provider_create
	 */
	public function test_create($uri, $client_class)
	{
		$request = Request::factory($uri);

		$this->assertInstanceOf($client_class, $request->get_client());
	}

	/**
	 * Ensure that parameters can be read
	 *
	 * @test
	 */
	public function test_param()
	{
		$uri = 'foo/bar/id';
		$request = Request::factory($uri);

		$this->assertArrayHasKey('id', $request->param());
		$this->assertArrayNotHasKey('foo', $request->param());
		$this->assertEquals($request->uri(), $uri);

		// Ensure the params do not contain contamination from controller, action, route, uri etc etc
		$params = $request->param();

		// Test for illegal components
		$this->assertArrayNotHasKey('controller', $params);
		$this->assertArrayNotHasKey('action', $params);
		$this->assertArrayNotHasKey('directory', $params);
		$this->assertArrayNotHasKey('uri', $params);
		$this->assertArrayNotHasKey('route', $params);
	}

	/**
	 * Provides data for Request::create_response()
	 */
	public function provider_create_response()
	{
		return array(
			array('foo/bar', TRUE, TRUE),
			array('foo/bar', FALSE, FALSE)
		);
	}

	/**
	 * Ensures a request creates an empty response, and binds correctly
	 *
	 * @test
	 * @dataProvider  provider_create_response
	 */
	public function test_create_response($uri, $bind, $equality)
	{
		$request = Request::factory($uri);
		$response = $request->create_response($bind);

		$this->assertEquals(($request->response() === $response), $equality);
	}

	/**
	 * Tests Request::response()
	 *
	 * @test
	 */
	public function test_response()
	{
		$request = Request::factory('foo/bar');
		$response = $request->create_response(FALSE);

		$this->assertEquals($request->response(), NULL);
		$this->assertEquals(($request->response($response) === $request), TRUE);
		$this->assertEquals(($request->response() === $response), TRUE);
	}

	/**
	 * Tests Request::method()
	 *
	 * @test
	 */
	public function test_method()
	{
		$request = Request::factory('foo/bar');

		$this->assertEquals($request->method(), 'GET');
		$this->assertEquals(($request->method('post') === $request), TRUE);
		$this->assertEquals(($request->method() === 'POST'), TRUE);
	}

	/**
	 * Tests Request::route()
	 *
	 * @test
	 */
	public function test_route()
	{
		$request = Request::factory(''); // This should always match something, no matter what changes people make

		$this->assertInstanceOf('Route', $request->route());
	}

	/**
	 * Tests Request::accept_type()
	 *
	 * @test
	 * @covers Request::accept_type
	 */
	public function test_accept_type()
	{
		$this->assertEquals(array('*/*' => 1), Request::accept_type());
	}

	/**
	 * Provides test data for Request::accept_lang()
	 * @return array
	 */
	public function provider_accept_lang()
	{
		return array(
			array('en-us', 1, array('_SERVER' => array('HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5'))),
			array('en-us', 1, array('_SERVER' => array('HTTP_ACCEPT_LANGUAGE' => 'en-gb'))),
			array('en-us', 1, array('_SERVER' => array('HTTP_ACCEPT_LANGUAGE' => 'sp-sp;q=0.5')))
		);
	}

	/**
	 * Tests Request::accept_lang()
	 *
	 * @test
	 * @covers Request::accept_lang
	 * @dataProvider provider_accept_lang
	 * @param array $params Query string
	 * @param string $expected Expected result
	 * @param array $enviroment Set environment
	 */
	public function test_accept_lang($params, $expected, $enviroment)
	{
		$this->setEnvironment($enviroment);

		$this->assertEquals(
			$expected,
			Request::accept_lang($params)
		);
	}

	/**
	 * Provides test data for Request::url()
	 * @return array
	 */
	public function provider_url()
	{
		return array(
			array(
				'foo/bar',
				array(),
				'http',
				TRUE,
				'http://localhost/kohana/foo/bar'
			),
			array(
				'foo',
				array('action' => 'bar'),
				'http',
				TRUE,
				'http://localhost/kohana/foo/bar'
			),
		);
	}

	/**
	 * Tests Request::url()
	 *
	 * @test
	 * @dataProvider provider_url
	 * @covers Request::url
	 * @param string $route the route to use
	 * @param array $params params to pass to route::uri
	 * @param string $protocol the protocol to use
	 * @param array $expected The string we expect
	 */
	public function test_url($uri, $params, $protocol, $is_cli, $expected)
	{
		$this->setEnvironment(array(
			'Kohana::$base_url'  => '/kohana/',
			'_SERVER'            => array('HTTP_HOST' => 'localhost', 'argc' => $_SERVER['argc']),
			'Kohana::$index_file' => FALSE,
			'Kohana::$is_cli'    => $is_cli,
		));

		$this->assertEquals(Request::factory($uri)->url($params, $protocol), $expected);
	}

	/**
	 * Tests that request caching works
	 *
	 * @return null
	 */
	public function test_cache()
	{
		/**
		 * Sets up a mock cache object, asserts that:
		 * 
		 *  1. The cache set() method gets called
		 *  2. The cache get() method will return the response above when called
		 */
		$cache = $this->getMock('Cache_File', array('get', 'set'), array(), 'Cache');
		$cache->expects($this->once())
			->method('set');

		$foo = Request::factory('', $cache);
		$response = $foo->create_response(TRUE);

		$response->headers('Cache-Control', 'max-age=100');
		$foo->response($response);
		$foo->execute();

		/**
		 * Set up a mock response object to test with
		 */
		$response = $this->getMock('Response');
		$response->expects($this->any())
			->method('body')
			->will($this->returnValue('Foo'));

		$cache->expects($this->any())
			->method('get')
			->will($this->returnValue($response));

		$foo = Request::factory('', $cache)->execute();
		$this->assertSame('Foo', $foo->body());
	}

	/**
	 * Data provider for test_set_cache
	 *
	 * @return array
	 */
	public function provider_set_cache()
	{
		return array(
			array(
				array('cache-control' => 'no-cache'),
				array('no-cache' => NULL),
				FALSE,
			),
			array(
				array('cache-control' => 'no-store'),
				array('no-store' => NULL),
				FALSE,
			),
			array(
				array('cache-control' => 'max-age=100'),
				array('max-age' => '100'),
				TRUE
			),
			array(
				array('cache-control' => 'private'),
				array('private' => NULL),
				FALSE
			),
			array(
				array('cache-control' => 'private, max-age=100'),
				array('private' => NULL, 'max-age' => '100'),
				FALSE
			),
			array(
				array('cache-control' => 'private, s-maxage=100'),
				array('private' => NULL, 's-maxage' => '100'),
				TRUE
			),
			array(
				array(
					'expires' => date('m/d/Y', strtotime('-1 day')),
				),
				array(),
				FALSE
			),
			array(
				array(
					'expires' => date('m/d/Y', strtotime('+1 day')),
				),
				array(),
				TRUE
			),
			array(
				array(),
				array(),
				TRUE
			),
		);
	}

	/**
	 * Tests the set_cache() method
	 *
	 * @test
	 * @dataProvider provider_set_cache
	 * 
	 * @return null
	 */
	public function test_set_cache($headers, $cache_control, $expected)
	{
		/**
		 * Set up a mock response object to test with
		 */
		$response = $this->getMock('Response');
		$response->expects($this->any())
			->method('parse_cache_control')
			->will($this->returnValue($cache_control));
		$response->expects($this->any())
			->method('headers')
			->will($this->returnValue($headers));

		$request = new Request_Client_Internal;
		$this->assertEquals($request->set_cache($response), $expected);
	}

	/**
	 * Data provider for test_set_protocol() test
	 *
	 * @return array
	 */
	public function provider_set_protocol()
	{
		return array(
			array(
				'http',
				'http',
			),
			array(
				'FTP',
				'ftp',
			),
			array(
				'hTTps',
				'https',
			),
		);
	}

	/**
	 * Tests the protocol() method
	 *
	 * @dataProvider provider_set_protocol
	 * 
	 * @return null
	 */
	public function test_set_protocol($protocol, $expected)
	{
		$request = Request::factory();

		// Set the supplied protocol
		$result = $request->protocol($protocol);

		// Test the set value
		$this->assertSame($request->protocol(), $expected);

		// Test the return value
		$this->assertTrue($request instanceof $result);
	}
}
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
	public function test_initial()
	{
		$original = array(
			'Kohana::$is_cli' => Kohana::$is_cli,
			'Request::$initial' => Request::$initial,
			'Request::$client_ip' => Request::$client_ip,
			'Request::$user_agent' => Request::$user_agent,
			'_SERVER' => $_SERVER,
			'_GET' => $_GET,
			'_POST' => $_POST,
		);

		$this->setEnvironment(array(
			'Kohana::$is_cli' => FALSE,
			'Request::$initial' => NULL,
			'Request::$client_ip' => NULL,
			'Request::$user_agent' => NULL,
			'_SERVER' => array(
				'HTTPS' => NULL,
				'PATH_INFO' => '/',
				'HTTP_REFERER' => 'http://example.com/',
				'HTTP_USER_AGENT' => 'whatever (Mozilla 5.0/compatible)',
				'REMOTE_ADDR' => '127.0.0.1',
				'REQUEST_METHOD' => 'GET',
				'HTTP_X_REQUESTED_WITH' => 'ajax-or-something',
			),
			'_GET' => array(),
			'_POST' => array(),
		));

		$request = Request::factory();

		$this->assertEquals(Request::$initial, $request);

		$this->assertEquals(Request::$client_ip, '127.0.0.1');

		$this->assertEquals(Request::$user_agent, 'whatever (Mozilla 5.0/compatible)');

		$this->assertEquals($request->protocol(), 'http');

		$this->assertEquals($request->referrer(), 'http://example.com/');

		$this->assertEquals($request->requested_with(), 'ajax-or-something');

		$this->assertEquals($request->query(), array());

		$this->assertEquals($request->post(), array());

		$this->setEnvironment($original);
	}

	/**
	 * Tests that an initial request won't use an external client
	 * 
	 * @expectedException HTTP_Exception_404
	 *
	 * @return null
	 */
	public function test_initial_request_only_loads_internal()
	{
		$this->setEnvironment(
			array(
				'Kohana::$is_cli' => FALSE,
				'Request::$initial' => NULL,
			)
		);

		$request = new Request('http://www.google.com/');
	}

	/**
	 * Tests that with an empty request, cli requests are routed properly
	 *
	 * @return null
	 */
	public function test_empty_cli_requests_route_properly()
	{
		$this->setEnvironment(
			array(
				'Kohana::$is_cli' => TRUE,
				'Request::$initial' => NULL,
			)
		);

		$route = new Route('(<controller>(/<action>))');
		$route->defaults(array(
			'controller' => 'welcome',
			'action'     => 'index',
		));

		$request = Request::factory(TRUE, NULL, array($route));
		$response = $request->execute();
	}

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

		$route = new Route('(<uri>)', array('uri' => '.+'));
		$route->defaults(array('controller' => 'foobar', 'action' => 'index'));
		$request = Request::factory('foobar', NULL, array($route));

		$this->assertSame('foobar', $request->param('uri'));
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

	/**
	 * Provides data for test_post_max_size_exceeded()
	 * 
	 * @return  array
	 */
	public function provider_post_max_size_exceeded()
	{
		// Get the post max size
		$post_max_size = Num::bytes(ini_get('post_max_size'));

		return array(
			array(
				$post_max_size+200000,
				TRUE
			),
			array(
				$post_max_size-20,
				FALSE
			),
			array(
				$post_max_size,
				FALSE
			)
		);
	}

	/**
	 * Tests the post_max_size_exceeded() method
	 * 
	 * @dataProvider provider_post_max_size_exceeded
	 *
	 * @param   int      content_length 
	 * @param   bool     expected 
	 * @return  void
	 */
	public function test_post_max_size_exceeded($content_length, $expected)
	{
		// Ensure the request method is set to POST
		Request::$initial->method(HTTP_Request::POST);

		// Set the content length
		$_SERVER['CONTENT_LENGTH'] = $content_length;

		// Test the post_max_size_exceeded() method
		$this->assertSame(Request::post_max_size_exceeded(), $expected);
	}

	/**
	 * Provides data for test_uri_only_trimed_on_internal()
	 *
	 * @return  array
	 */
	public function provider_uri_only_trimed_on_internal()
	{
		$old_request = Request::$initial;
		Request::$initial = new Request('foo/bar');

		$result = array(
			array(
				new Request('http://www.google.com'),
				'http://www.google.com'
			),
			array(
				new Request('http://www.google.com/'),
				'http://www.google.com/'
			),
			array(
				new Request('foo/bar/'),
				'foo/bar'
			),
			array(
				new Request('foo/bar'),
				'foo/bar'
			)
		);

		Request::$initial = $old_request;
		return $result;
	}

	/**
	 * Tests that the uri supplied to Request is only trimed
	 * for internal requests.
	 * 
	 * @dataProvider provider_uri_only_trimed_on_internal
	 *
	 * @return void
	 */
	public function test_uri_only_trimed_on_internal(Request $request, $expected)
	{
		$this->assertSame($request->uri(), $expected);
	}

	/**
	 * Data provider for test_options_set_to_external_client()
	 *
	 * @return  array
	 */
	public function provider_options_set_to_external_client()
	{
		$provider = array(
			array(
				array(
					CURLOPT_PROXYPORT   => 8080,
					CURLOPT_PROXYTYPE   => CURLPROXY_HTTP,
					CURLOPT_VERBOSE     => TRUE
				),
				array(
					CURLOPT_PROXYPORT   => 8080,
					CURLOPT_PROXYTYPE   => CURLPROXY_HTTP,
					CURLOPT_VERBOSE     => TRUE
				)
			)
		);

		if (extension_loaded('http'))
		{
			$provider[] = array(
				array(
					'proxyhost'         => 'http://localhost:8080',
					'proxytype'         => HTTP_PROXY_HTTP,
					'redirect'          => 2
				),
				array(
					'proxyhost'         => 'http://localhost:8080',
					'proxytype'         => HTTP_PROXY_HTTP,
					'redirect'          => 2
				)
			);
		}

		return $provider;
	}

	/**
	 * Test for Request_Client_External::options() to ensure options
	 * can be set to the external client (for cURL and PECL_HTTP)
	 *
	 * @dataProvider provider_options_set_to_external_client
	 * 
	 * @param   array    settings 
	 * @param   array    expected 
	 * @return void
	 */
	public function test_options_set_to_external_client($settings, $expected)
	{
		$request = Request::factory('http://www.kohanaframework.org');
		$request_client = $request->get_client();

		// Test for empty array
		$this->assertSame($request_client->options(), array());

		// Test that set works as expected
		$this->assertSame($request_client->options($settings), $request_client);

		// Test that each setting is present and returned
		foreach ($expected as $key => $value)
		{
			$this->assertSame($request_client->options($key), $value);
		}
	}

	/**
	 * Provides data for test_headers_get()
	 *
	 * @return  array
	 */
	public function provider_headers_get()
	{
		$x_powered_by = 'Kohana Unit Test';
		$content_type = 'application/x-www-form-urlencoded';

		return array(
			array(
				$request = Request::factory('foo/bar')
					->headers(array(
						'x-powered-by' => $x_powered_by,
						'content-type' => $content_type
					)
				),
			array(
				'x-powered-by' => $x_powered_by,
				'content-type' => $content_type
				)
			)
		);
	}

	/**
	 * Tests getting headers from the Request object
	 * 
	 * @dataProvider provider_headers_get
	 *
	 * @param   Request  request to test
	 * @param   array    headers to test against
	 * @return  void
	 */
	public function test_headers_get($request, $headers)
	{
		foreach ($headers as $key => $expected_value)
		{
			$this->assertSame((string) $request->headers($key), $expected_value);
		}
	}

	/**
	 * Provides data for test_headers_set
	 *
	 * @return  array
	 */
	public function provider_headers_set()
	{
		return array(
			array(
				new Request('foo/bar'),
				array(
					'content-type'  => 'application/x-www-form-urlencoded',
					'x-test-header' => 'foo'
				),
				"content-type: application/x-www-form-urlencoded\r\nx-test-header: foo\r\n\n"
			),
			array(
				new Request('foo/bar'),
				array(
					'content-type'  => 'application/json',
					'x-powered-by'  => 'kohana'
				),
				"content-type: application/json\r\nx-powered-by: kohana\r\n\n"
			)
		);
	}

	/**
	 * Tests the setting of headers to the request object
	 * 
	 * @dataProvider provider_headers_set
	 *
	 * @param   Request    request object
	 * @param   array      header(s) to set to the request object
	 * @param   string     expected http header
	 * @return  void
	 */
	public function test_headers_set(Request $request, $headers, $expected)
	{
		$request->headers($headers);
		$this->assertSame($expected, (string) $request->headers());
	}

	/**
	 * Provides test data for test_query_parameter_parsing()
	 *
	 * @return  array
	 */
	public function provider_query_parameter_parsing()
	{
		return array(
			array(
				new Request('foo/bar'),
				array(
					'foo'   => 'bar',
					'sna'   => 'fu'
				),
				array(
					'foo'   => 'bar',
					'sna'   => 'fu'
				),
			),
			array(
				new Request('foo/bar?john=wayne&peggy=sue'),
				array(
					'foo'   => 'bar',
					'sna'   => 'fu'
				),
				array(
					'john'  => 'wayne',
					'peggy' => 'sue',
					'foo'   => 'bar',
					'sna'   => 'fu'
				),
			),
			array(
				new Request('http://host.tld/foo/bar?john=wayne&peggy=sue'),
				array(
					'foo'   => 'bar',
					'sna'   => 'fu'
				),
				array(
					'john'  => 'wayne',
					'peggy' => 'sue',
					'foo'   => 'bar',
					'sna'   => 'fu'
				),
			),
		);
	}

	/**
	 * Tests that query parameters are parsed correctly
	 * 
	 * @dataProvider provider_query_parameter_parsing
	 *
	 * @param   Request   request 
	 * @param   array     query 
	 * @param   array    expected 
	 * @return  void
	 */
	public function test_query_parameter_parsing(Request $request, $query, $expected)
	{
		foreach ($query as $key => $value)
		{
			$request->query($key, $value);
		}

		$this->assertSame($expected, $request->query());
	}

	/**
	 * Provider for test_uri_without_query_parameters
	 *
	 * @return  array
	 */
	public function provider_uri_without_query_parameters()
	{
		return array(
			array(
				new Request('foo/bar?foo=bar&bar=foo'),
				array(),
				'foo/bar'
			),
			array(
				new Request('foo/bar'),
				array('bar' => 'foo', 'foo' => 'bar'),
				'foo/bar'
			),
			array(
				new Request('foo/bar'),
				array(),
				'foo/bar'
			)
		);
	}

	/**
	 * Tests that the [Request::uri()] method does not return
	 * query parameters
	 *
	 * @dataProvider provider_uri_without_query_parameters
	 * 
	 * @param   Request   request 
	 * @param   array     query 
	 * @param   string    expected 
	 * @return  void
	 */
	public function test_uri_without_query_parameters(Request $request, $query, $expected)
	{
		$request->query($query);

		$this->assertSame($expected, $request->uri());
	}
} // End Kohana_RequestTest
<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Unit tests for request class
 *
 * @group kohana
 *
 * @package    Unittest
 * @author     Kohana Team
 * @author     BRMatt <matthew@sigswitch.com>
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_RequestTest extends Kohana_Unittest_TestCase
{
	/**
	 * Route::matches() should return false if the route doesn't match against a uri
	 *
	 * @test
	 */
	public function test_create()
	{
		$request = Request::factory('foo/bar')->execute();

		$this->assertEquals(200, $request->status);
		$this->assertEquals('foo', $request->response);

		try
		{
			$request = new Request('bar/foo');
			$request->execute();
		}
		catch (Exception $e)
		{
			$this->assertEquals(TRUE, $e instanceof ReflectionException);
			$this->assertEquals('404', $request->status);
		}
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
	 * Provides test data for test_instance()
	 * 
	 * @return array
	 */
	public function provider_instance()
	{
		return array(
			// $route, $is_cli, $_server, $status, $response
			array('foo/bar', TRUE, array(), 200, ''), // Shouldn't this be 'foo' ?
			array('foo/foo', TRUE, array(), 200, ''), // Shouldn't this be a 404?
			array(
				'foo/bar',
				FALSE,
				array(
					'REQUEST_METHOD' => 'get',
					'HTTP_REFERER' => 'http://www.kohanaframework.org',
					'HTTP_USER_AGENT' => 'Kohana Unit Test',
					'REMOTE_ADDR' => '127.0.0.1',
				), 200, ''), // Shouldn't this be 'foo' ?
		);
	}

	/**
	 * Tests Request::instance()
	 *
	 * @test
	 * @dataProvider provider_instance
	 * @covers Request::instance
	 * @param boolean $value  Input for Kohana::sanitize
	 * @param boolean $result Output for Kohana::sanitize
	 */
	public function test_instance($route, $is_cli, $server, $status, $response)
	{
		$this->setEnvironment(array(
			'_SERVER'            => $server+array('argc' => $_SERVER['argc']),
			'Kohana::$is_cli'    => $is_cli,
			'Request::$instance' => NULL
		));
	
		$request = Request::instance($route);

		$this->assertEquals($status, $request->status);
		$this->assertEquals($response, $request->response);
		$this->assertEquals($route, $request->uri);

		if ( ! $is_cli)
		{
			$this->assertEquals($server['REQUEST_METHOD'], Request::$method);
			$this->assertEquals($server['HTTP_REFERER'], Request::$referrer);
			$this->assertEquals($server['HTTP_USER_AGENT'], Request::$user_agent);
		}
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

		$this->assertEquals(Request::instance($uri)->url($params, $protocol), $expected);
	}
}

class Controller_Foo extends Controller {
	public function action_bar()
	{
		$this->request->response = 'foo';
	}
}

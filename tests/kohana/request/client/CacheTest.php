<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');
/**
 * Unit tests for request client cache logic
 *
 * @group kohana
 * @group kohana.request
 * @group kohana.request.client
 * @group kohana.request.client.cache
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Request_Client_CacheTest extends Unittest_TestCase {

	/**
	 * Sets up a test route for caching
	 *
	 * @return void
	 */
	public function setUp()
	{
		Route::set('welcome', 'welcome/index')
			->defaults(array(
				'controller' => 'welcome',
				'action'     => 'index'
			));
		parent::setUp();
	}

	/**
	 * Tests the Client does not attempt to load cache if no Cache library
	 * is present
	 *
	 * @return void
	 */
	public function test_cache_not_called_with_no_cache()
	{
		$request       = new Request('welcome/index');
		$client_mock   = $this->getMock('Request_Client_Internal');
		$response      = new Response;

		$request->client($client_mock);
		$client_mock->expects($this->exactly(0))
			->method('cache_response');
		$client_mock->expects($this->once())
			->method('execute')
			->will($this->returnValue($response));

		$this->assertSame($request->execute(), $response);
	}

	/**
	 * Tests that the client attempts to load a cached response from the
	 * cache library, but fails.
	 *
	 * @return void
	 */
	public function test_cache_miss()
	{
		$request       = new Request('welcome/index');
		$cache_mock    = $this->_get_cache_mock();
		$request->client()->cache($cache_mock);

		$cache_mock->expects($this->once())
			->method('get')
			->with($request->client()->create_cache_key($request))
			->will($this->returnValue(FALSE));

		$request->client()->execute($request);

		$this->assertSame(Request_Client::CACHE_STATUS_MISS, 
			$request->headers(Request_Client::CACHE_STATUS_KEY)->value());
	}

	/**
	 * Tests the client saves a response if the correct headers are set
	 *
	 * @return void
	 */
	public function test_cache_save()
	{
		$lifetime      = 800;
		$request       = new Request('welcome/index');
		$cache_mock    = $this->_get_cache_mock();
		$response      = $request->create_response();

		$request->client()->cache($cache_mock);
		$response->headers('cache-control', 'max-age='.$lifetime);

		$cache_mock->expects($this->once())
			->method('set')
			->with($request->client()->create_cache_key($request), $response,
				$lifetime)
			->will($this->returnValue(TRUE));

		$request->client()->cache_response($request, $response);

		$this->assertSame(Request_Client::CACHE_STATUS_SAVED, 
			$request->response()->headers(Request_Client::CACHE_STATUS_KEY)->value());
	}

	/**
	 * Tests the client handles a cache HIT event correctly
	 *
	 * @return void
	 */
	public function test_cache_hit()
	{
		$lifetime      = 800;
		$request       = new Request('welcome/index');
		$cache_mock    = $this->_get_cache_mock();

		$request->client()->cache($cache_mock);
		$response = $request->create_response();

		$response->headers(array(
			'cache-control'                  => 'max-age='.$lifetime,
			Request_Client::CACHE_STATUS_KEY => 
				Request_Client::CACHE_STATUS_HIT
		));

		$cache_mock->expects($this->once())
			->method('get')
			->with($request->client()->create_cache_key($request))
			->will($this->returnValue($response));

		$request->client()->cache_response($request);

		$this->assertSame(Request_Client::CACHE_STATUS_HIT,
			$response->headers(Request_Client::CACHE_STATUS_KEY)->value());
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
	 * Returns a mock object for Cache
	 *
	 * @return Cache
	 */
	protected function _get_cache_mock()
	{
		return  $this->getMock('Cache_File', array(), array(), '', FALSE);
	}
} // End Kohana_Request_Client_CacheTest
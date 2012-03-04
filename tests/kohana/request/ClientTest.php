<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Unit tests for generic Request_Client class
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.request
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author	   Andrew Coulton
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Request_ClientTest extends Unittest_TestCase
{
	protected $_inital_request;

	public function setUp()
	{
		parent::setUp();
		$this->_initial_request = Request::$initial;
		Request::$initial = new Request('/');
	}

	public function tearDown()
	{
		Request::$initial = $this->_initial_request;
		parent::tearDown();
	}

	/**
	 * Provider for test_follows_redirects
	 * @return array
	 */
	public function provider_follows_redirects()
	{
		return array(
			array(TRUE,  '200', array(), FALSE),
			array(TRUE,  '200', array('location' => 'http://foo.com/'),  FALSE),
			array(TRUE,  '302', array('location' => 'http://foo.com/'), TRUE),
			array(FALSE, '302', array('location' => 'http://foo.com/'), FALSE)
		);
	}

	/**
	 * Tests that the client optionally follows properly formed redirects
	 *
	 * @dataProvider provider_follows_redirects
	 *
	 * @param  bool   $follow           Option value to set
	 * @param  string $response_status  HTTP response status to fake for initial request
	 * @param  array  $response_headers HTTP response headers to fake for initial request
	 * @param  bool   $expect_follow    Whether to expect the client to attempt to follow redirect
	 */
	public function test_follows_redirects($follow, $response_status, $response_headers, $expect_follow)
	{
		$client = new Request_Client_FollowTest_Dummy(array(
			'follow' => $follow
		));

		$client->test_response_status = $response_status;
		$client->test_response_headers = $response_headers;

		Request::factory('http://bar.com/')
			->client($client)
			->execute();

		if ($expect_follow)
		{
			$this->assertInstanceOf('Request',$client->test_follow_request);
		}
		else
		{
			$this->assertNull($client->test_follow_request);
		}
	}

	/**
	 * Tests that only specified headers are resent following a redirect
	 */
	public function test_follows_with_headers()
	{
		$client = new Request_Client_FollowTest_Dummy(array(
			'follow' => TRUE,
			'follow_headers' => array('Authorization','X-Follow-With-Value')
		));

		$client->test_response_status = '301';
		$client->test_response_headers = array('location' => 'http://foo.com/');

		$response = Request::factory('http://bar.com')
					->client($client)
					->headers(array(
						'Authorization' => 'follow',
						'X-Follow-With-Value' => 'follow',
						'X-Not-In-Follow' => 'no-follow'
					))
					->execute();

		$follow_request = $client->test_follow_request;
		$this->assertEquals($follow_request->headers('Authorization'),'follow');
		$this->assertEquals($follow_request->headers('X-Follow-With-Value'),'follow');
		$this->assertNull($follow_request->headers('X-Not-In-Follow'));
	}

	/**
	 * Provider for test_follows_with_strict_method
	 *
	 * @return array
	 */
	public function provider_follows_with_strict_method()
	{
		return array(
			array(201, NULL, Request::POST, Request::GET),
			array(301, NULL, Request::GET, Request::GET),
			array(302, TRUE, Request::POST, Request::POST),
			array(302, FALSE, Request::POST, Request::GET),
			array(303, NULL, Request::POST, Request::GET),
			array(307, NULL, Request::POST, Request::POST),
		);
	}

	/**
	 * Tests that the correct method is used (allowing for the strict_redirect setting)
	 * for follow requests.
	 *
	 * @dataProvider provider_follows_with_strict_method
	 *
	 * @param string $status_code   HTTP response code to fake
	 * @param bool   $strict_redirect Option value to set
	 * @param string $orig_method   Request method for the original request
	 * @param string $expect_method Request method expected for the follow request
	 */
	public function test_follows_with_strict_method($status_code, $strict_redirect, $orig_method, $expect_method)
	{
		$client = new Request_Client_FollowTest_Dummy(array(
			'follow' => TRUE,
			'strict_redirect' => $strict_redirect
		));

		$client->test_response_status = $status_code;
		$client->test_response_headers = array('location' => 'http://foo.com/');

		Request::factory('http://bar.com')
			->client($client)
			->method($orig_method)
			->execute();

		$this->assertEquals($client->test_follow_request->method(), $expect_method);
	}

	/**
	 * Provider for test_follows_with_body_if_not_get
	 *
	 * @return array
	 */
	public function provider_follows_with_body_if_not_get()
	{
		return array(
			array('GET','301',NULL),
			array('POST','303',NULL),
			array('POST','307','foo-bar')
		);
	}

	/**
	 * Tests that the original request body is sent when following a redirect
	 * (unless redirect method is GET)
	 *
	 * @dataProvider provider_follows_with_body_if_not_get
	 * @depends test_follows_with_strict_method
	 * @depends test_follows_redirects
	 *
	 * @param string $original_method  Request method to use for the original request
	 * @param string $response_status  Redirect status that will be issued
	 * @param string $expect_body      Expected value of body() in the second request
	 */
	public function test_follows_with_body_if_not_get($original_method, $response_status, $expect_body)
	{
		$client = new Request_Client_FollowTest_Dummy(array(
			'follow' => TRUE,
		));

		$client->test_response_status = $response_status;
		$client->test_response_headers = array('location' => 'http://foo.com/');

		$response = Request::factory('http://bar.com')
					->client($client)
					->method($original_method)
					->body('foo-bar')
					->execute();

		$follow_request = $client->test_follow_request;
		$this->assertEquals($follow_request->body(),$expect_body);
	}

} // End Kohana_Request_ClientTest



/**
 * Test harness to allow mocking and testing of redirect following behaviour
 */
class Request_Client_FollowTest_Dummy extends Request_Client
{
	public $test_response_status = NULL;
	public $test_response_headers = array();
	public $test_follow_request = NULL;

	/**
	 * Fakes the response status and headers
	 *
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	public function execute_request(Request $request, Response $response)
	{
		$response->headers($this->test_response_headers);
		$response->status($this->test_response_status);
		return $response;
	}

	/**
	 * Mocks a new Request to use for following the redirect
	 * @param string $url
	 * @return Request
	 */
	protected function _create_request($url)
	{
		$this->test_follow_request = PHPUnit_Framework_MockObject_Generator::getMock(
			'Request',
			array('execute'),
			array($url));

		return $this->test_follow_request;
	}
} // End Request_Client_FollowTest_Dummy
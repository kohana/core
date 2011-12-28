<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests the HTTP class
 *
 * @group kohana
 * @group kohana.http
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_HTTPTest extends Unittest_TestCase {

	/**
	 * Tests HTTP::redirect() for absolute URIs
	 *
	 * @test
	 */
	public function test_redirect_absolute()
	{
		$request = Request::factory('http://example.com/uri');
		$response = Response::factory()
			->headers('X-Test', 'Test')
			->headers('Referrer', 'ShouldBeReplaced');

		HTTP::redirect($request, $response, 'http://example.org', 303);

		$this->assertSame($response->status(), 303);
		$this->assertSame($response->headers('Location'), 'http://example.org');
		$this->assertSame($response->headers('Referrer'), 'http://example.com/uri');
		$this->assertSame($response->headers('X-Test'), 'Test');
	}

	/**
	 * Tests HTTP::redirect() for relative URIs
	 *
	 * @test
	 */
	public function test_redirect_relative()
	{
		$request = Request::factory('https://example.com/uri');
		$response = Response::factory()
			->headers('X-Test', 'Test')
			->headers('Referrer', 'ShouldBeReplaced');

		HTTP::redirect($request, $response, 'account/login', 307);

		$this->assertSame($response->status(), 307);
		$this->assertSame($response->headers('Location'), 'http://example.org');
		$this->assertSame($response->headers('Referrer'), 'https://example.com/uri');
		$this->assertSame($response->headers('X-Test'), 'Test');
	}
}

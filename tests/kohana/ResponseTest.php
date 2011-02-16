<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Unit tests for response class
 *
 * @group kohana
 * @group kohana.response
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_ResponseTest extends Unittest_TestCase
{
	/**
	 * Ensures that Kohana::$expose adds the x-powered-by header and
	 * makes sure it's set to the correct Kohana Framework string
	 *
	 * @test
	 */
	public function test_expose()
	{
		$this->markTestSkipped('send_headers() can only be executed once, test will never pass in current API');

		Kohana::$expose = TRUE;
		$response = new Response;
		$headers = $response->send_headers()->headers();
		$this->assertArrayHasKey('x-powered-by', (array) $headers);

		if (isset($headers['x-powered-by']))
		{
			$this->assertSame($headers['x-powered-by']->value, 'Kohana Framework '.Kohana::VERSION.' ('.Kohana::CODENAME.')');
		}

		Kohana::$expose = FALSE;
		$response = new Response;
		$headers = $response->send_headers()->headers();
		$this->assertArrayNotHasKey('x-powered-by', (array) $headers);
	}

	/**
	 * Provider for test_body
	 *
	 * @return array
	 */
	public function provider_body()
	{
		$view = $this->getMock('View');
		$view->expects($this->any())
			->method('__toString')
			->will($this->returnValue('foo'));

		return array(
			array('unit test', 'unit test'),
			array($view, 'foo'),
		);
	}

	/**
	 * Tests that we can set and read a body of a response
	 * 
	 * @test
	 * @dataProvider provider_body
	 *
	 * @return null
	 */
	public function test_body($source, $expected)
	{
		$response = new Response;
		$response->body($source);
		$this->assertSame($response->body(), $expected);

		$response = (string) $response;
		$this->assertSame($response, $expected);
	}

	/**
	 * Test the content type is sent when set
	 * 
	 * @test
	 */
	public function test_content_type_when_set()
	{
		$this->markTestSkipped('send_headers() can only be executed once, test will never pass in current API');

		$content_type = 'application/json';
		$response = new Response;
		$response->headers('content-type', $content_type);
		$headers  = $response->send_headers()->headers();
		$this->assertSame($content_type, (string) $headers['content-type']);
	}

	/**
	 * Tests that the default content type is sent if not set
	 * 
	 * @test
	 */
	public function test_default_content_type_when_not_set()
	{
		$this->markTestSkipped('send_headers() can only be executed once, test will never pass in current API');

		$response = new Response;
		$headers = $response->send_headers()->headers();
		$this->assertSame(Kohana::$content_type.'; charset='.Kohana::$charset, (string) $headers['content-type']);
	}
}
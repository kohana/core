<?php
use Kohana\Core\Request\Client\External;
use Kohana\Core\Request\Client\Stream;

/**
 * Unit tests for external request client
 *
 * @group kohana
 * @group kohana.request
 * @group kohana.request.client
 * @group kohana.request.client.external
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Request_Client_ExternalTest extends Unittest_TestCase {

	/**
	 * Provider for test_factory()
	 *
	 * @return  array
	 */
	public function provider_factory()
	{
		External::$client = 'Kohana\Core\Request\Client\Stream';

		$return = array(
			array(
				array(),
				NULL,
				'Kohana\Core\Request\Client\Stream'
			),
			array(
				array(),
				'Kohana\Core\Request\Client\Stream',
				'Kohana\Core\Request\Client\Stream'
			)
		);

		if (extension_loaded('curl'))
		{
			$return[] = array(
				array(),
				'Kohana\Core\Request\Client\Curl',
				'Kohana\Core\Request\Client\Curl'
			);
		}

		if (extension_loaded('http'))
		{
			$return[] = array(
				array(),
				'Kohana\Core\Request\Client\HTTP',
				'Kohana\Core\Request\Client\HTTP'
			);
		}

		return $return;
	}

	/**
	 * Tests the [Request_Client_External::factory()] method
	 * 
	 * @dataProvider provider_factory
	 *
	 * @param   array   $params  params 
	 * @param   string  $client  client 
	 * @param   Request_Client_External $expected expected 
	 * @return  void
	 */
	public function test_factory($params, $client, $expected)
	{
		$this->assertInstanceOf($expected, External::factory($params, $client));
	}

	/**
	 * Data provider for test_options
	 *
	 * @return  array
	 */
	public function provider_options()
	{
		return array(
			array(
				NULL,
				NULL,
				array()
			),
			array(
				array('foo' => 'bar', 'stfu' => 'snafu'),
				NULL,
				array('foo' => 'bar', 'stfu' => 'snafu')
			),
			array(
				'foo',
				'bar',
				array('foo' => 'bar')
			),
			array(
				array('foo' => 'bar'),
				'foo',
				array('foo' => 'bar')
			)
		);
	}

	/**
	 * Tests the [Request_Client_External::options()] method
	 *
	 * @dataProvider provider_options
	 * 
	 * @param   mixed  $key  key 
	 * @param   mixed  $value  value 
	 * @param   array  $expected  expected 
	 * @return  void
	 */
	public function test_options($key, $value, $expected)
	{
		// Create a mock external client
		$client = new Stream;

		$client->options($key, $value);
		$this->assertSame($expected, $client->options());
	}

	/**
	 * Data provider for test_execute
	 *
	 * @return  array
	 */
	public function provider_execute()
	{
		$json = '{"foo": "bar", "snafu": "stfu"}';
		$post = array('foo' => 'bar', 'snafu' => 'stfu');

		return array(
			array(
				'application/json',
				$json,
				array(),
				array(
					'content-type' => 'application/json',
					'body'         => $json
				)
			),
			array(
				'application/json',
				$json,
				$post,
				array(
					'content-type' => 'application/x-www-form-urlencoded; charset='.Kohana::$charset,
					'body'         => http_build_query($post, NULL, '&')
				)
			)
		);
	}

	/**
	 * Tests the [Request_Client_External::_send_message()] method
	 *
	 * @dataProvider provider_execute
	 * 
	 * @return  void
	 */
	public function test_execute($content_type, $body, $post, $expected)
	{
		$old_request = Request::$initial;
		Request::$initial = TRUE;

		// Create a mock Request
		$request = new Request('http://kohanaframework.org/');
		$request->method(\Kohana\Core\HTTP\RequestInterface::POST)
			->headers('content-type', $content_type)
			->body($body)
			->post($post);

		$client = $this->getMock('Kohana\Core\Request\Client\External', array('_send_message'));
		$client->expects($this->once())
			->method('_send_message')
			->with($request)
			->will($this->returnValue($this->getMock('Response')));

		$request->client($client);

		$this->assertInstanceOf('Response', $request->execute());
		$this->assertSame($expected['body'], $request->body());
		$this->assertSame($expected['content-type'], (string) $request->headers('content-type'));

		Request::$initial = $old_request;
	}
}

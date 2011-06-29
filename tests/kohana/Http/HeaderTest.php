<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Unit Tests for Kohana_HTTP_Header
 *
 * @group kohana
 * @group kohana.http
 * @group kohana.http.header
 * @group kohana.http.header
 * 
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_HTTP_HeaderTest extends Unittest_TestCase {

	/**
	 * Provides data for test_accept_quality
	 *
	 * @return  array
	 */
	public function provider_accept_quality()
	{
		return array(
			array(
				array(
					'text/html; q=1',
					'text/plain; q=.5',
					'application/json; q=.1',
					'text/*'
				),
				array(
					'text/html'        => (float) 1,
					'text/plain'       => 0.5,
					'application/json' => 0.1,
					'text/*'           => (float) 1
				)
			),
			array(
				array(
					'text/*',
					'text/html; level=1; q=0.4',
					'application/xml+rss; q=0.5; level=4'
				),
				array(
					'text/*'             => (float) 1,
					'text/html; level=1' => 0.4,
					'application/xml+rss; level=4' => 0.5
				)
			)
		);
	}

	/**
	 * Tests the `accept_quality` method parses the quality values
	 * correctly out of header parts
	 * 
	 * @dataProvider provider_accept_quality
	 *
	 * @param   array     input
	 * @param   array     expected output
	 * @return  void
	 */
	public function test_accept_quality(array $parts, array $expected)
	{
		$out = HTTP_Header::accept_quality($parts);

		foreach ($out as $key => $value)
		{
			$this->assertInternalType('float', $value);
		}

		$this->assertSame($expected, $out);
	}

	/**
	 * Data provider for test_parse_accept_header
	 *
	 * @return  array
	 */
	public function provider_parse_accept_header()
	{
		return array(
			array(
				'text/html, text/plain, text/*, */*',
				array(
					'text' => array(
						'html'   => (float) 1,
						'plain'  => (float) 1,
						'*'      => (float) 1
					),
					'*'    => array(
						'*'      => (float) 1
					)
				)
			),
			array(
				'text/html; q=.5, application/json, application/xml+rss; level=1; q=.7, text/*, */*',
				array(
					'text'        => array(
						'html'       => 0.5,
						'*'          => (float) 1
					),
					'application' => array(
						'json'       => (float) 1,
						'xml+rss; level=1' => 0.7
					),
					'*'           => array(
						'*'          => (float) 1
					)
				)
			)
		);
	}

	/**
	 * Tests the `parse_accept_header` method parses the Accept: header
	 * correctly and returns expected output
	 * 
	 * @dataProvider provider_parse_accept_header
	 *
	 * @param   string    accept in
	 * @param   array     expected out
	 * @return  void
	 */
	public function test_parse_accept_header($accept, array $expected)
	{
		$this->assertSame($expected, HTTP_Header::parse_accept_header($accept));
	}

	/**
	 * Provides data for test_parse_charset_header
	 *
	 * @return  array
	 */
	public function provider_parse_charset_header()
	{
		return array(
			array(
				'utf-8, utf-10, utf-16, iso-8859-1',
				array(
					'utf-8'     => (float) 1,
					'utf-10'    => (float) 1,
					'utf-16'    => (float) 1,
					'iso-8859-1'=> (float) 1
				)
			),
			array(
				'utf-8, utf-10; q=.9, utf-16; q=.5, iso-8859-1; q=.75',
				array(
					'utf-8'     => (float) 1,
					'utf-10'    => 0.9,
					'utf-16'    => 0.5,
					'iso-8859-1'=> 0.75
				)
			),
			array(
				NULL,
				array(
					'*'         => (float) 1
				)
			)
		);
	}

	/**
	 * Tests the `parse_charset_header` method parsed the Accept-Charset header
	 * correctly
	 * 
	 * @dataProvider provider_parse_charset_header
	 *
	 * @param   string    accept 
	 * @param   array     expected 
	 * @return  void
	 */
	public function test_parse_charset_header($accept, array $expected)
	{
		$this->assertSame($expected, HTTP_Header::parse_charset_header($accept));
	}

	/**
	 * Provides data for test_parse_charset_header
	 *
	 * @return  array
	 */
	public function provider_parse_encoding_header()
	{
		return array(
			array(
				'compress, gzip, blowfish',
				array(
					'compress'  => (float) 1,
					'gzip'      => (float) 1,
					'blowfish'  => (float) 1
				)
			),
			array(
				'compress, gzip; q=0.12345, blowfish; q=1.0',
				array(
					'compress'  => (float) 1,
					'gzip'      => 0.12345,
					'blowfish'  => (float) 1
				)
			),
			array(
				NULL,
				array(
					'*'         => (float) 1
				)
			),
			array(
				'',
				array(
					'identity'  => (float) 1
				)
			)
		);
	}

	/**
	 * Tests the `parse_encoding_header` method parses the Accept-Encoding header
	 * correctly
	 * 
	 * @dataProvider provider_parse_encoding_header
	 *
	 * @param   string    accept 
	 * @param   array     expected 
	 * @return  void
	 */
	public function test_parse_encoding_header($accept, array $expected)
	{
		$this->assertSame($expected, HTTP_Header::parse_encoding_header($accept));
	}

	/**
	 * Provides data for test_parse_charset_header
	 *
	 * @return  array
	 */
	public function provider_parse_language_header()
	{
		return array(
			array(
				'en, en-us, en-gb, fr, fr-fr, es-es',
				array(
					'en' => array(
						'*'  => (float) 1,
						'us' => (float) 1,
						'gb' => (float) 1
					),
					'fr' => array(
						'*'  => (float) 1,
						'fr' => (float) 1
					),
					'es' => array(
						'es' => (float) 1
					)
				)
			),
			array(
				'en; q=.9, en-us, en-gb, fr; q=.5, fr-fr; q=0.4, es-es; q=0.9, en-gb-gb; q=.45',
				array(
					'en' => array(
						'*'  => 0.9,
						'us' => (float) 1,
						'gb' => (float) 1,
						'gb-gb' => 0.45
					),
					'fr' => array(
						'*'  => 0.5,
						'fr' => 0.4
					),
					'es' => array(
						'es' => 0.9
					)
				)
			),
			array(
				NULL,
				array(
					'*'  => array(
						'*' => (float) 1
					)
				)
			)
		);
	}

	/**
	 * Tests the `parse_language_header` method parses the Accept-Language header
	 * correctly
	 * 
	 * @dataProvider provider_parse_language_header
	 * 
	 * @param   string    accept 
	 * @param   array     expected 
	 * @return  void
	 */
	public function test_parse_language_header($accept, array $expected)
	{
		$this->assertSame($expected, HTTP_Header::parse_language_header($accept));
	}

	/**
	 * Data provider for test_offsetSet
	 *
	 * @return  array
	 */
	public function provider_offsetSet()
	{
		return array(
			array(
				array(
					'Content-Type'    => 'application/x-www-form-urlencoded',
					'Accept'          => 'text/html, text/plain; q=.1, */*',
					'Accept-Language' => 'en-gb, en-us, en; q=.1'
				),
				array(
					array(
						'Accept-Encoding',
						'compress, gzip',
						FALSE
					)
				),
				array(
					'content-type'    => 'application/x-www-form-urlencoded',
					'accept'          => 'text/html, text/plain; q=.1, */*',
					'accept-language' => 'en-gb, en-us, en; q=.1',
					'accept-encoding' => 'compress, gzip'
				)
			),
			array(
				array(
					'Content-Type'    => 'application/x-www-form-urlencoded',
					'Accept'          => 'text/html, text/plain; q=.1, */*',
					'Accept-Language' => 'en-gb, en-us, en; q=.1'
				),
				array(
					array(
						'Accept-Encoding',
						'compress, gzip',
						FALSE
					),
					array(
						'Accept-Encoding',
						'bzip',
						FALSE
					)
				),
				array(
					'content-type'    => 'application/x-www-form-urlencoded',
					'accept'          => 'text/html, text/plain; q=.1, */*',
					'accept-language' => 'en-gb, en-us, en; q=.1',
					'accept-encoding' => array(
						'compress, gzip',
						'bzip'
					)
				)
			),
			array(
				array(
					'Content-Type'    => 'application/x-www-form-urlencoded',
					'Accept'          => 'text/html, text/plain; q=.1, */*',
					'Accept-Language' => 'en-gb, en-us, en; q=.1'
				),
				array(
					array(
						'Accept-Encoding',
						'compress, gzip',
						FALSE
					),
					array(
						'Accept-Encoding',
						'bzip',
						TRUE
					),
					array(
						'Accept',
						'text/*',
						FALSE
					)
				),
				array(
					'content-type'    => 'application/x-www-form-urlencoded',
					'accept'          => array(
						'text/html, text/plain; q=.1, */*',
						'text/*'
					),
					'accept-language' => 'en-gb, en-us, en; q=.1',
					'accept-encoding' => 'bzip'
				)
			),
		);
	}

	/**
	 * Ensures that offsetSet normalizes the array keys
	 *
	 * @dataProvider provider_offsetSet
	 * 
	 * @param   array     constructor
	 * @param   array     to_set 
	 * @param   array     expected
	 * @return  void
	 */
	public function test_offsetSet(array $constructor, array $to_set, array $expected)
	{
		$http_header = new HTTP_Header($constructor);

		$reflection = new ReflectionClass($http_header);
		$method = $reflection->getMethod('offsetSet');

		foreach ($to_set as $args)
		{
			$method->invokeArgs($http_header, $args);
		}

		$this->assertSame($expected, $http_header->getArrayCopy());
	}

	/**
	 * Data provider for test_offsetGet
	 *
	 * @return  array
	 */
	public function provider_offsetGet()
	{
		return array(
			array(
				array(
					'FoO'   => 'bar',
					'START' => 'end',
					'true'  => true
				),
				'FOO',
				'bar'
			),
			array(
				array(
					'FoO'   => 'bar',
					'START' => 'end',
					'true'  => true
				),
				'true',
				true
			),
			array(
				array(
					'FoO'   => 'bar',
					'START' => 'end',
					'true'  => true
				),
				'True',
				true
			),
			array(
				array(
					'FoO'   => 'bar',
					'START' => 'end',
					'true'  => true
				),
				'Start',
				'end'
			),
			array(
				array(
					'content-type'  => 'bar',
					'Content-Type'  => 'end',
					'Accept'        => '*/*'
				),
				'content-type',
				'end'
			)
		);
	}

	/**
	 * Ensures that offsetGet normalizes the array keys
	 * 
	 * @dataProvider provider_offsetGet
	 *
	 * @param   array     start state
	 * @param   string    key to retrieve
	 * @param   mixed     expected
	 * @return  void
	 */
	public function test_offsetGet(array $state, $key, $expected)
	{
		$header = new HTTP_Header($state);

		$this->assertSame($expected, $header->offsetGet($key));
	}

	/**
	 * Data provider for test_offsetExists
	 *
	 * @return  array
	 */
	public function provider_offsetExists()
	{
		return array(
			array(
				array(
					'Accept' => 'text/html, application/json',
					'Accept-Language' => 'en, en-GB',
					'Content-Type' => 'application/x-www-form-urlencoded'
				),
				'Content-Type',
				TRUE
			),
			array(
				array(
					'Accept' => 'text/html, application/json',
					'Accept-Language' => 'en, en-GB',
					'Content-Type' => 'application/x-www-form-urlencoded'
				),
				'CONTENT-TYPE',
				TRUE
			),
			array(
				array(
					'Accept' => 'text/html, application/json',
					'Accept-Language' => 'en, en-GB',
					'Content-Type' => 'application/x-www-form-urlencoded'
				),
				'accept-language',
				TRUE
			),
			array(
				array(
					'Accept' => 'text/html, application/json',
					'Accept-Language' => 'en, en-GB',
					'Content-Type' => 'application/x-www-form-urlencoded'
				),
				'x-powered-by',
				FALSE
			)
		);
	}

	/**
	 * Ensures that offsetExists normalizes the array key
	 * 
	 * @dataProvider provider_offsetExists
	 *
	 * @param   array    state 
	 * @param   string   key 
	 * @param   boolean  expected 
	 * @return  void
	 */
	public function test_offsetExists(array $state, $key, $expected)
	{
		$header = new HTTP_Header($state);

		$this->assertSame($expected, $header->offsetExists($key));
	}

	/**
	 * Data provider for test_offsetUnset
	 *
	 * @return  array
	 */
	public function provider_offsetUnset()
	{
		return array(
			array(
				array(
					'Accept' => 'text/html, application/json',
					'Accept-Language' => 'en, en-GB',
					'Content-Type' => 'application/x-www-form-urlencoded'
				),
				'Accept-Language',
				array(
					'accept' => 'text/html, application/json',
					'content-type' => 'application/x-www-form-urlencoded'
				)
			),
			array(
				array(
					'Accept' => 'text/html, application/json',
					'Accept-Language' => 'en, en-GB',
					'Content-Type' => 'application/x-www-form-urlencoded'
				),
				'ACCEPT',
				array(
					'accept-language' => 'en, en-GB',
					'content-type' => 'application/x-www-form-urlencoded'
				)
			),
			array(
				array(
					'Accept' => 'text/html, application/json',
					'Accept-Language' => 'en, en-GB',
					'Content-Type' => 'application/x-www-form-urlencoded'
				),
				'content-type',
				array(
					'accept' => 'text/html, application/json',
					'accept-language' => 'en, en-GB',
				)
			)
		);
	}

	/**
	 * Tests that `offsetUnset` normalizes the key names properly
	 *
	 * @dataProvider provider_offsetUnset
	 *
	 * @param   array     state 
	 * @param   string    remove 
	 * @param   array     expected 
	 * @return  void
	 */
	public function test_offsetUnset(array $state, $remove, array $expected)
	{
		$header = new HTTP_Header($state);
		$header->offsetUnset($remove);

		$this->assertSame($expected, $header->getArrayCopy());
	}

	/**
	 * Provides data for test_parse_header_string
	 *
	 * @return  array
	 */
	public function provider_parse_header_string()
	{
		return array(
			array(
				array(
					"Content-Type: application/x-www-form-urlencoded\r\n",
					"Accept: text/html, text/plain; q=.5, application/json, */* \r\n",
					"X-Powered-By: Kohana Baby     \r\n"
				),
				array(
					'content-type' => 'application/x-www-form-urlencoded',
					'accept'       => 'text/html, text/plain; q=.5, application/json, */* ',
					'x-powered-by' => 'Kohana Baby     '
				)
			),
			array(
				array(
					"Content-Type: application/x-www-form-urlencoded\r\n",
					"Accept: text/html, text/plain; q=.5, application/json, */* \r\n",
					"X-Powered-By: Kohana Baby     \r\n",
					"Content-Type: application/json\r\n"
				),
				array(
					'content-type' => array(
						'application/x-www-form-urlencoded',
						'application/json'
					),
					'accept'       => 'text/html, text/plain; q=.5, application/json, */* ',
					'x-powered-by' => 'Kohana Baby     '
				)
			)
		);
	}

	/**
	 * Tests that `parse_header_string` performs as expected
	 * 
	 * @dataProvider provider_parse_header_string
	 *
	 * @param   array    headers 
	 * @param   array    expected 
	 * @return  void
	 */
	public function test_parse_header_string(array $headers, array $expected)
	{
		$http_header = new HTTP_Header(array());

		foreach ($headers as $header)
		{
			
			$this->assertEquals(strlen($header), $http_header->parse_header_string(NULL, $header));
		}

		$this->assertSame($expected, $http_header->getArrayCopy());
	}

	/**
	 * Data Provider for test_accepts_at_quality
	 *
	 * @return  array
	 */
	public function provider_accepts_at_quality()
	{
		return array(
			array(
				array(
					'Accept' => 'application/json, text/html; q=.5, text/*; q=.1, */*'
				),
				'application/json',
				FALSE,
				1.0
			),
			array(
				array(
					'Accept' => 'application/json, text/html; q=.5, text/*; q=.1, */*'
				),
				'text/html',
				FALSE,
				0.5
			),
			array(
				array(
					'Accept' => 'application/json, text/html; q=.5, text/*; q=.1, */*'
				),
				'text/plain',
				FALSE,
				0.1
			),
			array(
				array(
					'Accept' => 'application/json, text/html; q=.5, text/*; q=.1, */*'
				),
				'text/plain',
				TRUE,
				FALSE
			),
			array(
				array(
					'Accept' => 'application/json, text/html; q=.5, text/*; q=.1, */*'
				),
				'application/xml',
				FALSE,
				1.0
			),
			array(
				array(
					'Accept' => 'application/json, text/html; q=.5, text/*; q=.1, */*'
				),
				'application/xml',
				TRUE,
				FALSE
			),
			array(
				array(),
				'application/xml',
				FALSE,
				1.0
			),
			array(
				array(),
				'application/xml',
				TRUE,
				FALSE
			)
		);
	}

	/**
	 * Tests `accepts_at_quality` parsed the Accept: header as expected
	 * 
	 * @dataProvider provider_accepts_at_quality
	 *
	 * @param   array     starting state
	 * @param   string    accept header to test
	 * @param   boolean   explicitly check
	 * @param   mixed     expected output
	 * @return  void
	 */
	public function test_accepts_at_quality(array $state, $accept, $explicit, $expected)
	{
		$header = new HTTP_Header($state);

		$this->assertSame($expected, $header->accepts_at_quality($accept, $explicit));
	}

	/**
	 * Data provider for test_preferred_accept
	 *
	 * @return  array
	 */
	public function provider_preferred_accept()
	{
		return array(
			array(
				array(
					'Accept' => 'application/json, text/html; q=.5, text/*; q=.1, */*'
				),
				array(
					'text/html', 
					'application/json', 
					'text/plain'
				),
				FALSE,
				'application/json'
			),
			array(
				array(
					'Accept' => 'application/json, text/html; q=.5, text/*; q=.1, */*'
				),
				array(
					'text/plain',
					'application/xml',
					'image/jpeg'
				),
				FALSE,
				'application/xml'
			),
			array(
				array(
					'Accept' => 'application/json, text/html; q=.5, text/*; q=.1'
				),
				array(
					'text/plain',
					'application/xml',
					'image/jpeg'
				),
				FALSE,
				'text/plain'
			),
			array(
				array(
					'Accept' => 'application/json, text/html; q=.5, text/*; q=.1, */*'
				),
				array(
					'text/plain',
					'application/xml',
					'image/jpeg'
				),
				TRUE,
				FALSE
			),
			
		);
	}

	/**
	 * Tests `preferred_accept` returns the correct preferred type
	 * 
	 * @dataProvider provider_preferred_accept
	 *
	 * @param   array     state 
	 * @param   array     accepts 
	 * @param   string    explicit 
	 * @param   string    expected 
	 * @return  void
	 */
	public function test_preferred_accept(array $state, array $accepts, $explicit, $expected)
	{
		$header = new HTTP_Header($state);

		$this->assertSame($expected, $header->preferred_accept($accepts, $explicit));
	}

	public function test_accepts_charset_at_quality()
	{
		
	}

	public function test_preferred_charset()
	{
		
	}

	public function test_accepts_encoding_at_quality()
	{
		
	}

	public function test_preferred_encoding()
	{
		
	}

	public function test_accepts_language_at_quality()
	{
		
	}

	public function test_preferred_language()
	{
		
	}

	public function test_send_headers()
	{
		
	}

} // End Kohana_HTTP_HeaderTest
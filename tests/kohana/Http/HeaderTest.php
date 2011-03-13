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
	 * Provides test data for test_parse_header_values()
	 *
	 * @return  array
	 */
	public function provider_parse_header_values()
	{
		return array(
			array(
				array(
					'Date'             => 'Sun, 13 Mar 2011 16:02:19 GMT',
					'Expires'          => '-1',
					'Cache-Control'    => 'private, max-age=0',
					'Content-Type'     => 'text/html; charset=ISO-8859-1',
					'Server'           => 'Apache'
				),
				array(
					'date'             => new HTTP_Header_Value('Sun, 13 Mar 2011 16:02:19 GMT'),
					'expires'          => new HTTP_Header_Value('-1'),
					'cache-control'    => array(new HTTP_Header_Value('private'), 'max-age' => new HTTP_Header_Value('max-age=0')),
					'content-type'     => new HTTP_Header_Value('text/html; charset=ISO-8859-1'),
					'server'           => new HTTP_Header_Value('Apache')
				)
			),
			array(
				array(
					'Date'             => 'Sun, 13 Mar 2011 16:02:19 GMT',
					'Expires'          => '-1',
					'Cache-Control'    => 'private, max-age=0',
					'Content-Type'     => 'text/html; charset=ISO-8859-1',
					'Server'           => 'Apache',
					'Set-Cookie'       => array(
						'PREF=ID=dbfa7be8975d02f9:FF=0:TM=1300032139:LM=1300032139:S=ufhpKoOHVm55WY6v; expires=Tue, 12-Mar-2013 16:02:19 GMT; path=/; domain=.google.co.uk',
						'NID=44=CxNpOQbQ7fFoxIDZWRHQJaKXgZvi76heU9OfsVi75gUH2Ik0p6tAuqW9AmTwvsE1oy0XBHDgIgMq-301hvAiyHC1sgI71pKcMUEf5VCRvCwHxJH9ZR-tlJdDD-df1vnz; expires=Mon, 12-Sep-2011 16:02:19 GMT; path=/; domain=.google.co.uk; HttpOnly'
					)
				),
				array(
					'date'             => new HTTP_Header_Value('Sun, 13 Mar 2011 16:02:19 GMT'),
					'expires'          => new HTTP_Header_Value('-1'),
					'cache-control'    => array(
						new HTTP_Header_Value('private'), 
						'max-age'            => new HTTP_Header_Value('max-age=0'
					)),
					'content-type'     => new HTTP_Header_Value('text/html; charset=ISO-8859-1'),
					'server'           => new HTTP_Header_Value('Apache'),
					'set-cookie'       => array(
						new HTTP_Header_Value(array(
							'key'        => 'PREF',
							'value'      => 'ID=dbfa7be8975d02f9:FF=0:TM=1300032139:LM=1300032139:S=ufhpKoOHVm55WY6v',
							'properties' => array(
								'expires'    => 'Tue, 12-Mar-2013 16:02:19 GMT',
								'path'       => '/',
								'domain'     => '.google.co.uk'
							)
						)),
						new HTTP_Header_Value(array(
							'key'        => 'NID',
							'value'      => '44=CxNpOQbQ7fFoxIDZWRHQJaKXgZvi76heU9OfsVi75gUH2Ik0p6tAuqW9AmTwvsE1oy0XBHDgIgMq-301hvAiyHC1sgI71pKcMUEf5VCRvCwHxJH9ZR-tlJdDD-df1vnz',
							'properties' => array(
								'expires'    => 'Mon, 12-Sep-2011 16:02:19 GMT',
								'path'       => '/',
								'domain'     => '.google.co.uk',
								'HttpOnly'
							)
						))
					)
				)
			),
		);
	}

	/**
	 * Tests the parse_header_values() method.
	 * 
	 * @dataProvider provider_parse_header_values
	 *
	 * @param   array    header array to parse
	 * @param   array    expected result
	 * @return  void
	 */
	public function test_parse_header_values($header_array, $expected)
	{
		$header = HTTP_Header::parse_header_values($header_array);

		// Test the correct type is returned
		$this->assertTrue(is_array($header));

		foreach ($header as $key => $value)
		{
			if ($value instanceof HTTP_Header_Value)
			{
				$this->assertSame($value->value(), $expected[$key]->value());
				$this->assertSame($value->key(), $expected[$key]->key());
				$this->assertSame($value->properties(), $expected[$key]->properties());
			}
			elseif (is_array($value))
			{
				foreach ($value as $k => $v)
				{
					$this->assertSame($v->value(), $expected[$key][$k]->value());
					$this->assertSame($v->key(), $expected[$key][$k]->key());
					$this->assertSame($v->properties(), $expected[$key][$k]->properties());
				}
			}
			else
			{
				$this->fail('Unexpected value in HTTP_Header::parse_header_values() return value.');
			}
		}
	}

} // End Kohana_HTTP_HeaderTest
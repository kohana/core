<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Unit tests for Kohana_HTTP_Header_Value
 *
 * @group kohana
 * @group kohana.http
 * @group kohana.http.header
 * @group kohana.http.header.value
 *
 * @see CLI
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     BRMatt <matthew@sigswitch.com>
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_HTTP_Header_ValueTest extends Unittest_TestCase
{
	/**
	 * If the header value is composed of a key value pair then the parser
	 * should return an array of key => value
	 *
	 * @test
	 */
	public function test_parser_returns_array_if_header_contains_key_val()
	{
		$key_val = Kohana_HTTP_Header_Value::parse_key_value('name=kohana');
		$this->assertSame(array('name' => 'kohana'), $key_val);
	}

	/**
	 * If the parser is passed a header which doesn't contain a key then it should
	 * return the header key as a string
	 *
	 * @test
	 * @covers Kohana_HTTP_Header_Value::parse_key_value
	 */
	public function test_parser_returns_key_as_array_if_value_isnt_present()
	{
		$this->assertSame(
			array('kohana'),
			Kohana_HTTP_Header_Value::parse_key_value('kohana')
		);
	}

	/**
	 * Provides test data for test_parsing_only_splits_on_first_separator()
	 *
	 * @return array
	 */
	public function provider_test_parsing_only_splits_on_first_separator()
	{
		return array(
			array('='),
			array('+')
		);
	}

	/**
	 * If we pass in a string containing multiple occurences of the separator then
	 * the string should only be split on the first occurence
	 *
	 * @test
	 * @dataProvider provider_test_parsing_only_splits_on_first_separator
	 * @covers Kohana_HTTP_Header_Value::parse_key_value
	 */
	public function test_parsing_only_splits_on_first_separator($separator)
	{
		$key = 'some_value';
		$val = 'pie,pizza'.$separator.'cheese';

		$key_value = Kohana_HTTP_Header_Value::parse_key_value($key.$separator.$val, $separator);

		$this->assertSame(array($key => $val), $key_value);
	}

	/**
	 * Provides test data
	 *
	 * @return array
	 */
	public function provider_constructor_throws_exception_if_header_value_type_not_allowed()
	{
		return array(
			array(42),
			array(new ArrayObject),
		);
	}
	/**
	 * If the constructor is passed a value of type other than string|array then it should
	 * throw a HTTP_Exception_500
	 *
	 * @test
	 * @dataProvider provider_constructor_throws_exception_if_header_value_type_not_allowed
	 * @expectedException HTTP_Exception_500
	 * @param mixed The header value to pass to the constructor
	 */
	public function test_constructor_throws_exception_if_header_value_type_not_allowed($header)
	{
		new Kohana_HTTP_Header_Value($header);
	}

	/**
	 * When the constructor is passed an array of values it should extract the appropriate values
	 * and set them in the object's properties
	 *
	 * @test
	 * @covers Kohana_HTTP_Header_Value
	 */
	public function test_constructor_allows_and_parses_header_in_array_format()
	{
		$input = array(
			'key'        => 'name',
			'value'      => 'kohana',
			'properties' => array(
				'ttl' => '60'
			)
		);
		$header = new Kohana_HTTP_Header_Value($input);

		$this->assertSame($input['key'],        $header->key);
		$this->assertSame($input['value'],      $header->value);
		$this->assertSame($input['properties'], $header->properties);
	}

	/**
	 * Provides test data
	 *
	 * @return array
	 */
	public function provider_compiles_down_to_valid_header_when_cast_to_string()
	{
		return array(
			// Basic test, try and achieve what the doccomment says it can do
			array(
				'name=value; property; another_property=property_value',
				array(
					'key'  => 'name',
					'value' => 'value',
					'properties' => array('property', 'another_property' => 'property_value')
				),
			),
		);
	}

	/**
	 * When we cast a Kohana_HTTP_Header_Value object to a string it should generate
	 * a valid header
	 *
	 * @test
	 * @covers Kohana_HTTP_Header_Value::__toString
	 * @dataProvider provider_compiles_down_to_valid_header_when_cast_to_string
	 * @param string       Expected compiled header
	 * @param array|string The compiled header
	 */
	public function test_compiles_down_to_valid_header_when_cast_to_string($expected, $input)
	{
		$header = new Kohana_HTTP_Header_Value($input);

		$this->assertSame($expected, (string) $header);
	}
}

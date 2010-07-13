<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana Form helper
 *
 * @group kohana
 * @group kohana.form
 *
 * @package    Unittest
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_FormTest extends Kohana_Unittest_Testcase
{
	/**
	 * Defaults for this test
	 * @var array
	 */
	protected $environmentDefault = array(
		'Kohana::$base_url' => '/',
		'HTTP_HOST' => 'kohanaframework.org',
	);

	/**
	 * Provides test data for testOpen()
	 * 
	 * @return array
	 */
	function providerOpen()
	{
		return array(
			// $value, $result
			#array(NULL, NULL, '<form action="/" method="post" accept-charset="utf-8">'), // Fails because of Request::$current
			array('foo', NULL),
			array('', NULL),
			array('foo', array('method' => 'get')),
		);
	}

	/**
	 * Tests Form::open()
	 *
	 * @test
	 * @dataProvider providerOpen
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	function testOpen($action, $attributes)
	{
		$tag = Form::open($action, $attributes);

		$matcher = array(
			'tag' => 'form',
			'attributes' => array(
				'method' => 'post',
				'accept-charset' => 'utf-8',
			),
		);
		
		if($attributes !== NULL)
			$matcher['attributes'] = $attributes + $matcher['attributes'];
		
		$this->assertTag($matcher, $tag);
	}

	/**
	 * Tests Form::close()
	 *
	 * @test
	 */
	function testClose()
	{
		$this->assertSame('</form>', Form::close());
	}

	/**
	 * Provides test data for testInput()
	 * 
	 * @return array
	 */
	function providerInput()
	{
		return array(
			// $value, $result
			array('input',    'foo', 'bar', NULL, 'input'),
			array('input',    'foo',  NULL, NULL, 'input'),
			array('hidden',   'foo', 'bar', NULL, 'hidden'),
			array('password', 'foo', 'bar', NULL, 'password'),
		);
	}

	/**
	 * Tests Form::input()
	 *
	 * @test
	 * @dataProvider providerInput
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	function testInput($type, $name, $value, $attributes)
	{
		$matcher = array(
			'tag' => 'input',
			'attributes' => array('name' => $name, 'type' => $type)
		);

		// Form::input creates a text input
		if($type === 'input')
			$matcher['attributes']['type'] = 'text';

		// NULL just means no value
		if($value !== NULL)
			$matcher['attributes']['value'] = $value;

		// Add on any attributes
		if(is_array($attributes))
			$matcher['attributes'] = $attributes + $matcher['attributes'];

		$tag = Form::$type($name, $value, $attributes);

		$this->assertTag($matcher, $tag, $tag);
	}

	/**
	 * Provides test data for testFile()
	 * 
	 * @return array
	 */
	function providerFile()
	{
		return array(
			// $value, $result
			array('foo', NULL, '<input type="file" name="foo" />'),
		);
	}

	/**
	 * Tests Form::file()
	 *
	 * @test
	 * @dataProvider providerFile
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	function testFile($name, $attributes, $expected)
	{
		$this->assertSame($expected, Form::file($name, $attributes));
	}

	/**
	 * Provides test data for testCheck()
	 * 
	 * @return array
	 */
	function provider_check()
	{
		return array(
			// $value, $result
			array('checkbox', 'foo', NULL, FALSE, NULL),
			array('checkbox', 'foo', NULL, TRUE, NULL),
			array('checkbox', 'foo', 'bar', TRUE, NULL),
			
			array('radio', 'foo', NULL, FALSE, NULL),
			array('radio', 'foo', NULL, TRUE, NULL),
			array('radio', 'foo', 'bar', TRUE, NULL),
		);
	}

	/**
	 * Tests Form::check()
	 *
	 * @test
	 * @dataProvider provider_check
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	function test_check($type, $name, $value, $checked, $attributes)
	{
		$matcher = array('tag' => 'input', 'attributes' => array('name' => $name, 'type' => $type));

		if($value !== NULL)
			$matcher['attributes']['value'] = $value;

		if(is_array($attributes))
			$matcher['attributes'] = $attributes + $matcher['attributes'];

		if($checked === TRUE)
			$matcher['attributes']['checked'] = 'checked';

		$tag = Form::$type($name, $value, $checked, $attributes);
		$this->assertTag($matcher, $tag, $tag);
	}

	/**
	 * Provides test data for testText()
	 * 
	 * @return array
	 */
	function providerText()
	{
		return array(
			// $value, $result
			array('textarea', 'foo', 'bar', NULL),
			array('textarea', 'foo', 'bar', array('rows' => 20, 'cols' => 20)),
			array('button', 'foo', 'bar', NULL),
			array('label', 'foo', 'bar', NULL),
			array('label', 'foo', NULL, NULL),
		);
	}

	/**
	 * Tests Form::textarea()
	 *
	 * @test
	 * @dataProvider providerText
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	function testText($type, $name, $body, $attributes)
	{
		$matcher = array(
			'tag' => $type,
			'attributes' => array(),
			'content' => $body,
		);

		if($type !== 'label')
			$matcher['attributes'] = array('name' => $name);
		else
			$matcher['attributes'] = array('for' => $name);


		if(is_array($attributes))
			$matcher['attributes'] = $attributes + $matcher['attributes'];

		$tag = Form::$type($name, $body, $attributes);

		$this->assertTag($matcher, $tag, $tag);
	}


	/**
	 * Provides test data for testSelect()
	 * 
	 * @return array
	 */
	function providerSelect()
	{
		return array(
			// $value, $result
			array('foo', NULL, NULL, "<select name=\"foo\"></select>"),
			array('foo', array('bar' => 'bar'), NULL, "<select name=\"foo\">\n<option value=\"bar\">bar</option>\n</select>"),
			array('foo', array('bar' => 'bar'), 'bar', "<select name=\"foo\">\n<option value=\"bar\" selected=\"selected\">bar</option>\n</select>"),
			array('foo', array('bar' => array('foo' => 'bar')), NULL, "<select name=\"foo\">\n<optgroup label=\"bar\">\n<option value=\"foo\">bar</option>\n</optgroup>\n</select>"),
			array('foo', array('bar' => array('foo' => 'bar')), 'foo', "<select name=\"foo\">\n<optgroup label=\"bar\">\n<option value=\"foo\" selected=\"selected\">bar</option>\n</optgroup>\n</select>"),
			// #2286
			array('foo', array('bar' => 'bar', 'unit' => 'test', 'foo' => 'foo'), array('bar', 'foo'), "<select name=\"foo\" multiple=\"multiple\">\n<option value=\"bar\" selected=\"selected\">bar</option>\n<option value=\"unit\">test</option>\n<option value=\"foo\" selected=\"selected\">foo</option>\n</select>"),
		);
	}

	/**
	 * Tests Form::select()
	 *
	 * @test
	 * @dataProvider providerSelect
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	function testSelect($name, $options, $selected, $expected)
	{
		// Much more efficient just to assertSame() rather than assertTag() on each element
		$this->assertSame($expected, Form::select($name, $options, $selected));
	}

	/**
	 * Provides test data for testSubmit()
	 * 
	 * @return array
	 */
	function providerSubmit()
	{
		return array(
			// $value, $result
			array('foo', 'Foobar!', '<input type="submit" name="foo" value="Foobar!" />'),
		);
	}

	/**
	 * Tests Form::submit()
	 *
	 * @test
	 * @dataProvider providerSubmit
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	function testSubmit($name, $value, $expected)
	{
		$matcher = array(
			'tag' => 'input',
			'attributes' => array('name' => $name, 'type' => 'submit', 'value' => $value)
		);
			
		$this->assertTag($matcher, Form::submit($name, $value));
	}


	/**
	 * Provides test data for testImage()
	 * 
	 * @return array
	 */
	function providerImage()
	{
		return array(
			// $value, $result
			array('foo', 'bar', array('src' => 'media/img/login.png'), '<input type="image" name="foo" value="bar" src="/media/img/login.png" />'),
		);
	}

	/**
	 * Tests Form::submit()
	 *
	 * @test
	 * @dataProvider providerImage
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	function testImage($name, $value, $attributes, $expected)
	{
		$this->assertSame($expected, Form::image($name, $value, $attributes));
	}
}

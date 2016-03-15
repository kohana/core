<?php
/**
 * Tests Kohana Form helper
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.form
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_FormTest extends Unittest_TestCase {

	/**
	 * Defaults for this test
	 * @var array
	 */
	// @codingStandardsIgnoreStart
	protected $environmentDefault = [
		'Kohana::$base_url' => '/',
		'HTTP_HOST' => 'kohanaframework.org',
		'Kohana::$index_file' => '',
	];
	// @codingStandardsIgnoreEnd

	/**
	 * Provides test data for test_open()
	 *
	 * @return array
	 */
	public function provider_open()
	{
		return [
			[
				['', NULL],
				['action' => '']
			],
			[
				[NULL, NULL],
				['action' => '']
			],
			[
				['foo', NULL],
				['action' => '/foo']
			],
			[
				['foo', ['method' => 'get']],
				['action' => '/foo', 'method' => 'get']
			],
			[
				['//www.example.com/', NULL],
				['action' => '//www.example.com/']
			],
		];
	}

	/**
	 * Tests Form::open()
	 *
	 * @test
	 * @dataProvider provider_open
	 * @param boolean $input  Input for Form::open
	 * @param boolean $expected Output for Form::open
	 */
	public function test_open($input, $expected)
	{
		list($action, $attributes) = $input;

		$tag = Form::open($action, $attributes);

		$matcher = [
			'tag' => 'form',
			// Default attributes
			'attributes' => [
				'method' => 'post',
				'accept-charset' => 'utf-8',
			],
		];

		$matcher['attributes'] = $expected + $matcher['attributes'];

		$this->assertTag($matcher, $tag);
	}

	/**
	 * Tests Form::close()
	 *
	 * @test
	 */
	public function test_close()
	{
		$this->assertSame('</form>', Form::close());
	}

	/**
	 * Provides test data for test_input()
	 *
	 * @return array
	 */
	public function provider_input()
	{
		return [
			// $value, $result
			['input', 'foo', 'bar', NULL, 'input'],
			['input', 'foo',  NULL, NULL, 'input'],
			['hidden', 'foo', 'bar', NULL, 'hidden'],
			['password', 'foo', 'bar', NULL, 'password'],
		];
	}

	/**
	 * Tests Form::input()
	 *
	 * @test
	 * @dataProvider provider_input
	 * @param boolean $input  Input for Form::input
	 * @param boolean $expected Output for Form::input
	 */
	public function test_input($type, $name, $value, $attributes)
	{
		$matcher = [
			'tag' => 'input',
			'attributes' => ['name' => $name, 'type' => $type]
		];

		// Form::input creates a text input
		if ($type === 'input')
		{
			$matcher['attributes']['type'] = 'text';
		}

		// NULL just means no value
		if ($value !== NULL)
		{
			$matcher['attributes']['value'] = $value;
		}

		// Add on any attributes
		if (is_array($attributes))
		{
			$matcher['attributes'] = $attributes + $matcher['attributes'];
		}

		$tag = Form::$type($name, $value, $attributes);

		$this->assertTag($matcher, $tag, $tag);
	}

	/**
	 * Provides test data for test_file()
	 *
	 * @return array
	 */
	public function provider_file()
	{
		return [
			// $value, $result
			['foo', NULL, '<input type="file" name="foo" />'],
		];
	}

	/**
	 * Tests Form::file()
	 *
	 * @test
	 * @dataProvider provider_file
	 * @param boolean $input  Input for Form::file
	 * @param boolean $expected Output for Form::file
	 */
	public function test_file($name, $attributes, $expected)
	{
		$this->assertSame($expected, Form::file($name, $attributes));
	}

	/**
	 * Provides test data for test_check()
	 *
	 * @return array
	 */
	public function provider_check()
	{
		return [
			// $value, $result
			['checkbox', 'foo', NULL, FALSE, NULL],
			['checkbox', 'foo', NULL, TRUE, NULL],
			['checkbox', 'foo', 'bar', TRUE, NULL],

			['radio', 'foo', NULL, FALSE, NULL],
			['radio', 'foo', NULL, TRUE, NULL],
			['radio', 'foo', 'bar', TRUE, NULL],
		];
	}

	/**
	 * Tests Form::check()
	 *
	 * @test
	 * @dataProvider provider_check
	 * @param boolean $input  Input for Form::check
	 * @param boolean $expected Output for Form::check
	 */
	public function test_check($type, $name, $value, $checked, $attributes)
	{
		$matcher = [
			'tag' => 'input',
			'attributes' => [
				'name' => $name,
				'type' => $type
			]
		];

		if ($value !== NULL)
		{
			$matcher['attributes']['value'] = $value;
		}

		if (is_array($attributes))
		{
			$matcher['attributes'] = $attributes + $matcher['attributes'];
		}

		if ($checked === TRUE)
		{
			$matcher['attributes']['checked'] = 'checked';
		}

		$tag = Form::$type($name, $value, $checked, $attributes);
		$this->assertTag($matcher, $tag, $tag);
	}

	/**
	 * Provides test data for test_text()
	 *
	 * @return array
	 */
	public function provider_text()
	{
		return [
			// $value, $result
			['textarea', 'foo', 'bar', NULL],
			['textarea', 'foo', 'bar', ['rows' => 20, 'cols' => 20]],
			['button', 'foo', 'bar', NULL],
			['label', 'foo', 'bar', NULL],
			['label', 'foo', NULL, NULL],
		];
	}

	/**
	 * Tests Form::textarea()
	 *
	 * @test
	 * @dataProvider provider_text
	 * @param boolean $input  Input for Form::textarea
	 * @param boolean $expected Output for Form::textarea
	 */
	public function test_text($type, $name, $body, $attributes)
	{
		$matcher = [
			'tag' => $type,
			'attributes' => [],
			'content' => $body,
		];

		if ($type !== 'label')
		{
			$matcher['attributes'] = ['name' => $name];
		}
		else
		{
			$matcher['attributes'] = ['for' => $name];
		}


		if (is_array($attributes))
		{
			$matcher['attributes'] = $attributes + $matcher['attributes'];
		}

		$tag = Form::$type($name, $body, $attributes);

		$this->assertTag($matcher, $tag, $tag);
	}


	/**
	 * Provides test data for test_select()
	 *
	 * @return array
	 */
	public function provider_select()
	{
		return [
			// $value, $result
			['foo', NULL, NULL, "<select name=\"foo\"></select>"],
			['foo', ['bar' => 'bar'], NULL, "<select name=\"foo\">\n<option value=\"bar\">bar</option>\n</select>"],
			['foo', ['bar' => 'bar'], 'bar', "<select name=\"foo\">\n<option value=\"bar\" selected=\"selected\">bar</option>\n</select>"],
			['foo', ['bar' => ['foo' => 'bar']], NULL, "<select name=\"foo\">\n<optgroup label=\"bar\">\n<option value=\"foo\">bar</option>\n</optgroup>\n</select>"],
			['foo', ['bar' => ['foo' => 'bar']], 'foo', "<select name=\"foo\">\n<optgroup label=\"bar\">\n<option value=\"foo\" selected=\"selected\">bar</option>\n</optgroup>\n</select>"],
			// #2286
			['foo', ['bar' => 'bar', 'unit' => 'test', 'foo' => 'foo'], ['bar', 'foo'], "<select name=\"foo\" multiple=\"multiple\">\n<option value=\"bar\" selected=\"selected\">bar</option>\n<option value=\"unit\">test</option>\n<option value=\"foo\" selected=\"selected\">foo</option>\n</select>"],
		];
	}

	/**
	 * Tests Form::select()
	 *
	 * @test
	 * @dataProvider provider_select
	 * @param boolean $input  Input for Form::select
	 * @param boolean $expected Output for Form::select
	 */
	public function test_select($name, $options, $selected, $expected)
	{
		// Much more efficient just to assertSame() rather than assertTag() on each element
		$this->assertSame($expected, Form::select($name, $options, $selected));
	}

	/**
	 * Provides test data for test_submit()
	 *
	 * @return array
	 */
	public function provider_submit()
	{
		return [
			// $value, $result
			['foo', 'Foobar!', '<input type="submit" name="foo" value="Foobar!" />'],
		];
	}

	/**
	 * Tests Form::submit()
	 *
	 * @test
	 * @dataProvider provider_submit
	 * @param boolean $input  Input for Form::submit
	 * @param boolean $expected Output for Form::submit
	 */
	public function test_submit($name, $value, $expected)
	{
		$matcher = [
			'tag' => 'input',
			'attributes' => ['name' => $name, 'type' => 'submit', 'value' => $value]
		];

		$this->assertTag($matcher, Form::submit($name, $value));
	}


	/**
	 * Provides test data for test_image()
	 *
	 * @return array
	 */
	public function provider_image()
	{
		return [
			// $value, $result
			['foo', 'bar', ['src' => 'media/img/login.png'], '<input type="image" name="foo" value="bar" src="/media/img/login.png" />'],
			['foo', 'bar', ['src' => 'http://kohanaframework.org/media/img/login.png'], '<input type="image" name="foo" value="bar" src="http://kohanaframework.org/media/img/login.png" />'],
			['foo', 'bar', ['src' => '//kohanaframework.org/media/img/login.png'], '<input type="image" name="foo" value="bar" src="//kohanaframework.org/media/img/login.png" />'],
		];
	}

	/**
	 * Tests Form::image()
	 *
	 * @test
	 * @dataProvider provider_image
	 * @param boolean $name         Input for Form::image
	 * @param boolean $value        Input for Form::image
	 * @param boolean $attributes  Input for Form::image
	 * @param boolean $expected    Output for Form::image
	 */
	public function test_image($name, $value, $attributes, $expected)
	{
		$this->assertSame($expected, Form::image($name, $value, $attributes));
	}

	/**
	 * Provides test data for test_label()
	 *
	 * @return array
	 */
	function provider_label()
	{
		return [
			// $value, $result
			// Single for provided
			['email', NULL, NULL, '<label for="email">Email</label>'],
			['email_address', NULL, NULL, '<label for="email_address">Email Address</label>'],
			['email-address', NULL, NULL, '<label for="email-address">Email Address</label>'],
			// For and text values provided
			['name', 'First name', NULL, '<label for="name">First name</label>'],
			// with attributes
			['lastname', 'Last name', ['class' => 'text'], '<label class="text" for="lastname">Last name</label>'],
			['lastname', 'Last name', ['class' => 'text', 'id'=>'txt_lastname'], '<label id="txt_lastname" class="text" for="lastname">Last name</label>'],
		];
	}

	/**
	 * Tests Form::label()
	 *
	 * @test
	 * @dataProvider provider_label
	 * @param boolean $for         Input for Form::label
	 * @param boolean $text        Input for Form::label
	 * @param boolean $attributes  Input for Form::label
	 * @param boolean $expected    Output for Form::label
	 */
	function test_label($for, $text, $attributes, $expected)
	{
		$this->assertSame($expected, Form::label($for, $text, $attributes));
	}

}

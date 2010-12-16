<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests the Validate lib that's shipped with Kohana
 *
 * @group kohana
 * @group kohana.validation
 *
 * @package    Unittest
 * @author     Kohana Team
 * @author     BRMatt <matthew@sigswitch.com>
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
Class Kohana_ValidateTest extends Kohana_Unittest_TestCase
{

	/**
	 * Provides test data for test_alpha()
	 * @return array
	 */
	public function provider_alpha()
	{
		return array(
			array('asdavafaiwnoabwiubafpowf', TRUE),
			array('!aidhfawiodb', FALSE),
			array('51535oniubawdawd78', FALSE),
			array('!"£$(G$W£(HFW£F(HQ)"n', FALSE),
			// UTF-8 tests
			array('あいうえお', TRUE, TRUE),
			array('¥', FALSE, TRUE)
		);
	}
	
	/**
	 * Tests Validate::alpha()
	 * 
	 * Checks whether a string consists of alphabetical characters only.
	 *
	 * @test
	 * @group kohana.validation.helpers
	 * @dataProvider provider_alpha
	 * @param string  $string
	 * @param boolean $expected
	 */
	public function test_alpha($string, $expected, $utf8 = FALSE)
	{
		$this->assertSame(
			$expected,
			Validate::alpha($string, $utf8)
		);
	}

	/*
	 * Provides test data for test_alpha_numeric
	 */
	public function provide_alpha_numeric()
	{
		return array(
			array('abcd1234',  TRUE),
		    array('abcd',      TRUE),
		    array('1234',      TRUE),
		    array('abc123&^/-', FALSE),
				// UTF-8 tests
				array('あいうえお', TRUE, TRUE),
				array('零一二三四五', TRUE, TRUE),
				array('あい四五£^£^', FALSE, TRUE),
		);
	}

	/**
	 * Tests Validate::alpha_numberic()
	 *
	 * Checks whether a string consists of alphabetical characters and numbers only.
	 *
	 * @test
	 * @group kohana.validation.helpers
	 * @dataProvider provide_alpha_numeric
	 * @param string  $input     The string to test
	 * @param boolean $expected  Is $input valid
	 */
	public function test_alpha_numeric($input, $expected, $utf8 = FALSE)
	{
		$this->assertSame(
			$expected,
			Validate::alpha_numeric($input, $utf8)
		);
	}

	/**
	 * Provides test data for test_alpha_dash
	 */
	public function provider_alpha_dash()
	{
		return array(
			array('abcdef',     TRUE),
		    array('12345',      TRUE),
		    array('abcd1234',   TRUE),
		    array('abcd1234-',  TRUE),
		    array('abc123&^/-', FALSE)
		);
	}

	/**
	 * Tests Validate::alpha_dash()
	 *
	 * Checks whether a string consists of alphabetical characters, numbers, underscores and dashes only.
	 *
	 * @test
	 * @group kohana.validation.helpers
	 * @dataProvider provider_alpha_dash
	 * @param string  $input          The string to test
	 * @param boolean $contains_utf8  Does the string contain utf8 specific characters
	 * @param boolean $expected       Is $input valid?
	 */
	public function test_alpha_dash($input, $expected, $contains_utf8 = FALSE)
	{
		if( ! $contains_utf8)
		{
			$this->assertSame(
				$expected,
				Validate::alpha_dash($input)
			);
		}		

		$this->assertSame(
			$expected,
			Validate::alpha_dash($input, TRUE)
		);
	}

	/**
	 * DataProvider for the valid::date() test
	 */
	public function provider_date()
	{
		return array(
			array('now',TRUE),
			array('10 September 2010',TRUE),
			array('+1 day',TRUE),
			array('+1 week',TRUE),
			array('+1 week 2 days 4 hours 2 seconds',TRUE),
			array('next Thursday',TRUE),
			array('last Monday',TRUE),

			array('blarg',FALSE),
			array('in the year 2000',FALSE),
			array('324824',FALSE),
		);
	}

	/**
	 * Tests Validate::date()
	 *
	 * @test
	 * @group kohana.validation.helpers
	 * @dataProvider provider_date
	 * @param string  $date  The date to validate
	 * @param integer $expected
	 */
	public function test_date($date, $expected)
	{
		$this->assertSame(
			$expected,
			Validate::date($date, $expected)
		);
	}

	/**
	 * DataProvider for the valid::decimal() test
	 */
	public function provider_decimal()
	{
		return array(
			array('45.1664',  3,    NULL, FALSE),
			array('45.1664',  4,    NULL, TRUE),
			array('45.1664',  4,    2,    TRUE),
		);
	}

	/**
	 * Tests Validate::decimal()
	 *
	 * @test
	 * @group kohana.validation.helpers
	 * @dataProvider provider_decimal
	 * @param string  $decimal  The decimal to validate
	 * @param integer $places   The number of places to check to
	 * @param integer $digits   The number of digits preceding the point to check
	 * @param boolean $expected Whether $decimal conforms to $places AND $digits
	 */
	public function test_decimal($decimal, $places, $digits, $expected)
	{
		$this->assertSame(
			$expected,
			Validate::decimal($decimal, $places, $digits),
			'Decimal: "'.$decimal.'" to '.$places.' places and '.$digits.' digits (preceeding period)'
		);
	}

	/**
	 * Provides test data for test_digit
	 * @return array
	 */
	public function provider_digit()
	{
		return array(
			array('12345',    TRUE),
			array('10.5',     FALSE),
			array('abcde',    FALSE),
			array('abcd1234', FALSE),
			array('-5',       FALSE),
			array(-5,         FALSE),
		);
	}

	/**
	 * Tests Validate::digit()
	 *
	 * @test
	 * @group kohana.validation.helpers
	 * @dataProvider provider_digit
	 * @param mixed   $input     Input to validate
	 * @param boolean $expected  Is $input valid
	 */
	public function test_digit($input, $expected, $contains_utf8 = FALSE)
	{
		if( ! $contains_utf8)
		{
			$this->assertSame(
				$expected,
				Validate::digit($input)
			);
		}

		$this->assertSame(
			$expected,
			Validate::digit($input, TRUE)
		);

	}

	/**
	 * DataProvider for the valid::color() test
	 */
	public function provider_color()
	{
		return array(
			array('#000000', TRUE),
			array('#GGGGGG', FALSE),
			array('#AbCdEf', TRUE),
			array('#000', TRUE),
			array('#abc', TRUE),
			array('#DEF', TRUE),
			array('000000', TRUE),
			array('GGGGGG', FALSE),
			array('AbCdEf', TRUE),
			array('000', TRUE),
			array('DEF', TRUE)
		);
	}

	/**
	 * Tests Validate::color()
	 *
	 * @test
	 * @group kohana.validation.helpers
	 * @dataProvider provider_color
	 * @param string  $color     The color to test
	 * @param boolean $expected  Is $color valid
	 */
	public function test_color($color, $expected)
	{
		$this->assertSame(
			$expected,
			Validate::color($color)
		);
	}

	/**
	 * Provides test data for test_credit_card()
	 */
	public function provider_credit_card()
	{
		return array(
			array('4222222222222',    'visa',       TRUE),
		    array('4012888888881881', 'visa',       TRUE),
		    array('4012888888881881', NULL,         TRUE),
		    array('4012888888881881', array('mastercard', 'visa'), TRUE),
		    array('4012888888881881', array('discover', 'mastercard'), FALSE),
		    array('4012888888881881', 'mastercard', FALSE),
		    array('5105105105105100', 'mastercard', TRUE),
		    array('6011111111111117', 'discover',   TRUE),
		    array('6011111111111117', 'visa',       FALSE)
		);
	}

	/**
	 * Tests Validate::credit_card()
	 *
	 * @test
	 * @covers Validate::credit_card
	 * @group kohana.validation.helpers
	 * @dataProvider  provider_credit_card()
	 * @param string  $number   Credit card number
	 * @param string  $type	    Credit card type
	 * @param boolean $expected
	 */
	public function test_credit_card($number, $type, $expected)
	{
		$this->assertSame(
			$expected,
			Validate::credit_card($number, $type)
		);
	}

	/**
	 * Provides test data for test_credit_card()
	 */
	public function provider_luhn()
	{
		return array(
			array('4222222222222', TRUE),
			array('4012888888881881', TRUE),
			array('5105105105105100', TRUE),
			array('6011111111111117', TRUE),
			array('60111111111111.7', FALSE),
			array('6011111111111117X', FALSE),
			array('6011111111111117 ', FALSE),
			array('WORD ', FALSE),
		);
	}

	/**
	 * Tests Validate::luhn()
	 *
	 * @test
	 * @covers Validate::luhn
	 * @group kohana.validation.helpers
	 * @dataProvider  provider_luhn()
	 * @param string  $number   Credit card number
	 * @param boolean $expected
	 */
	public function test_luhn($number, $expected)
	{
		$this->assertSame(
			$expected,
			Validate::luhn($number)
		);
	}

	/**
	 * Provides test data for test_email()
	 *
	 * @return array
	 */
	public function provider_email()
	{
		return array(
			array('foo', TRUE,  FALSE),
			array('foo', FALSE, FALSE),

			// RFC is less strict than the normal regex, presumably to allow
			//  admin@localhost, therefore we IGNORE IT!!!
			array('foo@bar', FALSE, FALSE),
			array('foo@bar.com', FALSE, TRUE),
			array('foo@bar.sub.com', FALSE, TRUE),
			array('foo+asd@bar.sub.com', FALSE, TRUE),
			array('foo.asd@bar.sub.com', FALSE, TRUE),
		);
	}

	/**
	 * Tests Validate::email()
	 *
	 * Check an email address for correct format.
	 *
	 * @test
	 * @group kohana.validation.helpers
	 * @dataProvider provider_email
	 * @param string  $email   Address to check
	 * @param boolean $strict  Use strict settings
	 * @param boolean $correct Is $email address valid?
	 */
	public function test_email($email, $strict, $correct)
	{
		$this->assertSame(
			$correct,
			Validate::email($email, $strict)
		);
	}

	/**
	 * Returns test data for test_email_domain()
	 *
	 * @return array
	 */
	public function provider_email_domain()
	{
		return array(
			array('google.com', TRUE),
			// Don't anybody dare register this...
			array('DAWOMAWIDAIWNDAIWNHDAWIHDAIWHDAIWOHDAIOHDAIWHD.com', FALSE)
		);
	}

	/**
	 * Tests Validate::email_domain()
	 *
	 * Validate the domain of an email address by checking if the domain has a
	 * valid MX record.
	 *
	 * Test skips on windows
	 *
	 * @test
	 * @group kohana.validation.helpers
	 * @dataProvider provider_email_domain
	 * @param string  $email   Email domain to check
	 * @param boolean $correct Is it correct?
	 */
	public function test_email_domain($email, $correct)
	{
		if ( ! $this->hasInternet())
			$this->markTestSkipped('An internet connection is required for this test');

		if( ! Kohana::$is_windows OR version_compare(PHP_VERSION, '5.3.0', '>='))
		{
			$this->assertSame(
				$correct,
				Validate::email_domain($email)
			);
		}
		else
		{
			$this->markTestSkipped('checkdnsrr() was not added on windows until PHP 5.3');
		}
	}

	/**
	 * Provides data for test_exact_length()
	 *
	 * @return array
	 */
	public function provider_exact_length()
	{
		return array(
			array('somestring', 10, TRUE),
			array('anotherstring', 13, TRUE),
		);
	}

	/**
	 *
	 * Tests Validate::exact_length()
	 *
	 * Checks that a field is exactly the right length.
	 *
	 * @test
	 * @group kohana.validation.helpers
	 * @dataProvider provider_exact_length
	 * @param string  $string  The string to length check
	 * @param integer $length  The length of the string
	 * @param boolean $correct Is $length the actual length of the string?
	 * @return bool
	 */
	public function test_exact_length($string, $length, $correct)
	{
		return $this->assertSame(
			$correct,
			Validate::exact_length($string, $length),
			'Reported string length is not correct'
		);
	}

	/**
	 * Provides data for test_equals()
	 *
	 * @return array
	 */
	public function provider_equals()
	{
		return array(
			array('foo', 'foo', TRUE),
			array('1', '1', TRUE),
			array(1, '1', FALSE),
			array('011', 011, FALSE),
		);
	}

	/**
	 * Tests Validate::equals()
	 *
	 * @test
	 * @group kohana.validation.helpers
	 * @dataProvider provider_equals
	 * @param   string   $string    value to check
	 * @param   integer  $required  required value
	 * @param   boolean  $correct   is $string the same as $required?
	 * @return  boolean
	 */
	public function test_equals($string, $required, $correct)
	{
		return $this->assertSame(
			$correct,
			Validate::equals($string, $required),
			'Values are not equal'
		);
	}

	/**
	 * Tests Validate::factory()
	 *
	 * Makes sure that the factory method returns an instance of Validate lib
	 * and that it uses the variables passed
	 *
	 * @test
	 */
	public function test_factory_method_returns_instance_with_values()
	{
		$values = array(
			'this'			=> 'something else',
			'writing tests' => 'sucks',
			'why the hell'	=> 'amIDoingThis',
		);

		$instance = Validate::factory($values);

		$this->assertTrue($instance instanceof Validate);

		$this->assertSame(
			$values,
			$instance->as_array()
		);
	}

	/**
	 * DataProvider for the valid::ip() test
	 * @return array
	 */
	public function provider_ip()
	{
		return array(
			array('75.125.175.50',   FALSE, TRUE),
		    array('127.0.0.1',       FALSE, TRUE),
		    array('256.257.258.259', FALSE, FALSE),
		    array('255.255.255.255', FALSE, FALSE),
		    array('192.168.0.1',     FALSE, FALSE),
		    array('192.168.0.1',     TRUE,  TRUE)
		);
	}

	/**
	 * Tests Validate::ip()
	 *
	 * @test
	 * @group kohana.validation.helpers
	 * @dataProvider  provider_ip
	 * @param string  $input_ip
	 * @param boolean $allow_private
	 * @param boolean $expected_result
	 */
	public function test_ip($input_ip, $allow_private, $expected_result)
	{
		$this->assertEquals(
			$expected_result,
			Validate::ip($input_ip, $allow_private)
		);
	}

	/**
	 * Returns test data for test_max_length()
	 *
	 * @return array
	 */
	public function provider_max_length()
	{
		return array(
			// Border line
			array('some', 4, TRUE),
			// Exceeds
			array('KOHANARULLLES', 2, FALSE),
			// Under
			array('CakeSucks', 10, TRUE)
		);
	}

	/**
	 * Tests Validate::max_length()
	 *
	 * Checks that a field is short enough.
	 * 
	 * @test
	 * @group kohana.validation.helpers
	 * @dataProvider provider_max_length
	 * @param string  $string    String to test
	 * @param integer $maxlength Max length for this string
	 * @param boolean $correct   Is $string <= $maxlength
	 */
	public function test_max_length($string, $maxlength, $correct)
	{
		 $this->assertSame(
			$correct,
			Validate::max_length($string, $maxlength)
		);
	}

	/**
	 * Returns test data for test_min_length()
	 *
	 * @return array
	 */
	public function provider_min_length()
	{
		return array(
			array('This is obviously long enough', 10, TRUE),
			array('This is not', 101, FALSE),
			array('This is on the borderline', 25, TRUE)
		);
	}

	/**
	 * Tests Validate::min_length()
	 *
	 * Checks that a field is long enough.
	 *
	 * @test
	 * @group kohana.validation.helpers
	 * @dataProvider provider_min_length
	 * @param string  $string     String to compare
	 * @param integer $minlength  The minimum allowed length
	 * @param boolean $correct    Is $string 's length >= $minlength
	 */
	public function test_min_length($string, $minlength, $correct)
	{
		$this->assertSame(
			$correct,
			Validate::min_length($string, $minlength)
		);
	}

	/**
	 * Returns test data for test_not_empty()
	 *
	 * @return array
	 */
	public function provider_not_empty()
	{
		// Create a blank arrayObject
		$ao = new ArrayObject;

		// arrayObject with value
		$ao1 = new ArrayObject;
		$ao1['test'] = 'value';
		
		return array(
			array(array(),      FALSE),
			array(NULL,         FALSE),
			array('',           FALSE),
			array($ao,          FALSE),
			array($ao1,         TRUE),
			array(array(NULL),  TRUE),
			array(0,            TRUE),
			array('0',          TRUE),
			array('Something',  TRUE),
		);
	}

	/**
	 * Tests Validate::not_empty()
	 *
	 * Checks if a field is not empty.
	 *
	 * @test
	 * @group kohana.validation.helpers
	 * @dataProvider provider_not_empty
	 * @param mixed   $value  Value to check
	 * @param boolean $empty  Is the value really empty?
	 */
	public function test_not_empty($value, $empty)
	{
		return $this->assertSame(
			$empty,
			Validate::not_empty($value)
		);
	}

	/**
	 * DataProvider for the Validate::numeric() test
	 */
	public function provider_numeric()
	{
		return array(
			array(12345,   TRUE),
			array(123.45,  TRUE),
			array('12345', TRUE),
			array('10.5',  TRUE),
			array('-10.5', TRUE),
			array('10.5a', FALSE),
			// @issue 3240
			array(.4,      TRUE),
			array(-.4,     TRUE),
			array(4.,      TRUE),
			array(-4.,     TRUE),
			array('.5',    TRUE),
			array('-.5',   TRUE),
			array('5.',    TRUE),
			array('-5.',   TRUE),
			array('.',     FALSE),
			array('1.2.3', FALSE),
		);
	}

	/**
	 * Tests Validate::numeric()
	 *
	 * @test
	 * @group kohana.validation.helpers
	 * @dataProvider provider_numeric
	 * @param string  $input     Input to test
	 * @param boolean $expected  Whether or not $input is numeric
	 */
	public function test_numeric($input, $expected)
	{
		$this->assertSame(
			$expected,
			Validate::numeric($input)
		);
	}

	/**
	 * Provides test data for test_phone()
	 * @return array
	 */
	public function provider_phone()
	{
		return array(
			array('0163634840',       NULL, TRUE),
		    array('+27173634840',     NULL, TRUE),
		    array('123578',           NULL, FALSE),
			// Some uk numbers
			array('01234456778',      NULL, TRUE),
			array('+0441234456778',   NULL, FALSE),
			// Google UK case you're interested
			array('+44 20-7031-3000', array(12), TRUE),
			// BT Corporate
			array('020 7356 5000',	  NULL, TRUE),
		);
	}

	/**
	 * Tests Validate::phone()
	 *
	 * @test
	 * @group kohana.validation.helpers
	 * @dataProvider  provider_phone
	 * @param string  $phone     Phone number to test
	 * @param boolean $expected  Is $phone valid
	 */
	public function test_phone($phone, $lengths, $expected)
	{
		$this->assertSame(
			$expected,
			Validate::phone($phone, $lengths)
		);
	}

	/**
	 * DataProvider for the valid::regex() test
	 */
	public function provider_regex()
	{
		return array(
			array('hello world', '/[a-zA-Z\s]++/', TRUE),
			array('123456789', '/[0-9]++/', TRUE),
			array('£$%£%', '/[abc]/', FALSE),
			array('Good evening',  '/hello/',  FALSE),
		);
	}

	/**
	 * Tests Validate::range()
	 *
	 * Tests if a number is within a range.
	 *
	 * @test
	 * @group kohana.validation.helpers
	 * @dataProvider provider_regex
	 * @param string Value to test against
	 * @param string Valid pcre regular expression
	 * @param bool Does the value match the expression?
	 */
	public function test_regex($value, $regex, $expected)
	{
		$this->AssertSame(
			$expected,
			Validate::regex($value, $regex)
		);
	}

	/**
	 * DataProvider for the valid::range() test
	 */
	public function provider_range()
	{
		return array(
			array(1,  0,  2, TRUE),
			array(-1, -5, 0, TRUE),
			array(-1, 0,  1, FALSE),
			array(1,  0,  0, FALSE),
			array(2147483647, 0, 200000000000000, TRUE),
			array(-2147483647, -2147483655, 2147483645, TRUE)
		);
	}

	/**
	 * Tests Validate::range()
	 *
	 * Tests if a number is within a range.
	 *
	 * @test
	 * @group kohana.validation.helpers
	 * @dataProvider provider_range
	 * @param integer $number    Number to test
	 * @param integer $min       Lower bound
	 * @param integer $max       Upper bound
	 * @param boolean $expected  Is Number within the bounds of $min && $max
	 */
	public function test_range($number, $min, $max, $expected)
	{
		$this->AssertSame(
			$expected,
			Validate::range($number, $min, $max)
		);
	}

	/**
	 * Provides test data for test_url()
	 *
	 * @return array
	 */
	public function provider_url()
	{
		$data = array(
			array('http://google.com', TRUE),
			array('http://google.com/', TRUE),
			array('http://google.com/?q=abc', TRUE),
			array('http://google.com/#hash', TRUE),
			array('http://localhost', TRUE),
			array('http://hello-world.pl', TRUE),
			array('http://hello--world.pl', TRUE),
			array('http://h.e.l.l.0.pl', TRUE),
			array('http://server.tld/get/info', TRUE),
			array('http://127.0.0.1', TRUE),
			array('http://127.0.0.1:80', TRUE),
			array('http://user@127.0.0.1', TRUE),
			array('http://user:pass@127.0.0.1', TRUE),
			array('ftp://my.server.com', TRUE),
			array('rss+xml://rss.example.com', TRUE),

			array('http://google.2com', FALSE),
			array('http://google.com?q=abc', FALSE),
			array('http://google.com#hash', FALSE),
			array('http://hello-.pl', FALSE),
			array('http://hel.-lo.world.pl', FALSE),
			array('http://ww£.google.com', FALSE),
			array('http://127.0.0.1234', FALSE),
			array('http://127.0.0.1.1', FALSE),
			array('http://user:@127.0.0.1', FALSE),
			array("http://finalnewline.com\n", FALSE),
		);

		$data[] = array('http://'.str_repeat('123456789.', 25).'com/', TRUE); // 253 chars
		$data[] = array('http://'.str_repeat('123456789.', 25).'info/', FALSE); // 254 chars

		return $data;
	}

	/**
	 * Tests Validate::url()
	 *
	 * @test
	 * @group kohana.validation.helpers
	 * @dataProvider provider_url
	 * @param string  $url       The url to test
	 * @param boolean $expected  Is it valid?
	 */
	public function test_url($url, $expected)
	{
		$this->assertSame(
			$expected,
			Validate::url($url)
		);
	}

	/**
	 * When we copy() a validate object, we should have a new validate object
	 * with the exact same attributes, apart from the data, which should be the 
	 * same as the array we pass to copy()
	 *
	 * @test
	 * @covers Validate::copy
	 */
	public function test_copy_copies_all_attributes_except_data()
	{
		$validate = new Validate(array('foo' => 'bar', 'fud' => 'fear, uncertainty, doubt', 'num' => 9));

		$validate->rule('num', 'is_int')->rule('foo', 'is_string');

		$validate->callback('foo', 'heh', array('ding'));

		$copy_data = array('foo' => 'no', 'fud' => 'maybe', 'num' => 42);

		$copy = $validate->copy($copy_data);

		$this->assertNotSame($validate, $copy);
		
		foreach(array('_filters', '_rules', '_callbacks', '_labels', '_empty_rules', '_errors') as $attribute)
		{
			// This is just an easy way to check that the attributes are identical
			// Without hardcoding the expected values
			$this->assertAttributeSame(
				self::readAttribute($validate, $attribute),
				$attribute,
				$copy
			);
		}

		$this->assertSame($copy_data, $copy->as_array());
	}

	/**
	 * By default there should be no callbacks registered with validate
	 *
	 * @test
	 */
	public function test_initially_there_are_no_callbacks()
	{
		$validate = new Validate(array());

		$this->assertAttributeSame(array(), '_callbacks', $validate);
	}

	/**
	 * This is just a quick check that callback() returns a reference to $this
	 *
	 * @test
	 * @covers Validate::callback
	 */
	public function test_callback_returns_chainable_this()
	{
		$validate = new Validate(array());

		$this->assertSame($validate, $validate->callback('field', 'something'));
	}

	/**
	 * Check that callback() is storign callbacks in the correct manner
	 *
	 * @test
	 * @covers Validate::callback
	 */
	public function test_callback_stores_callback()
	{
		$validate = new Validate(array('id' => 355));

		$validate->callback('id', 'misc_callback');

		$this->assertAttributeSame(
			array(
				'id' => array(array('misc_callback', array())),
			), 
			'_callbacks',
			$validate
		);
	}

	/**
	 * Calling Validate::callbacks() should store multiple callbacks for the specified field
	 *
	 * @test
	 * @covers Validate::callbacks
	 * @covers Validate::callback
	 */
	public function test_callbacks_stores_multiple_callbacks()
	{
		$validate = new Validate(array('year' => 1999));

		$validate->callbacks('year', array('misc_callback', 'another_callback'));

		$this->assertAttributeSame(
			array(
				'year' => array( 
					array('misc_callback', array()),
					array('another_callback', array()),
				),
			), 
			'_callbacks', 
			$validate
		);
	}

	/**
	 * When the validate object is initially created there should be no labels
	 * specified
	 *
	 * @test
	 */
	public function test_initially_there_are_no_labels()
	{
		$validate = new Validate(array());

		$this->assertAttributeSame(array(), '_labels', $validate);
	}

	/**
	 * Adding a label to a field should set it in the labels array
	 * If the label already exists it should overwrite it
	 *
	 * In both cases thefunction should return a reference to $this
	 *
	 * @test
	 * @covers Validate::label
	 */
	public function test_label_adds_and_overwrites_label_and_returns_this()
	{
		$validate = new Validate(array());

		$this->assertSame($validate, $validate->label('email', 'Email Address'));

		$this->assertAttributeSame(array('email' => 'Email Address'), '_labels', $validate);

		$this->assertSame($validate, $validate->label('email', 'Your Email'));

		$validate->label('name', 'Your Name');

		$this->assertAttributeSame(
			array('email' => 'Your Email', 'name' => 'Your Name'),
			'_labels',
			$validate
		);
	}

	/**
	 * Using labels() we should be able to add / overwrite multiple labels
	 *
	 * The function should also return $this for chaining purposes
	 *
	 * @test
	 * @covers Validate::labels
	 */
	public function test_labels_adds_and_overwrites_multiple_labels_and_returns_this()
	{
		$validate = new Validate(array());
		$initial_data = array('kung fu' => 'fighting', 'fast' => 'cheetah');

		$this->assertSame($validate, $validate->labels($initial_data));

		$this->assertAttributeSame($initial_data, '_labels', $validate);

		$this->assertSame($validate, $validate->labels(array('fast' => 'lightning')));

		$this->assertAttributeSame(
			array('fast' => 'lightning', 'kung fu' => 'fighting'),
			'_labels',
			$validate
		);
	}

	/**
	 * We should be able to add a filter to the queue by calling filter()
	 *
	 * @test
	 * @covers Validate::filter
	 */
	public function test_filter_adds_a_filter_and_returns_this()
	{
		$validate = new Validate(array());

		$this->assertSame($validate, $validate->filter('name', 'trim'));

		$this->assertAttributeSame(
			array('name' => array('trim' => array())),
			'_filters',
			$validate
		);
	}

	/**
	 * filters() should be able to add multiple filters for a field and return
	 * $this when done
	 *
	 * @test
	 * @covers Validate::filters
	 */
	public function test_filters_adds_multiple_filters_and_returns_this()
	{
		$validate = new Validate(array());

		$this->assertSame(
			$validate,
			$validate->filters('id', array('trim' => NULL, 'some_func' => array('yes', 'no')))
		);

		$this->assertAttributeSame(
			array('id' => array('trim' => array(), 'some_func' => array('yes', 'no'))),
			'_filters',
			$validate
		);
	}

	/**
	 * Provides test data for test_check
	 *
	 * @return array
	 */
	public function provider_check()
	{
		$mock = $this->getMock('Crazy_Test', array('unit_test_callback'));
		// TODO: enchance this / make params more specific
		$mock
			->expects($this->once())
			->method('unit_test_callback')
			->withAnyParameters();

		// $first_array, $second_array, $rules, $first_expected, $second_expected
		return array(
			array(
				array('foo' => 'bar'),
				array('foo' => array('not_empty', NULL)),
				array('foo' => array($mock, 'unit_test_callback')),
				TRUE,
				array(),
			),
			array(
				array('unit' => 'test'),
				array('foo' => array('not_empty', NULL), 'unit' => array('min_length', 6)),
				array(),
				FALSE,
				array('foo' => 'foo must not be empty', 'unit' => 'unit must be at least 6 characters long'),
			),
		);
	}

	/**
	 * Tests Validate::check()
	 *
	 * @test
	 * @covers Validate::check
	 * @covers Validate::callbacks
	 * @covers Validate::callback
	 * @covers Validate::rule
	 * @covers Validate::rules
	 * @covers Validate::errors
	 * @covers Validate::error
	 * @dataProvider provider_check
	 * @param string  $url       The url to test
	 * @param boolean $expected  Is it valid?
	 */
	public function test_check($array, $rules, $callbacks, $expected, $expected_errors)
	{
		$validate = new Validate($array);

		foreach ($rules as $field => $rule)
		{
			$validate->rule($field, $rule[0], array($rule[1]));
		}
		foreach ($callbacks as $field => $callback)
			$validate->callback($field, $callback);

		$status = $validate->check();
		$errors = $validate->errors(TRUE);

		$this->assertSame($expected, $status);
		$this->assertSame($expected_errors, $errors);

		$validate = new Validate($array);
		foreach ($rules as $field => $rule)
			$validate->rules($field, array($rule[0] => array($rule[1])));
		$this->assertSame($expected, $validate->check());
	}

	/**
	 * This test asserts that Validate::check will call callbacks with all of the 
	 * parameters supplied when the callback was specified
	 *
	 * @test
	 * @covers Validate::callback
	 */
	public function test_object_callback_with_parameters()
	{
		$params = array(42, 'kohana' => 'rocks');

		$validate = new Validate(array('foo' => 'bar'));

		// Generate an isolated callback
		$mock = $this->getMock('Random_Class_That_DNX', array('unit_test_callback'));

		$mock->expects($this->once())
			->method('unit_test_callback')
			->with($validate, 'foo', $params);

		$validate->callback('foo', array($mock, 'unit_test_callback'), $params);

		$validate->check();
	}

	/**
	 * In some cases (such as when validating search params in GET) it is necessary for
	 * an empty array to validate successfully
	 *
	 * This test checks that Validate::check() allows the user to specify this setting when 
	 * calling check()
	 *
	 * @test
	 * @ticket 3059
	 * @covers Validate::check
	 */
	public function test_check_allows_option_for_empty_data_array_to_validate()
	{
		$validate = new Validate(array());

		$this->assertFalse($validate->check(FALSE));

		$this->assertTrue($validate->check(TRUE));

		$validate->rule('name', 'not_empty');

		$this->assertFalse($validate->check(TRUE));
		$this->assertFalse($validate->check());
	}

	/**
	 * If you add a rule that says a field should match another field then
	 * a label should be added for the field to match against to ensure that
	 * it will be available when check() is called 
	 *
	 * @test
	 * @ticket 3158
	 * @covers Validate::rule
	 */
	public function test_rule_adds_label_if_rule_is_match_and_label_dnx()
	{
		$data   = array('password' => 'lolcats',  'password_confirm' => 'lolcats');
		$labels = array('password' => 'password', 'password_confirm' => 'password confirm');

		$validate = new Validate($data);
		
		$validate->rule('password', 'matches', array('password_confirm'));

		$this->assertAttributeSame($labels, '_labels', $validate);

		$this->assertTrue($validate->check());

		// Now we do the dnx check 

		$validate = new Validate($data);

		$labels = array('password_confirm' => 'TEH PASS') + $labels;
		$validate->label('password_confirm', $labels['password_confirm']);

		$validate->rule('password', 'matches', array('password_confirm'));

		$this->assertAttributeSame($labels, '_labels', $validate);

		$this->assertTrue($validate->check());
	}

	/**
	 * Provides test data for test_errors()
	 *
	 * @return array
	 */
	public function provider_errors()
	{
		// [data, rules, expected], ...
		return array(
			array(
				array('username' => 'frank'),
				array('username' => array('not_empty' => NULL)),
				array(),
			),
			array(
				array('username' => ''),
				array('username' => array('not_empty' => NULL)),
				array('username' => 'username must not be empty'),
			),
			array(
				array('username1' => ''),
				array('username1' => array('not_empty' => NULL)),
				array('username1' => 'username must not be empty'),
			),
		);
	}

	/**
	 * Tests Validate::errors()
	 *
	 * @test
	 * @covers Validate::errors
	 * @dataProvider provider_errors
	 * @param string  $url       The url to test
	 * @param boolean $expected  Is it valid?
	 */
	public function test_errors($array, $rules, $expected)
	{
		$validate = Validate::factory($array);

		foreach($rules as $field => $field_rules)
		{
			$validate->rules($field, $field_rules);
		}

		$validate->check();

		$this->assertSame($expected, $validate->errors('validate', FALSE));
	}
}

<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests the kohana text class (Kohana_Text)
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.text
 *
 * @package    Kohana
 * @category   Tests
 */
class Kohana_TextTest extends Unittest_TestCase
{

	/**
	 * Sets up the test enviroment
	 */
	// @codingStandardsIgnoreStart
	function setUp()
	// @codingStandardsIgnoreEnd
	{
		parent::setUp();

		Text::alternate();
	}

	/**
	 * This test makes sure that auto_p returns an empty string if
	 * an empty input was provided
	 *
	 * @test
	 * @covers Text::auto_p
	 */
	function test_auto_para_returns_empty_string_on_empty_input()
	{
		$this->assertSame('', Text::auto_p(''));
	}

	/**
	 *
	 * @return array Test Data
	 */
	function provider_auto_para_does_not_enclose_html_tags_in_paragraphs()
	{
		return array(
			array(
				array('div'),
				'<div>Pick a plum of peppers</div>',
			),
			array(
				array('div'),
				'<div id="awesome">Tangas</div>',
			),
		);
	}

	/**
	 * This test makes sure that auto_p doesn't enclose HTML tags
	 * in paragraphs
	 *
	 * @test
	 * @covers Text::auto_p
	 * @dataProvider provider_auto_para_does_not_enclose_html_tags_in_paragraphs
	 */
	function test_auto_para_does_not_enclose_html_tags_in_paragraphs(array $tags, $text)
	{
		$output = Text::auto_p($text);

		foreach ($tags as $tag)
		{
			$this->assertNotTag(
				array('tag' => $tag, 'ancestor' => array('tag' => 'p')),
				$output
			);
		}
	}

	/**
	 * This test makes sure that auto_p surrounds a single line of text
	 * with paragraph tags
	 *
	 * @test
	 * @covers Text::auto_p
	 */
	function test_auto_para_encloses_slot_in_paragraph()
	{
		$text = 'Pick a pinch of purple pepper';

		$this->assertSame('<p>'.$text.'</p>', Text::auto_p($text));
	}

	/**
	 * Make sure that multiple new lines are replaced with paragraph tags
	 *
	 * @test
	 * @covers Text::auto_p
	 */
	public function test_auto_para_replaces_multiple_newlines_with_paragraph()
	{
		$this->assertSame(
			"<p>My name is john</p>\n\n<p>I'm a developer</p>",
			Text::auto_p("My name is john\n\n\n\nI'm a developer")
		);
	}

	/**
	 * Data provider for test_limit_words
	 *
	 * @return array Array of test data
	 */
	function provider_limit_words()
	{
		return array
		(
			array('', '', 100, NULL),
			array('…', 'The rain in spain', -10, NULL),
			array('The rain…', 'The rain in spain', 2, NULL),
			array('The rain...', 'The rain in spain', 2, '...'),
		);
	}

	/**
	 *
	 * @test
	 * @dataProvider provider_limit_words
	 */
	function test_limit_words($expected, $str, $limit, $end_char)
	{
		$this->assertSame($expected, Text::limit_words($str, $limit, $end_char));
	}

	/**
	 * Provides test data for test_limit_chars()
	 *
	 * @return array Test data
	 */
	function provider_limit_chars()
	{
		return array
		(
			array('', '', 100, NULL, FALSE),
			array('…', 'BOO!', -42, NULL, FALSE),
			array('making php bet…', 'making php better for the sane', 14, NULL, FALSE),
			array('Garçon! Un café s.v.p.', 'Garçon! Un café s.v.p.', 50, '__', FALSE),
			array('Garçon!__', 'Garçon! Un café s.v.p.', 8, '__', FALSE),
			// @issue 3238
			array('making php…', 'making php better for the sane', 14, NULL, TRUE),
			array('Garçon!__', 'Garçon! Un café s.v.p.', 9, '__', TRUE),
			array('Garçon!__', 'Garçon! Un café s.v.p.', 7, '__', TRUE),
			array('__', 'Garçon! Un café s.v.p.', 5, '__', TRUE),
		);
	}

	/**
	 * Tests Text::limit_chars()
	 *
	 * @test
	 * @dataProvider provider_limit_chars
	 */
	function test_limit_chars($expected, $str, $limit, $end_char, $preserve_words)
	{
		$this->assertSame($expected, Text::limit_chars($str, $limit, $end_char, $preserve_words));
	}

	/**
	 * Test Text::alternate()
	 *
	 * @test
	 */
	function test_alternate_alternates_between_parameters()
	{
		list($val_a, $val_b, $val_c) = array('good', 'bad', 'ugly');

		$this->assertSame('good', Text::alternate($val_a, $val_b, $val_c));
		$this->assertSame('bad',  Text::alternate($val_a, $val_b, $val_c));
		$this->assertSame('ugly', Text::alternate($val_a, $val_b, $val_c));

		$this->assertSame('good', Text::alternate($val_a, $val_b, $val_c));
	}

	/**
	 * Tests Text::alternate()
	 *
	 * @test
	 * @covers Text::alternate
	 */
	function test_alternate_resets_when_called_with_no_params_and_returns_empty_string()
	{
		list($val_a, $val_b, $val_c) = array('yes', 'no', 'maybe');

		$this->assertSame('yes', Text::alternate($val_a, $val_b, $val_c));

		$this->assertSame('', Text::alternate());

		$this->assertSame('yes', Text::alternate($val_a, $val_b, $val_c));
	}

	/**
	 * Provides test data for test_ucfirst
	 *
	 * @return array Test data
	 */
	public function provider_ucfirst()
	{
		return array(
			array('Content-Type', 'content-type', '-'),
			array('Բարեւ|Ձեզ', 'բարեւ|ձեզ', '|'),
		);
	}
	
	/**
	 * Covers Text::ucfirst()
	 *
	 * @test
	 * @dataProvider provider_ucfirst
	 */
	public function test_ucfirst($expected, $string, $delimiter)
	{
		$this->assertSame($expected, Text::ucfirst($string, $delimiter));
	}

	/**
	 * Provides test data for test_reducde_slashes()
	 *
	 * @returns array Array of test data
	 */
	function provider_reduce_slashes()
	{
		return array
			(
				array('/', '//'),
				array('/google/php/kohana/', '//google/php//kohana//'),
			);
	}

	/**
	 * Covers Text::reduce_slashes()
	 *
	 * @test
	 * @dataProvider provider_reduce_slashes
	 */
	function test_reduce_slashes($expected, $str)
	{
		$this->assertSame($expected, Text::reduce_slashes($str));
	}

	/**
	 * Provides test data for test_censor()
	 *
	 * @return array Test data
	 */
	function provider_censor()
	{

		return array
			(
				// If the replacement is 1 character long it should be repeated for the length of the removed word
				array("A donkey is also an ***", 'A donkey is also an ass', array('ass'), '*', TRUE),
				array("Cake### isn't nearly as good as kohana###", "CakePHP isn't nearly as good as kohanaphp", array('php'), '#', TRUE),
				// If it's > 1 then it's just replaced straight out
				array("If you're born out of wedlock you're a --expletive--", "If you're born out of wedlock you're a child", array('child'), '--expletive--', TRUE),

				array('class', 'class', array('ass'), '*', FALSE),
			);
	}

	/**
	 * Tests Text::censor
	 *
	 * @test
	 * @dataProvider provider_censor
	 */
	function test_censor($expected, $str, $badwords, $replacement, $replace_partial_words)
	{
		$this->assertSame($expected, Text::censor($str, $badwords, $replacement, $replace_partial_words));
	}

	/**
	 * Provides test data for test_random
	 *
	 * @return array Test Data
	 */
	function provider_random()
	{
		return array(
			array('alnum', 8),
			array('alpha', 10),
			array('hexdec', 20),
			array('nozero', 5),
			array('numeric', 14),
			array('distinct', 12),
			array('aeiou', 4),
			array('‹¡›«¿»', 8), // UTF8 characters
			array(NULL, 8), // Issue #3256
		);
	}

	/**
	 * Tests Text::random() as well as possible
	 *
	 * Obviously you can't compare a randomly generated string against a
	 * pre-generated one and check that they are the same as this goes
	 * against the whole ethos of random.
	 *
	 * This test just makes sure that the value returned is of the correct
	 * values and length
	 *
	 * @test
	 * @dataProvider provider_random
	 */
	function test_random($type, $length)
	{
		if ($type === NULL)
		{
			$type = 'alnum';
		}

		$pool = (string) $type;

		switch ($pool)
		{
			case 'alnum':
				$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;
			case 'alpha':
				$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;
			case 'hexdec':
				$pool = '0123456789abcdef';
			break;
			case 'numeric':
				$pool = '0123456789';
			break;
			case 'nozero':
				$pool = '123456789';
			break;
			case 'distinct':
				$pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
			break;
		}

		$this->assertRegExp('/^['.$pool.']{'.$length.'}$/u', Text::random($type, $length));
	}

	/**
	 * Provides test data for test_similar
	 *
	 * @return array
	 */
	function provider_similar()
	{
		return array
			(
				// TODO: add some more cases
				array('foo', array('foobar', 'food', 'fooberry')),
			);
	}

	/**
	 * Tests Text::similar()
	 *
	 * @test
	 * @dataProvider provider_similar
	 * @covers Text::similar
	 */
	function test_similar($expected, $words)
	{
		$this->assertSame($expected, Text::similar($words));
	}

	/**
	 * Provides test data for test_bytes
	 *
	 * @return array
	 */
	public function provider_bytes()
	{
		return array
			(
				// TODO: cover the other units
				array('256.00 B', 256, NULL, NULL, TRUE),
				array('1.02 kB', 1024, NULL, NULL, TRUE),

				// In case you need to know the size of a floppy disk in petabytes
				array('0.00147 GB', 1.44 * 1000 * 1024, 'GB', '%01.5f %s', TRUE),

				// SI is the standard, but lets deviate slightly
				array('1.00 MiB', 1024 * 1024, 'MiB', NULL, FALSE),
			);
	}

	/**
	 * Tests Text::bytes()
	 *
	 * @test
	 * @dataProvider provider_bytes
	 */
	function test_bytes($expected, $bytes, $force_unit, $format, $si)
	{
		$this->assertSame($expected, Text::bytes($bytes, $force_unit, $format, $si));
	}

	/**
	 * Provides test data for test_widont()
	 *
	 * @return array Test data
	 */
	function provider_widont()
	{
		return array
			(
				// A very simple widont test
				array(
					'A very simple&nbsp;test',
					'A very simple test',
				),
				// Single word items shouldn't be changed
				array(
					'Test',
					'Test',
				),
				// Single word after single space shouldn't be changed either
				array(
					' Test',
					' Test',
				),
				// Single word with HTML all around
				array(
					'<ul><li><p>Test</p></li><ul>',
					'<ul><li><p>Test</p></li><ul>',
				),
				// Single word after single space with HTML all around
				array(
					'<ul><li><p> Test</p></li><ul>',
					'<ul><li><p> Test</p></li><ul>',
				),
				// Widont with more than one paragraph
				array(
					'<p>In a couple of&nbsp;paragraphs</p><p>paragraph&nbsp;two</p>',
					'<p>In a couple of paragraphs</p><p>paragraph two</p>',
				),
				// a link inside a heading
				array(
					'<h1><a href="#">In a link inside a&nbsp;heading </a></h1>',
					'<h1><a href="#">In a link inside a heading </a></h1>',
				),
				// a link followed by text
				array(
					'<h1><a href="#">In a link</a> followed by other&nbsp;text</h1>',
					'<h1><a href="#">In a link</a> followed by other text</h1>',
				),
				// empty html, with no text inside
				array(
					'<h1><a href="#"></a></h1>',
					'<h1><a href="#"></a></h1>',
				),
				// apparently, we don't love DIVs
				array(
					'<div>Divs get no love!</div>',
					'<div>Divs get no love!</div>',
				),
				// we don't love PREs, either
				array(
					'<pre>Neither do PREs</pre>',
					'<pre>Neither do PREs</pre>',
				),
				// but we love DIVs with paragraphs
				array(
					'<div><p>But divs with paragraphs&nbsp;do!</p></div>',
					'<div><p>But divs with paragraphs do!</p></div>',
				),
				array(
					'No gain, no&nbsp;pain',
					'No gain, no pain',
				),
				array(
					"spaces?what'rethey?",
					"spaces?what'rethey?",
				),
			/*
			 * // @issue 3499, with HTML at the end
			 * array(
			 * 		'with HTML at the end &nbsp;<strong>Kohana</strong>',
			 * 		'with HTML at the end <strong>Kohana</strong>',
			 * 	),
			 * 	// @issue 3499, with HTML with attributes at the end
			 * 	array(
			 * 		'with HTML at the end:&nbsp;<a href="#" title="Kohana">Kohana</a>',
			 * 		'with HTML at the end: <a href="#" title="Kohana">Kohana</a>',
			 * 	),
			 */
				array(
					'',
					'',
				),
			);
	}

	/**
	 * Tests Text::widont()
	 *
	 * @test
	 * @dataProvider provider_widont
	 */
	function test_widont($expected, $string)
	{
		$this->assertSame($expected, Text::widont($string));
	}


	/**
	 * This checks that auto_link_emails() respects word boundaries and does not
	 * just blindly replace all occurences of the email address in the text.
	 *
	 * In the sample below the algorithm was replacing all occurences of voorzitter@xxxx.com
	 * inc the copy in the second list item.
	 *
	 * It was updated in 6c199366efc1115545ba13108b876acc66c54b2d to respect word boundaries
	 *
	 * @test
	 * @covers Text::auto_link_emails
	 * @ticket 2772
	 */
	function test_auto_link_emails_respects_word_boundaries()
	{
		$original = '<ul>
						<li>voorzitter@xxxx.com</li>
						<li>vicevoorzitter@xxxx.com</li>
					</ul>';

		$this->assertFalse(strpos('vice', Text::auto_link_emails($original)));
	}


	/**
	 * Provides some test data for test_number()
	 *
	 * @return array
	 */
	public function provider_number()
	{
		return array(
			array('one', 1),
			array('twenty-three', 23),
			array('fourty-two', 42),
			array('five million, six hundred and thirty-two', 5000632),
			array('five million, six hundred and thirty', 5000630),
			array('nine hundred million', 900000000),
			array('thirty-seven thousand', 37000),
			array('one thousand and twenty-four', 1024),
		);
	}

	/**
	 * Checks that Text::number formats a number into english text
	 *
	 * @test
	 * @dataProvider provider_number
	 */
	public function test_number($expected, $number)
	{
		$this->assertSame($expected, Text::number($number));
	}

	/**
	 * Provides test data for test_auto_link_urls()
	 *
	 * @return array
	 */
	public function provider_auto_link_urls()
	{
		return array(
			// First we try with the really obvious url
			array(
				'Some random text <a href="http://www.google.com">http://www.google.com</a>',
				'Some random text http://www.google.com',
			),
			// Then we try with varying urls
			array(
				'Some random <a href="http://www.google.com">www.google.com</a>',
				'Some random www.google.com',
			),
			array(
				'Some random google.com',
				'Some random google.com',
			),
			// Check that it doesn't link urls in a href
			array(
				'Look at me <a href="http://google.com">Awesome stuff</a>',
				'Look at me <a href="http://google.com">Awesome stuff</a>',
			),
			array(
				'Look at me <a href="http://www.google.com">http://www.google.com</a>',
				'Look at me <a href="http://www.google.com">http://www.google.com</a>',
			),
			// Punctuation at the end of the URL
			array(
				'Wow <a href="http://www.google.com">http://www.google.com</a>!',
				'Wow http://www.google.com!',
			),
			array(
				'Zomg <a href="http://www.google.com">www.google.com</a>!',
				'Zomg www.google.com!',
			),
			array(
				'Well this, <a href="http://www.google.com">www.google.com</a>, is cool',
				'Well this, www.google.com, is cool',
			),
			// @issue 3190
			array(
				'<a href="http://www.google.com/">www.google.com</a>',
				'<a href="http://www.google.com/">www.google.com</a>',
			),
			array(
				'<a href="http://www.google.com/">www.google.com</a> <a href="http://www.google.com/">http://www.google.com/</a>',
				'<a href="http://www.google.com/">www.google.com</a> http://www.google.com/',
			),
			// @issue 3436
			array(
				'<strong><a href="http://www.google.com/">http://www.google.com/</a></strong>',
				'<strong>http://www.google.com/</strong>',
			),
			// @issue 4208, URLs with a path
			array(
				'Foobar <a href="http://www.google.com/analytics">www.google.com/analytics</a> cake',
				'Foobar www.google.com/analytics cake',
			),
			array(
				'Look at this <a href="http://www.google.com/analytics">www.google.com/analytics</a>!',
				'Look at this www.google.com/analytics!',
			),
			array(
				'Path <a href="http://www.google.com/analytics">http://www.google.com/analytics</a> works?',
				'Path http://www.google.com/analytics works?',
			),
			array(
				'Path <a href="http://www.google.com/analytics">http://www.google.com/analytics</a>',
				'Path http://www.google.com/analytics',
			),
			array(
				'Path <a href="http://www.google.com/analytics">www.google.com/analytics</a>',
				'Path www.google.com/analytics',
			),
		);
	}

	/**
	 * Runs tests for Test::auto_link_urls
	 *
	 * @test
	 * @dataProvider provider_auto_link_urls
	 */
	public function test_auto_link_urls($expected, $text)
	{
		$this->assertSame($expected, Text::auto_link_urls($text));
	}

	/**
	 * Provides test data for test_auto_link_emails()
	 *
	 * @return array
	 */
	public function provider_auto_link_emails()
	{
		return array(
			// @issue 3162
			array(
				'<span class="broken"><a href="mailto:info@test.com">info@test.com</a></span>',
				'<span class="broken">info@test.com</span>',
			),
			array(
				'<a href="mailto:info@test.com">info@test.com</a>',
				'<a href="mailto:info@test.com">info@test.com</a>',
			),
			// @issue 3189
			array(
				'<a href="mailto:email@address.com">email@address.com</a> <a href="mailto:email@address.com">email@address.com</a>',
				'<a href="mailto:email@address.com">email@address.com</a> email@address.com',
			),
		);
	}

	/**
	 * Runs tests for Test::auto_link_emails
	 *
	 * @test
	 * @dataProvider provider_auto_link_emails
	 */
	public function test_auto_link_emails($expected, $text)
	{
		// Use html_entity_decode because emails will be randomly encoded by HTML::mailto
		$this->assertSame($expected, html_entity_decode(Text::auto_link_emails($text)));
	}

	/**
	 * Provides test data for test_auto_link
	 *
	 * @return array Test data
	 */
	public function provider_auto_link()
	{
		return array(
			array(
				'Hi there, my site is kohanaframework.org and you can email me at nobody@kohanaframework.org',
				array('kohanaframework.org'),
			),

			array(
				'Hi my.domain.com@domain.com you came from',
				FALSE,
				array('my.domain.com@domain.com'),
			),
		);
	}

	/**
	 * Tests Text::auto_link()
	 *
	 * @test
	 * @dataProvider provider_auto_link
	 */
	public function test_auto_link($text, $urls = array(), $emails = array())
	{
		$linked_text = Text::auto_link($text);

		if ($urls === FALSE)
		{
			$this->assertNotContains('http://', $linked_text);
		}
		elseif (count($urls))
		{
			foreach ($urls as $url)
			{
				// Assert that all the urls have been caught by text auto_link_urls()
				$this->assertContains(Text::auto_link_urls($url), $linked_text);
			}
		}

		foreach ($emails as $email)
		{
			$this->assertContains('&#109;&#097;&#105;&#108;&#116;&#111;&#058;'.$email, $linked_text);
		}

	}


	public function provider_user_agents()
	{
		return array(
			array(
				"Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2049.0 Safari/537.36",
				array(
					'browser' => 'Chrome',
					'version' => '37.0.2049.0',
					'platform' => "Windows 8.1"
				)
			),
			array(
				"Mozilla/5.0 (Macintosh; U; Mac OS X 10_6_1; en-US) AppleWebKit/530.5 (KHTML, like Gecko) Chrome/ Safari/530.5",
				array(
					'browser' => 'Chrome',
					'version' => '530.5',
					'platform' => "Mac OS X"
				)
			),
			array(
				"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/537.13+ (KHTML, like Gecko) Version/5.1.7 Safari/534.57.2",
				array(
					'browser' => 'Safari',
					'version' => '534.57.2',
					'platform' => 'Mac OS X'
				)
			),
			array(
				"Lynx/2.8.8dev.3 libwww-FM/2.14 SSL-MM/1.4.1",
				array(
					'browser' => 'Lynx',
					'version' => '2.8.8dev.3',
					'platform' => false
				)
			)
		);
	}

	/**
	 * Tests Text::user_agent
	 *
	 * @dataProvider provider_user_agents
	 * @group current
	 */
	public function test_user_agent_returns_correct_browser($userAgent, $expectedData)
	{
		$browser = Text::user_agent($userAgent, 'browser');

		$this->assertEquals($expectedData['browser'], $browser);
	}

	/**
	 * Tests Text::user_agent
	 *
	 * @dataProvider provider_user_agents
	 * @test
	 */
	public function test_user_agent_returns_correct_version($userAgent, $expectedData)
	{
		$version = Text::user_agent($userAgent, 'version');

		$this->assertEquals($expectedData['version'], $version);
	}

	/**
	 * Tests Text::user_agent
	 * @test
	 */
	public function test_user_agent_recognizes_robots()
	{
		$bot = Text::user_agent('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', 'robot');

		$this->assertEquals('Googlebot', $bot);
	}

	/**
	 * Tests Text::user_agent
	 *
	 * @dataProvider provider_user_agents
	 * @test
	 */
	public function test_user_agent_returns_correct_platform($userAgent, $expectedData)
	{
		$platform = Text::user_agent($userAgent, 'platform');

		$this->assertEquals($expectedData['platform'], $platform);
	}


	/**
	 * Tests Text::user_agent
	 * @test
	 */
	public function test_user_agent_accepts_array()
	{
		$agent_info = Text::user_agent(
		    'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 '.
		    '(KHTML, like Gecko) Chrome/37.0.2049.0 Safari/537.36',
		    array('browser', 'version', 'platform'));

		$this->assertArrayHasKey('browser', $agent_info);
		$this->assertArrayHasKey('version', $agent_info);
		$this->assertArrayHasKey('platform', $agent_info);

	}

}

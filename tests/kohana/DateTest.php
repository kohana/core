<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Date class
 * 
 * @group kohana
 *
 * @package    Unittest
 * @author     Kohana Team
 * @author     BRMatt <matthew@sigswitch.com>
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
Class Kohana_DateTest extends Kohana_Unittest_TestCase
{
	/**
	 * Provides test data for test_date()
	 *
	 * @return array
	 */
	function provider_am_pm()
	{
		return array(
			// All possible values
			array(0, 'AM'),
			array(1, 'AM'),
			array(2, 'AM'),
			array(3, 'AM'),
			array(4, 'AM'),
			array(5, 'AM'),
			array(6, 'AM'),
			array(7, 'AM'),
			array(8, 'AM'),
			array(9, 'AM'),
			array(10, 'AM'),
			array(11, 'AM'),
			array(12, 'PM'),
			array(13, 'PM'),
			array(14, 'PM'),
			array(15, 'PM'),
			array(16, 'PM'),
			array(17, 'PM'),
			array(18, 'PM'),
			array(19, 'PM'),
			array(20, 'PM'),
			array(21, 'PM'),
			array(22, 'PM'),
			array(23, 'PM'),
			array(24, 'PM'),
			// ampm doesn't validate the hour, so I don't think we should test it..
			// test strings are converted
			array('0', 'AM'),
			array('12', 'PM'),
		);
	}

	/**
	 * Tests Date::ampm()
	 * 
	 * @test
	 * @covers Date::ampm
	 * @dataProvider provider_am_pm
	 * @param <type> $hour
	 * @param <type> $expected
	 */
	function test_am_pm($hour, $expected)
	{
		$this->assertSame(
			$expected,
			Date::ampm($hour)
		);
	}

	/**
	 * Provides test data for test_adjust()
	 *
	 * @return array
	 */
	function provider_adjust()
	{
		return array(
			// Might as well test all possibilities
			array(1,  'am', '01'),
			array(2,  'am', '02'),
			array(3,  'am', '03'),
			array(4,  'am', '04'),
			array(5,  'am', '05'),
			array(6,  'am', '06'),
			array(7,  'am', '07'),
			array(8,  'am', '08'),
			array(9,  'am', '09'),
			array(10, 'am', '10'),
			array(11, 'am', '11'),
			array(12, 'am', '00'),
			array(1,  'pm', '13'),
			array(2,  'pm', '14'),
			array(3,  'pm', '15'),
			array(4,  'pm', '16'),
			array(5,  'pm', '17'),
			array(6,  'pm', '18'),
			array(7,  'pm', '19'),
			array(8,  'pm', '20'),
			array(9,  'pm', '21'),
			array(10, 'pm', '22'),
			array(11, 'pm', '23'),
			array(12, 'pm', '12'),
			// It should also work with strings instead of ints
			array('10', 'pm', '22'),
			array('10', 'am', '10'),
		);
	}

	/**
	 * Tests Date::ampm()
	 *
	 * @test
	 * @dataProvider provider_adjust
	 * @param integer $hour       Hour in 12 hour format
	 * @param string  $ampm       Either am or pm
	 * @param string  $expected   Expected result
	 */
	function test_adjust($hour, $ampm, $expected)
	{
		$this->assertSame(
			$expected,
			Date::adjust($hour, $ampm)
		);
	}

	/**
	 * Provides test data for test_days()
	 *
	 * @return array
	 */
	function provider_days()
	{
		return array(
			// According to "the rhyme" these should be the same every year
			array(9, FALSE, 30),
			array(4, FALSE, 30),
			array(6, FALSE, 30),
			array(11, FALSE, 30),
			array(1, FALSE, 31),
			array(3, FALSE, 31),
			array(5, FALSE, 31),
			array(7, FALSE, 31),
			array(8, FALSE, 31),
			array(10, FALSE, 31),
			// February is such a pain
			array(2, 2001, 28),
			array(2, 2000, 29),
			array(2, 2012, 29),
		);
	}

	/**
	 * Tests Date::days()
	 *
	 * @test
	 * @covers Date::days
	 * @dataProvider provider_days
	 * @param integer $month
	 * @param integer $year
	 * @param integer $expected
	 */
	function test_days($month, $year, $expected)
	{
		$days = Date::days($month, $year);

		$this->assertSame(
			$expected,
			count($days)
		);

		// This should be a mirrored array, days => days
		for($i = 1; $i <= $expected; ++$i)
		{
			$this->assertArrayHasKey($i, $days);
			// Combining the type check into this saves about 400-500 assertions!
			$this->assertSame((string) $i, $days[$i]);
		}
	}

	/**
	 * Tests Date::months()
	 * 
	 * @test
	 * @covers Date::months
	 */
	function test_months()
	{
		$months = Date::months();

		$this->assertSame(12, count($months));

		for($i = 1; $i <= 12; ++$i)
		{
			$this->assertArrayHasKey($i, $months);
			$this->assertSame((string) $i, $months[$i]);
		}
	}

	/**
	 * Provides test data for test_span()
	 *
	 * @return array
	 */
	function provider_span()
	{
		$time = time();
		return array(
			// Test that it must specify an output format
			array(
				$time,
				$time,
				'',
				FALSE
			),
			// Test that providing only one output just returns that output
			array(
				$time - 30,
				$time,
				'seconds',
				30
			),
			// Random tests
			array(
				$time - 30,
				$time,
				'years,months,weeks,days,hours,minutes,seconds',
				array('years' => 0, 'months' => 0, 'weeks' => 0, 'days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 30),
			),
			array(
				$time - (60 * 60 * 24 * 782) + (60 * 25),
				$time,
				'years,months,weeks,days,hours,minutes,seconds',
				array('years' => 2, 'months' => 1, 'weeks' => 3, 'days' => 0, 'hours' => 1, 'minutes' => 28, 'seconds' => 24),
			),
			// Should be able to compare with the future & that it only uses formats specified
			array(
				$time + (60 * 60 * 24 * 15) + (60 * 5),
				$time,
				'weeks,days,hours,minutes,seconds',
				array('weeks' => 2, 'days' => 1, 'hours' => 0, 'minutes' => 5, 'seconds' => 0),
			),
			array(
				// Add a bit of extra time to account for phpunit processing
				$time + (14 * 31 * 24* 60 * 60) + (79 * 80),
				NULL,
				'months,years',
				array('months' => 2, 'years' => 1),
			),
		);
	}

	/**
	 * Tests Date::span()
	 *
	 * @test
	 * @covers Date::span
	 * @dataProvider provider_span
	 * @param integer $time1     Time in the past
	 * @param integer $time2     Time to compare against
	 * @param string  $output    Units to output
	 * @param array   $expected  Array of $outputs => values
	 */
	function test_span($time1, $time2, $output, $expected)
	{
		$this->assertSame(
			$expected,
			Date::span($time1, $time2, $output)
		);
	}

	/**
	 * Provides test data to test_fuzzy_span
	 * 
	 * This test data is provided on the assumption that it
	 * won't take phpunit more than 30 seconds to get the 
	 * data from this provider to the test... ;)
	 *
	 * @return array Test Data
	 */
	function provider_fuzzy_span()
	{
		return array(
			array('moments ago', time() - 30),
			array('in moments', time() + 30),

			array('a few minutes ago', time() - 10*60),
			array('in a few minutes', time() + 10*60),

			array('less than an hour ago', time() - 45*60),
			array('in less than an hour', time() + 45*60),

			array('a couple of hours ago', time() - 2*60*60),
			array('in a couple of hours', time() + 2*60*60),

			array('less than a day ago', time() - 12*60*60),
			array('in less than a day', time() + 12*60*60),

			array('about a day ago', time() - 30*60*60),	
			array('in about a day', time() + 30*60*60),	

			array('a couple of days ago', time() - 3*24*60*60),
			array('in a couple of days', time() + 3*24*60*60),

			array('less than a week ago', time() - 5*24*60*60),
			array('in less than a week', time() + 5*24*60*60),

			array('about a week ago', time() - 9*24*60*60),
			array('in about a week', time() + 9*24*60*60),

			array('less than a month ago', time() - 20*24*60*60),
			array('in less than a month', time() + 20*24*60*60),

			array('about a month ago', time() - 40*24*60*60),
			array('in about a month', time() + 40*24*60*60),

			array('a couple of months ago', time() - 3*30*24*60*60),
			array('in a couple of months', time() + 3*30*24*60*60),

			array('less than a year ago', time() - 7*31*24*60*60),
			array('in less than a year', time() + 7*31*24*60*60),

			array('about a year ago', time() - 18*31*24*60*60),
			array('in about a year', time() + 18*31*24*60*60),

			array('a couple of years ago', time() - 3*12*31*24*60*60),
			array('in a couple of years', time() + 3*12*31*24*60*60),

			array('a few years ago', time() - 5*12*31*24*60*60),
			array('in a few years', time() + 5*12*31*24*60*60),

			array('about a decade ago', time() - 11*12*31*24*60*60),
			array('in about a decade', time() + 11*12*31*24*60*60),

			array('a couple of decades ago', time() - 20*12*31*24*60*60),
			array('in a couple of decades', time() + 20*12*31*24*60*60),

			array('several decades ago', time() - 50*12*31*24*60*60),
			array('in several decades', time() + 50*12*31*24*60*60),

			array('a long time ago', time() - pow(10,10)),
			array('in a long time', time() + pow(10,10)),
		);
	}

	/**
	 * Test of Date::fuzy_span()
	 *
	 * @test
	 * @dataProvider provider_fuzzy_span
	 * @param string $expected Expected output
	 * @param integer $timestamp Timestamp to use
	 */
	function test_fuzzy_span($expected, $timestamp)
	{
		$this->assertSame(
			$expected,
			Date::fuzzy_span($timestamp)
		);
	}

	/** 
	 * Provides test data for test_years()
	 *
	 * @return array Test Data
	 */
	function provider_years()
	{
		return array(
			array(
				array (
					2005 => '2005', 
					2006 => '2006', 
					2007 => '2007',
				    2008 => '2008',
				    2009 => '2009',
				    2010 => '2010',
				    2011 => '2011',
				    2012 => '2012',
					2013 => '2013', 
					2014 => '2014',
					2015 => '2015',
				),
				2005,
				2015
			),
		);
	}

	/**
	 * Tests Data::years()
	 *
	 * @test
	 * @dataProvider provider_years
	 */
	function test_years($expected, $start = FALSE, $end = FALSE)
	{
		$this->assertSame(
			$expected,
			Date::years($start, $end)
		);
	}

	function provider_hours()
	{
		return array(
			array(
				array(
					1 => '1',
					2 => '2',
					3 => '3',
					4 => '4',
					5 => '5',
					6 => '6',
					7 => '7',
					8 => '8',
					9 => '9',
					10 => '10',
					11 => '11',
					12 => '12',
				),
			),
		);
	}

	/**
	 * Test for Date::hours
	 *
	 * @test
	 * @dataProvider provider_hours
	 */
	function test_hours($expected, $step = 1, $long = FALSE, $start = NULL) 
	{
		$this->assertSame(
			$expected,
			Date::hours($step, $long, $start)
		);
	}

	/**
	 * Provides test data for test_seconds
	 *
	 * @return array Test data
	 */
	function provider_seconds()
	{
		return array(
			array(
				// Thank god for var_export()
				array ( 
					0 => '00', 1 => '01', 2 => '02', 3 => '03', 4 => '04', 
					5 => '05', 6 => '06', 7 => '07', 8 => '08', 9 => '09', 
					10 => '10', 11 => '11', 12 => '12', 13 => '13', 14 => '14', 
					15 => '15', 16 => '16', 17 => '17', 18 => '18', 19 => '19', 
					20 => '20', 21 => '21', 22 => '22', 23 => '23', 24 => '24', 
					25 => '25', 26 => '26', 27 => '27', 28 => '28', 29 => '29', 
					30 => '30', 31 => '31', 32 => '32', 33 => '33', 34 => '34', 
					35 => '35', 36 => '36', 37 => '37', 38 => '38', 39 => '39', 
					40 => '40', 41 => '41', 42 => '42', 43 => '43', 44 => '44', 
					45 => '45', 46 => '46', 47 => '47', 48 => '48', 49 => '49', 
					50 => '50', 51 => '51', 52 => '52', 53 => '53', 54 => '54', 
					55 => '55', 56 => '56', 57 => '57', 58 => '58', 59 => '59', 
				),
				1,
				0,
				60
			),
		);
	}

	/**
	 *
	 * @test
	 * @dataProvider provider_seconds
	 * @covers Date::seconds
	 */
	function test_seconds($expected, $step = 1, $start = 0, $end = 60)
	{
		$this->assertSame(
			$expected,
			Date::seconds($step, $start, $end)
		);
	}

	/**
	 * Provides test data for test_minutes
	 *
	 * @return array Test data
	 */
	function provider_minutes()
	{
		return array(
			array(
				array( 
					0 => '00', 5 => '05', 10 => '10', 
					15 => '15', 20 => '20', 25 => '25', 
					30 => '30', 35 => '35', 40 => '40', 
					45 => '45', 50 => '50', 55 => '55', 
				),
				5,
			),		
		);
	}

	/**
	 *
	 * @test
	 * @dataProvider provider_minutes
	 */
	function test_minutes($expected, $step)
	{
		$this->assertSame(
			$expected,
			Date::minutes($step)
		);
	}

	/**
	 * This tests that the minutes helper defaults to using a $step of 5
	 * and thus returns an array of 5 minute itervals
	 *
	 * @test
	 * @covers Date::minutes
	 */
	function test_minutes_defaults_to_using_step_of5()
	{
		$minutes = array( 
			0 => '00', 5 => '05', 10 => '10', 
			15 => '15', 20 => '20', 25 => '25', 
			30 => '30', 35 => '35', 40 => '40', 
			45 => '45', 50 => '50', 55 => '55', 
		);

		$this->assertSame(
			$minutes,
			Date::minutes()
		);
	}

}	

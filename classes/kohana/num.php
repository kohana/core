<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Number helper class. Provides additional formatting methods that for working
 * with numbers.
 *
 * @package    Kohana
 * @category   Helpers
 * @author     Kohana Team
 * @copyright  (c) 2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Num {

	/**
	 * Returns the English ordinal suffix (th, st, nd, etc) of a number.
	 *
	 *     echo 2, Num::ordinal(2);   // "2nd"
	 *     echo 10, Num::ordinal(10); // "10th"
	 *     echo 33, Num::ordinal(33); // "33rd"
	 *
	 * @param   integer  number
	 * @return  string
	 */
	public static function ordinal($number)
	{
		if ($number % 100 > 10 AND $number % 100 < 14)
		{
			return 'th';
		}

		switch ($number % 10)
		{
			case 1:
				return 'st';
			case 2:
				return 'nd';
			case 3:
				return 'rd';
			default:
				return 'th';
		}
	}

	/**
	 * Locale-aware number and monetary formatting.
	 *
	 *     // In English, "1,200.05"
	 *     // In Spanish, "1200,05"
	 *     // In Portuguese, "1 200,05"
	 *     echo Num::format(1200.05, 2);
	 *
	 *     // In English, "1,200.05"
	 *     // In Spanish, "1.200,05"
	 *     // In Portuguese, "1.200.05"
	 *     echo Num::format(1200.05, 2, TRUE);
	 *
	 * @param   float    number to format
	 * @param   integer  decimal places
	 * @param   boolean  monetary formatting?
	 * @return  string
	 * @since   3.0.2
	 */
	public static function format($number, $places, $monetary = FALSE)
	{
		$info = localeconv();

		if ($monetary)
		{
			$decimal   = $info['mon_decimal_point'];
			$thousands = $info['mon_thousands_sep'];
		}
		else
		{
			$decimal   = $info['decimal_point'];
			$thousands = $info['thousands_sep'];
		}

		return number_format($number, $places, $decimal, $thousands);
	}

} // End num

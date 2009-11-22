<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Number helper class.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Num {

	/**
	 * Returns the English ordinal suffix (th, st, nd, etc) of a number.
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
	 * Locale-aware number formatting.
	 *
	 * @param   float    number to format
	 * @param   integer  decimal places
	 * @param   boolean  monetary formatting?
	 * @return  string
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

	final private function __construct()
	{
		// This is a static class
	}

} // End num

<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Number helper class. Provides additional formatting methods that for working
 * with numbers.
 *
 * @package    Kohana
 * @category   Helpers
 * @author     Kohana Team
 * @copyright  (c) 2009-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Num {

	const ROUND_HALF_UP		= 1;
	const ROUND_HALF_DOWN	= 2;
	const ROUND_HALF_EVEN	= 3;
	const ROUND_HALF_ODD	= 4;

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

	/**
	 * Round a number to a specified precision, using a specified tie breaking technique
	 * 
	 * @param float $value Number to round
	 * @param integer $precision Desired precision
	 * @param integer $mode Tie breaking mode, accepts the PHP_ROUND_HALF_* constants
	 * @param boolean $native Set to false to force use of the userland implementation
	 * @return float Rounded number
	 */
	public static function round($value, $precision = 0, $mode = self::ROUND_HALF_UP, $native = true)
	{
		if (version_compare(PHP_VERSION, '5.3', '>=') && $native)
		{
			return round($value, $precision, $mode);
		}

		if ($mode === self::ROUND_HALF_UP)
		{
			return round($value, $precision);
		}
		else
		{
			$factor = ($precision === 0) ? 1 : pow(10, $precision);
			
			switch ($mode)
			{
				case self::ROUND_HALF_DOWN:
				case self::ROUND_HALF_EVEN:
				case self::ROUND_HALF_ODD:
					// Check if we have a rounding tie, otherwise we can just call round()
					if (($value * $factor) - floor($value * $factor) === 0.5)
					{
						if ($mode === self::ROUND_HALF_DOWN)
						{
							// Round down operation, so we round down unless the value
							// is -ve because up is down and down is up down there. ;)
							$up = ($value < 0);
						}
						else
						{
							// Round up if the integer is odd and the round mode is set to even
							// or the integer is even and the round mode is set to odd.
							// Any other instance round down.
							$up = (!!(floor($value * $factor) & 1) === ($mode === self::ROUND_HALF_EVEN));
						}

						if ($up)
						{
							$value = ceil($value * $factor);
						}
						else
						{
							$value = floor($value * $factor);
						}
						return $value / $factor;
					}
					else
					{
						return round($value, $precision);
					}
				break;
			}
		}
	}

	/**
	 * Converts a file size number to an integer in bytes. File sizes are
	 * defined in the format: SB, where S is the size (1, 15, 300, etc) and B
	 * is the byte modifier: (B)ytes, (K)ilobytes, (M)egabytes, (G)igabytes.
	 *
	 *     echo Num::bytes('1000'); // 1000
	 *     echo Num::bytes('5M');   // 5242880
	 *     echo Num::bytes('200K'); // 204800
	 *
	 * @param   string   file size in SB format
	 * @return  integer
	 */
	public static function bytes($size)
	{
		// Sanitize the size number
		$size = strtoupper(trim($size));

		// Verify the that the format is correct
		if ( ! preg_match('/^[0-9]++[BKMG]?$/', $size))
			throw new Kohana_Exception('The size, ":size", is improperly formatted.', array(
				':size' => $size,
			));

		// Get the unit from the end of the number
		$unit = substr($size, -1);

		// Setup conversions: each unit maps to an exponent
		$conversions = array
		(
			'K' => 1,
			'M' => 2,
			'G' => 3,
		);

		// Convert the number into bytes
		$bytes = intval($size) * pow(1024, Arr::get($conversions, $unit, 0));

		return $bytes;
	}

} // End num

<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Number helper class.
 *
 * @package	   Kohana
 * @author	   Kohana Team
 * @copyright  (c) 2009 Kohana Team
 * @license	   http://kohanaphp.com/license.html
 */
class Kohana_Num {

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

	final private function __construct()
	{
		// This is a static class
	}

} // End num

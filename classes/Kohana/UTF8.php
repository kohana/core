<?php
/**
 * A port of [phputf8](http://phputf8.sourceforge.net) to a unified set of files.
 * Provides multi-byte aware replacement string functions.
 *
 * For UTF-8 support to work correctly, the following requirements must be met:
 *
 * - PCRE needs to be compiled with UTF-8 support (--enable-utf8)
 * - Support for [Unicode properties](http://php.net/reference.pcre.pattern.modifiers)
 *   is highly recommended (--enable-unicode-properties)
 * - The [mbstring extension](http://php.net/mbstring) is highly recommended,
 *   but must not be overloading string functions
 *
 * [!!] This file is licensed differently from the rest of Kohana. As a port of
 * [phputf8](http://phputf8.sourceforge.net), this file is released under the LGPL.
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2007-2014 Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
abstract class Kohana_UTF8 {

	/**
	 * @var  bool  Does the server support UTF-8 natively?
	 */
	public static $server_utf8;

	/**
	 * @var  array  List of called methods that have had their required file included.
	 */
	public static $called = array();

	/**
	 * Includes function files (once)
	 *
	 * @param  string $function
	 * @return void
	 */
	protected static function _load($function)
	{
		if ( ! isset(UTF8::$called[$function]))
		{
			require Kohana::find_file('utf8', $function);

			// Function has been called
			UTF8::$called[$function] = TRUE;
		}
	}

	/**
	 * Recursively cleans arrays, objects, and strings. Removes ASCII control
	 * codes and converts to the requested charset while silently discarding
	 * incompatible characters.
	 *
	 *     UTF8::clean($_GET); // Clean GET data
	 *
	 * @param   mixed   $var      Variable to clean
	 * @param   string  $charset  Character set, defaults to Kohana::$charset
	 * @return  mixed
	 * @uses    Kohana::$charset
	 */
	public static function clean($var, $charset = NULL)
	{
		if ($charset === NULL)
		{
			// Use the application character set
			$charset = Kohana::$charset;
		}

		if (Arr::is_array($var))
		{
			foreach ($var as $key => $val)
			{
				// Recursion!
				$var[UTF8::clean($key)] = UTF8::clean($val);
			}
		}
		elseif (is_string($var) AND $var !== '')
		{
			// Remove control characters
			$var = UTF8::strip_ascii_ctrl($var);

			if ( ! UTF8::is_ascii($var) AND UTF8::$server_utf8)
			{
				// Disable notices
				$error_reporting = error_reporting(~E_NOTICE);

				$var = mb_convert_encoding($var, $charset, $charset);

				// Turn notices back on
				error_reporting($error_reporting);
			}
		}

		return $var;
	}

	/**
	 * Tests whether a string contains only 7-bit ASCII bytes, used to
	 * determine when to use native functions or UTF-8 functions.
	 *
	 *     $ascii = UTF8::is_ascii($str);
	 *
	 * @param   mixed  $str  String or array of strings to check
	 * @return  bool
	 */
	public static function is_ascii($str)
	{
		if (is_array($str))
		{
			$str = implode($str);
		}

		return ! preg_match('/[^\x00-\x7F]/S', $str);
	}

	/**
	 * Strips out device control codes in the ASCII range.
	 *
	 *     $str = UTF8::strip_ascii_ctrl($str);
	 *
	 * @param   string  $str  String to clean
	 * @return  string
	 */
	public static function strip_ascii_ctrl($str)
	{
		return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $str);
	}

	/**
	 * Strips out all non-7bit ASCII bytes.
	 *
	 *     $str = UTF8::strip_non_ascii($str);
	 *
	 * @param   string  $str  String to clean
	 * @return  string
	 */
	public static function strip_non_ascii($str)
	{
		return preg_replace('/[^\x00-\x7F]+/S', '', $str);
	}

	/**
	 * Replaces special/accented UTF-8 characters by ASCII-7 "equivalents".
	 *
	 *     $ascii = UTF8::transliterate_to_ascii($utf8);
	 *
	 * @author  Andreas Gohr <andi@splitbrain.org>
	 * @param   string   $str   String to transliterate
	 * @param   integer  $case  -1 lowercase only, +1 uppercase only, 0 both cases
	 * @return  string
	 * @uses    Kohana::find_file
	 */
	public static function transliterate_to_ascii($str, $case = 0)
	{
		UTF8::_load(__FUNCTION__);

		return _transliterate_to_ascii($str, $case);
	}

	/**
	 * Returns the length of the given string. 
	 * This is a UTF8-aware version of [strlen](http://php.net/strlen).
	 *
	 *     $length = UTF8::strlen($str);
	 *
	 * @param   string  $str  String being measured for length
	 * @return  int
	 * @uses    Kohana::$charset
	 * @uses    Kohana::find_file
	 */
	public static function strlen($str)
	{
		if (UTF8::$server_utf8)
			return mb_strlen($str, Kohana::$charset);

		UTF8::_load(__FUNCTION__);

		return _strlen($str);
	}

	/**
	 * Finds position of first occurrence of a UTF-8 string.
	 * This is a UTF8-aware version of [strpos](http://php.net/strpos).
	 *
	 *     $position = UTF8::strpos($str, $search);
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 * @param   string  $str     Haystack
	 * @param   string  $search  Needle
	 * @param   int     $offset  Offset from which character in haystack to start searching
	 * @return  int|bool  Position of needle, FALSE if the needle is not found
	 * @uses    Kohana::$charset
	 * @uses    Kohana::find_file
	 */
	public static function strpos($str, $search, $offset = 0)
	{
		if (UTF8::$server_utf8)
			return mb_strpos($str, $search, $offset, Kohana::$charset);

		UTF8::_load(__FUNCTION__);

		return _strpos($str, $search, $offset);
	}

	/**
	 * Finds position of last occurrence of a char in a UTF-8 string.
	 * This is a UTF8-aware version of [strrpos](http://php.net/strrpos).
	 *
	 *     $position = UTF8::strrpos($str, $search);
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 * @param   string  $str     Haystack
	 * @param   string  $search  Needle
	 * @param   int     $offset  Offset from which character in haystack to start searching
	 * @return  int|bool  Position of needle, FALSE if the needle is not found
	 * @uses    Kohana::$charset
	 * @uses    Kohana::find_file
	 */
	public static function strrpos($str, $search, $offset = 0)
	{
		if (UTF8::$server_utf8)
			return mb_strrpos($str, $search, $offset, Kohana::$charset);

		UTF8::_load(__FUNCTION__);

		return _strrpos($str, $search, $offset);
	}

	/**
	 * Returns part of a UTF-8 string.
	 * This is a UTF8-aware version of [substr](http://php.net/substr).
	 *
	 *     $sub = UTF8::substr($str, $offset);
	 *
	 * @author  Chris Smith <chris@jalakai.co.uk>
	 * @param   string  $str     Input string
	 * @param   int     $offset  Offset
	 * @param   int     $length  Length limit
	 * @return  string
	 * @uses    Kohana::$charset
	 * @uses    Kohana::find_file
	 */
	public static function substr($str, $offset, $length = NULL)
	{
		if (UTF8::$server_utf8)
		{
			return $length === NULL
				? mb_substr($str, $offset, mb_strlen($str), Kohana::$charset)
				: mb_substr($str, $offset, $length, Kohana::$charset);
		}

		UTF8::_load(__FUNCTION__);

		return _substr($str, $offset, $length);
	}

	/**
	 * Replaces text within a portion of a UTF-8 string.
	 * This is a UTF8-aware version of [substr_replace](http://php.net/substr_replace).
	 *
	 *     $str = UTF8::substr_replace($str, $replacement, $offset);
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 * @param   string  $str          Input string
	 * @param   string  $replacement  Replacement string
	 * @param   int     $offset       Offset
	 * @return  string
	 * @uses    Kohana::$charset
	 * @uses    Kohana::find_file
	 */
	public static function substr_replace($str, $replacement, $offset, $length = NULL)
	{
		UTF8::_load(__FUNCTION__);

		return _substr_replace($str, $replacement, $offset, $length);
	}

	/**
	 * Makes a UTF-8 string lowercase.
	 * This is a UTF8-aware version of [strtolower](http://php.net/strtolower).
	 *
	 *     $str = UTF8::strtolower($str);
	 *
	 * @author  Andreas Gohr <andi@splitbrain.org>
	 * @param   string  $str  Mixed case string
	 * @return  string
	 * @uses    Kohana::$charset
	 * @uses    Kohana::find_file
	 */
	public static function strtolower($str)
	{
		if (UTF8::$server_utf8)
			return mb_strtolower($str, Kohana::$charset);

		UTF8::_load(__FUNCTION__);

		return _strtolower($str);
	}

	/**
	 * Makes a UTF-8 string uppercase.
	 * This is a UTF8-aware version of [strtoupper](http://php.net/strtoupper).
	 *
	 * @author  Andreas Gohr <andi@splitbrain.org>
	 * @param   string  $str  Mixed case string
	 * @return  string
	 * @uses    Kohana::$charset
	 * @uses    Kohana::find_file
	 */
	public static function strtoupper($str)
	{
		if (UTF8::$server_utf8)
			return mb_strtoupper($str, Kohana::$charset);

		UTF8::_load(__FUNCTION__);

		return _strtoupper($str);
	}

	/**
	 * Makes a UTF-8 string's first character uppercase.
	 * This is a UTF8-aware version of [ucfirst](http://php.net/ucfirst).
	 *
	 *     $str = UTF8::ucfirst($str);
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 * @param   string  $str  Mixed case string
	 * @return  string
	 * @uses    Kohana::find_file
	 */
	public static function ucfirst($str)
	{
		UTF8::_load(__FUNCTION__);

		return _ucfirst($str);
	}

	/**
	 * Makes the first character of every word in a UTF-8 string uppercase.
	 * This is a UTF8-aware version of [ucwords](http://php.net/ucwords).
	 *
	 *     $str = UTF8::ucwords($str);
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 * @param   string  $str  mixed case string
	 * @return  string
	 * @uses    Kohana::find_file
	 */
	public static function ucwords($str)
	{
		UTF8::_load(__FUNCTION__);

		return _ucwords($str);
	}

	/**
	 * Case-insensitive UTF-8 string comparison.
	 * This is a UTF8-aware version of [strcasecmp](http://php.net/strcasecmp).
	 *
	 *     $compare = UTF8::strcasecmp($str1, $str2);
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 * @param   string  $str1  String to compare
	 * @param   string  $str2  String to compare
	 * @return  int  Less than 0 if str1 is less than str2
	 * @return  int  Greater than 0 if str1 is greater than str2
	 * @return  int  0 if they are equal
	 * @uses    Kohana::find_file
	 */
	public static function strcasecmp($str1, $str2)
	{
		UTF8::_load(__FUNCTION__);

		return _strcasecmp($str1, $str2);
	}

	/**
	 * Returns a string or an array with all occurrences of search in subject
	 * (ignoring case) and replaced with the given replace value.
	 * This is a UTF8-aware version of [str_ireplace](http://php.net/str_ireplace).
	 *
	 * [!!] This function is very slow compared to the native version. Avoid
	 * using it when possible.
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com
	 * @param   string|array  $search   Text to replace
	 * @param   string|array  $replace  Replacement text
	 * @param   string|array  $str      Subject text
	 * @param   integer       $count    Number of matched and replaced needles will be returned via this parameter which is passed by reference
	 * @return  string  If the input was a string
	 * @return  array   If the input was an array
	 * @uses    Kohana::find_file
	 */
	public static function str_ireplace($search, $replace, $str, & $count = NULL)
	{
		UTF8::_load(__FUNCTION__);

		return _str_ireplace($search, $replace, $str, $count);
	}

	/**
	 * Case-insensitive UTF-8 version of [stristr](http://php.net/stristr).
	 * Returns all of input string from the first occurrence of needle to the end.
	 *
	 *     $found = UTF8::stristr($str, $search);
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 * @param   string  $str     Input string
	 * @param   string  $search  Needle
	 * @return  string|bool  Matched substring if found, FALSE if the substring was not found
	 * @uses    Kohana::find_file
	 */
	public static function stristr($str, $search)
	{
		UTF8::_load(__FUNCTION__);

		return _stristr($str, $search);
	}

	/**
	 * Finds the length of the initial segment matching mask. 
	 * This is a UTF8-aware version of [strspn](http://php.net/strspn).
	 *
	 *     $found = UTF8::strspn($str, $mask);
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 * @param   string  $str     Input string
	 * @param   string  $mask    Mask for search
	 * @param   int     $offset  Start position of the string to examine
	 * @param   int     $length  Length of the string to examine
	 * @return  int  Length of the initial segment that contains characters in the mask
	 * @uses    Kohana::find_file
	 */
	public static function strspn($str, $mask, $offset = NULL, $length = NULL)
	{
		UTF8::_load(__FUNCTION__);

		return _strspn($str, $mask, $offset, $length);
	}

	/**
	 * Finds the length of the initial segment not matching mask. 
	 * This is a UTF8-aware version of [strcspn](http://php.net/strcspn).
	 *
	 *     $found = UTF8::strcspn($str, $mask);
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 * @param   string  $str     Input string
	 * @param   string  $mask    Mask for search
	 * @param   int     $offset  Start position of the string to examine
	 * @param   int     $length  Length of the string to examine
	 * @return  int  Length of the initial segment that contains characters not in the mask
	 * @uses    Kohana::find_file
	 */
	public static function strcspn($str, $mask, $offset = NULL, $length = NULL)
	{
		UTF8::_load(__FUNCTION__);

		return _strcspn($str, $mask, $offset, $length);
	}

	/**
	 * Pads a UTF-8 string to a certain length with another string.
	 * This is a UTF8-aware version of [str_pad](http://php.net/str_pad).
	 *
	 *     $str = UTF8::str_pad($str, $length);
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 * @param   string  $str               Input string
	 * @param   int     $final_str_length  Desired string length after padding
	 * @param   string  $pad_str           String to use as padding
	 * @param   string  $pad_type          Padding type: STR_PAD_RIGHT, STR_PAD_LEFT, or STR_PAD_BOTH
	 * @return  string
	 * @uses    Kohana::find_file
	 */
	public static function str_pad($str, $final_str_length, $pad_str = ' ', $pad_type = STR_PAD_RIGHT)
	{
		UTF8::_load(__FUNCTION__);

		return _str_pad($str, $final_str_length, $pad_str, $pad_type);
	}

	/**
	 * Converts a UTF-8 string to an array. 
	 * This is a UTF8-aware version of [str_split](http://php.net/str_split).
	 *
	 *     $array = UTF8::str_split($str);
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 * @param   string  $str           Input string
	 * @param   int     $split_length  Maximum length of each chunk
	 * @return  array
	 * @uses    Kohana::find_file
	 */
	public static function str_split($str, $split_length = 1)
	{
		UTF8::_load(__FUNCTION__);

		return _str_split($str, $split_length);
	}

	/**
	 * Reverses a UTF-8 string. 
	 * This is a UTF8-aware version of [strrev](http://php.net/strrev).
	 *
	 *     $str = UTF8::strrev($str);
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 * @param   string  $str  String to be reversed
	 * @return  string
	 * @uses    Kohana::find_file
	 */
	public static function strrev($str)
	{
		UTF8::_load(__FUNCTION__);

		return _strrev($str);
	}

	/**
	 * Strips whitespace (or other UTF-8 characters) from the beginning and
	 * end of a string. This is a UTF8-aware version of [trim](http://php.net/trim).
	 *
	 *     $str = UTF8::trim($str);
	 *
	 * @author  Andreas Gohr <andi@splitbrain.org>
	 * @param   string  $str       Input string
	 * @param   string  $charlist  String of characters to remove
	 * @return  string
	 * @uses    Kohana::find_file
	 */
	public static function trim($str, $charlist = NULL)
	{
		UTF8::_load(__FUNCTION__);

		return _trim($str, $charlist);
	}

	/**
	 * Strips whitespace (or other UTF-8 characters) from the beginning of a string.
	 * This is a UTF8-aware version of [ltrim](http://php.net/ltrim).
	 *
	 *     $str = UTF8::ltrim($str);
	 *
	 * @author  Andreas Gohr <andi@splitbrain.org>
	 * @param   string  $str       Input string
	 * @param   string  $charlist  String of characters to remove
	 * @return  string
	 * @uses    Kohana::find_file
	 */
	public static function ltrim($str, $charlist = NULL)
	{
		UTF8::_load(__FUNCTION__);

		return _ltrim($str, $charlist);
	}

	/**
	 * Strips whitespace (or other UTF-8 characters) from the end of a string.
	 * This is a UTF8-aware version of [rtrim](http://php.net/rtrim).
	 *
	 *     $str = UTF8::rtrim($str);
	 *
	 * @author  Andreas Gohr <andi@splitbrain.org>
	 * @param   string  $str       Input string
	 * @param   string  $charlist  String of characters to remove
	 * @return  string
	 * @uses    Kohana::find_file
	 */
	public static function rtrim($str, $charlist = NULL)
	{
		UTF8::_load(__FUNCTION__);

		return _rtrim($str, $charlist);
	}

	/**
	 * Returns the unicode ordinal for a character.
	 * This is a UTF8-aware version of [ord](http://php.net/ord).
	 *
	 *     $digit = UTF8::ord($character);
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 * @param   string  $chr  UTF-8 encoded character
	 * @return  int
	 * @uses    Kohana::find_file
	 */
	public static function ord($chr)
	{
		UTF8::_load(__FUNCTION__);

		return _ord($chr);
	}

	/**
	 * Takes an UTF-8 string and returns an array of ints representing the Unicode characters.
	 * Astral planes are supported i.e. the ints in the output can be > 0xFFFF.
	 * Occurrences of the BOM are ignored. Surrogates are not allowed.
	 *
	 *     $array = UTF8::to_unicode($str);
	 *
	 * The Original Code is Mozilla Communicator client code.
	 * The Initial Developer of the Original Code is Netscape Communications Corporation.
	 * Portions created by the Initial Developer are Copyright (C) 1998 the Initial Developer.
	 * Ported to PHP by Henri Sivonen <hsivonen@iki.fi>, see <http://hsivonen.iki.fi/php-utf8/>
	 * Slight modifications to fit with phputf8 library by Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string  $str  UTF-8 encoded string
	 * @return  array|bool  Unicode code points, FALSE if the string is invalid
	 * @uses    Kohana::find_file
	 */
	public static function to_unicode($str)
	{
		UTF8::_load(__FUNCTION__);

		return _to_unicode($str);
	}

	/**
	 * Takes an array of ints representing the Unicode characters and returns a UTF-8 string.
	 * Astral planes are supported i.e. the ints in the input can be > 0xFFFF.
	 * Occurrences of the BOM are ignored. Surrogates are not allowed.
	 *
	 *     $str = UTF8::to_unicode($array);
	 *
	 * The Original Code is Mozilla Communicator client code.
	 * The Initial Developer of the Original Code is Netscape Communications Corporation.
	 * Portions created by the Initial Developer are Copyright (C) 1998 the Initial Developer.
	 * Ported to PHP by Henri Sivonen <hsivonen@iki.fi>, see http://hsivonen.iki.fi/php-utf8/
	 * Slight modifications to fit with phputf8 library by Harry Fuecks <hfuecks@gmail.com>.
	 *
	 * @param   array   $str  Unicode code points representing a string
	 * @return  string  utf8  String of characters
	 * @return  bool  FALSE if a code point cannot be found
	 * @uses    Kohana::find_file
	 */
	public static function from_unicode($arr)
	{
		UTF8::_load(__FUNCTION__);

		return _from_unicode($arr);
	}

}

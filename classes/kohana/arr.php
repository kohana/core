<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Array helper.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Kohana_Arr {

	/**
	 * Tests if an array is associative or or not.
	 *
	 * @param   array   array to check
	 * @return  boolean
	 */
	public static function is_assoc(array $array)
	{
		// Keys of the array
		$keys = array_keys($array);

		// If the array keys of the keys match the keys, then the array must
		// be associative.
		return array_keys($keys) !== $keys;
	}

	/**
	 * Gets a value from an array using a dot separated path.
	 *
	 *     // Get the value of $array['foo']['bar']
	 *     $value = Arr::path($array, 'foo.bar');
	 *
	 * @param   array   array to search
	 * @param   string  key path, dot separated
	 * @param   mixed   default value if the path is not set
	 * @return  mixed
	 */
	public static function path($array, $path, $default = NULL)
	{
		// Split the keys by slashes
		$keys = explode('.', $path);

		do
		{
			$key = array_shift($keys);

			if (ctype_digit($key))
			{
				// Make the key an integer
				$key = (int) $key;
			}

			if (isset($array[$key]))
			{
				if ($keys)
				{
					if (is_array($array[$key]))
					{
						// Dig down into the next part of the path
						$array = $array[$key];
					}
					else
					{
						// Unable to dig deeper
						break;
					}
				}
				else
				{
					// Found the path requested
					return $array[$key];
				}
			}
			else
			{
				// Unable to dig deeper
				break;
			}
		}
		while ($keys);

		// Unable to find the value requested
		return $default;
	}

	/**
	 * Fill an array with a range of numbers.
	 *
	 * @param   integer  stepping
	 * @param   integer  ending number
	 * @return  array
	 */
	public static function range($step = 10, $max = 100)
	{
		if ($step < 1)
			return array();

		$array = array();
		for ($i = $step; $i <= $max; $i += $step)
		{
			$array[$i] = $i;
		}

		return $array;
	}

	/**
	 * Retreive a single key from an array. If the key does not exist in the
	 * array, the default value will be returned instead.
	 *
	 * @param   array   array to extract from
	 * @param   string  key name
	 * @param   mixed   default value
	 * @return  mixed
	 */
	public static function get(array $array, $key, $default = NULL)
	{
		return isset($array[$key]) ? $array[$key] : $default;
	}

	/**
	 * Retrieves multiple keys from an array. If the key does not exist in the
	 * array, the default value will be added instead.
	 *
	 * @param   array   array to extract keys from
	 * @param   array   list of key names
	 * @param   mixed   default value
	 * @return  array
	 */
	public static function extract(array $array, array $keys, $default = NULL)
	{
		$found = array();
		foreach ($keys as $key)
		{
			$found[$key] = isset($array[$key]) ? $array[$key] : $default;
		}

		return $found;
	}

	/**
	 * Binary search algorithm.
	 *
	 * @param   mixed    the value to search for
	 * @param   array    an array of values to search in
	 * @param   boolean  return false, or the nearest value
	 * @param   mixed    sort the array before searching it
	 * @return  integer
	 */
	public static function binary_search($needle, $haystack, $nearest = FALSE, $sort = FALSE)
	{
		if ($sort === TRUE)
		{
			sort($haystack);
		}

		$high = count($haystack);
		$low = 0;

		while ($high - $low > 1)
		{
			$probe = ($high + $low) / 2;
			if ($haystack[$probe] < $needle)
			{
				$low = $probe;
			}
			else
			{
				$high = $probe;
			}
		}

		if ($high == count($haystack) OR $haystack[$high] != $needle)
		{
			if ($nearest === FALSE)
				return FALSE;

			// return the nearest value
			$high_distance = $haystack[ceil($low)] - $needle;
			$low_distance = $needle - $haystack[floor($low)];

			return ($high_distance >= $low_distance) ? $haystack[ceil($low)] : $haystack[floor($low)];
		}

		return $high;
	}

	/**
	 * Adds a value to the beginning of an associative array.
	 *
	 * @param   array   array to modify
	 * @param   string  array key name
	 * @param   mixed   array value
	 * @return  array
	 */
	public static function unshift( array & $array, $key, $val)
	{
		$array = array_reverse($array, TRUE);
		$array[$key] = $val;
		$array = array_reverse($array, TRUE);

		return $array;
	}

	/**
	 * Merges one or more arrays recursively and preserves all keys.
	 * Note that this does not work the same the PHP function array_merge_recursive()!
	 *
	 * @param   array  initial array
	 * @param   array  array to merge
	 * @param   array  ...
	 * @return  array
	 */
	public static function merge(array $a1)
	{
		$result = array();
		for ($i = 0, $total = func_num_args(); $i < $total; $i++)
		{
			foreach (func_get_arg($i) as $key => $val)
			{
				if (isset($result[$key]))
				{
					if (is_array($val))
					{
						// Arrays are merged recursively
						$result[$key] = Arr::merge($result[$key], $val);
					}
					elseif (is_int($key))
					{
						// Indexed arrays are appended
						array_push($result, $val);
					}
					else
					{
						// Associative arrays are replaced
						$result[$key] = $val;
					}
				}
				else
				{
					// New values are added
					$result[$key] = $val;
				}
			}
		}

		return $result;
	}

	/**
	 * Overwrites an array with values from input array(s).
	 * Non-existing keys will not be appended!
	 *
	 * @param   array   key array
	 * @param   array   input array(s) that will overwrite key array values
	 * @return  array
	 */
	public static function overwrite($array1, $array2)
	{
		foreach (array_intersect_key($array2, $array1) as $key => $value)
		{
			$array1[$key] = $value;
		}

		if (func_num_args() > 2)
		{
			foreach (array_slice(func_get_args(), 2) as $array2)
			{
				foreach (array_intersect_key($array2, $array1) as $key => $value)
				{
					$array1[$key] = $value;
				}
			}
		}

		return $array1;
	}

	/**
	 * Creates a callable function and parameter list from a string representation.
	 * Note that this function does not validate the callback string.
	 *
	 *     // Get the callback function and parameters
	 *     list($func, $params) = Arr::callback('Foo::bar(apple,orange)');
	 *
	 *     // Get the result of the callback
	 *     $result = call_user_func_array($func, $params);
	 *
	 * @param   string  callback string
	 * @return  array   function, params
	 */
	public static function callback($str)
	{
		// Overloaded as parts are found
		$command = $params = NULL;

		// command[param,param]
		if (preg_match('/^([^\(]*+)\((.*)\)$/', $str, $match))
		{
			// command
			$command = $match[1];

			if ($match[2] !== '')
			{
				// param,param
				$params = preg_split('/(?<!\\\\),/', $match[2]);
				$params = str_replace('\,', ',', $params);
			}
		}
		else
		{
			// command
			$command = $str;
		}

		if (strpos($command, '::') !== FALSE)
		{
			// Create a static method callable command
			$command = explode('::', $command, 2);
		}

		return array($command, $params);
	}

	final private function __construct()
	{
		// This is a static class
	}

} // End arr

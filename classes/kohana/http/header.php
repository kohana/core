<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 
 *
 * @package    Kohana
 * @category   Http
 * @author     Kohana Team
 * @since      3.1.0
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Http_Header extends ArrayObject {

	/**
	 * @var     boolean   controls whether to automatically sort headers by quality value
	 */
	public static $sort_by_quality = FALSE;

	/**
	 * @var     array     default positive filter for sorting header values 
	 */
	public static $default_sort_filter = array('accept','accept-charset','accept-encoding','accept-language');

	/**
	 * Parses Http Header values and creating an appropriate object
	 * depending on type; i.e. accept-type, accept-char, cache-control etc.
	 *
	 * @param   string   values to parse
	 * @return  array
	 */
	public static function parse_header_values(array $header_values)
	{
		// Controls whether commas are allowed in the header string, e.g. user-agent should allow commas
		$header_commas_allowed = array('user-agent');

		// Foreach of the header values applied
		foreach ($header_values as $key => $value)
		{
			if (is_array($value))
			{
				$values = array();

				foreach ($value as $k => $v)
				{
					$values[] = Http_Header::parse_header_values($v);
				}
				$header_values[$key] = $values;
				continue;
			}

			// If the key allows commas or no commas are found
			if (in_array($key, $header_commas_allowed) or strpos($value, ',') === FALSE)
			{
				$header_value[$key] = new Http_Header_Value($value);

				// Move to next header
				continue;
			}

			// Create an array of the values and clear any whitespace
			$value = array_map('trim', explode(',', $value));

			$parsed_values = array();

			// Foreach value
			foreach ($value as $v)
			{
				$v = new Http_Header_Value($v);

				// Convert the value string into an object
				if ($v->key === NULL)
				{
					$parsed_values[] = $v;
				}
				else
				{
					$parsed_values[$v->key] = $v;
				}
			}

			// Apply parsed value to the header
			$header_values[$key] = $parsed_values;
		}

		// Return the parsed header values
		return $header_values;
	}

	/**
	 * Constructor method for Kohana_Http_Header.
	 *
	 * @param   mixed    input array
	 * @param   int      flags
	 * @param   string   the iterator class to use
	 */
	public function __construct($input, $flags, $iterator_class)
	{
		/**
		 * @see http://www.w3.org/Protocols/rfc2616/rfc2616.html
		 * 
		 * HTTP header declarations should be treated as case-insensitive
		 */
		$input = array_change_key_case($input, CASE_LOWER);

		// Parse the values into [Http_Header_Values]
		parent::__construct(Http_Header::parse_header_values($input), $flags, $iterator_class);

		// If sort by quality is set, sort the fields by q=0.0 value
		if (Http_Header::$sort_by_quality)
		{
			$this->sort_values_by_quality();
		}
	}

	/**
	 * Sort the headers by quality property if the header matches the
	 * [Kohana_Http_Header::$default_sort_filter] definition.
	 * 
	 * #### Default sort values
	 * 
	 *  - Accept
	 *  - Accept-Chars
	 *  - Accept-Encoding
	 *  - Accept-Lang
	 *
	 * @param   array    header fields to parse
	 * @return  self
	 */
	public function sort_values_by_quality(array $filter = NULL)
	{
		// If a filter argument is supplied
		if ($filter)
		{
			// Apply filter and store previous
			$previous_filter = Http_Header::$default_sort_filter;
			Http_Header::$default_sort_filter = $filter;
		}

		// Get a copy of this ArrayObject
		$values = $this->getArrayCopy();

		// Sort the values of the header
		$sorted_values = array_map(array($this, '_sort_values'), $values, array_keys($values));

		// Return filter to previous state if required
		if ($filter)
		{
			Http_Header::$default_sort_filter = $previous_filter;
		}

		// Exchange the array for the sorted values
		$this->exchangeArray($sorted_values);

		// Return this
		return $this;
	}

	/**
	 * Sort the values supplied in the argument
	 *
	 * @param   mixed    value to sort
	 * @param   mixed    key identifier
	 * @return  array|Kohana_Http_Value
	 * @throws  Kohana_Http_Exception
	 */
	protected function _sort_values($value, $key)
	{
		if ( ! is_array($value) or ! in_array($key, Http_Header::$default_sort_filter))
		{
			return $value;
		}

		// Get the current value
		$current = current($value);

		// If the values are header values
		if ($current instanceof Kohana_Http_Header_Value)
		{
			// Sort them by comparisom
			uasort($value, array($this, '_compare'));
		}
		// If the values is an array
		else if (is_array($current))
		{
			$value = array_map(array($this, '_sort_values'), $value, array_keys($value));
		}
		else
		{
			// Unknown value type
			throw new Kohana_Http_Exception(__METHOD__.' unknown values type: :type', array(':type' => gettype($values)));
		}

		// Return parsed values
		return $value;
	}

	/**
	 * Provides sorting logic for `uksort()` by quality in [Kohana_Http_Header::_sort_values]
	 *
	 * @param   Kohana_Http_Header_Value   first value to compare
	 * @param   Kohana_Http_Header_Value   second value to compare
	 * @return  int
	 */
	protected function _compare($value_a, $value_b)
	{
		// Test for correct instance type
		if ( ! $value_a instanceof Kohana_Http_Header_Value or ! $value_b instanceof Kohana_Http_Header_Value)
		{
			// Return neutral if cannot test value
			return 0;
		}

		// Extract the qualities
		$a = (float) Arr::get($value_a->properties, 'q', Http_Header_Value::$default_quality);
		$b = (float) Arr::get($value_b->properties, 'q', Http_Header_Value::$default_quality);

		// If a == b
		if ($a == $b)
		{
			return (int) 0; // Return neutral (0)
		}
		// If a < b
		else if ($a < $b)
		{
			return (int) -1; // Return negative (-1)
		}
		// If a > b
		else if ($a > $b)
		{
			return (int) 1; // Return positive (1)
		}
	}
}
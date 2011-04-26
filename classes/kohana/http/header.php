<?php defined('SYSPATH') or die('No direct script access.');
/**
 * The Kohana_HTTP_Header class provides an Object-Orientated interface
 * to HTTP headers. This can parse header arrays returned from the
 * PHP functions `apache_request_headers()` or the `http_parse_headers()`
 * function available within the PECL HTTP library.
 *
 * @package    Kohana
 * @category   HTTP
 * @author     Kohana Team
 * @since      3.1.0
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_HTTP_Header extends ArrayObject {

	/**
	 * @var     boolean   Controls whether to automatically sort headers by quality value
	 */
	public static $sort_by_quality = FALSE;

	/**
	 * @var     array     Default positive filter for sorting header values
	 */
	public static $default_sort_filter = array('accept','accept-charset','accept-encoding','accept-language');

	/**
	 * Parses HTTP Header values and creating an appropriate object
	 * depending on type; i.e. accept-type, accept-char, cache-control etc.
	 *
	 *     $header_values_array = HTTP_Header::parse_header_values(array('cache-control' => 'max-age=200; public'));
	 *
	 * @param   array    $header_values          Values to parse
	 * @param   array    $header_commas_allowed  Header values where commas are not delimiters (usually date)
	 * @return  array
	 */
	public static function parse_header_values(array $header_values, array $header_commas_allowed = array('user-agent', 'date', 'expires', 'last-modified'))
	{
		/**
		 * @see http://www.w3.org/Protocols/rfc2616/rfc2616.html
		 *
		 * HTTP header declarations should be treated as case-insensitive
		 */
		$header_values = array_change_key_case($header_values, CASE_LOWER);

		// Foreach of the header values applied
		foreach ($header_values as $key => $value)
		{
			if (is_array($value))
			{
				$values = array();

				if (Arr::is_assoc($value))
				{

					foreach ($value as $k => $v)
					{
						$values[] = HTTP_Header::parse_header_values($v);
					}
				}
				else
				{
					// RFC 2616 allows multiple headers with same name if they can be
					// concatinated using commas without altering the original message.
					// This usually occurs with multiple Set-Cookie: headers
					$array = array();
					foreach ($value as $k => $v)
					{
						// Break value into component parts
						$v = explode(';', $v);

						// Do some nasty parsing to flattern the array into components,
						// parsing key values
						$array = Arr::flatten(array_map('HTTP_Header_Value::parse_key_value', $v));

						// Get the K/V component and extract the first element
						$key_value_component = array_slice($array, 0, 1, TRUE);
						array_shift($array);

						// Create the HTTP_Header_Value component array
						$http_header['key']        = key($key_value_component);
						$http_header['value']      = current($key_value_component);
						$http_header['properties'] = $array;

						// Create the HTTP_Header_Value
						$values[] = new HTTP_Header_Value($http_header);
					}
				}

				// Assign HTTP_Header_Value array to the header
				$header_values[$key] = $values;
				continue;
			}

			// If the key allows commas or no commas are found
			if (in_array($key, $header_commas_allowed) or (strpos($value, ',') === FALSE))
			{
				// If the key is user-agent, we don't want to parse the string
				if ($key === 'user-agent')
				{
					$header_values[$key] = new HTTP_Header_Value($value, TRUE);
				}
				// Else, behave normally
				else
				{
					$header_values[$key] = new HTTP_Header_Value($value);
				}

				// Move to next header
				continue;
			}

			// Create an array of the values and clear any whitespace
			$value = array_map('trim', explode(',', $value));

			$parsed_values = array();

			// Foreach value
			foreach ($value as $v)
			{
				$v = new HTTP_Header_Value($v);

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
	 * Constructor method for [Kohana_HTTP_Header]. Uses the standard constructor
	 * of the parent `ArrayObject` class.
	 *
	 *     $header_object = new HTTP_Header(array('x-powered-by' => 'Kohana 3.1.x', 'expires' => '...'));
	 *
	 * @param   mixed    Input array
	 * @param   int      Flags
	 * @param   string   The iterator class to use
	 */
	public function __construct($input, $flags = NULL, $iterator_class = 'ArrayIterator')
	{
		// Parse the values into [HTTP_Header_Values]
		parent::__construct(HTTP_Header::parse_header_values($input), $flags, $iterator_class);

		// If sort by quality is set, sort the fields by q=0.0 value
		if (HTTP_Header::$sort_by_quality)
		{
			$this->sort_values_by_quality();
		}
	}

	/**
	 * Returns the header object as a string, including
	 * the terminating new line
	 *
	 *     // Return the header as a string
	 *     echo (string) $request->headers();
	 *
	 * @return  string
	 */
	public function __toString()
	{
		$header = '';

		foreach ($this as $key => $value)
		{
			if (is_array($value))
			{
				$header .= $key.': '.(implode(', ', $value))."\r\n";
			}
			else
			{
				$header .= $key.': '.$value."\r\n";
			}
		}

		return $header."\n";
	}

	/**
	 * Overloads the `ArrayObject::exchangeArray()` method to ensure all
	 * values passed are parsed correctly into a [Kohana_HTTP_Header_Value].
	 *
	 *     // Input new headers
	 *     $headers->exchangeArray(array(
	 *          'date'          => 'Wed, 24 Nov 2010 21:09:23 GMT',
	 *          'cache-control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0'
	 *     ));
	 *
	 * @param   array    $array Array to exchange
	 * @return  array
	 */
	public function exchangeArray($array)
	{
		return parent::exchangeArray(HTTP_Header::parse_header_values($array));
	}

	/**
	 * Overloads the `ArrayObject::offsetSet` method to ensure any
	 * access is correctly converted to the correct object type.
	 *
	 *     // Add a new header from encoded string
	 *     $headers['cache-control'] = 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0'
	 *
	 * @param   mixed    $index   Key
	 * @param   mixed    $newval  Value
	 * @return  void
	 */
	public function offsetSet($index, $newval)
	{
		if (is_array($newval) AND (current($newval) instanceof HTTP_Header_Value))
			return parent::offsetSet(strtolower($index), $newval);
		elseif ( ! $newval instanceof HTTP_Header_Value)
		{
			$newval = new HTTP_Header_Value($newval);
		}

		parent::offsetSet(strtolower($index), $newval);
	}

	/**
	 * Sort the headers by quality property if the header matches the
	 * [Kohana_HTTP_Header::$default_sort_filter] definition.
	 *
	 * #### Default sort values
	 *
	 *  - Accept
	 *  - Accept-Chars
	 *  - Accept-Encoding
	 *  - Accept-Lang
	 *
	 * @param   array    $filter  Header fields to parse
	 * @return  self
	 */
	public function sort_values_by_quality(array $filter = array())
	{
		// If a filter argument is supplied
		if ($filter)
		{
			// Apply filter and store previous
			$previous_filter = HTTP_Header::$default_sort_filter;
			HTTP_Header::$default_sort_filter = $filter;
		}

		// Get a copy of this ArrayObject
		$values = $this->getArrayCopy();

		foreach ($values as $key => $value)
		{
			if ( ! is_array($value) or ! in_array($key, HTTP_Header::$default_sort_filter))
			{
				unset($values[$key]);
				continue;
			}

			// Sort them by comparison
			uasort($value, array($this, '_sort_by_comparison'));

			$values[$key] = $value;
		}

		// Return filter to previous state if required
		if ($filter)
		{
			HTTP_Header::$default_sort_filter = $previous_filter;
		}

		foreach ($values as $key => $value)
		{
			$this[$key] = $value;
		}

		// Return this
		return $this;
	}

	protected function _sort_by_comparison($value_a, $value_b)
	{
		// Test for correct instance type
		if ( ! $value_a instanceof HTTP_Header_Value OR ! $value_b instanceof HTTP_Header_Value)
		{
			// Return neutral if cannot test value
			return 0;
		}

		// Extract the qualities
		$a = (float) Arr::get($value_a->properties, 'q', HTTP_Header_Value::$default_quality);
		$b = (float) Arr::get($value_b->properties, 'q', HTTP_Header_Value::$default_quality);

		if ($a == $b)
			return 0;
		elseif ($a < $b)
			return 1;
		elseif ($a > $b)
			return -1;
	}

} // End Kohana_HTTP_Header
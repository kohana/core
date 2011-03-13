<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana_HTTP_Header_Value represents a value assigned to an HTTP header, i.e.
 *
 *      Accept: [key=]value[; property[=property_value][; ...]]
 *
 * Values are either single values,
 *
 * @package    Kohana
 * @category   HTTP
 * @author     Kohana Team
 * @since      3.1.0
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_HTTP_Header_Value {

	/**
	 * @var     float    The default quality header property value
	 */
	public static $default_quality = 1.0;

	/**
	 * Detects and returns key/value pairs
	 *
	 * @param   string   $string String to parse
	 * @param   string   $separator
	 * @return  array
	 */
	public static function parse_key_value($string, $separator = '=')
	{
		$parts = explode($separator, trim($string), 2);

		if (count($parts) == 1)
		{
			return $parts;
		}
		else
		{
			return array($parts[0] => $parts[1]);
		}
	}

	/**
	 * @var     array
	 */
	public $properties = array();

	/**
	 * @var     void|string
	 */
	public $key;

	/**
	 * @var     array
	 */
	public $value = array();

	/**
	 * Builds the header field
	 *
	 * @param   mixed    value  configuration array passed
	 * @param   boolean  no_parse  skip parsing of the string (i.e. user-agent)
	 * @throws  Kohana_HTTP_Exception
	 */
	public function __construct($value, $no_parse = FALSE)
	{
		// If no parse is set, set the value and get out of here (user-agent)
		if ($no_parse)
		{
			$this->key = NULL;
			$this->value = $value;
			return;
		}

		// If configuration array passed
		if (is_array($value))
		{
			// Parse each value
			foreach ($value as $k => $v)
			{
				// If the key is a property
				if (property_exists($this, $k))
				{
					// Map values
					$this->$k = $v;
				}
			}

		}
		// If value is a string
		elseif (is_string($value))
		{
			// Detect properties
			if (strpos($value, ';') !== FALSE)
			{
				// Remove properties from the string
				$parts = explode(';', $value);
				$value = array_shift($parts);

				// Parse the properties
				$properties = array();

				// Foreach part
				foreach ($parts as $part)
				{
					// Merge the parsed values
					$properties = array_merge(HTTP_Header_Value::parse_key_value($part), $properties);
				}

				// Apply the parsed values
				$this->properties = $properties;
			}

			// Parse the value and get key
			$value = HTTP_Header_Value::parse_key_value($value);
			$key = key($value);

			// If the key is a string
			if (is_string($key))
			{
				// Apply the key as a property
				$this->key = $key;
			}

			// Apply the value
			$this->value = current($value);
		}
		// Unrecognised value type
		else
		{
			throw new HTTP_Exception_500(__METHOD__.' unknown header value type: :type. array or string allowed.', array(':type' => gettype($value)));
		}
	}

	/**
	 * Provides direct access to the key of this header value
	 *
	 * @param   string   $key  Key value to set
	 * @return  mixed
	 */
	public function key($key = NULL)
	{
		if ($key === NULL)
		{
			return $this->key;
		}
		else
		{
			$this->key = $key;
			return $this;
		}
	}

	/**
	 * Provides direct access to the value of this header value
	 *
	 * @param   string   $value Value to set
	 * @return  mixed
	 */
	public function value($value = NULL)
	{
		if ($value === NULL)
		{
			return $this->value;
		}
		else
		{
			$this->value = $value;
			return $this;
		}
	}

	/**
	 * Provides direct access to the properties of this header value
	 *
	 * @param   array    $properties Properties to set to this value
	 * @return  mixed
	 */
	public function properties(array $properties = array())
	{
		if ( ! $properties)
		{
			return $this->properties;
		}
		else
		{
			$this->properties = $properties;
			return $this;
		}
	}

	/**
	 * Magic method to handle object being cast to
	 * string. Produces the following header value syntax
	 *
	 *      [key=]value[; property[=property_value][; ... ]]
	 *
	 * @return  string
	 */
	public function __toString()
	{

		$string = ($this->key !== NULL) ? ($this->key.'='.$this->value) : $this->value;

		if ($this->properties)
		{
			$props = array($string);
			foreach ($this->properties as $k => $v)
			{
				$props[] = is_int($k) ? $v : ($k.'='.$v);
			}
			$string = implode('; ', $props);
		}

		return $string;
	}

} // End Kohana_HTTP_Header_Value
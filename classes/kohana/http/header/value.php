<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana_Http_Header_Value represents a value assigned to an HTTP header, i.e.
 * 
 *      Accept: [key=]value[; property[=property_value][; ...]]
 * 
 * Values are either single values, 
 *
 * @package    Kohana
 * @category   Http
 * @author     Kohana Team
 * @since      3.1.0
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Http_Header_Value {

	/**
	 * @var     float    the default quality header property value
	 */
	public static $default_quality = 1.0;

	/**
	 * Detects and returns key/value pairs
	 *
	 * @param   string   string to parse
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
	 * @param   string|array   value string|values
	 * @throws  Kohana_Http_Exception
	 */
	public function __construct($value)
	{
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
		else if (is_string($value))
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
					$properties = array_merge(Http_Header_Value::parse_key_value($part), $properties);
				}

				// Apply the parsed values
				$this->properties = $properties;
			}

			// Parse the value and get key
			$value = Http_Header_Value::parse_key_value($value);
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
			throw new Kohana_Http_Exception(__METHOD__.' unknown header value type: :type. array or string allowed.', array(':type' => gettype($value)));
		}
	}

	/**
	 * Provides direct access to the key of this header value
	 *
	 * @param   string   key value to set
	 * @return  voic|string|self
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
	 * @param   string   value to set
	 * @return  string|self
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
	 * @param   array    properties to set to this value
	 * @return  array
	 */
	public function properties(array $properties = NULL)
	{
		if ($properties === NULL)
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

		$string = ($this->key === NULL) ? $this->key.'='.$this->$value : $this->$value;

		if ($this->properties)
		{
			$props = array($string);
			foreach ($this->_properties as $k => $v)
			{
				$props[] = is_int($k) ? $v : $k.'='.$v;
			}
			$string = implode('; ', $props);
		}

		return $string;
	}

} // End Http_Header_Field
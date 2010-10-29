<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Array and variable validation.
 *
 * @package    Kohana
 * @category   Security
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Validate extends ArrayObject {

	/**
	 * Creates a new Validation instance.
	 *
	 * @param   array   array to use for validation
	 * @return  Validate
	 */
	public static function factory(array $array)
	{
		return new Validate($array);
	}

	/**
	 * Checks if a field is not empty.
	 *
	 * @return  boolean
	 */
	public static function not_empty($value)
	{
		if (is_object($value) AND $value instanceof ArrayObject)
		{
			// Get the array from the ArrayObject
			$value = $value->getArrayCopy();
		}

		// Value cannot be NULL, FALSE, '', or an empty array
		return ! in_array($value, array(NULL, FALSE, '', array()), TRUE);
	}

	/**
	 * Checks a field against a regular expression.
	 *
	 * @param   string  value
	 * @param   string  regular expression to match (including delimiters)
	 * @return  boolean
	 */
	public static function regex($value, $expression)
	{
		return (bool) preg_match($expression, (string) $value);
	}

	/**
	 * Checks that a field is long enough.
	 *
	 * @param   string   value
	 * @param   integer  minimum length required
	 * @return  boolean
	 */
	public static function min_length($value, $length)
	{
		return UTF8::strlen($value) >= $length;
	}

	/**
	 * Checks that a field is short enough.
	 *
	 * @param   string   value
	 * @param   integer  maximum length required
	 * @return  boolean
	 */
	public static function max_length($value, $length)
	{
		return UTF8::strlen($value) <= $length;
	}

	/**
	 * Checks that a field is exactly the right length.
	 *
	 * @param   string   value
	 * @param   integer  exact length required
	 * @return  boolean
	 */
	public static function exact_length($value, $length)
	{
		return UTF8::strlen($value) === $length;
	}

	/**
	 * Check an email address for correct format.
	 *
	 * @link  http://www.iamcal.com/publish/articles/php/parsing_email/
	 * @link  http://www.w3.org/Protocols/rfc822/
	 *
	 * @param   string   email address
	 * @param   boolean  strict RFC compatibility
	 * @return  boolean
	 */
	public static function email($email, $strict = FALSE)
	{
		if ($strict === TRUE)
		{
			$qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
			$dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
			$atom  = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
			$pair  = '\\x5c[\\x00-\\x7f]';

			$domain_literal = "\\x5b($dtext|$pair)*\\x5d";
			$quoted_string  = "\\x22($qtext|$pair)*\\x22";
			$sub_domain     = "($atom|$domain_literal)";
			$word           = "($atom|$quoted_string)";
			$domain         = "$sub_domain(\\x2e$sub_domain)*";
			$local_part     = "$word(\\x2e$word)*";

			$expression     = "/^$local_part\\x40$domain$/D";
		}
		else
		{
			$expression = '/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD';
		}

		return (bool) preg_match($expression, (string) $email);
	}

	/**
	 * Validate the domain of an email address by checking if the domain has a
	 * valid MX record.
	 *
	 * @link  http://php.net/checkdnsrr  not added to Windows until PHP 5.3.0
	 *
	 * @param   string   email address
	 * @return  boolean
	 */
	public static function email_domain($email)
	{
		// Check if the email domain has a valid MX record
		return (bool) checkdnsrr(preg_replace('/^[^@]++@/', '', $email), 'MX');
	}

	/**
	 * Validate a URL.
	 *
	 * @param   string   URL
	 * @return  boolean
	 */
	public static function url($url)
	{
		// Based on http://www.apps.ietf.org/rfc/rfc1738.html#sec-5
		if ( ! preg_match(
			'~^

			# scheme
			[-a-z0-9+.]++://

			# username:password (optional)
			(?:
				    [-a-z0-9$_.+!*\'(),;?&=%]++   # username
				(?::[-a-z0-9$_.+!*\'(),;?&=%]++)? # password (optional)
				@
			)?

			(?:
				# ip address
				\d{1,3}+(?:\.\d{1,3}+){3}+

				| # or

				# hostname (captured)
				(
					     (?!-)[-a-z0-9]{1,63}+(?<!-)
					(?:\.(?!-)[-a-z0-9]{1,63}+(?<!-)){0,126}+
				)
			)

			# port (optional)
			(?::\d{1,5}+)?

			# path (optional)
			(?:/.*)?

			$~iDx', $url, $matches))
			return FALSE;

		// We matched an IP address
		if ( ! isset($matches[1]))
			return TRUE;

		// Check maximum length of the whole hostname
		// http://en.wikipedia.org/wiki/Domain_name#cite_note-0
		if (strlen($matches[1]) > 253)
			return FALSE;

		// An extra check for the top level domain
		// It must start with a letter
		$tld = ltrim(substr($matches[1], (int) strrpos($matches[1], '.')), '.');
		return ctype_alpha($tld[0]);
	}

	/**
	 * Validate an IP.
	 *
	 * @param   string   IP address
	 * @param   boolean  allow private IP networks
	 * @return  boolean
	 */
	public static function ip($ip, $allow_private = TRUE)
	{
		// Do not allow reserved addresses
		$flags = FILTER_FLAG_NO_RES_RANGE;

		if ($allow_private === FALSE)
		{
			// Do not allow private or reserved addresses
			$flags = $flags | FILTER_FLAG_NO_PRIV_RANGE;
		}

		return (bool) filter_var($ip, FILTER_VALIDATE_IP, $flags);
	}

	/**
	 * Validates a credit card number, with a Luhn check if possible.
	 *
	 * @param   integer       credit card number
	 * @param   string|array  card type, or an array of card types
	 * @return  boolean
	 * @uses    Validate::luhn
	 */
	public static function credit_card($number, $type = NULL)
	{
		// Remove all non-digit characters from the number
		if (($number = preg_replace('/\D+/', '', $number)) === '')
			return FALSE;

		if ($type == NULL)
		{
			// Use the default type
			$type = 'default';
		}
		elseif (is_array($type))
		{
			foreach ($type as $t)
			{
				// Test each type for validity
				if (Validate::credit_card($number, $t))
					return TRUE;
			}

			return FALSE;
		}

		$cards = Kohana::config('credit_cards');

		// Check card type
		$type = strtolower($type);

		if ( ! isset($cards[$type]))
			return FALSE;

		// Check card number length
		$length = strlen($number);

		// Validate the card length by the card type
		if ( ! in_array($length, preg_split('/\D+/', $cards[$type]['length'])))
			return FALSE;

		// Check card number prefix
		if ( ! preg_match('/^'.$cards[$type]['prefix'].'/', $number))
			return FALSE;

		// No Luhn check required
		if ($cards[$type]['luhn'] == FALSE)
			return TRUE;

		return Validate::luhn($number);
	}

	/**
	 * Validate a number against the [Luhn](http://en.wikipedia.org/wiki/Luhn_algorithm)
	 * (mod10) formula.
	 *
	 * @param   string   number to check
	 * @return  boolean
	 */
	public static function luhn($number)
	{
		// Force the value to be a string as this method uses string functions.
		// Converting to an integer may pass PHP_INT_MAX and result in an error!
		$number = (string) $number;

		if ( ! ctype_digit($number))
		{
			// Luhn can only be used on numbers!
			return FALSE;
		}

		// Check number length
		$length = strlen($number);

		// Checksum of the card number
		$checksum = 0;

		for ($i = $length - 1; $i >= 0; $i -= 2)
		{
			// Add up every 2nd digit, starting from the right
			$checksum += substr($number, $i, 1);
		}

		for ($i = $length - 2; $i >= 0; $i -= 2)
		{
			// Add up every 2nd digit doubled, starting from the right
			$double = substr($number, $i, 1) * 2;

			// Subtract 9 from the double where value is greater than 10
			$checksum += ($double >= 10) ? $double - 9 : $double;
		}

		// If the checksum is a multiple of 10, the number is valid
		return ($checksum % 10 === 0);
	}

	/**
	 * Checks if a phone number is valid.
	 *
	 * @param   string   phone number to check
	 * @return  boolean
	 */
	public static function phone($number, $lengths = NULL)
	{
		if ( ! is_array($lengths))
		{
			$lengths = array(7,10,11);
		}

		// Remove all non-digit characters from the number
		$number = preg_replace('/\D+/', '', $number);

		// Check if the number is within range
		return in_array(strlen($number), $lengths);
	}

	/**
	 * Tests if a string is a valid date string.
	 *
	 * @param   string   date to check
	 * @return  boolean
	 */
	public static function date($str)
	{
		return (strtotime($str) !== FALSE);
	}

	/**
	 * Checks whether a string consists of alphabetical characters only.
	 *
	 * @param   string   input string
	 * @param   boolean  trigger UTF-8 compatibility
	 * @return  boolean
	 */
	public static function alpha($str, $utf8 = FALSE)
	{
		$str = (string) $str;

		if ($utf8 === TRUE)
		{
			return (bool) preg_match('/^\pL++$/uD', $str);
		}
		else
		{
			return ctype_alpha($str);
		}
	}

	/**
	 * Checks whether a string consists of alphabetical characters and numbers only.
	 *
	 * @param   string   input string
	 * @param   boolean  trigger UTF-8 compatibility
	 * @return  boolean
	 */
	public static function alpha_numeric($str, $utf8 = FALSE)
	{
		if ($utf8 === TRUE)
		{
			return (bool) preg_match('/^[\pL\pN]++$/uD', $str);
		}
		else
		{
			return ctype_alnum($str);
		}
	}

	/**
	 * Checks whether a string consists of alphabetical characters, numbers, underscores and dashes only.
	 *
	 * @param   string   input string
	 * @param   boolean  trigger UTF-8 compatibility
	 * @return  boolean
	 */
	public static function alpha_dash($str, $utf8 = FALSE)
	{
		if ($utf8 === TRUE)
		{
			$regex = '/^[-\pL\pN_]++$/uD';
		}
		else
		{
			$regex = '/^[-a-z0-9_]++$/iD';
		}

		return (bool) preg_match($regex, $str);
	}

	/**
	 * Checks whether a string consists of digits only (no dots or dashes).
	 *
	 * @param   string   input string
	 * @param   boolean  trigger UTF-8 compatibility
	 * @return  boolean
	 */
	public static function digit($str, $utf8 = FALSE)
	{
		if ($utf8 === TRUE)
		{
			return (bool) preg_match('/^\pN++$/uD', $str);
		}
		else
		{
			return (is_int($str) AND $str >= 0) OR ctype_digit($str);
		}
	}

	/**
	 * Checks whether a string is a valid number (negative and decimal numbers allowed).
	 *
	 * Uses {@link http://www.php.net/manual/en/function.localeconv.php locale conversion}
	 * to allow decimal point to be locale specific.
	 *
	 * @param   string   input string
	 * @return  boolean
	 */
	public static function numeric($str)
	{
		// Get the decimal point for the current locale
		list($decimal) = array_values(localeconv());

		// A lookahead is used to make sure the string contains at least one digit (before or after the decimal point)
		return (bool) preg_match('/^-?+(?=.*[0-9])[0-9]*+'.preg_quote($decimal).'?+[0-9]*+$/D', (string) $str);
	}

	/**
	 * Tests if a number is within a range.
	 *
	 * @param   string   number to check
	 * @param   integer  minimum value
	 * @param   integer  maximum value
	 * @return  boolean
	 */
	public static function range($number, $min, $max)
	{
		return ($number >= $min AND $number <= $max);
	}

	/**
	 * Checks if a string is a proper decimal format. Optionally, a specific
	 * number of digits can be checked too.
	 *
	 * @param   string   number to check
	 * @param   integer  number of decimal places
	 * @param   integer  number of digits
	 * @return  boolean
	 */
	public static function decimal($str, $places = 2, $digits = NULL)
	{
		if ($digits > 0)
		{
			// Specific number of digits
			$digits = '{'. (int) $digits.'}';
		}
		else
		{
			// Any number of digits
			$digits = '+';
		}

		// Get the decimal point for the current locale
		list($decimal) = array_values(localeconv());

		return (bool) preg_match('/^[0-9]'.$digits.preg_quote($decimal).'[0-9]{'. (int) $places.'}$/D', $str);
	}

	/**
	 * Checks if a string is a proper hexadecimal HTML color value. The validation
	 * is quite flexible as it does not require an initial "#" and also allows for
	 * the short notation using only three instead of six hexadecimal characters.
	 *
	 * @param   string   input string
	 * @return  boolean
	 */
	public static function color($str)
	{
		return (bool) preg_match('/^#?+[0-9a-f]{3}(?:[0-9a-f]{3})?$/iD', $str);
	}

	/**
	 * @var  array  Validators added to the array
	 */
	protected $_validate = array(
		// Filters which modify a value
		'filter'   => array(),
		// Rules which verify a value
		'rule'     => array(),
		// Custom callbacks
		'callback' => array(),
	);
	
	/**
	 * @var  array  Current context
	 */
	protected $_context = array();
	
	/**
	 * @var  array  Field labels
	 */
	protected $_labels = array();
	
	/**
	 * @var  array  Error list
	 */
	protected $_errors = array();

	/**
	 * Creates an ArrayObject from the passed array.
	 *
	 * @param   array   array to validate
	 * @return  void
	 */
	public function __construct(array $array)
	{
		parent::__construct($array, ArrayObject::STD_PROP_LIST);
	}

	/**
	 * Copies the current filter/rule/callback to a new array.
	 *
	 *     $copy = $array->copy($new_data);
	 *
	 * @param   array   new data set
	 * @return  Validation
	 * @since   3.0.5
	 */
	public function copy(array $array)
	{
		// Create a copy of the current validation set
		$copy = clone $this;

		// Replace the data set
		$copy->exchangeArray($array);

		return $copy;
	}

	/**
	 * Returns the array representation of the current object.
	 *
	 * @return  array
	 */
	public function as_array()
	{
		return $this->getArrayCopy();
	}

	/**
	 * Sets or overwrites the label name for a field.
	 *
	 * @param   string  field name
	 * @param   string  label
	 * @return  $this
	 */
	public function label($field, $label)
	{
		// Set the label for this field
		$this->_labels[$field] = $label;

		return $this;
	}

	/**
	 * Sets labels using an array.
	 *
	 * @param   array  list of field => label names
	 * @return  $this
	 */
	public function labels(array $labels)
	{
		$this->_labels = $labels + $this->_labels;

		return $this;
	}
	
	/**
	 * Add a filter to a field. Each filter will be executed once.
	 *
	 * @param   string  field name
	 * @param   mixed   valid PHP callback
	 * @param   array   extra parameters for the filter
	 * @return  $this
	 */
	public function filter($field, $callback, array $params = NULL)
	{
		return $this->_add('filter', $field, array(array($callback, $params)));
	}

	/**
	 * Adds multiple filters to a field.
	 *
	 * @param   string  field name
	 * @param   array   array of filters
	 * @return  $this
	 */
	public function filters($field, $filters)
	{
		return $this->_add('filter', $field, $filters);
	}
	
	/**
	 * Add a rule to a field. Each rule will be executed once.
	 *
	 * @param   string  field name
	 * @param   mixed   valid PHP callback
	 * @param   array   extra parameters for the callback
	 * @return  $this
	 */
	public function rule($field, $callback, array $params = NULL)
	{
		return $this->_add('rule', $field, array(array($callback, $params)));
	}

	/**
	 * Adds multiple rules to a field.
	 *
	 * @param   string  field name
	 * @param   array   array of rules
	 * @return  $this
	 */
	public function rules($field, $rules)
	{
		return $this->_add('rule', $field, $rules);
	}
	
	/**
	 * Add a callback to a field. Each callback will be executed once.
	 *
	 * @param   string  field name
	 * @param   mixed   valid PHP callback
	 * @param   array   extra parameters for the callback
	 * @return  $this
	 */
	public function callback($field, $callback, array $params = NULL)
	{
		return $this->_add('callback', $field, array(array($callback, $params)));
	}

	/**
	 * Adds multiple callbacks to a field.
	 *
	 * @param   string  field name
	 * @param   array   array of callbacks
	 * @return  $this
	 */
	public function callbacks($field, $callbacks)
	{
		return $this->_add('callback', $field, $callbacks);
	}
	
	/**
	 * Add a context to the validation object. 
	 * 
	 * The context key should not have a ':' on the front of it.
	 * 
	 * Omit passing a value to return the value for the context.
	 *
	 * @param   string  context name
	 * @param   mixed   context value
	 * @return  $this
	 */
	public function context($key, $value = NULL)
	{
		// Return a value for the context, since nothing was passed for $value
		if (func_num_args() === 1)
		{
			return isset($this->_context[$key]) ? $this->_context[$key] : NULL;
		}
		
		return $this->contexts(array($key => $value));
	}

	/**
	 * Adds multiple contexts to the validation object. 
	 * 
	 * The context keys should not have a ':' on the front.
	 *
	 * @param   string  field name
	 * @param   array   array of callbacks
	 * @return  $this
	 */
	public function contexts(array $array)
	{
		foreach ($array as $key => $value)
		{
			$this->_context[$key] = $value;
		}
		
		return $this;
	}

	/**
	 * Executes all validation filters, rules, and callbacks. This should
	 * typically be called within an if/else block.
	 *
	 *     if ($validation->check())
	 *     {
	 *          // The data is valid, do something here
	 *     }
	 *
	 * @param   boolean   allow empty array?
	 * @return  boolean
	 */
	public function check($allow_empty = TRUE)
	{
		if (Kohana::$profiling === TRUE)
		{
			// Start a new benchmark
			$benchmark = Profiler::start('Validation', __FUNCTION__);
		}

		// New data set
		$data = $this->_errors = array();

		// Assume nothing has been submitted
		$submitted = FALSE;

		// Get a list of the expected fields
		$expected = array_keys($this->_labels);
		
		// Import the validators locally
		$validate = $this->_validate;

		foreach ($expected as $field)
		{
			if (isset($this[$field]))
			{
				// Some data has been submitted, continue validation
				$submitted = TRUE;

				// Use the submitted value
				$data[$field] = $this[$field];
			}
			else
			{
				// No data exists for this field
				$data[$field] = NULL;
			}
		}

		// Overload the current array with the new one
		$this->exchangeArray($data);

		if ($submitted === FALSE AND ! $allow_empty)
		{
			// Because no data was submitted, validation will not be forced
			return FALSE;
		}
		
		// Execute all callbacks
		foreach ($validate as $type => $fields)
		{
			foreach ($fields as $field => $set)
			{
				// Skip TRUE callbacks and errored out fields
				if ($field === 1 OR isset($this->_errors[$field])) continue;
				
				// Add the TRUE callbacks to the array
				if (isset($validate[$type][TRUE]))
				{
					$set = array_merge($validate[$type][TRUE], $set);
				}
				
				// Process each callback
				foreach ($set as $callback)
				{
					// Set the current context
					$this->_current_context(array(
						'field'    => $field,
						'callback' => $callback,
						'value'    => $this[$field],
						'validate' => $this,
					));
					
					// Call
					$callback->call($this);
					
					// Any new errors? Then we're done for this field
					if (isset($this->_errors[$field]))
					{
						break;
					}
				}
			}
		}
		
		if (isset($benchmark))
		{
			// Stop benchmarking
			Profiler::stop($benchmark);
		}

		return empty($this->_errors);
	}

	/**
	 * Add an error to a field.
	 *
	 * @param   string  field name
	 * @param   string  error message
	 * @return  $this
	 */
	public function error($field, $error, array $params = NULL)
	{
		$this->_errors[$field] = array($error, $params);

		return $this;
	}

	/**
	 * Returns the error messages. If no file is specified, the error message
	 * will be the name of the rule that failed. When a file is specified, the
	 * message will be loaded from "field/rule", or if no rule-specific message
	 * exists, "field/default" will be used. If neither is set, the returned
	 * message will be "file/field/rule".
	 *
	 * By default all messages are translated using the default language.
	 * A string can be used as the second parameter to specified the language
	 * that the message was written in.
	 *
	 *     // Get errors from messages/forms/login.php
	 *     $errors = $validate->errors('forms/login');
	 *
	 * @uses    Kohana::message
	 * @param   string  file to load error messages from
	 * @param   mixed   translate the message
	 * @return  array
	 */
	public function errors($file = NULL, $translate = TRUE)
	{
		if ($file === NULL)
		{
			// Return the error list
			return $this->_errors;
		}

		// Create a new message list
		$messages = array();

		foreach ($this->_errors as $field => $set)
		{
			list($error, $params) = $set;

			// Get the label for this field
			$label = $this->_labels[$field];

			if ($translate)
			{
				// Translate the label
				$label = __($label);
			}

			// Add the field name to the params, everything else should be set in place by the callback
			$values = array(':field' => $label) + (array) $params;

			if ($message = Kohana::message($file, "{$field}.{$error}"))
			{
				// Found a message for this field and error
			}
			elseif ($message = Kohana::message($file, "{$field}.default"))
			{
				// Found a default message for this field
			}
			elseif ($message = Kohana::message($file, $error))
			{
				// Found a default message for this error
			}
			elseif ($message = Kohana::message('validate', $error))
			{
				// Found a default message for this error
			}
			else
			{
				// No message exists, display the path expected
				$message = "{$file}.{$field}.{$error}";
			}

			if ($translate)
			{
				if (is_string($translate))
				{
					// Translate the message using specified language
					$message = __($message, $values, $translate);
				}
				else
				{
					// Translate the message using the default language
					$message = __($message, $values);
				}
			}
			else
			{
				// Do not translate, just replace the values
				$message = strtr($message, $values);
			}

			// Set the message for this field
			$messages[$field] = $message;
		}

		return $messages;
	}
	

	/**
	 * Checks if a field matches the value of another field.
	 *
	 * @param   string   field value
	 * @param   string   field name to match
	 * @return  boolean
	 */
	public function matches($value, $match)
	{
		return ($value === $this[$match]);
	}
	
	/**
	 * Generic method for adding a new validator.
	 *
	 * @param   string  the validator type
	 * @param   string  field name
	 * @param   array   an array of callbacks and params
	 * @return  $this
	 */
	protected function _add($type, $field, $callbacks)
	{
		// Ensure the validator type exists
		if ( ! isset($this->_validate[$type]))
		{
			$this->_validate[$type] = array();
		}
		
		// Ensure the validator field exists
		if ( ! isset($this->_validate[$type][$field]))
		{
			$this->_validate[$type][$field] = array();
		}
		
		// Set the field label to the field name if it doesn't exist
		if ($field !== TRUE AND ! isset($this->_labels[$field]))
		{
			$this->_labels[$field] = inflector::humanize($field);
		}
		
		// The class we'll be converting all callbacks to
		$class = 'Validate_'.$type;
		
		// Loop through each, adding them all
		foreach ($callbacks as $key => $set)
		{
			// Allow old style callbacks 'callback' => $params
			if (is_string($key))
			{
				$set = array($key, $set ? $set : NULL);
			}
			
			$callback = $set[0];
			$params   = isset($set[1]) ? $set[1] : NULL;
			
			// Are we supposed to convert this to a callback of this class?
			if (is_string($callback) AND is_callable(array('Validate', $callback)))
			{
				// Test to see if the method is static or not
				$method = new ReflectionMethod('Validate', $callback);
				
				if ($method->isStatic())
				{
					$callback = array('Validate', $callback);
				}
				else
				{
					$callback = array(':validate', $callback);
				}
			}
			
			// Create an object out of the callback if it isn't already one
			if ( ! $callback instanceof $class)
			{
				$callback = new $class($callback, $params);
			}

			// Append to the list
			$this->_validate[$type][$field][] = $callback;
		}
		
		return $this;
	}
	
	/**
	 * Sets the default context using the values provided.
	 * 
	 * This is a simple method to override to provide default 
	 * contexts. It is re-called every time a new callback is called
	 * on each field when check()ing.
	 *
	 * @param   array  $array 
	 * @return  NULL
	 */
	protected function _current_context($array)
	{
		$this->contexts($array);
	}

} // End Validation

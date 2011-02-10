<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Array and variable validation.
 *
 * @package    Kohana
 * @category   Security
 * @author     Kohana Team
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Validation extends ArrayObject {

	/**
	 * Creates a new Validation instance.
	 *
	 * @param   array   array to use for validation
	 * @return  Validation
	 */
	public static function factory(array $array)
	{
		return new Validation($array);
	}

	// Bound values
	protected $_bound = array();

	// Field rules
	protected $_rules = array();

	// Field labels
	protected $_labels = array();

	// Rules that are executed even when the value is empty
	protected $_empty_rules = array('not_empty', 'matches');

	// Error list, field => rule
	protected $_errors = array();

	/**
	 * Sets the unique "any field" key and creates an ArrayObject from the
	 * passed array.
	 *
	 * @param   array   array to validate
	 * @return  void
	 */
	public function __construct(array $array)
	{
		parent::__construct($array, ArrayObject::STD_PROP_LIST);
	}

	/**
	 * Copies the current rule to a new array.
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
	 * Overwrites or appends rules to a field. Each rule will be executed once.
	 * All rules must be string names of functions method names. Parameters must
	 * match the parameters of the callback function exactly
	 *
	 * Aliases you can use in callback parameters:
	 * - :validation - the validation object
	 * - :field - the field name
	 * - :value - the value of the field
	 *
	 *     // The "username" must not be empty and have a minimum length of 4
	 *     $validation->rule('username', 'not_empty')
	 *                ->rule('username', 'min_length', array('username', 4));
	 *
	 *     // The "password" field must match the "password_repeat" field
	 *     $validation->rule('password', 'matches', array(':validation', 'password', 'password_repeat'));
	 *
	 * @param   string    field name
	 * @param   callback  valid PHP callback
	 * @param   array     extra parameters for the rule
	 * @return  $this
	 */
	public function rule($field, $rule, array $params = NULL)
	{
		if ($params === NULL)
		{
			// Default to array(':value')
			$params = array(':value');
		}

		if ($field !== TRUE AND ! isset($this->_labels[$field]))
		{
			// Set the field label to the field name
			$this->_labels[$field] = preg_replace('/[^\pL]+/u', ' ', $field);
		}

		// Store the rule and params for this rule
		$this->_rules[$field][] = array($rule, $params);

		return $this;
	}

	/**
	 * Add rules using an array.
	 *
	 * @param   string  field name
	 * @param   array   list of callbacks
	 * @return  $this
	 */
	public function rules($field, array $rules)
	{
		foreach ($rules as $rule)
		{
			$this->rule($field, $rule[0], Arr::get($rule, 1));
		}

		return $this;
	}

	/**
	 * Bind a value to a parameter definition.
	 *
	 *     // This allows you to use :model in the parameter definition of rules
	 *     $validation->bind(':model', $model)
	 *         ->rule('status', 'valid_status', array(':model'));
	 *
	 * @param   string  variable name or an array of variables
	 * @param   mixed   value
	 * @return  $this
	 */
	public function bind($key, $value = NULL)
	{
		if (is_array($key))
		{
			foreach ($key as $name => $value)
			{
				$this->_bound[$name] = $value;
			}
		}
		else
		{
			$this->_bound[$key] = $value;
		}

		return $this;
	}

	/**
	 * Executes all validation rules. This should
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
	public function check()
	{
		if (Kohana::$profiling === TRUE)
		{
			// Start a new benchmark
			$benchmark = Profiler::start('Validation', __FUNCTION__);
		}

		// New data set
		$data = $this->_errors = array();

		// Store the original data because this class should not modify it post-validation
		$original = $this->getArrayCopy();

		// Get a list of the expected fields
		$expected = Arr::merge(array_keys($original), array_keys($this->_labels));

		// Import the rules locally
		$rules     = $this->_rules;

		foreach ($expected as $field)
		{
			// Use the submitted value or NULL if no data exists
			$data[$field] = Arr::get($this, $field);

			if (isset($rules[TRUE]))
			{
				if ( ! isset($rules[$field]))
				{
					// Initialize the rules for this field
					$rules[$field] = array();
				}

				// Append the rules
				$rules[$field] = array_merge($rules[$field], $rules[TRUE]);
			}
		}

		// Overload the current array with the new one
		$this->exchangeArray($data);

		// Remove the rules that apply to every field
		unset($rules[TRUE]);

		// Bind the validation object to :validation
		$this->bind(':validation', $this);

		// Execute the rules
		foreach ($rules as $field => $set)
		{
			// Get the field value
			$value = $this[$field];

			// Bind the field name and value to :field and :value respectively
			$this->bind(array
			(
				':field' => $field,
				':value' => $value,
			));

			foreach ($set as $array)
			{
				// Rules are defined as array($rule, $params)
				list($rule, $params) = $array;

				foreach ($params as $key => $param)
				{
					if (is_string($param) AND array_key_exists($param, $this->_bound))
					{
						// Replace with bound value
						$params[$key] = $this->_bound[$param];
					}
				}

				// Default the error name to be the rule (except array and lambda rules)
				$error_name = $rule;

				if (is_array($rule))
				{
					// This is an array callback, the method name is the error name
					$error_name = $rule[1];
					$passed = call_user_func_array($rule, $params);
				}
				elseif ( ! is_string($rule))
				{
					// This is a lambda function, there is no error name (errors must be added manually)
					$error_name = FALSE;
					$passed = call_user_func_array($rule, $params);
				}
				elseif (method_exists('Valid', $rule))
				{
					// Use a method in this object
					$method = new ReflectionMethod('Valid', $rule);

					// Call static::$rule($this[$field], $param, ...) with Reflection
					$passed = $method->invokeArgs(NULL, $params);
				}
				elseif (strpos($rule, '::') === FALSE)
				{
					// Use a function call
					$function = new ReflectionFunction($rule);

					// Call $function($this[$field], $param, ...) with Reflection
					$passed = $function->invokeArgs($params);
				}
				else
				{
					// Split the class and method of the rule
					list($class, $method) = explode('::', $rule, 2);

					// Use a static method call
					$method = new ReflectionMethod($class, $method);

					// Call $Class::$method($this[$field], $param, ...) with Reflection
					$passed = $method->invokeArgs(NULL, $params);
				}

				// Ignore return values from rules when the field is empty
				if ( ! in_array($rule, $this->_empty_rules) AND ! Valid::not_empty($value))
					continue;

				if ($passed === FALSE AND $error_name !== FALSE)
				{
					// Add the rule to the errors
					$this->error($field, $error_name, $params);

					// This field has an error, stop executing rules
					break;
				}
			}
		}

		// Restore the data to its original form
		$this->exchangeArray($original);

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
	 *     $errors = $Validation->errors('forms/login');
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
				if (is_string($translate))
				{
					// Translate the label using the specified language
					$label = __($label, NULL, $translate);
				}
				else
				{
					// Translate the label
					$label = __($label);
				}
			}

			// Start the translation values list
			$values = array(
				':field' => $label,
				':value' => Arr::get($this, $field),
			);

			if (is_array($values[':value']))
			{
				// All values must be strings
				$values[':value'] = implode(', ', Arr::flatten($values[':value']));
			}

			if ($params)
			{
				foreach ($params as $key => $value)
				{
					if (is_array($value))
					{
						// All values must be strings
						$value = implode(', ', Arr::flatten($value));
					}
					elseif (is_object($value))
					{
						// Objects cannot be used in message files
						continue;
					}

					// Check if a label for this parameter exists
					if (isset($this->_labels[$value]))
					{
						// Use the label as the value, eg: related field name for "matches"
						$value = $this->_labels[$value];

						if ($translate)
						{
							if (is_string($translate))
							{
								// Translate the value using the specified language
								$value = __($value, NULL, $translate);
							}
							else
							{
								// Translate the value
								$value = __($value);
							}
						}
					}

					// Add each parameter as a numbered value, starting from 1
					$values[':param'.($key + 1)] = $value;
				}
			}

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
			elseif ($message = Kohana::message('validation', $error))
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

} // End Validation

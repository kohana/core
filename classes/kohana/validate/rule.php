<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @package    Kohana
 * @category   Security
 * @author     Kohana Team
 * @copyright  (c) 2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Validate_Rule extends Kohana_Validate_Callback {
	
	/**
	 * @var  array  Original parameters, used for generating error params
	 */
	protected $_original_params = array();
	
	/**
	 * Creates a new rule.
	 * 
	 * If $params is empty or doesn't contain the :value context, the
	 * :value context is added to the front of the array as was the 
	 * behavior for the old validate class.
	 *
	 * @param  callback  $callback 
	 * @param  array     $params 
	 */
	public function __construct($callback, array $params = NULL)
	{
		$params = $params ? $params : array();
		
		// Save the original parameters for the potential error messe
		$this->_original_params = $params;
		
		// Check the parameters to see if we need to add ':value'
		if ( ! in_array(':value', $params))
		{
			array_unshift($params, ':value');
		}
		
		parent::__construct($callback, $params);
	}
	
	/**
	 * Calls the rule and returns a value from the callback.
	 * 
	 * For rules, an error is added to the validation array if FALSE is returned.
	 *
	 * @param   Validate $validate 
	 * @return  mixed
	 */
	public function call(Validate $validate)
	{
		if (parent::call($validate) === FALSE)
		{
			// Determine the name of the error based on the callback
			$error = is_array($this->_callback) ? $this->_callback[1] : $this->_callback;
			
			$params = array();
			$i = 1;
			
			// Create the error context
			foreach ($this->_original_params as $key => $param)
			{
				// Each error, with contexts, has the potential for multiple keys
				$keys = array(':param'.$i);
				
				// See if we need to replace the parameter with its context
				if (is_string($param) AND substr($param, 0, 1) === ':')
				{
					// Add the context to the keys so it 
					// can be identified by that name as well
					$keys[] = $param;
					
					// We only need to re-contextualize the parameter
					// if $key is not a string, which indicates it should be used as a message
					if ( ! is_string($key))
					{
						$param = $this->_replace_context($validate, $param);
					}
				}
				
				// Key is a custom message for this parameter, use that as the param instead
				if (is_string($key))
				{
					$param = __($key);
				}
				
				// Add any and all keys
				foreach ($keys as $key)
				{
					$params[$key] = $param;
				}
				
				// Increment parameter count
				$i++;
			}
			
			// Ensure :value is passed to the params
			if ( ! isset($params[':value']))
			{
				$params[':value'] = $validate->context('value');
			}
			
			// Add it to the list
			$validate->error($validate->context('field'), $error, $params);
		}
	}

} // End Kohana_Validate_Rule
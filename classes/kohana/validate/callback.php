<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @package    Kohana
 * @category   Security
 * @author     Kohana Team
 * @copyright  (c) 2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Validate_Callback {
	
	/**
	 * @var  callback  The callback that will be called
	 */
	protected $_callback = NULL;
	
	/**
	 * @var  array  Any params that will be added to the callback
	 */
	protected $_params = NULL;
	
	/**
	 * Creates a new callback.
	 * 
	 * If $params is empty it is set to array(':validate', ':field'), 
	 * which mimics the old validation class. 
	 *
	 * @param  callback  $callback 
	 * @param  array     $params 
	 */
	public function __construct($callback, array $params = NULL)
	{
		$this->_callback = $callback;
		$this->_params   = $params ? $params : array(':validate', ':field');
	}
	
	/**
	 * Calls the rule and returns a value from the callback.
	 * 
	 * For callbacks, it is the responsibility of the method called
	 * to add the error to the validation array.
	 * 
	 * When a callback is added an error to the validation array and it
	 * wants to add parameters, it must "name" them as well:
	 * 
	 *    $array->error($field, $error, array(":param1" => $param));
	 * 
	 * The names are arbitrary and can be whatever the developer chooses.
	 * For consistency with the rest of the library it is recommended that
	 * the name begin with a colon (:).
	 *
	 * @param   Validate $validate 
	 * @return  mixed
	 */
	public function call(Validate $validate)
	{
		// Contextualize the callback and parameters
		list($callback, $params) = $this->_contextualize($validate);
		
		// Simply call the method
		return call_user_func_array($callback, $params);
	}
	
	/**
	 * Returns a callback and parameter list with contexts replaced.
	 *
	 * @param   Validate  $validate 
	 * @return  array
	 */
	protected function _contextualize(Validate $validate)
	{
		// Copy locally, because we don't want 
		// to go mucking with the originals
		$callback = $this->_callback;
		$params   = $this->_params;
		
		// Check for a context to replace on the callback object
		if (is_array($callback) AND isset($callback[0]))
		{
			$callback[0] = $this->_replace_context($validate, $callback[0]);
		}
		
		// Replace all param contexts
		foreach ((array)$params as $key => $param)
		{
			$params[$key] = $this->_replace_context($validate, $param);
		}
		
		return array($callback, $params);
	}
	
	/**
	 * Replaces a context with its actual replacement.
	 * 
	 * If $key is not a string or does not start with ':'
	 * the key is simply returned.
	 *
	 * @param   Validate  $validate
	 * @param   mixed     $key 
	 * @return  mixed
	 */
	protected function _replace_context(Validate $validate, $key)
	{
		// Ensure we actually have a potentially valid context
		if ( ! is_string($key) OR substr($key, 0, 1) !== ':')
		{
			return $key;
		}
		
		return $validate->context(substr($key, 1));
	}

} // End Kohana_Validate_Callback
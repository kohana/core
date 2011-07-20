<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Security helper class.
 *
 * @package    Kohana
 * @category   Security
 * @author     Kohana Team
 * @copyright  (c) 2007-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Security {

	/**
	 * @var  string  key name used for token storage
	 */
	public static $token_name = 'security_token';

	/**
	 * @var  integer max capacity of the array used for token storage
	 */
	public static $token_capacity = 50;

	/**
	 * Generate and store a unique token which can be used to help prevent
	 * [CSRF](http://wikipedia.org/wiki/Cross_Site_Request_Forgery) attacks.
	 *
	 *     $token = Security::token();
	 *
	 * You can insert this token into your forms as a hidden field:
	 *
	 *     echo Form::hidden('csrf', Security::token());
	 *
	 * And then check it when using [Validation]:
	 *
	 *     $array->rules('csrf', array(
	 *         'not_empty'       => NULL,
	 *         'Security::check' => NULL,
	 *     ));
	 *
	 * This provides a basic, but effective, method of preventing CSRF attacks.
	 *
	 * @return  string
	 * @uses    Session::instance
	 */
	public static function token()
	{
		$session = Session::instance();

		// Get the token array
		$tokens = (array) $session->get(Security::$token_name);

		// Generate a new unique token
		$token = sha1(uniqid(NULL, TRUE));

		// Add the token to the end of the array
		array_push($tokens, $token);

		// If capacity reached then remove the oldest token
		if (count($tokens) > Security::$token_capacity)
		{
			array_shift($tokens);
		}

		// Store the modified token array
		$session->set(Security::$token_name, $tokens);

		return $token;
	}

	/**
	 * Check that the given token exists in the currently stored security token array.
	 *
	 *     if (Security::check($token))
	 *     {
	 *         // Pass
	 *     }
	 *
	 * @param   string   token to check
	 * @return  boolean
	 * @uses    Session::instance
	 */
	public static function check($token)
	{
		$session = Session::instance();

		// Get the token array
		$tokens = (array) $session->get(Security::$token_name);

		// If the token exists in the tokens array
		if ($key = array_search($token, $tokens))
		{
			// Remove the token
			unset($tokens[$key]);

			// Store the modified token array
			$session->set(self::$token_name, $tokens);
		}

		return (bool) $key;
	}

	/**
	 * Remove image tags from a string.
	 *
	 *     $str = Security::strip_image_tags($str);
	 *
	 * @param   string  string to sanitize
	 * @return  string
	 */
	public static function strip_image_tags($str)
	{
		return preg_replace('#<img\s.*?(?:src\s*=\s*["\']?([^"\'<>\s]*)["\']?[^>]*)?>#is', '$1', $str);
	}

	/**
	 * Encodes PHP tags in a string.
	 *
	 *     $str = Security::encode_php_tags($str);
	 *
	 * @param   string  string to sanitize
	 * @return  string
	 */
	public static function encode_php_tags($str)
	{
		return str_replace(array('<?', '?>'), array('&lt;?', '?&gt;'), $str);
	}

} // End security

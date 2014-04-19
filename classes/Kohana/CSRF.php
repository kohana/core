<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Security helper class, provides a basic, 
 * but effective, preventing [CSRF][ref-csrf] attacks.
 * 
 * [ref-csrf]: http://wikipedia.org/wiki/Cross_Site_Request_Forgery
 *
 * @package    Kohana
 * @category   Security
 * @author     Kohana Team
 * @copyright  (c) 2007-2014 Kohana Team
 * @license    http://kohanaframework.org/license
 */
abstract class Kohana_CSRF {

	/**
	 * @var  string  Key name used as token storage
	 */
	public static $token_name = 'csrf_token';

	/**
	 * Generate and store a unique token.
	 *
	 *     $token = CSRF::token();
	 *
	 * You can insert this token into your forms as a hidden field:
	 *
	 *     echo Form::hidden('csrf', CSRF::token());
	 *
	 * And then check it when using [Validation]:
	 *
	 *     $validation->rules(
	 *         'csrf', 
	 *         array('not_empty' => NULL, 'CSRF::check' => NULL)
	 *     );
	 *
	 * @param   boolean  $new  Force a new token to be generated?
	 * @return  string
	 * @uses    Session
	 */
	public static function token($new = FALSE)
	{
		$session = Session::instance();

		// Get the current token
		if ($new === FALSE)
		{
			$token = $session->get(static::$token_name, FALSE);
		}

		if ($new === TRUE OR ! $token)
		{
			// Generate a new unique token
			if (function_exists('openssl_random_pseudo_bytes'))
			{
				// Generate a random pseudo bytes token if openssl is available.
				// This is safer than uniqid, because uniqid relies on microtime, 
				// which is predictable.
				$token = base64_encode(openssl_random_pseudo_bytes(32));
			}
			else
			{
				// Otherwise, fall back to a hashed uniqid
				$token = sha1(uniqid(NULL, TRUE));
			}

			// Store the new token
			$session->set(static::$token_name, $token);
		}

		return $token;
	}

	/**
	 * Check that the given token matches the currently stored security token.
	 *
	 *     if (CSRF::check($token))
	 *     {
	 *         // Pass
	 *     }
	 *
	 * @param   string  $token  Token to check
	 * @return  boolean
	 */
	public static function check($token)
	{
		return static::token() === $token;
	}

}

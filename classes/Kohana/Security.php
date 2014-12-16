<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Security helper class.
 *
 * @package    Kohana
 * @category   Security
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Security {

	// The length of the random length
	const RANDOM_LENGTH = 16;

	/**
	 * @var  string  key name used for token storage
	 */
	public static $token_name = 'security_token';

	/**
	 * Generate and store a unique token which can be used to help prevent
	 * [CSRF](http://wikipedia.org/wiki/Cross_Site_Request_Forgery) attacks.
	 *
	 * See [RFC 3548](http://tools.ietf.org/html/rfc3548) for the definition of URL-safe base64.
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
	 * If $n is not specified, Secure::RANDOM_LENGTH is assumed. It may be larger in future.
	 *
	 * @param   boolean $new     force a new token to be generated?
	 * @param   integer $n       specifies the length of the random length
	 * @param   bool    $padding
	 * @return  string  The result may contain A-Z, a-z, 0-9, "-" and "_". "=" is also used if $padding is true.
	 * @uses    Session::instance
	 */
	public static function token($new = FALSE, $n = NULL, $padding = FALSE)
	{
		$session = Session::instance();

		// Get the current token
		$token = $session->get(Security::$token_name);

		if ($new === TRUE OR ! $token)
		{
			// Generate a new unique token
			if (function_exists('openssl_random_pseudo_bytes'))
			{
				// Generate a random pseudo bytes token if openssl_random_pseudo_bytes is available
				// This is more secure than uniqid, because uniqid relies on microtime, which is predictable
				$token = pack('a*', openssl_random_pseudo_bytes($n ?: Security::RANDOM_LENGTH));
				$token = str_replace(array("\n", "\r", "\n\r"), '', $token);
			}
			else
			{
				// Otherwise, fall back to a hashed uniqid
				$token = substr(hash('sha256', uniqid(null, true)), 0, $n ?: Security::RANDOM_LENGTH);
			}

			$token = $padding ? strtr(base64_encode($token), '+/', '-_') : rtrim(strtr(base64_encode($token), '+/', '-_'), '=');

			// Store the new token
			$session->set(Security::$token_name, $token);
		}

		return $token;
	}

	/**
	 * Check that the given token matches the currently stored security token.
	 *
	 *     if (Security::check($token))
	 *     {
	 *         // Pass
	 *     }
	 *
	 * @param   string  $token  token to check
	 * @return  boolean
	 * @uses    Security::token
	 */
	public static function check($token)
	{
		return Security::slow_equals(Security::token(), $token);
	}



	/**
	 * Compare two hashes in a time-invariant manner.
	 * Prevents cryptographic side-channel attacks (timing attacks, specifically)
	 *
	 * @param string $a cryptographic hash
	 * @param string $b cryptographic hash
	 * @return boolean
	 */
	public static function slow_equals($a, $b)
	{
		$diff = strlen($a) ^ strlen($b);
		for($i = 0; $i < strlen($a) AND $i < strlen($b); $i++)
		{
			$diff |= ord($a[$i]) ^ ord($b[$i]);
		}
		return $diff === 0;
	}


	/**
	 * Remove image tags from a string.
	 *
	 *     $str = Security::strip_image_tags($str);
	 *
	 * @param   string  $str    string to sanitize
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
	 * @param   string  $str    string to sanitize
	 * @return  string
	 */
	public static function encode_php_tags($str)
	{
		return str_replace(array('<?', '?>'), array('&lt;?', '?&gt;'), $str);
	}

}

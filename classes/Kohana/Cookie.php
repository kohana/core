<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Cookie helper.
 *
 * @package    Kohana
 * @category   Helpers
 * @author     Kohana Team
 * @copyright  (c) 2008-2014 Kohana Team
 * @license    http://kohanaframework.org/license
 */
abstract class Kohana_Cookie {

	/**
	 * @var  string  Magic salt to add to the cookie
	 */
	public static $salt = NULL;

	/**
	* @var  string  Separator between cookie value and salt
	*/
	public static $salt_separator = '~';

	/**
	* Use function [hash_hmac](http://php.net/hash-hmac) to generate salt.
	* 
	* [!!] For more information, see [FAQ safe hashing](http://php.net/faq.passwords).
	*
	* @var  boolean|int
	*/
	public static $hash_hmac = FALSE;

	/**
	* Name of hashing algorithm, recommended values: sha512, whirlpool, sha384, ripemd320.
	* 
	* [!!] Use [hash-algos](http://php.net/hash-algos) to get all available algorithms.
	*
	* @var  string
	*/
	public static $hash_hmac_algo = 'sha512';

	/**
	 * @var  integer  Number of seconds before the cookie expires
	 */
	public static $expiration = 0;

	/**
	 * @var  string  Restrict the path that the cookie is available to
	 */
	public static $path = '/';

	/**
	 * @var  string  Restrict the domain that the cookie is available to
	 */
	public static $domain = NULL;

	/**
	 * @var  boolean  Only transmit cookies over secure connections
	 */
	public static $secure = FALSE;

	/**
	 * @var  boolean  Only transmit cookies over HTTP, disabling JavaScript access
	 */
	public static $httponly = FALSE;

	/**
	 * Gets the value of a signed cookie. Cookies without signatures will not
	 * be returned. If the cookie signature is present, but invalid, the cookie
	 * will be deleted.
	 *
	 *     // Get the "theme" cookie, or use "blue" if the cookie does not exist
	 *     $theme = Cookie::get('theme', 'blue');
	 *
	 * @param   string  $key      Cookie name
	 * @param   mixed   $default  Default value to return
	 * @return  string
	 */
	public static function get($key, $default = NULL)
	{
		if ( ! isset($_COOKIE[$key]))
		{
			// The cookie does not exist
			return $default;
		}

		// Get the cookie value
		$cookie = $_COOKIE[$key];

		// Find the position of the split between salt and contents
		$split = strlen(Cookie::salt($key, NULL));

		if (isset($cookie[$split]) AND $cookie[$split] === Cookie::$salt_separator)
		{
			// Separate the salt and the value
			list ($hash, $value) = explode(Cookie::$salt_separator, $cookie, 2);

			if (Cookie::salt($key, $value) === $hash)
			{
				// Cookie signature is valid
				return $value;
			}

			// The cookie signature is invalid, delete it
			Cookie::delete($key);
		}

		return $default;
	}

	/**
	 * Sets a signed cookie.
	 * 
	 * [!!] All values must be strings and no automatic serialization will be performed!
	 *
	 *     // Set the "theme" cookie
	 *     Cookie::set('theme', 'red');
	 *
	 * @param   string  $name        Name of cookie
	 * @param   string  $value       Value of cookie
	 * @param   integer $expiration  Lifetime in seconds
	 * @return  boolean
	 */
	public static function set($name, $value, $expiration = NULL)
	{
		if ($expiration === NULL)
		{
			// Use the default expiration
			$expiration = Cookie::$expiration;
		}

		if ($expiration !== 0)
		{
			// The expiration is expected to be a UNIX timestamp
			$expiration += time();
		}

		// Add the salt to the cookie value
		$value = Cookie::salt($name, $value).Cookie::$salt_separator.$value;

		return setcookie(
			$name,
			$value,
			$expiration,
			Cookie::$path,
			Cookie::$domain,
			Cookie::$secure,
			Cookie::$httponly
		);
	}

	/**
	 * Deletes a cookie by making the value NULL and expiring it.
	 *
	 *     Cookie::delete('theme');
	 *
	 * @param   string  $name  Cookie name
	 * @return  boolean
	 */
	public static function delete($name)
	{
		// Remove the cookie
		unset($_COOKIE[$name]);

		// Nullify the cookie and make it expire
		return setcookie(
			$name,
			NULL,
			-86400,
			Cookie::$path,
			Cookie::$domain,
			Cookie::$secure,
			Cookie::$httponly
		);
	}

	/**
	* Generates a salt string for a cookie based on the name and value.
	*
	*     $salt = Cookie::salt('theme', 'red');
	*
	* @param   string  $name   Name of cookie
	* @param   string  $value  Value of cookie
	* @return  string
	* @throws  Kohana_Exception
	*/
	public static function salt($name, $value)
	{
		// Require a valid salt
		if ( ! Cookie::$salt)
		{
			throw new Kohana_Exception('A valid cookie salt is required, set Cookie::$salt.');
		}

		// Determine the user agent
		$agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : 'unknown';

		if (Cookie::$hash_hmac)
		{
			if (Cookie::$hash_hmac === TRUE)
			{
				if ( ! function_exists('hash'))
				{
					throw new Kohana_Exception('Hash extension not available.');
				}
				elseif ( ! in_array(Cookie::$hash_hmac_algo, hash_algos()))
				{
					throw new Kohana_Exception(
						'Hashing algorithm :algo not supporteded.',
						array(':algo' => Cookie::$hash_hmac_algo)
					);
				};
				// Change type to check this block only once
				Cookie::$hash_hmac = 1;
			}

			// [HMAC](http://wikipedia.org/wiki/HMAC) salt generator
			return hash_hmac(Cookie::$hash_hmac_algo, $agent.$name.$value, Cookie::$salt);
		}

		// Default salt generator
		return sha1($agent.$name.$value.Cookie::$salt);
	}

}

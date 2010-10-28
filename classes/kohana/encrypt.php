<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * The Encrypt library provides two-way encryption of text and binary strings
 * using the [Mcrypt](http://php.net/mcrypt) extension, which consists of three
 * parts: the key, the cipher, and the mode.
 *
 * The Key
 * :  A secret passphrase that is used for encoding and decoding
 *
 * The Cipher
 * :  A [cipher](http://php.net/mcrypt.ciphers) determines how the encryption
 *    is mathematically calculated. By default, the "rijndael-128" cipher
 *    is used. This is commonly known as "AES-128" and is an industry standard.
 *
 * The Mode
 * :  The [mode](http://php.net/mcrypt.constants) determines how the encrypted
 *    data is written in binary form. By default, the "nofb" mode is used,
 *    which produces short output with high entropy.
 *
 * @package    Kohana
 * @category   Security
 * @author     Kohana Team
 * @copyright  (c) 2007-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
abstract class Kohana_Encrypt {

	/**
	 * @var  string  default instance name
	 */
	public static $default = 'default';

	/**
	 * @var  array  Encrypt class instances
	 */
	public static $instances = array();

	// OS-dependent RAND type to use
	protected static $_rand;

	/**
	 * Returns a singleton instance of Encrypt. An encryption key must be
	 * provided in your "encrypt" configuration file.
	 *
	 *     $encrypt = Encrypt::instance();
	 *
	 * @param   string  configuration group name
	 * @return  object
	 */
	public static function instance($name = NULL)
	{
		if ($name === NULL)
		{
			// Use the default instance name
			$name = Kohana::config('encrypt.default_group');

		}

		if ( ! isset(Encrypt::$instances[$name]))
		{
			// Load the configuration data
			$config = Kohana::config('encrypt')->$name;
			$config['name'] = $name;
			$class = 'Encrypt_'.ucfirst($config['driver']);

			// Create a new instance
			Encrypt::$instances[$name] = new $class($config);
		}

		Encrypt::$instances[$name]->random_source();
		return Encrypt::$instances[$name];
	}

	/**
	 * Returns the type of random number generator
	 *
	 *
	 */
	public static function random_source()
	{
		// Set the rand type if it has not already been set
		if (Encrypt::$_rand === NULL)
		{
			if (Kohana::$is_windows)
			{
				// Windows only supports the system random number generator
				Encrypt::$_rand = MCRYPT_RAND;
			}
			else
			{
				if (defined('MCRYPT_DEV_URANDOM'))
				{
					// Use /dev/urandom
					Encrypt::$_rand = MCRYPT_DEV_URANDOM;
				}
				elseif (defined('MCRYPT_DEV_RANDOM'))
				{
					// Use /dev/random
					Encrypt::$_rand = MCRYPT_DEV_RANDOM;
				}
				else
				{
					// Use the system random number generator
					Encrypt::$_rand = MCRYPT_RAND;
				}
			}
		}

		if (Encrypt::$_rand === MCRYPT_RAND)
		{
			// The system random number generator must always be seeded each
			// time it is used, or it will not produce true random results
			mt_srand();
		}

		return Encrypt::$_rand;
	}


	/**
	 * Encrypts a string and returns an encrypted string that can be decoded.
	 *
	 *     $data = $encrypt->encode($data);
	 *
	 * The encrypted binary data is encoded using [base64](http://php.net/base64_encode)
	 * to convert it to a string. This string can be stored in a database,
	 * displayed, and passed using most other means without corruption.
	 *
	 * @param   string  data to be encrypted
	 * @return  string
	 */
	abstract public function encode($data);

	/**
	 * Decrypts an encoded string back to its original value.
	 *
	 *     $data = $encrypt->decode($data);
	 *
	 * @param   string  encoded string to be decrypted
	 * @return  FALSE   if decryption fails
	 * @return  string
	 */
	abstract public function decode($data);

	public function get_iv_size(){
		return $this->_iv_size;
	}
	
	public function get_key_size(){
		return $this->_key_size;
	}
	
	public function algo(){
		return $this->_cipher;
	}
	
	public function mode(){
		return $this->_mode;
	}
	
	public function set_key($key){
		$this->_key = $key;
	}
	
	public function get_key(){
		return $this->_key;
	}

	abstract public function compare_hash($data, $hash);
	
	abstract public function hash($data, $salt = null, $cycles = null);
	
	public function generate_key(){
		return Encrypt::random_data($this->_key_size);
	}

	public function generate_iv(){
		return Encrypt::random_data($this->_iv_size);
	}
	
	public static function random_data($len){
		return mcrypt_create_iv($len, Encrypt::random_source());
	}

} // End Encrypt

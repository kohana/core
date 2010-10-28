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
class Kohana_Encrypt_Default extends Kohana_Encrypt{

	/**
	 * Creates a new mcrypt wrapper.
	 *
	 * @param   string   encryption key
	 * @param   string   mcrypt mode
	 * @param   string   mcrypt cipher
	 */
	public function __construct($config)
	{
		
		if ( ! isset($config['mode']))
		{
			// Add the default mode
			$config['mode'] = MCRYPT_MODE_NOFB;
		}

		if ( ! isset($config['cipher']))
		{
			// Add the default cipher
			$config['cipher'] = MCRYPT_RIJNDAEL_128;
		}

		if ( ! isset($config['hash_function']))
		{
			// Add the default mode
			$config['hash'] = 'whirlpool';
		}
		
		if ( ! isset($config['key']))
		{
			// Add the default mode
			$config['key'] = 'null';
		}
		// Store the key, mode, and cipher
		$this->_key    = $config['key'];
		$this->_mode   = $config['mode'];
		$this->_cipher = $config['cipher'];
		$this->_hash_fcn=$config['hash_function'];
		
		
		$this->_key_size = mcrypt_get_key_size($this->_cipher, $this->_mode );
		$this->_iv_size = mcrypt_get_iv_size($this->_cipher, $this->_mode);
		
		if (isset($this->_key[$key_size]))
		{
			// Shorten the key to the maximum size
			$this->_key = substr($this->_key, 0, $this->_key_size);
		}

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
	public function encode($data)
	{
		if ( is_null($this->_key) )
		{
			// No default encryption key is provided!
			throw new Kohana_Exception('No encryption key is defined in the encryption configuration');
		}
		
		// Set the rand type if it has not already been set
		$this->random_source();

		// Create a random initialization vector of the proper size for the current cipher
		$iv = $this->create_iv();

		// Encrypt the data using the configured options and generated iv
		$data = mcrypt_encrypt($this->_cipher, $this->_key, $data, $this->_mode, $iv);

		// Use base64 encoding to convert to a string
		return base64_encode($iv.$data);
	}

	/**
	 * Decrypts an encoded string back to its original value.
	 *
	 *     $data = $encrypt->decode($data);
	 *
	 * @param   string  encoded string to be decrypted
	 * @return  FALSE   if decryption fails
	 * @return  string
	 */
	public function decode($data)
	{
		// Convert the data back to binary
		$data = base64_decode($data, TRUE);

		if ( ! $data)
		{
			// Invalid base64 data
			return FALSE;
		}

		// Extract the initialization vector from the data
		$iv = substr($data, 0, $this->_iv_size);

		if ($this->_iv_size !== strlen($iv))
		{
			// The iv is not the expected size
			return FALSE;
		}

		// Remove the iv from the data
		$data = substr($data, $this->_iv_size);

		// Return the decrypted data, trimming the \0 padding bytes from the end of the data
		return rtrim(mcrypt_decrypt($this->_cipher, $this->_key, $data, $this->_mode, $iv), "\0");
	}

	public function compare_hash($data, $hash){
		$data_hash = $this->hash($data);
		return $data_hash === $hash;
	}
	
	public function hash($data, $salt = null, $cycles = null){
		return hash($this->_hash_fcn, $data);
	}
}

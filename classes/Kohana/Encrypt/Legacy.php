<?php
/**
 * [!] Warning Not suitable for production.
 *
 * Legacy Kohana Encryption class, provided only as convenience to upgrade
 * from previous versions of Kohana.
 *
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
 * @copyright  (c) 2007-2016 Kohana Team
 * @license    http://kohanaframework.org/license
 * @deprecated since version 4.0
 */
class Kohana_Encrypt_Legacy implements Kohana_Crypto {

	/**
	 * @var  string  RAND type to use
	 *
	 * Only MCRYPT_DEV_URANDOM and MCRYPT_DEV_RANDOM are considered safe.
	 * Using MCRYPT_RAND will silently revert to MCRYPT_DEV_URANDOM
	 */
	protected static $_rand = MCRYPT_DEV_URANDOM;

	/**
	 * @var string Encryption key
	 */
	protected $_key;

	/**
	 * @var string mcrypt mode
	 */
	protected $_mode;

	/**
	 * @var string mcrypt cipher
	 */
	protected $_cipher;

	/**
	 * @var int the size of the Initialization Vector (IV) in bytes
	 */
	protected $_iv_size;

	/**
	 * Creates a new mcrypt wrapper.
	 *
	 * @param   array   $settings
	 * @param   string  $mode   mcrypt mode
	 * @param   string  $cipher mcrypt cipher
	 */
	public function __construct(array $settings)
	{

		if ( ! isset($settings['key']))
		{
			// No default encryption key is provided!
			throw new Kohana_Exception('No encryption key defined');
		}

		$key = $settings['key'];
		$mode = isset($settings['mode']) ? $settings['mode'] : MCRYPT_MODE_NOFB;
		$cipher = isset($settings['cipher']) ? $settings['cipher'] : MCRYPT_RIJNDAEL_128;

		// Find the max length of the key, based on cipher and mode
		$size = mcrypt_get_key_size($cipher, $mode);

		if (isset($key[$size]))
		{
			// Shorten the key to the maximum size
			$key = substr($key, 0, $size);
		}
		else if (version_compare(PHP_VERSION, '5.6.0', '>='))
		{
			$key = $this->_normalize_key($key, $cipher, $mode);
		}

		// Store the key, mode, and cipher
		$this->_key    = $key;
		$this->_mode   = $mode;
		$this->_cipher = $cipher;

		// Store the IV size
		$this->_iv_size = mcrypt_get_iv_size($this->_cipher, $this->_mode);
	}

	/**
	 * Encrypts a plaintext and returns an encrypted ciphertext that can be
	 * decrypted back.
	 *
	 *     $cyphertext = $crypto->encode($plaintext);
	 *
	 * The encrypted binary data is encrypted using [base64](http://php.net/base64_encode)
	 * to convert it to a string. This string can be stored in a database,
	 * displayed, and passed using most other means without corruption.
	 *
	 * @param   string  $plaintext   Text to be encrypted
	 * @return  string  Cyphertext
	 */
	public function encode($plaintext)
	{
		// Get an initialization vector
		$iv = $this->_create_iv();

		// Encrypt the plaintext using the configured options and generated iv
		$cipher = mcrypt_encrypt($this->_cipher, $this->_key, $plaintext, $this->_mode, $iv);

		// Use base64 encoding to convert to a string
		return base64_encode($iv.$cipher);
	}

	/**
	 * Decrypts an encrypted ciphertext back to its original plaintext value.
	 *
	 *     $plaintext = $crypto->decode($ciphertext);
	 *
	 * @param   string  $ciphertext   encrypted string to be decrypted
	 * @return  FALSE   if decryption fails
	 * @return  string
	 */
	public function decode($ciphertext)
	{
		// Convert the data back to binary
		$data = base64_decode($ciphertext, TRUE);

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

	/**
	 * Proxy for the mcrypt_create_iv function - to allow mocking and testing against KAT vectors
	 *
	 * @return string the initialization vector or FALSE on error
	 */
	protected function _create_iv()
	{
		/*
		 * Silently use MCRYPT_DEV_URANDOM when the chosen random number generator
		 * is not one of those that are considered secure.
		 *
		 * Also sets static::$_rand to MCRYPT_DEV_URANDOM when it's not already set
		 */
		if ((static::$_rand !== MCRYPT_DEV_URANDOM) AND ( static::$_rand !== MCRYPT_DEV_RANDOM))
		{
			static::$_rand = MCRYPT_DEV_URANDOM;
		}

		// Create a random initialization vector of the proper size for the current cipher
		return mcrypt_create_iv($this->_iv_size, static::$_rand);
	}

	/**
	 * Normalize key for PHP 5.6 for backwards compatibility
	 *
	 * This method is a shim to make PHP 5.6 behave in a B/C way for
	 * legacy key padding when shorter-than-supported keys are used
	 *
	 * @param   string  $key    encryption key
	 * @param   string  $cipher mcrypt cipher
	 * @param   string  $mode   mcrypt mode
	 */
	protected function _normalize_key($key, $cipher, $mode)
	{
		// open the cipher
		$td = mcrypt_module_open($cipher, '', $mode, '');

		// loop through the supported key sizes
		foreach (mcrypt_enc_get_supported_key_sizes($td) as $supported) {
			// if key is short, needs padding
			if (strlen($key) <= $supported)
			{
				return str_pad($key, $supported, "\0");
			}
		}

		// at this point key must be greater than max supported size, shorten it
		return substr($key, 0, mcrypt_get_key_size($cipher, $mode));
	}

}

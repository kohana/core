<?php
/**
 * A wrapper class for the `defuse\php-encryption` package to be initialized
 * by the Encrypt factory class
 */

use \Defuse\Crypto\Crypto;
use \Defuse\Crypto\Key;

class Kohana_Encrypt_Defuse implements Kohana_Crypto {

	/**
	 * @var  \Defuse\Crypto\Key  Key to be used for encryption/decryption
	 */
	private $key;

	public function __construct(array $settings)
	{
		$this->key = Key::loadFromAsciiSafeString($settings['key']);
	}

	public function encode($plaintext)
	{
		return Crypto::encrypt($plaintext, $this->key);
	}

	public function decode($cyphertext)
	{
		return Crypto::decrypt($cyphertext, $this->key);
	}
}

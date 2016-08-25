<?php
/**
 * A wrapper class for the `defuse\php-encryption` package to be initialized
 * by the Encrypt factory class
 *
 * @package    Kohana
 * @category   Security
 * @author     Kohana Team
 * @copyright  (c) 2007-2016 Kohana Team
 * @license    http://kohanaframework.org/license
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
		if ( ! isset($settings['key']))
		{
			// No encryption key is provided!
			throw new Kohana_Exception('No encryption key defined');
		}

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

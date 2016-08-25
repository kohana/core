<?php
/**
 * A simple encryption interface used to abstract implementations
 * of external libraries
 *
 * @package    Kohana
 * @category   Security
 * @author     Kohana Team
 * @copyright  (c) 2007-2016 Kohana Team
 * @license    http://kohanaframework.org/license
 */
interface Kohana_Crypto {

	/**
	 * Constructor
	 *
	 * @param  array $settings Should include encryption key(s)
	 * @return Kohana_Crypto
	 */
	public function __construct(array $settings);

	/**
	 * Encrypts a plaintext string into hex-encoded cipher
	 *
	 * @param  string $plaintext Text to encrypt
	 * @return string Encrypted cipher text
	 */
	public function encode($plaintext);

	/**
	 * Decrypts a hex-encoded ciphertext string into a plaintext string
	 *
	 * @param  string $ciphertext Hex-encoded ciphertext
	 * @return string Decrypted plaintext
	 */
	public function decode($ciphertext);

}

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
class Kohana_Encrypt_Extended extends Kohana_Encrypt{
	
	public $crypt_functions;

	/**
	 * Creates a new mcrypt wrapper.
	 *
	 * @param   string   encryption key
	 * @param   string   mcrypt mode
	 * @param   string   mcrypt cipher
	 */
	public function __construct($config)
	{
		$this->_mode   = $config['mode'];
		$this->_cipher = $config['cipher'];
		$this->_hash_function = $config['hash_function'];
		
		// Find the max length of the key, based on cipher and mode
		$size = mcrypt_get_key_size($this->_cipher, $this->_mode);

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

		if(!array_key_exists('sha256', $config)){
			$config['sha256'] = array();
		}
		if(!array_key_exists('rounds', $config['sha256'])){
			$config['sha256']['rounds'] = 0;
		}
		
		if(!array_key_exists('sha512', $config)){
			$config['sha512'] = array();
		}
		if(!array_key_exists('rounds', $config['sha512'])){
			$config['sha512']['rounds'] = 0;
		}		
		
		if(!array_key_exists('blowfish', $config)){
			$config['blowfish'] = array();
		}
		if(!array_key_exists('cost', $config['blowfish'])){
			$config['blowfish']['cost'] = 0;
		}
		if ( ! isset($config['key']))
		{
			// Add the default mode
			$config['key'] = 'null';
		}
		$this->crypt_functions  = array(
			'sha256' => array(
				'value' => '5',
				'rounds' => $config['sha256']['rounds'],
				'salt_length' => 16
			),
			'sha512' => array(
				'value' => '6',
				'rounds' => $config['sha512']['rounds'],
				'salt_length' => 16
			),
			'blowfish' => array(
				'value' => '2a',
				'cost' => $config['blowfish']['cost'],
				'salt_length' => 22
			),
			'md5' => array(
				'value' => '1',
				'salt_length' => 12
			),
			'std_des' => array(
				'salt_length' => 2
			), 
			'ext_des' => array(
				'salt_length' => 9
			),
		);

		$this->_iv_size = mcrypt_get_iv_size($this->_cipher, $this->_mode);
		$this->_key_size = mcrypt_get_key_size($this->_cipher, $this->_mode);
		$this->_key = $config['key'];
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
		if(is_null($this->_key)){
			throw new Kohana_Exception('No encryption key is defined');
		}
		// Create a random initialization vector of the proper size for the current cipher
		$data_iv = Encrypt::random_data($this->_iv_size);
		$metadata_iv = Encrypt::random_data($this->_iv_size);
		$metadata_iv_b64 = base64_encode($metadata_iv);
		$data_iv_b64 = base64_encode($data_iv);

		$data_len = strlen($data);

		// Encrypt the data using the configured options and generated iv
		$metadata = $data_len . ":" . $data_iv_b64 .":".$this->hash($data);
		
		$metadata = $this->encrypt($metadata, $metadata_iv, $this->_key);

		$data = $this->encrypt($data, $data_iv, $this->_key);
		
		$data = "{$this->_cipher}:{$this->_mode}:{$metadata_iv_b64}:{$metadata}:{$data}";
		return $data;
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
		var_dump($this->_key);
		if(is_null($this->_key)){
			throw new Kohana_Exception('No encryption key is defined');
		}
		
		if (empty($data))
		{
			throw new Kohana_Exception('Cannot decrypt no data');
		}

		$old_cipher = $this->_cipher;
		$old_mode = $this->_mode;
		$old_iv_size = $this->_iv_size;
		
		list($this->_cipher, $this->_mode, $metadata_iv_b64, $metadata, $data) = explode(":", $data, 5);
		$iv_size = mcrypt_get_iv_size($this->_cipher, $this->_mode);
		$metadata_iv = base64_decode($metadata_iv_b64);
		
		if ($iv_size !== strlen($metadata_iv))
		{
			throw new Kohana_Exception('IV is the wrong size');
		}		
		
		# metadata will never end in \0
		$metadata = rtrim($this->decrypt($metadata, $metadata_iv, $this->_key), "\0");

		list($data_len, $data_iv_b64, $data_hash) = explode(":", $metadata, 3);
		$data_iv = base64_decode($data_iv_b64);
		if ($iv_size !== strlen($data_iv))
		{
			throw new Kohana_Exception('IV is the wrong size'); 
		}		
		
		$data = substr($this->decrypt($data, $data_iv, $this->_key), 0, $data_len);
		
		if(! $this->compare_hash($data, $data_hash)){
			throw new Kohana_Exception('Hashes do not match');
		}
		
		$this->_cipher = $old_cipher;
		$this->_mode = $old_mode;
		$this->_iv_size = $old_iv_size;
		
		return $data;
	}

	public function compare_hash($data, $hash){
		$old_hash_fcn = $this->_hash_function;
		$list = explode('$', $hash);
		array_shift($list);
		$hash_fcn = '';
		$salt = '';
		$cost = '';
		if(strlen($list[0]) < 3 ){
			if($list[0] === '1'){
				$hash_fcn = 'md5';
				$salt = $list[1];
			}
			else if($list[0] === '5')
			{
				$hash_fcn = 'sha256';
				$cost = $list[1];
				$salt = $list[2];
			}
			else if($list[0] === '6')
			{
				$hash_fcn = 'sha512';
				$cost = $list[1];
				$salt = $list[2];
			}
			else if($list[0] === '2a')
			{
				$hash_fcn = 'blowfish';
				$cost = $list[1];
				$salt = $list[2];
			}
		}else if(in_array($list[0], hash_algos()))
		{
			$hash_fcn =$list[0];
		}else if(strlen($list[0]) === 2){
			$hash_fcn = 'std_des';
			$salt = $list[0];
		}else if(strlen($list[0]) === 9){
			$hash_fcn = 'ext_des';
			$salt = $list[0];
		}
		$this->_hash_function = $hash_fcn;
		$data_hash = $this->hash($data, $salt, $cost);
		$this->_hash_function = $old_hash_fcn;
		return ($data_hash === $hash);
	}

	private function encrypt($data, $iv, $key = null, $base64 = true){

		if(is_null($key)){
			$key = $this->_key;
		}
		if(is_null($key)){
			throw new Kohana_Exception('No encryption key is defined');
		}
		$e = mcrypt_encrypt($this->_cipher, $key, $data, $this->_mode, $iv);

		if($base64){
			$e = base64_encode($e);
		}
		return $e;
	}

	private function decrypt($data, $iv, $key=null, $base64 = true){
		if(is_null($key)){
			$key = $this->_key;
		}
		if(is_null($key)){
			throw new Kohana_Exception('No encryption key is defined');
		}

		if($base64){
			$e = base64_decode($data);
		}
		$e = mcrypt_encrypt($this->_cipher, $key, $e, $this->_mode, $iv);

		return $e;
	}
	
	public function hash($data, $salt = null, $cycles = null){
		$hash = false;
		if(in_array($this->_hash_function, hash_algos()))
		{
			$hash = hash($this->_hash_function, $data);
			$hash = '$' . $this->_hash_function . '$' . $hash;
		}elseif(array_key_exists($this->_hash_function, $this->crypt_functions)){
				$cur_hash_fcn = $this->crypt_functions[$this->_hash_function];

				if(is_null($salt))
				{
					$salt = Text::random('alnum', $cur_hash_fcn['salt_length']);
				}else{
					if(strlen($salt) < $cur_hash_fcn['salt_length']){
						$salt .= Text::random('alnum', $cur_hash_fcn['salt_length'] - strlen($salt));
					}else{
						$salt = substr($salt, 0, $cur_hash_fcn['salt_length']);
					}
				}
				if(is_null($cycles))
				{
					if(array_key_exists('rounds', $cur_hash_fcn))
					{
						$cycles = $cur_hash_fcn['rounds'];
					}elseif(array_key_exists('cost', $cur_hash_fcn))
					{
						$cycles = $cur_hash_fcn['cost'];
					}
				}

			if($this->_hash_function === 'sha256' or
			   $this->_hash_function === 'sha512')
			{
				$salt = "rounds=" . $cycles .'$' . $cur_hash_fcn['value'] . '$' . $salt;
			}elseif($this->_hash_function === 'blowfish')
			{
				$salt = '$' . $cur_hash_fcn['value'] . '$' . $cycles .'$' . $salt;
			}elseif($this->_hash_function === 'md5')
			{
				$salt = '$' . $cur_hash_fcn['value'] . '$' . $salt;
			}
			$hash = crypt($data, $salt);
			$hash =  $salt .'$' . substr($hash, strlen($salt)); 
		}
		return $hash;
	}
	
}

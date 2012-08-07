# Encryption

The [Encryption] class performs a very easy-to-use way to encode and decode strings.

[!!] You have to enable Mcrypt extension in your PHP installation.

## Getting started

The [Encryption] class is part of Kohana's core, so there is no need to enable it in `bootstrap.php`.

Just create a new `encrypt.php` config file at `application/config` folder. The example below shows the default values:

    <?php defined('SYSPATH') or die('No direct script access.');

	return array(
		'default' => array(
			'key' => 'EK5yL1Gwnj',
			'mode' => 'nofb',
			'cipher' => 'rijndael-128'
		)
	);

You can create as many configurations as you want rather than the `default`:

	return array(
		'default' => array(
			'key' => 'EK5yL1Gwnj',
			'mode' => 'nofb',
			'cipher' => 'rijndael-128'
		),
		'creditcard' => array(
			'key' => '25eSzhOdbx',
			'mode' => 'cbc',
			'cipher' => 'rijndael-256'
		),
		'security_code' => array(
			'key' => '1iwd6OJa0q',
			'mode' => 'ebc',
			'cipher' => 'twofish'
		),
	);

## The parameters

[Encryption] class expects three parameters to get started:

*  Key - A secret passphrase that is used for encoding and decoding.

*  Mode - Determines how the encrypteddata is written in binary form. The default is **nofb**.

*  Cipher - Determines how the encryption is mathematically calculated. The default is **rijndael-128**, this is commonly known as "*AES-128*" and is an industry standard.

For more information about Mode and Cipher options, visit the PHP documentation:

[Mcrypt mode constants](http://br.php.net/mcrypt.constants)

[Ciphers options](http://br.php.net/mcrypt.ciphers)


## Loading a new instance

You can get a new instante of [Encryption] class by doing one of these methods:

	$encrypt = Encrypt::instance();
	// Or
	$encrypt = new Encrypt('EK5yL1Gwnj', 'nofb', 'rijndael-128');

[!!] If no instance name is given, the `default` is used.

[!!] If you choose to instantiating the object rather than using the static method, there is no need to create a `encrypt.php` config file.

## Enconding

The encrypted binary data is encoded using [base64](http://php.net/base64_encode) to convert it to a string. This string can be stored in a database, displayed, and passed using most other means without corruption.

	$data = $encrypt->encode('Some text');
		
	$this->response->body($data);

## Decoding

Decrypts an encoded string back to its original value.

	$original_data = $encrypt->decode($data);
		
	$this->response->body($original_data);
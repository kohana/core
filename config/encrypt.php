<?php defined('SYSPATH') OR die('No direct access allowed.');

return array
(
	'default_group' => 'default',
	'default'=>array(
		'driver'=>'extended',
		'hash_function' => 'whirlpool',
		'cipher'=>'blowfish',
		'mode' => MCRYPT_MODE_NOFB,
	),
	'password'=>array(
		'driver'=>'extended',
		'cipher'=>'blowfish',
		'mode' => MCRYPT_MODE_NOFB,
		'hash_function' => 'blowfish',
		'blowfish'=>array(
			'cost' => '15'
		)
	),
);
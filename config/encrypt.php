<?php

return array(

	'default' => array(

		'driver'     => 'defuse',
		'settings'   => array(
			/**
			 * The key setting must be set for `defuse` driver,
			 * it should be generated from the library itself:
			 *
			 *     \Defuse\Crypto\Key::createNewRandomKey()
			 *
			 * see https://github.com/defuse/php-encryption/blob/master/docs/classes/Key.md
			 */
			'key' => NULL,
		),
	),

);

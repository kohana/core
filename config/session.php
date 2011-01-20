<?php defined('SYSPATH') or die('No direct script access.');

return array(
	'native' => array(
        'name' => 'nsession',
        
    ),
    'cookie' => array(
        'name' => 'csession',
        'encrypted' => FALSE,
        
    ),
    /** Will default to this anyway in code. Can be overridden by Session::$default.*/
    'default' => 'native'
);

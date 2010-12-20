<?php defined('SYSPATH') or die('No direct script access.');

return array(
	CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; Kohana v'.Kohana::VERSION.' +http://kohanaframework.org/)',
	CURLOPT_CONNECTTIMEOUT => 5,
	CURLOPT_TIMEOUT        => 5,
	CURLOPT_HEADERFUNCTION => array('Request_Client_External', '_parse_headers'),
	CURLOPT_HEADER         => FALSE,
);
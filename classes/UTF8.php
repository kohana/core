<?php

class UTF8 extends Kohana_UTF8 {}

if (UTF8::$server_utf8 === NULL)
{
	// Determine if this server supports UTF-8 natively
	UTF8::$server_utf8 = extension_loaded('mbstring');
}

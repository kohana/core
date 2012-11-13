<?php

namespace Kohana\Core;

class Cache
{
	protected $_base;

	public function __construct($base)
	{
		$this->_base = $base;
	}

	public function save($name, $data)
	{
		$file = sha1($name).'.txt';
		$dir = $this->_base.$file[0].$file[1].'/';

		if ( ! is_dir($dir))
		{
			mkdir($dir, 0777, TRUE);
			// Set permissions (must be manually set to fix umask issues)
			chmod($dir, 0777);
		}

		// Force the data to be a string
		$data = serialize($data);

		return (bool) file_put_contents($dir.$file, $data/*, LOCK_EX*/);
	}

	public function read($name)
	{
		$file = sha1($name).'.txt';
		$dir = $this->_base.$file[0].$file[1].'/';

		$contents = NULL;

		if (is_file($dir.$file))
		{
			// Return the cache
			try
			{
				$contents = unserialize(file_get_contents($dir.$file));
			}
			catch (Exception $e)
			{
				// Cache is corrupt, let return happen normally.
			}
		}

		return $contents;
	}
}

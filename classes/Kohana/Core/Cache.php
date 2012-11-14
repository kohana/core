<?php

namespace Kohana\Core;

class Cache
{
	protected $_base;
	protected $_cache_life = 60;

	public function __construct($base, $cache_life = 60)
	{
		$this->_base = $base;
		$this->_cache_life = $cache_life OR 60;
	}

	public function save($name, $data)
	{
		$file = $this->_generate_file_name($name);
		$dir = $this->_generate_directory($file);

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

	public function read($name, $lifetime = NULL)
	{
		$file = $this->_generate_file_name($name);
		$dir = $this->_generate_directory($file);

		return $this->_read_cache($dir.$file, $lifetime);
	}

	protected function _generate_file_name($name)
	{
		return sha1($name).'.txt';
	}

	protected function _generate_directory($filename)
	{
		return $this->_base.$filename[0].$filename[1].'/';
	}

	protected function _read_cache($file, $lifetime)
	{
		if ($lifetime == NULL)
		{
			$lifetime = $this->_cache_life;
		}

		if (is_file($file) AND (time() - filemtime($file)) < $lifetime)
		{
			// Return the cache
			try
			{
				return unserialize(file_get_contents($file));
			}
			catch (Exception $e)
			{
				// Cache is corrupt, let return happen normally.
			}
		}
		else
		{
			try
			{
				// Cache has expired
				unlink($file);
			}
			catch (Exception $e)
			{
				// Cache has mostly likely already been deleted,
				// let return happen normally.
			}
		}
	}
}

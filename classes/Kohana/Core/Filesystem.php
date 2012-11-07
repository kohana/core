<?php

namespace Kohana\Core;

class Filesystem
{
	protected $_include_paths = array();

	public function __construct(array $paths)
	{
		$this->_paths = $paths;
	}

	public function include_paths()
	{
		return $this->_paths;
	}

	public function list_files($directory)
	{
		$found = array();

		foreach ($this->_paths as $path)
		{
			if (is_dir($path.$directory))
			{
				$dir = new \DirectoryIterator($path.$directory);

				foreach ($dir as $file)
				{
					$filename = $file->getFilename();

					// Relative filename is the array key
					$key = $directory.'/'.$filename;
					if ( ! isset($found[$key]))
						$found[$key] = realpath($file->getPathName());
				}
			}
		}

		return $found;
	}

	public function find_file($dir, $file, $ext = 'php')
	{
		$found = FALSE;

		$path = $this->_build_file_path($dir, $file, $ext);

		foreach ($this->_paths as $dir)
		{
			if (is_file($dir.$path))
			{
				$found = $dir.$path;
				break;
			}
		}

		return $found;
	}

	public function find_all_files($dir, $file, $ext = 'php')
	{
		$found = array();

		$path = $this->_build_file_path($dir, $file, $ext);

		foreach ($this->_paths as $dir)
		{
			if (is_file($dir.$path))
				$found[] = $dir.$path;
		}

		return $found;
	}

	protected function _build_file_path($dir, $file, $ext)
	{
		return "$dir/$file.$ext";
	}
}

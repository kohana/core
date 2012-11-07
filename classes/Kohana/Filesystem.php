<?php

namespace Kohana;

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

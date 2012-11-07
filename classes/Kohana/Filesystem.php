<?php

namespace Kohana;

class Filesystem
{
	protected $_include_paths = array();

	public function __construct($apppath, $modules, $syspath)
	{
		$this->_include_paths = array_merge(array($apppath), $modules, array($syspath));
	}

	public function include_paths()
	{
		return $this->_include_paths;
	}

	public function find_file($dir, $file)
	{
		$found = FALSE;

		$path = $dir.'/'.$file.'.php';

		foreach ($this->_include_paths as $dir)
		{
			if (is_file($dir.$path))
				$found = $dir.$path;
		}

		return $found;
	}
}

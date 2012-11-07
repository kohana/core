<?php

namespace Kohana\Core;

class AutoLoad
{
	protected $_filesystem;
	protected $_root = 'classes';

	public function __construct(Filesystem $filesystem, $root = 'classes')
	{
		$this->_filesystem = $filesystem;
		$this->_root = $root;
	}

	public function load($class)
	{
		// Transform the class name according to PSR-0
		$class     = ltrim($class, '\\');
		$file      = '';
		$namespace = '';

		if ($last_namespace_position = strripos($class, '\\'))
		{
			$namespace = substr($class, 0, $last_namespace_position);
			$class     = substr($class, $last_namespace_position + 1);
			$file      = str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR;
		}

		$file .= str_replace('_', DIRECTORY_SEPARATOR, $class);

		if ($path = $this->_filesystem->find_file($this->_root, $file))
		{
			// Load the class file
			require_once $path;

			// Class has been found
			return TRUE;
		}

		// Class is not in the filesystem
		return FALSE;
	}
}

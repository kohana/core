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

	/**
	 * Provides auto-loading support of classes that follow Kohana's [class
	 * naming conventions](kohana/conventions#class-names-and-file-location).
	 * See [Loading Classes](kohana/autoloading) for more information.
	 *
	 * You must provide a Filesystem instance to the constructor of this class.
	 *
	 *     $autoload = new AutoLoad($filesystem);
	 *
	 *     // Loads classes/My/Class/Name.php
	 *     $autoload->load('My_Class_Name');
	 *
	 * or with a custom directory:
	 *
	 *     // Loads vendor/My/Class/Name.php
	 *     $autoload = new AutoLoad($filesystem 'vendor');
	 *     $autoload->load('My_Class_Name');
	 *
	 * You should never have to call this function, as simply calling a class
	 * will cause it to be called.
	 *
	 * This function must be enabled as an autoloader in the bootstrap:
	 *
	 *     spl_autoload_register(array(new AutoLoad($filesystem), 'auto_load'));
	 *
	 * @param   string  $class      Class name
	 * @param   string  $directory  Directory to load from
	 * @return  boolean
	 */
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

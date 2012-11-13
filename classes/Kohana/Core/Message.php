<?php

namespace Kohana\Core;

class Message
{
	public function __construct(Filesystem $filesystem)
	{
		$this->_filesystem = $filesystem;
	}

	public function message($file, $path)
	{
		$messages = $this->_load_messages($file);
		return $messages[$file][$path];
	}

	public function messages($file)
	{
		$messages = $this->_load_messages($file);
		return $messages[$file];
	}

	protected function _load_messages($file)
	{
		$messages = array();

		$files = $this->_filesystem->find_all_files('messages', $file);
		if ($files)
		{
			$messages[$file] = array();

			foreach ($files as $f)
			{
				$messages[$file] = \array_merge($messages[$file], $this->_filesystem->load($f));
			}
		}

		return $messages;
	}
}

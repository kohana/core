<?php

/**
 * A log level filter
 *
 * @package    Kohana
 * @category   Logging
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Log_Filter_Level implements Kohana_Log_Filter {

	/**
	 * @var a hashtable to lookup if a log level is enabled or not
	 *
	 * TRUE value indicates that the level at the key is enabled
	 */
	private $enabled_log_levels;

	public function __construct(array $levels)
	{
		// construct log level lookup hashtable
		$this->enabled_log_levels = array_fill_keys(Log::get_levels(), FALSE);

		$this->enable($levels);
	}

	public function enable(array $levels)
	{
		return $this->toggle($levels, TRUE);
	}

	public function disable(array $levels)
	{
		return $this->toggle($levels, FALSE);
	}

	private function toggle(array $levels, $toggle)
	{
		$psr_levels = array_map('Log::to_psr_level', $levels);

		foreach ($psr_levels as $level)
		{
			$this->enabled_log_levels[$level] = $toggle;
		}

		return $this;
	}

	public function process(array $messages)
	{
		// if levels are either all enabled (TRUE) or all desabled (FALSE)
		if(count(array_unique($this->enabled_log_levels)) === 1)
		{
			// Return all messages, or return an empty array
			return current($this->enabled_log_levels) ? $messages : array();
		}

		$filtered = array();

		foreach ($messages as $message)
		{
			if ($this->enabled_log_levels[$message['level']])
			{
				$filtered[] = $message;
			}
		}

		return $filtered;
	}

}

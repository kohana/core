<?php

namespace Kohana\Core\Log\Filter;

use Kohana\Core\Log\LogFilter;
use Log;
use Psr\Log\InvalidArgumentException;

/**
 * A log level filter
 *
 * @package    Kohana
 * @category   Logging
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class PSRLevel implements LogFilter {

	/**
	 * @var boolean a hashtable to lookup if a log level is enabled or not
	 *
	 * TRUE value indicates that the level at the key is enabled
	 */
	private $enabled_log_levels;

	public function __construct(array $levels)
	{

		// construct log level lookup hashtable
		$this->enabled_log_levels = array_fill_keys(Log::get_levels(), FALSE);

		foreach ($levels as $level)
		{
			if ( ! isset($this->enabled_log_levels[$level]))
			{
				throw new InvalidArgumentException(
					"Argument 1 of Log_Filter_PSRLevel constructor must be an array of valid PSR log levels"
				);
			}

			$this->enabled_log_levels[$level] = TRUE;
		}

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

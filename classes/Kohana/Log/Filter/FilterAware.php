<?php

namespace Kohana\Core\Log\Filter;

use Kohana\Core\Log\LogFilter;

/**
 * Interface to make log writers aware of log filters
 */
interface FilterAware {

	public function attach_filter(LogFilter $filter);

	public function detach_filter(LogFilter $filter);

	/**
	 * Process filtering of the log messages, usually this means just to call
	 * process() of all Kohana_Log_Filter objects attached
	 *
	 * @param array $messages log messages to filter
	 * @return array filtered log messages
	 */
	public function filter(array $messages);
}

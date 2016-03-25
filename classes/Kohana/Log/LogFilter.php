<?php

namespace Kohana\Core\Log;

/**
 * Interface to filter logs
 */
interface LogFilter {

	/**
	 * Process filtering of the log messages
	 *
	 * @param array $messages log messages to filter
	 * @return array filtered log messages
	 */
	public function process(array $messages);
}

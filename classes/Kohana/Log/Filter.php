<?php
/**
 * Interface to filter logs
 */
interface Kohana_Log_Filter {

	/**
	 * Process filtering of the log messages
	 *
	 * @param array $messages log messages to filter
	 * @return array filtered log messages
	 */
	public function process(array $messages);
}

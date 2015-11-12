<?php
/**
 * Interface to make log writers aware of log filters
 */
interface Kohana_Log_Filter_Aware {

	public function attach_filter(Kohana_Log_Filter $filter);

	public function detach_filter(Kohana_Log_Filter $filter);

	/**
	 * Process filtering of the log messages, usually this means just to call
	 * process() of all Kohana_Log_Filter objects attached
	 *
	 * @param array $messages log messages to filter
	 * @return array filtered log messages
	 */
	public function filter(array $messages);
}

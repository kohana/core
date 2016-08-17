<?php
/**
 * Filter out logs when they are generated through an exception
 *
 * @package    Kohana
 * @category   Logging
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Log_Filter_Exception implements Kohana_Log_Filter {

	/**
	 * @var Exception class name to filter log entries against
	 */
	private $exception;

	public function __construct($exception = 'Exception')
	{
		if (!is_string($exception))
		{
			throw new InvalidArgumentException('Argument 1 of the constructor of Log_Filter_Exception must be a string');
		}
		$this->exception = $exception;
	}

	public function process(array $messages)
	{
		$filtered = array();

		foreach ($messages as $message)
		{
			if (
			  (isset($message['exception']))
			  AND
			  ($message['exception'] instanceof $this->exception)
			)
			{
				$filtered[] = $message;
			}
		}

		return $filtered;
	}

}

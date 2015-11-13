<?php
/**
 * Filter out logs by the body of the log message using a regex pattern
 *
 * @package    Kohana
 * @category   Logging
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Log_Filter_Body implements Kohana_Log_Filter {

	/**
	 * @var a regex pattern to test log entries' bodies against
	 */
	private $regex_pattern;

	public function __construct($regex_pattern)
	{
		if ( ! is_string($regex_pattern))
		{
			throw new InvalidArgumentException('Argument 1 of the constructor of Log_Filter_Body must be a string');
		}
		$this->regex_pattern = $regex_pattern;
	}

	public function process(array $messages)
	{
		$filtered = array();

		foreach ($messages as $message)
		{
			if (preg_match($this->regex_pattern, $message['body']))
			{
				$filtered[] = $message;
			}
		}

		return $filtered;
	}

}

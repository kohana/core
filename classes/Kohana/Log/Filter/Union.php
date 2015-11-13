<?php
/**
 * A log filter that accepts other filters and make a union (OR logic) of logs
 *
 * @package    Kohana
 * @category   Logging
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Log_Filter_Union implements Kohana_Log_Filter_Aware, Kohana_Log_Filter {

	/**
	 * @var array of Kohana_Log_Filter
	 */
	private $filters;

	public function __construct(array $filters)
	{
		foreach ($filters as $filter)
		{
			$this->attach_filter($filter);
		}
	}

	public function process(array $messages)
	{
		$filtered = array();

		foreach ($this->filters as $filter)
		{
			$filtered[] = $filter->process($messages);
		}

		return array_unique($filtered);
	}

	/**
	 * Ataches a log filter
	 *
	 * @param Kohana_Log_Filter $filter
	 * @return Log_Filter_Union
	 */
	public function attach_filter(Kohana_Log_Filter $filter)
	{
		$this->filters[spl_object_hash($filter)] = $filter;

		return $this;
	}

	/**
	 * Detaches a log filter
	 *
	 * @param Kohana_Log_Filter $filter
	 * @return Log_Filter_Union
	 */
	public function detach_filter(Kohana_Log_Filter $filter)
	{
		unset($this->filters[spl_object_hash($filter)]);

		return $this;
	}

	public function filter(array $messages)
	{
		return $this->process($messages);
	}
}

<?php
/**
 * Extends the PRS-3 LoggerInterface, with additional methods for deffered logging.
 */
interface Kohana_Logger extends Psr\Log\LoggerInterface {

	public function attach(Log_Writer $writer, $levels = array(), $min_level = 0);

	public function detach(Log_Writer $writer);

	public function write();
}

<?php

namespace Kohana\Core\Log;

/**
 * Interface to attach/detach Log_Writer(s) for deffered log writing
 */
interface LogBuffer {

	public function attach(LogWriter $writer);

	public function detach(LogWriter $writer);

	public function flush();
}

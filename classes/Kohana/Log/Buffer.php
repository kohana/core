<?php
/**
 * Interface to attach/detach Log_Writer(s) for deffered log writing
 */
interface Kohana_Log_Buffer {

	public function attach(Log_Writer $writer);

	public function detach(Log_Writer $writer);

	public function flush();
}

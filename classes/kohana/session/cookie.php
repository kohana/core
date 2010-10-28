<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Cookie-based session class.
 *
 * @package    Kohana
 * @category   Session
 * @author     Kohana Team
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Session_Cookie extends Session {

	protected function _read($id = NULL)
	{
		return Cookie::get($this->_name, NULL);
	}

	protected function _regenerate()
	{
		// Cookie sessions have no id
		return NULL;
	}

	protected function _write()
	{
		return Cookie::set($this->_name, $this->__toString(), $this->_lifetime);
	}

	protected function _destroy()
	{
		return Cookie::delete($this->_name);
	}

} // End Session_Cookie

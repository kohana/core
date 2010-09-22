<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Native PHP session class.
 *
 * @package    Kohana
 * @category   Session
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Session_Native extends Session {

	public function id()
	{
		return session_id();
	}

	protected function _read($id = NULL)
	{
		// Sync up the session cookie with Cookie parameters
		session_set_cookie_params($this->_lifetime, Cookie::$path, Cookie::$domain, Cookie::$secure, Cookie::$httponly);

		// Do not allow PHP to send Cache-Control headers
		session_cache_limiter(FALSE);

		// Set the session cookie name
		session_name($this->_name);

		if ($id)
		{
			// Set the session id
			session_id($id);
		}

		// Start the session
		session_start();

		// Use the $_SESSION global for storing data
		$this->_data =& $_SESSION;

		return NULL;
	}

	protected function _regenerate()
	{
		// Regenerate the session id
		session_regenerate_id();

		return session_id();
	}

	protected function _write()
	{
		// Write and close the session
		session_write_close();

		return TRUE;
	}

	protected function _destroy()
	{
		// Destroy the current session
		session_destroy();

		return ! session_id();
	}

} // End Session_Native

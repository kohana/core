<?php defined('SYSPATH') or die('No direct script access.');
/**
 * View fragment caching.
 *
 *     <?php if ( ! Fragment::load('footer')): ?>
 *     <p>This content will be cached.</p>
 *     <?php Fragment::save(); endif ?>
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Kohana_Fragment {

	/**
	 * @var  integer  default number of seconds to cache for
	 */
	public static $lifetime = 30;

	// List of buffer => cache key
	protected static $_caches = array();

	/**
	 * Load a fragment from cache and display it. Multiple fragments can
	 * be nested.
	 *
	 * @param   string   fragment name
	 * @param   integer  fragment cache lifetime
	 * @return  boolean
	 */
	public static function load($name, $lifetime = NULL)
	{
		// Set the cache key name
		$cache_key = 'Fragment::cache('.$name.')';

		// Set the cache lifetime
		$lifetime = ($lifetime === NULL) ? Fragment::$lifetime : (int) $lifetime;

		if ($fragment = Kohana::cache($cache_key, NULL, $lifetime))
		{
			// Display the cached fragment now
			echo $fragment;

			return TRUE;
		}
		else
		{
			// Start the output buffer
			ob_start();

			// Store the cache key by the buffer level
			Fragment::$_caches[ob_get_level()] = $cache_key;

			return FALSE;
		}
	}

	/**
	 * Saves a fragment in the cache.
	 *
	 * @return  void
	 */
	public static function save()
	{
		// Get the buffer level
		$level = ob_get_level();

		if (isset(Fragment::$_caches[$level]))
		{
			// Get the cache key based on the level
			$cache_key = Fragment::$_caches[$level];

			// Delete the cache key, we don't need it anymore
			unset(Fragment::$_caches[$level]);

			// Get the output buffer and display it at the same time
			$fragment = ob_get_flush();

			// Cache the fragment
			Kohana::cache($cache_key, $fragment);
		}
	}

	/**
	 * Delete a cached fragment.
	 *
	 * @param   string   fragment name
	 * @return  void
	 */
	public static function delete($name)
	{
		// Set the cache key name
		$cache_key = 'Fragment::cache('.$name.')';

		// Invalid the cache
		Kohana::cache($cache_key, NULL, -3600);
	}

} // End Fragment

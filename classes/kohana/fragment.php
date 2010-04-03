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
 * @license    http://kohanaphp.com/license
 */
class Kohana_Fragment {

	/**
	 * @var  integer  default number of seconds to cache for
	 */
	public static $lifetime = 30;

	/**
	 * @var  boolean  default multilingual fragment support
	 */
	public static $i18n = FALSE;

	// List of buffer => cache key
	protected static $_caches = array();

	/**
	 * Generate the cache key name for a fragment.
	 *
	 * @param   string   fragment name
	 * @param   boolean  multilingual fragment support
	 * @return  string
	 */
	protected static function _cache_key($name, $i18n = NULL)
	{
		if ($i18n === NULL)
		{
			// Use the default setting
			$i18n = Fragment::$i18n;
		}

		// Language prefix for cache key
		$i18n = ($i18n === TRUE) ? I18n::lang() : '';

		// Note: $i18n and $name need to be delimited to prevent naming collisions
		return 'Fragment::cache('.$i18n.'+'.$name.')';
	}

	/**
	 * Load a fragment from cache and display it. Multiple fragments can
	 * be nested.
	 *
	 * @param   string   fragment name
	 * @param   integer  fragment cache lifetime
	 * @param   boolean  multilingual fragment support
	 * @return  boolean
	 */
	public static function load($name, $lifetime = NULL, $i18n = NULL)
	{
		// Set the cache lifetime
		$lifetime = ($lifetime === NULL) ? Fragment::$lifetime : (int) $lifetime;

		// Get the cache key name
		$cache_key = Fragment::_cache_key($name, $i18n);

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
	 * @param   boolean  multilingual fragment support
	 * @return  void
	 */
	public static function delete($name, $i18n = NULL)
	{
		// Invalid the cache
		Kohana::cache(Fragment::_cache_key($name, $i18n), NULL, -3600);
	}

} // End Fragment

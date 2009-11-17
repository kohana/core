<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Internationalization (i18n) class.
 *
 * @package    I18n
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_I18n {

	/**
	 * @var  string   target language: en-us, es-es, zh-cn, etc
	 */
	public static $lang = 'en-us';

	// Cache of loaded languages
	protected static $_cache = array();

	/**
	 * Returns translation of a string. If no translation exists, the original
	 * string will be returned.
	 *
	 * @param   string   text to translate
	 * @return  string
	 */
	public static function get($string)
	{
		if ( ! isset(I18n::$_cache[I18n::$lang]))
		{
			// Load the translation table
			I18n::load(I18n::$lang);
		}

		// Return the translated string if it exists
		return isset(I18n::$_cache[I18n::$lang][$string]) ? I18n::$_cache[I18n::$lang][$string] : $string;
	}

	/**
	 * Returns the translation table for a given language.
	 *
	 * @param   string   language to load
	 * @return  array
	 */
	public static function load($lang)
	{
		if ( ! isset(I18n::$_cache[$lang]))
		{
			// New translation table
			$table = array();

			// Split the language: language, region, locale, etc
			$parts = explode('-', $lang);

			do
			{
				// Create a path from the parts left
				$path = implode(DIRECTORY_SEPARATOR, $parts);

				if ($files = Kohana::find_file('i18n', $path))
				{
					foreach ($files as $file)
					{
						// Merge the language strings into the translation table
						$table = array_merge($table, Kohana::load($file));
					}
				}

				// Remove the last part
				array_pop($parts);
			}
			while ($parts);

			// Cache the translation table locally
			I18n::$_cache[$lang] = $table;
		}

		return I18n::$_cache[$lang];
	}

	final private function __construct()
	{
		// This is a static class
	}

} // End I18n

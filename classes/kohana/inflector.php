<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Inflector helper class. Inflection is changing the form of a word based on
 * the context it is used in. For example, changing a word into a plural form.
 *
 * [!!] Inflection is only tested with English, and is will not work with other languages.
 *
 * @package    Kohana
 * @category   Helpers
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Inflector {

	// Cached inflections
	protected static $cache = array();

	// Uncountable and irregular words
	protected static $uncountable;
	protected static $irregular;

	/**
	 * Checks if a word is defined as uncountable. An uncountable word has a
	 * single form. For instance, one "fish" and many "fish", not "fishes".
	 *
	 *     Inflector::uncountable('fish'); // TRUE
	 *     Inflector::uncountable('cat');  // FALSE
	 *
	 * If you find a word is being pluralized improperly, it has probably not
	 * been defined as uncountable in `config/inflector.php`. If this is the
	 * case, please report [an issue](http://dev.kohanaphp.com/projects/kohana3/issues).
	 *
	 * @param   string   word to check
	 * @return  boolean
	 */
	public static function uncountable($str)
	{
		if (Inflector::$uncountable === NULL)
		{
			// Cache uncountables
			Inflector::$uncountable = Kohana::config('inflector')->uncountable;

			// Make uncountables mirrored
			Inflector::$uncountable = array_combine(Inflector::$uncountable, Inflector::$uncountable);
		}

		return isset(Inflector::$uncountable[strtolower($str)]);
	}

	/**
	 * Makes a plural word singular.
	 *
	 *     echo Inflector::singular('cats'); // "cat"
	 *     echo Inflector::singular('fish'); // "fish", uncountable
	 *
	 * You can also provide the count to make inflection more intelligent.
	 * In this case, it will only return the singular value if the count is
	 * greater than one and not zero.
	 *
	 *     echo Inflector::singular('cats', 2); // "cats"
	 *
	 * [!!] Special inflections are defined in `config/inflector.php`.
	 *
	 * @param   string   word to singularize
	 * @param   integer  number of things
	 * @return  string
	 * @uses    Inflector::uncountable
	 */
	public static function singular($str, $count = NULL)
	{
		// Remove garbage
		$str = strtolower(trim($str));

		if (is_string($count))
		{
			// Convert to integer when using a digit string
			$count = (int) $count;
		}

		// Do nothing with a single count
		if ($count === 0 OR $count > 1)
			return $str;

		// Cache key name
		$key = 'singular_'.$str.$count;

		if (isset(Inflector::$cache[$key]))
			return Inflector::$cache[$key];

		if (Inflector::uncountable($str))
			return Inflector::$cache[$key] = $str;

		if (empty(Inflector::$irregular))
		{
			// Cache irregular words
			Inflector::$irregular = Kohana::config('inflector')->irregular;
		}

		if ($irregular = array_search($str, Inflector::$irregular))
		{
			$str = $irregular;
		}
		elseif (preg_match('/[sxz]es$/', $str) OR preg_match('/[^aeioudgkprt]hes$/', $str))
		{
			// Remove "es"
			$str = substr($str, 0, -2);
		}
		elseif (preg_match('/[^aeiou]ies$/', $str))
		{
			$str = substr($str, 0, -3).'y';
		}
		elseif (substr($str, -1) === 's' AND substr($str, -2) !== 'ss')
		{
			$str = substr($str, 0, -1);
		}

		return Inflector::$cache[$key] = $str;
	}

	/**
	 * Makes a singular word plural.
	 *
	 *     echo Inflector::plural('fish'); // "fish", uncountable
	 *     echo Inflector::plural('cat');  // "cats"
	 *
	 * You can also provide the count to make inflection more intelligent.
	 * In this case, it will only return the plural value if the count is
	 * not one.
	 *
	 *     echo Inflector::singular('cats', 3); // "cats"
	 *
	 * [!!] Special inflections are defined in `config/inflector.php`.
	 *
	 * @param   string  word to pluralize
	 * @return  string
	 * @uses    Inflector::uncountable
	 */
	public static function plural($str, $count = NULL)
	{
		// Remove garbage
		$str = strtolower(trim($str));

		if (is_string($count))
		{
			// Convert to integer when using a digit string
			$count = (int) $count;
		}

		// Do nothing with singular
		if ($count === 1)
			return $str;

		// Cache key name
		$key = 'plural_'.$str.$count;

		if (isset(Inflector::$cache[$key]))
			return Inflector::$cache[$key];

		if (Inflector::uncountable($str))
			return Inflector::$cache[$key] = $str;

		if (empty(Inflector::$irregular))
		{
			// Cache irregular words
			Inflector::$irregular = Kohana::config('inflector')->irregular;
		}

		if (isset(Inflector::$irregular[$str]))
		{
			$str = Inflector::$irregular[$str];
		}
		elseif (preg_match('/[sxz]$/', $str) OR preg_match('/[^aeioudgkprt]h$/', $str))
		{
			$str .= 'es';
		}
		elseif (preg_match('/[^aeiou]y$/', $str))
		{
			// Change "y" to "ies"
			$str = substr_replace($str, 'ies', -1);
		}
		else
		{
			$str .= 's';
		}

		// Set the cache and return
		return Inflector::$cache[$key] = $str;
	}

	/**
	 * Makes a phrase camel case. Spaces and underscores will be removed.
	 *
	 *     $str = Inflector::camelize('mother cat');     // "motherCat"
	 *     $str = Inflector::camelize('kittens in bed'); // "kittensInBed"
	 *
	 * @param   string  phrase to camelize
	 * @return  string
	 */
	public static function camelize($str)
	{
		$str = 'x'.strtolower(trim($str));
		$str = ucwords(preg_replace('/[\s_]+/', ' ', $str));

		return substr(str_replace(' ', '', $str), 1);
	}

	/**
	 * Makes a phrase underscored instead of spaced.
	 *
	 *     $str = Inflector::underscore('five cats'); // "five_cats";
	 *
	 * @param   string  phrase to underscore
	 * @return  string
	 */
	public static function underscore($str)
	{
		return preg_replace('/\s+/', '_', trim($str));
	}

	/**
	 * Makes an underscored or dashed phrase human-readable.
	 *
	 *     $str = Inflector::humanize('kittens-are-cats'); // "kittens are cats"
	 *     $str = Inflector::humanize('dogs_as_well');     // "dogs as well"
	 *
	 * @param   string  phrase to make human-readable
	 * @return  string
	 */
	public static function humanize($str)
	{
		return preg_replace('/[_-]+/', ' ', trim($str));
	}

} // End Inflector

<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Internationalization (i18n) class. Provides language loading and translation
 * methods without dependencies on [gettext](http://php.net/gettext).
 *
 * Typically this class would never be used directly, but used via the __()
 * function, which loads the message and replaces parameters:
 *
 *     // Display a translated message
 *     echo __('Hello, world');
 *
 *     // With parameter replacement
 *     echo __('Hello, :user', array(':user' => $username));
 *
 * @package    Kohana
 * @category   Internationalization
 * @author     Kohana Team
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_I18n {

	/**
	 * @var  string   target language: en-us, es-es, zh-cn, etc
	 */
	public $lang = 'en-us';

	/**
	 * @var  string  source language: en-us, es-es, zh-cn, etc
	 */
	public $source = 'en-us';

	/**
	 * @var  array  cache of loaded languages
	 */
	protected $_cache = array();
	
	/**
	 * @var	array	list of i18n drivers
	 */
	protected $_sources = array();

	/**
	 * Get and set the target language.
	 *
	 *     // Get the current language
	 *     $lang = $i18n->lang();
	 *
	 *     // Change the current language to Spanish
	 *     $i18n->lang('es-es');
	 *
	 * @param   string  $lang   new language setting
	 * @return  string
	 * @since   3.0.2
	 */
	public function lang($lang = NULL)
	{
		if ($lang)
		{
			// Normalize the language
			$this->lang = strtolower(str_replace(array(' ', '_'), '-', $lang));
		}

		return $this->lang;
	}
	
	/**
	 * Attach an I18n reader. By default, the reader will be added as
	 * the first used reader. However, if the reader should be used only when
	 * all other readers fail, use `FALSE` for the second parameter.
	 *
	 *     $i18n->attach($reader);        // Try first
	 *     $i18n->attach($reader, FALSE); // Try last
	 *
	 * @param   Kohana_I18n_Source    $source instance
	 * @param   boolean               $first  add the reader as the first used object
	 * @return  $this
	 */
	public function attach(Kohana_I18n_Source $source, $first = TRUE)
	{
		if ($first === TRUE)
		{
			// Place the log reader at the top of the stack
			array_unshift($this->_sources, $source);
		}
		else
		{
			// Place the reader at the bottom of the stack
			$this->_sources[] = $source;
		}

		return $this;
	}
	
	/**
	 * Detach an i18n reader.
	 *
	 *     $i18n->detach($reader);
	 *
	 * @param   Kohana_I18n_Source    $source instance
	 * @return  $this
	 */
	public function detach(Kohana_I18n_Source $source)
	{
		if (($key = array_search($source, $this->_sources)) !== FALSE)
		{
			// Remove the writer
			unset($this->_sources[$key]);
		}

		return $this;
	}

	/**
	 * Returns translation of a string. If no translation exists, the original
	 * string will be returned. No parameters are replaced.
	 *
	 *     $hello = $i18n->get('Hello friends, my name is :name');
	 *
	 * @param   string  $string text to translate
	 * @param   string  $lang   target language
	 * @return  string
	 */
	public function get($string, $lang = NULL)
	{
		if ( ! $lang)
		{
			// Use the global target language
			$lang = $this->lang;
		}

		// Load the translation table for this language
		$table = $this->load($lang);

		// Return the translated string if it exists
		return isset($table[$string]) ? $table[$string] : $string;
	}

	/**
	 * Returns the translation table for a given language.
	 *
	 *     // Get all defined Spanish messages
	 *     $messages = $i18n->load('es-es');
	 *
	 * @param   string  $lang   language to load
	 * @return  array
	 */
	public function load($lang)
	{
		if( ! count($this->_sources))
		{
			return array();
		}
		
		if (isset($this->_cache[$lang]))
		{
			return $this->_cache[$lang];
		}
		
		// New translation table
		$table = array();

		foreach($this->_sources as $source)
		{
			$table = array_merge($table, $source->load($lang));
		}

		// Cache the translation table locally
		return $this->_cache[$lang] = $table;
	}

} // End I18n

if ( ! function_exists('__'))
{
	/**
	 * Kohana translation/internationalization function. The PHP function
	 * [strtr](http://php.net/strtr) is used for replacing parameters.
	 *
	 *    __('Welcome back, :user', array(':user' => $username));
	 *
	 * [!!] The target language is defined by [I18n::$lang].
	 * 
	 * @uses    I18n::get
	 * @param   string  $string text to translate
	 * @param   array   $values values to replace in the translated text
	 * @param   string  $lang   source language
	 * @return  string
	 */
	function __($string, array $values = NULL, $lang = NULL)
	{
		if ($lang === NULL)
		{
			$lang = Kohana::$i18n->source;
		}
		
		if ($lang !== Kohana::$i18n->lang)
		{
			// The message and target languages are different
			// Get the translation for this message
			$string = Kohana::$i18n->get($string);
		}

		return empty($values) ? $string : strtr($string, $values);
	}
}

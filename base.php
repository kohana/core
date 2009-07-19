<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Kohana translation/internationalization function.
 *
 *    __('Welcome back, :user', array(':user' => $username));
 *
 * @param   string  text to translate
 * @param   array   values to replace in the translated text
 * @return  string
 */
function __($string, array $values = NULL, $lang = 'en-us')
{
	if ($lang !== I18n::$lang)
	{
		// The message and target languages are different
		// Get the translation for this message
		$string = I18n::get($string);
	}

	return empty($values) ? $string : strtr($string, $values);
}

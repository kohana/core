<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Interface for config readers
 *
 * @package    Kohana
 * @category   Internationalization
 * @author     Kohana Team
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
interface Kohana_I18n_Reader extends Kohana_I18n_Source
{
	
	/**
	 * Tries to load the specificed i18n translation table
	 *
	 * Returns FALSE if table does not exist or an array if it does
	 *
	 * @param  string $lang Table language
	 * @return boolean|array
	 */
	public function load($lang);
	
}

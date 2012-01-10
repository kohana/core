<?php
/**
 * File-based translation reader. Multiple directories can be
 * used by attaching multiple instances of this class to [Kohana_I18n].
 *
 * @package    Kohana
 * @category   Internationalization
 * @author     Kohana Team
 * @copyright  (c) 2009-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_I18n_File_Reader implements Kohana_I18n_Reader {

	/**
	 * The directory where config files are located
	 * @var string
	 */
	protected $_directory = '';

	/**
	 * Creates a new file reader using the given directory as a source
	 *
	 * @param string    $directory  Translation directory to search
	 */
	public function __construct($directory = 'i18n')
	{
		// Set the configuration directory name
		$this->_directory = trim($directory, '/');
	}

	/**
	 * Load and merge all of the translation tables for this language.
	 *
	 *     $config->load($name);
	 *
	 * @param   string  $lang  translation table language
	 * @return  array	translation table
	 * @uses    Kohana::load
	 */
	public function load($lang)
	{
		$table = array();
		
		// Split the language: language, region, locale, etc
		$parts = explode('-', $lang);
		
		do
		{
			// Create a path for this set of parts
			$path = implode(DIRECTORY_SEPARATOR, $parts);
			
			if ($files = Kohana::find_file($this->_directory, $path, NULL, TRUE))
			{
				foreach ($files as $file)
				{
					// Merge each file to the translation table array
					$table = Arr::merge($table, Kohana::load($file));
				}
			}
			
			// Remove the last part
			array_pop($parts);
		}
		while ($parts);
		
		return $table;
	}

} // End Kohana_I18n_File_Reader

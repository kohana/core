<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Abstract model class. All models should extend this class.
 * See [MVC models](kohana/mvc/models) for more information.
 *
 * @package    Kohana
 * @category   Model
 * @author     Kohana Team
 * @copyright  (c) 2008-2014 Kohana Team
 * @license    http://kohanaframework.org/license
 */
abstract class Kohana_Model {

	// Prefix to class name
	const CLASS_PREFIX = 'Model_';

	/**
	 * Create a new model instance.
	 *
	 *     $model = Model::factory($name);
	 *
	 * @param   string  $name      Model name
	 * @param   mixed   $settings  Model settings, eg: id
	 * @return  Model
	 */
	public static function factory($name, $settings = NULL)
	{
		// Add the model prefix
		$class = Model::CLASS_PREFIX.$name;

		return new $class($settings);
	}

}

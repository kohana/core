<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Abstract class, inherited static classes that require initialization properties.
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2014 Kohana Team
 * @license    http://kohanaframework.org/license
 */
abstract class Kohana_Statical {

	/**
	 * @var  bool  Properties initialized?
	 */
	protected static $_initialized = FALSE;

	/**
	 * Iinitialize properties (analogue __construct() for static classes).
	 * Uses [late static bindings](http://php.net/oop5.late-static-bindings).
	 * 
	 * @return  void
	 */
	final public static initialize()
	{
		if (static::$_initialized === FALSE)
		{
			static::_initialize();

			static::$_initialized = TRUE;
		}
	}

	/**
	 * Initialize properties in inherited class.
	 * 
	 * @return  void
	 */
	abstract protected static _initialize();

}

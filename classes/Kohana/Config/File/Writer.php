<?php
/**
 * File-based configuration writer. Multiple configuration directories can be
 * used by attaching multiple instances of this class to [Kohana_Config].
 *
 * @package    Kohana
 * @category   Configuration
 * @author     Kohana Team
 * @copyright  (c) 2014 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Config_File_Writer extends Kohana_Config_File_Reader implements Kohana_Config_Writer {

    protected $_loaded_keys = array();

    /**
     * Tries to load the specificed configuration group
     *
     * Returns FALSE if group does not exist or an array if it does
     *
     * @param  string $group Configuration group
     * @return boolean|array
     */
    public function load($group)
    {
        $config = parent::load($group);

        if ($config !== FALSE)
        {
            $this->_loaded_keys[$group] = $config;
        }

        return $config;
    }

    /**
     * Writes the passed config for $group
     *
     * Returns chainable instance on success
     *
     * @param string      $group  The config group
     * @param string      $key    The config key to write to
     * @param array       $config The configuration to write
     * @return boolean
     */
    public function write($group, $key, $config)
    {
        $this->_loaded_keys[$group][$key] = $config;

        $config_file = APPPATH.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.$group.'.php';

        $result = file_put_contents($config_file, "<?php\nreturn ".var_export($this->_loaded_keys[$group], TRUE).';');

        return $result !== FALSE;
    }
}

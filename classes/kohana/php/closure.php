<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Wrapper for closures to make it serializable
 */
class Kohana_PHP_Closure {
    
    /**
     * @var Closure
     */
    protected $closure = NULL;
    
    /**
     * @var ReflectionFunction  reflection of the closure
     */
    protected $reflection = NULL;
    
    /**
     * @var string  closure code string
     */
    protected $code = NULL;
    
    /**
     * @var array   external variables passed to closure
     */
    protected $used_variables = array();
    
    /**
     * Creates a new serializable closure
     *      $closure = new PHP_Closure(function($arg1, $arg2){
     *          return $arg1 + $arg2;
     *      });
     * 
     * @param Closure   Closure instance
     * @return void
     * @throws Kohana_Exception
     */
    public function __construct($function)
    {
        if ( ! $function instanceof Closure)
        {
            throw new Kohana_Exception('Constructor argument must be a Closure instance');
        }
        
        $this->closure = $function;
        $this->reflection = new ReflectionFunction($function);
        $this->code = $this->_fetch_code();
        $this->used_variables = $this->_fetch_used_variables();
    }
    
    /**
     * Method executed when calling PHP_Closure instance as function
     * Returns result of closure
     * 
     * @return mixed
     */
    public function __invoke()
    {
        $args = func_get_args();
        
        return $this->reflection->invokeArgs($args);
    }
    
    /**
     * Method executed before serialization
     * 
     * @return array    objext properties to serialize
     */
    public function __sleep()
    {
        return array('code', 'used_variables');
    }
    
    /**
     * Method executed before unserialization
     * 
     * @return void
     */
    public function __wakeup()
    {
        extract($this->used_variables);
        eval('$_function = '.$this->code.';');
        $this->closure = $_function;
        $this->reflection = new ReflectionFunction($_function);
    }
    
    /**
     * Get the closure
     * 
     * @return Closure
     */
    public function get_closure()
    {
        return $this->closure;
    }
    
    /**
     * Get closure parameters
     * 
     * @return mixed
     */
    public function get_parameters()
    {
        return $this->reflection->getParameters();
    }
    
    /**
     * Get code of the closure
     * 
     * @return string
     */
    public function get_code()
    {
        return $this->code;
    }
    
    /**
     * Get external vars of the closure
     * 
     * @return array
     */
    public function get_used_variabless()
    {
        return $this->used_vars;
    }
    
    /**
     * Fetch string representation of the closure
     * 
     * @return string
     */
    protected function _fetch_code()
    {
        // Open file and seek to the first line of the closure
        $file = new SplFileObject($this->reflection->getFileName());
        $file->seek($this->reflection->getStartLine() - 1);
        // Retrieve all of the lines that contain code for the closure
        $code = '';
        while ($file->key() < $this->reflection->getEndLine())
        {
            $code .= $file->current();
            $file->next();
        }
        // Only keep the code defining that closure
        $begin = strpos($code, 'function');
        $end = strrpos($code, '}');
        $code = substr($code, $begin, $end - $begin + 1);
        
        return $code;
    }
    
    /**
     * Retrieve external vars of closure passed using `use` keyword
     * 
     * @return array
     */
    protected function _fetch_used_variables()
    {
        $used_variables = array();
        // Make sure the use construct is actually used
        $use_index = stripos($this->code, 'use');
        if ($use_index !== FALSE)
        {
            // Get the names of the variables inside the use statement
            $begin = strpos($this->code, '(', $use_index) + 1;
            $end = strpos($this->code, ')', $begin);
            $vars = explode(',', substr($this->code, $begin, $end - $begin));
            // Get the static variables of the function via reflection
            $static_vars = $this->reflection->getStaticVariables();
            // Only keep the variables that appeared in both sets
            foreach ($vars as $var)
            {
                $var = trim($var, ' $&amp;');
                $used_variables[$var] = $static_vars[$var];
            }
        }
        
        return $used_variables;
    }
}
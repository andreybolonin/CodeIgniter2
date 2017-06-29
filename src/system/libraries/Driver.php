<?php

namespace Codeigniter2\system\libraries;

/**
 * CodeIgniter Driver Class
 *
 * This class enables you to create drivers for a Library based on the Driver Library.
 * It handles the drivers' access to the parent library
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		EllisLab Dev Team
 * @link
 */
class Driver {
    protected $parent;

    private $methods = array();
    private $properties = array();

    private static $reflections = array();

    /**
     * Decorate
     *
     * Decorates the child with the parent driver lib's methods and properties
     *
     * @param	object
     * @return	void
     */
    public function decorate($parent)
    {
        $this->parent = $parent;

        // Lock down attributes to what is defined in the class
        // and speed up references in magic methods

        $class_name = get_class($parent);

        if ( ! isset(self::$reflections[$class_name]))
        {
            $r = new ReflectionObject($parent);

            foreach ($r->getMethods() as $method)
            {
                if ($method->isPublic())
                {
                    $this->methods[] = $method->getName();
                }
            }

            foreach ($r->getProperties() as $prop)
            {
                if ($prop->isPublic())
                {
                    $this->properties[] = $prop->getName();
                }
            }

            self::$reflections[$class_name] = array($this->methods, $this->properties);
        }
        else
        {
            list($this->methods, $this->properties) = self::$reflections[$class_name];
        }
    }

    // --------------------------------------------------------------------

    /**
     * __call magic method
     *
     * Handles access to the parent driver library's methods
     *
     * @access	public
     * @param	string
     * @param	array
     * @return	mixed
     */
    public function __call($method, $args = array())
    {
        if (in_array($method, $this->methods))
        {
            return call_user_func_array(array($this->parent, $method), $args);
        }

        $trace = debug_backtrace();
        _exception_handler(E_ERROR, "No such method '{$method}'", $trace[1]['file'], $trace[1]['line']);
        exit;
    }

    // --------------------------------------------------------------------

    /**
     * __get magic method
     *
     * Handles reading of the parent driver library's properties
     *
     * @param	string
     * @return	mixed
     */
    public function __get($var)
    {
        if (in_array($var, $this->properties))
        {
            return $this->parent->$var;
        }
    }

    // --------------------------------------------------------------------

    /**
     * __set magic method
     *
     * Handles writing to the parent driver library's properties
     *
     * @param	string
     * @param	array
     * @return	mixed
     */
    public function __set($var, $val)
    {
        if (in_array($var, $this->properties))
        {
            $this->parent->$var = $val;
        }
    }

    // --------------------------------------------------------------------

}
// END CI_Driver CLASS

/* End of file Driver.php */
/* Location: ./system/libraries/Driver.php */
<?php

/**
 * This file contains the class definition of Result
 *
 * @package Class_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2008 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */

   
/**
 * This class contains the cumulative results of function calls
 *
 * @package Class_FirstThingsFirst
 */
class Result
{
    /**
    * error string
    * @var string
    */
    protected $error_str;

    /**
    * DOM element id that should contain the error
    * @var string
    */
    protected $error_element;
    
    /**
    * result string
    * @var string
    */
    protected $result_str;
    
    /**
    * reference to global logging object
    * @var Logging
    */
    protected $_log;

    /**
    * overwrite __construct() function
    * @return void
    */
    function __construct ()
    {
        # these variables are assumed to be globally available
        global $logging;
        
        # set global references for this object
        $this->_log =& $logging;

        $this->reset();
    }

    /**
    * get value of error_str attribute
    * @return int value of error_str attribute
    */
    function get_error_str ()
    {
        return $this->error_str;
    }
    
    /**
    * get value of error_element attribute
    * @return int value of error_element attribute
    */
    function get_error_element ()
    {
        return $this->error_element;
    }

    /**
    * get value of result_str attribute
    * @return int value of result_str attribute
    */
    function get_result_str ()
    {
        return $this->result_str;
    }
    
    /**
    * set value of error_str attribute
    * @param string $str value of error_str attribute
    * @return void
    */
    function set_error_str ($str)
    {
        $this->error_str = $str;
    }
    
    /**
    * set value of error_element attribute
    * @param string $str value of error_element attribute
    * @return void
    */
    function set_error_element ($str)
    {
        $this->error_element = $str;
    }
    
    /**
    * set value of result_str attribute (BEWARE: this function appends to existing string)
    * @param string $str value of result_str attribute
    * @return void
    */
    function set_result_str ($str)
    {
        $this->result_str = $str;
    }
    
    /**
    * reset attributes to initial values
    * @return void
    */
    function reset ()
    {
        $this->_log->trace("resetting Result");

        $this->error_str = "";
        $this->error_element = "";
        $this->result_str = "";
    }
    
}

?>

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
    * error message for user
    * @var string
    */
    protected $error_message_str;

    /**
    * error message for log
    * @var string
    */
    protected $error_log_str;

    /**
    * error string, contains last known error
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
    * get value of error_message_str attribute
    * @return string value of error_message_str attribute
    */
    function get_error_message_str ()
    {
        return $this->error_message_str;
    }

    /**
    * get value of error_log_str attribute
    * @return string value of error_log_str attribute
    */
    function get_error_log_str ()
    {
        return $this->error_log_str;
    }

    /**
    * get value of error_str attribute
    * @return string value of error_str attribute
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
    * set value of error_message_str attribute
    * @param string $str value of error_message_str attribute
    * @return void
    */
    function set_error_message_str ($str)
    {
        $this->error_message_str = $str;
    }

    /**
    * set value of error_log_str attribute
    * @param string $str value of error_log_str attribute
    * @return void
    */
    function set_error_log_str ($str)
    {
        $this->error_log_str = $str;
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

        $this->error_message_str = "";
        $this->error_log_str = "";
        $this->error_str = "";
        $this->error_element = "";
        $this->result_str = "";
    }
    
}

?>

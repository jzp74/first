<?php

# this class contains the cumulative results of function calls
# TODO improve use of trace/debug logging
class Result
{
    # error string
    protected $error_str;

    # element id that should display the error
    protected $error_element;
    
    # result string
    protected $result_str;
    
    # reference to global logging object
    protected $_log;

    # set attributes of this object when it is constructed
    function __construct ()
    {
        # these variables are assumed to be globally available
        global $json;
        global $logging;
        
        # set global references for this object
        $this->_json =& $json;
        $this->_log =& $logging;

        $this->reset();
        
        $this->_log->trace("constructed new Result object");
    }

    # getter
    function get_error_str ()
    {
        return $this->error_str;
    }
    
    # getter
    function get_error_element ()
    {
        return $this->error_element;
    }

    # getter
    function get_result_str ()
    {
        return $this->result_str;
    }
    
    # setter
    function set_error_str ($str)
    {
        $this->error_str = $str;
    }
    
    # setter
    function set_error_element ($str)
    {
        $this->error_element = $str;
    }
    
    # setter (append to result string)
    function set_result_str ($str)
    {
        $this->result_str .= $str;
    }
    
    # reset values of this object
    function reset ()
    {
        $this->_log->trace("resetting Result");

        $this->error_str = "";
        $this->error_element = "";
        $this->result_str = "";
    }
    
}

?>

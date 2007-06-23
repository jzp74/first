<?php

# this class contains the cumulative results of function calls
class Result
{
    # error string
    protected $error_str;

    # element id that should display the error
    protected $error_element;
    
    # result string
    protected $result_str;
    
    # string representation of an array
    protected $array_str;
    
    # reference to global logging object
    protected $_log;

    # set attributes of this object when it is constructed
    # TODO is there really need for a string_array?
    function __construct ()
    {
        # these variables are assumed to be globally available
        global $json;
        global $logging;
        
        # set global references for this object
        $this->_json = $json;
        $this->_log = $logging;

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
    
    # getter
    function get_array_str ()
    {
        # no decode here because javascript needs the string representation of the array
        return $this->array_str;
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
    
    # setter (append to result string)
    function set_array_str ($str)
    {
        $this->array_str = htmlentities($this->_json->encode($str), ENT_QUOTES);
    }

    # reset values of this object
    function reset ()
    {
        $this->error_str = "";
        $this->error_element = "";
        $this->result_str = "";
        $this->array_str = "";
    }
    
    # return json string representation of this object
    # TODO is this function used anywhere?
    function get_json ()
    {
        $new_array = array();
        array_push($new_array, $this->error_str);
        array_push($new_array, $this->html_str);
        foreach ($array_of_strings as $string)
            array_push($new_array, $string);
        return $json->encode($new_array);
    }
    
}

?>

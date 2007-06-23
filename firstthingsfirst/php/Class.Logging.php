<?php

# This class represents a logfile
# Log messages of varies levels can be added to the logfile


# TODO redefine logging statements (debug -> trace)

# Logging defines
define("LOGGING_OFF", 0);
define("LOGGING_TRACE", 1);
define("LOGGING_DEBUG", 2);
define("LOGGING_INFO", 3);
define("LOGGING_WARN", 4);
define("LOGGING_ERROR", 5);
define("LOGGING_NAME", "tasklist.log");

# Class definition
class Logging
{        
    # current log level
    protected $level;
    
    # current name of the logfile
    protected $name;
    
    # set attributes of this object when it is constructed
    function __construct ($level = LOGGING_TRACE, $name = LOGGING_NAME)
    {
        # globals defined in localsetting.php
        global $tasklist_full_pathname;
        
        $this->name = $tasklist_full_pathname."/".$name;
        $this->level = $level;
    }
        
    # this function should not be called directly but is used by main logging functions
    # write given string to fixed log file as given type
    # logging setting can be modified in globals.php
    function _log ($tp, $str)
    {
        $trace = debug_backtrace();
        if (count($trace) > 2)
        {
            $func = $trace[2]['function'];
            $filename = $trace[1]['file'];
            $line = $trace[1]['line'];    
        }
        else
        {
            $func = "";
            $filename = $trace[1]['file'];
            $line = $trace[1]['line'];    
        }
        
        $the_time = strftime("%d-%m-%Y %H:%M:%S");
    
        error_log($the_time." [".$tp."] ".$filename.":".$line." [".$func."] ".$str."\n", 3, $this->name);        
    }

    # log given array as a debug or info line
    # array should not contain arrays
    # debug log line is standard
    function log_array ($the_array, $the_array_name="Array", $level=LOGGING_DEBUG)
    {
        $log_str = $the_array_name.": (";
        $keys = array_keys($the_array);
        if (count($keys))
        {
            for ($i=0; $i<count($keys); $i++)
            {
                $array_key = $keys[$i];
                $log_str .= $array_key."=".$the_array[$array_key];
                if ($i < (count($keys) - 1))
                    $log_str .= ", ";
            }
        }
        else
            $log_str .= "<empty>";
        $log_str .= ")";
        
        if ($level == LOGGING_DEBUG)
        {
            if (($this->level > 0) && (LOGGING_DEBUG >= $this->level))
                $this->_log ("debug", $log_str);
        }
        else if ($level == LOGGING_INFO)
        {
            if ($this->level > 0 && (LOGGING_INFO >= $this->level))
                $this->_log ("info", $log_str);
        }
    }

    # write given string as TRACE message to logfile
    # logging setting are can be modified in globals.php
    function trace ($str)
    {
        if (($this->level > 0) && (LOGGING_TRACE >= $this->level))
            $this->_log ("trace", $str);
    }

    # write given string as DEBUG message to logfile
    # logging setting are can be modified in globals.php
    function debug ($str)
    {
        if (($this->level > 0) && (LOGGING_DEBUG >= $this->level))
            $this->_log ("debug", $str);
    }

    # write given string as info message to logfile
    # logging setting can be modified in globals.php
    function info ($str)
    {
        if ($this->level > 0 && (LOGGING_INFO >= $this->level))
            $this->_log ("info", $str);
    }

    # write given string as warn message to logfile
    # logging setting can be modified in globals.php
    function warn ($str)
    {
        if ($this->level > 0 && (LOGGING_WARN >= $this->level))
            $this->_log ("WARN", $str);
    }

    # write given string as info message to logfile
    # logging setting can be modified in globals.php
    function error ($str)
    {
        if ($this->level > 0 && (LOGGING_ERROR >= $this->level))
            $this->_log ("ERROR", $str);
    }
}

?>

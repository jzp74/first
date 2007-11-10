<?php

/**
 * This file contains the class definition of ListTable
 *
 * @package Class_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definition of no loglevel (logging is switched off)
 */
define("LOGGING_OFF", 0);

/**
 * definition of TRACE loglevel
 */
define("LOGGING_TRACE", 1);

/**
 * definition of DEBUG loglevel
 */
define("LOGGING_DEBUG", 2);

/**
 * definition of INFO loglevel
 */
define("LOGGING_INFO", 3);

/**
 * definition of WARN loglevel
 */
define("LOGGING_WARN", 4);

/**
 * definition of ERROR loglevel
 */
define("LOGGING_ERROR", 5);

/**
 * definition of standard logfile name
 */
define("LOGGING_NAME", "logfile.log");


/**
 * This class represents a logfile
 * Log messages of various levels can be added to the logfile
 *
 * @package Class_FirstThingsFirst
 */
class Logging
{        
    /**
    * current loglevel
    * @var int
    */
    protected $level;
    
    /**
    * current name of the logfile
    * @var string
    */
    protected $name;
    
    /**
    * overwrite __construct() function
    * @return void
    */
    function __construct ($level = LOGGING_INFO, $name = LOGGING_NAME)
    {
        # globals defined in localsetting.php
        global $firstthingsfirst_full_pathname;
        
        $this->name = $firstthingsfirst_full_pathname."/".$name;
        $this->level = $level;
    }
        
    /**
    * write given string to fixed log file as given type
    * this function should not be called directly but is used by main logging functions
    * @param $log_level string log level
    * @param $str string string to log
    * @return void
    */
    function _log ($log_level, $str)
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
    
        error_log($the_time." [".$log_level."] ".$filename.":".$line." [".$func."] ".$str."\n", 3, $this->name);        
    }

    /**
    * log given array
    * array should not contain arrays
    * @param $the_array array array to be logged
    * @param $the_array_name string name of the array ("Array" if not provided)
    * @param $level int log level (DEBUG if not provided)
    * @return void
    */
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
                
        if ($level == LOGGING_TRACE)
            $this->trace($log_str);
        else if ($level == LOGGING_DEBUG)
            $this->debug($log_str);
        else # level INFO
            $this->info($log_str);
    }

    /**
    * write given string as TRACE message to logfile
    * @param $str string string to be logged
    * @return void
    */
    function trace ($str)
    {
        if (($this->level > 0) && (LOGGING_TRACE >= $this->level))
            $this->_log ("trace", $str);
    }

    /**
    * write given string as DEBUG message to logfile
    * @param $str string string to be logged
    * @return void
    */
    function debug ($str)
    {
        if (($this->level > 0) && (LOGGING_DEBUG >= $this->level))
            $this->_log ("debug", $str);
    }

    /**
    * write given string as INFO message to logfile
    * @param $str string string to be logged
    * @return void
    */
    function info ($str)
    {
        if ($this->level > 0 && (LOGGING_INFO >= $this->level))
            $this->_log ("info", $str);
    }

    /**
    * write given string as WARN message to logfile
    * @param $str string string to be logged
    * @return void
    */
    function warn ($str)
    {
        if ($this->level > 0 && (LOGGING_WARN >= $this->level))
            $this->_log ("WARN", $str);
    }

    /**
    * write given string as ERROR message to logfile
    * @param $str string string to be logged
    * @return void
    */
    function error ($str)
    {
        if ($this->level > 0 && (LOGGING_ERROR >= $this->level))
            $this->_log ("ERROR", $str);
    }
}

?>

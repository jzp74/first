<?php

/**
 * This file contains a number of utility functions
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2008 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * test if given string is not empty
 * @param string $field_name name of field that contains this string
 * @param string $str string to test
 * @return bool indicates if string is empty
 */
function is_not_empty ($field_name, $str)
{
    global $logging;

    $logging->trace("is_not_empty (field_name=".$field_name.", str=".$str.")");
    
    if (strlen($str) == 0)
    {
        $logging->warn($field_name." is empty");
        
        return FALSE_RETURN_STRING;
    }
    
    $logging->trace("is_not_empty");

    return $str;
}

/**
 * test if given string is a number
 * @todo write checks for this function
 * @param string $field_name name of field that contains this string
 * @param string $str string to test
 * @return bool indicates if string is a number
 */
function is_number ($field_name, $str)
{
    global $logging;

    $logging->trace("is_number (field_name=".$field_name.", str=".$str.")");
    
    $logging->trace("is_number");

    return $str;
}

/**
 * test if string is well formed
 * @param string $field_name name of field that contains this string
 * @param string $str string to test
 * @param string $use_pipe_char bool indicates if pipe character is permitted (standard: false)
 * @return bool indicates if string is well formed
 */
function is_well_formed_string ($field_name, $str, $use_pipe_char=0)
{
    global $logging;

    $logging->trace("is_well_formed_string (field_name=".$field_name.", str=".$str.", use_pipe_char=".$use_pipe_char.")");
    
    if ($use_pipe_char == 0)
    {
        $logging->debug("checking (str=".$str.", pipe char NOT permitted)");
        if (ereg ("[\"\*'/:<>?|\\&;#]+", $str))
        {
            $logging->warn($field_name." is not well formed (pipe char NOT permitted)");
        
            return FALSE_RETURN_STRING;
        }
    }
    else
    {
        $logging->debug("checking (str=".$str.", pipe char permitted)");
        if (ereg ("[\"\*'/:<>?\\&;#]+", $str))
        {
            $logging->warn($field_name." is not well formed (pipe char permitted)");
        
            return FALSE_RETURN_STRING;
        }
    }
    
    $logging->trace("is_well_formed_string");

    return $str;
}

/**
 * test if string complies with predefined date format
 * @param string $field_name name of field that contains this string
 * @param string $str string to test
 * @return bool indicates if string is empty
 */
function is_date ($field_name, $str)
{
    global $logging;
    global $firstthingsfirst_date_string;
    
    $logging->trace("is_date (field_name=".$field_name.", str=".$str.")");

    if ($firstthingsfirst_date_string == DATE_FORMAT_US)
    {
        # proces us date
        $date_parts = explode("/", $str);
        if (!count($date_parts) == 3)
            return FALSE_RETURN_STRING;
        
        $month = intval($date_parts[0]);
        $day = intval($date_parts[1]);
        $year = intval($date_parts[2]);
        $logging->debug("found us date (DD-MM-YYYY): ".$day."-".$month."-".$year);
    }
    else
    {
        # proces european date
        $date_parts = explode("-", $str);
        if (count($date_parts) == 2)
            $year = idate("Y");
        else if (count($date_parts) == 3)
            $year = intval($date_parts[2]);
        else
            return FALSE_RETURN_STRING;
        
        $day = intval($date_parts[0]);
        $month = intval($date_parts[1]);
        $logging->debug("found eu date (DD-MM-YYYY): ".$day."-".$month."-".$year);
    }

    # rewrite 2 digit year
    if ($year < 100)
    {
        $century = (int)(idate("Y") / 100);
        $logging->debug("found century: ".$century);
        $year = ($century * 100) + $year;
    }
    
    $logging->debug("checking date (DD-MM-YYYY): ".$day."-".$month."-".$year);
    if (!checkdate($month, $day, $year))
        return FALSE_RETURN_STRING;
 
    $logging->trace("is_date");

    return sprintf("%04d-%02d-%02d", $year, $month, $day);
}

/**
 * convert one date string into another date string
 * @param int $format format of date string
 * @param string $value string representation of date
 * @return string date string
 */
function get_date_str ($format, $value)
{
    global $logging;
    global $firstthingsfirst_date_string;
    global $first_things_first_day_definitions;
    
    $logging->debug("get_date_str (format=".$format.", value=".$value.")");
    
    if ($format == DATE_FORMAT_NORMAL)
        return strftime($firstthingsfirst_date_string, (strtotime($value)));
    else if ($format == DATE_FORMAT_WEEKDAY)
    {
        # get weekday
        $weekday = strftime("%w", (strtotime($value)));
        $logging->debug("found weekday (weekday=".$weekday.")");
        # get normal date format
        $date_str = strftime($firstthingsfirst_date_string, (strtotime($value)));
        
        return constant($first_things_first_day_definitions[$weekday])."&nbsp;".$date_str;
    }
    else
        return $value;
}

?>

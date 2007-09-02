<?php

# This file contains a number of utility functions


# check if given string is not empty
# return "<-FALSE->" when string is empty
function is_not_empty ($field_name, $str)
{
    global $logging;

    $logging->trace("is_not_empty (field_name=".$field_name.", str=".$str.")");
    
    if (strlen($str) == 0)
    {
        $logging->warn($field_name." is empty");
        
        return "<-FALSE->";
    }
    
    return $str;
}

# check if given string is a number
# return "<-FALSE->" when string is not a number
# TODO write and test this function
function is_number ($field_name, $str)
{
    global $logging;

    $logging->trace("is_number (field_name=".$field_name.", str=".$str.")");
    
    return $str;
}

# check if given string complies with predifined date format
# return "<-FALSE->" when string doesn't comply
function is_date ($field_name, $str)
{
    global $firstthingsfirst_date_string;
    global $logging;
    
    $logging->trace("is_date (field_name=".$field_name.", str=".$str.")");

    if ($firstthingsfirst_date_string == DATE_FORMAT_US)
    {
        $date_parts = explode("/", $str);
        $month = intval($date_parts[0]);
        $day = intval($date_parts[1]);
        $year = intval($date_parts[2]);
    }
    else
    {
        $date_parts = explode("-", $str);
        $day = intval($date_parts[0]);
        $month = intval($date_parts[1]);
        $year = intval($date_parts[2]);
    }

    if (!checkdate($month, $day, $year))
        return "<-FALSE->";
    if ($year < 1900)
        return "<-FALSE->";
 
    return sprintf("%04d-%02d-%02d", $year, $month, $day);
}


?>

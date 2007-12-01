<?php

/**
 * This file contains a number of utility functions
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */

/**
 * test if given string is not empty
 * @todo write checks for this function
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
    
    return $str;
}

/**
 * test if given string is a number
 * @param string $field_name name of field that contains this string
 * @param string $str string to test
 * @return bool indicates if string is a number
 */
function is_number ($field_name, $str)
{
    global $logging;

    $logging->trace("is_number (field_name=".$field_name.", str=".$str.")");
    
    return $str;
}

/**
 * test if string is well formed
 * @param string $field_name name of field that contains this string
 * @param string $str string to test
 * @return bool indicates if string is well formed
 */
function is_well_formed_string ($field_name, $str)
{
    global $logging;

    $logging->trace("is_well_formed_string (field_name=".$field_name.", str=".$str.")");
    
    if (ereg ("[\"\*'/:<>?|\\&;#]+", $str))
    {
        $logging->warn($field_name." is not well formed");
        
        return FALSE_RETURN_STRING;
    }
    
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
    global $firstthingsfirst_date_string;
    global $logging;
    
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
 
    return sprintf("%04d-%02d-%02d", $year, $month, $day);
}

?>

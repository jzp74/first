<?php

/**
 * This file contains a number of utility functions
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007-2009 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * return the necessary javascript code to configure xajax
 * @return string javascript code
 */
function get_xajax_javascript ()
{
    $html_str = "<script language=\"javascript\">\n";
    $html_str .= "    try { if (undefined == xajax.config) xajax.config = {}; } catch (e) { xajax = {}; xajax.config = {}; }\n";
    $html_str .= "    xajax.config.requestURI = \"".$_SERVER["REQUEST_URI"]."\"\n";
    $html_str .= "    xajax.config.statusMessages = false;\n";
    $html_str .= "    xajax.config.waitCursor = true;\n";
    $html_str .= "    xajax.config.version = \"xajax 0.5\";\n";
    $html_str .= "    xajax.config.legacy = false;\n";
    $html_str .= "    xajax.config.defaultMode = \"asynchronous\";\n";
    $html_str .= "    xajax.config.defaultMethod = \"POST\";\n";
    $html_str .= "</script>\n";

    return $html_str;
}

/**
 * return translation of given string
 * @param string $str string to translate
 * @return string translated string
 */
function translate ($string)
{
    global $text_translations;

    if (array_key_exists($string, $text_translations))
        return $text_translations[$string];
    else
        return "NO&nbsp;TRANSLATION&nbsp;[".$string."]";
}

/**
 * perform a number of test functions on given string
 * @param array $check_functions array containing names of zero or more test functions
 * @param string $field_name name of field that contains this string
 * @param string $str string on which to perform tests
 * @param Result $result rusult of this function
 * @return void
 */
function check_field ($check_functions, $field_name, $str, $result)
{
    global $logging;
    global $firstthingsfirst_date_string;

    $logging->trace("check_field (field_name=".$field_name.", str=".$str.")");

    $result_str = $str;

    foreach ($check_functions as $check_function)
    {
        if ($check_function == "str_is_not_empty")
        {
            $result_str = str_is_not_empty($field_name, $result_str);
            if ($result_str == FALSE_RETURN_STRING)
            {
                $result->set_error_message_str("ERROR_NO_FIELD_VALUE_GIVEN");

                return;
            }
        }
        else if ($check_function == "str_is_number")
        {
            $result_str = str_is_number($field_name, $result_str);
            if ($result_str == FALSE_RETURN_STRING)
            {
                $result->set_error_message_str("ERROR_NO_NUMBER_GIVEN");

                return;
            }
        }
        else if ($check_function == "str_is_date")
        {
            $result_str = str_is_date($field_name, $result_str);
            if ($result_str == FALSE_RETURN_STRING)
            {
                if ($firstthingsfirst_date_string == DATE_FORMAT_US)
                    $result->set_error_message_str("ERROR_DATE_WRONG_FORMAT_US");
                else
                    $result->set_error_message_str("ERROR_DATE_WRONG_FORMAT_EU");

                return;
            }
        }
        else if (strlen($check_function))
            $logging->warn("unknown check function (function=".$check_function.", $field_name=".$field_name.")");
    }

    $result->set_result_str($result_str);

    $logging->trace("check_field");

    return;
}

/**
 * test if given string is not empty
 * @param string $field_name name of field that contains this string
 * @param string $str string to test
 * @return bool indicates if string is empty
 */
function str_is_not_empty ($field_name, $str)
{
    global $logging;

    $logging->trace("str_is_not_empty (field_name=".$field_name.", str=".$str.")");

    if (strlen($str) == 0)
    {
        $logging->warn($field_name." is empty");

        return FALSE_RETURN_STRING;
    }

    $logging->trace("str_is_not_empty");

    return $str;
}

/**
 * test if given string is a number
 * @todo write checks for this function
 * @param string $field_name name of field that contains this string
 * @param string $str string to test
 * @return bool indicates if string is a number
 */
function str_is_number ($field_name, $str)
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
function str_is_well_formed ($field_name, $str, $use_pipe_char=0)
{
    global $logging;

    $logging->trace("str_is_well_formed (field_name=".$field_name.", str=".$str.", use_pipe_char=".$use_pipe_char.")");

    if ($use_pipe_char == 0)
    {
        $logging->debug("checking (str=".$str.", pipe char NOT permitted)");
        if (ereg ("[".EREG_ALLOWED_CHARS."]", $str))
        {
            $logging->warn($field_name." is not well formed (pipe char NOT permitted)");

            return FALSE_RETURN_STRING;
        }
    }
    else
    {
        $logging->debug("checking (str=".$str.", pipe char permitted)");
        if (ereg ("[".EREG_ALLOWED_CHARS."|]", $str))
        {
            $logging->warn($field_name." is not well formed (pipe char permitted)");

            return FALSE_RETURN_STRING;
        }
    }

    $logging->trace("str_is_well_formed");

    return $str;
}

/**
 * test if string complies with predefined date format
 * @param string $field_name name of field that contains this string
 * @param string $str string to test
 * @return bool indicates if string is empty
 */
function str_is_date ($field_name, $str)
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
        $logging->trace("found us date (MM/DD/YYYY): ".$month."/".$day."/".$year);
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
        $logging->trace("found eu date (DD-MM-YYYY): ".$day."-".$month."-".$year);
    }

    # rewrite 2 digit year
    if ($year < 100)
    {
        $century = (int)(idate("Y") / 100);
        $logging->trace("found century: ".$century);
        $year = ($century * 100) + $year;
    }

    $logging->trace("checking date (DD-MM-YYYY): ".$day."-".$month."-".$year);
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

    $logging->trace("get_date_str (format=".$format.", value=".$value.")");

    if ($format == DATE_FORMAT_NORMAL)
        return strftime($firstthingsfirst_date_string, (strtotime($value)));
    if ($format == DATE_FORMAT_DATETIME)
    {
        if ($firstthingsfirst_date_string == DATE_FORMAT_EU)
            return strftime(DATETIME_FORMAT_EU, (strtotime($value)));
        else
            return strftime(DATETIME_FORMAT_EU, (strtotime($value)));
    }
    else if ($format == DATE_FORMAT_WEEKDAY)
    {
        # get weekday
        $weekday = strftime("%w", (strtotime($value)));
        $logging->trace("found weekday (weekday=".$weekday.")");
        # get normal date format
        $date_str = strftime($firstthingsfirst_date_string, (strtotime($value)));

        return translate($first_things_first_day_definitions[$weekday])."&nbsp;".$date_str;
    }
    else
        return $value;
}

/**
 * calculate the time (in ms) for complete function call and return a log message
 * @param string name of function
 * @return string log message
 */
function get_function_time_str ($function_name)
{
    global $user_start_time_array;

    # substract start time from end time to calculate interval
    $total_time_msec = (float)(microtime(TRUE) - $user_start_time_array[$function_name]) * 1000;
    # round the number to two digits (Dutch number notation)
    $total_rounded_time_msec = number_format($total_time_msec, 2, ',', '');

    return "USER_ACTION $function_name TIME $total_rounded_time_msec ms";
}

?>
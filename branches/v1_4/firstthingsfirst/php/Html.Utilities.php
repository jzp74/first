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
 * @param string $date_format preferred date format of active user
 * @param Result $result rusult of this function
 * @return void
 */
function check_field ($check_functions, $field_name, $str, $date_format, $result)
{
    global $logging;
    global $firstthingsfirst_date_string;
    global $$firstthingsfirst_date_format_prefix_array;

    $logging->trace("check_field (field_name=$field_name, str=$str, date_format=$date_format)");

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
            $result_str = str_is_date($field_name, $result_str, $date_format);
            if ($result_str == FALSE_RETURN_STRING)
            {
                if ($date_format == DATE_FORMAT_US)
                    $result->set_error_message_str("ERROR_DATE_WRONG_FORMAT_US");
                else
                    $result->set_error_message_str("ERROR_DATE_WRONG_FORMAT_EU");

                return;
            }
        }
        else if (strlen($check_function))
            $logging->warn("unknown check function (function=$check_function, $field_name=$field_name)");
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

    if (preg_match("/^\d*$/", $str) == 0)
    {
        $logging->warn("$field_name is not an integer");

        return FALSE_RETURN_STRING;
    }

    # create an integer
    $the_number = (int)$str;
    # check if number equals zero
    if ($the_number == 0)
    {
        $logging->warn("$field_name is zero");

        return FALSE_RETURN_STRING;
    }

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

    # only check when string contains characters
    if (strlen($str) > 0)
    {
        if ($use_pipe_char == 0)
        {
            $logging->debug("checking (str=".$str.", pipe char NOT permitted)");
            if (preg_match(PREG_ALLOWED_CHARS, $str) == 0)
            {
                $logging->warn("$field_name is not well formed (pipe char NOT permitted)");

                return FALSE_RETURN_STRING;
            }
        }
        else
        {
            $logging->debug("checking (str=".$str.", pipe char permitted)");
            if (preg_match(PREG_ALLOWED_CHARS_EXTRA, $str) == 0)
            {
                $logging->warn($field_name." is not well formed (pipe char permitted)");

                return FALSE_RETURN_STRING;
            }
        }
    }

    $logging->trace("str_is_well_formed");

    return $str;
}

/**
 * test if string complies with predefined date format
 * @param string $field_name name of field that contains this string
 * @param string $str string to test
 * @param string $date_format preferred date format of active user
 * @return bool indicates if string is empty
 */
function str_is_date ($field_name, $str, $date_format)
{
    global $logging;
    global $firstthingsfirst_date_format_prefix_array;

    $logging->trace("is_date (field_name=$field_name, str=$str, date_format=$date_format)");

    $date_format_user = DATE_FORMAT_USER_DMY;

    # proces different dates (us and eu) and check characters
    if ($date_format == DATE_FORMAT_US)
    {
        if (preg_match(PREG_ALLOWED_DATE_US, $str) == 0)
            return FALSE_RETURN_STRING;
        $date_parts = explode("/", $str);
    }
    else if ($date_format == DATE_FORMAT_EU)
    {
        if (preg_match(PREG_ALLOWED_DATE_EU, $str) == 0)
            return FALSE_RETURN_STRING;
        $date_parts = explode("-", $str);
    }
    else
        return FALSE_RETURN_STRING;

    # only one number given, it should be a year
    if (count($date_parts) == 1)
    {
        # make it the last year of the year
        $day = 31;
        $month = 12;
        $year = intval($date_parts[0]);
        $date_format_user = DATE_FORMAT_USER_Y;
    }
    # when two numbers have been given it can be a day and a month or a month and a year
    else if (count($date_parts) == 2)
    {
        # check if the second part is a year
        $dmy = intval($date_parts[1]);
        if ($dmy > 1000)
        {
            # dmy is a year
            $year = $dmy;
            $month = intval($date_parts[0]);
            $day = 31;

            # check if month is valid
            if (($month < 1) || ($month > 12))
                return FALSE_RETURN_STRING;

            # calculate the last day of the month
            if ($month != 12)
                $day = intval(strftime("%d", mktime(0, 0, 0, ($month + 1), 0, $dmy)));
            $date_format_user = DATE_FORMAT_USER_MY;
        }
        else
        {
            if ($date_format == DATE_FORMAT_US)
            {
                # dmy is a day
                $day = $dmy;
                $month = intval($date_parts[0]);
            }
            else
            {
                # dmy is a month
                $month = $dmy;
                $day = intval($date_parts[0]);
            }

            # check if month is valid
            if (($month < 1) || ($month > 12))
                return FALSE_RETURN_STRING;

            $year = idate("Y");
            # get the current month and compare it to given month
            $current_month = idate("m");
            if ($month < $current_month)
            {
                # given month is before current month, add 1 to year
                $year += 1;
            }
            else if ($month == $current_month)
            {
                $current_day = idate("d");
                if ($day < $current_day)
                {
                    # given day and month are before current day and month, add 1 to year
                    $year += 1;
                }
            }
        }
    }
    else if (count($date_parts) == 3)
    {
        if ($date_format == DATE_FORMAT_US)
        {
            $day = intval($date_parts[1]);
            $month = intval($date_parts[0]);
        }
        else
        {
            $day = intval($date_parts[0]);
            $month = intval($date_parts[1]);
        }
        $year = intval($date_parts[2]);
    }
    else
        return FALSE_RETURN_STRING;

    if ($date_format == DATE_FORMAT_US)
        $logging->trace("found eu date (MM/DD/YYYY): $month/$day/$year");
    else
        $logging->trace("found eu date (DD-MM-YYYY): $day-$month-$year");

    # rewrite 1 or 2 digit year
    if ($year < 100)
    {
        $century = (int)(idate("Y") / 100);
        $logging->trace("found century: ".$century);
        $year = ($century * 100) + $year;
    }

    $logging->trace("checking date (DD-MM-YYYY): $day-$month-$year");
    if (!checkdate($month, $day, $year))
        return FALSE_RETURN_STRING;

    $logging->trace("is_date");

    return sprintf("%04d-%02d-%02d $date_format_user", $year, $month, $day);
}

/**
 * convert one date string into another date string
 * @param int $type type of date string
 * @param string $value string representation of date
 * @param string $date_format preferred date format of active user
 * @return string date string
 */
function get_date_str ($type, $value, $date_format)
{
    global $logging;
    global $firstthingsfirst_date_format_prefix_array;
    global $first_things_first_day_definitions;
    global $first_things_first_month_definitions;

    $logging->trace("get_date_str (type=$type, value=$value, date_format=$date_format)");

    $date_format_str = $firstthingsfirst_date_format_prefix_array[$date_format];

    if ($type == DATE_FORMAT_DATETIME)
    {
        if ($date_format == DATE_FORMAT_EU)
            return strftime(DATETIME_FORMAT_EU, (strtotime($value)));
        else
            return strftime(DATETIME_FORMAT_US, (strtotime($value)));
    }
    else if ($type == DATE_FORMAT_NORMAL)
    {
        # get time to determine user date format
        $time_str = strftime("%H:%M:%S", (strtotime($value)));

        # determine in which format to display the date
        if ($time_str == DATE_FORMAT_USER_Y)
            return strftime("%Y", (strtotime($value)));
        else if ($time_str == DATE_FORMAT_USER_MY)
        {
            if ($date_format == DATE_FORMAT_US)
                return strftime("%m/%Y", (strtotime($value)));
            else
                return strftime("%m-%Y", (strtotime($value)));
        }
        else
            return strftime($date_format_str, (strtotime($value)));
    }
    else if ($type == DATE_FORMAT_FANCY)
    {
        # get time to determine user date format
        $time_str = strftime("%H:%M:%S", (strtotime($value)));

        # determine in which format to display the date
        if ($time_str == DATE_FORMAT_USER_Y)
            return strftime("%Y", (strtotime($value)));
        else if ($time_str == DATE_FORMAT_USER_MY)
        {
            # get month
            $month = strftime("%m", (strtotime($value)));
            return translate($first_things_first_month_definitions[($month - 1)]).strftime("&nbsp;%Y", (strtotime($value)));
        }
        else
        {
            # get weekday
            $weekday = strftime("%w", (strtotime($value)));
            return translate($first_things_first_day_definitions[$weekday]).strftime("&nbsp;$date_format_str", (strtotime($value)));
        }
    }
    else
        return $value;
}

/**
 * calculate the time (in ms) for complete function call and return a log message
 * @param string $function_name name of function
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

/**
 * prepare string for display on screen
 * @param string $str string to transform
 * @return string transformed string
 */
function transform_str ($str)
{
    # replace /n by <br>
    $str = nl2br($str);

    # replace url by href
    $pattern = array(
        '`((?:https?|ftp)://\S+[[:alnum:]]/?)`si',
        '`((?<!//)(www\.\S+[[:alnum:]]/?))`si'
    );
    $replacement = array(
    '<a href="$1">$1</a>',
    '<a href="http://$1">$1</a>'
    );
    $str = preg_replace($pattern, $replacement, $str);

    return $str;
}





?>
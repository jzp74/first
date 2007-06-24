<?php

# This file contains a number of utility functions


# check if given date string complies with predifined date format
function check_date ($date_string)
{
    global $tasklist_date_string;
    global $logging;
    
    $logging->trace("checking date (date_string=".$date_string.")");

    if ($tasklist_date_string == DATE_FORMAT_US)
    {
        $date_parts = explode("/", $date_string);
        $month = intval($date_parts[0]);
        $day = intval($date_parts[1]);
        $year = intval($date_parts[2]);
    }
    else
    {
        $date_parts = explode("-", $date_string);
        $day = intval($date_parts[0]);
        $month = intval($date_parts[1]);
        $year = intval($date_parts[2]);
    }

    if (!checkdate($month, $day, $year))
        return "ERROR";
    if ($year < 1900)
        return "ERROR";
 
    return sprintf("%04d-%02d-%02d", $year, $month, $day);
}

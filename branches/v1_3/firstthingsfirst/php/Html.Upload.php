<?php

/**
 * This file contains code that does only one thing: move an uploaded file
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007-2009 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */

require_once("Class.Logging.php");

require_once("../globals.php");
require_once("../localsettings.php");

require_once("Html.Utilities.php");


$logging = new Logging($firstthingsfirst_loglevel, "../logs/".$firstthingsfirst_logfile);


# the file name has to be given by means of a parameter
if (isset($_GET['file_name']))
    $file_name = $_GET['file_name'];
else
{
    $file_name = 'tmp.csv';
    $logging->warn("parameter file_name not given, assuming tmp.csv");
}

# the language has to be given by means of a parameter
if (isset($_GET['lang']))
    $lang = $_GET['lang'];
else
{
    $lang = LANG_EN;
    $logging->warn("parameter lang not given, assuming ".LANG_EN);
}

# get the error messages
require_once("../lang/".$firstthingsfirst_lang_prefix_array[$lang].".Text.Errors.php");

# test if the uploaded file is actually present
if(isset($_FILES['import_file']))
{
    # uploaded file is present
    $tmp_name = $_FILES['import_file']['tmp_name'];
    $org_name = $_FILES['import_file']['name'];
    $tmp_error = $_FILES['import_file']['error'];
    $tmp_size = $_FILES['import_file']['size'];
    $path_parts = pathinfo($org_name);
    $org_name_extension = $path_parts['extension'];
    # we want to save the file in the uploads directory
    $save_path = "../uploads";

    $logging->info("uploading file (name=$tmp_name, size=$tmp_size, lang=$lang)");

    # only allow files smaller than 1Mb
    if ($tmp_size > 1048576)
    {
        $loggin->warn("file size exceeds 1MB");
        die(translate("ERROR_IMPORT_FILE_SIZE_TOO_LARGE"));
    }

    # only allow uploading of .txt or .csv files
    if (($org_name_extension != "txt") && ($org_name_extension != "csv"))
    {
        $logging->warn("file extension incorrect (extension=$org_name_extension)");
        die(translate("ERROR_IMPORT_FILE_WRONG_EXTENSION"));
    }

    $full_file_name = $save_path."/".$file_name;
    if(move_uploaded_file($tmp_name, $full_file_name) == FALSE)
    {
        $logging->warn("could not move uploaded file (tmp_name=$tmp_name, full_file_name=$full_file_name)");
        die(translate("ERROR_IMPORT_FILE_NOT_MOVE"));
    }

    # file has been moved
    $logging->info("moved uploaded file to $full_file_name");
    die("SUCCES $file_name");
}
else
{
    # uploaded file is not present
    $logging->warn("cannot find uploaded file");

    die(translate("ERROR_IMPORT_FILE_NOT_FOUND"));
}

?>
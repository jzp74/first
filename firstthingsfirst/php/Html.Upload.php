<?php

/**
 * This file contains code that does only one thing: move an uploaded file
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007-2012 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */

require_once("Class.Logging.php");

require_once("../globals.php");
require_once("../localsettings.php");

require_once("Html.Utilities.php");


$logging = new Logging($firstthingsfirst_loglevel, "../logs/".$firstthingsfirst_logfile);


# the user name has to be given by means of a parameter
if (isset($_GET['user_name']))
    $file_name = "upload_".$_GET['user_name'].strftime("_%d%m%Y_%H%M%S");
else
{
    $file_name = "upload_none_".strftime("%d%m%Y_%H%M%S.csv");
    $logging->warn("parameter user_name not given, assuming none");
}

# the language has to be given by means of a parameter
if (isset($_GET['lang']))
    $lang = $_GET['lang'];
else
{
    $lang = LANG_EN;
    $logging->warn("parameter lang not given, assuming ".LANG_EN);
}

# the mode has to be given by means of a parameter
if (isset($_GET['mode']))
    $lang = $_GET['mode'];
else
{
    $mode = UPLOAD_MODE_ATTACHMENT;
    $logging->warn("parameter mode not given, assuming ".UPLOAD_MODE_ATTACHMENT);
}

# get the error messages
require_once("../lang/".$firstthingsfirst_lang_prefix_array[$lang].".Text.Errors.php");

# test if the uploaded file is actually present
if(isset($_FILES['upload_file']))
{
    # uploaded file is present
    $tmp_name = $_FILES['upload_file']['tmp_name'];
    $upload_file_name = $_FILES['upload_file']['name'];
    $upload_file_size = $_FILES['upload_file']['size'];
    $upload_file_type = $_FILES['upload_file']['type'];
    $error_str = $_FILES['upload_file']['error'];
    $path_parts = pathinfo($upload_file_name);
    $upload_file_name_extension = $path_parts['extension'];
    # we want to save the file in the uploads directory
    $save_path = "../uploads";

    $logging->info("uploading file (name=$tmp_name, size=$upload_file_size, lang=$lang, mode=$mode)");

    # only allow files smaller than 1Mb
    if ($upload_file_size > 1048576)
    {
        $loggin->warn("file size exceeds 1MB");
        die(translate("ERROR_IMPORT_FILE_SIZE_TOO_LARGE"));
    }

    # only allow uploading of .txt or .csv files in import mode csv
    if ($mode == UPLOAD_MODE_CSV)
    {
        if (($upload_file_name_extension != "txt") && ($upload_file_name_extension != "csv"))
        {
            $logging->warn("file extension incorrect (extension=$upload_file_name_extension)");
            die(translate("ERROR_IMPORT_FILE_WRONG_EXTENSION"));
        }
    }

    $full_file_name = $save_path."/".$file_name;
    if(move_uploaded_file($tmp_name, $full_file_name) == FALSE)
    {
        $logging->warn("could not move uploaded file (tmp_name=$tmp_name, full_file_name=$full_file_name)");
        die(translate("ERROR_UPLOAD_FILE_NOT_MOVE"));
    }

    # file has been moved
    $logging->info("moved uploaded file to $full_file_name");
    die("SUCCES $file_name|$upload_file_type|$upload_file_size|$upload_file_name");
}
else
{
    # uploaded file is not present
    $logging->warn("cannot find uploaded file");

    die(translate("ERROR_UPLOAD_FILE_NOT_FOUND"));
}

?>
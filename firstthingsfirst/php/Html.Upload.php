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

$logging = new Logging($firstthingsfirst_loglevel, "../logs/".$firstthingsfirst_logfile);


$logging->info("starting upload proces");

# the file name has to be given by means of a parameter
if (isset($_GET['file_name']))
    $file_name = $_GET['file_name'];
else
{
    $file_name = 'tmp.csv';
    $logging->warn("parameter file_name not given, assuming tmp.csv");
}

# test if the uploaded file is actually present
if(isset($_FILES['import_file']))
{
    # uploaded file is present
    $tmp_name = $_FILES['import_file']['tmp_name'];
    $tmp_error = $_FILES['import_file']['error'];
    $tmp_size = $_FILES['import_file']['size'];
    $path_parts = pathinfo($tmp_name);
    $tmp_name_extension = $path_parts['extension'];
    # we want to save the file in the uploads directory
    $save_path = "../uploads";

    $logging->info("found file to upload (name=$tmp_name, size=$tmp_size)");

    # only allow files smaller than 1Mb
    if ($tmp_size > 1048576)
    {
        $loggin->warn("file size exceeds 1MB");
        die("FILE_SIZE_TOO_LARGE");
    }

    $full_file_name = $save_path."/".$file_name;
    if(move_uploaded_file($tmp_name, $full_file_name) == FALSE)
    {
        $logging->warn("could not move uploaded file (tmp_name=$tmp_name, full_file_name=$full_file_name)");
        die("ERROR_MOVE_UPLOADED_FILE");
    }

    # file has been moved
    die("SUCCES");
}
else
{
    # uploaded file is not present
    $logging->warn("can not find uploaded file");

    die("ERROR_UPLOAD_FILE_NOT_FOUND");
}

?>
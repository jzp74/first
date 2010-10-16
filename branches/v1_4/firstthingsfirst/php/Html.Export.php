<?php

/**
 * This file contains code that outputs a dynamically created file
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


# the tmp file name has to be given by means of a parameter
if (isset($_GET['tmp_file']))
    $tmp_file = $_GET['tmp_file'];
else
{
    $tmp_file = 'tmp.txt';
    $logging->warn("parameter tmp_file not given, assuming tmp.txt");
}
# the tmp file name has to be given by means of a parameter
if (isset($_GET['file_name']))
    $file_name = $_GET['file_name'];
else
{
    $file_name = 'list_export.csv';
    $logging->warn("parameter file_name not given, assuming list_export.csv");
}

$logging->info("exporting file (tmp_file=$tmp_file, file_name=$file_name)");

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private, false");
header("Content-Type: text/plain" );
header("Content-Disposition: attachment; filename=\"$file_name\";");
header("Content-Transfer-Encoding:­ binary");
ob_clean();
flush();

# read the file to standard out
$full_tmp_file_name = "../uploads/".$tmp_file;
if (readfile($full_tmp_file_name) == FALSE)
    print("ERROR could not read temporary file");

# delete the tmp file
unlink($full_tmp_file_name);

$logging->info("exported file")

?>
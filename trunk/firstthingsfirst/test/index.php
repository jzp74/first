<?php

/**
 * index.php for regression testing
 *
 * @author Jasper de Jong
 * @copyright 2008 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


require_once("../php/Class.Logging.php");

require_once("../globals.php");
require_once("../localsettings.php");

require_once("../php/external/JSON.php");
require_once("../xajax/xajax.inc.php");

require_once("../php/Text.Buttons.php");
require_once("../php/Text.Errors.php");
require_once("../php/Text.Labels.php");

require_once("../php/Class.Result.php");
require_once("../php/Class.Database.php");
require_once("../php/Class.ListState.php");
require_once("../php/Class.DatabaseTable.php");
require_once("../php/Class.UserDatabaseTable.php");
require_once("../php/Class.User.php");
require_once("../php/Class.ListTableDescription.php");
require_once("../php/Class.ListTable.php");
require_once("../php/Class.ListTableNote.php");

require_once("Html.RegressionTest.php");
require_once("testfunctions.php");
require_once("testdata.php");


/**
 * create global objects
 */
$logging = new Logging($firstthingsfirst_loglevel, $firstthingsfirst_logfile);
$database = new Database();
$list_state = new ListState();
$user = new User();
$result = new Result();
$list_table_description = new ListTableDescription();

/**
 * Initialize xajax
 */
$xajax = new xajax();

/**
 * Register ajax functions
 */
$xajax->registerFunction("start_regression_test");
$xajax->registerFunction("prepare_test");
$xajax->registerFunction("execute_test");
$xajax->registerFunction("end_regression_test");

/**
 * start ajax interactions
 */
$xajax->processRequests();


function test_1 ()
{
    return TRUE;
}

function test_2 ()
{
    return FALSE;
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>

<head>
<meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
    
<title>First Things First</title>
<link rel="shortcut icon" href="images/favicon.ico">
<link rel="stylesheet" href="../css/standard.css">

<?php $xajax->printJavascript("../xajax"); ?>
</head>

<body>
<div id="upper_margin"></div>

<div id="header">
    <div id="header_left_margin">&nbsp</div>
    <div id="header_right_margin">&nbsp</div>
    <?php echo "<div id=\"header_text\">&nbsp;&nbsp;".file_get_contents("../VERSION")."    </div>\n" ?>
</div> <!-- header -->
    
<div id="outer_body">

    <div id="main_body">

        <script language="javascript">xajax_start_regression_test()</script>
            
    </div> <!-- main_body -->

</div> <!-- outer_body -->

<div id="footer">
    <div id="footer_left_margin">&nbsp</div>
    <div id="footer_right_margin">&nbsp</div>
    <div id="footer_text">&nbsp;</div>
</div> <!-- footer -->

<div id="lower_margin"></div>
        
</body>

<html>



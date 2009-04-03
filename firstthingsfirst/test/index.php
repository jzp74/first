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
require_once("../xajax/xajax_core/xajaxAIO.inc.php");

require_once("../php/Class.Result.php");
require_once("../php/Class.Database.php");
require_once("../php/Class.ListState.php");
require_once("../php/Class.DatabaseTable.php");
require_once("../php/Class.UserDatabaseTable.php");
require_once("../php/Class.User.php");
require_once("../php/Class.ListTableDescription.php");
require_once("../php/Class.ListTable.php");
require_once("../php/Class.ListTableNote.php");
require_once("../php/Class.UserListTablePermissions.php");


/**
 * Initialize global objects and language settings
 */
$xajax = new xajax();
$xajax->configure("javascript URI", "../xajax");
$logging = new Logging($firstthingsfirst_loglevel, $firstthingsfirst_logfile);
$database = new Database();
$list_state = new ListState();
$user = new User();
$result = new Result();
$list_table_description = new ListTableDescription();
$user_list_permissions = new UserListTablePermissions();

$text_translations = array();
require_once("../lang/".$firstthingsfirst_lang_prefix_array[$firstthingsfirst_lang].".Text.Buttons.php");
require_once("../lang/".$firstthingsfirst_lang_prefix_array[$firstthingsfirst_lang].".Text.Errors.php");
require_once("../lang/".$firstthingsfirst_lang_prefix_array[$firstthingsfirst_lang].".Text.Labels.php");


/**
 * Import HTML related files
 */
require_once("Html.RegressionTest.php");
require_once("testfunctions.php");
require_once("testdata.php");


/**
 * Register ajax functions
 */
$xajax->register(XAJAX_FUNCTION, "start_regression_test");
$xajax->register(XAJAX_FUNCTION, "prepare_test");
$xajax->register(XAJAX_FUNCTION, "execute_test");
$xajax->register(XAJAX_FUNCTION, "end_regression_test");

/**
 * start ajax interactions
 */
$xajax->processRequest();


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
    <div id="header_contents">
<?php 
echo "        <div id=\"software_version\">&nbsp;&nbsp;".file_get_contents("../VERSION")."</div>\n";
?>
        <div id="page_title">Regression test</div>
        <div id="page_explanation">&nbsp;</div>
        <div id="navigation_container">&nbsp;</div>
    </div> <!-- header_contents -->
</div> <!-- header -->

</div> <!-- header -->
    
<div id="outer_body">

    <div id="main_body">

        <script language="javascript">xajax_start_regression_test()</script>
            
    </div> <!-- main_body -->

</div> <!-- outer_body -->

<div id="footer">
    <div id="footer_left_margin">&nbsp</div>
    <div id="footer_right_margin">&nbsp</div>
    <div id="footer_text"></div>
</div> <!-- footer -->
<div id="lower_margin"><input id="focus_on_this_input" size="1" readonly></div>

<div id="lower_margin"></div>
        
</body>

<html>
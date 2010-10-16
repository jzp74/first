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
require_once("../php/external/xajaxAIO.inc.php");

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

$firstthingsfirst_theme = THEME_BLUE;
$logging = new Logging($firstthingsfirst_loglevel, "../logs/".$firstthingsfirst_logfile);
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
require_once("../php/Html.Utilities.php");
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


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>

<head>
<meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">

<title>Regression test</title>
<link rel="shortcut icon" href="../images/favicon.ico">
<?php echo "<link rel=\"stylesheet\" href=\"../css/".$firstthingsfirst_theme_prefix_array[$firstthingsfirst_theme].".min.css\">\n"; ?>

</head>

<body>
<div id="upper_margin"></div>

<body>
<div id="outer_body">

    <div id="header_container">
        <div id="header_logo"></div>
        <div id="header">
            <div class="corner top_left_normal"></div>
            <div class="corner top_right_normal"></div>
            <div id="header_contents_status">
                <div id="header_contents_status_software_version"><?php print(file_get_contents("VERSION")); ?></div>
                <div id="header_contents_status_login_status">&nbsp;</div>
            </div> <!-- header_contents_status -->
            <div id="portal_title"><?php print($firstthingsfirst_portal_title); ?></div>
            <div id="page_title">&nbsp;</div>
            <div id="navigation_container">&nbsp;</div> <!-- navigation_container -->
        </div> <!-- header -->
    </div> <!-- header_container -->

    <div id="main_body">&nbsp;</div>

    <div id="footer">
        <div id="footer_text"></div>
        <div class="corner bottom_left_normal"></div>
        <div class="corner bottom_right_normal"></div>
    </div> <!-- footer -->

    <div id="lower_margin"><input id="focus_on_this_input" size="1" readonly></div>

</div> <!-- outer_body -->

<script language="javascript" src="../js/external/external.min.js"></script>
<?php print(get_xajax_javascript()); ?>
<script language="javascript" src="../js/handlers.min.js"></script>
<script language="javascript">
handleFunction('start_regression_test');
</script>

</body>

<html>
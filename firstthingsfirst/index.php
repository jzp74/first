<?php

# General index.php as used in this project
# Jasper de Jong
# 2007

# TODO how much data can we transfer from php to js?


require_once("globals.php");
require_once("localsettings.php");

require_once("php/external/JSON.php");
require_once("xajax/xajax.inc.php");

require_once("php/Class.Logging.php");
require_once("php/Class.Result.php");
require_once("php/Class.Database.php");
require_once("php/Class.User.php");
require_once("php/Class.ListTableDescription.php");
require_once("php/Class.ListTable.php");

require_once("php/Html.php");
require_once("php/Html.Login.php");
require_once("php/Html.Portal.php");
require_once("php/Html.List.php");
require_once("php/Html.ListBuilder.php");


# create global objects
$json = new Services_JSON();
$logging = new Logging(LOGGING_TRACE);
$result = new Result();
$database = new Database();
$user = new User();
$list_table_description = new ListTableDescription();
$list_table = new ListTable;
$response = new xajaxResponse();


# Initialize xajax
$xajax = new xajax();

# register portal actions
$xajax->registerFunction("action_get_portal_page");

# register login actions
$xajax->registerFunction("action_get_login_page");
$xajax->registerFunction("action_login");
$xajax->registerFunction("action_logout");

# register list actions
$xajax->registerFunction("action_get_list_page");
$xajax->registerFunction("action_get_list_content");
$xajax->registerFunction("action_get_list_row");
$xajax->registerFunction("action_update_list_row");
$xajax->registerFunction("action_add_list_row");
$xajax->registerFunction("action_del_list_row");
$xajax->registerFunction("action_cancel_list_action");

# register listbuilder actions
$xajax->registerFunction("action_get_listbuilder_page");
$xajax->registerFunction("action_add_listbuilder_row");
$xajax->registerFunction("action_move_listbuilder_row");
$xajax->registerFunction("action_del_listbuilder_row");
$xajax->registerFunction("action_refresh_listbuilder");
$xajax->registerFunction("action_create_list");

$xajax->processRequests();

# TODO remove this temporary hack
if (!$user->is_login())
    $user->login("jasper", "jasper");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>
    <meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
    
    <title><?php echo $tasklist_portal_title ?></title>
    <link rel="stylesheet" href="css/standard.css">
    <link rel="stylesheet" href="css/standard_list.css">
    <link rel="stylesheet" href="css/standard_portal.css">
    <link rel="stylesheet" href="css/standard_listbuilder.css">

    <?php $xajax->printJavascript("xajax"); ?>

</head>

<body>
    
    <div id="left_margin"></div>    

    <div id="right_margin"></div>    

    <div id="upper_margin"></div>    
    
    <?php echo "<div id=\"header\">&nbsp;&nbsp;".file_get_contents("VERSION")."    </div>\n" ?>

    <div id="main_body">

        <script language="javaScript">xajax_action_get_portal_page()</script>            

    </div> <!-- main_body -->

    <div id="footer">...</div>

    <div id="lower_margin"></div>

</body>

<html>


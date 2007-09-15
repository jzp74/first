<?php

# General index.php as used in this project
# Jasper de Jong
# 2007

# TODO how much data can we transfer from php to js?


require_once("globals.php");
require_once("localsettings.php");

require_once("php/external/JSON.php");
require_once("xajax/xajax.inc.php");

require_once("php/Utilities.php");

require_once("php/Text.Buttons.php");
require_once("php/Text.Errors.php");
require_once("php/Text.Labels.php");

require_once("php/Class.Logging.php");
require_once("php/Class.Result.php");
require_once("php/Class.Database.php");
require_once("php/Class.User.php");
require_once("php/Class.ListTableDescription.php");
require_once("php/Class.ListTable.php");
require_once("php/Class.ListTableItemNotes.php");

# Initialize xajax
$xajax = new xajax();

require_once("php/Html.php");
require_once("php/Html.Login.php");
require_once("php/Html.Portal.php");
require_once("php/Html.AddUser.php");
require_once("php/Html.ListTable.php");
require_once("php/Html.ListTableItemNotes.php");
require_once("php/Html.ListBuilder.php");


# needed to initialise several classes
class EmptyClass {}


# dummy initialisations
$list_table = new EmptyClass();
$list_table_item_remarks = new EmptyClass();

# create global objects
$json = new Services_JSON();
$logging = new Logging(LOGGING_TRACE, "firstthingsfirst.log");
$result = new Result();
$database = new Database();
$user = new User();
$list_table_description = new ListTableDescription();
$list_table = new ListTable();
$list_table_item_notes = new ListTableItemNotes();
$response = new xajaxResponse();

# start ajax interactions
$xajax->processRequests();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>

<head>
<meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
    
<title><?php echo $firstthingsfirst_portal_title ?></title>
<link rel="stylesheet" href="css/standard.css">
<link rel="stylesheet" href="css/standard_listbuilder.css">
<link rel="stylesheet" href="css/standard_list.css">

<?php $xajax->printJavascript("xajax"); ?>
</head>

<body>
<div id="upper_margin"></div>

<div id="header">
    <div id="header_left_margin">&nbsp</div>
    <div id="header_right_margin">&nbsp</div>
    <?php echo "<div id=\"header_text\">&nbsp;&nbsp;".file_get_contents("VERSION")."    </div>\n" ?>
</div> <!-- header -->
    
<div id="outer_body">

    <div id="main_body">

        <script language="javaScript">xajax_action_get_portal_page()</script>

    </div> <!-- main_body -->

</div> <!-- outer_body -->

<div id="footer">
    <div id="footer_left_margin">&nbsp</div>
    <div id="footer_right_margin">&nbsp</div>
    <div id="footer_text">...</div>
</div> <!-- footer -->

<div id="lower_margin"></div>
        
</body>

<html>


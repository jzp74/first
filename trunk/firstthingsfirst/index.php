<?php

# General index.php as used in this project
# Jasper de Jong
# 2007

# TODO how much data can we transfer from php to js?


require_once("globals.php");
require_once("localsettings.php");

require_once("php/external/JSON.php");
require_once("xajax/xajax.inc.php");

require_once("php/Text.Buttons.php");
require_once("php/Text.Errors.php");
require_once("php/Text.Labels.php");

require_once("php/Class.Logging.php");
require_once("php/Class.Result.php");
require_once("php/Class.Database.php");
require_once("php/Class.ListState.php");
require_once("php/Class.User.php");
require_once("php/Class.ListTableDescription.php");
require_once("php/Class.ListTable.php");
require_once("php/Class.ListTableItemNotes.php");

# Initialize xajax
$xajax = new xajax();

require_once("php/Html.Utilities.php");
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
$logging = new Logging(LOGGING_INFO, "firstthingsfirst.log");
$result = new Result();
$database = new Database();
$list_state = new ListState();
$user = new User();
$list_table_description = new ListTableDescription();
$list_table = new ListTable();
$list_table_item_notes = new ListTableItemNotes();
$response = new xajaxResponse();

# register process_url function
$xajax->registerFunction("process_url");

# start ajax interactions
$xajax->processRequests();

# parse the url and call function accordingly
# TODO do we want to show an error page in case of malformed request uri?
function process_url ()
{
    global $logging;
    global $user;
    global $response;
    
    $logging->trace("PROCESS_URL (request_uri=".$_SERVER[REQUEST_URI].")");
    
    # show portal page if no action is set
    if (isset($_GET['action']))
        $action = $_GET['action'];
    else
        $action = ACTION_GET_PORTAL_PAGE;
    
    # show portal page
    if ($action == ACTION_GET_PORTAL_PAGE)
        action_get_portal_page();
    # show list page
    else if ($action == ACTION_GET_LIST_PAGE)
    {
        if (isset($_GET['list']))
            action_get_list_page($_GET['list']);
        else
            action_get_portal_page();
    }
    # show add user page
    else if ($action == ACTION_GET_ADD_USER_PAGE)
        action_get_add_user_page();
    # show list builder page
    else if ($action == ACTION_GET_LISTBUILDER_PAGE)
        action_get_listbuilder_page();
    # show portal page in all other instances
    else
        action_get_portal_page();

    return $response;
}

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

        <script language="javaScript">xajax_process_url()</script>
            
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



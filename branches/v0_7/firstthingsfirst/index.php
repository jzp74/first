<?php

/**
 * General index.php as used in this project
 *
 * @author Jasper de Jong
 * @copyright 2008 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 * @todo how much data can we transfer from php to js?
 */


require_once("php/Class.Logging.php");

require_once("globals.php");
require_once("localsettings.php");

require_once("php/external/JSON.php");
require_once("xajax/xajax.inc.php");

require_once("php/Text.Buttons.php");
require_once("php/Text.Errors.php");
require_once("php/Text.Labels.php");

require_once("php/Class.Result.php");
require_once("php/Class.Database.php");
require_once("php/Class.ListState.php");
require_once("php/Class.DatabaseTable.php");
require_once("php/Class.UserDatabaseTable.php");
require_once("php/Class.User.php");
require_once("php/Class.ListTableDescription.php");
require_once("php/Class.ListTable.php");
require_once("php/Class.ListTableNote.php");

require_once("php/Class.HtmlDatabaseTable.php");

/**
 * Initialize xajax
 */
$xajax = new xajax();

require_once("php/Html.Utilities.php");
require_once("php/Html.php");
require_once("php/Html.Login.php");
require_once("php/Html.Portal.php");
require_once("php/Html.UserAdministration.php");
require_once("php/Html.ListTable.php");
require_once("php/Html.ListTableItemNotes.php");
require_once("php/Html.ListBuilder.php");


/**
 * create global objects
 */
$logging = new Logging($firstthingsfirst_loglevel, $firstthingsfirst_logfile);
$database = new Database();
$list_state = new ListState();
$user = new User();

/**
 * register process_url function
 */
$xajax->registerFunction("process_url");

/**
 * start ajax interactions
 */
$xajax->processRequests();


/**
 * parse the url and return html code accordingly
 * @return html code
 */
function process_url ()
{
    global $logging;
    global $user;
    
    $logging->trace("PROCESS_URL (request_uri=".$_SERVER["REQUEST_URI"].")");
    
    # show portal page if no action is set
    if (isset($_GET['action']))
        $action = $_GET['action'];
    else
        $action = ACTION_GET_PORTAL_PAGE;
    
    # show portal page
    if ($action == ACTION_GET_PORTAL_PAGE)
        return action_get_portal_page();
    # show or print list page
    else if ($action == ACTION_GET_LIST_PAGE)
    {
        if (isset($_GET['list']))
            return action_get_list_page($_GET['list']);
        else
            return action_get_portal_page();
    }
    else if ($action == ACTION_GET_LIST_PRINT_PAGE)
    {
        if (isset($_GET['list']))
            return action_get_list_print_page($_GET['list']);
        else
            return action_get_portal_page();
    }
    # show add user page
    else if ($action == ACTION_GET_USER_ADMIN_PAGE)
        return action_get_user_admin_page();
    # show list builder page
    else if ($action == ACTION_GET_LISTBUILDER_PAGE)
    {
        if (isset($_GET['list']))
            return action_get_listbuilder_page($_GET['list']);
        else
            return action_get_listbuilder_page("");
    }
    # show portal page in all other instances
    else
        return action_get_portal_page();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>

<head>
<meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
    
<title>First Things First</title>
<link rel="shortcut icon" href="images/favicon.ico">
<link rel="stylesheet" href="css/standard.css">
<link rel="stylesheet" href="css/standard_listbuilder.css">
<link rel="stylesheet" href="css/standard_database_table.css">
<link rel="stylesheet" href="css/standard_print.css" media="print">

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

        <script language="javascript">xajax_process_url()</script>
            
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



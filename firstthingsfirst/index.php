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
    $action = "";
    if (isset($_GET['action']))
        $action = $_GET['action'];
    
    # do nothing for the login page
    # TEMPORARY SOLUTION
    # for some reason firefox needs the login page served as a whole
    if ($action == ACTION_GET_LOGIN_PAGE)
    {
        $response = new xajaxResponse();

        $response->addScript("document.getElementById('user_name').focus()");
        
        return $response;
    }
    # redirect to login page when user is not logged in
    else if (!$user->is_login())
    {
        $response = new xajaxResponse();

        $response->AddScript("window.location.assign('index.php?action=".ACTION_GET_LOGIN_PAGE."')");
        $response->addScript("document.getElementById('user_name').focus()");
    
        return $response;
    }
    
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
    # redirect to portal page in all other instances
    else
    {
        $response = new xajaxResponse();
        $response->AddScriptCall("window.location.assign('index.php?action=".ACTION_GET_PORTAL_PAGE."')");
        return $response;
    }
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
    
<?php
    # define js functions
    print("        <script type=\"text/javascript\">\n");

    # set a permission error in the message pane
    # each page should have a message pane
    print("function set_permission_error()\n");
    print("{\n");
    print("    alert(\"no permissions\");\n");
    print("}\n");
    
    # end of js functions
    print("        </script>\n");
?>

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
<?php
    # logout any active user and serve the html page for login
    # TEMPORARY SOLUTION
    # for some reason firefox needs the login page served as a whole
    if ($_GET['action'] == ACTION_GET_LOGIN_PAGE)
    {
        $user->logout();
        print get_login_page_html();
    }
?>
    </div> <!-- main_body -->

</div> <!-- outer_body -->

<div id="footer">
    <div id="footer_left_margin">&nbsp</div>
    <div id="footer_right_margin">&nbsp</div>
    <div id="footer_text"></div>
</div> <!-- footer -->

<div id="lower_margin"></div>

<script language="javascript">xajax_process_url()</script>
        
</body>

<html>



<?php

/**
 * General index.php as used in this project
 *
 * @author Jasper de Jong
 * @copyright 2007-2009 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 * @todo how much data can we transfer from php to js?
 */


/**
 * Import class files and settings files
 */
require_once("php/Class.Logging.php");

require_once("globals.php");
require_once("localsettings.php");

require_once("php/external/JSON.php");
require_once("xajax/xajax_core/xajaxAIO.inc.php");

require_once("php/Html.Utilities.php");

require_once("php/Class.Result.php");
require_once("php/Class.Database.php");
require_once("php/Class.ListState.php");
require_once("php/Class.DatabaseTable.php");
require_once("php/Class.UserDatabaseTable.php");
require_once("php/Class.User.php");
require_once("php/Class.ListTableDescription.php");
require_once("php/Class.ListTable.php");
require_once("php/Class.ListTableNote.php");
require_once("php/Class.UserListTablePermissions.php");


/**
 * Initialize global objects and language settings
 */
$xajax = new xajax();
$xajax->configure("javascript URI", "../xajax");

$logging = new Logging($firstthingsfirst_loglevel, "logs/".$firstthingsfirst_logfile);
$database = new Database();
$list_state = new ListState();
$user = new User();
$list_table_description = new ListTableDescription();
$user_list_permissions = new UserListTablePermissions();
$user_start_time_array = array();

$text_translations = array();
if ($user->is_login())
    $firstthingsfirst_lang = $user->get_lang();
require_once("lang/".$firstthingsfirst_lang_prefix_array[$firstthingsfirst_lang].".Text.Buttons.php");
require_once("lang/".$firstthingsfirst_lang_prefix_array[$firstthingsfirst_lang].".Text.Errors.php");
require_once("lang/".$firstthingsfirst_lang_prefix_array[$firstthingsfirst_lang].".Text.Labels.php");


/**
 * Import HTML related files
 */
require_once("php/Class.HtmlDatabaseTable.php");

require_once("php/Html.php");
require_once("php/Html.Login.php");
require_once("php/Html.Portal.php");
require_once("php/Html.UserAdministration.php");
require_once("php/Html.ListTable.php");
require_once("php/Html.ListTableItemNotes.php");
require_once("php/Html.ListBuilder.php");
require_once("php/Html.UserListTablePermissions.php");
require_once("php/Html.UserSettings.php");


/**
 * register local functions
 */
$xajax->register(XAJAX_FUNCTION, "set_translations");
$xajax->register(XAJAX_FUNCTION, "process_url");

/**
 * start ajax interactions
 */
$xajax->processRequest();


function get_xajax_javascript ()
{
    $html_str = "<script language=\"javascript\">\n";
    $html_str .= "    try { if (undefined == xajax.config) xajax.config = {}; } catch (e) { xajax = {}; xajax.config = {}; }\n";
    $html_str .= "    xajax.config.requestURI = \"".$_SERVER["REQUEST_URI"]."\"\n";
    $html_str .= "    xajax.config.statusMessages = false;\n";
    $html_str .= "    xajax.config.waitCursor = true;\n";
    $html_str .= "    xajax.config.version = \"xajax 0.5\";\n";
    $html_str .= "    xajax.config.legacy = false;\n";
    $html_str .= "    xajax.config.defaultMode = \"asynchronous\";\n";
    $html_str .= "    xajax.config.defaultMethod = \"POST\";\n";
    $html_str .= "</script>\n";

    return $html_str;
}

/**
 * set translation vars in javascript
 * @return void
 */
function set_translations ()
{
    global $logging;

    $logging->trace("set translations");

    $response = new xajaxResponse();
    $accept_str = translate("BUTTON_ACCEPT");
    $cancel_str = translate("BUTTON_CANCEL");
    $close_str = translate("BUTTON_CLOSE");

    $response->script("setTranslations('".$accept_str."', '".$cancel_str."', '".$close_str."')");

    return $response;
}

/**
 * parse the url and return html code accordingly
 * @return html code
 */
function process_url ()
{
    global $logging;
    global $user;

    $logging->info("PROCESS_URL (request_uri=".$_SERVER["REQUEST_URI"].")");

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

        $response->script("document.getElementById('user_name_id').focus()");

        return $response;
    }
    # redirect to login page when user is not logged in
    else if (!$user->is_login())
    {
        $response = new xajaxResponse();

        $response->script("window.location.assign('index.php?action=".ACTION_GET_LOGIN_PAGE."')");
        $response->script("document.getElementById('user_name_id').focus()");

        return $response;
    }

    # show portal page
    if ($action == ACTION_GET_PORTAL_PAGE)
        return action_get_portal_page();
    # show list builder page
    else if ($action == ACTION_GET_LISTBUILDER_PAGE)
    {
        if (isset($_GET['list']))
            return action_get_listbuilder_page($_GET['list']);
        else
            return action_get_listbuilder_page("");
    }
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
    # show user list permissions page
    else if ($action == ACTION_GET_USERLISTTABLEPERMISSIONS_PAGE)
        return action_get_user_list_permissions_page();
    # show user admin page
    else if ($action == ACTION_GET_USER_ADMIN_PAGE)
        return action_get_user_admin_page();
    # show user admin page
    else if ($action == ACTION_GET_USER_SETTINGS_PAGE)
        return action_get_user_settings_page();
    # redirect to portal page in all other instances
    else
    {
        $response = new xajaxResponse();
        $response->call("window.location.assign('index.php?action=".ACTION_GET_PORTAL_PAGE."')");

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
</head>

<body>
<div id="upper_margin"></div>

<div id="header">
    <div id="header_left_margin"></div>
    <div id="header_right_margin"></div>
    <div id="header_contents">
        <div id="header_contents_status">
            <div id="header_contents_status_software_version"><?php print(file_get_contents("VERSION")); ?></div>
            <div id="header_contents_status_login_status"><?php print(get_login_status()); ?></div>
        </div> <!-- header_contents_status -->
        <div id="portal_title"><?php print($firstthingsfirst_portal_title); ?></div>
<?php
# TEMPORARY SOLUTION
if (isset($_GET['action']) && $_GET['action'] == ACTION_GET_LOGIN_PAGE)
    echo "        <div id=\"page_title\">".translate("LABEL_PLEASE_LOGIN")."</div>\n";
else
    echo "        <div id=\"page_title\">&nbsp;</div>\n";
?>
        <div id="navigation_container">&nbsp;</div> <!-- navigation_container -->
    </div> <!-- header_contents -->
</div> <!-- header -->

<div id="outer_body">

    <div id="main_body">
<?php
    # logout any active user and serve the html page for login
    # TEMPORARY SOLUTION
    # for some reason firefox needs the login page served as a whole
    if (isset($_GET['action']) && $_GET['action'] == ACTION_GET_LOGIN_PAGE)
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
<div id="lower_margin"><input id="focus_on_this_input" size="1" readonly></div>

<script language="javascript" src="xajax/xajax_js/xajax_core.js"></script>
<?php print(get_xajax_javascript()); ?>
<script language="javascript" src="js/external/jquery.min.js"></script>
<script language="javascript" src="js/external/jquery.qtip.min.js"></script>
<script language="javascript" src="js/external/ajaxupload.min.js"></script>
<script language="javascript" src="js/tooltips.js"></script>
<script language="javascript" src="js/handlers.js"></script>
<script language="javascript">
handleFunction('set_translations');
handleFunction('process_url');
</script>

<div id="modal_blanket"></div>

</body>

<html>
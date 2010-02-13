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
require_once("php/external/xajaxAIO.inc.php");

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
$xajax->register(XAJAX_FUNCTION, "set_browser_compatibility_message");

/**
 * start ajax interactions
 */
$xajax->processRequest();


/**
 * set translation vars in javascript
 * @return xajaxResponse every xajax registered function needs to return this object
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
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function process_url ()
{
    global $logging;
    global $user;

    $logging->debug("PROCESS_URL (request_uri=".$_SERVER["REQUEST_URI"].")");

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

/**
 * show a compatibility message to the user on the login screen
 * @param $browser_name_str string string containing browser name
 * @param $browser_version float browser version number
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function set_browser_compatibility_message ($browser_name_str, $browser_version)
{
    $response = new xajaxResponse();
    $unsupported_browser_message = translate("ERROR_BROWSER_UNSUPPORTED")."$browser_name_str $browser_version";

    if (($browser_name_str == 'Firefox') && (($browser_version < 2) || ($browser_version > 3.5)))
    {
        $response->script("showTooltip('#login_contents', '$unsupported_browser_message', 'info', 'below')");

        return $response;
    }
    if (($browser_name_str == 'Chrome') && (($browser_version < 4) || ($browser_version > 4)))
    {
        $response->script("showTooltip('#login_contents', '$unsupported_browser_message', 'info', 'below')");

        return $response;
    }
    if (($browser_name_str == 'Internet Explorer') && (($browser_version < 7) || ($browser_version > 9)))
    {
        $response->script("showTooltip('#login_contents', '$unsupported_browser_message', 'info', 'below')");

        return $response;
    }
    if (($browser_name_str != 'Firefox') && ($browser_name_str != 'Chrome') && ($browser_name_str != 'Internet Explorer'))
    {
        $response->script("showTooltip('#login_contents', '$unsupported_browser_message', 'info', 'below')");

        return $response;
    }

    // do not show any message when browser is compatible
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>

<head>
<meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
<title>First Things First</title>
<link rel="shortcut icon" href="images/favicon.ico">
<link rel="stylesheet" href="css/standard.min.css">
<link rel="stylesheet" href="css/standard_listbuilder.min.css">
<link rel="stylesheet" href="css/standard_database_table.min.css">
<link rel="stylesheet" href="css/standard_print.min.css" media="print">
</head>

<body>
<div id="outer_body">

    <div id="header_container">
        <div id="header">
            <div class="corner top_left_normal"></div>
            <div class="corner top_right_normal"></div>
            <div id="header_contents_status">
                <div id="header_contents_status_software_version"><?php print(file_get_contents("VERSION")); ?></div>
                <div id="header_contents_status_login_status"><?php print(get_login_status()); ?></div>
            </div> <!-- header_contents_status -->
            <div id="portal_title"><?php print($firstthingsfirst_portal_title); ?></div>
<?php
# TEMPORARY SOLUTION
if (isset($_GET['action']) && $_GET['action'] == ACTION_GET_LOGIN_PAGE)
    echo "            <div id=\"page_title\">".translate("LABEL_PLEASE_LOGIN")."</div>\n";
else
    echo "            <div id=\"page_title\">&nbsp;</div>\n";
?>
            <div id="navigation_container">&nbsp;</div> <!-- navigation_container -->
        </div> <!-- header -->
    </div> <!-- header_container -->

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

    <div id="footer">
        <div id="footer_text"></div>
        <div class="corner bottom_left_normal"></div>
        <div class="corner bottom_right_normal"></div>
    </div> <!-- footer -->

</div> <!-- outer_body -->

<script language="javascript" src="js/external/external.min.js"></script>
<?php print(get_xajax_javascript()); ?>
<script language="javascript" src="js/tooltips.min.js"></script>
<script language="javascript" src="js/handlers.min.js"></script>
<script language="javascript">
handleFunction('set_translations');
handleFunction('process_url');
<?php
# TEMPORARY SOLUTION
# set variable in javascript
if (isset($_GET['action']) && $_GET['action'] == ACTION_GET_LOGIN_PAGE)
    echo "browserDetect.init();\n";
    echo "handleFunction('set_browser_compatibility_message', browserDetect.browser, browserDetect.version);\n";
?>
</script>

</body>

<html>
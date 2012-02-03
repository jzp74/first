<?php

/**
 * General index.php as used in this project
 *
 * @author Jasper de Jong
 * @copyright 2007-2010 Jasper de Jong
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
$mobile = FALSE;

$text_translations = array();
if ($user->is_login())
    $firstthingsfirst_lang = $user->get_lang();
require_once("lang/".$firstthingsfirst_lang_prefix_array[$firstthingsfirst_lang].".Text.Buttons.php");
require_once("lang/".$firstthingsfirst_lang_prefix_array[$firstthingsfirst_lang].".Text.Errors.php");
require_once("lang/".$firstthingsfirst_lang_prefix_array[$firstthingsfirst_lang].".Text.Labels.php");


/**
 * import HTML related files
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
 * register plugin class
 */
$objPluginManager = &xajaxPluginManager::getInstance();
$objPluginManager->registerPlugin(new custom_response());

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


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>

<head>
    <meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
    <title>First Things First</title>
    <link rel="shortcut icon" href="images/favicon.ico">
    <?php echo "<link rel=\"stylesheet\" href=\"css/".$firstthingsfirst_theme_prefix_array[$firstthingsfirst_theme].".min.css\">\n"; ?>
    <link rel="stylesheet" href="css/print.min.css" media="print">
</head>

<body>
<div id="outer_body">

    <div id="header_container">
        <div id="header_logo"></div>
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

    <div id="lower_margin"><input id="focus_on_this_input" size="1" readonly></div>

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

</html>
<?php

/**
 * This is the script to upgrade from version 0.9 to version 1.0
 *
 */

require_once("../php/Class.Logging.php");

require_once("../globals.php");
require_once("../localsettings.php");

require_once("../php/external/JSON.php");

/**
 * create language array and load current language
 */
$text_translations = array();
require_once("../lang/".$firstthingsfirst_lang_prefix_array[$firstthingsfirst_lang].".Text.Buttons.php");
require_once("../lang/".$firstthingsfirst_lang_prefix_array[$firstthingsfirst_lang].".Text.Errors.php");
require_once("../lang/".$firstthingsfirst_lang_prefix_array[$firstthingsfirst_lang].".Text.Labels.php");

require_once("../php/Class.Database.php");
require_once("../php/Class.DatabaseTable.php");
require_once("../php/Class.UserDatabaseTable.php");
require_once("../php/Class.User.php");
require_once("../php/Class.ListState.php");
require_once("../php/Class.ListTableDescription.php");
require_once("../php/Class.ListTable.php");
require_once("../php/Class.ListTableNote.php");
require_once("../php/Class.UserListTablePermissions.php");


$logging = new Logging(LOGGING_OFF);
$database = new Database();
$list_state = new ListState();
$user = new User();
$user_list_permissions = new UserListTablePermissions();

# update string
$update_string = "firstthingsfirst update (v0.9 -> v1.0)";


# several helper functions
function fatal ($str)
{
    global $update_string;
    
    echo "<br><strong><font color=\"red\">".$update_string." failed</font><br>";
    exit("&nbsp;&nbsp;&nbsp;-> ".$str."</strong><br>");
}

# opening message
echo "<strong>starting ".$update_string."</strong><br><br>\n";

echo "reading all users<br>";
$query = "SELECT _name FROM ".USER_TABLE_NAME;
$query_result = $database->query($query);
if ($query_result != FALSE)
{
    $all_users = array();
    while ($row = $database->fetch($query_result))
    {
        echo "&nbsp;&nbsp;&nbsp;found user <strong>".$row[0]."</strong><br>";
        array_push($all_users, $row[0]);
    }
}
else
    fatal("could not find any users");

echo "update users table<br>";
$query = "ALTER TABLE ".USER_TABLE_NAME." ADD COLUMN ".USER_LANG_FIELD_NAME." ".DB_DATATYPE_TEXTMESSAGE." AFTER ".USER_PW_FIELD_NAME;
$query_result = $database->query($query);
if ($query_result == FALSE)
    fatal("could not update users table");
echo "updated users table<br>";
            
echo "update all users<br>";
foreach ($all_users as $user_name)
{
    $query = "UPDATE ".USER_TABLE_NAME." SET ".USER_LANG_FIELD_NAME."='".LANG_EN."'";
    $query_result = $database->query($query);
    if ($query_result == FALSE)
        fatal("could not create user list permissions for list ".$list_name);
}
echo "all users updated<br>";
    
# succes
echo "<br><strong>update complete!</strong><br>";

?>
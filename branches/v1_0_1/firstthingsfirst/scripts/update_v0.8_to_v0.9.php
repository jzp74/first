<?php

/**
 * This is the script to upgrade from version 0.8 to version 0.9
 *
 */

require_once("../php/Class.Logging.php");

require_once("../globals.php");
require_once("../localsettings.php");

require_once("../php/external/JSON.php");

require_once("../php/Text.Labels.php");
require_once("../php/Text.Errors.php");

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
$update_string = "firstthingsfirst update (v0.8 -> v0.9)";


# several helper functions
function fatal ($str)
{
    global $update_string;
    
    echo "<br><strong><font color=\"red\">".$update_string." failed</font><br>";
    exit("&nbsp;&nbsp;&nbsp;-> ".$str."</strong><br>");
}

# opening message
echo "<strong>starting ".$update_string."</strong><br><br>\n";

echo "reading all lists<br>";
$query = "SELECT _title, _creator FROM ".LISTTABLEDESCRIPTION_TABLE_NAME;
$query_result = $database->query($query);
if ($query_result != FALSE)
{
    $all_lists = array();
    while ($row = $database->fetch($query_result))
    {
        echo "&nbsp;&nbsp;&nbsp;found list <strong>".$row[0]."</strong><br>";
        array_push($all_lists, array($row[0], $row[1]));
    }
}
else
    fatal("could not find any lists");
    
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

echo "create user list permissions table<br>";
if ($user_list_permissions->create() == FALSE)
{
    if ($database->table_exists(USERLISTTABLEPERMISSIONS_TABLE_NAME) == TRUE)
    {
        echo "removing table ".USERLISTTABLEPERMISSIONS_TABLE_NAME."<br>";
        $query = "DROP TABLE ".USERLISTTABLEPERMISSIONS_TABLE_NAME;
        $result = $database->query($query);
        if ($result != FALSE)
            echo "removed table<br>";
        if ($user_list_permissions->create() == FALSE)
            fatal("could not create user list permissions table");
    }
    else
        fatal("could not create user list permissions table");
}
        
echo "create user list permissions for all users and all lists<br>";
echo "user admin is list admin of all lists<br>";
foreach ($all_lists as $list)
{
    $list_name = $list[0];
    $list_creator = $list[1];
    
    foreach ($all_users as $user_name)
    {
        $query = "INSERT INTO ".USERLISTTABLEPERMISSIONS_TABLE_NAME." VALUES (0, \"".$list_name."\", \"".$user_name."\", ";
        if ($user_name == $list_creator)
        {
            echo "&nbsp;&nbsp;&nbsp;user <strong>".$user_name."</strong> is creator of list <strong>";
            echo $list_name."</strong> (and is therefore list admin)<br>";
            $query .= "1, 1, 1, \"admin\", \"".strftime(DB_DATETIME_FORMAT)."\")";
        }
        else if ($user_name == "admin")
            $query .= "1, 1, 1, \"admin\", \"".strftime(DB_DATETIME_FORMAT)."\")";
        else
            $query .= "0, 0, 0, \"admin\", \"".strftime(DB_DATETIME_FORMAT)."\")";
#        echo "query: ".$query."<br>";
        $query_result = $database->query($query);
        if ($query_result == FALSE)
            fatal("could not create user list permissions for list ".$list_name);
    }
}

echo "<br>transform all users<br>";
$query = "ALTER TABLE ".USER_TABLE_NAME." DROP COLUMN ";
$query .= "_edit_list";
$query_result = $database->query($query);
if ($query_result == FALSE)
    fatal("could transform users");
echo "users transformed<br>";
    
# succes
echo "<br><strong>update complete!</strong><br>";


?>
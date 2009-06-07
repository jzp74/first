<?php

/**
 * This is the script to upgrade from version 1.0 to version 1.1
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


$json = new Services_JSON();
$logging = new Logging(LOGGING_OFF);
$database = new Database();
$list_state = new ListState();
$user = new User();
$user_list_permissions = new UserListTablePermissions();

# update string
$update_string = "firstthingsfirst update (v1.0 -> v1.1)";


# several helper functions
function fatal ($str)
{
    global $update_string;

    echo "<br><strong><font color=\"red\">".$update_string." failed</font><br>";
    exit("&nbsp;&nbsp;&nbsp;-> ".$str."</strong><br>");
}

# opening message
echo "<strong>starting ".$update_string."</strong><br><br>\n";

# global arrays
$all_foreign_keys = array();
$all_tables = array();

echo "removing all foreign keys from database<br>";
$query = "SELECT TABLE_NAME, CONSTRAINT_NAME FROM information_schema.`TABLE_CONSTRAINTS`";
$query .= "WHERE TABLE_NAME LIKE '".$firstthingsfirst_db_table_prefix."%' AND CONSTRAINT_TYPE='FOREIGN KEY'";
$query_result = $database->query($query);
if ($query_result != FALSE)
{
    while ($row = $database->fetch($query_result))
    {
        echo "&nbsp;&nbsp;&nbsp;found foreign key <strong>".$row[0].".".$row[1]."</strong><br>";
        array_push($all_foreign_keys, array($row[0], $row[1]));
    }
}

foreach ($all_foreign_keys as $one_foreign_key)
{
    $query = "ALTER TABLE ".$one_foreign_key[0]." DROP FOREIGN KEY ".$one_foreign_key[1];
    $query_result = $database->query($query);
    if ($query_result == FALSE)
        fatal("could not remove foreign key: ".$one_foreign_key[0].".".$one_foreign_key[1]);
}
echo "removed ".count($all_foreign_keys)." foreign keys<br>";

echo "converting all database tables to MyISAM<br>";
$query = "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_NAME LIKE '".$firstthingsfirst_db_table_prefix."_%'";
$query_result = $database->query($query);
if ($query_result != FALSE)
{
    while ($row = $database->fetch($query_result))
    {
#         echo "&nbsp;&nbsp;&nbsp;found table <strong>".$row[0]."</strong><br>";
        array_push($all_tables, $row[0]);
    }
}
else
    fatal("could not find any tables");

foreach ($all_tables as $one_table)
{
    $query = "ALTER TABLE ".$one_table." ENGINE=MyISAM";
    $query_result = $database->query($query);
    if ($query_result == FALSE)
        fatal("could not convert database table: ".$one_table);
}
echo "converted ".count($all_tables)." database tables<br>";

# succes
echo "<br><strong>update complete!</strong><br>";

?>
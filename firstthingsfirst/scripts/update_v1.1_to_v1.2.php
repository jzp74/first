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
$update_string = "firstthingsfirst update (v1.1 -> v1.2)";


# several helper functions
function fatal ($str)
{
    global $update_string;

    echo "<br><strong><font color=\"red\">".$update_string." failed</font><br>";
    exit("&nbsp;&nbsp;&nbsp;-> ".$str."</strong><br>");
}

function ok ($str)
{
    echo "<strong>ACTION OK</strong>&nbsp;-&nbsp;$str<br>";
}

# opening message
echo "<strong>starting ".$update_string."</strong><br><br>\n";

# global arrays
#$all_foreign_keys = array();
#$all_tables = array();

echo "add columns for date format and number of lines per page to users table<br>";
$query = "ALTER TABLE ".USER_TABLE_NAME;
# add date format field
$query .= " ADD COLUMN ".USER_DATE_FORMAT_FIELD_NAME." ".DB_DATATYPE_TEXTMESSAGE." AFTER ".USER_LANG_FIELD_NAME;
# add date format field
$query .= ", ADD COLUMN ".USER_LINES_PER_PAGE_FIELD_NAME." ".DB_DATATYPE_INT." AFTER ".USER_DATE_FORMAT_FIELD_NAME;
#echo "query=".$query."<br>";
$query_result = $database->query($query);
if ($query_result == FALSE)
    fatal("could not add columns for date format and number of lines per page to users table");
echo "added columns to users table<br>";

echo "set date format and number of lines per page for all users<br>";
$query = "UPDATE ".USER_TABLE_NAME." SET ";
# set US date format for all users
$query .= USER_DATE_FORMAT_FIELD_NAME."='".LABEL_USER_DATE_FORMAT_US."', ";
# set 12 lines per page for all users
$query .= USER_LINES_PER_PAGE_FIELD_NAME."=12";
#echo "query=".$query."<br>";
$query_result = $database->query($query);
    if ($query_result == FALSE)
        fatal("could not set date format and number of lines per page for all users");
echo "all users have been updated<br>";

echo "add columns for active records and archived records to list description table<br>";
$query = "ALTER TABLE ".LISTTABLEDESCRIPTION_TABLE_NAME;
# add date format field
$query .= " ADD COLUMN ".LISTTABLEDESCRIPTION_ACTIVE_RECORDS_FIELD_NAME." ".DB_DATATYPE_INT." AFTER ".LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME;
# add date format field
$query .= ", ADD COLUMN ".LISTTABLEDESCRIPTION_ARCHIVED_RECORDS_FIELD_NAME." ".DB_DATATYPE_INT." AFTER ".LISTTABLEDESCRIPTION_ACTIVE_RECORDS_FIELD_NAME;
#echo "query=".$query."<br>";
$query_result = $database->query($query);
if ($query_result == FALSE)
    fatal("could not add columns for active records and archived records to list description table");
echo "added columns to list description table<br>";

echo "set active records and archived records for all lists<br>";
$query = "SELECT ".LISTTABLEDESCRIPTION_TITLE_FIELD_NAME." FROM ".LISTTABLEDESCRIPTION_TABLE_NAME;
#echo "query=".$query."<br>";
$query_result = $database->query($query);
if ($query_result != FALSE)
{
    $all_lists = array();
    while ($row = $database->fetch($query_result))
    {
        #echo "&nbsp;&nbsp;&nbsp;found list <strong>".$row[0]."</strong><br>";
        array_push($all_lists, $row[0]);
    }
}
else
    fatal("could not find any lists");

foreach($all_lists as $one_list)
{
    echo "&nbsp;&nbsp;&nbsp;update list <strong>$one_list</strong><br>";
    $table_name = ListTable::_convert_list_name_to_table_name($one_list);

    # select active records
    $query = "SELECT COUNT(".DB_ID_FIELD_NAME.") FROM $table_name WHERE ".DB_TS_ARCHIVED_FIELD_NAME."='".DB_NULL_DATETIME."'";
    #echo "query=".$query."<br>";
    $query_result = $database->query($query);
    if ($query_result == FALSE)
        fatal("could not find active records of list: $one_list");
    $result = $database->fetch($query_result);
    $active_records = $result[0];

    # select archived records
    $query = "SELECT COUNT(".DB_ID_FIELD_NAME.") FROM $table_name WHERE ".DB_TS_ARCHIVED_FIELD_NAME.">'".DB_NULL_DATETIME."'";
    #echo "query=".$query."<br>";
    $query_result = $database->query($query);
    if ($query_result == FALSE)
        fatal("could not find archived records of list: $one_list");
    $result = $database->fetch($query_result);
    $archived_records = $result[0];

    # update list table description
    $query = "UPDATE ".LISTTABLEDESCRIPTION_TABLE_NAME." SET ";
    $query .= LISTTABLEDESCRIPTION_ACTIVE_RECORDS_FIELD_NAME."=$active_records, ";
    $query .= LISTTABLEDESCRIPTION_ARCHIVED_RECORDS_FIELD_NAME."=$archived_records ";
    $query .= "WHERE ".LISTTABLEDESCRIPTION_TITLE_FIELD_NAME."='$one_list'";
    #echo "query=".$query."<br>";
    $query_result = $database->query($query);
    if ($query_result == FALSE)
        fatal("could not set active records and archived records for list: $one_list");
}
echo "all lists have been updated<br>";

# succes
echo "<br><strong>update complete!</strong><br>";

?>
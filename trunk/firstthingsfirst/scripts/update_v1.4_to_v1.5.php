<?php

/**
 * This is the script to upgrade from version 1.4 to version 1.5
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
$update_string = "firstthingsfirst update (v1.4 -> v1.5)";


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

echo "upgrading definitions of all lists<br>";
$query = "SELECT ".DB_ID_FIELD_NAME.", ".LISTTABLEDESCRIPTION_TITLE_FIELD_NAME.", ".LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME." FROM ".LISTTABLEDESCRIPTION_TABLE_NAME;
$query_result = $database->query($query);
if ($query_result != FALSE)
{
    $all_lists = array();
    while ($row = $database->fetch($query_result))
    {
        #echo "&nbsp;&nbsp;&nbsp;found list <strong>".$row[0]."</strong><br>";
        array_push($all_lists, $row);
    }
}
else
    fatal("could not find any lists");

foreach($all_lists as $one_list)
{
    $list_id = $one_list[DB_ID_FIELD_NAME];
    $list_name = $one_list[LISTTABLEDESCRIPTION_TITLE_FIELD_NAME];
    $table_name = ListTable::_convert_list_name_to_table_name($list_name);
    $definition = $one_list[LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME];
    $definition_array = (array)$json->decode(html_entity_decode($definition, ENT_QUOTES));
    
    echo "&nbsp;&nbsp;&nbsp;update list <strong>$list_name</strong><br>";

    $field_names = array_keys($definition_array);
    echo "&nbsp;&nbsp;&nbsp;updating list definition<br>\n";

    $new_definition = array();
    foreach ($field_names as $field_name)
    {
        $field_definition = $definition_array[$field_name];
        $field_definition_str = htmlentities($json->encode($field_definition), ENT_QUOTES);
        $field_type = $field_definition[0];
        $field_options = $field_definition[1];
        #echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;before: $field_definition_str<br>";
        #echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;found field <strong>".$field_name."</strong> of field type ".$field_type." with options ".$field_options."<br>\n";            
        
        if (count($field_definition) != 2)
            fatal ("definition of list has incorrect number of columns");

        # add field_visible_in_overview
        if (($field_name == DB_ID_FIELD_NAME) && ($field_options == ID_COLUMN_NO_SHOW))
            array_push($field_definition, COLUMN_NO_SHOW);
        else
            array_push($field_definition, COLUMN_SHOW);
        
        # fix empty options in id field
        if ($field_name == DB_ID_FIELD_NAME)
            $field_definition[1] = "";

        $new_definition[$field_name] = $field_definition;
        $field_definition_str = htmlentities($json->encode($field_definition), ENT_QUOTES);
        #echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;after: $field_definition_str<br>";
        $definition = htmlentities($json->encode($new_definition), ENT_QUOTES);
    }
    
    $query = "UPDATE ".LISTTABLEDESCRIPTION_TABLE_NAME." SET ".LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME;
    $query .= "='$definition' WHERE ".DB_ID_FIELD_NAME."='$list_id'";
    #echo $query."<br>\n";
    $result = $database->query($query);
    if ($result == FALSE)
        fatal("could not update list definition");
}

echo "add columns for theme, decimal mark and can create user to users table<br>";
$query = "ALTER TABLE ".USER_TABLE_NAME;
# add theme field
$query .= " ADD COLUMN ".USER_THEME_FIELD_NAME." ".DB_DATATYPE_TEXTMESSAGE." AFTER ".USER_LINES_PER_PAGE_FIELD_NAME;
$query .= ", ADD COLUMN ".USER_DECIMAL_MARK_FIELD_NAME." ".DB_DATATYPE_TEXTMESSAGE." AFTER ".USER_DATE_FORMAT_FIELD_NAME;
$query .= ", ADD COLUMN ".USER_CAN_CREATE_USER_FIELD_NAME." ".DB_DATATYPE_BOOL." AFTER ".USER_CAN_CREATE_LIST_FIELD_NAME;
#echo "query=".$query."<br>";
$query_result = $database->query($query);
if ($query_result == FALSE)
    fatal("could not add columns for theme, decimal mark and can create user to users table");
echo "added columns to users table<br>";

echo "update users table<br>";
$query = "UPDATE ".USER_TABLE_NAME." SET ";
# set BLUE theme for all users
$query .= USER_THEME_FIELD_NAME."='".LABEL_USER_THEME_BLUE."'";
# set POINT as decimal mark for all users
$query .= ", ".USER_DECIMAL_MARK_FIELD_NAME."='".LABEL_USER_DECIMAL_MARK_POINT."'";
# set FALSE as can create user for all users
$query .= ", ".USER_CAN_CREATE_USER_FIELD_NAME."=0";
#echo "query=".$query."<br>";
$query_result = $database->query($query);
if ($query_result == FALSE)
    fatal("could not set theme and decimal mark for all users");
echo "users table has been updated<br>";

echo "update permissions of all users<br>";
$query = "SELECT ".USER_NAME_FIELD_NAME.", ".USER_IS_ADMIN_FIELD_NAME." FROM ".USER_TABLE_NAME;
$query_result = $database->query($query);
if ($query_result != FALSE)
{
    $all_users = array();
    while ($row = $database->fetch($query_result))
    {
        echo "&nbsp;&nbsp;&nbsp;found user <strong>".$row[0]."</strong><br>";
        array_push($all_users, array($row[0], $row[1]));
    }
}
else
    fatal("could not find any users");

foreach ($all_users as $user_array)
{
    $user_name = $user_array[0];
    $user_is_admin = $user_array[1];
    
    if ($user_is_admin == 1)
    {
        $query = "UPDATE ".USER_TABLE_NAME." SET ".USER_CAN_CREATE_USER_FIELD_NAME."=1 WHERE ";
        $query .= USER_NAME_FIELD_NAME."='$user_name'";
        #echo "query=".$query."<br>";
        $query_result = $database->query($query);
        if ($query_result == FALSE)
            fatal("could not create user list permissions for list ".$list_name);
    }
}
echo "all users have been updated<br>";

# succes
echo "<br><strong>update complete!</strong><br>";

?>
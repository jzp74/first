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
$update_string = "firstthingsfirst update (v1.2 -> v1.3)";


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

echo "change date fields in lists<br>";
$query = "SELECT ".LISTTABLEDESCRIPTION_TITLE_FIELD_NAME.", ".LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME." FROM ".LISTTABLEDESCRIPTION_TABLE_NAME;
#echo "query=".$query."<br>";
$query_result = $database->query($query);
if ($query_result != FALSE)
{
    $all_lists = array();
    while ($row = $database->fetch($query_result))
    {
        #echo "&nbsp;&nbsp;&nbsp;found list <strong>".$row[0]."</strong><br>";
        array_push($all_lists, array($row[0], $row[1]));
    }
}
else
    fatal("could not find any lists");

foreach($all_lists as $row)
{
    $list_name = $row[0];
    $table_name = ListTable::_convert_list_name_to_table_name($list_name);
    $definition = (array)$json->decode(html_entity_decode(html_entity_decode($row[1], ENT_QUOTES), ENT_QUOTES));
    $db_field_names = array_keys($definition);

    foreach ($db_field_names as $db_field_name)
    {
        $field_definition = $definition[$db_field_name];
        $field_type = $field_definition[0];
        $field_name = ListTable::_get_field_name($db_field_name);

        if($field_type == "LABEL_DEFINITION_DATE")
            $new_field_type = FIELD_TYPE_DEFINITION_DATETIME;
        $new_field_type = $field_type;

        $new_field_definition = array();
        array_push($new_field_definition, $new_field_type);
        array_push($new_field_definition, $field_definition[1]);
        $definition[$field_name] = $new_field_definition;

        # update auto created or auto update field
        if ($field_type == "LABEL_DEFINITION_DATE")
        {
            # update list table description
            echo "&nbsp;&nbsp;&nbsp;update field <strong>$field_name</strong> of list <strong>$list_name</strong><br>";
            $query = "ALTER TABLE $table_name MODIFY COLUMN $db_field_name ".DB_DATATYPE_DATETIME;
            echo $query."<br>\n";
            $result = $database->query($query);
            if ($result == FALSE)
                fatal("could not update list");
        }
    }
}
echo "lists have been updated<br>";

# succes
echo "<br><strong>update complete!</strong><br>";

?>
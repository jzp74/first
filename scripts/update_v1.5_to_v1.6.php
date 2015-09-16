<?php

/**
 * This is the script to upgrade from version 1.5 to version 1.6
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
$update_string = "firstthingsfirst update (v1.5 -> v1.6)";


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

    $last_field_name = "";
    $new_definition = array();
    foreach ($field_names as $field_name)
    {
        $field_definition = $definition_array[$field_name];
        $field_definition_str = htmlentities($json->encode($field_definition), ENT_QUOTES);
        $field_type = $field_definition[0];
        $field_options = $field_definition[1];
        #echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;found field <strong>".$field_name."</strong> of field type ".$field_type." with options ".$field_options."<br>\n";            
        
        $new_definition[$field_name] = $field_definition;
        $field_definition_str = htmlentities($json->encode($field_definition), ENT_QUOTES);
        #echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;after: $field_definition_str<br>";
        $last_field_name = $field_name;
    }
    
    # add attachments field to the definition of the list
    $new_definition['_user_defined_attachments'] = array('LABEL_DEFINITION_ATTACHMENTS', '', COLUMN_SHOW);
    $definition = htmlentities($json->encode($new_definition), ENT_QUOTES);
    
    $query = "UPDATE ".LISTTABLEDESCRIPTION_TABLE_NAME." SET ".LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME;
    $query .= "='$definition' WHERE ".DB_ID_FIELD_NAME."='$list_id'";
#    echo $query."<br>\n";
    $result = $database->query($query);
    if ($result == FALSE)
        fatal("could not update list definition");
    echo "&nbsp;&nbsp;&nbsp;updated list definition<br>\n";
        
    echo "&nbsp;&nbsp;&nbsp;updating list table<br>\n";
    $table_name = ListTable::_convert_list_name_to_table_name($list_name);

    $query = "ALTER TABLE $table_name ADD _user_defined_attachments ".DB_DATATYPE_INT." AFTER $last_field_name";
#    echo $query."<br>\n";
    $result = $database->query($query);
    if ($result == FALSE)
        fatal("could not update list table");
    
    echo "&nbsp;&nbsp;&nbsp;updated list table<br>\n";    
}

# succes
echo "<br><strong>update complete!</strong><br>";

?>
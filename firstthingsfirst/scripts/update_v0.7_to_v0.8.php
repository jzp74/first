<?php

/**
 * This is the script to upgrade from version 0.7 to version 0.8
 *
 */

require_once("../php/Class.Logging.php");

require_once("../globals.php");
require_once("../localsettings.php");

require_once("../php/external/JSON.php");

require_once("../php/Text.Labels.php");

require_once("../php/Class.Database.php");
require_once("../php/Class.DatabaseTable.php");
require_once("../php/Class.UserDatabaseTable.php");
require_once("../php/Class.User.php");
require_once("../php/Class.ListState.php");
require_once("../php/Class.ListTableDescription.php");
require_once("../php/Class.ListTable.php");
require_once("../php/Class.ListTableNote.php");

$logging = new Logging(LOGGING_OFF);
$database = new Database();

# update string
$update_string = "firstthingsfirst update (v0.7 -> v0.8)";


# several helper functions
function fatal ($str)
{
    global $update_string;
    
    echo "<br><strong><font color=\"red\">".$update_string." failed</font><br>";
    exit("&nbsp;&nbsp;&nbsp;-> ".$str."</strong><br>");
}


# opening message
echo "<strong>starting ".$update_string."</strong><br><br>\n";

$updated_lists = 0;
$list_ids_array = array();
$json = new Services_JSON();

echo "updating list descriptions table (".LISTTABLEDESCRIPTION_TABLE_NAME.")<br>";
$field_names = array();
$query = "SHOW COLUMNS FROM ".LISTTABLEDESCRIPTION_TABLE_NAME;
$result = $database->query($query);
if ($result != FALSE)
{
    while ($row = $database->fetch($result))
        array_push($field_names, $row[0]);
    if (in_array(LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME, $field_names))
    {
        # add column for creator
        $query = "ALTER TABLE ".LISTTABLEDESCRIPTION_TABLE_NAME." ADD COLUMN ";
        $query .= LISTTABLEDESCRIPTION_CREATOR_FIELD_NAME." ";
        $query .= $firstthingsfirst_field_descriptions["LABEL_DEFINITION_AUTO_CREATED"][FIELD_DESCRIPTION_FIELD_DB_DEFINITION];
        $query .= " AFTER ".LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME;
        # add column for modifier
        $query .= ", ADD COLUMN ".LISTTABLEDESCRIPTION_MODIFIER_FIELD_NAME." ";
        $query .= $firstthingsfirst_field_descriptions["LABEL_DEFINITION_AUTO_MODIFIED"][FIELD_DESCRIPTION_FIELD_DB_DEFINITION];
        $query .= " AFTER ".LISTTABLEDESCRIPTION_CREATOR_FIELD_NAME;
#        echo $query."<br>\n";
        $result = $database->query($query);
        if ($result == FALSE)
            fatal("could not update list descriptions table (".LISTTABLEDESCRIPTION_TABLE_NAME.")");
    }
    else
        fatal("could not load list descriptions table");
}
else
    fatal("could not find any list descriptions");
        


echo "updating lists<br>";

$query = "SELECT _id, _title, _description, _definition FROM ".LISTTABLEDESCRIPTION_TABLE_NAME;
$query_result = $database->query($query);
if ($query_result != FALSE)
{
    while ($row = $database->fetch($query_result))
    {
        $list_name = $row[1];
        $table_name = LISTTABLE_TABLE_NAME_PREFIX.strtolower(str_replace(" ", "_", $row[1]));
        $definition = (array)$json->decode(html_entity_decode(html_entity_decode($row[3], ENT_QUOTES), ENT_QUOTES));
        
        echo "updating list: <strong>".$list_name."</strong> (".$table_name.")<br>\n";
        
        $field_names = array_keys($definition);

        $previous_field_name = "";
        foreach ($field_names as $field_name)
        {
            $field_definition = $definition[$field_name];
            $field_type = $field_definition[0];
            
            # remove key from definition
            $new_field_definition = array();
            array_push($new_field_definition, $field_definition[0]);
            array_push($new_field_definition, $field_definition[2]);
            $definition[$field_name] = $new_field_definition;

            # update auto created or auto update field
            if ($field_type == "LABEL_DEFINITION_AUTO_CREATED" || $field_type == "LABEL_DEFINITION_AUTO_CREATED")
            {
                # update list table description
                echo "&nbsp;&nbsp;&nbsp;updating list table<br>\n";
                $query = "ALTER TABLE ".$table_name." ADD COLUMN ".$field_name." ";
                $query .= $firstthingsfirst_field_descriptions[$field_type][FIELD_DESCRIPTION_FIELD_DB_DEFINITION];
                $query .= " AFTER ".$previous_field_name;
#                echo $query."<br>\n";
                $result = $database->query($query);
                if ($result == FALSE)
                    fatal("could not update list table");
            }
            
            $previous_field_name = $field_name;
        }
        
        # update complete description
        echo "&nbsp;&nbsp;&nbsp;updating list description<br>\n";
        $new_definition = htmlentities($json->encode($definition), ENT_QUOTES);
        $query = "UPDATE ".LISTTABLEDESCRIPTION_TABLE_NAME." SET ";
        $query .= "_definition='".$new_definition."' WHERE _id='".$row[0]."'";
#        echo $query."<br>\n";
        $result = $database->query($query);
        if ($result == FALSE)
            fatal("could not update list description");
    }
}
else
    fatal("could not find any lists");
    
# succes
echo "<br><strong>update complete!</strong><br>";

?>
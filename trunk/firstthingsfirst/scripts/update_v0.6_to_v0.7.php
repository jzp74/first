<?php

/**
 * This is the script to upgrade from version 0.6 to version 0.7
 *
 * field "_archived" is removed from all lists
 * _user table is updated
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
$update_string = "firstthingsfirst update (v0.6 -> v0.7)";


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

echo "updating lists<br>";

$query = "SELECT _id, _title, _description, _definition FROM ".LISTTABLEDESCRIPTION_TABLE_NAME;
$query_result = $database->query($query);
if ($query_result != FALSE)
{
    while ($row = $database->fetch($query_result))
    {
        $list_name = $row[1];
        $table_name = LISTTABLE_TABLE_NAME_PREFIX.strtolower(str_replace(" ", "_", $row[1]));
        $description = html_entity_decode(html_entity_decode($row[2], ENT_QUOTES), ENT_QUOTES);
        $definition = (array)$json->decode(html_entity_decode(html_entity_decode($row[3], ENT_QUOTES), ENT_QUOTES));
        
        echo "updating list: <strong>".$list_name."</strong> (".$table_name.")<br>\n";
        
        $field_names = array_keys($definition);
        echo "&nbsp;&nbsp;&nbsp;updating list description<br>\n";

        $previous_field_name = "";
        foreach ($field_names as $field_name)
        {
            $field_definition = $definition[$field_name];
            $field_type = $field_definition[0];
#            echo "&nbsp;&nbsp;&nbsp;found field <strong>".$field_name."</strong> of field type ".$field_type."<br>\n";            
            
            # update auto update field
            if ($field_type == "LABEL_DEFINITION_AUTO_DATE")
            {
                $new_field_definition = array();
                array_push($new_field_definition, "LABEL_DEFINITION_AUTO_CREATED");
                array_push($new_field_definition, $field_definition[1]);
                array_push($new_field_definition, NAME_DATE_OPTION_DATE);
                $definition[$field_name] = $new_field_definition;

                # update list table description
                echo "&nbsp;&nbsp;&nbsp;updating list table<br>\n";
                $query = "ALTER TABLE ".$table_name." DROP COLUMN ".$field_name;
#                echo $query."<br>\n";
                $result = $database->query($query);
                if ($result == FALSE)
                    fatal("could not update list table description");
            }
            $previous_field_name = $field_name;
        }
        
        # update complete description
        $new_description = htmlentities($description, ENT_QUOTES);
        $new_definition = htmlentities($json->encode($definition), ENT_QUOTES);
        $query = "UPDATE ".LISTTABLEDESCRIPTION_TABLE_NAME." SET _description='".$new_description."', ";
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
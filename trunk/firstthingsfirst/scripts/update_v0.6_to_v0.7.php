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
echo "<strong>starting ".$update_string."</strong><br><br>";

echo "updating list: <strong>list descriptions table</strong> (".LISTTABLEDESCRIPTION_TABLE_NAME.")<br>";
    
# insert field LISTTABLEDESCRIPTION_CREATOR_FIELD_NAME
$query = "ALTER TABLE ".LISTTABLEDESCRIPTION_TABLE_NAME." ADD COLUMN ".LISTTABLEDESCRIPTION_CREATOR_FIELD_NAME." ".DB_DATATYPE_BOOL." AFTER ".LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME;
# insert field LISTTABLEDESCRIPTION_MODIFIER_FIELD_NAME
$query .= ", ADD COLUMN ".LISTTABLEDESCRIPTION_MODIFIER_FIELD_NAME." ".DB_DATATYPE_BOOL." AFTER ".LISTTABLEDESCRIPTION_CREATOR_FIELD_NAME;
$result = $database->query($query);
if ($result != FALSE)
    echo "&nbsp;&nbsp;&nbsp;updated list<br>";
else
    fatal("could not update this list");

# succes
echo "<strong>update complete!</strong><br>";

?>

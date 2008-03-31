<?php

/**
 * This is the script to upgrade from version 0.5 to version 0.6
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
$update_string = "firstthingsfirst update (v0.5 -> v0.6)";


# several helper functions
function fatal ($str)
{
    global $update_string;
    
    echo "<br><strong><font color=\"red\">".$update_string." failed</font><br>";
    exit("&nbsp;&nbsp;&nbsp;-> ".$str."</strong><br>");
}


# opening message
echo "<strong>starting ".$update_string."</strong><br><br>";

#$query = "SHOW TABLES FROM ".$firstthingsfirst_db_schema." LIKE '".LISTTABLE_TABLE_NAME_PREFIX."%'";
$list_ids_array = array();
$query = "SELECT _id, _title FROM ".LISTTABLEDESCRIPTION_TABLE_NAME;
$result = $database->query($query);
if ($result != FALSE)
{
    while ($row = $database->fetch($result))
        $list_ids_array [$row[0]] = array($row[1], LISTTABLE_TABLE_NAME_PREFIX.strtolower(str_replace(" ", "_", $row[1])));
}
else
    fatal("could not find any lists");

echo "updating lists<br>";

$updated_lists = 0;
$list_ids = array_keys($list_ids_array);
foreach ($list_ids as $list_id)
{
    $table_name = $list_ids_array[$list_id][1];
    $list_name = $list_ids_array[$list_id][0];
    echo "updating list: <strong>".$list_name."</strong> (".$table_name.")<br>";
    
    # test if list has the "_archived" field
    $field_names = array();
    $query = "SHOW COLUMNS FROM ".$table_name;
    $result = $database->query($query);
    if ($result != FALSE)
    {
        while ($row = $database->fetch($result))
            array_push($field_names, $row[0]);
        if (in_array("_archived", $field_names))
        {
            # remove the '_archived' field from table
            $query = "ALTER TABLE ".$table_name." DROP COLUMN _archived";
            $result = $database->query($query);
            if ($result != FALSE)
            {
                $updated_lists += 1;
                echo "&nbsp;&nbsp;&nbsp;updated list<br>";
            }
            else
                fatal("could not update this list");
        }
    }
    else
        fatal("could not find any field in this list");
}

# succes
echo "updated ".$updated_lists." lists<br><br>";

# update _user table
echo "updating users table (".USER_TABLE_NAME.")<br>";
$field_names = array();
$query = "SHOW COLUMNS FROM ".USER_TABLE_NAME;
$result = $database->query($query);
if ($result != FALSE)
{
    while ($row = $database->fetch($result))
        array_push($field_names, $row[0]);
    if (in_array("_ts_last_login", $field_names))
    {
        # change the '_ts_last_login' field (rename)
        $query = "ALTER TABLE ".USER_TABLE_NAME." CHANGE COLUMN _ts_last_login ".DB_TS_MODIFIED_FIELD_NAME." ".DB_DATATYPE_DATETIME; 
        # insert field DB_CREATOR_FIELD_NAME
        $query .= ", ADD COLUMN ".DB_CREATOR_FIELD_NAME." ".DB_DATATYPE_USERNAME." AFTER ".USER_TIMES_LOGIN_FIELD_NAME;
        # move the DB_TS_CREATED_FIELD_NAME field
        $query .= ", MODIFY COLUMN ".DB_TS_CREATED_FIELD_NAME." ".DB_DATATYPE_DATETIME." AFTER ".DB_CREATOR_FIELD_NAME;
        # insert field DB_MODIFIER_FIELD_NAME
        $query .= ", ADD COLUMN ".DB_MODIFIER_FIELD_NAME." ".DB_DATATYPE_USERNAME." AFTER ".DB_TS_CREATED_FIELD_NAME;
        $result = $database->query($query);
        if ($result == FALSE)
            fatal("could not update users table (".USER_TABLE_NAME.")");
        echo "updating all users<br>";
        $rows = array();
        $query = "SELECT * FROM ".USER_TABLE_NAME;
        $result = $database->query($query);
        if ($result != FALSE)
        {
            while ($row = $database->fetch($result))
                array_push($rows, $row);
        }
        else
            fatal("could not select any user");
        foreach ($rows as $row)
        {
            $query = "UPDATE ".USER_TABLE_NAME." SET ".DB_CREATOR_FIELD_NAME."='admin', ";
            $query .= DB_MODIFIER_FIELD_NAME."='".$row[USER_NAME_FIELD_NAME]."' WHERE ";
            $query .= DB_ID_FIELD_NAME."='".$row[DB_ID_FIELD_NAME]."'";
            $result = $database->query($query);
            if ($result == FALSE)
                fatal("could not update user: ".$row[USER_NAME_FIELD_NAME]);
        }
    }
}
else
    fatal("could not find ".USER_TABLE_NAME." table");
echo "updated all users<br><br>";


# update notes table
echo "creating seperate notes tables<br>";
$list_table_ids_array = array();
$table_name = $firstthingsfirst_db_table_prefix."listtableitemnote";
$query = "SELECT DISTINCT(_list_table_description_id) FROM ".$table_name;
$result = $database->query($query);
if ($result == FALSE)
    fatal("could not select lists with notes from database");

while ($row = $database->fetch($result))
    array_push($list_table_ids_array, $row[0]);

foreach ($list_table_ids_array as $list_table_id)
{
    $list_title = $list_ids_array[$list_table_id][0];
    $list_table_table_name = $list_ids_array[$list_table_id][1];
    echo "creating notes table for list: <strong>".$list_title."</strong><br>";
    $list_table_note_table_name = LISTTABLENOTE_TABLE_NAME.strtolower(str_replace(" ", "_", $list_title));
    
    $query = "CREATE TABLE ".$list_table_note_table_name." (_id INT NOT NULL AUTO_INCREMENT, _record_id INT NOT NULL, ";
    $query .= "_field_name VARCHAR(100) NOT NULL, _note MEDIUMTEXT NOT NULL, _creator VARCHAR(20) NOT NULL, ";
    $query .= "_ts_created DATETIME NOT NULL, _modifier VARCHAR(20) NOT NULL, _ts_modified DATETIME NOT NULL, ";
    $query .= "PRIMARY KEY (_id), FOREIGN KEY (_record_id) REFERENCES ".$list_table_table_name."(_id) ON DELETE CASCADE)";
    $result = $database->query($query);
    if ($result == FALSE)
        fatal ("could not create notes table for list: ".$list_title."<br>");
    
    $query = "SELECT COUNT(_id) FROM ".$table_name." WHERE _list_table_description_id=".$list_table_id;
    $result = $database->query($query);
    if ($result == FALSE)
        fatal("could not count number of notes for list: ".$list_title."<br>");

    $row = $database->fetch($result);
    $num_of_notes = (int)$row[0];
    echo "&nbsp;&nbsp;&nbsp;notes table contains ".$num_of_notes." notes<br>";

    for ($note_number = 0; $note_number < $num_of_notes; $note_number ++)
    {
        $query = "SELECT * from ".$table_name." WHERE _list_table_description_id=".$list_table_id." LIMIT 1";
        $result = $database->query($query);
        if ($result == FALSE)
            fatal ("could not select notes from database");
            
        $row = $database->fetch($result);
        $note_id = $row[0];
        $item_id = $row[2];
        $field_name = $row[3];
        $note = $row[4];
        $creator = $row[5];
        $created = $row[6];
        $modifier = $row[7];
        $modified = $row[8];
                            
        $query = "INSERT INTO ".$list_table_note_table_name." VALUES ";                
        $query .= "(0, ".$item_id.", '".$field_name."', '".$note."', '".$creator."', '".$created."', '".$modifier."', '".$modified."')";
        $result = $database->query($query);
        if ($result == FALSE)
            fatal("could not insert note in table: ".$list_table_note_table_name."<br>");
             
        $query = "DELETE FROM ".$table_name." WHERE _id=".$note_id;
        $result = $database->query($query);
        if ($result == FALSE)
            fatal("could not delete note from table: ".$table_name."<br>");
    }
    
    # test if number of notes in new table is the same
    $query = "SELECT COUNT(_id) FROM ".$list_table_note_table_name;
    $result = $database->query($query);
    if ($result == FALSE)
        fatal("could not count the new number of notes<br>");
    $row = $database->fetch($result);
    $new_num_of_notes = (int)$row[0];
    if ($new_num_of_notes != $num_of_notes)
        fatal("new notes table contains a different number of notes (new=".$new_num_of_notes.", org=".$num_of_notes.")<br>");
    
    echo "&nbsp;&nbsp;&nbsp;new notes table contains ".$new_num_of_notes." notes<br>";
}

echo "delete old notes table: ".$table_name."<br>";
$query = "DROP TABLE ".$table_name;
$result = $database->query($query);
if ($result == FALSE)
    fatal("could not delete old notes table<br>");

echo "converted all notes<br><br>";

# succes
echo "<strong>update complete!</strong><br>";

?>

<?php

require_once("php/Class.Logging.php");

require_once("globals.php");
require_once("localsettings.php");

require_once("php/external/JSON.php");
require_once("xajax/xajax.inc.php");

require_once("php/Text.Buttons.php");
require_once("php/Text.Errors.php");
require_once("php/Text.Labels.php");

require_once("php/Class.Result.php");
require_once("php/Class.Database.php");
require_once("php/Class.ListState.php");
require_once("php/Class.DatabaseTable.php");
require_once("php/Class.UserDatabaseTable.php");
require_once("php/Class.User.php");
require_once("php/Class.ListTableDescription.php");
require_once("php/Class.ListTable.php");
require_once("php/Class.ListTableNote.php");


# create global objects
$logging = new Logging($firstthingsfirst_loglevel, $firstthingsfirst_logfile);
$database = new Database();
$list_state = new ListState();
$user = new User();
$result = new Result();
$list_table_description = new ListTableDescription();


# cleanup tables and entries created during this test
$cleanup = FALSE;


# login as admin
$user->logout();
$user->login("admin", $firstthingsfirst_admin_passwd);


# definitions
$_title = "testing 1 2 3";
$_description = "This is a regression test";
$id_field = ListTable::_get_db_field_name("id");
$name_field = ListTable::_get_db_field_name("name");
$description_field = ListTable::_get_db_field_name("description");
$notes1_field = ListTable::_get_db_field_name("first notes");
$status_field = ListTable::_get_db_field_name("status");
$notes2_field = ListTable::_get_db_field_name("second notes");
$_definition = array (
    $id_field => array("LABEL_DEFINITION_AUTO_NUMBER", 1, ""),
    $name_field => array("LABEL_DEFINITION_TEXT_LINE", 0, ""),
    $description_field => array("LABEL_DEFINITION_TEXT_LINE", 0, ""),
    $notes1_field => array("LABEL_DEFINITION_NOTES_FIELD", 0, ""),
    $status_field => array("LABEL_DEFINITION_SELECTION", 0, "open|close"),
    $notes2_field => array("LABEL_DEFINITION_NOTES_FIELD", 0, "")
);
$_name_value_arrays = array (
    array  ($name_field => "name 1", 
            $description_field => "this is description numero 1", 
            $notes1_field => array(array(0, "some note"), array(0, "the second note for this field")), 
            $status_field => "open", 
            $notes2_field => array(array(0, ""))),
    array  ($name_field => "name 2", 
            $description_field => "this is description numero 2", 
            $notes1_field => array(array(0, "")), 
            $status_field => "open", 
            $notes2_field => array(array(0, ""))),
    array  ($name_field => "name 3", 
            $description_field => "this is description numero 3", 
            $notes1_field => array(array(0, "")),
            $status_field => "open", 
            $notes2_field => array(array(0, "other note"))),
    array  ($name_field => "name 4", 
            $description_field => "this is description numero 4", 
            $notes1_field => array(array(0, "")), 
            $status_field => "open", 
            $notes2_field => array(array(0, ""))),
    array  ($name_field => "name 5", 
            $description_field => "this is description numero 5", 
            $notes1_field => array(array(0, "")), 
            $status_field => "open", 
            $notes2_field => array(array(0, ""))),
    array  ($name_field => "name 6", 
            $description_field => "this is description numero 6", 
            $notes1_field => array(array(0, "")), 
            $status_field => "open", 
            $notes2_field => array(array(0, ""))),
    array  ($name_field => "name 7", 
            $description_field => "this is description numero 7", 
            $notes1_field => array(array(0, "")), 
            $status_field => "open", 
            $notes2_field => array(array(0, ""))),
    array  ($name_field => "name 8", 
            $description_field => "this is description numero 8", 
            $notes1_field => array(array(0, "next note")), 
            $status_field => "open", 
            $notes2_field => array(array(0, ""))),
    array  ($name_field => "name 9", 
            $description_field => "this is description numero 9", 
            $notes1_field => array(array(0, "")), 
            $status_field => "open", 
            $notes2_field => array(array(0, ""))),
    array  ($name_field => "name 10", 
            $description_field => "this is description numero 10", 
            $notes1_field => array(array(0, "")), 
            $status_field => "open", 
            $notes2_field => array(array(0, ""))),
    array  ($name_field => "name 11", 
            $description_field => "this is description numero 11", 
            $notes1_field => array(array(0, "")), 
            $status_field => "open", 
            $notes2_field => array(array(0, ""))),
    array  ($name_field => "name 12", 
            $description_field => "this is description numero 12", 
            $notes1_field => array(array(0, "")), 
            $status_field => "open", 
            $notes2_field => array(array(0, "some other"))),
    array  ($name_field => "name 13", 
            $description_field => "this is description numero 13", 
            $notes1_field => array(array(0, "")), 
            $status_field => "open", 
            $notes2_field => array(array(0, ""))),
    array  ($name_field => "name 14", 
            $description_field => "this is description numero 14", 
            $notes1_field => array(array(0, "")), 
            $status_field => "open", 
            $notes2_field => array(array(0, ""))),
    array  ($name_field => "name 15", 
            $description_field => "this is description numero 15", 
            $notes1_field => array(array(0, "")), 
            $status_field => "open", 
            $notes2_field => array(array(0, ""))),
    array  ($name_field => "name 16", 
            $description_field => "this is description numero 16", 
            $notes1_field => array(array(0, "nothing much to say")), 
            $status_field => "open", 
            $notes2_field => array(array(0, "last note"))),
    array  ($name_field => "name 17", 
            $description_field => "this is description numero 17", 
            $notes1_field => array(array(0, "")), 
            $status_field => "open", 
            $notes2_field => array(array(0, ""))),
    array  ($name_field => "name 18", 
            $description_field => "this is description numero 18", 
            $notes1_field => array(array(0, "")), 
            $status_field => "open", 
            $notes2_field => array(array(0, ""))),
    array  ($name_field => "name 19", 
            $description_field => "this is description numero 19", 
            $notes1_field => array(array(0, "")), 
            $status_field => "open", 
            $notes2_field => array(array(0, ""))),
    array  ($name_field => "name 20", 
            $description_field => "this is description numero 20", 
            $notes1_field => array(array(0, "")), 
            $status_field => "open", 
            $notes2_field => array(array(0, "")))
);


# functions needed in this test
function dump ($line)
{
    echo $line;
}

function dump_test ($line)
{
    dump ("[TEST] ".$line."... ");
}

function dump_line ($line)
{
    dump($line."\n<br>");
}

function dump_greenline ($line)
{
    dump_line("<font color=\"green\">".$line."</font>");
}

function dump_redline ($line)
{
    print_line("<font color=\"red\">".$line."</font>");
}

function fatal ()
{
    exit("<font color=\"red\">nok</font>\n");
}


# start of test
dump("<h1>Regression Test</h1>");

$name_values_array = array();
$name_values_array[LISTTABLEDESCRIPTION_TITLE_FIELD_NAME] = $_title;
$name_values_array[LISTTABLEDESCRIPTION_DESCRIPTION_FIELD_NAME] = $_description;
$name_values_array[LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME] = $_definition;

dump_line("checking if list: ".$_title." exists");
if ($list_table_description->select_record($_title))
{
    $list_table = new ListTable($_title);
    if ($list_table->drop())
        dump_line("&nbsp;&nbsp;&nbsp;<em>removed list: ".$_title." from database</em>");
}
dump_line("");

dump_test("create list description: ".$_title);
if ($list_table_description->insert($name_values_array))
    dump_greenline("ok");
else
    fatal();

$list_table = new ListTable($_title);
$list_table_note = new ListTableNote ($_title);

dump_test("create list: ".$_title);
if ($list_table->create())
    dump_greenline("ok");
else
    fatal();

dump_line("&nbsp;&nbsp;&nbsp;<em>reset list</em>");
dump_test("reading list: ".$_title);
if ($list_table_description->select_record($_title))
    dump_greenline("ok");
else
    fatal();
dump_line("");

dump_test("adding 20 entries to database");
foreach ($_name_value_arrays as $name_value_array)
    if (!$list_table->insert($name_value_array))
        fatal();
dump_greenline("ok");

dump_test("reading entry 1 from database");
$results = $list_table->select_record("_id=1");
if (count($results) > 0)
    dump_greenline("ok");
else
    fatal();
$user_field_names = $list_table->get_user_field_names();
$str = "";
foreach($user_field_names as $user_field_name)
{
    $db_field_name = $list_table->_get_db_field_name($user_field_name);
    $str .= $user_field_name."=".$results[$db_field_name].", ";
}
dump_line("&nbsp;&nbsp;&nbsp;<em>".$str."</em>");

dump_test("change second note of first notes field of entry 1");
$note_field = $results[$notes1_field][1];
$note_array = array($note_field["_id"], $note_field["_note"]." changed");
if ($list_table_note->update($note_field["_id"], $note_field["_note"]." changed"))
    dump_greenline("ok");
else
    fatal();

dump_test("delete entry 3 from database");
if ($list_table->delete("_id=3"))
    dump_greenline("ok");
else
    fatal();

# cleanup only when cleanup is TRUE
if ($cleanup)
{
    dump_line("<br><strong>cleanup database entries from this test</strong>");
    dump_test("removing list: ".$_title." from database");
    if ($list_table->drop())
        dump_greenline("ok");
    else
        fatal();
}
else
{
    dump_line("<br><strong>database entries from this test have not been removed</strong>");
}

?>
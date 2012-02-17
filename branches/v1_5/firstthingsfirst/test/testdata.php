<?php

/**
 * set value below to FALSE if you do not want to delete test user and test list
 */
define("REGRESSION_TEST_CLEANUP", TRUE);

/**
 * definition of all test definitions
 */
define("REGRESSION_TEST_USER_NAME", "tester");
define("REGRESSION_TEST_USER_PW", "sometestpassword");
define("REGRESSION_TEST_USER_NEW_PW", "someotherpassword");
define("REGRESSION_TEST_LIST_TITLE", "regression test");
define("REGRESSION_TEST_LIST_DESCRIPTION", "This is a list that has been created only for testing purposes");
define("REGRESSION_TEST_LIST_NEW_DESCRIPTION", "This is a list that has been created only for testing purposes [UPDATED BY REGRESSION TEST]");
define("REGRESSION_TEST_LIST_ID_FIELD", ListTable::_get_db_field_name("id"));
define("REGRESSION_TEST_LIST_NAME_FIELD", ListTable::_get_db_field_name("name"));
define("REGRESSION_TEST_LIST_DESCRIPTION_FIELD", ListTable::_get_db_field_name("description"));
define("REGRESSION_TEST_LIST_NOTES1_FIELD", ListTable::_get_db_field_name("first notes"));
define("REGRESSION_TEST_LIST_STATUS_FIELD", ListTable::_get_db_field_name("status"));
define("REGRESSION_TEST_LIST_NOTES2_FIELD", ListTable::_get_db_field_name("second notes"));
define("REGRESSION_TEST_LIST_CREATED_FIELD", ListTable::_get_db_field_name("created"));


/**
 * definition of all test functions (as needed by Html.RegressionTest.php)
 */
$regression_test_functions = array(
    array("create and modify user", "", "", ""),
    array("login as 'administrator'", "login_user_admin", "'administrator' logged in", "could not login administrator"),
    array("create a new test user for this test", "create_test_user", "test user created", "could not create new test user"),
    array("login as test user", "login_test_user", "test user logged in", "could not login test user"),
    array("login as 'administrator'", "login_user_admin", "'administrator' logged in", "could not login administrator"),
    array("change password of test user", "update_test_user", "password of test user updated", "could not change password of test user"),
    array("login as test user", "login_test_user_new_pw", "test user logged in", "could not login test user"),
    array("create and modify test list", "", "", ""),
    array("create a new test list", "create_test_list", "test list created", "could not create new test list"),
    array("open test list", "open_test_list", "test list opened", "could not open test list"),
    array("add 20 list items to test list", "add_list_items", "added 20 list items to test list", "could not add list items to test list"),
    array("update description of test list", "update_test_list", "updated description of test list", "could not update test list"),
    array("read 9th list item from test list", "read_list_item", "read 9th list item test list", "could not read list item from test list"),
    array("update the second note of the first notes column of the 10th list item from test list", "update_list_item", "updated 10th list item test list", "could not update list item from test list"),
    array("delete 20th list item from test list", "delete_list_item", "deleted 20th list item from test list", "could not delete list item from test list"),
    array("cleanup", "", "", ""),
    array("delete test list", "delete_test_list", "deleted test list", "could not delete test list"),
    array("delete test user", "delete_test_user", "deleted test user", "could not delete test user"),
);

/**
 * definition of new user 'regressiontester'
 */
$regression_tester_user_array = array(
    USER_NAME_FIELD_NAME => REGRESSION_TEST_USER_NAME,
    USER_PW_FIELD_NAME => REGRESSION_TEST_USER_PW,
    USER_LANG_FIELD_NAME => LANG_EN,
    USER_DATE_FORMAT_FIELD_NAME => DATE_FORMAT_EU,
    USER_DECIMAL_MARK_FIELD_NAME => DECIMAL_MARK_POINT,
    USER_LINES_PER_PAGE_FIELD_NAME => 12,
    USER_THEME_FIELD_NAME => THEME_BLUE,
    USER_CAN_CREATE_LIST_FIELD_NAME => 1,
    USER_CAN_CREATE_USER_FIELD_NAME => 0,
    USER_IS_ADMIN_FIELD_NAME => 0,
    USER_TIMES_LOGIN_FIELD_NAME => 0
);

/**
 * definition of new list 'regression test list'
 */
$regression_test_list_definition = array (
    REGRESSION_TEST_LIST_ID_FIELD => array(FIELD_TYPE_DEFINITION_AUTO_NUMBER, ID_COLUMN_SHOW),
    REGRESSION_TEST_LIST_NAME_FIELD => array(FIELD_TYPE_DEFINITION_TEXT_LINE, ""),
    REGRESSION_TEST_LIST_DESCRIPTION_FIELD => array(FIELD_TYPE_DEFINITION_TEXT_LINE, ""),
    REGRESSION_TEST_LIST_NOTES1_FIELD => array(FIELD_TYPE_DEFINITION_NOTES_FIELD, ""),
    REGRESSION_TEST_LIST_STATUS_FIELD => array(FIELD_TYPE_DEFINITION_SELECTION, "open|busy|pending|close"),
    REGRESSION_TEST_LIST_NOTES2_FIELD => array(FIELD_TYPE_DEFINITION_NOTES_FIELD, "")
);

$regression_test_list_description_definition = array(
    LISTTABLEDESCRIPTION_TITLE_FIELD_NAME => REGRESSION_TEST_LIST_TITLE,
    LISTTABLEDESCRIPTION_DESCRIPTION_FIELD_NAME => REGRESSION_TEST_LIST_DESCRIPTION,
    LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME => $regression_test_list_definition,
    LISTTABLEDESCRIPTION_ACTIVE_RECORDS_FIELD_NAME => 0,
    LISTTABLEDESCRIPTION_ARCHIVED_RECORDS_FIELD_NAME => 0,
    LISTTABLEDESCRIPTION_CREATOR_FIELD_NAME => 0,
    LISTTABLEDESCRIPTION_MODIFIER_FIELD_NAME => 0
);

$regression_test_new_list_definition = array (
    0 => array(REGRESSION_TEST_LIST_ID_FIELD, FIELD_TYPE_DEFINITION_AUTO_NUMBER, ID_COLUMN_NO_SHOW),
    6 => array(REGRESSION_TEST_LIST_CREATED_FIELD, FIELD_TYPE_DEFINITION_AUTO_CREATED, NAME_DATE_OPTION_DATE_NAME),
    1 => array(REGRESSION_TEST_LIST_NAME_FIELD, FIELD_TYPE_DEFINITION_TEXT_LINE, ""),
    3 => array(REGRESSION_TEST_LIST_NOTES1_FIELD, FIELD_TYPE_DEFINITION_NOTES_FIELD, ""),
    2 => array(REGRESSION_TEST_LIST_DESCRIPTION_FIELD, FIELD_TYPE_DEFINITION_TEXT_LINE, ""),
    4 => array(REGRESSION_TEST_LIST_STATUS_FIELD, FIELD_TYPE_DEFINITION_SELECTION, "open|busy|pending|close"),
    5 => array(REGRESSION_TEST_LIST_NOTES2_FIELD, FIELD_TYPE_DEFINITION_NOTES_FIELD, "")
);

/**
 * definition of list items of new list
 */
$regression_test_list_items = array (
    array  (REGRESSION_TEST_LIST_NAME_FIELD => "notes test 1",
            REGRESSION_TEST_LIST_DESCRIPTION_FIELD => "this is a test with zero notes",
            REGRESSION_TEST_LIST_NOTES1_FIELD => array(array(0, "")),
            REGRESSION_TEST_LIST_STATUS_FIELD => "open",
            REGRESSION_TEST_LIST_NOTES2_FIELD => array(array(0, ""))),
    array  (REGRESSION_TEST_LIST_NAME_FIELD => "notes test 2",
            REGRESSION_TEST_LIST_DESCRIPTION_FIELD => "this is a test with one note",
            REGRESSION_TEST_LIST_NOTES1_FIELD => array(array(0, "the very first note for this column")),
            REGRESSION_TEST_LIST_STATUS_FIELD => "open",
            REGRESSION_TEST_LIST_NOTES2_FIELD => array(array(0, ""))),
    array  (REGRESSION_TEST_LIST_NAME_FIELD => "notes test 3",
            REGRESSION_TEST_LIST_DESCRIPTION_FIELD => "this is a test with one note",
            REGRESSION_TEST_LIST_NOTES1_FIELD => array(array(0, "")),
            REGRESSION_TEST_LIST_STATUS_FIELD => "open",
            REGRESSION_TEST_LIST_NOTES2_FIELD => array(array(0, "the very first note for this column which is the second note for this list"))),
    array  (REGRESSION_TEST_LIST_NAME_FIELD => "notes test 4",
            REGRESSION_TEST_LIST_DESCRIPTION_FIELD => "this is a test with two notes",
            REGRESSION_TEST_LIST_NOTES1_FIELD => array(array(0, "now let's try one note in this column")),
            REGRESSION_TEST_LIST_STATUS_FIELD => "open",
            REGRESSION_TEST_LIST_NOTES2_FIELD => array(array(0, "and one note in this column"))),
    array  (REGRESSION_TEST_LIST_NAME_FIELD => "notes test 5",
            REGRESSION_TEST_LIST_DESCRIPTION_FIELD => "this is a test with three notes",
            REGRESSION_TEST_LIST_NOTES1_FIELD => array(array(0, "maybe try two notes in this column"), array(0, "this being the second note")),
            REGRESSION_TEST_LIST_STATUS_FIELD => "open",
            REGRESSION_TEST_LIST_NOTES2_FIELD => array(array(0, "only one note here"))),
    array  (REGRESSION_TEST_LIST_NAME_FIELD => "notes test 6",
            REGRESSION_TEST_LIST_DESCRIPTION_FIELD => "this is a test with three notes",
            REGRESSION_TEST_LIST_NOTES1_FIELD => array(array(0, "we can also try one note in this column")),
            REGRESSION_TEST_LIST_STATUS_FIELD => "open",
            REGRESSION_TEST_LIST_NOTES2_FIELD => array(array(0, "and see what happens if we add two notes in this column"), array(0, "this is the second note as you may have guessed"))),
    array  (REGRESSION_TEST_LIST_NAME_FIELD => "notes test 7",
            REGRESSION_TEST_LIST_DESCRIPTION_FIELD => "this is a test with four notes",
            REGRESSION_TEST_LIST_NOTES1_FIELD => array(array(0, "two notes here"), array(0, "as I said: two notes")),
            REGRESSION_TEST_LIST_STATUS_FIELD => "open",
            REGRESSION_TEST_LIST_NOTES2_FIELD => array(array(0, "and also two notes here"), array(0, "look at me! I'm the second note"))),
    array  (REGRESSION_TEST_LIST_NAME_FIELD => "notes test 8",
            REGRESSION_TEST_LIST_DESCRIPTION_FIELD => "this is a test with four notes",
            REGRESSION_TEST_LIST_NOTES1_FIELD => array(array(0, "now we're going to add three notes in this column"), array(0, "this is the second note"), array(0, "and this is the final and third note")),
            REGRESSION_TEST_LIST_STATUS_FIELD => "open",
            REGRESSION_TEST_LIST_NOTES2_FIELD => array(array(0, "we'll only add one note here"))),
    array  (REGRESSION_TEST_LIST_NAME_FIELD => "notes test 9",
            REGRESSION_TEST_LIST_DESCRIPTION_FIELD => "this is a test with four notes",
            REGRESSION_TEST_LIST_NOTES1_FIELD => array(array(0, "now we'll add only one note in this column")),
            REGRESSION_TEST_LIST_STATUS_FIELD => "open",
            REGRESSION_TEST_LIST_NOTES2_FIELD => array(array(0, "we'll add three notes to this column"), array(0, "let's see what happens here"), array(0, "this is the third note"))),
    array  (REGRESSION_TEST_LIST_NAME_FIELD => "notes test 10",
            REGRESSION_TEST_LIST_DESCRIPTION_FIELD => "this is a test with six notes",
            REGRESSION_TEST_LIST_NOTES1_FIELD => array(array(0, "for this final test"), array(0, "we'll add three notes"), array(0, "in this column")),
            REGRESSION_TEST_LIST_STATUS_FIELD => "open",
            REGRESSION_TEST_LIST_NOTES2_FIELD => array(array(0, "and we'll add three notes"), array(0, "in this column"), array(0, "just to see what happens"))),
    array  (REGRESSION_TEST_LIST_NAME_FIELD => "general test 1",
            REGRESSION_TEST_LIST_DESCRIPTION_FIELD => "this is the test with the humpty dumpty text (item 1)",
            REGRESSION_TEST_LIST_NOTES1_FIELD => array(array(0, "")),
            REGRESSION_TEST_LIST_STATUS_FIELD => "open",
            REGRESSION_TEST_LIST_NOTES2_FIELD => array(array(0, ""))),
    array  (REGRESSION_TEST_LIST_NAME_FIELD => "general test 2",
            REGRESSION_TEST_LIST_DESCRIPTION_FIELD => "this is the test with the humpty dumpty text (item 2)",
            REGRESSION_TEST_LIST_NOTES1_FIELD => array(array(0, "Humpty Dumpty")),
            REGRESSION_TEST_LIST_STATUS_FIELD => "open",
            REGRESSION_TEST_LIST_NOTES2_FIELD => array(array(0, "sat on a wall"))),
    array  (REGRESSION_TEST_LIST_NAME_FIELD => "general test 3",
            REGRESSION_TEST_LIST_DESCRIPTION_FIELD => "this is the test with the humpty dumpty text (item 3)",
            REGRESSION_TEST_LIST_NOTES1_FIELD => array(array(0, "Humpty Dumpty")),
            REGRESSION_TEST_LIST_STATUS_FIELD => "open",
            REGRESSION_TEST_LIST_NOTES2_FIELD => array(array(0, "had a great fall"))),
    array  (REGRESSION_TEST_LIST_NAME_FIELD => "general test 4",
            REGRESSION_TEST_LIST_DESCRIPTION_FIELD => "this is the test with the humpty dumpty text (item 4)",
            REGRESSION_TEST_LIST_NOTES1_FIELD => array(array(0, "All the king's horses")),
            REGRESSION_TEST_LIST_STATUS_FIELD => "open",
            REGRESSION_TEST_LIST_NOTES2_FIELD => array(array(0, "and all the king's men"))),
    array  (REGRESSION_TEST_LIST_NAME_FIELD => "general test 5",
            REGRESSION_TEST_LIST_DESCRIPTION_FIELD => "this is the test with the humpty dumpty text (item 5)",
            REGRESSION_TEST_LIST_NOTES1_FIELD => array(array(0, "Couldn't put Humpty")),
            REGRESSION_TEST_LIST_STATUS_FIELD => "open",
            REGRESSION_TEST_LIST_NOTES2_FIELD => array(array(0, "together again"))),
    array  (REGRESSION_TEST_LIST_NAME_FIELD => "general test 6",
            REGRESSION_TEST_LIST_DESCRIPTION_FIELD => "this is status field test [1]",
            REGRESSION_TEST_LIST_NOTES1_FIELD => array(array(0, "some times we'll add a note")),
            REGRESSION_TEST_LIST_STATUS_FIELD => "open",
            REGRESSION_TEST_LIST_NOTES2_FIELD => array(array(0, ""))),
    array  (REGRESSION_TEST_LIST_NAME_FIELD => "general test 7",
            REGRESSION_TEST_LIST_DESCRIPTION_FIELD => "this is status field test [2]",
            REGRESSION_TEST_LIST_NOTES1_FIELD => array(array(0, "")),
            REGRESSION_TEST_LIST_STATUS_FIELD => "busy",
            REGRESSION_TEST_LIST_NOTES2_FIELD => array(array(0, "we'll add three notes here"), array(0, "since this item is busy"))),
    array  (REGRESSION_TEST_LIST_NAME_FIELD => "general test 8",
            REGRESSION_TEST_LIST_DESCRIPTION_FIELD => "this is status field test [3]",
            REGRESSION_TEST_LIST_NOTES1_FIELD => array(array(0, "maybe one note here")),
            REGRESSION_TEST_LIST_STATUS_FIELD => "pending",
            REGRESSION_TEST_LIST_NOTES2_FIELD => array(array(0, "and one note here"))),
    array  (REGRESSION_TEST_LIST_NAME_FIELD => "general test 9",
            REGRESSION_TEST_LIST_DESCRIPTION_FIELD => "this is status field test [4]",
            REGRESSION_TEST_LIST_NOTES1_FIELD => array(array(0, "")),
            REGRESSION_TEST_LIST_STATUS_FIELD => "close",
            REGRESSION_TEST_LIST_NOTES2_FIELD => array(array(0, ""))),
    array  (REGRESSION_TEST_LIST_NAME_FIELD => "general test 10",
            REGRESSION_TEST_LIST_DESCRIPTION_FIELD => "this is status field test [5]",
            REGRESSION_TEST_LIST_NOTES1_FIELD => array(array(0, "this note belongs to the 20th item of the test list")),
            REGRESSION_TEST_LIST_STATUS_FIELD => "close",
            REGRESSION_TEST_LIST_NOTES2_FIELD => array(array(0, "this note also belongs to the 20th item of the test list")))
);

?>
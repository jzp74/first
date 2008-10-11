<?php

/**
 * login as user admin
 */
function login_user_admin ()
{
    global $user;
    global $firstthingsfirst_admin_passwd;

    # logout current user    
    $user->logout();
    # login as admin
    return $user->login("admin", $firstthingsfirst_admin_passwd);
}

/**
 * create user regressiontest
 */
function create_test_user ()
{
    global $user;
    global $regression_tester_user_array;
    
    # check if user already exists
    if ($user->exists(REGRESSION_TEST_USER_NAME))
        $user->delete(USER_NAME_FIELD_NAME."='".REGRESSION_TEST_USER_NAME."'");
    
    # create new user
    return $user->insert($regression_tester_user_array);
}

/**
 * login user regressiontest
 */
function login_test_user ()
{
    global $user;

    # logout current user    
    $user->logout();
    # login as test user
    return $user->login(REGRESSION_TEST_USER_NAME, REGRESSION_TEST_USER_PW);
}

/**
 * change password of user regressiontest
 */
function update_test_user ()
{
    global $user;
    
    # set new password
    $update_array = array(USER_PW_FIELD_NAME => REGRESSION_TEST_USER_NEW_PW);
    
    # update test user
    return $user->update(USER_NAME_FIELD_NAME."='".REGRESSION_TEST_USER_NAME."'", $update_array);
}

/**
 * login user regressiontest with new password
 */
function login_test_user_new_pw ()
{
    global $user;

    # logout current user    
    $user->logout();
    # login as test user
    return $user->login(REGRESSION_TEST_USER_NAME, REGRESSION_TEST_USER_NEW_PW);
}

/**
 * delete user regressiontest
 */
function delete_test_user ()
{
    global $user;
    
    # check if we have to cleanup
    if (REGRESSION_TEST_CLEANUP == FALSE)
        return TRUE;

    # logout current user    
    $user->logout();
    # delete test user
    return $user->delete(USER_NAME_FIELD_NAME."='".REGRESSION_TEST_USER_NAME."'");
}

/**
 * create list regressiontest
 */
function create_test_list ()
{
    global $list_table_description;
    global $regression_test_list_description_definition;
    
    # check if list already exists
    if ($list_table_description->select_record(REGRESSION_TEST_LIST_TITLE))
    {
        $list_table = new ListTable(REGRESSION_TEST_LIST_TITLE);
        if ($list_table->drop() == FALSE)
            return FALSE;
    }

    if ($list_table_description->insert($regression_test_list_description_definition) == FALSE)
        return FALSE;
    
    $list_table = new ListTable(REGRESSION_TEST_LIST_TITLE);
    return $list_table->create();
}

/**
 * open list
 */
function open_test_list ()
{
    global $list_table_description;
    
    # open list
    $results = $list_table_description->select_record(REGRESSION_TEST_LIST_TITLE);
    if (count($results) > 0)
        return TRUE;
    else
        return FALSE;
}

/**
 * update test list
 */
function update_test_list ()
{
    global $list_table_description;

    $description_array = array(LISTTABLEDESCRIPTION_DESCRIPTION_FIELD_NAME => REGRESSION_TEST_LIST_NEW_DESCRIPTION);
    if ($list_table_description->update(REGRESSION_TEST_LIST_TITLE, $description_array) == FALSE)
        return FALSE;
    
    return TRUE;
}

/**
 * add list items 
 */
function add_list_items ()
{
    global $regression_test_list_items;
    
    $list_table = new ListTable(REGRESSION_TEST_LIST_TITLE);

    foreach ($regression_test_list_items as $regression_test_list_item)
        if (!$list_table->insert($regression_test_list_item))
            return FALSE;
   
    return TRUE;
}

/**
 * read list item
 */
function read_list_item ()
{
    $list_table = new ListTable(REGRESSION_TEST_LIST_TITLE);

    $results = $list_table->select_record("_id=9");
    if (count($results) > 0)
        return TRUE;
    
    return FALSE;
}

/**
 * update list item
 */
function update_list_item ()
{
    $list_table = new ListTable(REGRESSION_TEST_LIST_TITLE);
    $list_table_note = new ListTableNote(REGRESSION_TEST_LIST_TITLE);

    $results = $list_table->select_record("_id=10");
    # get second note of first notes column
    $note_field = $results[REGRESSION_TEST_LIST_NOTES1_FIELD][1];
    $note_array = array($note_field["_id"], $note_field["_note"]);    
    if ($list_table_note->update($note_field["_id"], $note_field["_note"]." [UPDATED BY REGRESSION TEST]") == TRUE)
        return TRUE;
    
    return FALSE;
}

/**
 * delete list item
 */
function delete_list_item ()
{
    $list_table = new ListTable(REGRESSION_TEST_LIST_TITLE);

    if ($list_table->delete("_id=3") == TRUE)
        return TRUE;
    
    return FALSE;
}

/**
 * delete list
 */
function delete_test_list ()
{
    $list_table = new ListTable(REGRESSION_TEST_LIST_TITLE);

    # check if we have to cleanup
    if (REGRESSION_TEST_CLEANUP == FALSE)
        return TRUE;
    
    if ($list_table->drop() == TRUE)
        return TRUE;
    
    return FALSE;
}   

?>

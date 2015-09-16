<?php

/**
 * This file contains the class definition of UserListTablePermissions
 *
 * @package Class_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007-2012 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * defintion of database table name
 */
define("USERLISTTABLEPERMISSIONS_TABLE_NAME", $firstthingsfirst_db_table_prefix."userlisttablepermissions");

/**
 * definition of _listtable_title field name
 */
define("USERLISTTABLEPERMISSIONS_LISTTABLE_TITLE_FIELD_NAME", "_listtable_title");

/**
 * definition of _user_name field name
 */
define("USERLISTTABLEPERMISSIONS_USER_NAME_FIELD_NAME", "_user_name");

/**
 * definition of times_login field name
 */
define("USERLISTTABLEPERMISSIONS_CAN_VIEW_LIST_FIELD_NAME", "_can_view_list");

/**
 * definition of times_login field name
 */
define("USERLISTTABLEPERMISSIONS_CAN_EDIT_LIST_FIELD_NAME", "_can_edit_list");

/**
 * definition of times_login field name
 */
define("USERLISTTABLEPERMISSIONS_CAN_CREATE_LIST_FIELD_NAME", "_can_create_list");

/**
 * definition of times_login field name
 */
define("USERLISTTABLEPERMISSIONS_IS_AMDIN_FIELD_NAME", "_is_admin");

/**
 * definition of fields
 */
$class_userlisttablepermissions_fields = array(
    DB_ID_FIELD_NAME => array("", FIELD_TYPE_DEFINITION_AUTO_NUMBER, "", COLUMN_SHOW),
    USERLISTTABLEPERMISSIONS_LISTTABLE_TITLE_FIELD_NAME => array("", FIELD_TYPE_DEFINITION_NON_EDIT_TEXT_LINE, "", COLUMN_NO_SHOW),
    USERLISTTABLEPERMISSIONS_USER_NAME_FIELD_NAME => array("LABEL_USERLISTTABLEPERMISSIONS_USER_NAME", FIELD_TYPE_DEFINITION_NON_EDIT_TEXT_LINE, "", COLUMN_SHOW),
    USERLISTTABLEPERMISSIONS_CAN_VIEW_LIST_FIELD_NAME => array("LABEL_USERLISTTABLEPERMISSIONS_CAN_VIEW_LIST", FIELD_TYPE_DEFINITION_BOOL, "", COLUMN_SHOW),
    USERLISTTABLEPERMISSIONS_CAN_EDIT_LIST_FIELD_NAME => array("LABEL_USERLISTTABLEPERMISSIONS_CAN_EDIT_LIST", FIELD_TYPE_DEFINITION_BOOL, "", COLUMN_SHOW),
    USERLISTTABLEPERMISSIONS_CAN_CREATE_LIST_FIELD_NAME => array("LABEL_USERLISTTABLEPERMISSIONS_CAN_CREATE_LIST", FIELD_TYPE_DEFINITION_BOOL, "", COLUMN_SHOW),
    USERLISTTABLEPERMISSIONS_IS_AMDIN_FIELD_NAME => array("LABEL_USERLISTTABLEPERMISSIONS_IS_AMDIN", FIELD_TYPE_DEFINITION_BOOL, "", COLUMN_SHOW),
);

/**
 * definition of metadata
 */
define("USERLISTTABLEPERMISSIONS_METADATA", "--1");


/**
 * This class represents the permissions of all user/list combinations
 *
 * @package Class_FirstThingsFirst
 */
class UserListTablePermissions extends UserDatabaseTable
{
    /**
    * overwrite __construct() function
    * @return void
    */
    function __construct ()
    {
        # these variables are assumed to be globally available
        global $class_userlisttablepermissions_fields;

        # call parent __construct()
        parent::__construct(USERLISTTABLEPERMISSIONS_TABLE_NAME, $class_userlisttablepermissions_fields, USERLISTTABLEPERMISSIONS_METADATA);

        $this->_log->debug("constructed new UserListTablePermissions object");
    }

    /**
     * select user permissions for specified list
     * this function is used to check if a user has list permissions
     * @param $list_title string title of new list
     * @param $user_name string name of user
     * @return array array in specified format with permissions
     */
    function select_user_list_permissions ($list_title, $user_name)
    {
        $this->_log->trace("select user list permissions (list_title=".$list_title.", user_name=".$user_name.")");

        # select only the permissions from database
        $query = "SELECT ".USERLISTTABLEPERMISSIONS_CAN_VIEW_LIST_FIELD_NAME.", ".USERLISTTABLEPERMISSIONS_CAN_EDIT_LIST_FIELD_NAME.", ";
        $query .= USERLISTTABLEPERMISSIONS_CAN_CREATE_LIST_FIELD_NAME.", ".USERLISTTABLEPERMISSIONS_IS_AMDIN_FIELD_NAME;
        $query .= " FROM ".USERLISTTABLEPERMISSIONS_TABLE_NAME." WHERE ";
        $query .= USERLISTTABLEPERMISSIONS_LISTTABLE_TITLE_FIELD_NAME."=\"".$list_title."\" AND ";
        $query .= USERLISTTABLEPERMISSIONS_USER_NAME_FIELD_NAME."=\"".$user_name."\"";

        $result = $this->_database->query($query);
        if ($result != FALSE)
        {
            $row = $this->_database->fetch($result);
            if (count($row) > 0)
            {
                # create a new array to store the permissions
                $new_row = array();
                $new_row[PERMISSION_CAN_VIEW_SPECIFIC_LIST] = $row[USERLISTTABLEPERMISSIONS_CAN_VIEW_LIST_FIELD_NAME];
                $new_row[PERMISSION_CAN_EDIT_SPECIFIC_LIST] = $row[USERLISTTABLEPERMISSIONS_CAN_EDIT_LIST_FIELD_NAME];
                $new_row[PERMISSION_CAN_CREATE_SPECIFIC_LIST] = $row[USERLISTTABLEPERMISSIONS_CAN_CREATE_LIST_FIELD_NAME];
                $new_row[PERMISSION_IS_ADMIN_SPECIFIC_LIST] = $row[USERLISTTABLEPERMISSIONS_IS_AMDIN_FIELD_NAME];

                $log_str = "selected user list permissions (permissions=[";
                $log_str = $new_row[PERMISSION_CAN_VIEW_SPECIFIC_LIST].$new_row[PERMISSION_CAN_EDIT_SPECIFIC_LIST];
                $log_str = $new_row[PERMISSION_CAN_CREATE_SPECIFIC_LIST].$new_row[PERMISSION_IS_ADMIN_SPECIFIC_LIST]."]";
                $this->_log->trace($log_str);

                return $new_row;
            }
            else
            {
                $this->_log->warn("fetching from database yielded no results");

                return array();
            }
        }
        else
        {
            $this->_handle_error("could not read user list permissions row from table", "ERROR_DATABASE_PROBLEM");

            return array();
        }
    }

    /**
     * update a list permission
     * @param string encoded_key_string encoded_key_string of list permission
     * @param $name_values array array containing new name-values of record
     * @return bool indicates if list permission has been updated
     */
    function update ($encoded_key_string, $name_values_array)
    {
        $this->_log->trace("update user list permission (encoded_key_string=".$encoded_key_string.")");

        # if user is list admin then user must also be able to create list
        if (array_key_exists(USERLISTTABLEPERMISSIONS_IS_AMDIN_FIELD_NAME, $name_values_array) == TRUE)
            if ($name_values_array[USERLISTTABLEPERMISSIONS_IS_AMDIN_FIELD_NAME] == 1)
                $name_values_array[USERLISTTABLEPERMISSIONS_CAN_CREATE_LIST_FIELD_NAME] = 1;

        # if user can create list than user must also be able to edit list
        if (array_key_exists(USERLISTTABLEPERMISSIONS_CAN_CREATE_LIST_FIELD_NAME, $name_values_array) == TRUE)
            if ($name_values_array[USERLISTTABLEPERMISSIONS_CAN_CREATE_LIST_FIELD_NAME] == 1)
                $name_values_array[USERLISTTABLEPERMISSIONS_CAN_EDIT_LIST_FIELD_NAME] = 1;

        # if user can edit list than user must also be able to view list
        if (array_key_exists(USERLISTTABLEPERMISSIONS_CAN_EDIT_LIST_FIELD_NAME, $name_values_array) == TRUE)
            if ($name_values_array[USERLISTTABLEPERMISSIONS_CAN_EDIT_LIST_FIELD_NAME] == 1)
                $name_values_array[USERLISTTABLEPERMISSIONS_CAN_VIEW_LIST_FIELD_NAME] = 1;

        if (parent::update($encoded_key_string, $name_values_array) == FALSE)
            return FALSE;

        $this->_log->trace("list permission updated (encoded_key_string=".$encoded_key_string.")");

        return TRUE;
    }

    /**
    * insert list permissions for a new list for all users
    * current user and user admin get all permissions and all other users get no permissions
    * @param $list_title string title of new list
    * @return bool indicates if all permissions have been inserted
    */
    function insert_list_permissions_new_list ($list_title)
    {
        $this->_log->trace("insert list permissions (list_title=".$list_title.")");

        # get list of all users
        $current_user = $this->_user->get_name();
        $results = $this->_user->select("", DATABASETABLE_ALL_PAGES, array(0 => USER_NAME_FIELD_NAME, 1 => USER_IS_ADMIN_FIELD_NAME));
        if (count($results) == 0)
        {
            $this->_handle_error("could not select all users", "ERROR_DATABASE_PROBLEM");

            return FALSE;
        }

        foreach($results as $user_array)
        {
            $user_name = $user_array[0];
            $user_is_admin = $user_array[1];

            $this->_log->trace("insert permissions for user (user=".$user_name.")");
            $name_values_array = array();
            $name_values_array[USERLISTTABLEPERMISSIONS_LISTTABLE_TITLE_FIELD_NAME] = $list_title;
            $name_values_array[USERLISTTABLEPERMISSIONS_USER_NAME_FIELD_NAME] = $user_name;

            # give current user (creator of the list) and user admin all permissions
            if (($user_name == $current_user) || ($user_name == "admin") || ($user_is_admin == 1))
            {
                $name_values_array[USERLISTTABLEPERMISSIONS_CAN_VIEW_LIST_FIELD_NAME] = 1;
                $name_values_array[USERLISTTABLEPERMISSIONS_CAN_EDIT_LIST_FIELD_NAME] = 1;
                $name_values_array[USERLISTTABLEPERMISSIONS_CAN_CREATE_LIST_FIELD_NAME] = 1;
                $name_values_array[USERLISTTABLEPERMISSIONS_IS_AMDIN_FIELD_NAME] = 1;
            }
            # other users get no permissions
            else
            {
                $name_values_array[USERLISTTABLEPERMISSIONS_CAN_VIEW_LIST_FIELD_NAME] = 0;
                $name_values_array[USERLISTTABLEPERMISSIONS_CAN_EDIT_LIST_FIELD_NAME] = 0;
                $name_values_array[USERLISTTABLEPERMISSIONS_CAN_CREATE_LIST_FIELD_NAME] = 0;
                $name_values_array[USERLISTTABLEPERMISSIONS_IS_AMDIN_FIELD_NAME] = 0;
            }

            # insert permissions
            $result = $this->insert($name_values_array);
            if ($result == 0)
            {
                $this->_handle_error("could not insert user permissions (user=".$user_name.")", "ERROR_DATABASE_PROBLEM");

                return FALSE;
            }
        }

        $this->_log->trace("inserted list permissions");

        return TRUE;
    }

    /**
     * insert list permissions for a new user for all lists
     * this new user gets no permissions for existing listst
     * @param $user_name string name of new user
     * @param $is_admin int indicates if new user is administrator
     * @return bool indicates if all permissions have been inserted
     */
    function insert_list_permissions_new_user ($user_name, $is_admin)
    {
        global $list_table_description;

        $this->_log->trace("insert list permissions (user_name=$user_name, is_admin=$is_admin)");

        # get list of all users
        $results = $list_table_description->select("", DATABASETABLE_ALL_PAGES, array(0 => LISTTABLEDESCRIPTION_TITLE_FIELD_NAME));
        if (count($results) > 0)
        {
            foreach($results as $list_title_array)
            {
                $list_title = $list_title_array[0];
                $name_values_array = array();
                $name_values_array[USERLISTTABLEPERMISSIONS_LISTTABLE_TITLE_FIELD_NAME] = $list_title;
                $name_values_array[USERLISTTABLEPERMISSIONS_USER_NAME_FIELD_NAME] = $user_name;

                # check if new user is administrator
                if ($is_admin == 0)
                {
                    # user has no admin permission and therefore gets no list permissions
                    $name_values_array[USERLISTTABLEPERMISSIONS_CAN_VIEW_LIST_FIELD_NAME] = 0;
                    $name_values_array[USERLISTTABLEPERMISSIONS_CAN_EDIT_LIST_FIELD_NAME] = 0;
                    $name_values_array[USERLISTTABLEPERMISSIONS_CAN_CREATE_LIST_FIELD_NAME] = 0;
                    $name_values_array[USERLISTTABLEPERMISSIONS_IS_AMDIN_FIELD_NAME] = 0;
                }
                else
                {
                    # user has admin permissions and get all permissions for all lists
                    $name_values_array[USERLISTTABLEPERMISSIONS_CAN_VIEW_LIST_FIELD_NAME] = 1;
                    $name_values_array[USERLISTTABLEPERMISSIONS_CAN_EDIT_LIST_FIELD_NAME] = 1;
                    $name_values_array[USERLISTTABLEPERMISSIONS_CAN_CREATE_LIST_FIELD_NAME] = 1;
                    $name_values_array[USERLISTTABLEPERMISSIONS_IS_AMDIN_FIELD_NAME] = 1;
                }

                # insert permissions
                $result = $this->insert($name_values_array);
                if ($result == 0)
                {
                    $this->_handle_error("could not insert user permissions (list=".$list_title.")", "ERROR_DATABASE_PROBLEM");

                    return FALSE;
                }
            }
        }

        $this->_log->trace("inserted list permissions");

        return TRUE;
    }

    /**
     * delete specific user permissions
     * this function is used to check if a user has list permissions
     * @param $key_string string permissions key string
     * @return bool indicates if record has been deleted
     */
    function delete ($key_string)
    {
        $this->_log->trace("delete user list permissions (key_string=$key_string)");
        
        # rerurn TRUE when database has not yet been created
        if (!$this->_database->table_exists($this->table_name))
        {
            $this->_log->warn("table does not exist");
            
            return TRUE;
        }
        
        # call parent delete
        if (parent::delete($key_string) == FALSE)
            return FALSE;

        $this->_log->trace("deleted user list permissions");

        return TRUE;
    }
    
}

?>
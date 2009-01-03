<?php

/**
 * This file contains the class definition of User
 *
 * @package Class_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2008 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * defintion of database table name
 */
define("USER_TABLE_NAME", $firstthingsfirst_db_table_prefix."user");

/**
 * definition of id of an empty (non initialized) User object
 */
define("USER_ID_RESET_VALUE", -1);

/**
 * definition of name of an empty (non initialized) User object
 */
define("USER_NAME_RESET_VALUE", "-system-");

/**
 * definition of name field name
 */
define("USER_NAME_FIELD_NAME", "_name");

/**
 * definition of pw field name
 */
define("USER_PW_FIELD_NAME", "_pw");

/**
 * definition of edit_list field name
 */
define("USER_CAN_EDIT_LIST_FIELD_NAME", "_edit_list");

/**
 * definition of create_list field name
 */
define("USER_CAN_CREATE_LIST_FIELD_NAME", "_create_list");

/**
 * definition of admin field name
 */
define("USER_IS_ADMIN_FIELD_NAME", "_admin");

/**
 * definition of times_login field name
 */
define("USER_TIMES_LOGIN_FIELD_NAME", "_times_login");

/**
 * definition of fields
 */
$class_user_fields = array(
    DB_ID_FIELD_NAME => array(LABEL_LIST_ID, "LABEL_DEFINITION_AUTO_NUMBER", ""),
    USER_NAME_FIELD_NAME => array(LABEL_USER_NAME, "LABEL_DEFINITION_USERNAME", DATABASETABLE_UNIQUE_FIELD),
    USER_PW_FIELD_NAME => array(LABEL_USER_PW, "LABEL_DEFINITION_PASSWORD", ""),
    USER_CAN_EDIT_LIST_FIELD_NAME => array(LABEL_USER_CAN_EDIT_LIST, "LABEL_DEFINITION_BOOL", ""),
    USER_CAN_CREATE_LIST_FIELD_NAME => array(LABEL_USER_CAN_CREATE_LIST, "LABEL_DEFINITION_BOOL", ""),
    USER_IS_ADMIN_FIELD_NAME => array(LABEL_USER_IS_ADMIN, "LABEL_DEFINITION_BOOL", ""),
    USER_TIMES_LOGIN_FIELD_NAME => array(LABEL_USER_TIMES_LOGIN, "LABEL_DEFINITION_NON_EDIT_NUMBER", ""),
);

/**
 * definition of metadata
 */
define("USER_METADATA", "-11");


/**
 * This class represents a user and handles login/logout as well as permissions
 * This class contains no actual data. Data is only stored in session params
 *
 * @package Class_FirstThingsFirst
 */
class User extends UserDatabaseTable
{    
    # set attributes of this object when it is constructed
    function __construct ()
    {
        # these variables are assumed to be globally available
        global $class_user_fields;
        
        # call parent __construct()
        parent::__construct(USER_TABLE_NAME, $class_user_fields, USER_METADATA);
        
        # start a session
        session_cache_limiter('private, must-revalidate');
        session_start();
        
        # reset relevant session parameters
        
        if (isset($_SESSION["login"]))
            $this->_log->debug("user session is still active (name=".$this->get_name().")");
        else
            $this->reset();
        
        $this->_log->debug("constructed new User object");
    }

    /**
    * get value of SESSION variable id.
    * @return int value of SESSION variable id.
    */
    function get_id ()
    {
        return $_SESSION["id"];
    }
    
    /**
    * get value of SESSION variable name.
    * @return string value of SESSION variable name.
    */
    function get_name ()
    {
        return $_SESSION["name"];
    }

    /**
    * get value of SESSION variable edit_list.
    * @return bool value of SESSION variable can_edit_list.
    */
    function get_can_edit_list ()
    {
        return $_SESSION["can_edit_list"];
    }

    /**
    * get value of SESSION variable create_list.
    * @return bool value of SESSION variable can_create_list.
    */
    function get_can_create_list ()
    {
        return $_SESSION["can_create_list"];
    }

    /**
    * get value of SESSION variable admin.
    * @return bool value of SESSION variable is_admin.
    */
    function get_is_admin ()
    {
        return $_SESSION["is_admin"];
    }

    /**
    * get value of SESSION variable login.
    * @return bool value of SESSION variable login.
    */
    function get_login ()
    {
        return $_SESSION["login"];
    }

    /**
    * get value of SESSION variable list_state.
    * get settings from session and set global list_state object
    * @param string $list_title set blobal list_state varible with state of this list
    * @return string value of SESSION variable list_state.
    */
    function get_list_state ($list_title)        
    {
        global $list_state;
        
        # for some reason a cast is needed here
        $list_states = (array)$this->_json->decode($_SESSION["list_states"]);
        
        if (array_key_exists($list_title, $list_states))
            $list_state->set($list_title, (array)$list_states[$list_title]); # mind the cast
        else
        {
            $this->_log->trace("list_state not found in session (list_title=".$list_title.")");
            
            $list_state->reset();
            $list_state->set_list_title($list_title);
        }
    }
    
    /**
    * set value of SESSION variable id
    * @param int $id id of current user
    * @return void
    */
    function set_id ($id)
    {
        $_SESSION["id"] = $id;
    }
    
    /**
    * set value of SESSION variable name
    * @param int $name name of current user
    * @return void
    */
    function set_name ($name)
    {
        $_SESSION["name"] = $name;
    }
    
    /**
    * set value of SESSION variable can_edit_list
    * @param bool $permission indicates if current user is allowed to edit a list
    * @return void
    */
    function set_can_edit_list ($permission)
    {
        $_SESSION["can_edit_list"] = $permission;
    }

    /**
    * set value of SESSION variable can_create_list
    * @param bool $permission indicates if current user is allowed to create a new list
    * @return void
    */
    function set_can_create_list ($permission)
    {
        $_SESSION["can_create_list"] = $permission;
    }

    /**
    * set value of SESSION variable is_admin
    * @param bool $permission indicates if current user is has admin privileges
    * @return void
    */
    function set_is_admin ($permission)
    {
        $_SESSION["is_admin"] = $permission;
    }

    /**
    * set value of SESSION variable times_login
    * @param int $times_login number of times current user has logged in
    * @return void
    */
    function set_times_login ($times_login)
    {
        $_SESSION["times_login"] = $times_login;
    }

    /**
    * set value of SESSION variable login
    * @param bool $login indicates if current user is logged in
    * @return void
    */
    function set_login ($login)
    {
        $_SESSION["login"] = $login;
    }

    /**
    * store values from global list state object in session 
    * @return void
    */
    function set_list_state ()
    {
        global $list_state;
        
        # for some reason a cast is needed here
        $list_states = (array)$this->_json->decode($_SESSION["list_states"]);

        $list_states[$list_state->get_list_title()] = $list_state->pass();        
        $_SESSION["list_states"] = $this->_json->encode($list_states);
    }
        
    
    /**
    * reset attributes to initial values
    * @return void
    */
    function reset ()
    {
        $this->_log->trace("resetting User");

        $this->set_id(USER_ID_RESET_VALUE);
        $this->set_name(USER_NAME_RESET_VALUE);
        $this->set_times_login("0");
        $this->set_can_edit_list("0");
        $this->set_can_create_list("0");
        $this->set_is_admin("0");
        $this->set_login("0");
        $_SESSION["list_states"] = $this->_json->encode(array());
    }

    /**
    * check if current user is logged in
    * @return bool indicates if current user is logged in
    */
    function is_login ()
    {
        if (($this->get_login()) && ($this->get_id() != USER_ID_RESET_VALUE) && ($this->get_name() != USER_NAME_RESET_VALUE) && ($this->get_name() != ""))
            return TRUE;
        return FALSE;
    }

    /**
    * login a user and restore user settings from database
    * @param string $name name of user
    * @param string $pw encrypted password
    * @return bool indicates if user has been logged in
    */
    function login ($name, $pw)
    {
        global $firstthingsfirst_admin_passwd;
        
        $this->_log->trace("login (name=".$name.")");
        
        if ($this->is_login())
            $this->logout();
            
        # create user admin the first time user admin tries to login    
        if ($name == "admin" && !$this->exists("admin"))
        {
            $this->_log->info("first time login for admin");
            $name_value_array = array();
            $name_value_array[USER_NAME_FIELD_NAME] = $name;
            $name_value_array[USER_PW_FIELD_NAME] = $firstthingsfirst_admin_passwd;
            $name_value_array[USER_CAN_EDIT_LIST_FIELD_NAME] = 1;
            $name_value_array[USER_CAN_CREATE_LIST_FIELD_NAME] = 1;
            $name_value_array[USER_IS_ADMIN_FIELD_NAME] = 1;
            $name_value_array[USER_TIMES_LOGIN_FIELD_NAME] = 0;
            
            if (!$this->insert($name_value_array))
                return FALSE;
        }

        # create encoded_key_string
        $encoded_key_string = parent::_encode_key_string(USER_NAME_FIELD_NAME."='".$name."'");

        # check if record exists
        $record = parent::select_record($encoded_key_string);
        if (count($record) == 0)
        {
            if ($this->error_message_str != ERROR_DATABASE_CONNECT)
            {
                $this->_handle_error("", ERROR_INCORRECT_NAME_PASSWORD);
                $this->error_str = "";
            }
            
            return FALSE;
        }
        
        $password = md5($pw);
        $db_password = $record[USER_PW_FIELD_NAME];
            
        if ($db_password == $password)
        {
            # set session parameters
            $this->set_id($record[DB_ID_FIELD_NAME]);
            $this->set_name($record[USER_NAME_FIELD_NAME]);
            $this->set_can_edit_list($record[USER_CAN_EDIT_LIST_FIELD_NAME]);
            $this->set_can_create_list($record[USER_CAN_CREATE_LIST_FIELD_NAME]);
            $this->set_is_admin($record[USER_IS_ADMIN_FIELD_NAME]);
            $this->set_times_login($record[USER_TIMES_LOGIN_FIELD_NAME] + 1);
            $this->set_login(1);
                
            $name_values_array = array();
            $name_values_array[USER_TIMES_LOGIN_FIELD_NAME] = ($record[USER_TIMES_LOGIN_FIELD_NAME] + 1);
            
            # update the number of times this user has logged in
            if (parent::update($encoded_key_string, $name_values_array) == FALSE)                
                return FALSE;
            else
            {
                $this->_log->debug("user logged in (name=".$name.")");
            
                return TRUE;
            }
        }
        else
        {        
            $this->_log->warn("passwords do not match (name=".$name."), user is not logged in");
            $this->error_str = "";
            $this->_handle_error("", ERROR_INCORRECT_NAME_PASSWORD);
            
            return FALSE;
        }
    } 
    
    /**
    * logout current user
    * @return void
    */
    function logout ()
    {
        $name = $this->get_name();
        
        $this->_log->trace("log out (name=".$name.")");
        
        $this->reset();

        $this->_log->trace("user logged out (name=".$name.")");        
    }
    
    /**
    * check if user already exists
    * @param string $name name of user
    * @return bool indicates if user already exists
    */
    function exists ($name)
    {
        # create encoded_key_string
        $encoded_key_string = parent::_encode_key_string(USER_NAME_FIELD_NAME."='".$name."'");

        $record = parent::select_record($encoded_key_string);
        if (count($record) > 0)
        {
            $this->_log->debug("user already exists (name=".$name.")");
                
            return TRUE;
        }
        else if (strlen($this->get_error_message_str()) == 0)
        {
            $this->_log->debug("user does not exist (name=".$name.")");
                
            return FALSE;
        }
        else
            return FALSE;
    }                    

    /**
    * insert a new user to database
    * @param $name_values_array array array containing name-values of record
    * @return bool indicates if user has been inserted
    */
    function insert ($name_values_array)
    {
        $this->_log->trace("insert user (name=".$name_values_array[USER_NAME_FIELD_NAME].")");
        
        if (strlen($name_values_array[USER_PW_FIELD_NAME]) > 0)
        {
            $this->_log->debug("found a password");
            $name_values_array[USER_PW_FIELD_NAME] = md5($name_values_array[USER_PW_FIELD_NAME]);
        }
        else
        {
            $this->_log->error("could not find a password");
            
            return FALSE;
        }

        if ($this->exists($name_values_array[USER_NAME_FIELD_NAME]))
        {
            $this->_handle_error("user already exists", ERROR_DUPLICATE_USER_NAME);
            
            return FALSE;
        }
        
        if (parent::insert($name_values_array) == FALSE)
            return FALSE;
        
        $this->_log->info("user added (name=".$name_values_array[USER_NAME_FIELD_NAME].")");
        
        return TRUE;
    }

    /**
    * update a user
    * @param string encoded_key_string encoded_key_string of user
    * @param $name_values array array containing new name-values of record
    * @return bool indicates if user has been updated
    */
    function update ($encoded_key_string, $name_values_array)
    {
        $this->_log->trace("update user (encoded_key_string=".$encoded_key_string.")");
                
        if (array_key_exists(USER_PW_FIELD_NAME, $name_values_array) == TRUE)
        {
            $password_str = $name_values_array[USER_PW_FIELD_NAME];
            if (strlen($password_str) > 0)
            {
                $this->_log->debug("found a password");
                $name_values_array[USER_PW_FIELD_NAME] = md5($password_str);
            }
            else
            {
                $this->_log->debug("found an empty password");
                unset($name_values_array[USER_PW_FIELD_NAME]);
            }
        }

        if (parent::update($encoded_key_string, $name_values_array) == FALSE)
            return FALSE;
        
        $this->_log->info("user updated (encoded_key_string=".$encoded_key_string.")");

        return TRUE;
    }

}

?>

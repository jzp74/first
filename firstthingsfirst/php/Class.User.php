<?php

/**
 * This file contains the class definition of User
 *
 * @package Class_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007-2012 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * defintion of database table name
 */
define("USER_TABLE_NAME", $firstthingsfirst_db_table_prefix."user");

/**
 * definition of the session name
 */
define("USER_SESSION_NAME", "FIRSTTHINGSFIRST_SESSION");

/**
 * definition of id of an empty (non initialized) User object
 */
define("USER_ID_RESET_VALUE", -1);

/**
 * definition of name of an empty (non initialized) User object
 */
define("USER_NAME_RESET_VALUE", "-system-");

/**
 * definition of name of an empty (non initialized) User object
 */
define("USER_LINES_PER_PAGE_RESET_VALUE", "12");

/**
 * definition of name field name
 */
define("USER_NAME_FIELD_NAME", "_name");

/**
 * definition of pw field name
 */
define("USER_PW_FIELD_NAME", "_pw");

/**
 * definition of lang field name
 */
define("USER_LANG_FIELD_NAME", "_lang");

/**
 * definition of date format field name
 */
define("USER_DATE_FORMAT_FIELD_NAME", "_date_format");

/**
 * definition of decimal format field name
 */
define("USER_DECIMAL_MARK_FIELD_NAME", "_decimal_mark");

/**
 * definition of lines per page field name
 */
define("USER_LINES_PER_PAGE_FIELD_NAME", "_lines_per_page");

/**
 * definition of theme field name
 */
define("USER_THEME_FIELD_NAME", "_theme");

/**
 * definition of create_list field name
 */
define("USER_CAN_CREATE_LIST_FIELD_NAME", "_create_list");

/**
 * definition of create_list field name
 */
define("USER_CAN_CREATE_USER_FIELD_NAME", "_create_user");

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
    DB_ID_FIELD_NAME => array("LABEL_LIST_ID", FIELD_TYPE_DEFINITION_AUTO_NUMBER, "", COLUMN_SHOW),
    USER_NAME_FIELD_NAME => array("LABEL_USER_NAME", FIELD_TYPE_DEFINITION_USERNAME, DATABASETABLE_UNIQUE_FIELD, COLUMN_SHOW),
    USER_PW_FIELD_NAME => array("LABEL_USER_PW", FIELD_TYPE_DEFINITION_PASSWORD, "", COLUMN_NO_SHOW),
    USER_LANG_FIELD_NAME => array("LABEL_USER_LANG", FIELD_TYPE_DEFINITION_SELECTION, implode(array_keys($firstthingsfirst_lang_prefix_array), '|'), COLUMN_SHOW),
    USER_DATE_FORMAT_FIELD_NAME => array("LABEL_USER_DATE_FORMAT", FIELD_TYPE_DEFINITION_SELECTION, implode(array_keys($firstthingsfirst_date_format_prefix_array), '|'), COLUMN_SHOW),
    USER_DECIMAL_MARK_FIELD_NAME => array("LABEL_USER_DECIMAL_MARK", FIELD_TYPE_DEFINITION_SELECTION, implode(array_keys($firstthingsfirst_decimal_mark_prefix_array), '|'), COLUMN_SHOW),
    USER_LINES_PER_PAGE_FIELD_NAME => array("LABEL_USER_LINES_PER_PAGE", FIELD_TYPE_DEFINITION_NUMBER, USER_LINES_PER_PAGE_RESET_VALUE, COLUMN_SHOW),
    USER_THEME_FIELD_NAME => array("LABEL_USER_THEME", FIELD_TYPE_DEFINITION_SELECTION, implode(array_keys($firstthingsfirst_theme_prefix_array), '|'), COLUMN_SHOW),
    USER_CAN_CREATE_LIST_FIELD_NAME => array("LABEL_USER_CAN_CREATE_LIST", FIELD_TYPE_DEFINITION_BOOL, "", COLUMN_SHOW),
    USER_CAN_CREATE_USER_FIELD_NAME => array("LABEL_USER_CAN_CREATE_USER", FIELD_TYPE_DEFINITION_BOOL, "", COLUMN_SHOW),
    USER_IS_ADMIN_FIELD_NAME => array("LABEL_USER_IS_ADMIN", FIELD_TYPE_DEFINITION_BOOL, "", COLUMN_SHOW),
    USER_TIMES_LOGIN_FIELD_NAME => array("LABEL_USER_TIMES_LOGIN", FIELD_TYPE_DEFINITION_NON_EDIT_NUMBER, "", COLUMN_SHOW),
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
    /**
    * overwrite __construct() function
    * @return void
    */
    function __construct ()
    {
        # these variables are assumed to be globally available
        global $class_user_fields;
        global $firstthingsfirst_session_time;

        # call parent __construct()
        parent::__construct(USER_TABLE_NAME, $class_user_fields, USER_METADATA);

        # set session name
        session_name(USER_SESSION_NAME);

        # set cookie time and path
        # the path is constructed from the request uri
        $request_uri_array = explode('/', $_SERVER["REQUEST_URI"]);
        $array_length = count($request_uri_array);
        $cookie_path_str = "";
        # firstthingsfirst is installed in the document root
        if ($array_length == 1)
            $cookie_path_str = "/";
        # firstthingsfirst is not installed in the document root
        else
        {
            for ($position = 0; $position < ($array_length - 1); $position += 1)
                $cookie_path_str .= $request_uri_array[$position]."/";
        }
        session_set_cookie_params(($firstthingsfirst_session_time * 60), $cookie_path_str);

        # start a session
#        session_cache_limiter('private, must-revalidate');
        session_start();

        # reset relevant session parameters
        if (isset($_SESSION["login"]))
        {
            $this->_log->debug("user session is still active (name=".$this->get_name().")");

            # adjust cookie life time
            setcookie(USER_SESSION_NAME, $_COOKIE[USER_SESSION_NAME], time() + ($firstthingsfirst_session_time * 60));
        }
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
     * get value of SESSION variable current_list_name.
     * @return string value of SESSION variable current_list_name.
     */
    function get_current_list_name ()
    {
        return $_SESSION["current_list_name"];
    }

    /**
     * get value of SESSION variable lang.
     * @return string value of SESSION variable lang.
     */
    function get_lang ()
    {
        return $_SESSION["lang"];
    }

    /**
     * get value of SESSION variable date_format.
     * @return string value of SESSION variable date_format.
     */
    function get_date_format ()
    {
        return $_SESSION["date_format"];
    }

    /**
     * get value of SESSION variable decimal_mark.
     * @return string value of SESSION variable decimal_mark.
     */
    function get_decimal_mark ()
    {
        return $_SESSION["decimal_mark"];
    }

    /**
     * get value of SESSION variable lines_per_page.
     * @return string value of SESSION variable lang.
     */
    function get_lines_per_page ()
    {
        return $_SESSION["lines_per_page"];
    }

    /**
     * get value of SESSION variable theme.
     * @return string value of SESSION variable theme.
     */
    function get_theme ()
    {
        return $_SESSION["theme"];
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
    * get value of SESSION variable create_user.
    * @return bool value of SESSION variable can_create_user.
    */
    function get_can_create_user ()
    {
        return $_SESSION["can_create_user"];
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
     * set value of SESSION variable current_list_name
     * @param string $list_name name of current list
     * @return void
     */
    function set_current_list_name ($list_name)
    {
        $_SESSION["current_list_name"] = $list_name;
    }

    /**
     * set value of SESSION variable lang
     * @param string $lang preferred language
     * @return void
     */
    function set_lang ($lang)
    {
        $_SESSION["lang"] = $lang;
    }

    /**
     * set value of SESSION variable date_format
     * @param string $date_format preferred date format
     * @return void
     */
    function set_date_format ($date_format)
    {
        $_SESSION["date_format"] = $date_format;
    }

    /**
     * set value of SESSION variable decimal_mark
     * @param string $decimal_mark preferred decimal mark
     * @return void
     */
    function set_decimal_mark ($decimal_mark)
    {
        $_SESSION["decimal_mark"] = $decimal_mark;
    }

    /**
     * set value of SESSION variable lines_per_page
     * @param string $lines_per_page preferred maximum number of lines per page
     * @return void
     */
    function set_lines_per_page ($lines_per_page)
    {
        $_SESSION["lines_per_page"] = $lines_per_page;
    }

    /**
     * set value of SESSION variable theme
     * @param string $theme preferred theme
     * @return void
     */
    function set_theme ($theme)
    {
        $_SESSION["theme"] = $theme;
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
    * set value of SESSION variable can_create_user
    * @param bool $permission indicates if current user is allowed to create a new user
    * @return void
    */
    function set_can_create_user ($permission)
    {
        $_SESSION["can_create_user"] = $permission;
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
        $this->set_current_list_name("");
        $this->set_lang(LANG_EN);
        $this->set_date_format(DATE_FORMAT_EU);
        $this->set_decimal_mark(DECIMAL_MARK_POINT);
        $this->set_lines_per_page(12);
        $this->set_theme(THEME_BLUE);
        $this->set_can_create_list("0");
        $this->set_can_create_user("0");
        $this->set_is_admin("0");
        $this->set_times_login("0");
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
        global $firstthingsfirst_lang;

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
            $name_value_array[USER_LANG_FIELD_NAME] = $firstthingsfirst_lang;
            $name_value_array[USER_DATE_FORMAT_FIELD_NAME] = DATE_FORMAT_EU;
            $name_value_array[USER_DECIMAL_MARK_FIELD_NAME] = DECIMAL_MARK_POINT;
            $name_value_array[USER_LINES_PER_PAGE_FIELD_NAME] = 12;
            $name_value_array[USER_THEME_FIELD_NAME] = $firstthingsfirst_theme;
            $name_value_array[USER_CAN_CREATE_LIST_FIELD_NAME] = 1;
            $name_value_array[USER_CAN_CREATE_USER_FIELD_NAME] = 1;
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
            if ($this->error_message_str != "ERROR_DATABASE_CONNECT")
            {
                $this->_handle_error("", "ERROR_INCORRECT_NAME_PASSWORD");
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
            $this->set_current_list_name("");
            $this->set_lang($record[USER_LANG_FIELD_NAME]);
            $this->set_date_format($record[USER_DATE_FORMAT_FIELD_NAME]);
            $this->set_decimal_mark($record[USER_DECIMAL_MARK_FIELD_NAME]);
            $this->set_lines_per_page($record[USER_LINES_PER_PAGE_FIELD_NAME]);
            $this->set_theme($record[USER_THEME_FIELD_NAME]);
            $this->set_can_create_list($record[USER_CAN_CREATE_LIST_FIELD_NAME]);
            $this->set_can_create_user($record[USER_CAN_CREATE_USER_FIELD_NAME]);
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
                $this->_log->info("user logged in (name=".$name.")");

                return TRUE;
            }
        }
        else
        {
            $this->_log->warn("passwords do not match (name=".$name."), user is not logged in");
            $this->error_str = "";
            $this->_handle_error("", "ERROR_INCORRECT_NAME_PASSWORD");

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

        # reset this User object
        $this->reset();

        $this->_log->info("user logged out (name=".$name.")");
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
        global $user_list_permissions;

        $user_name = $name_values_array[USER_NAME_FIELD_NAME];
        $is_admin = $name_values_array[USER_IS_ADMIN_FIELD_NAME];

        $this->_log->trace("insert user (name=".$user_name.")");

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

        # if user is admin then user must also be able to create lists and users
        if (array_key_exists(USER_IS_ADMIN_FIELD_NAME, $name_values_array) == TRUE)
        {
            if ($name_values_array[USER_IS_ADMIN_FIELD_NAME] == 1)
            {
                $name_values_array[USER_CAN_CREATE_LIST_FIELD_NAME] = 1;
                $name_values_array[USER_CAN_CREATE_USER_FIELD_NAME] = 1;
            }
        }

        # if user is able to create users then user must also be able to create lists
        if (array_key_exists(USER_CAN_CREATE_USER_FIELD_NAME, $name_values_array) == TRUE)
        {
            if ($name_values_array[USER_CAN_CREATE_USER_FIELD_NAME] == 1)
            {
                $name_values_array[USER_CAN_CREATE_LIST_FIELD_NAME] = 1;
            }
        }

        if ($this->exists($user_name))
        {
            $this->_handle_error("user already exists", "ERROR_DUPLICATE_USER_NAME");

            return FALSE;
        }

        if (parent::insert($name_values_array) == FALSE)
            return FALSE;

        if ($user_list_permissions->insert_list_permissions_new_user($user_name, $is_admin) == FALSE)
        {
            # copy error strings from user_list_permissions
            $this->error_message_str = $user_list_permissions->get_error_message_str();
            $this->error_log_str = $user_list_permissions->get_error_log_str();
            $this->error_str = $user_list_permissions->get_error_str();

            return FALSE;
        }

        $this->_log->info("user added (name=".$user_name.")");

        return TRUE;
    }

    /**
    * update a user in database
    * @param string encoded_key_string encoded_key_string of user
    * @param $name_values array array containing new name-values of record
    * @param $update_session bool indicates if active user session parameters should be updated (only when user updates own settings)
    * @return bool indicates if user has been updated
    */
    function update ($encoded_key_string, $name_values_array, $update_session = FALSE)
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

        # if user is admin then user must also be able to create lists and users
        if (array_key_exists(USER_IS_ADMIN_FIELD_NAME, $name_values_array) == TRUE)
        {
            if ($name_values_array[USER_IS_ADMIN_FIELD_NAME] == 1)
            {
                $name_values_array[USER_CAN_CREATE_LIST_FIELD_NAME] = 1;
                $name_values_array[USER_CAN_CREATE_USER_FIELD_NAME] = 1;
            }
        }

        # if user is able to create users then user must also be able to create lists
        if (array_key_exists(USER_CAN_CREATE_USER_FIELD_NAME, $name_values_array) == TRUE)
        {
            if ($name_values_array[USER_CAN_CREATE_USER_FIELD_NAME] == 1)
            {
                $name_values_array[USER_CAN_CREATE_LIST_FIELD_NAME] = 1;
            }
        }

        # update user name in user_list_permissions
        if (array_key_exists(USER_NAME_FIELD_NAME, $name_values_array) == TRUE)
        {
            # select something from user_list_permissions to check if database table exists
            if ($this->_user_list_permissions->select(USERLISTTABLEPERMISSIONS_USER_NAME_FIELD_NAME, 1) == TRUE)
            {
                # database table user_list_permissions exists
                # first get the current user name
                $user_array = $this->select_record($encoded_key_string);
                if (count($user_array) == 0)
                    return FALSE;

                $current_user_name = $user_array[USER_NAME_FIELD_NAME];

                # create key string for user_list_permissions
                $permission_key_string = USERLISTTABLEPERMISSIONS_USER_NAME_FIELD_NAME."='".$current_user_name."'";

                # create array with new title
                $new_title_array = array();
                $new_title_array[USERLISTTABLEPERMISSIONS_USER_NAME_FIELD_NAME] = $name_values_array[USER_NAME_FIELD_NAME];

                if ($this->_user_list_permissions->update($permission_key_string, $new_title_array) == FALSE)
                {
                    # copy error strings from user_list_permissions
                    $this->error_message_str = $this->_user_list_permissions->get_error_message_str();
                    $this->error_log_str = $this->_user_list_permissions->get_error_log_str();
                    $this->error_str = $this->_user_list_permissions->get_error_str();

                    return FALSE;
                }
            }
        }

        if (parent::update($encoded_key_string, $name_values_array) == FALSE)
            return FALSE;

        # set session parameters (only for fields that can be changed in UserSettings page)
        if ($update_session == TRUE)
        {
            $this->set_name($name_values_array[USER_NAME_FIELD_NAME]);
            $this->set_lang($name_values_array[USER_LANG_FIELD_NAME]);
            $this->set_date_format($name_values_array[USER_DATE_FORMAT_FIELD_NAME]);
            $this->set_decimal_mark($name_values_array[USER_DECIMAL_MARK_FIELD_NAME]);
            $this->set_lines_per_page($name_values_array[USER_LINES_PER_PAGE_FIELD_NAME]);
            $this->set_theme($name_values_array[USER_THEME_FIELD_NAME]);
        }

        $this->_log->info("user updated (encoded_key_string=".$encoded_key_string.")");

        return TRUE;
    }

    /**
     * delete a user from database
     * @param $encoded_key_string string unique identifier of user
     * @return bool indicates if user has been deleted
     */
    function delete ($encoded_key_string)
    {
        $this->_log->trace("delete user (encoded_key_string=".$encoded_key_string.")");

        # first get the user name
        $user_array = $this->select_record($encoded_key_string);
        if (count($user_array) == 0)
            return FALSE;

        $user_name = $user_array[USER_NAME_FIELD_NAME];

        # delete the user
        if (parent::delete($encoded_key_string) == FALSE)
            return FALSE;

        # create key string for user_list_permissions
        $permission_key_string = USERLISTTABLEPERMISSIONS_USER_NAME_FIELD_NAME."='".$user_name."'";

        if ($this->_user_list_permissions->delete($permission_key_string) == FALSE)
        {
            # copy error strings from user_list_permissions
            $this->error_message_str = $this->_user_list_permissions->get_error_message_str();
            $this->error_log_str = $this->_user_list_permissions->get_error_log_str();
            $this->error_str = $this->_user_list_permissions->get_error_str();

            return FALSE;
        }

        $this->_log->info("user deleted (encoded_key_string=".$encoded_key_string.")");

        return TRUE;
    }

}

?>
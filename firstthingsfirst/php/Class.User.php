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
define("USER_NAME_RESET_VALUE", "_");


/**
 * This class represents a user and handles login/logout as well as permissions
 * This class contains no actual data. Data is only stored in session params
 *
 * @package Class_FirstThingsFirst
 */
class User
{
    /**
    * error string, contains last known error
    * @var string
    */
    protected $error_str;

    /**
    * reference to global json object
    * @var Services_JSON
    */
    protected $_json;
    
    /**
    * reference to global logging object
    * @var Logging
    */
    protected $_log;
    
    /**
    * reference to global database object
    * @var Database
    */
    protected $_database;
    
    # set attributes of this object when it is constructed
    function __construct ()
    {
        # these variables are assumed to be globally available
        global $json;
        global $database;
        global $logging;
        
        # set global references for this object
        $this->_json =& $json;
        $this->_database =& $database;
        $this->_log =& $logging;
        
        # start a session
        session_cache_limiter('private, must-revalidate');
        session_start();
        
        # reset relevant session parameters
        
        if ($this->is_login())
        {
            $this->_log->debug("user session is still active (name=".$this->get_name().")");
            $this->page = ACTION_GET_PORTAL;
            $this->page_title = "-";
        }
        else
            $this->reset();
        
        $this->_log->trace("constructed new User object");
    }

    /**
    * overwrite __toString() function
    * @todo function seems to be obsolete
    * @return void
    */
    function __toString ()
    {
        $str = "User: id=\"".$this->get_id()."\", ";
        $str .= "name=\"".$this->get_name()."\", ";
        $str .= "edit_list=\"".$this->get_edit_list()."\", ";
        $str .= "create_list=\"".$this->get_create_list()."\", ";
        $str .= "admin=\"".$this->get_admin()."\", ";
        return $str;
    }

    /**
    * get value of error_str attribute
    * @return string value of error_str attribute
    */
    function get_error_str ()
    {
        return $this->error_str;
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
    * @return bool value of SESSION variable edit_list.
    */
    function get_edit_list ()
    {
        return $_SESSION["edit_list"];
    }

    /**
    * get value of SESSION variable create_list.
    * @return bool value of SESSION variable create_list.
    */
    function get_create_list ()
    {
        return $_SESSION["create_list"];
    }

    /**
    * get value of SESSION variable admin.
    * @return bool value of SESSION variable admin.
    */
    function get_admin ()
    {
        return $_SESSION["admin"];
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
        $list_states = (array)$this->_json->decode(html_entity_decode($_SESSION["list_states"]), ENT_QUOTES);
        
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
    * set value of SESSION variable created
    * @param string $created datetime at which current user was created
    * @return void
    */
    function set_created ($created)
    {
        $_SESSION["created"] = $created;
    }
    
    /**
    * set value of SESSION variable edit_list
    * @param bool $permission indicates if current user is allowed to edit a list
    * @return void
    */
    function set_edit_list ($permission)
    {
        $_SESSION["edit_list"] = $permission;
    }

    /**
    * set value of SESSION variable create_list
    * @param bool $permission indicates if current user is allowed to create a new list
    * @return void
    */
    function set_create_list ($permission)
    {
        $_SESSION["create_list"] = $permission;
    }

    /**
    * set value of SESSION variable admin
    * @param bool $permission indicates if current user is has admin privileges
    * @return void
    */
    function set_admin ($permission)
    {
        $_SESSION["admin"] = $permission;
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
        $list_states = (array)$this->_json->decode(html_entity_decode($_SESSION["list_states"]), ENT_QUOTES);

        $list_states[$list_state->get_list_title()] = $list_state->pass();        
        $_SESSION["list_states"] = htmlentities($this->_json->encode($list_states), ENT_QUOTES);
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
        $this->set_edit_list("0");
        $this->set_create_list("0");
        $this->set_admin("0");
        $this->set_login("0");
    }

    /**
    * create new database table that contains all users
    * @return bool indicates if table has been created
    */
    function create ()
    {
        $this->_log->trace("creating User (table=".USER_TABLE_NAME.")");
        
        $query = "CREATE TABLE ".USER_TABLE_NAME." (";
        $query .= DB_ID_FIELD_NAME." ".DB_DATATYPE_ID.", ";
        $query .= "_name ".DB_DATATYPE_USERNAME.", ";
        $query .= "_pw ".DB_DATATYPE_PASSWORD.", ";
        $query .= DB_TS_CREATED_FIELD_NAME." ".DB_DATATYPE_DATETIME.", ";
        $query .= "_edit_list ".DB_DATATYPE_BOOL.", ";
        $query .= "_create_list ".DB_DATATYPE_BOOL.", ";
        $query .= "_admin ".DB_DATATYPE_BOOL.", ";
        $query .= "_times_login ".DB_DATATYPE_INT.", ";
        $query .= "_ts_last_login ".DB_DATATYPE_DATETIME.", ";
        $query .= "PRIMARY KEY (".DB_ID_FIELD_NAME."), ";
        $query .= "UNIQUE KEY _name (_name)) ";

        $result = $this->_database->query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not create table in database for user");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }
        
        $this->_log->trace("created table");

        return TRUE;
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
        
        $this->_log->trace("log in (name=".$name.")");
        
        if ($this->is_login())
        {
            $this->_log->warn("user already logged in (name=".$name.")");
            return FALSE;
        }
            
        # create user admin the first time user admin tries to login    
        if ($name == "admin" && !$this->exists("admin"))
        {
            $this->_log->info("first time login for admin");
            if (!$this->add($name, $firstthingsfirst_admin_passwd, 1, 1, 1))
                return FALSE;
        }

        $password = md5($pw);
        $query = "SELECT ".DB_ID_FIELD_NAME.", _name, _pw, ".DB_TS_CREATED_FIELD_NAME.", _edit_list, ";
        $query .= "_create_list, _admin, _times_login, _ts_last_login FROM ".USER_TABLE_NAME." WHERE _name=\"".$name."\"";
        $result = $this->_database->query($query);
        $row = $this->_database->fetch($result);
        
        if ($row != FALSE)
        {
            $db_id = $row[0];
            $db_name = $row[1];
            $db_password = $row[2];
            $db_created = $row[3];
            $db_edit_list = $row[4];
            $db_create_list = $row[5];
            $db_admin = $row[6];
            $times_login = $row[7] + 1;
            $last_login = $row[8];
            
            if ($db_password == $password)
            {
                # set session parameters
                $this->set_id($db_id);
                $this->set_name($db_name);
                $this->set_created($db_created);
                $this->set_edit_list($db_edit_list);
                $this->set_create_list($db_create_list);
                $this->set_admin($db_admin);
                $this->set_times_login($times_login);
                $this->set_login(1);
                
                # update the number of times this user has logged in
                $query = "UPDATE ".USER_TABLE_NAME." SET _times_login=\"".$times_login."\", _ts_last_login=\"".strftime(DB_DATETIME_FORMAT)."\" where _name=\"".$name."\"";
                $result = $this->_database->query($query);
                if ($result == FALSE)
                {
                    $this->_log->error("could not update _times_login (name=".$name.")");
                    $this->_log->error("database error: ".$this->_database->get_error_str());
                    $this->error_str = ERROR_DATABASE_PROBLEM;
                    
                    return FALSE;
                }
                else
                {
                    $this->_log->info("user logged in (name=".$name.")");
                
                    return TRUE;
                }
            }
            else
            {        
                $this->_log->warn("passwords do not match (name=".$name."), user is not logged in");
                $this->error_str = ERROR_INCORRECT_NAME_PASSWORD;
                
                return FALSE;
            }
        }
        else
        {
            $this->_log->warn("passwords do not match (name=".$name."), user is not logged in");
            $this->error_str = ERROR_INCORRECT_NAME_PASSWORD;
                    
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
        
        $this->_log->trace("log out");
        
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
        $query = "SELECT _name FROM ".USER_TABLE_NAME." WHERE _name=\"".$name."\"";
        $result = $this->_database->query($query);
        $row = $this->_database->fetch($result);
        
        $this->_log->trace("checking if user exists (name=".$name.")");
        
        if ($row != FALSE)
        {
            if ($row[0] == $name)
            {
                $this->_log->debug("user already exists (name=".$name.")");
                
                return TRUE;
            }
            else
            {
                $this->_log->debug("user does not exist (name=".$name.")");
                
                return FALSE;
            }
        }
        else if (strlen($this->_database->get_error_str()) == 0)
        {
            $this->_log->debug("user does not exist (name=".$name.")");
                
            return FALSE;
        }
        else
        {
            $this->_log->error("could not select (name=".$name.")");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }
    }                    

    /**
    * add a new user to database
    * @param string $name name of new user
    * @param string $pw password of new user
    * @param bool $edit_list indicates if new user is allowed to edit a list (FALSE if not provided)
    * @param bool $create_list indicates if current user is allowed to create a new list (FALSE if not provided)
    * @param bool $is_admin indicates if current user is has admin privileges (FALSE if not provided)
    * @return bool indicates if user has been added
    */
    function add ($name, $pw, $edit_list = 0, $create_list = 0, $is_admin = 0)
    {
        $password = md5($pw);

        $this->_log->trace("add user (name=".$name.")");
        
        # create table if it does not yet exists
        if (!$this->_database->table_exists(USER_TABLE_NAME))
            $this->create();
        
        # check if user already exists
        if ($this->exists($name))
        {
            $this->error_str = ERROR_DUPLICATE_USER_NAME;
            
            return FALSE;
        }

        $query = "INSERT INTO ".USER_TABLE_NAME." VALUES (0, \"".$name."\", \"".$password."\", \"".strftime(DB_DATETIME_FORMAT)."\", ";
        $query .= $edit_list.", ".$create_list.", ".$is_admin.", 0, \"".strftime(DB_DATETIME_FORMAT)."\")";
        $result = $this->_database->insertion_query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not add user (name=".$name.")");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            $this->error_str = ERROR_DATABASE_PROBLEM;
            
            return FALSE;
        }
        else
        {
            $this->_log->info("user added (name=".$name.")");
        
            return TRUE;
        }
    }
}

?>

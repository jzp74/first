<?php

# This class represents a user and handles login/logout as well as permissions
# This class contains now actual data. Data is only stored in session params


# User defines
define("USER_TABLE_NAME", $firstthingsfirst_db_table_prefix."user");
define("USER_ID_RESET_VALUE", -1);
define("USER_NAME_RESET_VALUE", "_");


# Class definition
# TODO improve use of trace/debug logging
# TODO add user created, last login date/time
class User
{
    # representing the list_table the user is currently using
    protected $_page_title;
    
    # reference to global database object
    protected $_database;

    # reference to global logging object
    protected $_log;
    
    # set attributes of this object when it is constructed
    function __construct ()
    {
        # these variables are assumed to be globally available
        global $logging;
        global $database;
        
        # set global references for this object
        $this->_log =& $logging;
        $this->_database =& $database;
        
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

    # return string representation of this object
    function __toString ()
    {
        $str = "User: id=\"".$this->get_id()."\", ";
        $str .= "name=\"".$this->get_name()."\", ";
        $str .= "times_login=\"".$this->get_times_login()."\", ";
        $str .= "edit_list=\"".$this->get_edit_list()."\", ";
        $str .= "create_list=\"".$this->get_create_list()."\", ";
        $str .= "admin=\"".$this->get_admin()."\", ";
        $str .= "list_obf=\"".$this->get_list_order_by_field()."\", ";
        $str .= "list_oasc=\"".$this->get_list_order_ascending()."\", ";
        return $str;
    }

    # getter
    function get_id ()
    {
        return $_SESSION["id"];
    }
    
    # getter
    function get_name ()
    {
        return $_SESSION["name"];
    }

    # getter
    function get_edit_list ()
    {
        return $_SESSION["edit_list"];
    }

    # getter
    function get_create_list ()
    {
        return $_SESSION["create_list"];
    }

    # getter
    function get_admin ()
    {
        return $_SESSION["admin"];
    }

    # getter
    function get_times_login ()
    {
        return $_SESSION["times_login"];
    }

    # getter
    function get_login ()
    {
        return $_SESSION["login"];
    }

    # getter
    function get_action ()
    {
        return $_SESSION["action"];
    }

    # getter
    function get_page_title ()
    {
        return $_SESSION["page_title"];
    }

    # getter
    function get_list_order_by_field ()
    {
        return $_SESSION["list_order_by_field"];
    }

    # getter
    function get_list_order_ascending ()
    {
        return $_SESSION["list_order_ascending"];
    }
    
    # getter
    function get_recent_url ()
    {
        return $_SESSION["recent_url"];
    }

    # setter
    function set_id ($id)
    {
        $_SESSION["id"] = $id;
    }
    
    # setter
    function set_name ($name)
    {
        $_SESSION["name"] = $name;
    }
    
    # setter
    function set_edit_list ($permission)
    {
        $_SESSION["edit_list"] = $permission;
    }

    # setter
    function set_create_list ($permission)
    {
        $_SESSION["create_list"] = $permission;
    }

    # setter
    function set_admin ($permission)
    {
        $_SESSION["admin"] = $permission;
    }

    # setter
    function set_times_login ($times_login)
    {
        $_SESSION["times_login"] = $times_login;
    }

    # setter
    function set_login ($login)
    {
        $_SESSION["login"] = $login;
    }

    # setter
    function set_action ($action)
    {
        $_SESSION["action"] = $action;
    }
    
    # setter
    function set_page_title ($title)
    {
        $_SESSION["page_title"] = $title;
    }

    # setter
    function set_list_order_by_field ($order)
    {
        $_SESSION["list_order_by_field"] = $order;
    }

    # setter
    function set_list_order_ascending ($ascending)
    {
        $_SESSION["list_order_ascending"] = $ascending;
    }
    
    # setter
    function set_recent_url ($recent_url)
    {
        $_SESSION["recent_url"];
    }

    # reset attributes to standard values
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
        $this->set_action(ACTION_GET_PORTAL);
        $this->set_page_title("-");
        $this->set_list_order_by_field("");
        $this->set_list_order_ascending(1);
    }

    # create the database table that contains all users
    function create ()
    {
        $this->_log->trace("creating User (table=".USER_TABLE_NAME.")");
        
        $query = "CREATE TABLE ".USER_TABLE_NAME." (";
        $query .= "_id INT NOT NULL AUTO_INCREMENT, ";
        $query .= "_name VARCHAR(20) NOT NULL, ";
        $query .= "_pw char(32) BINARY NOT NULL, ";
        $query .= "_edit_list INT NOT NULL, ";
        $query .= "_create_list INT NOT NULL, ";
        $query .= "_admin INT NOT NULL, ";
        $query .= "_times_login INT NOT NULL, ";
        $query .= "_last_login DATETIME NOT NULL, ";
        $query .= "PRIMARY KEY (_id), ";
        $query .= "UNIQUE KEY _name (_name)) ";

        $result = $this->_database->query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not create table in database for user");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            return FALSE;
        }
        
        $this->_log->trace("created table");

        return TRUE;
    }

    # check if user is really logged in
    function is_login ()
    {
        if (($this->get_login()) && ($this->get_id() != USER_ID_RESET_VALUE) && ($this->get_name() != USER_NAME_RESET_VALUE) && ($this->get_name() != ""))
            return TRUE;
        return FALSE;
    }

    # try to login specified user with specified password
    # restore user settings from database if name/password combination is valid
    function login ($name, $pw)
    {
        global $firstthingsfirst_db_passwd;
        
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
            if (!$this->add($name, $pw, 1, 1, 1))
                return FALSE;
        }

        $password = md5($pw);
        $query = "SELECT _id, _name, _pw, _edit_list, _create_list, _admin, _times_login, _last_login FROM ".USER_TABLE_NAME." WHERE _name=\"".$name."\"";
        $result = $this->_database->query($query);
        $row = $this->_database->fetch($result);
        
        if ($row != FALSE)
        {
            $db_id = $row[0];
            $db_name = $row[1];
            $db_password = $row[2];
            $db_edit_list = $row[3];
            $db_create_list = $row[4];
            $db_admin = $row[5];
            $times_login = $row[6] + 1;
            $last_login = $row[7];
            
            # obtain admin pw from localsettings
            if ($name == "admin")
                $db_password = md5($firstthingsfirst_db_passwd);
            
            if ($db_password == $password)
            {
                # set session parameters
                $this->set_id($db_id);
                $this->set_name($db_name);
                $this->set_edit_list($db_edit_list);
                $this->set_create_list($db_create_list);
                $this->set_admin($db_admin);
                $this->set_times_login($times_login);
                $this->set_login(1);
                
                # set other attributes of this class
                $this->set_action(ACTION_GET_PORTAL);
                $this->set_page_title("-");
                $this->set_list_order_by_field("");
                $this->set_list_order_ascending(1);
                
                # update the number of times this user has logged in
                $query = "UPDATE ".USER_TABLE_NAME." SET _times_login=\"".$times_login."\", _last_login=\"".strftime(DB_DATETIME_FORMAT)."\" where _name=\"".$name."\"";
                $result = $this->_database->query($query);
                if ($result == FALSE)
                {
                    $this->_log->error("could not update _times_login (name=".$name.")");
                    $this->_log->error("database error: ".$this->_database->get_error_str());
                }
                
                $this->_log->info("user logged in (name=".$name.")");
                
                return TRUE;
            }
                        
            $this->_log->warn("passwords do not match (name=".$name."), user is not logged in");
        }
        
        return FALSE;
    } 
    
    # logout current user
    function logout ()
    {
        $name = $this->get_name();
        
        $this->_log->trace("log out");
        
        $this->reset();

        $this->_log->info("user logged out (name=".$name.")");        
    }
    
    # return TRUE when user already exists
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

            $this->_log->debug("user does not exist (name=".$name.")");
            return FALSE;
        }
    }                    

    # add given user with given characteristics to database
    function add ($name, $pw, $edit_list = 0, $create_list = 0, $is_admin = 0)
    {
        $password = md5($pw);

        $this->_log->trace("add user (name=".$name.")");
        
        # create table if it does not yet exists
        if (!$this->_database->table_exists(USER_TABLE_NAME))
            $this->create();

        $query = "INSERT INTO ".USER_TABLE_NAME." VALUES (0, \"".$name."\", \"".$password."\", ";
        $query .= $edit_list.", ".$create_list.", ".$is_admin.", 0, \"".strftime(DB_DATETIME_FORMAT)."\")";
        $result = $this->_database->insertion_query($query);
        if ($result == FALSE)
        {
            $this->_log->error("could not add user (name=".$name.")");
            $this->_log->error("database error: ".$this->_database->get_error_str());
            return FALSE;
        }
        
        $this->_log->info("user added (name=".$name.")");
        
        return TRUE;
    }
}

?>

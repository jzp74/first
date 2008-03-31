<?php

/**
 * This file contains the class definition of ListState
 *
 * @package Class_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2008 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definition of archived (show non archived records)
 */
define("LISTSTATE_SELECT_NON_ARCHIVED", 1);

/**
 * definition of archived (show only archived records)
 */
define("LISTSTATE_SELECT_ARCHIVED", 2);

/**
 * definition of archived (show both archived and non archived records)
 */
define("LISTSTATE_SELECT_BOTH_ARCHIVED", 3);


/**
 * This class contains the state of a specific list
 * List specific data is passed to User object and stored in session
 *
 * @package Class_FirstThingsFirst
 */
class ListState
{
    /**
    * list title
    * @var string
    */
    protected $list_title;
    
    /**
    * field by which this list is ordered
    * @var string
    */
    protected $order_by_field;
    
    /**
    * order list ascending by order_by_field when value is TRUE
    * @var bool
    */
    protected $order_ascending;

    /**
    * indicates if only archived records should be selected
    * @var bool
    */
    protected $archived;
        
    /**
    * selection filter str (human readable)
    * @var array
    */
    protected $filter_str;

    /**
    * selection filter str (sql 'where' clause string)
    * @var array
    */
    protected $filter_str_sql;

    /**
    * total number of records
    * @var int
    */
    protected $total_records;

    /**
    * total number of pages
    * @var int
    */
    protected $total_pages;

    /**
    * current page
    * @var int
    */
    protected $current_page;
    
    /**
    * reference to global logging object
    * @var Logging
    */
    protected $_log;

    /**
    * overwrite __construct() function
    * @return void
    */
    function __construct ()
    {
        # these variables are assumed to be globally available
        global $logging;
        
        # set global references for this object
        $this->_log =& $logging;

        $this->reset();
        
        $this->_log->debug("constructed new ListState object");
    }

    /**
    * get value of list_title attribute
    * @return string value of list_title attribute
    */
    function get_list_title ()
    {
        return $this->list_title;
    }

    /**
    * get value of order_by_field attribute
    * @return string value of order_by_field attribute
    */
    function get_order_by_field ()
    {
        return $this->order_by_field;
    }

    /**
    * get value of order_ascending attribute
    * @return bool value of order_ascending attribute
    */
    function get_order_ascending ()
    {
        return $this->order_ascending;
    }

    /**
    * get value of archived attribute
    * @return string value of archived attribute
    */
    function get_archived ()
    {
        return $this->archived;
    }

    /**
    * get value of filter_str attribute
    * @return array value of filter_str_sql attribute
    */
    function get_filter_str ()
    {
        return $this->filter_str;
    }

    /**
    * get value of filter_str_sql attribute
    * @return array value of filter_str_sql attribute
    */
    function get_filter_str_sql ()
    {
        $this->_log->debug("get (filter_str_sql=".$this->filter_str_sql.")");
        return $this->filter_str_sql;
    }

    /**
    * get value of total_records attribute
    * @return int value of total_records attribute
    */
    function get_total_records ()
    {
        return $this->total_records;
    }

    /**
    * get value of total_pages attribute
    * @return int value of total_pages attribute
    */
    function get_total_pages ()
    {
        return $this->total_pages;
    }

    /**
    * get value of current_page attribute
    * @return int value of current_page attribute
    */
    function get_current_page ()
    {
        return $this->current_page;
    }
    
    /**
    * set value of list_title attribute
    * @param string $list_title value of list_title attribute
    * @return void
    */
    function set_list_title ($list_title)
    {
        $this->list_title = $list_title;
    }
    
    /**
    * set value of order_by_field attribute
    * @param string $order_by_field value of order_by_field attribute
    * @return void
    */
    function set_order_by_field ($order_by_field)
    {
        $this->order_by_field = $order_by_field;
    }

    /**
    * set value of order_ascending attribute
    * @param bool $order_ascending value of order_ascending attribute
    * @return void
    */
    function set_order_ascending ($order_ascending)
    {
        $this->order_ascending = $order_ascending;
    }

    /**
    * set value of archived attribute
    * @param int $archived value of archived attribute
    * @return void
    */
    function set_archived ($archived)
    {
        $this->archived = $archived;
    }
    
    /**
    * set value of filter_str attribute
    * @param int filter_str value of filter_str attribute
    * @return void
    */
    function set_filter_str ($filter_str)
    {
        $this->filter_str = $filter_str;
    }
    
    /**
    * set value of filter_str_sql attribute
    * @param int filter_str_sql value of filter_str_sql attribute
    * @return void
    */
    function set_filter_str_sql ($filter_str_sql)
    {
        $this->_log->debug("set (filter_str_sql=".$filter_str_sql.")");
        $this->filter_str_sql = $filter_str_sql;
    }
    
    /**
    * set value of total_records attribute
    * @param int $total_records value of total_records attribute
    * @return void
    */
    function set_total_records ($total_records)
    {
        $this->total_records = $total_records;
    }
    
    /**
    * set value of total_pages attribute
    * @param int $total_pages value of total_pages attribute
    * @return void
    */
    function set_total_pages ($total_pages)
    {
        $this->total_pages = $total_pages;
    }
    
    /**
    * set value of current_page attribute
    * @param int $current_page value of current_page attribute
    * @return void
    */
    function set_current_page ($current_page)
    {
        $this->current_page = $current_page;
    }
    
    /**
    * reset attributes to initial values
    * @return void
    */
    function reset ()
    {
        $this->_log->trace("resetting ListState");

        $this->list_title = "no title has been set";
        $this->order_by_field = "";
        $this->order_ascending = 1;
        $this->archived = LISTSTATE_SELECT_NON_ARCHIVED;
        $this->filter_str = "";
        $this->filter_str_sql = "";
        $this->total_records = 0;
        $this->total_pages = 1;
        $this->current_page = 1;
    }
    
    /**
    * set attributes passed by User object (initiate this object)
    * @param string $list_title title of list this object is associated with
    * @param array $list_state_array array containing attribute values for this object
    * @return void
    */
    function set ($list_title, $list_state_array)
    {
        $this->_log->trace("setting ListState (title=".$list_title.")");

        $this->list_title = $list_title;
        $this->order_by_field = $list_state_array['order_by_field'];
        $this->order_ascending = $list_state_array['order_ascending'];
        $this->archived = $list_state_array['archived'];
        $this->filter_str = $list_state_array['filter_str'];
        $this->filter_str_sql = $list_state_array['filter_str_sql'];
        $this->total_records = $list_state_array['total_records'];
        $this->total_pages = $list_state_array['total_pages'];
        $this->current_page = $list_state_array['current_page'];

        $this->_log->trace("set ListState (order=".$list_state_array['order_by_field'].", asc=".$list_state_array['order_ascending'].", page=".$list_state_array['current_page'].")");
    }
    
    /**
    * pass attributes to User class
    * @return array $list_state_array array containing attribute values of this object
    */
    function pass ()
    {
        $list_state_array = array();
        
        $this->_log->trace("passing ListState (title=".$this->list_title.", order=".$this->order_by_field.", asc=".$this->order_ascending.", page=".$this->current_page.")");

        $list_state_array['order_by_field'] = $this->order_by_field;
        $list_state_array['order_ascending'] = $this->order_ascending;
        $list_state_array['archived'] = $this->archived;
        $list_state_array['filter_str'] = $this->filter_str;
        $list_state_array['filter_str_sql'] = $this->filter_str_sql;
        $list_state_array['total_records'] = $this->total_records;
        $list_state_array['total_pages'] = $this->total_pages;
        $list_state_array['current_page'] = $this->current_page;
        
        $this->_log->trace("passed ListState");
        
        return $list_state_array;
    }
    
}

?>

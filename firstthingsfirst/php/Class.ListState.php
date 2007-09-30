<?php

# this class contains the state of a specific list
# list specific data is passed to user class and stored in a cookie
class ListState
{
    # list title
    protected $list_title;
    
    # list is ordered by this field
    protected $order_by_field;

    # list is ordered ascending when value of this attribute
    protected $order_ascending;
    
    # current list page which is shown
    protected $current_page;
    
    # total number of pages
    protected $total_pages;

    # reference to global json object
    protected $_json;
    
    # reference to global logging object
    protected $_log;

    # set attributes of this object when it is constructed
    function __construct ()
    {
        # these variables are assumed to be globally available
        global $json;
        global $logging;
        
        # set global references for this object
        $this->_json =& $json;
        $this->_log =& $logging;

        $this->reset();
        
        $this->_log->trace("constructed new ListState object");
    }

    # getter
    function get_list_title ()
    {
        return $this->list_title;
    }

    # getter
    function get_order_by_field ()
    {
        return $this->order_by_field;
    }

    # getter
    function get_order_ascending ()
    {
        return $this->order_ascending;
    }

    # getter
    function get_total_pages ()
    {
        return $this->total_pages;
    }

    # getter
    function get_current_page ()
    {
        return $this->current_page;
    }
    
    # setter
    function set_list_title ($list_title)
    {
        $this->list_title = $list_title;
    }
    
    # setter
    function set_order_by_field ($order_by_field)
    {
        $this->order_by_field = $order_by_field;
    }

    # setter
    function set_order_ascending ($order_ascending)
    {
        $this->order_ascending = $order_ascending;
    }

    # setter
    function set_total_pages ($total_pages)
    {
        $this->total_pages = $total_pages;
    }
    
    # setter
    function set_current_page ($current_page)
    {
        $this->current_page = $current_page;
    }
    
    # reset attributes to initial values
    function reset ()
    {
        $this->_log->trace("resetting ListState");

        $this->list_title = "no title has been set";
        $this->order_by_field = "";
        $this->order_ascending = 1;
        $this->total_pages = 1;
        $this->current_page = 1;
    }
    
    # set attributes passed by User class
    function set ($list_title, $list_state_array)
    {
        $this->_log->trace("setting ListState (title=".$list_title.")");

        $this->list_title = $list_title;
        $this->order_by_field = $list_state_array['order_by_field'];
        $this->order_ascending = $list_state_array['order_ascending'];
        $this->total_pages = $list_state_array['total_pages'];
        $this->current_page = $list_state_array['current_page'];

        $this->_log->trace("set ListState (order=".$list_state_array['order_by_field'].", asc=".$list_state_array['order_ascending'].", page=".$list_state_array['current_page'].")");
    }
    
    # pass attributes to User class
    function pass ()
    {
        $list_state_array = array();
        
        $this->_log->trace("passing ListState (title=".$this->list_title.", order=".$this->order_by_field.", asc=".$this->order_ascending.", page=".$this->current_page.")");

        $list_state_array['order_by_field'] = $this->order_by_field;
        $list_state_array['order_ascending'] = $this->order_ascending;
        $list_state_array['total_pages'] = $this->total_pages;
        $list_state_array['current_page'] = $this->current_page;
        
        return $list_state_array;
    }
    
}

?>

<?php

/**
 * This file contains the class definition of UserDatabaseTable
 *
 * @package Class_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007-2012 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * This class inherits all DatabaseTable class functionality and uses the User and ListState classes
 *
 * @package Class_FirstThingsFirst
 */
class UserDatabaseTable extends DatabaseTable
{
    /**
    * json object
    * @var Services_JSON
    */
    protected $_json;

    /**
    * reference to global list_state object
    * @todo: move this var to DatabaseTable
    * @var ListState
    */
    protected $_list_state;

    /**
    * reference to global user object
    * @var User
    */
    protected $_user;

    /**
     * reference to global user_list_permissions object
     * @var Database
     */
    protected $_user_list_permissions;

    /**
    * overwrite __construct() function
    * @param $table_name string table name of this DatabaseTable object
    * @param $fields array array containing all database fields of this DatabaseTable object
    * @param $metadat_str string string indicating which metadata should be stored for this DatabaseTable object
    * @return void
    */
    function __construct ($table_name, $fields, $metadata_str)
    {
        # these variables are assumed to be globally available
        global $list_state;
        global $user;
        global $user_list_permissions;

        # call parent __construct()
        parent::__construct($table_name, $fields, $metadata_str);

        # set global references for this object
        $this->_list_state =& $list_state;
        $this->_user =& $user;
        $this->_user_list_permissions =& $user_list_permissions;

        $this->_json = new Services_JSON();

        self::reset();

        $this->_log->debug("constructed UserDatabaseTable (table_name=".$this->table_name.", metadata_str=".$metadata_str.")");
    }

    /**
    * reset attributes to initial values (parent reset() is called by parent construct())
    * @return void
    */
    function reset ()
    {
        $this->_log->trace("resetting UserDatabaseTable");

        $this->_list_state->reset();
    }

    /**
    * select a fixed number of records from database
    * @todo filter sql settings also include note fields. note fields should not be known in this file.
    * @param $order_by_field string order records by this db_field_name
    * @param $page int the page number to select
    * @param $db_field_names array array containing db_field_names to select for each record
    * @return array array containing the records (each records is an array)
    */
    function select ($order_by_field, $page, $db_field_names = array())
    {
        $this->_log->trace("selecting UserDatabaseTable (order_by_field=".$order_by_field.", page=".$page.", db_field_names=".count($db_field_names).")");

        # get lines per page from session
        $lines_per_page = $this->_user->get_lines_per_page();

        # get list_state from session
        $this->_user->get_list_state($this->table_name);

        # get previous field order
        $prev_order_by_field = $this->_list_state->get_order_by_field();

        if (strlen($order_by_field) == 0)
        {
            # no order_by_field had been given
            if (strlen($this->_list_state->get_order_by_field()) > 0)
            {
                # order by previously given field
                $order_by_field = $prev_order_by_field;
            }
            else
            {
                # no field to order by has been given previously
                # order by first field that has a non empty field_name of this UserDatabaseTable
                foreach ($this->db_field_names as $db_field_name)
                {
                    if ((strlen($this->fields[$db_field_name][0]) > 0) && ($this->fields[$db_field_name][2] != ID_COLUMN_NO_SHOW))
                    {
                        # set different db_field_names for automatic creator and modifier fields
                        if ($this->fields[$db_field_name][1] == FIELD_TYPE_DEFINITION_AUTO_CREATED)
                            $order_by_field = DB_TS_CREATED_FIELD_NAME;
                        else if ($this->fields[$db_field_name][1] == FIELD_TYPE_DEFINITION_AUTO_MODIFIED)
                            $order_by_field = DB_TS_MODIFIED_FIELD_NAME;
                        else
                            $order_by_field = $db_field_name;
                        break;
                    }
                }
                $this->_list_state->set_order_by_field($order_by_field);
                $this->_list_state->set_order_ascending(1);
            }
        }
        else
        {
            # order by field has been provided
            # set order by field attribute value and reverse order
            $this->_list_state->set_order_by_field($order_by_field);

            # only change sort order when user sorts by same field as previous time
            if ($order_by_field == $prev_order_by_field)
            {
                if ($this->_list_state->get_order_ascending())
                    $this->_list_state->set_order_ascending(0);
                else
                    $this->_list_state->set_order_ascending(1);
            }
            # set fixed sort order when user sorts by differen field
            else
                $this->_list_state->set_order_ascending(1);
        }

        $order_ascending = $this->_list_state->get_order_ascending();
        $archived = $this->_list_state->get_archived();
        $filter_str_sql = $this->_list_state->get_filter_str_sql();

        if ($page == DATABASETABLE_UNKWOWN_PAGE)
            $page = $this->_list_state->get_current_page();

        # call parent select()
        $rows = parent::select($order_by_field, $order_ascending, $archived, $filter_str_sql, $page, $lines_per_page, $db_field_names);

        if ($page != DATABASETABLE_ALL_PAGES)
            $this->_list_state->set_current_page($page);
        if (count($rows) > 0)
        {
            $this->_list_state->set_total_records($rows[0][DB_TOTAL_RECORDS]);
            $this->_list_state->set_total_pages($rows[0][DB_TOTAL_PAGES]);
            $this->_list_state->set_current_page($rows[0][DB_CURRENT_PAGE]);
        }
        else
        {
            $this->_list_state->set_total_records(0);
            $this->_list_state->set_total_pages(0);
            $this->_list_state->set_current_page(0);
        }

        # store list_state to session
        $this->_user->set_list_state();

        $this->_log->trace("selected UserDatabaseTable");

        return $rows;
    }

    /**
    * add a new record to database
    * @param $name_values_array array array containing name-values of the record
    * @return int number indicates the id of the new record or 0 when no record was added
    */
    function insert ($name_values_array)
    {
        $this->_log->trace("inserting record into UserDatabaseTable");

        # call parent insert()
        $result = parent::insert($name_values_array, $this->_user->get_name());
        if ($result == 0)
            return 0;

        $this->_log->trace("inserted record into UserDatabaseTable (result=".$result.")");

        return $result;
    }

    /**
    * update an existing record in database
    * @param $encoded_key_string string unique identifier of record
    * @param $name_values array array containing new name-values of record
    * @return bool indicates if record has been updated
    */
    function update ($encoded_key_string, $name_values_array = array())
    {
        $this->_log->trace("updating record from UserDatabaseTable (encoded_key_string=".$encoded_key_string.")");

        # call parent update()
        if (parent::update($encoded_key_string, $this->_user->get_name(), $name_values_array) == FALSE)
            return FALSE;

        $this->_log->trace("updated record from UserDatabaseTable");

        return TRUE;
    }

    /**
    * archive an existing record in database
    * @param $encoded_key_string string unique identifier of record to be archived
    * @return bool indicates if record has been archived
    */
    function archive ($encoded_key_string)
    {
        $this->_log->trace("archiving record from UserDatabaseTable (encoded_key_string=".$encoded_key_string.")");

        # call parent archive()
        if (parent::archive($encoded_key_string, $this->_user->get_name()) == FALSE)
            return FALSE;

        $this->_log->trace("archived record from UserDatabaseTable");

        return TRUE;
    }

    /**
     * activate an existing record in database
     * @param $encoded_key_string string unique identifier of record to be archived
     * @return bool indicates if record has been archived
     */
    function activate ($encoded_key_string)
    {
        $this->_log->trace("activating record from UserDatabaseTable (encoded_key_string=".$encoded_key_string.")");

        # call parent archive()
        if (parent::activate($encoded_key_string, $this->_user->get_name()) == FALSE)
            return FALSE;

        $this->_log->trace("activated record from UserDatabaseTable");

        return TRUE;
    }

}

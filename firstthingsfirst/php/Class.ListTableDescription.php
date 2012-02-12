<?php

/**
 * This file contains the class definition of ListTableDescription
 *
 * @package Class_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007-2012 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definition of database table name
 */
define("LISTTABLEDESCRIPTION_TABLE_NAME", $firstthingsfirst_db_table_prefix."listtabledescription");

/**
 * definition of title field name
 */
define("LISTTABLEDESCRIPTION_TITLE_FIELD_NAME", "_title");

/**
 * definition of description field name
 */
define("LISTTABLEDESCRIPTION_DESCRIPTION_FIELD_NAME", "_description");

/**
 * definition of active fields field name
 */
define("LISTTABLEDESCRIPTION_ACTIVE_RECORDS_FIELD_NAME", "_active_records");

/**
 * definition of archived fields field name
 */
define("LISTTABLEDESCRIPTION_ARCHIVED_RECORDS_FIELD_NAME", "_archived_records");

/**
 * definition of name and date of creator field name
 */
define("LISTTABLEDESCRIPTION_CREATOR_FIELD_NAME", "_name_date_creator");

/**
 * definition of name and dat of modifier field name
 */
define("LISTTABLEDESCRIPTION_MODIFIER_FIELD_NAME", "_name_date_modifier");

/**
 * @todo is_key_field is obsolete
 * definition of definition field name
 * this is an array containing the definition of a ListTable
 * this array is of the following structure:
 *   field_name => (field_type, field_options)
 * a definition is stored as a json string
 */
define("LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME", "_definition");

/**
 * definition of fields
 */
$class_listtabledescription_fields = array(
    DB_ID_FIELD_NAME => array("", FIELD_TYPE_DEFINITION_AUTO_NUMBER, ""),
    LISTTABLEDESCRIPTION_TITLE_FIELD_NAME => array("LABEL_LIST_NAME", FIELD_TYPE_DEFINITION_TEXT_LINE, DATABASETABLE_UNIQUE_FIELD),
    LISTTABLEDESCRIPTION_DESCRIPTION_FIELD_NAME => array("LABEL_LIST_DESCRIPTION", FIELD_TYPE_DEFINITION_TEXT_FIELD, ""),
    LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME => array("", FIELD_TYPE_DEFINITION_TEXT_FIELD, ""),
    LISTTABLEDESCRIPTION_ACTIVE_RECORDS_FIELD_NAME => array("LABEL_LIST_ACTIVE_RECORDS", FIELD_TYPE_DEFINITION_NUMBER, NUMBER_COLUMN_NO_SUMMATION),
    LISTTABLEDESCRIPTION_ARCHIVED_RECORDS_FIELD_NAME => array("LABEL_LIST_ARCHIVED_RECORDS", FIELD_TYPE_DEFINITION_NUMBER, NUMBER_COLUMN_NO_SUMMATION),
    LISTTABLEDESCRIPTION_CREATOR_FIELD_NAME => array("", FIELD_TYPE_DEFINITION_AUTO_CREATED, NAME_DATE_OPTION_DATE_NAME),
    LISTTABLEDESCRIPTION_MODIFIER_FIELD_NAME => array("LABEL_LIST_MODIFIER", FIELD_TYPE_DEFINITION_AUTO_MODIFIED, NAME_DATE_OPTION_DATE_NAME)
);

/**
 * definition of metadata
 */
define("LISTTABLEDESCRIPTION_METADATA","-11");


/**
 * This class represents the description of a user defined list
 *
 * @package Class_FirstThingsFirst
 */
class ListTableDescription extends UserDatabaseTable
{
    /**
    * overwrite __construct() function
    * @return void
    */
    function __construct ()
    {
        # these variables are assumed to be globally available
        global $class_listtabledescription_fields;

        # call parent __construct()
        parent::__construct(LISTTABLEDESCRIPTION_TABLE_NAME, $class_listtabledescription_fields, LISTTABLEDESCRIPTION_METADATA);

        $this->_log->debug("constructed new ListTableDescription object");
    }

    /**
    * select a fixed number of records from database
    * @param $order_by_field string order records by this db_field_name
    * @param $page int the page number to select
    * @return array array containing the records (each records is an array)
    */
    function select ($order_by_field, $page, $db_field_names = array())
    {
        $this->_log->trace("selecting ListTableDescription (order_by_field=".$order_by_field.", page=".$page.")");

        # get list_state from session
        $this->_user->get_list_state($this->table_name);

        # set filter to select only listst for which current user has at least view permission
        $filter_str_sql = "(".LISTTABLEDESCRIPTION_TITLE_FIELD_NAME." IN (SELECT DISTINCT ".USERLISTTABLEPERMISSIONS_LISTTABLE_TITLE_FIELD_NAME;
        $filter_str_sql .= " FROM ".USERLISTTABLEPERMISSIONS_TABLE_NAME." WHERE ".USERLISTTABLEPERMISSIONS_USER_NAME_FIELD_NAME;
        $filter_str_sql .= "='".$this->_user->get_name()."' AND ".USERLISTTABLEPERMISSIONS_CAN_VIEW_LIST_FIELD_NAME."=1))";

        # store list_state to session
        $this->_list_state->set_filter_str_sql($filter_str_sql);
        $this->_user->set_list_state();

        $records = parent::select($order_by_field, $page, $db_field_names);
        if (count($records) == 0)
            return array();

        $new_records = array();
        foreach ($records as $record)
        {
            # convert value
            if (array_key_exists(LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME, $record) == TRUE)
                $record[LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME] = (array)$this->_json->decode($record[LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME]);
            array_push($new_records, $record);
        }

        $this->_log->trace("selected ListTableDescription");

        return $new_records;
    }

    /**
    * select a specific ListTableDescription object
    * @param $title string title of ListTableDescription
    * @return array array containing the ListTableDescription object
    */
    function select_record ($title)
    {
        $this->_log->trace("selecting ListTableDescription record (title=".$title.")");

        # create encoded_key_string
        parent::_encode_key_string($encoded_key_string = LISTTABLEDESCRIPTION_TITLE_FIELD_NAME."='".$title."'");

        $record = parent::select_record($encoded_key_string);
        if (count($record) == 0)
            return array();

        # convert value
        if (array_key_exists(LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME, $record) == TRUE)
            $record[LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME] = (array)$this->_json->decode($record[LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME]);

        $this->_log->trace("selected ListTableDescription record (title=\"".$title."\")");

        return $record;
    }

    /**
    * add new ListTableDescription object to database
    * @param array $name_values_array values of new ListTableDescription
    * @return bool indicates if ListTableDescription has been added
    */
    function insert ($name_values_array)
    {
        $title = $name_values_array[LISTTABLEDESCRIPTION_TITLE_FIELD_NAME];

        $this->_log->trace("inserting ListTableDescription (title=".$title.")");

        $record = parent::select_record(LISTTABLEDESCRIPTION_TITLE_FIELD_NAME."='".$title."'");
        if (count($record) > 0)
        {
            $this->_handle_error("this is a duplicate list", "ERROR_DUPLICATE_LIST_NAME");

            return FALSE;
        }

        # convert value
        if (array_key_exists(LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME, $name_values_array) == TRUE)
            $name_values_array[LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME] = $this->_json->encode($name_values_array[LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME]);

        if (parent::insert($name_values_array) == 0)
            return FALSE;

        if ($this->_user_list_permissions->insert_list_permissions_new_list($title) == FALSE)
        {
            # copy error strings from user_list_permissions
            $this->error_message_str = $this->_user_list_permissions->get_error_message_str();
            $this->error_log_str = $this->_user_list_permissions->get_error_log_str();
            $this->error_str = $this->_user_list_permissions->get_error_str();

            return FALSE;
        }

        $this->_log->trace("inserted ListTableDescription (title=".$title.")");

        return TRUE;
    }

    /**
    * update ListTableDescription object in database
    * @param string $title title of ListTableDescription
    * @param $name_values array array containing new name-values of record
    * @return bool indicates if ListTableDescription has been updated
    */
    function update ($title, $name_values_array = array())
    {
        $this->_log->trace("updating ListTableDescription in database (title=".$title.")");

        # create encoded_key_string
        $encoded_key_string = parent::_encode_key_string(LISTTABLEDESCRIPTION_TITLE_FIELD_NAME."='".$title."'");

        # convert value
        if (array_key_exists(LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME, $name_values_array) == TRUE)
            $name_values_array[LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME] = $this->_json->encode($name_values_array[LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME]);

        if (parent::update($encoded_key_string, $name_values_array) == FALSE)
            return FALSE;

        # update list title in user_list_permissions
        if (array_key_exists(LISTTABLEDESCRIPTION_TITLE_FIELD_NAME, $name_values_array) == TRUE)
        {
            # create key string for user_list_permissions
            $permission_key_string = USERLISTTABLEPERMISSIONS_LISTTABLE_TITLE_FIELD_NAME."='".$title."'";

            # create array with new title
            $new_title_array = array();
            $new_title_array[USERLISTTABLEPERMISSIONS_LISTTABLE_TITLE_FIELD_NAME] = $name_values_array[LISTTABLEDESCRIPTION_TITLE_FIELD_NAME];

            if ($this->_user_list_permissions->update($permission_key_string, $new_title_array) == FALSE)
            {
                # copy error strings from user_list_permissions
                $this->error_message_str = $this->_user_list_permissions->get_error_message_str();
                $this->error_log_str = $this->_user_list_permissions->get_error_log_str();
                $this->error_str = $this->_user_list_permissions->get_error_str();

                return FALSE;
            }
        }

        $this->_log->trace("updated ListTableDescription (title=".$title.")");

        return TRUE;
    }

    /**
    * delete ListTableDescription object from database
    * this function also deletes the ListTable that is connected to current object
    * @param string $title title of ListTableDescription
    * @return bool indicates if ListTableDescription has been deleted
    */
    function delete ($title)
    {
        $this->_log->trace("deleting ListTableDescription from database (title=".$title.")");

        # create encoded_key_string
        $encoded_key_string = parent::_encode_key_string(LISTTABLEDESCRIPTION_TITLE_FIELD_NAME."='".$title."'");

        if (parent::delete($encoded_key_string) == FALSE)
            return FALSE;

        # create key string for user_list_permissions
        $permission_key_string = USERLISTTABLEPERMISSIONS_LISTTABLE_TITLE_FIELD_NAME."='".$title."'";

        if ($this->_user_list_permissions->delete($permission_key_string) == FALSE)
        {
            # copy error strings from user_list_permissions
            $this->error_message_str = $this->_user_list_permissions->get_error_message_str();
            $this->error_log_str = $this->_user_list_permissions->get_error_log_str();
            $this->error_str = $this->_user_list_permissions->get_error_str();

            return FALSE;
        }

        $this->_log->trace("deleted ListTableDescription (title=".$title.")");

        return TRUE;
    }

}

?>

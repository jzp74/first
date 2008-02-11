<?php

/**
 * This file contains the class definition of ListTableDescription
 *
 * @package Class_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2008 Jasper de Jong
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
 * @todo is_key_field is obsolete
 * definition of definition field name
 * this is an array containing the definition of a ListTable
 * this array is of the following structure:
 *   field_name => (field_type, is_key_field, field_options)
 * a definition is stored as a json string
 */
define("LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME", "_definition");

/**
 * definition of fields
 */
$class_listtabledescription_fields = array(
    DB_ID_FIELD_NAME => array(LABEL_LIST_ID, "LABEL_DEFINITION_AUTO_NUMBER", ""),
    LISTTABLEDESCRIPTION_TITLE_FIELD_NAME => array(LABEL_LIST_NAME, "LABEL_DEFINITION_TEXT_LINE", DATABASETABLE_UNIQUE_FIELD),
    LISTTABLEDESCRIPTION_DESCRIPTION_FIELD_NAME => array(LABEL_LIST_DESCRIPTION, "LABEL_DEFINITION_TEXT_FIELD", ""),
    LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME => array(LABEL_LIST_DEFINITION, "LABEL_DEFINITION_TEXT_FIELD", "")
);

/**
 * definition of metadata
 */
define("LISTTABLEDESCRIPTION_METADATA", "-11");


/**
 * This class represents the description of a user defined list
 *
 * @package Class_FirstThingsFirst
 */
class ListTableDescription extends UserDatabaseTable
{
    /**
    * reference to global list_state object
    * @var ListState
    */
    protected $_list_table;
    
    /**
    * overwrite __construct() function
    * @return void
    */
    function __construct ()
    {
        # these variables are assumed to be globally available
        global $list_table;
        
        # call parent __construct()
        parent::__construct();
        
        # set global references for this object
        $this->_list_table =& $list_table;

        $this->set();

        $this->_log->trace("constructed new ListTableDescription object");
    }
                
    /**
    * set attributes to initial values
    * @return void
    */
    function set ()
    {
        global $class_listtabledescription_fields;
        
        $this->_log->trace("setting ListTableDescription");
        
        # call parent set()
        parent::set(LISTTABLEDESCRIPTION_TABLE_NAME, $class_listtabledescription_fields, LISTTABLEDESCRIPTION_METADATA);

        $this->_log->trace("set ListTableDescription");
    }

    /**
    * select a fixed number of records from database
    * @param $order_by_field string order records by this db_field_name
    * @param $page int the page number to select
    * @return array array containing the records (each records is an array)
    */
    function select ($order_by_field, $page)
    {
        $this->_log->trace("selecting ListTableDescription (order_by_field=".$order_by_field.", page=".$page.")");

        $rows = parent::select($order_by_field, $page);
        if (count($rows) == 0)
            return array();
        
        $new_rows = array();
        foreach ($rows as $row)
        {            
            # convert several values
            $row[LISTTABLEDESCRIPTION_DESCRIPTION_FIELD_NAME] = html_entity_decode($row[LISTTABLEDESCRIPTION_DESCRIPTION_FIELD_NAME], ENT_QUOTES);
            $row[LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME] = (array)$this->_json->decode(html_entity_decode($row[LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME], ENT_QUOTES));
            array_push($new_rows, $row);
        }
        
        $this->_log->trace("selected ListTableDescription");
        
        return $new_rows;
    }

    /**
    * select a specific ListTableDescription object
    * @param $title string title of ListTableDescription
    * @return array array containing the ListTableDescription object
    */
    function select_row ($title)
    {
        $this->_log->trace("selecting ListTableDescription row (title=".$title.")");
        
        # create key_string
        $key_string = LISTTABLEDESCRIPTION_TITLE_FIELD_NAME."='".$title."'";
        
        $row = parent::select_row($key_string);
        if (count($row) == 0)
            return array();
        
        # convert several values
        $row[LISTTABLEDESCRIPTION_DESCRIPTION_FIELD_NAME] = html_entity_decode($row[LISTTABLEDESCRIPTION_DESCRIPTION_FIELD_NAME], ENT_QUOTES);
        $row[LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME] = (array)$this->_json->decode(html_entity_decode($row[LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME], ENT_QUOTES));

        $this->_log->trace("selected ListTableDescription row (title=\"".$title."\")");

        return $row;
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
        
        $row = parent::select_row(LISTTABLEDESCRIPTION_TITLE_FIELD_NAME."='".$title."'");
        if (count($row) > 0)
        {
            $this->_log->error("this is a duplicate list");
            $this->error_str = ERROR_DUPLICATE_LIST_NAME;
            
            return FALSE;
        }

        # convert several values
        $name_values_array[LISTTABLEDESCRIPTION_DESCRIPTION_FIELD_NAME] = htmlentities($name_values_array[LISTTABLEDESCRIPTION_DESCRIPTION_FIELD_NAME], ENT_QUOTES);
        $name_values_array[LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME] = htmlentities($this->_json->encode($name_values_array[LISTTABLEDESCRIPTION_DEFINITION_FIELD_NAME], ENT_QUOTES));

        if (parent::insert($name_values_array) == FALSE)
            return FALSE;
        
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

        # create key_string
        $key_string = LISTTABLEDESCRIPTION_TITLE_FIELD_NAME."='".$title."'";

        if (parent::update($key_string, $name_values_array) == FALSE)
            return FALSE;        
                                    
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
        $this->_log->trace("deleting ListTableDescription from database (title=".title.")");

        # create key_string
        $key_string = LISTTABLEDESCRIPTION_TITLE_FIELD_NAME."='".$title."'";

        if (parent::delete($key_string) == FALSE)
            return FALSE;        
            
        $this->_log->trace("deleted ListTableDescription (title=".$title.")");

        return TRUE;
    }
    
}

?>

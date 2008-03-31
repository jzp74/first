<?php

/**
 * This file contains the class definition of HtmlDatabaseTable
 *
 * @package Class_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2008 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definition of page type
 */
define("HTML_TABLE_PAGE_TYPE", "html_table_page_type");

/**
 * definition of prefix for each css class and each js function
 */
define("HTML_TABLE_JS_NAME_PREFIX", "html_table_js_name_prefix");

/**
 * definition of prefix for each css class and each js function
 */
define("HTML_TABLE_CSS_NAME_PREFIX", "html_table_css_name_prefix");

/**
 * definition of delete mode
 */
define("HTML_TABLE_DELETE_MODE", "html_table_delete_mode");

/**
 * definition of delete mode always (always show delete button)
 */
define("HTML_TABLE_DELETE_MODE_ALWAYS", 0);

/**
 * definition of delete mode archived (only show delete button for archived records)
 */
define("HTML_TABLE_DELETE_MODE_ARCHIVED", 1);

/**
 * definition of record name
 */
define("HTML_TABLE_RECORD_NAME", "html_table_record_name");


/**
 * This class provides a html presentation of a DatabaseTable object
 *
 * @package Class_FirstThingsFirst
 */

class HtmlDatabaseTable
{    
    /**
     * configuration of this HtmlTable
     * @var array
     */
    protected $configuration;
    
    /**
     * reference to databasetable object
     * @var DatabaseTable
     */
    protected $_database_table;
    
    /**
     * reference to global user object
     * @var User
     */
    protected $_user;

    /**
     * reference to global list_state object
     * @var ListState
     */
    protected $_list_state;

    /**
     * reference to global logging object
     * @var Logging
     */
    protected $_log;

    /**
     * overwrite __construct() function
     * @param $configuration array configuration of this HtmlListTable object
     * @param $database_table DatabaseTable reference to an existing DatabaseTable object
     * @return void
     */
    function __construct ($configuration, &$database_table)
    {
        # these variables are assumed to be globally available
        global $user;
        global $list_state;
        global $logging;        
        
        $this->configuration = $configuration;

        # set global references for this object
        $this->_user =& $user;
        $this->_log =& $logging;
        $this->_list_state =& $list_state;
        $this->_database_table =& $database_table;
        
        $this->_log->debug("constructed new HtmlTable object");
    }
    
    /**
     * get the html (use Result object) for a database table
     * @param $list_title string title of list
     * @param $explanation string user explanation
     * @param $result Result result object
     * @return xajaxResponse every xajax registered function needs to return this object
     */
    function get_page ($title, $explanation, $result)
    {
        $html_str = "";

        $this->_log->trace("getting page (title=".$title.")");

        $html_str .= "\n\n        <div id=\"hidden_upper_margin\">something to fill space</div>\n\n";
        $html_str .= "        <div id=\"page_title\">".$title."</div>\n\n";
        if (strlen($explanation) > 0)
            $html_str .= "        <div id=\"page_explanation\">".$explanation."</div>\n\n";
        $html_str .= "        <div id=\"navigation_container\">\n";
        
        # add various navigation links
        $portal_link = FALSE;
        $admin_link = FALSE;
        $html_str .= "            <div id=\"navigation\">";
        if ($this->configuration[HTML_TABLE_PAGE_TYPE] != PAGE_TYPE_PORTAL)
            $portal_link = TRUE;
        if (($this->_user->is_login() && $this->_user->get_is_admin()) && $this->configuration[HTML_TABLE_PAGE_TYPE] != PAGE_TYPE_USER_ADMIN)
            $admin_link = TRUE;
        if ($portal_link || $admin_link)
            $html_str .= "|&nbsp;";
        if ($portal_link)
            $html_str .= get_query_href("action=get_portal_page", BUTTON_PORTAL)."&nbsp;|&nbsp;";
        if ($admin_link)
            $html_str .= get_query_href("action=get_user_admin_page", BUTTON_USER_ADMINISTRATION)."&nbsp;|&nbsp;";
            
        $html_str .= "</div>\n";
        
        $html_str .= "            <div id=\"login_status\">&nbsp;</div>&nbsp\n";
        $html_str .= "        </div> <!-- navigation_container -->\n\n";    
        $html_str .= "        <div id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."content_pane\">\n\n";
        $html_str .= "        </div> <!-- ".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."content_pane -->\n\n";
        $html_str .= "        <div id=\"action_pane\">\n\n";
        $html_str .= "        </div> <!-- action_pane -->\n\n";           
        $html_str .= "        <div id=\"hidden_lower_margin\">something to fill space</div>\n\n    ";

        $result->set_result_str($html_str);    
    
        $this->_log->trace("got content");
    }

    /**
     * get the html (use Result object) for a database table to print
     * @param $list_title string title of list
     * @param $result Result result object
     * @return void
     */
    function get_print_page ($title, $result)
    {
        $html_str = "";

        $this->_log->trace("getting print page (title=".$title.")");
    
        $html_str .= "\n\n        <div id=\"hidden_upper_margin\">something to fill space</div>\n\n";
        $html_str .= "        <div id=\"page_title\">".$title."</div>\n\n";
        $html_str .= "        <div id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."content_pane\">\n\n";
        $html_str .= "        </div> <!-- ".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."content_pane -->\n\n";
        $html_str .= "        <div id=\"hidden_lower_margin\">something to fill space</div>\n\n    ";

        $result->set_result_str($html_str);
    
        $this->_log->trace("getting print page (title=".$title.")");

        return;
    }

    /**
     * get html (use Result object) for the records of a databasetable
     * @param $list_title string title of list
     * @param $order_by_field string name of field by which this list needs to be ordered
     * @param $page int page to be shown (show first page when 0 is given)
     * @param $result Result result object
     * @return void
     */
    function get_content ($list_title, $order_by_field, $page, $result)
    {
        global $firstthingsfirst_list_page_entries;
        
        $html_str = "";
        $field_names = $this->_database_table->get_user_field_names();
        $fields = $this->_database_table->get_fields();
        $user_fields = $this->_database_table->get_user_fields();
        $metadata_str = $this->_database_table->get_metadata_str();

        $this->_log->trace("get content ($list_title=".$list_title.", $page=".$page.")");
    
        # select entries
        $records = $this->_database_table->select($order_by_field, $page);

        if (strlen($this->_database_table->get_error_str()) > 0)
        {
            $result->set_error_str($this->_database_table->get_error_str());
            $result->set_error_element($this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."content_pane");
            # no return statement here because we want the complete page to be displayed
        }
    
        # get list_state properties
        $this->_user->get_list_state($this->_database_table->get_table_name());
        $total_pages = $this->_list_state->get_total_pages();
        if ($total_pages == 0)
        {
            $total_records = 0;
            $current_page = 0;
        }
        else
        {
            $total_records = $this->_list_state->get_total_records();
            $current_page = $this->_list_state->get_current_page();
        }

        # add contents top
        $html_str .= "\n\n            <div id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."contents_top\">\n";
        if ($page != DATABASETABLE_ALL_PAGES)
        {
            $archive_select = FALSE;
            $filter = FALSE;
            
            # add record summary
            if ($current_page == 0)
            {
                $first_record = 0;
                $last_record = 0;
            }
            else
            {
                $first_record = ((((int)$current_page - 1) * $firstthingsfirst_list_page_entries) + 1);
                $last_record = (($first_record + count($records)) - 1);
            }
            $html_str .= "                <div id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."pages_top\">";
            $html_str .= LABEL_RECORDS." ".$first_record." - ";
            $html_str .= $last_record." ".LABEL_OF." ".$total_records." ".LABEL_RECORDS."</div>\n";
            # add archive select mechanism only when list supports archived records
            if ($metadata_str[DATABASETABLE_METADATA_ENABLE_ARCHIVE] != DATABASETABLE_METADATA_FALSE)
            {
                $html_str .= $this->get_archive_select($list_title);
                $archive_select = TRUE;
            }
            # add filter only for lists
            if ($this->configuration[HTML_TABLE_PAGE_TYPE] == PAGE_TYPE_LIST)
            {
                $html_str .= $this->get_filter($list_title);
                $filter = TRUE;
            }
            if (!$archive_select && !$filter)
                $html_str .= "                &nbsp;\n";
        }
        else
            $html_str .= "                &nbsp;\n";        
        $html_str .= "            </div> <!-- ".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."contents_top -->\n\n";

        # start with the table definition
        # this is a different table when all pages have to be displayed
        $html_str .= "            <table id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."contents\" align=\"left\">\n";
    
        # now the first record containing the field names
        $html_str .= "                <thead>\n";
        $html_str .= "                    <tr>\n";
        $field_names_with_length = array();
        foreach ($field_names as $field_name)
        {
            # replace all space chars with &nbsp
            $field_name_replaced = str_replace(' ', '&nbsp;', $field_name);

            # only display field names that have a length
            if (strlen($field_name) > 0)
            {
                $db_field_name = $user_fields[$field_name];
                $html_str .= "                        <th onclick=\"xajax_action_get_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."content('".$list_title."', '".$db_field_name."', ".$current_page.")\">".$field_name_replaced."</th>\n";
                array_push($field_names_with_length, $field_name);
            }
        }
        
        $html_str .= "                    </tr>\n";
        $html_str .= "                </thead>\n";
        $html_str .= "                <tbody>\n";
        
        # now all the records
        $record_number = 0;
        foreach ($records as $record)
        {
            # build key string for this record
            $key_string = $this->_database_table->_get_key_string($record);
            $key_values_string = $this->_database_table->_get_key_values_string($record);
    
            $html_str .= "                    <tr id=\"".$key_values_string."\">\n";
            
            $col_number = 0;
            foreach ($field_names_with_length as $field_name)
            {
                $db_field_name = $user_fields[$field_name];
                $value = $record[$db_field_name];
                if ($this->configuration[HTML_TABLE_PAGE_TYPE] != PAGE_TYPE_PORTAL)
                    $onclick_str = "onclick=\"xajax_action_get_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."record('".$list_title."', &quot;".$key_string."&quot;)\"";
                else
                    $onclick_str = "onclick=\"window.location='index.php?action=get_list_page&list=".$record[LISTTABLEDESCRIPTION_TITLE_FIELD_NAME]."'\"";
            
                if (stristr($fields[$db_field_name][1], "DATE"))
                {
                    $date_string = get_date_str(DATE_FORMAT_WEEKDAY, $value);
                    $html_str .= "                        <td ".$onclick_str.">".$date_string."</td>\n";
                }
                else if ($fields[$db_field_name][1] == "LABEL_DEFINITION_NOTES_FIELD")
                {
                    $html_str .= "                        <td ".$onclick_str.">";
                    if (count($value) > 0)
                    {
                        $html_str .= "\n";
                        foreach ($value as $note_array)
                        {
                            $html_str .= "                            <p>".$note_array[DB_CREATOR_FIELD_NAME]."&nbsp;".LABEL_AT."&nbsp;";
                            $html_str .= get_date_str(DATE_FORMAT_NORMAL, $note_array[DB_TS_CREATED_FIELD_NAME]).": ";
                            $html_str .= $note_array["_note"]."</p>\n";
                        }
                    }
                    else
                        $html_str .= "-";
                    $html_str .= "                        </td>\n";
                }
                else if ($fields[$db_field_name][1] == "LABEL_DEFINITION_TEXT_FIELD")
                {
                    $html_str .= "                        <td ".$onclick_str.">".nl2br($value)."</td>\n";
                }            
                else
                {
                    $html_str .= "                        <td ".$onclick_str.">".$value."</td>\n";
                }
                $col_number += 1;
            }
        
            # only add buttons when not all pages need to be displayed at once
            if ($page != DATABASETABLE_ALL_PAGES)
            {
                # add buttons for normal lists
                if ($this->configuration[HTML_TABLE_PAGE_TYPE] == PAGE_TYPE_PORTAL)
                {
                    # add the archive button only when this record is not archived
                    if (($metadata_str[DATABASETABLE_METADATA_ENABLE_ARCHIVE] != DATABASETABLE_METADATA_FALSE) && (strlen($record[DB_ARCHIVER_FIELD_NAME]) == 0))
                    {
                        $html_str .= "                        <td width=\"1%\">";
                        $html_str .= get_button("xajax_action_archive_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."record('".$list_title."', &quot;".$key_string."&quot;)", BUTTON_ARCHIVE);
                        $html_str .= "</td>\n";
                    }
                    # add the delete link when it should always be displayed
                    if ($this->configuration[HTML_TABLE_DELETE_MODE] == HTML_TABLE_DELETE_MODE_ALWAYS)
                    {
                        $html_str .= "                        <td width=\"1%\">";
                        $html_str .= get_button("xajax_action_delete_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."record('".$list_title."', &quot;".$key_string."&quot;)", BUTTON_DELETE);
                        $html_str .= "</td>\n";
                    }
                    # or add the delete link when record is archived
                    else if ((strlen($record[DB_ARCHIVER_FIELD_NAME]) > 0) && ($this->configuration[HTML_TABLE_DELETE_MODE] == HTML_TABLE_DELETE_MODE_ARCHIVED))
                    {
                        $html_str .= "                        <td width=\"1%\">";
                        $html_str .= get_button("xajax_action_delete_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."record('".$list_title."', &quot;".$key_string."&quot;)", BUTTON_DELETE);
                        $html_str .= "</td>\n";
                    }
                    $html_str .= "                    </tr>\n";
                }
            }            
            # add buttons for portal page
            if ($this->configuration[HTML_TABLE_PAGE_TYPE] == PAGE_TYPE_PORTAL)
            {
                # add modify button
                $html_str .= "                        <td width=\"1%\">";
                $html_str .= get_query_button("action=get_listbuilder_page&list=".$record[LISTTABLEDESCRIPTION_TITLE_FIELD_NAME], BUTTON_MODIFY);
                $html_str .= "</td>\n";
                # add delete button
                $html_str .= "                        <td width=\"1%\">";
                $html_str .= get_button_confirm("xajax_action_delete_portal_record('".$record[LISTTABLEDESCRIPTION_TITLE_FIELD_NAME]."')", LABEL_CONFIRM_DELETE, BUTTON_DELETE);
                $html_str .= "</td>\n                    </tr>\n";
            }
            
            $record_number += 1;
        }
    
        if ($total_pages == 0)
        {
            $html_str .= "                    <tr>\n";
            foreach ($field_names_with_length as $field_name)
                $html_str .= "                        <td>".LABEL_MINUS."</td>\n";
                $html_str .= "                        <td>&nbsp</td>\n";
            $html_str .= "                    </tr>\n";
        }
    
        # end table definition
        $html_str .= "                </tbody>\n";
        $html_str .= "            </table>\n\n";
        
        
        # add navigation links, except when all pages have to be shown
        $html_str .= "            <div id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."contents_bottom\">\n";
        if ($page != DATABASETABLE_ALL_PAGES)
        {
            $html_str .= "                <div id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."pages_bottom\">";
            # display 1 pagenumber when there is only one page (or none)
            if ($total_pages == 0 || $total_pages == 1)
            {
                $html_str .= LABEL_PAGE.": <strong>".$total_pages."</strong>";
            }
            # pagenumber display algorithm for 2 or more pages
            else
            {
                # display previous page link
                if ($current_page > 1)
                    $html_str .= get_href("xajax_action_get_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."content('".$list_title."', '', ".($current_page - 1).")", "&laquo;&nbsp;".BUTTON_PREVIOUS_PAGE)."&nbsp;&nbsp;";
        
                # display first pagenumber
                if ($current_page == 1)
                    $html_str .= " <strong>1</strong>";
                else
                    $html_str .= " ".get_href("xajax_action_get_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."content('".$list_title."', '', 1)", 1);
                # display middle pagenumbers
                for ($cnt = 2; $cnt<$total_pages; $cnt += 1)
                {
                    if ($cnt == ($current_page - 2))
                        $html_str .= " <strong>...<strong>";
                    else if ($cnt == ($current_page - 1))
                        $html_str .= " ".get_href("xajax_action_get_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."content('".$list_title."', '', ".$cnt.")", $cnt);
                    else if ($cnt == $current_page)
                        $html_str .= " <strong>".$cnt."</strong>";
                    else if ($cnt == ($current_page + 1))
                        $html_str .= " ".get_href("xajax_action_get_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."content('".$list_title."', '', ".$cnt.")", $cnt);
                    if ($cnt == ($current_page + 2))
                        $html_str .= " <strong>...<strong>";
                }
                # display last pagenumber
                if ($current_page == $total_pages)
                    $html_str .= "  <strong>".$total_pages."</strong>";
                else
                    $html_str .= "  ".get_href("xajax_action_get_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."content('".$list_title."', '', ".$total_pages.")", $total_pages);
    
                # display next page link
                if ($current_page < $total_pages)
                    $html_str .= "&nbsp;&nbsp;".get_href("xajax_action_get_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."content('".$list_title."', '', ".($current_page + 1).")", BUTTON_NEXT_PAGE."&nbsp;&raquo;");        
            }
            $html_str .= "</div>\n";
        }
        else
            $html_str .= "                &nbsp;\n";        
        $html_str .= "            </div> <!-- ".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."contents_bottom -->\n\n        ";
        
        $result->set_result_str($html_str);

        $this->_log->trace("got content");
    }
    
    /**
     * get html (use Result object) of one specified record
     * @param $list_title string title of list
     * @param $key_string string comma separated name value pairs
     * @param $result Result result object
     * @return void
     */
    function get_record ($list_title, $key_string, $result)
    {
        global $firstthingsfirst_field_descriptions;
        global $firstthingsfirst_date_string;
    
        $html_str = "";
        $field_names = $this->_database_table->get_user_field_names();
        $fields = $this->_database_table->get_fields();
    
        $this->_log->trace("getting record (list_title=".$list_title.", key_string=".$key_string.")");

        # get list record when key string has been given
        if (strlen($key_string) > 0)
        {
            $record = $this->_database_table->select_record($key_string);
            if (strlen($this->_database_table->get_error_str()) > 0)
            {
                $result->set_error_str($list_table->get_error_str());
                $result->set_error_element("".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."content_pane");
                # no return statement here because we want the complete page to be displayed
            }
        }

        # start with the action bar
        if (strlen($key_string))
            $html_str .= $this->get_action_bar($list_title, "edit");
        else
            $html_str .= $this->get_action_bar($list_title, "insert");
       
        # then the form and table definition
        $html_str .= "\n                <div id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."record_contents_pane\">\n";
        $html_str .= "                    <form name=\"record_form_name\" id=\"record_form\">\n";
        $html_str .= "                        <table id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."record_contents\" align=\"left\" border=\"0\" cellspacing=\"2\">\n";
        $html_str .= "                            <tbody>\n";

        # add table record for each field type
        for ($i=0; $i<count($field_names); $i++)
        {
            $field_name = $field_names[$i];
            $user_fields = $this->_database_table->get_user_fields();
            $db_field_name = $user_fields[$field_name];        
            $field_type = $fields[$db_field_name][1];
            $field_options = $fields[$db_field_name][2];
            $this->_log->debug("record (name=".$field_name." db_name=".$db_field_name." type=".$field_type.")");
        
            # set empty string if field does not exist (key_string was not set)
            if (strlen($key_string) == 0)
                $record[$db_field_name] = "";

            # replace all " chars with &quot
            $record[$db_field_name] = str_replace('"', '&quot', $record[$db_field_name]);
        
            # replace all space chars with &nbsp
            $field_name_replaced = str_replace(' ', '&nbsp;', $field_names[$i]);

            # only add non auto_increment field types (check database definition for this)
            if (!stristr($firstthingsfirst_field_descriptions[$field_type][0], "auto_increment"))
            {
                $html_str .= "                                <tr id=\"".$db_field_name."\">\n";
                $html_str .= "                                    <th>".$field_name_replaced."</th>\n";
            
                if ($field_type != "LABEL_DEFINITION_NOTES_FIELD")
                {
                    $html_str .= "                                    <td id=\"".$db_field_name.GENERAL_SEPARATOR.$field_type.GENERAL_SEPARATOR."0";
                    $html_str .= "\"><".$firstthingsfirst_field_descriptions[$field_type][1];
                    # create a name tag
                    $html_str .= " name=".$db_field_name.GENERAL_SEPARATOR.$field_type.GENERAL_SEPARATOR."0";
                }
            
                # set values from database
                if (strlen($key_string))
                {
                    if ($field_type == "LABEL_DEFINITION_BOOL")
                    {
                        if ($record[$db_field_name] == "1")
                            $html_str .= " checked";
                    }
                    else if (stristr($field_type, "DATE"))
                    {
                        $date_string = get_date_str(DATE_FORMAT_NORMAL, $record[$db_field_name]);
                        $html_str .= " value=\"".$date_string."\"";
                    }
                    else if ($field_type == "LABEL_DEFINITION_TEXT_FIELD")
                        $html_str .= ">".$record[$db_field_name]."</textarea";
                    else if ($field_type == "LABEL_DEFINITION_PASSWORD")
                        $html_str .= " value=\"\"";
                    else if ($field_type == "LABEL_DEFINITION_SELECTION")
                    {
                        $html_str .= ">";
                        $option_list = explode("|", $field_options);
                        foreach ($option_list as $option)
                        {
                            $html_str .= "\n                                        <option value=\"".$option."\"";
                            if ($option == $record[$db_field_name])
                                $html_str .= " selected";
                            $html_str .= ">".$option."</option>";
                        }
                        $html_str .= "\n                                    </select";
                    }
                    else if ($field_type == "LABEL_DEFINITION_NOTES_FIELD")
                    {
                        $html_str .= get_list_record_notes($db_field_name, $record[$db_field_name]);
                    }
                    else
                        $html_str .= " value=\"".$record[$db_field_name]."\"";
                }
                # set initial values
                else
                {
                    if ($field_type == "LABEL_DEFINITION_AUTO_DATE")
                        $html_str .= " value=\"".strftime($firstthingsfirst_date_string)."\"";
                    else if ($field_type == "LABEL_DEFINITION_NON_EDIT_NUMBER")
                        $html_str .=  " value=\"0\"";
                    else if ($field_type == "LABEL_DEFINITION_TEXT_FIELD")
                        $html_str .= "></textarea";
                    else if ($field_type == "LABEL_DEFINITION_NOTES_FIELD")
                        $html_str .= get_list_record_notes($db_field_name, array());
                    else if ($field_type == "LABEL_DEFINITION_SELECTION")
                    {
                        $html_str .= ">";
                        $option_list = explode("|", $field_options);
                        foreach ($option_list as $option)
                            $html_str .= "\n                                        <option value=\"".$option."\">".$option."</option>\n";
                        $html_str .= "\n                                    </select";
                    }
                    else
                        $html_str .= " value=\"\"";
                }
                if ($field_type != "LABEL_DEFINITION_NOTES_FIELD")
                    $html_str .= "></td>\n";
                    $html_str .= "                                    <td class=\"super_width\">&nbsp;</td>\n";
                $html_str .= "                                </tr>\n";
            }
        }
        
        $html_str .= "                                <tr align=\"left\">\n";
        $html_str .= "                                    <td>&nbsp;</td>\n";
        $html_str .= "                                </tr>\n";

        # end table definition
        $html_str .= "                            </tbody>\n";
        $html_str .= "                        </table> <!-- ".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."record_contents -->\n";

        # add link to confirm contents to database
        $html_str .= "                        ";
        if (!strlen($key_string))
            $html_str .= get_button("xajax_action_insert_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."record('".$list_title."', xajax.getFormValues('record_form'))", BUTTON_ADD);
        else
            $html_str .= get_button("xajax_action_update_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."record('".$list_title."', &quot;".$key_string."&quot;, xajax.getFormValues('record_form'))", BUTTON_COMMIT);

        $html_str .= "&nbsp;&nbsp;".get_button("xajax_action_cancel_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."action ('".$list_title."')", BUTTON_CANCEL);

        #end form
        $html_str .= "                    </form> <!-- record_form -->\n";
        $html_str .= "                </div> <!-- ".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."record_contents_pane -->\n\n            ";
    
        $result->set_result_str($html_str);    

        $this->_log->trace("got record");
    }

    /**
     * get html for action bar
     * @param $list_title string title of list
     * @param $action string highlight given action in the action bar (highlight none when action is empty)
     * @param $result Result result object
     * @return string returned html
     */
    function get_action_bar ($list_title, $action)
    {
        $this->_log->trace("get action bar (list_title=".$list_title.", action=".$action.")");

        $html_str = "";

        $html_str .= "\n            <div id=\"action_bar\" align=\"left\" valign=\"top\">\n";
        $html_str .= "                ";
        
        if ($this->configuration[HTML_TABLE_PAGE_TYPE] != PAGE_TYPE_PORTAL)
        {
            if ($action == "edit")
                $html_str .= "<strong>".LABEL_EDIT_RECORD."</strong>";
            else if ($action == "insert")
                $html_str .= "<strong>".LABEL_ADD_RECORD."</strong>";
            else
                $html_str .= get_href("xajax_action_get_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."record('".$list_title."', '')", BUTTON_ADD_RECORD.$this->configuration[HTML_TABLE_RECORD_NAME]);
            if ($this->configuration[HTML_TABLE_PAGE_TYPE] == PAGE_TYPE_LIST)
            {
                $html_str .= "&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"javascript:void(0);\" onclick=\"window.open('http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
                $html_str .= "?action=get_list_print_page&list=".$list_title."')\">".BUTTON_PRINT_LIST."</a>";
            }
        }
        else
            $html_str .= get_query_href("action=get_listbuilder_page", BUTTON_CREATE_NEW_LIST);
            
        $html_str .= "\n            </div> <!-- action_bar -->\n        ";
    
        $this->_log->trace("got action bar");
    
        return $html_str;
    }

    /**
     * get html for the archive records selector
     * @param $list_title string title of list
     * @return string returned html
     */
    function get_archive_select ($list_title)
    {
        $this->_log->trace("get archive select");

        $html_str = "";
        
        $this->_user->get_list_state($this->_database_table->get_table_name());
        $archived = $this->_list_state->get_archived();
        
        $html_str .= "                <div id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."archive_select\">".LABEL_DISPLAY."&nbsp;\n";
        $html_str .= "                    <select id=\"archive_select\"";
        $html_str .= " onChange=\"xajax_action_set_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."archive('".$list_title."', document.getElementById('archive_select').value);\">\n";
        $html_str .= "                        <option value=\"".LISTSTATE_SELECT_NON_ARCHIVED."\"";
        if ($archived == LISTSTATE_SELECT_NON_ARCHIVED)
            $html_str .= " selected";
        $html_str .= ">".LABEL_NORMAL_RECORDS."</option>\n";
        $html_str .= "                        <option value=\"".LISTSTATE_SELECT_ARCHIVED."\"";
        if ($archived == LISTSTATE_SELECT_ARCHIVED)
            $html_str .= " selected";
        $html_str .= ">".LABEL_ARCHIVED_RECORDS."</option>\n";
        $html_str .= "                    </select>\n";
        $html_str .= "                </div>\n";    

        $this->_log->trace("got archive select");
    
        return $html_str;        
    }        

    /**
     * get html for filter
     * @param $list_title string title of list
     * @return string returned html
     */
    function get_filter ($list_title)
    {
        $this->_log->trace("get filter");

        $html_str = "";
        
        $this->_user->get_list_state($this->_database_table->get_table_name());
        $filter_str = $this->_list_state->get_filter_str();
        
        $html_str .= "                <div id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."filter\">\n";
        $html_str .= "                    <form name=\"filter_form_name\" id=\"filter_form\" ";
        $html_str .= "onsubmit=\"javascript:xajax_action_set_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."filter('".$list_title."', document.getElementById('filter_str').value); return false;\">\n";
        $html_str .= "                        ".LABEL_FILTER."&nbsp;&nbsp;<input size=\"34\" maxlength=\"100\" value=\"".$filter_str."\" id=\"filter_str\">&nbsp;&nbsp;";
        $html_str .= "<input type=submit class=\"button\" value=\"".BUTTON_FILTER."\">\n";
        $html_str .= "                    </form>\n";
        $html_str .= "                </div>\n";

        $this->_log->trace("got filter");
    
        return $html_str;        
    }            

}

?>

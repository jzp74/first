<?php

/**
 * This file contains the class definition of HtmlDatabaseTable
 *
 * @package Class_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007-2009 Jasper de Jong
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
 * definition of delete mode never (never show delete button)
 */
define("HTML_TABLE_DELETE_MODE_NEVER", 2);

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
    function __construct ($configuration)
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

        # set permissions_list_title
        if (($this->configuration[HTML_TABLE_PAGE_TYPE] == PAGE_TYPE_LIST) || ($this->configuration[HTML_TABLE_PAGE_TYPE] == PAGE_TYPE_USERLISTTABLEPERMISSIONS))
            $this->permissions_list_title = $this->_user->get_current_list_name();
        else
            $this->permissions_list_title = HTML_EMPTY_LIST_TITLE;

        $this->_log->debug("constructed new HtmlTable object");
    }

    /**
     * set appropriate variables of given result object
     * @param $result Result result object
     * @param $error_element string id of element that will contain the error
     * @return void
     */
    function _handle_error ($database_table, $result, $element_id)
    {
        $result->set_error_message_str($database_table->get_error_message_str());
        $result->set_error_log_str($database_table->get_error_log_str());
        $result->set_error_str($database_table->get_error_str());
        $result->set_error_element($element_id);
    }

    /**
     * get the html (use Result object) for a database table
     * @param $list_title string title of list
     * @param $result Result result object
     * @return xajaxResponse every xajax registered function needs to return this object
     */
    function get_page ($list_title, $result)
    {
        $html_str = "";

        $this->_log->trace("getting page (title=".$list_title.")");

        $html_str .= "\n\n        <div id=\"hidden_upper_margin\">something to fill space</div>\n\n";
        $html_str .= "        <div class=\"white_area\"></div>\n\n";
        $html_str .= "        <div id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."content_pane\">\n\n";
        $html_str .= "        </div> <!-- ".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."content_pane -->\n\n";
        $html_str .= "        <div id=\"".MESSAGE_PANE_DIV."\">\n";
        $html_str .= "        &nbsp;";
        $html_str .= "        </div> <!-- ".MESSAGE_PANE_DIV." -->\n\n";
        $html_str .= "        <div id=\"action_pane\">\n\n";
        $html_str .= "        </div> <!-- action_pane -->\n\n";
        $html_str .= "        <div class=\"white_area\"></div>\n\n";
        $html_str .= "        <div id=\"hidden_lower_margin\">something to fill space</div>\n\n    ";

        $result->set_result_str($html_str);

        $this->_log->trace("got content");
    }

    /**
     * get the html (use Result object) for a database table to print
     * @param $result Result result object
     * @return void
     */
    function get_print_page ($result)
    {
        $html_str = "";

        $this->_log->trace("getting print page (title=".$list_title.")");

        $html_str .= "\n\n        <div id=\"hidden_upper_margin\">something to fill space</div>\n\n";
        $html_str .= "        <div id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."content_pane\">\n\n";
        $html_str .= "        </div> <!-- ".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."content_pane -->\n\n";
        $html_str .= "        <div id=\"".MESSAGE_PANE_DIV."\">\n";
        $html_str .= "        </div> <!-- ".MESSAGE_PANE_DIV." -->\n\n";
        $html_str .= "        <div id=\"hidden_lower_margin\">something to fill space</div>\n\n    ";

        $result->set_result_str($html_str);

        $this->_log->trace("got print page (title=".$title.")");

        return;
    }

    /**
     * get html (use Result object) for the records of a databasetable
     * @param $database_table DatabaseTable database table object
     * @param $list_title string title of list
     * @param $order_by_field string name of field by which this list needs to be ordered
     * @param $page int page to be shown (show first page when 0 is given)
     * @param $result Result result object
     * @return void
     */
    function get_content ($database_table, $list_title, $order_by_field, $page, $result)
    {
        global $firstthingsfirst_list_page_entries;

        $html_str = "";
        $field_names = $database_table->get_user_field_names();
        $fields = $database_table->get_fields();
        $user_fields = $database_table->get_user_fields();
        $metadata_str = $database_table->get_metadata_str();

        $this->_log->trace("get content (list_title=".$list_title.", page=".$page.")");

        # select entries
        $records = $database_table->select($order_by_field, $page);

        if (strlen($database_table->get_error_message_str()) > 0 && $database_table->get_error_message_str() != translate("ERROR_DATABASE_EXISTENCE"))
        {
            # only show an error when this is not the portal page
            if ($this->configuration[HTML_TABLE_PAGE_TYPE] != PAGE_TYPE_PORTAL)
                $this->_handle_error($database_table, $result, MESSAGE_PANE_DIV);
            # no return statement here because we want the complete page to be displayed
        }

        # get list_state properties
        $this->_user->get_list_state($database_table->get_table_name());
        $total_pages = $this->_list_state->get_total_pages();
        $order_by_field = $this->_list_state->get_order_by_field();
        $order_ascending = $this->_list_state->get_order_ascending();
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
        $html_str .= "\n\n            <div id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."contents_top_left\">\n";
        $html_str .= "                <div id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."contents_top_right\">\n";
        $html_str .= "                    <div id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."contents_top\">&nbsp;\n";
        if ($page != DATABASETABLE_ALL_PAGES)
        {
            $archive_select = FALSE;
            $filter = FALSE;

            # add archive select mechanism only when list supports archived records
            if ($metadata_str[DATABASETABLE_METADATA_ENABLE_ARCHIVE] != DATABASETABLE_METADATA_FALSE)
            {
                $html_str .= $this->get_archive_select($database_table, $list_title);
                $archive_select = TRUE;
            }

            # add filter only for lists
            if ($this->configuration[HTML_TABLE_PAGE_TYPE] == PAGE_TYPE_LIST)
            {
                $html_str .= $this->get_filter($database_table, $list_title);
                $filter = TRUE;
            }
            if (!$archive_select && !$filter)
                $html_str .= "                        &nbsp;\n";

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
            $html_str .= "                        <div id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."pages_top\">";
            $html_str .= translate("LABEL_RECORDS")." ".$first_record." - ";
            $html_str .= $last_record." ".translate("LABEL_OF")." ".$total_records." ".translate("LABEL_RECORDS")."</div>\n";
        }
        else
            $html_str .= "                        &nbsp;\n";

        $html_str .= "                    </div> <!-- ".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."contents_top -->\n";
        $html_str .= "                </div> <!-- ".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."contents_top_right -->\n";
        $html_str .= "            </div> <!-- ".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."contents_top_left -->\n\n";

        # start with the table definition
        # this is a different table when all pages have to be displayed
        $html_str .= "            <table id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."contents\" align=\"left\">\n";

        # now the first record containing the field names
        $html_str .= "                <thead>\n";
        $html_str .= "                    <tr>\n";
        $field_names_with_length = array();
        foreach ($field_names as $field_name)
        {
            $db_field_name = $user_fields[$field_name];

            # replace all space chars with &nbsp
            $field_name_replaced = str_replace(' ', '&nbsp;', $field_name);

            # translate field_name when this is not a list table
            if ($this->configuration[HTML_TABLE_PAGE_TYPE] != PAGE_TYPE_LIST)
                $field_name_replaced = str_replace(' ', '&nbsp;', translate($field_name));

            # only display field names that have a length
            if ((strlen($field_name) > 0) && ($fields[$db_field_name][2] != ID_COLUMN_NO_SHOW))
            {
                $sort_name = $user_fields[$field_name];
                # change names to sort by for automatic creator and modifier fields
                if ($fields[$sort_name][1] == FIELD_TYPE_DEFINITION_AUTO_CREATED)
                    $sort_name = DB_TS_CREATED_FIELD_NAME;
                else if ($fields[$sort_name][1] == FIELD_TYPE_DEFINITION_AUTO_MODIFIED)
                    $sort_name = DB_TS_MODIFIED_FIELD_NAME;

                # set class name to determine arrow image
                $class_name = "database_table_contents_header_sort";

                if ($order_by_field == $sort_name)
                {
                    if ($order_ascending)
                        $class_name .= "_up";
                    else
                        $class_name .= "_down";
                }

                $html_str .= "                        <th ";
                $html_str .= "onclick=\"xajax_action_get_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX];
                $html_str .= "content('".$list_title."', '".$sort_name."', ".$current_page.")\"><div class=\"".$class_name."\">";
                # add some blanks for the arrow images
                $html_str .= $field_name_replaced."&nbsp;&nbsp;&nbsp;</div></th>\n";

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
            $encoded_key_string = $database_table->_get_encoded_key_string($record);
            $key_values_string = $database_table->_get_key_values_string($record);

            $html_str .= "                    <tr id=\"".$key_values_string."\">\n";

            $col_number = 0;
            foreach ($field_names_with_length as $field_name)
            {
                $db_field_name = $user_fields[$field_name];
                $value = $record[$db_field_name];

                # add onclick actions
                if ($this->configuration[HTML_TABLE_PAGE_TYPE] != PAGE_TYPE_PORTAL)
                {
                    if ($this->permissions_list_title == HTML_EMPTY_LIST_TITLE)
                        $onclick_str = "onclick=\"xajax_check_permissions('".ACTION_GET_LIST_RECORD."', 'xajax_action_get_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."record(%27".$list_title."%27, %27".$encoded_key_string."%27)', '".$key_values_string."', 'below')\"";
                    else
                        $onclick_str = "onclick=\"xajax_check_list_permissions('".ACTION_GET_LIST_RECORD."', '".$this->permissions_list_title."', 'xajax_action_get_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."record(%27".$list_title."%27, %27".$encoded_key_string."%27)', '".$key_values_string."', 'below')\"";
                }
                else
                    $onclick_str = "onclick=\"xajax_check_list_permissions('".ACTION_GET_LIST_PAGE."', '".$record[LISTTABLEDESCRIPTION_TITLE_FIELD_NAME]."', 'window.location.assign(%27index.php?action=".ACTION_GET_LIST_PAGE."&list=".$record[LISTTABLEDESCRIPTION_TITLE_FIELD_NAME]."%27)', '".$key_values_string."', 'below')\"";

                if ($fields[$db_field_name][1] == FIELD_TYPE_DEFINITION_BOOL)
                {
                    if ($value == 0)
                        $html_str .= "                        <td ".$onclick_str.">".translate("LABEL_NO")."</td>\n";
                    else
                        $html_str .= "                        <td ".$onclick_str.">".translate("LABEL_YES")."</td>\n";
                }
                else if (stristr($fields[$db_field_name][1], "DATE"))
                {
                    $date_string = str_replace('-', '&#8209;', get_date_str(DATE_FORMAT_WEEKDAY, $value));
                    $html_str .= "                        <td ".$onclick_str.">".$date_string."</td>\n";
                }
                else if ($fields[$db_field_name][1] == FIELD_TYPE_DEFINITION_AUTO_CREATED)
                {

                    if ($fields[$db_field_name][2] == NAME_DATE_OPTION_NAME)
                        $html_str .= "                        <td ".$onclick_str.">".str_replace('-', '&#8209;', $record[DB_CREATOR_FIELD_NAME])."</td>\n";
                    else
                    {
                        if ($fields[$db_field_name][2] == NAME_DATE_OPTION_DATE)
                        {
                            $html_str .= "                        <td ".$onclick_str.">";
                            $html_str .= str_replace('-', '&#8209;', get_date_str(DATE_FORMAT_WEEKDAY, $record[DB_TS_CREATED_FIELD_NAME]))."</td>\n";
                        }
                        else if ($fields[$db_field_name][2] == NAME_DATE_OPTION_DATE_NAME)
                        {
                            $html_str .= "                        <td ".$onclick_str.">";
                            $html_str .= str_replace('-', '&#8209;', get_date_str(DATE_FORMAT_NORMAL, $record[DB_TS_CREATED_FIELD_NAME]));
                            $html_str .= "&nbsp;(".$record[DB_CREATOR_FIELD_NAME].")</td>\n";
                        }
                    }
                }
                else if ($fields[$db_field_name][1] == FIELD_TYPE_DEFINITION_AUTO_MODIFIED)
                {
                    if ($fields[$db_field_name][2] == NAME_DATE_OPTION_NAME)
                        $html_str .= "                        <td ".$onclick_str.">".str_replace('-', '&#8209;', $record[DB_MODIFIER_FIELD_NAME])."</td>\n";
                    else
                    {
                        if ($fields[$db_field_name][2] == NAME_DATE_OPTION_DATE)
                        {
                            $html_str .= "                        <td ".$onclick_str.">";
                            $html_str .= str_replace('-', '&#8209;', get_date_str(DATE_FORMAT_WEEKDAY, $record[DB_TS_MODIFIED_FIELD_NAME]))."</td>\n";
                        }
                        else if ($fields[$db_field_name][2] == NAME_DATE_OPTION_DATE_NAME)
                        {
                            $html_str .= "                        <td ".$onclick_str.">";
                            $html_str .= str_replace('-', '&#8209;', get_date_str(DATE_FORMAT_NORMAL, $record[DB_TS_MODIFIED_FIELD_NAME]));
                            $html_str .= "&nbsp;(".$record[DB_MODIFIER_FIELD_NAME].")</td>\n";
                        }
                    }
                }
                else if ($fields[$db_field_name][1] == FIELD_TYPE_DEFINITION_NOTES_FIELD)
                {
                    $html_str .= "                        <td ".$onclick_str.">";
                    if (count($value) > 0)
                    {
                        $html_str .= "\n";
                        foreach ($value as $note_array)
                        {
                            $html_str .= "                            <p><span class=\"note_creator\">";
                            $html_str .= str_replace('-', '&#8209;', get_date_str(DATE_FORMAT_NORMAL, $note_array[DB_TS_CREATED_FIELD_NAME]));
                            $html_str .= "&nbsp;(".$note_array[DB_CREATOR_FIELD_NAME].")</span> ";
                            $html_str .= $note_array["_note"]."</p>\n";
                        }
                    }
                    else
                        $html_str .= "-";
                    $html_str .= "                        </td>\n";
                }
                else if ($fields[$db_field_name][1] == FIELD_TYPE_DEFINITION_TEXT_FIELD)
                {
                    $html_str .= "                        <td ".$onclick_str.">".nl2br($value)."</td>\n";
                }
                # translate language options in user admin page
                else if (($this->configuration[HTML_TABLE_PAGE_TYPE] == PAGE_TYPE_USER_ADMIN) && ($db_field_name == USER_LANG_FIELD_NAME))
                {
                    $html_str .= "                        <td ".$onclick_str.">".translate($value)."</td>\n";
                }
                else
                {
                    $html_str .= "                        <td ".$onclick_str.">".$value."</td>\n";
                }
                $col_number += 1;
            }

            # only add buttons when all pages do not need to be displayed at once
            if ($page != DATABASETABLE_ALL_PAGES)
            {
                # define delete and archive buttons
                $js_button_archive ="action_archive_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."record";
                $js_button_activate ="action_activate_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."record";
                $js_button_delete ="action_delete_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."record";
                # add buttons for normal lists
                if ($this->configuration[HTML_TABLE_PAGE_TYPE] != PAGE_TYPE_PORTAL)
                {
                    # add the archive button only when this record is not archived
                    if (($metadata_str[DATABASETABLE_METADATA_ENABLE_ARCHIVE] != DATABASETABLE_METADATA_FALSE) && (strlen($record[DB_ARCHIVER_FIELD_NAME]) == 0))
                    {
                        $html_str .= "                        <td width=\"1%\">";
                        $html_str .= get_href($js_button_archive, $key_values_string, "below", $this->permissions_list_title, "xajax_".$js_button_archive."(%27".$list_title."%27, %27".$encoded_key_string."%27)", translate("BUTTON_ARCHIVE"), "icon_archive");
                        $html_str .= "</td>\n";
                    }
                    # add the delete link when it should always be displayed
                    if ($this->configuration[HTML_TABLE_DELETE_MODE] == HTML_TABLE_DELETE_MODE_ALWAYS)
                    {
                        $html_str .= "                        <td width=\"1%\">";
                        $html_str .= get_href($js_button_delete, $key_values_string, "below", $this->permissions_list_title, "xajax_".$js_button_delete."(%27".$list_title."%27, %27".$encoded_key_string."%27)", translate("BUTTON_DELETE"), "icon_delete");
                        $html_str .= "</td>\n";
                    }
                    # or add the delete link when record is archived
                    else if (($this->configuration[HTML_TABLE_DELETE_MODE] == HTML_TABLE_DELETE_MODE_ARCHIVED) && (strlen($record[DB_ARCHIVER_FIELD_NAME]) > 0))
                    {
                        $html_str .= "                        <td width=\"1%\">";
                        $html_str .= get_href($js_button_archive, $key_values_string, "below", $this->permissions_list_title, "xajax_".$js_button_activate."(%27".$list_title."%27, %27".$encoded_key_string."%27)", translate("BUTTON_ACTIVATE"), "icon_unarchive");
                        $html_str .= "</td>\n";
                        $html_str .= "                        <td width=\"1%\">";
                        $html_str .= get_href($js_button_delete, $key_values_string, "below", $this->permissions_list_title, "xajax_".$js_button_delete."(%27".$list_title."%27, %27".$encoded_key_string."%27)", translate("BUTTON_DELETE"), "icon_delete");
                        $html_str .= "</td>\n";
                    }
                    else if ($this->configuration[HTML_TABLE_DELETE_MODE] == HTML_TABLE_DELETE_MODE_NEVER)
                        $html_str .= "                        <td width=\"1%\">&nbsp;</td>\n";
                    $html_str .= "                    </tr>\n";
                }
            }
            # add buttons for portal page
            else if ($this->configuration[HTML_TABLE_PAGE_TYPE] == PAGE_TYPE_PORTAL)
            {
                # add modify button
                $html_str .= "                        <td width=\"1%\">";
                $html_str .= get_query_href(ACTION_GET_LISTBUILDER_PAGE, $key_values_string, "below", $record[LISTTABLEDESCRIPTION_TITLE_FIELD_NAME], "action=".ACTION_GET_LISTBUILDER_PAGE."&list=".$record[LISTTABLEDESCRIPTION_TITLE_FIELD_NAME], translate("BUTTON_MODIFY"), "icon_edit");
                $html_str .= "</td>\n";
                # add delete button
                $html_str .= "                        <td width=\"1%\">";
                $html_str .= get_href_confirm(ACTION_DELETE_PORTAL_RECORD, $key_values_string, "below", $record[LISTTABLEDESCRIPTION_TITLE_FIELD_NAME], "xajax_action_delete_portal_record(%22".$record[LISTTABLEDESCRIPTION_TITLE_FIELD_NAME]."%22)", translate("LABEL_CONFIRM_DELETE"), translate("BUTTON_DELETE"), "icon_delete");
                $html_str .= "</td>\n                    </tr>\n";
            }
            else
                $html_str .= "                        <td width=\"1%\">&nbsp;</td>\n                    </tr>\n";

            $record_number += 1;
        }

        if ($total_pages == 0)
        {
            $html_str .= "                    <tr>\n";
            foreach ($field_names_with_length as $field_name)
                $html_str .= "                        <td>".translate("LABEL_MINUS")."</td>\n";
            $html_str .= "                        <td width=\"1%\">&nbsp</td>\n";
            $html_str .= "                    </tr>\n";
        }

        # end table definition
        $html_str .= "                </tbody>\n";
        $html_str .= "            </table>\n\n";


        # add navigation links, except when all pages have to be shown
        $html_str .= "            <div id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."contents_bottom_left\">\n";
        $html_str .= "                <div id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."contents_bottom_right\">\n";
        $html_str .= "                    <div id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."contents_bottom\">\n";
        if ($page != DATABASETABLE_ALL_PAGES)
        {
            $html_str .= "                        <div id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."pages_bottom\">";
            # display 1 pagenumber when there is only one page (or none)
            if ($total_pages == 0 || $total_pages == 1)
            {
                $html_str .= translate("LABEL_PAGE").": <strong>".$total_pages."</strong>";
            }
            # pagenumber display algorithm for 2 or more pages
            else
            {

                $js_href_get = "action_get_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."content";

                # display previous page link
                if ($current_page > 1)
                    $html_str .= get_href(HTML_NO_ACTION, "", "", $this->permissions_list_title, "xajax_".$js_href_get."(%27".$list_title."%27, %27%27, ".($current_page - 1).")", "&laquo;&nbsp;".translate("BUTTON_PREVIOUS_PAGE"), "")."&nbsp;&nbsp;";

                # display first pagenumber
                if ($current_page == 1)
                    $html_str .= " <strong>1</strong>";
                else
                    $html_str .= " ".get_href(HTML_NO_ACTION, "", "", $this->permissions_list_title, "xajax_".$js_href_get."(%27".$list_title."%27, %27%27, 1)", 1, "");
                # display middle pagenumbers
                for ($cnt = 2; $cnt<$total_pages; $cnt += 1)
                {
                    if ($cnt == ($current_page - 2))
                        $html_str .= " <strong>...<strong>";
                    else if ($cnt == ($current_page - 1))
                        $html_str .= " ".get_href(HTML_NO_ACTION, "", "", $this->permissions_list_title, "xajax_".$js_href_get."(%27".$list_title."%27, %27%27, ".$cnt.")", $cnt, "");
                    else if ($cnt == $current_page)
                        $html_str .= " <strong>".$cnt."</strong>";
                    else if ($cnt == ($current_page + 1))
                        $html_str .= " ".get_href(HTML_NO_ACTION, "", "", $this->permissions_list_title, "xajax_".$js_href_get."(%27".$list_title."%27, %27%27, ".$cnt.")", $cnt, "");
                    if ($cnt == ($current_page + 2))
                        $html_str .= " <strong>...<strong>";
                }
                # display last pagenumber
                if ($current_page == $total_pages)
                    $html_str .= "  <strong>".$total_pages."</strong>";
                else
                    $html_str .= "  ".get_href(HTML_NO_ACTION, "", "", $this->permissions_list_title, "xajax_".$js_href_get."(%27".$list_title."%27, %27%27, ".$total_pages.")", $total_pages, "");

                # display next page link
                if ($current_page < $total_pages)
                    $html_str .= "&nbsp;&nbsp;".get_href(HTML_NO_ACTION, "", "", "below", $this->permissions_list_title, "xajax_".$js_href_get."(%27".$list_title."%27, %27%27, ".($current_page + 1).")", translate("BUTTON_NEXT_PAGE")."&nbsp;&raquo;", "");
            }
            $html_str .= "</div>\n";
        }
        else
            $html_str .= "                        &nbsp;\n";
        $html_str .= "                    </div> <!-- ".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."contents_bottom -->\n";
        $html_str .= "                </div> <!-- ".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."contents_bottom_right -->\n";
        $html_str .= "            </div> <!-- ".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."contents_bottom_left -->\n\n        ";

        $result->set_result_str($html_str);

        $this->_log->trace("got content");
    }

    /**
     * get html (use Result object) of one specified record
     * @param $database_table DatabaseTable database table object
     * @param $list_title string title of list
     * @param $encoded_key_string string comma separated name value pairs
     * @param $result Result result object
     * @return string name of input element that should get focus
     */
    function get_record ($database_table, $list_title, $encoded_key_string, $result)
    {
        global $firstthingsfirst_field_descriptions;
        global $firstthingsfirst_date_string;

        $this->_log->trace("getting record (list_title=".$list_title.", encoded_key_string=".$encoded_key_string.")");

        # get list record when key string has been given
        if (strlen($encoded_key_string) > 0)
        {
            $this->_log->debug("key string has been set");
            $record = $database_table->select_record($encoded_key_string);
            if (strlen($database_table->get_error_message_str()) > 0)
            {
                $this->_log->debug("error has been set");
                $this->_handle_error($database_table, $result, MESSAGE_PANE_DIV);

                return;
            }
        }

        $html_str = "";
        $return_name_tag = "";
        $field_names = $database_table->get_user_field_names();
        $fields = $database_table->get_fields();

        # start with the action bar
        if (strlen($encoded_key_string))
            $html_str .= $this->get_action_bar($list_title, "edit");
        else
            $html_str .= $this->get_action_bar($list_title, "insert");

        # then the form and table definition
        $html_str .= "\n                <div id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."record_contents_pane\">\n";
        $html_str .= "                    <form name=\"record_form_name\" id=\"record_form\" action=\"javascript:void(0);\" method=\"javascript:void(0);\">\n";
        $html_str .= "                        <table id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."record_contents\" align=\"left\" border=\"0\" cellspacing=\"2\">\n";
        $html_str .= "                            <tbody>\n";

        # add table record for each field type
        for ($i=0; $i<count($field_names); $i++)
        {
            $field_name = $field_names[$i];
            $user_fields = $database_table->get_user_fields();
            $db_field_name = $user_fields[$field_name];
            $field_type = $fields[$db_field_name][1];
            $field_options = $fields[$db_field_name][2];
            $this->_log->debug("record (name=".$field_name." db_name=".$db_field_name." type=".$field_type.")");

            # set empty string if field does not exist (encoded_key_string was not set)
            if (strlen($encoded_key_string) == 0)
                $record[$db_field_name] = "";

            # replace all " chars with &quot
            $record[$db_field_name] = str_replace('"', '&quot', $record[$db_field_name]);

            # replace all space chars with &nbsp
            $field_name_replaced = str_replace(' ', '&nbsp;', $field_names[$i]);

            # translate field_name when this is not a list table
            if ($this->configuration[HTML_TABLE_PAGE_TYPE] != PAGE_TYPE_LIST)
                $field_name_replaced = str_replace(' ', '&nbsp;', translate($field_names[$i]));

            # only add non auto_increment field types (check database definition for this)
            if (($field_type != FIELD_TYPE_DEFINITION_AUTO_NUMBER) && (strlen($field_name) > 0))
            {
                $html_str .= "                                <tr>\n";
                $html_str .= "                                    <th>".$field_name_replaced."</th>\n";

                # the name tag
                $tag = $db_field_name.GENERAL_SEPARATOR.$field_type.GENERAL_SEPARATOR."0";

                if ($field_type != FIELD_TYPE_DEFINITION_NOTES_FIELD)
                {
                    $html_str .= "                                    <td id=\"".$db_field_name;
                    $html_str .= "\" tabindex=\"".$i."\"><".$firstthingsfirst_field_descriptions[$field_type][FIELD_DESCRIPTION_FIELD_HTML_DEFINITION];
                    # create a name tag
                    $html_str .= " name=".$tag." id=".$tag;
                }

                # set element name for return value
                if ((strlen($return_name_tag) == 0) && !stristr($field_type, "AUTO"))
                    $return_name_tag = $tag;

                # set values from database
                if (strlen($encoded_key_string))
                {
                    if ($field_type == FIELD_TYPE_DEFINITION_BOOL)
                    {
                        if ($record[$db_field_name] == "1")
                            $html_str .= " checked";
                    }
                    else if (stristr($field_type, "DATE"))
                    {
                        $date_string = get_date_str(DATE_FORMAT_NORMAL, $record[$db_field_name]);
                        $html_str .= " value=\"".$date_string."\"";
                    }
                    else if ($fields[$db_field_name][1] == FIELD_TYPE_DEFINITION_AUTO_CREATED)
                    {
                        if ($fields[$db_field_name][2] == NAME_DATE_OPTION_NAME)
                            $html_str .= " value=\"".$record[DB_CREATOR_FIELD_NAME]."\"";
                        else
                        {
                            $ts_created = get_date_str(DATE_FORMAT_WEEKDAY, $record[DB_TS_CREATED_FIELD_NAME]);
                            if ($fields[$db_field_name][2] == NAME_DATE_OPTION_DATE)
                                $html_str .= " value=\"".get_date_str(DATE_FORMAT_WEEKDAY, $record[DB_TS_CREATED_FIELD_NAME])."\"";
                            else if ($fields[$db_field_name][2] == NAME_DATE_OPTION_DATE_NAME)
                            {
                                $html_str .= " value=\"".get_date_str(DATE_FORMAT_NORMAL, $record[DB_TS_CREATED_FIELD_NAME]);
                                $html_str .= "&nbsp;(".$record[DB_CREATOR_FIELD_NAME].")\"";
                            }
                        }
                    }
                    else if ($fields[$db_field_name][1] == FIELD_TYPE_DEFINITION_AUTO_MODIFIED)
                    {
                        if ($fields[$db_field_name][2] == NAME_DATE_OPTION_NAME)
                            $html_str .= " value=\"".$record[DB_MODIFIER_FIELD_NAME]."\"";
                        else
                        {
                            if ($fields[$db_field_name][2] == NAME_DATE_OPTION_DATE)
                                $html_str .= " value=\"".get_date_str(DATE_FORMAT_WEEKDAY, $record[DB_TS_MODIFIED_FIELD_NAME])."\"";
                            else if ($fields[$db_field_name][2] == NAME_DATE_OPTION_DATE_NAME)
                            {
                                $html_str .= " value=\"".get_date_str(DATE_FORMAT_NORMAL, $record[DB_TS_MODIFIED_FIELD_NAME]);
                                $html_str .= "&nbsp;(".$record[DB_MODIFIER_FIELD_NAME].")\"";
                            }
                        }
                    }
                    else if ($field_type == FIELD_TYPE_DEFINITION_TEXT_FIELD)
                        $html_str .= ">".$record[$db_field_name]."</textarea";
                    else if ($field_type == FIELD_TYPE_DEFINITION_PASSWORD)
                        $html_str .= " value=\"\"";
                    else if ($field_type == FIELD_TYPE_DEFINITION_SELECTION)
                    {
                        $html_str .= ">";
                        $option_list = explode("|", $field_options);
                        foreach ($option_list as $option)
                        {
                            $html_str .= "\n                                        <option value=\"".$option."\"";
                            if ($option == $record[$db_field_name])
                                $html_str .= " selected";
                            # translate language options in user admin page
                            if (($this->configuration[HTML_TABLE_PAGE_TYPE] == PAGE_TYPE_USER_ADMIN) && ($db_field_name == USER_LANG_FIELD_NAME))
                                $html_str .= ">".translate($option)."&nbsp;&nbsp;"."</option>";
                            else
                                $html_str .= ">".$option."&nbsp;&nbsp;"."</option>";
                        }
                        $html_str .= "\n                                    </select";
                    }
                    else if ($field_type == FIELD_TYPE_DEFINITION_NOTES_FIELD)
                    {
                        $html_str .= get_list_record_notes($db_field_name, $record[$db_field_name]);
                    }
                    else
                        $html_str .= " value=\"".$record[$db_field_name]."\"";
                }
                # set initial values
                else
                {
                    if ($field_type == FIELD_TYPE_DEFINITION_NON_EDIT_NUMBER)
                        $html_str .=  " value=\"0\"";
                    else if (($field_type == FIELD_TYPE_DEFINITION_AUTO_CREATED) || ($field_type == FIELD_TYPE_DEFINITION_AUTO_MODIFIED))
                        $html_str .=  " value=\"-\"";
                    else if ($field_type == FIELD_TYPE_DEFINITION_TEXT_FIELD)
                        $html_str .= "></textarea";
                    else if ($field_type == FIELD_TYPE_DEFINITION_NOTES_FIELD)
                        $html_str .= get_list_record_notes($db_field_name, array());
                    else if ($field_type == FIELD_TYPE_DEFINITION_SELECTION)
                    {
                        $html_str .= ">";
                        $option_list = explode("|", $field_options);
                        foreach ($option_list as $option)
                        {
                            if (($this->configuration[HTML_TABLE_PAGE_TYPE] == PAGE_TYPE_USER_ADMIN) && ($db_field_name == USER_LANG_FIELD_NAME))
                                $html_str .= "\n                                        <option value=\"".$option."\">".translate($option)."&nbsp;&nbsp;"."</option>\n";
                            else
                                $html_str .= "\n                                        <option value=\"".$option."\">".$option."&nbsp;&nbsp;"."</option>\n";
                        }
                        $html_str .= "\n                                    </select";
                    }
                    else
                        $html_str .= " value=\"\"";
                }
                if ($field_type != FIELD_TYPE_DEFINITION_NOTES_FIELD)
                    $html_str .= "></td>\n";
                    $html_str .= "                                    <td class=\"super_width\">&nbsp;</td>\n";
                $html_str .= "                                </tr>\n";
            }
        }

        # end table definition
        $html_str .= "                            </tbody>\n";
        $html_str .= "                        </table> <!-- ".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."record_contents -->\n";

        # define insert, update and cancel buttons
        $js_button_insert ="action_insert_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."record";
        $js_button_update ="action_update_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."record";
        $js_button_cancel ="action_cancel_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."action";

        # add link to confirm contents to database
        $html_str .= "                        <span id=\"record_contents_buttons\">\n";
        $html_str .= "                            ";
        if (!strlen($encoded_key_string))
            $html_str .= get_href($js_button_insert, "record_contents_buttons", "above", $this->permissions_list_title, "xajax_".$js_button_insert."(%27".$list_title."%27, xajax.getFormValues(%27record_form%27))", translate("BUTTON_ADD"), "icon_add");
        else
            $html_str .= get_href($js_button_update, "record_contents_buttons", "above", $this->permissions_list_title, "xajax_".$js_button_update."(%27".$list_title."%27, %27".$encoded_key_string."%27, xajax.getFormValues(%27record_form%27))", translate("BUTTON_COMMIT_CHANGES"), "icon_accept");
        $html_str .= "\n                            ";
        $html_str .= "&nbsp;&nbsp;".get_href(HTML_NO_ACTION, "", "", HTML_EMPTY_LIST_TITLE, "xajax_".$js_button_cancel."('".$list_title."')", translate("BUTTON_CANCEL"), "icon_cancel");
        $html_str .= "\n                        </span> <!-- record_contents_buttons -->\n";

        #end form
        $html_str .= "                    </form> <!-- record_form -->\n";
        $html_str .= "                </div> <!-- ".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."record_contents_pane -->\n";
        $html_str .= "                <div id=\"action_pane_bottom_left\"></div>\n";
        $html_str .= "                <div id=\"action_pane_bottom_right\"></div>\n\n            ";

        $result->set_result_str($html_str);

        $this->_log->trace("got record (return_name_tag=".$return_name_tag.")");

        # return the element name
        return $return_name_tag;
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

        $html_str .= "\n            <div id=\"action_bar_top_left\"></div>\n";
        $html_str .= "            <div id=\"action_bar_top_right\"></div>\n";
        $html_str .= "            <div id=\"action_bar\" align=\"left\" valign=\"top\">\n";
        $html_str .= "                ";

        if ($this->configuration[HTML_TABLE_PAGE_TYPE] != PAGE_TYPE_PORTAL)
        {
            if ($action == "edit")
                $html_str .= "<strong>".translate("LABEL_EDIT_RECORD").$this->configuration[HTML_TABLE_RECORD_NAME]."</strong>";
            else if ($action == "insert")
                $html_str .= "<strong>".translate("LABEL_ADD_RECORD").$this->configuration[HTML_TABLE_RECORD_NAME]."</strong>";
            else if ($this->configuration[HTML_TABLE_PAGE_TYPE] != PAGE_TYPE_USERLISTTABLEPERMISSIONS)
            {
                $html_str .= "<span id=\"action_bar_button_insert\">";
                $html_str .= get_href(ACTION_INSERT_LIST_RECORD, "action_bar_button_insert", "above", $this->permissions_list_title, "xajax_action_get_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."record(%27".$list_title."%27, %27%27)", translate("BUTTON_ADD_RECORD").$this->configuration[HTML_TABLE_RECORD_NAME], "icon_add");
                $html_str .= "</span>&nbsp;&nbsp;&nbsp;&nbsp;";
            }
        }
        # only display the print button when no action is active and only when this is a list page
        if (($action == "") && ($this->configuration[HTML_TABLE_PAGE_TYPE] == PAGE_TYPE_LIST))
        {
            $html_str .= "<span id=\"action_bar_button_print\">";
            $html_str .= get_href(HTML_NO_ACTION, "", "", $this->permissions_list_title, "window.open(%27index.php?action=".ACTION_GET_LIST_PRINT_PAGE."&list=".$list_title."%27)", translate("BUTTON_PRINT_LIST"), "icon_print");
            $html_str .= "</span>";
        }
        else
            $html_str .= "&nbsp;";

        $html_str .= "\n            </div> <!-- action_bar -->\n";
        if ($action == "")
        {
            $html_str .= "            <div id=\"action_bar_bottom_left\"></div>\n";
            $html_str .= "            <div id=\"action_bar_bottom_right\"></div>\n        ";
        }
        $this->_log->trace("got action bar");

        return $html_str;
    }

    /**
     * get html for the archive records selector
     * @param $database_table DatabaseTable database table object
     * @param $list_title string title of list
     * @return string returned html
     */
    function get_archive_select ($database_table, $list_title)
    {
        $this->_log->trace("get archive select");

        $html_str = "";

        $this->_user->get_list_state($database_table->get_table_name());
        $archived = $this->_list_state->get_archived();

        $html_str .= "                        ";
        $html_str .= "<div id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."archive_select\">\n";
        $html_str .= "                            <select id=\"archive_select\"";
        $html_str .= " onChange=\"xajax_action_set_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."archive('".$list_title."', document.getElementById('archive_select').value);\">\n";
        $html_str .= "                                <option value=\"".LISTSTATE_SELECT_NON_ARCHIVED."\"";
        if ($archived == LISTSTATE_SELECT_NON_ARCHIVED)
            $html_str .= " selected";
        $html_str .= ">".translate("LABEL_NORMAL_RECORDS")."</option>\n";
        $html_str .= "                                <option value=\"".LISTSTATE_SELECT_ARCHIVED."\"";
        if ($archived == LISTSTATE_SELECT_ARCHIVED)
            $html_str .= " selected";
        $html_str .= ">".translate("LABEL_ARCHIVED_RECORDS")."</option>\n";
        $html_str .= "                            </select>\n";
        $html_str .= "                        </div>\n";

        $this->_log->trace("got archive select");

        return $html_str;
    }

    /**
     * get html for filter
     * @param $database_table DatabaseTable database table object
     * @param $list_title string title of list
     * @return string returned html
     */
    function get_filter ($database_table, $list_title)
    {
        $this->_log->trace("get filter");

        $html_str = "";

        $this->_user->get_list_state($database_table->get_table_name());
        $filter_str = $this->_list_state->get_filter_str();

        $html_str .= "                        <div id=\"".$this->configuration[HTML_TABLE_CSS_NAME_PREFIX]."filter\">\n";
        $html_str .= "                            <form name=\"filter_form_name\" id=\"filter_form\" ";
        $html_str .= "onsubmit=\"javascript:xajax_action_set_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."filter('".$list_title."', document.getElementById('filter_str').value); return false;\">\n";
        $html_str .= "                                <input size=\"34\" maxlength=\"100\" value=\"".$filter_str."\" id=\"filter_str\">\n";
        $html_str .= "                                ".get_href(ACTION_SET_LIST_FILTER, "filter_str", "below", $list_title, "xajax_action_set_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."filter(%27".$list_title."%27, document.getElementById(%27filter_str%27).value)", "&nbsp;", "icon_none")."\n";
        $html_str .= "                            </form>\n";
        $html_str .= "                            ".get_href(ACTION_SET_LIST_FILTER, "filter_str", "below", $list_title, "xajax_action_set_".$this->configuration[HTML_TABLE_JS_NAME_PREFIX]."filter(%27".$list_title."%27, %27%27)", "&nbsp;", "icon_delete")."\n";
        $html_str .= "                        </div>\n";

        $this->_log->trace("got filter");

        return $html_str;
    }

}

?>
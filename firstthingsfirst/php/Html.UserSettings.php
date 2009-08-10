<?php

/**
 * This file contains all php code that is used to generate html for the user settings page
 *
 * @package HTML_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007-2009 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * definitions of all possible actions
 */
define("ACTION_GET_USER_SETTINGS_PAGE", "action_get_user_settings_page");
define("ACTION_UPDATE_USER_SETTINGS_RECORD", "action_update_user_settings_record");

/**
 * register all actions in xajax
 */
$xajax->register(XAJAX_FUNCTION, ACTION_GET_USER_SETTINGS_PAGE);
$xajax->register(XAJAX_FUNCTION, ACTION_UPDATE_USER_SETTINGS_RECORD);

/**
 * definition of action permissions
 * permission are stored in a six character string (P means permissions, - means don't care):
 *  - user has to have create list permission to be able to execute action
 *  - user has to have admin permission to be able to execute action
 *  - user has to have permission to view this list to execute list action for this list
 *  - user has to have permission to edit this list to execute action for this list
 *  - user has to have admin permission for this list to exectute action for this list
 */
$firstthingsfirst_action_description[ACTION_GET_USER_SETTINGS_PAGE] = "-----";
$firstthingsfirst_action_description[ACTION_UPDATE_USER_SETTINGS_RECORD] = "-----";

/**
 * definition of css name prefix
 */
define("USER_SETTINGS_CSS_NAME_PREFIX", "database_table_");


/**
 * configuration of HtlmTable
 */
$user_settings_table_configuration = array(
    HTML_TABLE_PAGE_TYPE => PAGE_TYPE_USER_SETTINGS,
    HTML_TABLE_JS_NAME_PREFIX => "user_settings_",
    HTML_TABLE_CSS_NAME_PREFIX => USER_SETTINGS_CSS_NAME_PREFIX,
    HTML_TABLE_DELETE_MODE => HTML_TABLE_DELETE_MODE_ALWAYS, # not used in user settings
    HTML_TABLE_RECORD_NAME => translate("LABEL_USER_SETTINGS_RECORD")
);


/**
 * set the html for user settings page
 * this function is registered in xajax
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_get_user_settings_page ()
{
    global $logging;
    global $user;
    global $user_settings_table_configuration;
    global $firstthingsfirst_portal_title;

    $logging->info("ACTION: get user settings page");

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($user_settings_table_configuration);

    # set page, title, explanation and navigation
    $html_database_table->get_page(translate("LABEL_USER_SETTINGS_TITLE"), $result);
    $response->assign("main_body", "innerHTML", $result->get_result_str());

    # create an array with selection of fields that user may change
    $db_fields_array = array(DB_ID_FIELD_NAME, USER_NAME_FIELD_NAME, USER_PW_FIELD_NAME, USER_LANG_FIELD_NAME);

    # get action pane for current user
    $user_record_key_string = DatabaseTable::_get_encoded_key_string(array(DB_ID_FIELD_NAME => $user->get_id()));
    $html_database_table->get_record($user, USER_TABLE_NAME, $user_record_key_string, $db_fields_array, $result);
    $response->assign("action_pane", "innerHTML", $result->get_result_str());
    $response->assign("page_title", "innerHTML", translate("LABEL_USER_SETTINGS_TITLE"));
    $response->assign("navigation_container", "innerHTML", get_page_navigation(PAGE_TYPE_USER_SETTINGS));

    # set footer
    $response->assign("footer_text", "innerHTML", "&nbsp;");

    # check post conditions
    if (check_postconditions($result, $response) == FALSE)
        return $response;

    $logging->trace("got user settings page");

    return $response;
}

/**
 * update a user record
 * this function is registered in xajax
 * @param string $title title of page
 * @param string $key_string comma separated name value pairs
 * @param array $form_values values of new record (array of name value pairs)
 * @return xajaxResponse every xajax registered function needs to return this object
 */
function action_update_user_settings_record ($title, $key_string, $form_values)
{
    global $logging;
    global $user;
    global $user_settings_table_configuration;
    global $firstthingsfirst_field_descriptions;

    # WARNING: this function is almost identical to function UserAdministration::action_update_user_admin_record
    # changes in this function should also lead to changes in that function

    $logging->info("ACTION: update user settings record (title=".$title.", key_string=".$key_string.")");

    $html_str = "";
    $name_keys = array_keys($form_values);
    $new_form_values = array();
    $fields = $user->get_fields();
    $field_keys = array_keys($fields);

    # create necessary objects
    $result = new Result();
    $response = new xajaxResponse();
    $html_database_table = new HtmlDatabaseTable ($user_settings_table_configuration);

    foreach ($name_keys as $name_key)
    {
        $value_array = explode(GENERAL_SEPARATOR, $name_key);
        $db_field_name = $value_array[0];
        $field_type = $value_array[1];
        $field_number = $value_array[2];
        $check_functions = explode(" ", $firstthingsfirst_field_descriptions[$field_type][FIELD_DESCRIPTION_FIELD_INPUT_CHECKS]);
        $result->reset();

        $logging->debug("field (name=".$db_field_name.", type=".$field_type.", number=".$field_number.")");

        # check field values (check password field only when new password has been set)
        if (($db_field_name != USER_PW_FIELD_NAME) || (($db_field_name == USER_PW_FIELD_NAME) && (strlen($form_values[$name_key]) > 0)))
        {
            check_field($check_functions, $db_field_name, $form_values[$name_key], $result);
            if (strlen($result->get_error_str()) > 0)
            {
                set_error_message($name_key, "right", $result->get_error_str(), "", "", $response);

                return $response;
            }
        }

        # set new value
        $new_form_values[$db_field_name] = $result->get_result_str();
        $logging->debug("setting new form value (db_field_name=".$db_field_name.", result=".$result->get_result_str().")");
    }

    # check if someone tries to change user admin
    if ($user->get_name() == "admin")
    {
        # check if the name of user admin is changed
        if ($new_form_values[USER_NAME_FIELD_NAME] != "admin")
        {
            set_error_message("record_contents_buttons", "right", "ERROR_CANNOT_UPDATE_NAME_USER_ADMIN", "", "", $response);

            return $response;
        }
    }

    # remove any error messages
    $response->script("$('*').qtip('destroy')");

    # display error when insertion returns false
    if (!$user->update($key_string, $new_form_values))
    {
        $logging->warn("update user settings record returns false");
        $error_message_str = $user->get_error_message_str();
        $error_log_str = $user->get_error_log_str();
        $error_str = $user->get_error_str();
        set_error_message("record_contents_buttons", "right", $error_message_str, $error_log_str, $error_str, $response);

        return $response;
    }

    # set message for user
    set_info_message("record_contents_buttons", "right", "LABEL_USER_SETTINGS_CHANGED", $response);

    # check post conditions not necessary

    $logging->trace("updated user settings record");

    return $response;
}

?>
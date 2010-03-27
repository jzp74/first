/*!
 * This file contains js code for function handlers
 *
 * @author Jasper de Jong
 * @copyright 2007-2010 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


// call check_permissions php function via xajax
// @param array variable array of arguments
// @return void
function handlePermissionCheck ()
{
    xajax.request({ xjxfun : "check_permissions" }, { parameters : arguments });
}

// call check_list_permissions php function via xajax
// @param array variable array of arguments
// @return void
function handleListPermissionCheck ()
{
    xajax.request({ xjxfun : "check_list_permissions" }, { parameters : arguments });
}

// call specified php function via xajax
// @param string name of php function
// @param array variable array of arguments
// @return void
function handleFunction (functionName)
{
    // remove any static qtip from screen
    if ( $('#qtip_close_button').length )
    {
        // click on close button of qtip
        $('#qtip_close_button').click();
    }

    // remove the first argument from the arguments list
    var argArray = $.makeArray(arguments).slice(1);

    // call specified xajax function
    xajax.request({ xjxfun : functionName }, { parameters : argArray });
}
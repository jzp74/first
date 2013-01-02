/*!
 * This file contains js code for function handlers
 *
 * @author Jasper de Jong
 * @copyright 2007-2012 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


// set global vars

// time for visuals
var visualTime = 500;

// returns minimum of 2 digits for an input digit
// @param int digit any digit
// @return string a string consisting of two digits minimum
function minTwoDigits (digit) 
{
  return (digit < 10 ? '0' : '') + digit;
}

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

    // do other stuff
    // TODO?

    xajax.request({ xjxfun : functionName }, { parameters : argArray });
}

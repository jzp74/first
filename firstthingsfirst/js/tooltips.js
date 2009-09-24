/*!
 * This file contains js code for tooltips using qtip
 *
 * @author Jasper de Jong
 * @copyright 2007-2009 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


// set global vars
// we use these vars for translations

// translation of ok
var acceptStr = "OK";
// translation of cancel
var cancelStr = "CANCEL";
// translation of close
var closeStr = "CLOSE";


// show a tooltip
// @param string domElement id of DOM element that contains the tooltip
// @param string messageStr message for user
// @param string type indicates if this is an error or an info tooltip (values 'error' or 'info')
// @param string position display this tooltip left or right (values 'left' or 'right')
// @return void
function showTooltip(domElementId, messageStr, type, position)
{
    // determine screen dimensions
    // screen.height and screen.width
    //
    // determine offset position and dimensions of element
    // var offset = p.offset()
    // offset.left and offset.top
    // $(domElementId).height() and $(domElementId).height()

    // set color scheme and image URL for info and error tooltip
    if (type == "error")
    {
        // error color scheme and image URL
        backgroundColor = "rgb(244, 203, 203)";
        textColor = "rgb(50, 50, 50)";
        borderColor = "rgb(185, 10, 10)";
        imageUrl = "images/icons/nuove_error.png";
    }
    else
    {
        // info color scheme and image URL
        backgroundColor = "rgb(255, 245, 189)";
        textColor = "rgb(50, 50, 50)";
        borderColor = "rgb(255, 173, 37)";
        imageUrl = "images/icons/nuove_info.png";
    }

    // set left, above, below of right position
    if (position == "left")
    {
        // left position
        cornerTarget = "leftMiddle";
        cornerTooltip = "rightMiddle";
        tipCorner = "rightMiddle";
    }
    else if (position == "above")
    {
        // right position
        cornerTarget = "topMiddle";
        cornerTooltip = "bottomLeft";
        tipCorner = "bottomLeft";
    }
    else if (position == "below")
    {
        // right position
        cornerTarget = "bottomMiddle";
        cornerTooltip = "topLeft";
        tipCorner = "topLeft";
    }
    else
    {
        // right position
        cornerTarget = "rightMiddle";
        cornerTooltip = "leftMiddle";
        tipCorner = "leftMiddle";
    }
    // get the tooltip HTML
    htmlStr = getTooltipContent (domElementId, messageStr, imageUrl, "");

    // create the tooltip
    $(domElementId).qtip(
    {
        content:
        {
            text: htmlStr
        },
        position:
        {
            corner:
            {
                target: cornerTarget,
                tooltip: cornerTooltip
            }
        },
        show:
        {
            delay: 0,
            solo: true,
            ready: true,
            when:
            {
                event: ''
            },
            effect:
            {
                type: 'fade',
                length: 200
            }
        },
        hide:
        {
            when:
            {
                event: ''
            }
        },
        style:
        {
            background: backgroundColor,
            color: textColor,
            padding: '0px 0px 0px 0px',
            width:
            {
                min: 350,
                max: 350
            },
            border:
            {
                width: 0,
                radius: 6,
                color: borderColor
            },
            tip:
            {
                corner: tipCorner
            }
        }
    });
}

// show a modal dialog
// @param string domElement id of DOM element that contains the tooltip
// @param string messageStr message for user
// @param string functionCall function to call in case user is ok
// @return void
function showModalDialog (domElementId, messageStr, functionStr)
{
    // get the image URL and the dialog HTML
    imageUrl = "images/icons/nuove_info.png";
    htmlStr = getTooltipContent (domElementId, messageStr, imageUrl, functionStr);

    // create the tooltip
    $(domElementId).qtip(
    {
        content:
        {
            text: htmlStr
        },
        position:
        {
            target: $(document.body),
            corner: 'center'
        },
        show:
        {
            delay: 0,
            solo: true,
            ready: true,
            when:
            {
                event: ''
            },
            effect:
            {
                type: 'fade',
                length: 200
            }
        },
        hide: false,
        style:
        {
            background: "rgb(255, 245, 189)",
            color: "rgb(50, 50, 50)",
            padding: '0px 0px 0px 0px',
            width:
            {
                min: 350,
                max: 350
            },
            border:
            {
                width: 0,
                radius: 6,
                color: "rgb(255, 173, 37)"
            }
        },
        api:
        {
            beforeShow: function()
            {
                // set correct height of blanket and fade in
                $('#modal_blanket').fadeIn("fast");
            },
            beforeDestroy: function()
            {
                // fade out and set height of blanket to 0
                $('#modal_blanket').fadeOut("fast");
            }
        }
    });
}

// return the HTML for a tooltip
// @param string domElement id of DOM element that contains the tooltip
// @param string messageStr message for user
// @param string imageUrl url to image (either info or error image)
// @param string functionStr string with function to be called when user clicks ok
// @return string string with resulting HTML
function getTooltipContent (domElementId, messageStr, imageUrl, functionStr)
{
    htmlStr = "\n";
    htmlStr += "    <table id=\"qtip_message_table\">\n";
    htmlStr += "        <thead>\n";
    htmlStr += "            <tr>\n";
    htmlStr += "                <th colspan=2><a href=\"javascript:void(0);\" id=\"qtip_close_button\" class=\"icon_delete\" onclick=\"$('" + domElementId + "').qtip('destroy');\">" + closeStr + "</a></th>\n";
    htmlStr += "            </tr>\n";
    htmlStr += "        </thead>\n";
    htmlStr += "        <tbody>\n";
    htmlStr += "            <tr>\n";
    htmlStr += "                <td><img src=\"" + imageUrl + "\"></td>\n";
    htmlStr += "                <td>" + messageStr + "</td>\n";
    htmlStr += "            </tr>\n";

    // add an extra row for ok and cancel buttons when this is a modal dialog
    if (functionStr.length > 0)
    {
        funcStr = functionStr.replace(/%22/g, "'");
        htmlStr += "            <tr>\n";
        htmlStr += "                <td colspan=2 class=\"buttons\">";
        htmlStr += "<a href=\"javascript:void(0);\" class=\"icon_accept\" onclick=\"$('" + domElementId + "').qtip('destroy'); " + funcStr + "\">" + acceptStr + "</a>";
        htmlStr += "<a href=\"javascript:void(0);\" class=\"icon_cancel\" onclick=\"$('" + domElementId + "').qtip('destroy');\">" + cancelStr + "</a></td>\n";
        htmlStr += "            </tr>\n";
    }

    htmlStr += "        </tbody>\n";
    htmlStr += "    </table>\n";

    return htmlStr;
}

// set translations
// @param string okStr translation of ok
// @param string cancelStr translation of cancel
// @param string closeStr translation of close
// @return void
function setTranslations (accept, cancel, close)
{
    // set global vars
    acceptStr = accept;
    cancelStr = cancel;
    closeStr = close;
}

// things to do at document load:
//  - create blanket
$(document).ready(function() {
    $('<div id="modal_blanket">')
    .css({
        position: 'absolute',
        top: $(document).scrollTop(),
        left: 0,
        height: $(document).height(),
        width: '100%',
        opacity: 0.6,
        backgroundColor: 'black',
        zIndex: 5000
    })
    .appendTo(document.body) // Append to the document body
    .hide(); // Hide it initially
});

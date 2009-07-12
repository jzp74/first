/*!
 * This file contains js code for tooltips using qtip
 *
 * @author Jasper de Jong
 * @copyright 2007-2009 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


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
    htmlStr = getTooltipContent (domElementId, messageStr, imageUrl);

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
                type: 'grow',
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
                min: 300,
                max: 320
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

// return the HTML for a tooltip
// @param string domElement id of DOM element that contains the tooltip
// @param string messageStr message for user
// @param string imageUrl url to image (either info or error image)
// @return string
function getTooltipContent (domElementId, messageStr, imageUrl)
{
    htmlStr = "\n";
    htmlStr += "    <table id=\"qtip_message_table\">\n";
    htmlStr += "        <thead>\n";
    htmlStr += "            <tr>\n";
    htmlStr += "                <th colspan=2><a href=\"javascript:void(0);\" class=\"icon_delete\" onclick=\"$('" + domElementId + "').qtip('destroy'); return false;\">&nbsp</a></th>\n";
    htmlStr += "            </tr>\n";
    htmlStr += "        </thead>\n";
    htmlStr += "        <tbody>\n";
    htmlStr += "            <tr>\n";
    htmlStr += "                <td><img src=\"" + imageUrl + "\"></td>\n";
    htmlStr += "                <td>" + messageStr + "</td>\n";
    htmlStr += "            </tr>\n";
    htmlStr += "        </tbody>\n";
    htmlStr += "    </table>\n";

    return htmlStr;
}
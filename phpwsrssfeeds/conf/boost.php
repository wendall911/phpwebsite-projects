<?php

/**
 * Boost configuration file for RSS Feeds
 *
 * @version $Id: boost.php,v 1.26 2005/03/15 16:23:03 wendall911 Exp $
 * @author Wendall Cada <wendall911@users.sourceforge.net>
 */

$mod_title = "phpwsrssfeeds";
/* Name to appear in Module list and displays */
$mod_pname = "RSS News Feeds";
/* Directory under /mod in which the module files are stored */
$mod_directory = "phpwsrssfeeds";
/* Initial access file for module */
$mod_filename = "index.php";
/* Opcode for admin entry ? */
$admin_op = "&RSS_MAN_op=list";
/* Opcode for user entry */
$user_op = "&RSS_MAN_op=list";
/* Who can view the list */
$allow_view = "all";
$priority = 50;
/* Icon user sees to select module */
$user_icon = "rss.png";
/* Icon admin sees to select module -- irrelevant in this case, see below */
$mod_icon = "rss.png";

$admin_mod = 1;
$user_mod = 0;
$active = "on";
$version = "0.4.2";
$depend = array("menuman", "search");

?>

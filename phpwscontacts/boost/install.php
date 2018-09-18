<?php
/**
 * phpwsContacts
 *
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version     $Id: install.php,v 1.22 2004/09/14 03:09:09 rizzo Exp $
 */

if (!$_SESSION['OBJ_user']->isDeity()) {
    header('location:index.php');
    exit();
}

// Need to do core version check
if (version_compare($GLOBALS['core']->version, '0.9.3-2') < 0) {
    $content .= 'This module requires a phpWebSite core version of 0.9.3-2 or greater to install.<br />';
    $content .= '<br />You are currently using phpWebSite core version ' . $GLOBALS['core']->version . '.<br />';
    return;
}

if ($status = $GLOBALS['core']->sqlImport(PHPWS_SOURCE_DIR . 'mod/phpwscontacts/boost/install.sql', TRUE)) {
    $content .= 'All phpwsContacts tables successfully written.<br />';

    // Load help information
    require_once(PHPWS_SOURCE_DIR . 'mod/help/class/CLS_help.php');
    CLS_help::setup_help('phpwscontacts');

    /* Create image directory */
    mkdir($GLOBALS['core']->home_dir . 'images/phpwscontacts');
    if (is_dir($GLOBALS['core']->home_dir . 'images/phpwscontacts'))
        $content .= 'phpwsContacts image directory ' . $GLOBALS['core']->home_dir . 'images/phpwscontacts successfully created!<br />';
    else
        $content .= 'phpwsContacts could not create the image directory: ' . $GLOBALS['core']->home_dir . 'images/phpwscontacts<br />You will have to do this manually!<br />';

    mkdir($GLOBALS['core']->home_dir . 'images/phpwscontacts/thumbs');
    if (is_dir($GLOBALS['core']->home_dir . 'images/phpwscontacts/thumbs'))
        $content .= 'phpwsContacts image directory ' . $GLOBALS['core']->home_dir . 'images/phpwscontacts/thumbs successfully created!<br />';
    else
        $content .= 'phpwsContacts could not create the image directory: ' . $GLOBALS['core']->home_dir . 'images/phpwscontacts/thumbs<br />You will have to do this manually!<br />';

} else {
    $content .= 'There was a problem writing to the database!<br />';
}
    
?>

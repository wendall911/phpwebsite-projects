<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * phpwsBB
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
 * @author      Don Seiler <don@NOSPAM.seiler.us>
 * @version     $Id: install.php,v 1.25 2006/08/13 23:46:40 singletrack Exp $
 */

require_once(PHPWS_SOURCE_DIR . 'core/File.php');

if(!$_SESSION['OBJ_user']->isDeity()) {
    header('location:index.php');
    exit();
}

// Need to do core version check
if(version_compare($GLOBALS['core']->version, '0.9.3-4') < 0) {
    $content .= 'This module requires a phpWebSite core version of 0.9.3-4 or greater to install.<br />';
    $content .= '<br />You are currently using phpWebSite core version ' . $GLOBALS['core']->version . '.<br />';
    return;
}

/* Create image directory */
$phpwsbb_sourcedir = $GLOBALS['core']->source_dir . 'mod/phpwsbb/img/';
$phpwsbb_imagedir = $GLOBALS['core']->home_dir . 'images/phpwsbb/';
if(PHPWS_File::recursiveFileCopy($phpwsbb_sourcedir, $phpwsbb_imagedir)) {
    $content .= 'phpwsBB image directory ' . $phpwsbb_imagedir . ' successfully created!<br />';
} else {
    $content .= 'phpwsBB could not create the image directory: ' . $phpwsbb_imagedir . '<br />You will have to do this manually!. Please also make sure it\'s writable before trying again.<br />';
    $content .= 'phpwsBB has not been installed.<br />';
    return;
}

if($status = $GLOBALS['core']->sqlImport(PHPWS_SOURCE_DIR . 'mod/phpwsbb/boost/install.sql', TRUE)) {

    require_once(PHPWS_SOURCE_DIR . 'mod/help/class/CLS_help.php');
    CLS_help::setup_help('phpwsbb');

    $content .= 'All Bulletin Board tables successfully written.<br />';
} else {
    $content .= 'There was a problem writing to the database!<br />';
}

?>
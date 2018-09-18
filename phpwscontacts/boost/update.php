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
 * @version     $Id: update.php,v 1.24 2004/09/14 03:09:09 rizzo Exp $
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

// Load help information
require_once(PHPWS_SOURCE_DIR . 'mod/help/class/CLS_help.php');
CLS_help::uninstall_help('phpwscontacts');
CLS_help::setup_help('phpwscontacts');

// Update Language
require_once(PHPWS_SOURCE_DIR . 'mod/language/class/Language.php');
PHPWS_Language::uninstallLanguages('phpwscontacts');
PHPWS_Language::installLanguages('phpwscontacts');

$status = 1;

if (version_compare($currentVersion, '0.4.0') < 0) {
    if ($status = $GLOBALS['core']->sqlImport(PHPWS_SOURCE_DIR . 'mod/phpwscontacts/boost/update040.sql', TRUE)) {
        $content .= 'All phpwsContacts tables successfully updated.<br />';

    } else {
        $content .= 'There was a problem writing to the database.<br />';
    }
}

if (version_compare($currentVersion, '0.5.0') < 0) {

    if ($status = $GLOBALS['core']->query('ALTER TABLE mod_phpwscontacts_contacts ADD visibility int NOT NULL DEFAULT 0', TRUE)) {
        $content .= 'All phpwsContacts tables successfully updated.<br />';

        if ($status = $GLOBALS['core']->sqlUpdate(array('visibility'=>0), 'mod_phpwscontacts_contacts')) {
            $content .= 'phpwsContacts data successfully updated.<br />';
        } else {
            $content .= 'There was a problem updating the database.<br />';
        }
    } else {
        $content .= 'There was a problem writing to the database.<br />';
    }
}

if (version_compare($currentVersion, '0.5.2') < 0) {
    // Include PEAR Date object
    require_once('Date.php');
    if ($status = $GLOBALS['core']->query('ALTER TABLE mod_phpwscontacts_contacts ADD str_birthday varchar(32), ADD str_anniversary varchar(32)', TRUE)) {
        $content .= 'All phpwsContacts tables successfully updated.<br />';

        $content .= 'Converting birthdays and anniversaries to new format ...<br />';

        $result = $GLOBALS['core']->sqlSelect('mod_phpwscontacts_contacts');
        if ($result) {
            foreach($result as $row) {
                $str_birthday = NULL;
                $str_anniversary = NULL;
                $content .= 'Updating ' . $row['label'] . ' ... ';
                if (!empty($row['birthday'])) {
                    $birthday = new Date($row['birthday']);
                    $str_birthday = $birthday->getDate(DATE_FORMAT_TIMESTAMP);
                }
                if (!empty($row['anniversary'])) {
                    $anniversary = new Date($row['anniversary']);
                    $str_anniversary = $anniversary->getDate(DATE_FORMAT_TIMESTAMP);
                }
                if ($status = $GLOBALS['core']->sqlUpdate(array('birthday'=>0,'anniversary'=>0,'str_birthday'=>$str_birthday,'str_anniversary'=>$str_anniversary), 'mod_phpwscontacts_contacts','id',$row['id'])) {
                    $content .= 'done.<br />';
                } else {
                    $content .= '<span class="errortext">*** ERROR ***</span><br />';
                }
            }
        }

        $content .= 'Conversion complete.<br />';

    } else {
        $content .= 'There was a problem writing to the database.<br />';
    }
}


if (version_compare($currentVersion, '0.7.0') < 0) {

    if ($status = $GLOBALS['core']->query('ALTER TABLE mod_phpwscontacts_contacts ADD mine smallint NOT NULL DEFAULT 0', TRUE)) {
        $content .= 'All phpwsContacts tables successfully updated.<br />';

        if ($status = $GLOBALS['core']->sqlUpdate(array('mine'=>0), 'mod_phpwscontacts_contacts')) {
            $content .= 'phpwsContacts data successfully updated.<br />';
        } else {
            $content .= 'There was a problem updating the database.<br />';
        }
    } else {
        $content .= 'There was a problem writing to the database.<br />';
    }

}


if (version_compare($currentVersion, '0.8.0') < 0) {

    if ($status = $GLOBALS['core']->query('ALTER TABLE mod_phpwscontacts_contacts ADD custom1 varchar(255), ADD custom2 varchar(255), ADD custom3 varchar(255), ADD custom4 varchar(255)', TRUE)) {
        if ($status = $GLOBALS['core']->query('ALTER TABLE mod_phpwscontacts_settings ADD custom1label varchar(255), ADD custom2label varchar(255), ADD custom3label varchar(255), ADD custom4label varchar(255)', TRUE)) {
            $content .= 'All phpwsContacts tables successfully updated.<br />';
        } else
            $content .= 'There was a problem writing to the database.<br />';
    } else
        $content .= 'There was a problem writing to the database.<br />';

}

if (version_compare($currentVersion, '0.9.0') < 0) {
    /* Need to register for search */
    require_once(PHPWS_SOURCE_DIR . 'mod/search/class/Search.php');
    PHPWS_Search::unregister('phpwscontacts'); /* Just to be safe */
    PHPWS_Search::register('phpwscontacts');

    if ($status = $GLOBALS['core']->query('ALTER TABLE mod_phpwscontacts_contacts ADD maidenname varchar(255) NOT NULL, ADD gender char(1), ADD str_deathday varchar(32)', TRUE)) {
        $content .= 'All phpwsContacts tables successfully updated.<br />';
    } else {
        $content .= 'There was a problem writing to the database.<br />';
    }
}


?>

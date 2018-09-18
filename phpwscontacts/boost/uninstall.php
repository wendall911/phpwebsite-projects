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
 * @version     $Id: uninstall.php,v 1.12 2005/01/04 06:32:53 rizzo Exp $
 */

if(!$_SESSION['OBJ_user']->isDeity()) {
    header('location:index.php');
    exit();
}

if($status = $GLOBALS['core']->sqlImport(PHPWS_SOURCE_DIR . 'mod/phpwscontacts/boost/uninstall.sql', 1, 1)) {
    $content .= 'All phpwsContacts tables successfully removed!<br />';

    $content .= 'Removing images directory ' . PHPWS_SOURCE_DIR . 'images/phpwscontacts<br />';
    if (PHPWS_File::rmdir($GLOBALS['core']->home_dir . 'images/phpwscontacts/')) {
        $content .= 'The phpwsContacts images directory was fully removed.<br />';
    } else {
        $content .= 'The phpwsContacts images directory could not be removed.<br />';
    }


    // Remove search registry in old core
    if(version_compare($GLOBALS['core']->version, '0.9.3-2') < 0)
        if(isset($_SESSION['OBJ_search']))
            $status = $GLOBALS['core']->sqlDelete('mod_search_register', 'module', 'phpwscontacts');

    // Remove help information
    require_once(PHPWS_SOURCE_DIR . 'mod/help/class/CLS_help.php');
    CLS_help::uninstall_help('phpwscontacts');

    // Remove translation
    require_once(PHPWS_SOURCE_DIR . 'mod/language/class/Language.php');
    PHPWS_Language::uninstallLanguages('phpwscontacts');

    $status = 1;

} else {
    $content .= 'There was a problem accessing to the database.<br />';
    $status = 0;
}

?>


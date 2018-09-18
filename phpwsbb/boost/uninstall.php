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
 * @version     $Id: uninstall.php,v 1.14 2004/12/11 22:55:02 singletrack Exp $
 */

require_once(PHPWS_SOURCE_DIR . 'core/File.php');
require_once(PHPWS_SOURCE_DIR . 'mod/language/class/Language.php');
require_once(PHPWS_SOURCE_DIR . 'mod/help/class/CLS_help.php');

if(!$_SESSION['OBJ_user']->isDeity()) {
    header('location:index.php');
    exit();
}

$status = 1;

if($status = $GLOBALS['core']->sqlImport(PHPWS_SOURCE_DIR . 'mod/phpwsbb/boost/uninstall.sql', 1, 1)) {
    $content .= 'All Bulletin Board tables successfully removed!<br />';

    // Remove search registry for old core
    if(version_compare($GLOBALS['core']->version, '0.9.3-2') < 0)
        if(isset($_SESSION['OBJ_search']))
            $status = $GLOBALS['core']->sqlDelete('mod_search_register', 'module', 'phpwsbb');

    // Remove help information
    CLS_help::uninstall_help('phpwsbb');

    // Remove Language
    PHPWS_Language::uninstallLanguages('phpwsbb');

    // Remove uservar entries
    $_SESSION['OBJ_user']->dropUserModule('phpwsbb');

    // Remove image dir
    PHPWS_File::rmdir($GLOBALS['core']->home_dir . 'images/phpwsbb/');

} else {
    $content .= 'There was a problem writing to the database.<br />';
    $status = 0;
}

?>


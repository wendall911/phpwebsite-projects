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
 * @version     $Id: index.php,v 1.17 2006/02/23 06:16:37 adarkling Exp $
 */

$CNT_phpwsbb['content'] = NULL;

if (!isset($_SESSION['PHPWSBB_Manager']))
    $_SESSION['PHPWSBB_Manager'] = new PHPWSBB_Manager;

if ($_SESSION['PHPWSBB_Manager']->offline() && !$_SESSION['OBJ_user']->isDeity()) { 
    $CNT_phpwsbb['title'] = $_SESSION['translate']->it('Bulletin Board Offline');
    $CNT_phpwsbb['content'] = $_SESSION['translate']->it('Sorry, the bulletin board is currently down for maintenance.');
} else {
		/* If the user just logged in, update their log times */
	 	if ($_SESSION['OBJ_user']->username && !$_SESSION['PHPWSBB_Manager']->logged_in)
	    	$_SESSION['PHPWSBB_Manager'] = new PHPWSBB_Manager;

   /* This'll let the module work without those brackets */
    if(isset($_REQUEST['PHPWS_MAN_ITEMS']) && is_numeric($_REQUEST['PHPWS_MAN_ITEMS'])) 
        $_REQUEST['PHPWS_MAN_ITEMS']=array($_REQUEST['PHPWS_MAN_ITEMS']);

    if (isset($_REQUEST['PHPWSBB_MAN_OP'])) {
        $_SESSION['PHPWSBB_Manager']->action();
    }

    if (isset($_REQUEST['PHPWSBB_FORUM_OP']) && isset($_SESSION['PHPWSBB_Manager']->forum))
        $_SESSION['PHPWSBB_Manager']->forum->action();

    if (isset($_REQUEST['PHPWSBB_THREAD_OP']) && isset($_SESSION['PHPWSBB_Manager']->thread))
        $_SESSION['PHPWSBB_Manager']->thread->action();

    if (isset($_REQUEST['PHPWSBB_MESSAGE_OP']) && isset($_SESSION['PHPWSBB_Manager']->message))
        $_SESSION['PHPWSBB_Manager']->message->action();

    if (isset($_REQUEST['IMGLib_op']) && $_SESSION['OBJ_user']->user_id) {
        /* If an image was selected from the library... */
        if ($_REQUEST['IMGLib_op']=='select_image') {
            if (isset($_POST['IMGLib_selected_image'])) {
                $_POST['IMGLib_selected_image'] = pos($_POST['IMGLib_selected_image']);
                $_POST['avatar_dir'] = $_POST['IMGLib_selected_gallery'];
                $_POST['avatar_file'] = $_POST['IMGLib_selected_image'];
            }
            $_SESSION['PHPWSBB_Manager']->_saveUserSettings();
            $_SESSION['PHPWSBB_Manager']->_getUserSettings($_POST['user']);
            return;
        }
        /* Handle gallery exit requests */
        if ($_REQUEST['IMGLib_op']=='exit') {
            if ($_REQUEST['IMGLib_return_data']=='edit')
                $_SESSION['PHPWSBB_Manager']->_getUserSettings($_REQUEST['user']);
            return;
        }
        /* Show the image galleries.  linkBack, current_image, & current_gallery
         * are already set as POSTs from the calling form.
         */
        require_once PHPWS_SOURCE_DIR . 'core/ImgLibrary.php';
        $library = new PHPWS_IMGLib($_SESSION['OBJ_user']->allow_access('phpwsbb', 'edit_avatars')
              , $_SESSION['OBJ_user']->allow_access('phpwsbb', 'edit_avatar_galleries')
              , true, null, null, null, 'phpwsbb', 'library', 'CNT_phpwsbb', 'avatar'
              , $_SESSION['PHPWSBB_Manager']->_max_avatar_size*1024
              , $_SESSION['PHPWSBB_Manager']->_max_avatar_height
              , $_SESSION['PHPWSBB_Manager']->_max_avatar_width);
        $library->action($_REQUEST['IMGLib_op']);
        /* If an image was moved between galleries... */
        if ($_REQUEST['IMGLib_op']=='move_image' && $library->_done) {
            $sql = 'UPDATE mod_phpwsbb_user_info SET avatar_dir="'
                . $library->_base_dir.'/'.$_POST['IMGLib_selected_gallery']
                . '/" WHERE avatar_dir="'
                . $library->_base_dir.'/'.$library->_current_view
                . '/" AND avatar_file IN ("'
                . implode('","', $_POST['IMGLib_selected_image'])
                . '")';
            /* Change directory names in all relevant sections */
            $GLOBALS['core']->query($sql, true);
        }
    }
}
?>

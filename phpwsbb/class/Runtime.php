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
 * @version     $Id: Runtime.php,v 1.18 2006/02/23 06:16:37 adarkling Exp $
 */

require_once(PHPWS_SOURCE_DIR . 'mod/phpwsbb/class/Forum.php');
require_once(PHPWS_SOURCE_DIR . 'mod/phpwsbb/class/Thread.php');
require_once(PHPWS_SOURCE_DIR . 'mod/phpwsbb/class/Message.php');

class PHPWSBB_Runtime {

    /**
    * Displays block with today's posts in it
    * 
    * @author   Don Seiler <don@NOSPAM.seiler.us>
    */
    function showForumsBlock() {
        // Set settings
        $result = $GLOBALS['core']->sqlSelect('mod_phpwsbb_settings');
        $showforumsblock = $result[0]['showforumsblock'];
        $blocktitle = $result[0]['forumsblocktitle'];
        $allow_anon_view = $result[0]['allow_anon_view'];
        $bboffline = $result[0]['bboffline'];

        if ($bboffline && !$_SESSION['OBJ_user']->isDeity()) {
            return;
        } else {
            if(!$showforumsblock)
                return;
    
            if(!$allow_anon_view && !$_SESSION['OBJ_user']->username)
                return;
    
            $block = NULL;
            $result = $GLOBALS['core']->sqlSelect('mod_phpwsbb_forums', 'hidden', 0, 'sortorder');
            if($result) {
                $block .= '<ul>';
                foreach($result as $row) {
                    $forum = new PHPWSBB_Forum($row);
                    //$block .= PHPWS_Text::moduleLink($forum->getLabel(), 'phpwsbb', array('PHPWSBB_MAN_OP'=>'viewforum', 'PHPWS_MAN_ITEMS'=>$forum->getId())).'<br />';
                    $block .= '<li>' . PHPWS_Text::moduleLink(PHPWS_Text::parseOutput($forum->getLabel()), 'phpwsbb', array('PHPWSBB_MAN_OP'=>'viewforum', 'PHPWS_MAN_ITEMS'=>$forum->getId())) . '</li>';
                }
                $block .= '</ul>';
		$GLOBALS['CNT_phpwsbb_forumsblock']['title'] = $blocktitle;
		$GLOBALS['CNT_phpwsbb_forumsblock']['content'] = $block;
            }
	}
    }// END FUNC showForumsBlock


    /**
    * Displays block with recently changed threads in it
    *
    * @author   Don Seiler <don@NOSPAM.seiler.us>
    */
    function showLatestThreadsBlock() {
        // Set settings
        $result = $GLOBALS['core']->sqlSelect('mod_phpwsbb_settings');
        $showlatestthreadsblock = $result[0]['showlatestthreadsblock'];
        $latestthreadsblocktitle = $result[0]['latestthreadsblocktitle'];
        $maxlatestthreads = $result[0]['maxlatestthreads'];
        $allow_anon_view = $result[0]['allow_anon_view'];
        $bboffline = $result[0]['bboffline'];

        if ($bboffline && !$_SESSION['OBJ_user']->isDeity()) {
            return;
        } else {
            if(!$showlatestthreadsblock)
                return;
    
            if(!$allow_anon_view && !$_SESSION['OBJ_user']->username)
                return;
    
            $c=0;
            $block = NULL;
            $result = $GLOBALS['core']->sqlSelect('mod_phpwsbb_threads', 'hidden', 0, 'lastpost DESC', NULL, NULL, $maxlatestthreads);
            if($result) {
                $block .= '<ul>';
                foreach($result as $row) {
                    $thread = new PHPWSBB_Forum($row);
                    if(strlen($thread->getLabel()) > 15)
                        $lbl = substr(PHPWS_Text::parseOutput($thread->getLabel()),0,15) . ' ...';
                    else
                        $lbl = PHPWS_Text::parseOutput($thread->getLabel());
                    $block .= '<li>' . PHPWS_Text::moduleLink($lbl, 'phpwsbb', array('PHPWSBB_MAN_OP'=>'view', 'PHPWS_MAN_ITEMS'=>$thread->getId())) . '</li>';
                    $c++;
                }
                $block .= '</ul>';
                $GLOBALS['CNT_phpwsbb_latestthreadsblock']['title'] = $latestthreadsblocktitle;
                $GLOBALS['CNT_phpwsbb_latestthreadsblock']['content'] = $block;
        				if($_SESSION['OBJ_user']->username)
		                $GLOBALS['CNT_phpwsbb_latestthreadsblock']['footer'] = '<a href="index.php?module=phpwsbb&amp;PHPWSBB_MAN_OP=getnew">'.$_SESSION['translate']->it('View All New Posts').'</a>';
           }
        }
    }// END FUNC showLatestThreadsBlock


}// END CLASS PHPWSBB_Runtime

?>

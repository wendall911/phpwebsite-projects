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
 * @version     $Id: runtime.php,v 1.9 2006/03/11 03:27:36 adarkling Exp $
 */

//require_once(PHPWS_SOURCE_DIR . 'mod/phpwsbb/class/Runtime.php');

if($GLOBALS['module'] == 'home') {
    // Retrieve settings
    $result = $GLOBALS['core']->sqlSelect('mod_phpwsbb_settings');
    $showforumsblock = $result[0]['showforumsblock'];
    $forumsblocktitle = $result[0]['forumsblocktitle'];
    $allow_anon_view = $result[0]['allow_anon_view'];
    $bboffline = $result[0]['bboffline'];
    $showlatestthreadsblock = $result[0]['showlatestthreadsblock'];
    $latestthreadsblocktitle = $result[0]['latestthreadsblocktitle'];
    $maxlatestthreads = $result[0]['maxlatestthreads'];

    if (($bboffline && !$_SESSION['OBJ_user']->isDeity())
    				|| (!$allow_anon_view && !$_SESSION['OBJ_user']->username)) 
        return;

    /**
    * Displays block with today's posts in it
    * 
    * @author   Don Seiler <don@NOSPAM.seiler.us>
    */
    if($showforumsblock) {
		    $block = NULL;
		    $result = $GLOBALS['core']->sqlSelect('mod_phpwsbb_forums', 'hidden', 0, 'sortorder');
		    if($result) {
		        $block .= '<ul>';
		        foreach($result as $row) {
		            $block .= '<li>' . PHPWS_Text::moduleLink(PHPWS_Text::parseOutput($row['label']), 'phpwsbb', array('PHPWSBB_MAN_OP'=>'viewforum', 'PHPWS_MAN_ITEMS'=>$row['id'])) . '</li>';
		        }
		        $block .= '</ul>';
						$GLOBALS['CNT_phpwsbb_forumsblock']['title'] = $forumsblocktitle;
						$GLOBALS['CNT_phpwsbb_forumsblock']['content'] = $block;
		    }
		}
		// END showForumsBlock


    /**
    * Displays block with recently changed threads in it
    *
    * @author   Don Seiler <don@NOSPAM.seiler.us>
    */
    if($showlatestthreadsblock) {
        $c=0;
        $block = NULL;
        $result = $GLOBALS['core']->sqlSelect('mod_phpwsbb_threads', 'hidden', 0, 'lastpost DESC', NULL, NULL, $maxlatestthreads);
        if($result) {
            $block .= '<ul>';
            foreach($result as $row) {
                if(strlen($row['label']) > 15) {
                    $title = PHPWS_Text::parseOutput($row['label']);
                    $lbl = substr($title,0,15) . ' ...';
                }
                else {
                    $title = $lbl = PHPWS_Text::parseOutput($row['label']);
                }
                $block .= '<li>' . PHPWS_Text::moduleLink($lbl, 'phpwsbb', array('PHPWSBB_MAN_OP'=>'view', 'PHPWS_MAN_ITEMS'=>$row['id']), NULL, '" title="'.$title) . '</li>';
                $c++;
            }
            $block .= '</ul>';
            $GLOBALS['CNT_phpwsbb_latestthreadsblock']['title'] = $latestthreadsblocktitle;
            $GLOBALS['CNT_phpwsbb_latestthreadsblock']['content'] = $block;
    				if($_SESSION['OBJ_user']->username)
                $GLOBALS['CNT_phpwsbb_latestthreadsblock']['footer'] = '<a href="index.php?module=phpwsbb&amp;PHPWSBB_MAN_OP=getnew">'.$_SESSION['translate']->it('View All New Posts').'</a>';
       }
    }
    // END showLatestThreadsBlock

}
?>

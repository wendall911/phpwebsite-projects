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
 * @version     $Id: manager.php,v 1.15 2006/02/04 01:33:42 singletrack Exp $
 */

/* The name of my lists and their corresponding database constraints */
$lists = array( 'forums'=>'approved=1',
                'threads'=>'approved=1');

/* The name of the tables to pull the lists from */
$tables = array('forums'=>'mod_phpwsbb_forums',
                'threads'=>'mod_phpwsbb_threads');

/* The directory where my templates are located */
$templates = array( 'forums'=>'forums',
                    'threads'=>'threads');

/* The columns to list for my defined 'saved' list and their labels */
$forumsColumns = array( 'label'=>$_SESSION['translate']->it('Forum'),
                        'description'=>$_SESSION['translate']->it('Description'),
                        'threads'=>$_SESSION['translate']->it('Topics'),
                        'lastpost'=>$_SESSION['translate']->it('Last Post'),
                        'posts'=>$_SESSION['translate']->it('Posts'),
                        'lastpost_topic_label'=>NULL,
                        'lastpost_topic_id'=>NULL,
                        'lastpost_post_id'=>NULL,
                        'editor'=>NULL,
                        'id'=>NULL );

/* The actions to show in the defined 'saved' list and their labels */
$forumsActions = array( 'view'=>'View',
                        'delete'=>'Delete',
                        'hide'=>'Hide',
                        'show'=>'Show'
                        );

/* The permissions associated with the defined actions above */
$forumsPermissions = array( 'view'=>NULL,
                            'delete'=>'delete_forums',
                            'hide'=>'hide_forums',
                            'show'=>'hide_forums'
                            );

/* The paging parameters for the list */
$forumsPaging = array(  'op'=>'PHPWSBB_MAN_OP=list',
                        'limit'=>50,
                        'section'=>1,
                        'limits'=>array(5,10,25,50),
                        'back'=>'&#60;&#60;',
                        'forward'=>'&#62;&#62;'
                        );

/* The columns to list for my defined 'saved' list and their labels */
$result = $GLOBALS['core']->sqlSelect('mod_phpwsbb_settings');
if ($result[0]['use_views']) {
    $threadsColumns = array(   'label'=>$_SESSION['translate']->it('Topic'),
                                'owner'=>$_SESSION['translate']->it('Poster'),
                                'replies'=>$_SESSION['translate']->it('Replies'),
                                'views'=>$_SESSION['translate']->it('Views'),
                                'lastpost'=>$_SESSION['translate']->it('Last Post'),
                                'id'=>NULL,
                                'locked'=>NULL,
                                'lastpost_post_id'=>NULL,
                                'sticky'=>NULL );
} else {
    $threadsColumns = array(   'label'=>$_SESSION['translate']->it('Topic'),
                                'owner'=>$_SESSION['translate']->it('Poster'),
                                'replies'=>$_SESSION['translate']->it('Replies'),
                                'lastpost'=>$_SESSION['translate']->it('Last Post'),
                                'id'=>NULL,
                                'locked'=>NULL,
                                'lastpost_post_id'=>NULL,
                                'sticky'=>NULL );
}
/* The actions to show in the defined 'saved' list and their labels */
$threadsActions = array(   'view'=>'View',
                            'delete'=>'Delete',
                            'lock'=>'Lock',
                            'unlock'=>'Unlock',
                            'hide'=>'Hide',
                            'show'=>'Show'
                        );

/* The permissions associated with the defined actions above */
$threadsPermissions = array(   'view'=>NULL,
                                'delete'=>'delete_threads',
                                'lock'=>'lock_threads',
                                'unlock'=>'lock_threads',
                                'hide'=>'hide_threads',
                                'show'=>'hide_threads'
                            );

/* The paging parameters for the list */
$threadsPaging = array(    'op'=>'PHPWSBB_MAN_OP=viewforum',
                            'limit'=>50,
                            'section'=>1,
                            'limits'=>array(5,10,25,50),
                            'back'=>'&#60;&#60;',
                            'forward'=>'&#62;&#62;'
                        );
?>

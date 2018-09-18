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
 * @version     $Id: update.php,v 1.52 2006/03/01 14:43:16 singletrack Exp $
 */

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

// Load needed classes
require_once(PHPWS_SOURCE_DIR . 'mod/phpwsbb/class/Manager.php');

// Load help information
require_once(PHPWS_SOURCE_DIR . 'mod/help/class/CLS_help.php');
CLS_help::uninstall_help('phpwsbb');
CLS_help::setup_help('phpwsbb');

// Update Language
require_once(PHPWS_SOURCE_DIR . 'mod/language/class/Language.php');
PHPWS_Language::uninstallLanguages('phpwsbb');
PHPWS_Language::installLanguages('phpwsbb');

$status = 1;

// Don't trigger any monitors
$_REQUEST['run_silent'] = 1;

// This might not exist during update
if (!isset($_SESSION['PHPWSBB_Manager']))
    $_SESSION['PHPWSBB_Manager'] = new PHPWSBB_Manager;

if(version_compare($currentVersion, '0.4.0') < 0) {
    if ($status = $GLOBALS['core']->sqlImport(PHPWS_SOURCE_DIR . 'mod/phpwsbb/boost/update040.sql', TRUE)) {
        $content .= 'All phpwsBB tables successfully updated.<br />';

    } else {
        $content .= 'There was a problem writing to the database.<br />';
    }
}

if(version_compare($currentVersion, '0.5.0') < 0) {
    if($status = $GLOBALS['core']->query('ALTER TABLE mod_phpwsbb_settings ADD admin_email varchar(80), ADD email_text text, ADD monitor_posts smallint', TRUE)) {
        $content .= 'phpwsBB settings table successfully modified.<br />';
        if($status = $GLOBALS['core']->sqlUpdate(array('email_text'=>'The thread [name] has been updated.  Go to [url] to view it.', 'monitor_posts'=>0), 'mod_phpwsbb_settings')) {
            $content .= 'phpwsBB settings table successfully updated.<br />';
        } else {
            $content .= 'There was a problem updating the phpwsBB settings.<br />';
        }
    } else {
        $content .= 'There was a problem modifying the phpwsBB settings table.<br />';
    }
}

if(version_compare($currentVersion, '0.5.2') < 0) {
    // Remove hard-coded labels in subjects
    $content .= 'Updating labels in sticky and locked threads ...<br />';
    $sql = "SELECT id FROM mod_phpwsbb_threads where label like '%" . $_SESSION['translate']->it('STICKY') . "%' or label like '%" . $_SESSION['translate']->it('LOCKED') . "%'";
    $result = $GLOBALS['core']->getCol($sql,TRUE);
    if($result) {
        foreach($result as $rowid) {
            $thread = new PHPWSBB_Thread($rowid);
            if(strpos($thread->_label, $_SESSION['translate']->it('STICKY'))) {
                $thread->setLabel(str_replace('[' . $_SESSION['translate']->it('STICKY') . '] ', '',  $thread->_label));
                $thread->commit(NULL, true);
                $content .= 'Updated sticky thread ' . $thread->_label . '.<br />';
            }

            if(strpos($thread->_label, $_SESSION['translate']->it('LOCKED'))) {
                $thread->setLabel(str_replace('[' . $_SESSION['translate']->it('LOCKED') . '] ', '',  $thread->_label));
                $thread->commit(NULL, true);
                $content .= 'Updated locked thread ' . $thread->_label . '.<br />';
            }

            sleep(1);
        }
    }
}

if(version_compare($currentVersion, '0.5.3') < 0) {
    // Remove hard-coded labels in subjects
    $content .= 'Updating labels in messages ...<br />';
    $sql = 'SELECT id FROM mod_phpwsbb_messages';
    $result = $GLOBALS['core']->getCol($sql,TRUE);
    if($result) {
        foreach($result as $rowid) {
            $message = new PHPWSBB_Message($rowid);
            if(strpos($message->_label, $_SESSION['translate']->it('STICKY'))) {
                $message->setLabel(str_replace('[' . $_SESSION['translate']->it('STICKY') . '] ', '',  $message->_label));
                $message->commit(NULL, true);
                $content .= 'Updated message ' . $message->_label . '.<br />';
            }

            if(strpos($message->_label, $_SESSION['translate']->it('LOCKED'))) {
                $message->setLabel(str_replace('[' . $_SESSION['translate']->it('LOCKED') . '] ', '',  $message->_label));
                $message->commit(NULL, true);
                $content .= 'Updated message ' . $message->_label . '.<br />';
            }

            sleep(1);
        }
    }
}

if(version_compare($currentVersion, '0.6.0') < 0) {
    if ($status = $GLOBALS['core']->sqlImport(PHPWS_SOURCE_DIR . 'mod/phpwsbb/boost/update060.sql', TRUE)) {
        $content .= 'All phpwsBB tables successfully updated.<br />';

    } else {
        $content .= 'There was a problem writing to the database.<br />';
    }

    if($status = $GLOBALS['core']->query('ALTER TABLE mod_phpwsbb_threads ADD fid int NOT NULL default 0', TRUE)) {
        $content .= 'Successfully modified the phpwsBB threads table.<br />';

        // Add one forum and assign all threads to it.
        $forum = new PHPWSBB_Forum();
        $forum->setLabel('General Discussion');
        $forum->_description = 'Topics on anything and everything.';
        $forum->commit();
        if($status = $GLOBALS['core']->sqlUpdate(array('fid'=>$forum->getId()),'mod_phpwsbb_threads')) {
            $content .= 'Successfully added default forum and assigned all threads to it.<br />';
            $forum->updateForum(true);
        }
    } else {
        $content .= 'There was a problem modifying the phpwsBB threads table.<br />';
    }

    $content .= 'Setting manager to NULL ... ';
    $_SESSION['PHPWSBB_Manager'] = NULL;
    if(is_null($_SESSION['PHPWSBB_Manager']))
        $content .= 'Done<br />';
    else
        $content .= 'NO<br />';

    $content .= 'Unsetting manager ... ';
    unset($_SESSION['PHPWSBB_Manager']);
    if(!isset($_SESSION['PHPWSBB_Manager']))
        $content .= 'Done<br />';
    else
        $content .= 'NO<br />';

}

if(version_compare($currentVersion, '0.7.0') < 0) {
    if($status = $GLOBALS['core']->sqlCreateIndex('mod_phpwsbb_threads',array('fid')))
        if($status = $GLOBALS['core']->sqlCreateIndex('mod_phpwsbb_messages',array('tid')))
            if($status = $GLOBALS['core']->sqlCreateIndex('mod_phpwsbb_monitors',array('thread_id')))
                $content .= 'Successfully added foreign key indexes.<br />';
}

if(version_compare($currentVersion, '0.8.0') < 0) {
    if($status = $GLOBALS['core']->query('ALTER TABLE mod_phpwsbb_threads ADD lastpost int NOT NULL default 0', TRUE)) {
        if($status = $GLOBALS['core']->query('ALTER TABLE mod_phpwsbb_forums ADD lastpost int NOT NULL default 0', TRUE)) {
            $sql = 'SELECT id FROM mod_phpwsbb_threads';
            $result = $GLOBALS['core']->getCol($sql,TRUE);
            if($result) {
                foreach($result as $rowid) {
                    $thread = new PHPWSBB_Thread($rowid);
                    $thread->updateThread(false, true);
                }
            }

            $content .= 'Successfully modified the phpwsBB threads table.<br />';
        } else {
            $content .= 'There was a problem modifying the phpwsBB forums table.<br />';
        }
    } else {
        $content .= 'There was a problem modifying the phpwsBB threads table.<br />';
    }

    if($status = $GLOBALS['core']->query('ALTER TABLE mod_phpwsbb_settings ADD showforumsblock smallint NOT NULL default 1, ADD forumsblocktitle  varchar(80) NULL, ADD showlatestthreadsblock smallint NOT NULL default 1, ADD latestthreadsblocktitle varchar(80) NULL, ADD maxlatestthreads int NOT NULL default 0', TRUE)) {
        $content .= 'phpwsBB settings table successfully modified.<br />';
        if($status = $GLOBALS['core']->sqlUpdate(
            array(  'showforumsblock'=>1,
                    'forumsblocktitle'=>'Forums',
                    'showlatestthreadsblock'=>1,
                    'latestthreadsblocktitle'=>'Latest Forum Posts',
                    'maxlatestthreads'=>5 )
                    , 'mod_phpwsbb_settings')) {
            $content .= 'phpwsBB settings table successfully updated.<br />';
        } else {
            $content .= 'There was a problem updating the phpwsBB settings.<br />';
        }
    } else {
        $content .= 'There was a problem modifying the phpwsBB settings table.<br />';
    }
}

if(version_compare($currentVersion, '0.9.0') < 0) {
    if($status = $GLOBALS['core']->query("ALTER TABLE mod_phpwsbb_settings ADD bboffline smallint NOT NULL default '0', ADD allow_user_monitors smallint NOT NULL default '1'", TRUE)) {
        $content .= 'phpwsBB settings table successfully updated.<br />';
        if($status = $GLOBALS['core']->sqlUpdate(
            array(  'allow_user_monitors'=>1,
                    'bboffline'=>0 )
                    , 'mod_phpwsbb_settings')) {
            $content .= 'phpwsBB settings table successfully updated.<br />';
        } else {
            $content .= 'There was a problem updating the phpwsBB settings.<br />';
        }
    } else {
        $content .= 'There was a problem updating the phpwsBB settings.<br />';
    }
}

if(version_compare($currentVersion, '0.9.1') < 0) {
    /* Create image directory */
    PHPWS_File::makeDir($GLOBALS['core']->home_dir . 'images/phpwsbb');
    if (is_dir($GLOBALS['core']->home_dir . 'images/phpwsbb')) {
        $content .= 'phpwsBB image directory ' . $GLOBALS['core']->home_dir . 'images/phpwsbb/ successfully created!<br />';
        /* Copy image files */
        $images = array();
        if($handle = opendir(PHPWS_SOURCE_DIR . 'mod/phpwsbb/img/')) {
            while (false !== ($file = readdir($handle))) {
                //if ($file != '.' && $file != '..')
                if (is_file(PHPWS_SOURCE_DIR . 'mod/phpwsbb/img/${file}'))
                    if(!copy(PHPWS_SOURCE_DIR . 'mod/phpwsbb/img/${file}', $GLOBALS['core']->home_dir . 'images/phpwsbb/${file}'))
                        $content .= 'Failed to copy ${file}<br />\n';
            }
        }
    } else
        $content .= 'phpwsBB could not create the image directory: ' . $GLOBALS['core']->home_dir . 'images/phpwsbb/<br />You will have to do this manually!<br />';
}

if(version_compare($currentVersion, '1.0.0') < 0) {
    if($status = $GLOBALS['core']->query('ALTER TABLE mod_phpwsbb_messages ADD owner_id int NOT NULL default 0 AFTER owner', TRUE)
      && $status = $GLOBALS['core']->sqlCreateIndex('mod_phpwsbb_messages',array('owner_id'))) {

        /* Make a list of names and message ids of all message contributors */
        $msg_list = $name_list = array();

        $result = $GLOBALS['core']->query('SELECT id,owner from mod_phpwsbb_messages WHERE owner IS NOT NULL', true);
        while ($result && $row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
            if (!$row['owner']) continue;
            $msg_list[$row['owner']][] = $row['id'];
            $name_list[$row['owner']] = $row['owner'];
        }
        unset($result);

        /* Make a list of all user ids for the users in the last list */
        $result = $GLOBALS['core']->query('SELECT username,user_id from mod_users WHERE username IN ("' . implode('","', $name_list) .'")', true);
        while ($result && $row = $result->fetchrow(DB_FETCHMODE_ASSOC))
            $name_tbl[$row['username']] = $row['user_id'];
        unset($result);

        /* Update user id information for all messages submitted by each user */
        if (count($msg_list) && count($name_list)) {
            foreach ($msg_list AS $key=>$value) {
                if (!isset($name_tbl[$key])) $name_tbl[$key] = 0;
                $GLOBALS['core']->query('UPDATE mod_phpwsbb_messages SET owner_id=' . $name_tbl[$key].' WHERE id IN ('.implode(',', $value).')', true);
            }
        }
        $content .= 'Successfully modified the phpwsBB messages table.<br />';
    } else 
        $content .= 'There was a problem modifying the phpwsBB messages table.<br />';

    if($status = $GLOBALS['core']->query("CREATE TABLE mod_phpwsbb_user_ranks (rank_id int DEFAULT '0' NOT NULL, rank_title varchar(50) NOT NULL, rank_min smallint DEFAULT '0' NOT NULL, rank_special smallint DEFAULT '0', rank_image varchar(255), rank_image_caption varchar(255), PRIMARY KEY (rank_id))", TRUE)
      && $status = $GLOBALS['core']->sqlInsert(array('rank_title'=>'Site Admin','rank_min'=>'0','rank_special'=>'1','rank_image'=>NULL,'rank_image_caption'=>NULL), 'mod_phpwsbb_user_ranks')
      && $status = $GLOBALS['core']->sqlInsert(array('rank_title'=>'Member','rank_min'=>'0','rank_special'=>'0','rank_image'=>NULL,'rank_image_caption'=>NULL), 'mod_phpwsbb_user_ranks'))
        $content .= 'Successfully created the phpwsBB User Ranking table.<br />';
    else 
        $content .= 'There was a problem creating the phpwsBB User Ranking table.<br />';

  if($status = $GLOBALS['core']->sqlCreateIndex('mod_phpwsbb_threads',array('fid')))
    $content .= 'Successfully added indexes.<br />';

    if($status = $GLOBALS['core']->query('ALTER TABLE mod_phpwsbb_threads ADD lastpost_post_id int NOT NULL default "0"', TRUE)) {
        if($status = $GLOBALS['core']->query('ALTER TABLE mod_phpwsbb_forums ADD posts int NOT NULL default "0", ADD lastpost_topic_label text NOT NULL, ADD lastpost_topic_id int NOT NULL default "0", ADD lastpost_post_id int NOT NULL default "0", ADD moderators varchar(40) NOT NULL default ""', TRUE)) {
            $sql = 'SELECT id FROM mod_phpwsbb_threads';
            $result = $GLOBALS['core']->getCol($sql,TRUE);
            if($result) {
                foreach($result as $rowid) {
                    $thread = new PHPWSBB_Thread($rowid);
                    $thread->updateThread(false, true);
                }
            }
            $content .= 'Successfully modified the phpwsBB threads table.<br />';
        } else 
            $content .= 'There was a problem modifying the phpwsBB forums table.<br />';
    } else 
        $content .= 'There was a problem modifying the phpwsBB threads table.<br />';

    if($status = $GLOBALS['core']->query('ALTER TABLE mod_phpwsbb_settings ADD use_avatars smallint NOT NULL default "1", ADD use_offsite_avatars smallint NOT NULL default "0", ADD max_avatar_height int NOT NULL default "90", ADD max_avatar_width int NOT NULL default "90", ADD max_avatar_size int NOT NULL default "6", ADD use_signatures smallint NOT NULL default "1"', TRUE)) 
        $content .= 'phpwsBB settings table successfully modified.<br />';
    else 
        $content .= 'There was a problem modifying the phpwsBB settings table.<br />';

    if($status = $GLOBALS['core']->query('CREATE TABLE mod_phpwsbb_user_info (user_id int NOT NULL, posts int NOT NULL default "0", location varchar(50) NOT NULL, avatar_dir varchar(50) NOT NULL, avatar_file varchar(100) NOT NULL, signature varchar(255),suspendmonitors smallint NOT NULL default "0", monitordefault smallint NOT NULL default "1", PRIMARY KEY (user_id))', TRUE)) 
        // User post counts will be created as users' messages are viewed
        $content .= 'Successfully created the phpwsBB User Information table.<br />';
    else 
        $content .= 'There was a problem creating the phpwsBB User Information table.<br />';
}
if(version_compare($currentVersion, '1.0.1') < 0) {
    $content .= 'Small bug fixes. Language filtering should now work.<br />';
    $content .= 'You can now uninstall, even though we highly recommend not doing so. ;)<br />';
}
if(version_compare($currentVersion, '1.0.2') < 0) {
    $content .= 'No longer allow forking on threads with a single post.<br />';
    $content .= 'Added "Back to the forum" link after posting.<br />';
    $content .= 'Added height and width attributes to all images.<br />';
}
if(version_compare($currentVersion, '1.0.3') < 0) {
    $content .= 'Several bug fixes see ChangeLog.<br />';
}
 
if(version_compare($currentVersion, '1.0.4') < 0) {
    $content .= '<br />v1.0.4<br />';
    $content .= '* Added private forums.<br />';
    $content .= '* Added reroute to logins if you\'re not logged in when posting.<br />';
    $content .= '* Added Category view.<br />';
    
    if ($status = $GLOBALS['core']->query('ALTER TABLE mod_phpwsbb_threads ADD views int NOT NULL default "0" AFTER replies', TRUE))
        $content .= '* Added Thread view count feature.<br />';
    else
        $content .= '<div class="errortext">There was a problem creating the views column to the thread table.</div>';

    if ($status = $GLOBALS['core']->query('ALTER TABLE mod_phpwsbb_settings ADD use_views smallint NOT NULL default "0", ADD use_low_priority smallint NOT NULL default "0"', TRUE))
        $content .= '* Added Settings for view counts.<br />';
    else
        $content .= '<div class="errortext">There was a problem creating the views settings in the settings table.</div>';

    if ($status = $GLOBALS['core']->query('ALTER TABLE mod_phpwsbb_user_info ADD session_start int NOT NULL default "0", ADD last_on int NOT NULL default "0"', TRUE)) {
        $content .= '* Added Settings for "View new posts" feature.<br />';
        $GLOBALS['core']->query('UPDATE mod_phpwsbb_user_info SET session_start = '.time()-86400, TRUE);
    }
    else
        $content .= '<div class="errortext">There was a problem creating the "View new posts" settings in the User Settings table.</div>';

    if ($status = $GLOBALS['core']->query('ALTER TABLE mod_phpwsbb_settings ADD show_categories smallint NOT NULL default "1"', TRUE))
        $content .= '* Added Settings for category related features.<br />';
    else
        $content .= '<div class="errortext">There was a problem creating the category settings in the settings table.</div>';

    $content .= '* Copying images to /images/phpwsbb/<br />';
    /* Copy new image files */
    $images = array();
    if($handle = opendir(PHPWS_SOURCE_DIR . 'mod/phpwsbb/img/')) {
        while (false !== ($file = readdir($handle))) {
            if (is_file(PHPWS_SOURCE_DIR . 'mod/phpwsbb/img/${file}'))
                if(!copy(PHPWS_SOURCE_DIR . 'mod/phpwsbb/img/${file}', $GLOBALS['core']->home_dir . 'images/phpwsbb/${file}'))
                    $content .= '<div class="errortext">Failed to copy ${file}</div>\n';
        }
    }
}

?>
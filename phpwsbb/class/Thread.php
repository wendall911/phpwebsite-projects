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
 * @version     $Id: Thread.php,v 1.86 2006/03/06 01:00:28 singletrack Exp $
 */

/** 
 * @since 0.6.0
 */
require_once(PHPWS_SOURCE_DIR . 'core/Item.php');
require_once(PHPWS_SOURCE_DIR . 'core/EZform.php');
require_once(PHPWS_SOURCE_DIR . 'core/Error.php');
require_once(PHPWS_SOURCE_DIR . 'core/Text.php');
require_once(PHPWS_SOURCE_DIR . 'mod/phpwsbb/class/Message.php');
require_once(PHPWS_SOURCE_DIR . 'mod/phpwsbb/class/Forum.php');
require_once(PHPWS_SOURCE_DIR . 'mod/users/class/Users.php');

/* PEAR mail class */
require_once('Mail.php');

class PHPWSBB_Thread extends PHPWS_Item {

    /**
     * Sticky flag
     *
     * @var      bit
     * @access   private
     */
    var $_sticky = 0;

    /**
     * Locked flag
     *
     * @var      bit
     * @access   private
     */
    var $_locked = 0;

    /**
     * Number of replies to this message
     *
     * @var      integer
     * @access   private
     */
    var $_replies = 0;

    /**
     * Number of times the thread has been viewed?
     *
     * @var      integer
     * @access   private
     */
    var $_views = 0;

    /**
     * Forum ID of this thread
     *
     * @var      integer
     * @access   private
     */
    var $_fid = 0;

    /**
     * unix timestamp of last post
     *
     * @var      integer
     * @access   private
     */
    var $_lastpost = 0;

    /**
    * ID of the last post
    *
    * @var      integer
    * @access   private
    */
    var $_lastpost_post_id = 0;

    /**
     * The ids of all the messages under this one
     *
     * @var      array
     * @access   private
     */
    var $messages = array();


    /**
     * Bool if this thread is monitored by this user
     *
     * @var      boolean
     * @access   private
     */
    var $monitored = FALSE;


    /**
     * Name of forum
     *
     * @var      string
     * @access   private
     */
    var $forumlabel = FALSE;


    /**
     * Forum Moderator flag
     *
     * @var      bool
     * @access   private
     */
    var $isModerator = FALSE;

    function PHPWSBB_Thread($THREAD_ID = NULL) {
        /* These variable are excluded when calling commit() */
        $exclude = array('messages', 'monitored', 'forumlabel', 'isModerator');
        $this->addExclude($exclude);

        /* Set database table */
        $this->setTable('mod_phpwsbb_threads');

        /* Populate this object if ID exists */
        if (isset($THREAD_ID) && is_numeric($THREAD_ID)) {
            $this->setId($THREAD_ID);
            $this->init();

            if (isset($this->_fid)) {
                $f = new PHPWSBB_Forum($this->_fid);
                $this->forumlabel = $f->getLabel();
                $this->isModerator = $f->isModerator;
                $f = NULL;
            }

            $sql = 'SELECT id FROM ' . $GLOBALS['core']->tbl_prefix . 'mod_phpwsbb_messages WHERE tid=' . $this->getId();
            if (!($_SESSION['OBJ_user']->allow_access('phpwsbb', 'hide_threads') && $this->isModerator))
                $sql .= ' AND hidden=0';
            $sql .= ' ORDER BY created';
            $this->messages = $GLOBALS['core']->getCol($sql);

            // Monitor stuff
            if ($_SESSION['PHPWSBB_Manager']->_allow_user_monitors) {
                if (!empty($_SESSION['OBJ_user']->user_id)) {
                    $data = array();
                    $data['thread_id'] = $this->getId();
                    $data['user_id'] = $_SESSION['OBJ_user']->user_id;
                    if ($test_sql = $GLOBALS['core']->sqlSelect('mod_phpwsbb_monitors',$data))
                        $this->monitored = TRUE;
                }
            }
        } elseif (is_array($THREAD_ID)) {
            $this->init($THREAD_ID);
        }
    }// END FUNC PHPWSBB_Thread


    function _view($ro = FALSE) {
        if ($this->isHidden() && !($_SESSION['OBJ_user']->allow_access('phpwsbb', 'hide_threads') && $this->isModerator)) {
            $content = '<div class="errortext">' . $_SESSION['translate']->it('There is nothing here to see.') . '</div>';
            return $content;
        }

        // Restricted Group Access
        $forum = new PHPWSBB_Forum($this->_fid);
        if(!empty($forum->_groups)) {
            $restricted = TRUE;
            foreach($forum->_groups as $group) {
                if(PHPWS_User::userInGroup($group)) { $restricted = FALSE; break; }
            }
            if(($restricted) && !$_SESSION['OBJ_user']->allow_access('phpwsbb', 'edit_forums')) {
                $content = '<div class="errortext">' . $_SESSION['translate']->it('Sorry, you do not have access to this forum.') . '</div>';
                return $content;
            }		
        }

        if (count($this->messages) == 0) {
            $content = '<div class="errortext">' . $_SESSION['translate']->it('This topic has no messages.') . '</div>';
            if ($_SESSION['OBJ_user']->allow_access('phpwsbb', 'delete_threads') && $this->isModerator)
                $content .= '<br />' . $this->createLink($_SESSION['translate']->it('Delete Topic'), array('PHPWSBB_MAN_OP' => 'delete', 'PHPWS_MAN_ITEMS[]' => $this->getId()));
            return $content;
        }

        // Not sure if we want this
        if ($_SESSION['OBJ_fatcat'])
            $_SESSION['OBJ_fatcat']->whatsRelated($this->_fid);

        $tags = array();

        $tags['ID']         = $this->getId();
        $tags['OWNER']      = $this->getOwner();
        $tags['EDITOR']     = $this->getEditor();
        $tags['IP']         = $this->getIp();
        $tags['CREATED']    = $this->getCreated();
        $tags['UPDATED']    = $this->getUpdated();
        $tags['LABEL']      = PHPWS_Text::parseOutput($this->getLabel());
        $tags['REPLIES']    = $this->_replies;

        if ($_SESSION['PHPWSBB_Manager']->_use_views) {
            $tags['VIEWS']      = $this->_views;
        }

        if (!$ro) {
            if (!$this->_locked || $_SESSION['OBJ_user']->allow_access('phpwsbb', 'edit_messages') || $this->isModerator) {
              $reply = $_SESSION['translate']->it('Reply');
              $tags['REPLY'] = $this->createLink($reply, array('PHPWSBB_MAN_OP'=>'reply',
                'PHPWS_MAN_ITEMS[]'=>$this->getId()), 'reply_message.png', 16, 16);
            }
            if ($_SESSION['OBJ_user']->allow_access('phpwsbb', 'lock_threads') && $this->isModerator) {
                if ($this->_locked) {
                    $unlock = $_SESSION['translate']->it('Unlock Topic');
                    $tags['LOCK'] = $this->createLink($unlock, array('PHPWSBB_MAN_OP'=>'unlock',
                      'PHPWS_MAN_ITEMS[]'=>$this->getId()), 'unlocked.png', 16, 16);
                } else {
                    $lock = $_SESSION['translate']->it('Lock Topic');
                    $tags['LOCK'] = $this->createLink($lock, array('PHPWSBB_MAN_OP'=>'lock',
                      'PHPWS_MAN_ITEMS[]'=>$this->getId()), 'locked.png', 16, 16);
                }
            }

            if ($_SESSION['OBJ_user']->allow_access('phpwsbb', 'move_threads') && $this->isModerator) {
                // Need drop-down of forums
                $forums = array();
                $results = $GLOBALS['core']->sqlSelect('mod_phpwsbb_forums',NULL,NULL,'sortorder');
                if ($results)
                    foreach($results as $rec)
                        $forums[$rec['id']] = PHPWS_Text::parseOutput($rec['label']);

                $form = new EZform('PHPWSBB_thread_move');

                $form->add('Thread_fid', 'select', $forums);
                $form->setMatch('Thread_fid',$this->_fid);
                $form->setTab('Thread_fid',1);

                $form->add('Thread_move', 'submit', $_SESSION['translate']->it('Move to Forum'));
                $form->setTab('Thread_move',2);

                $form->add('module','hidden','phpwsbb');
                $form->add('PHPWSBB_THREAD_OP', 'hidden', 'move');

                $tags = $form->getTemplate(TRUE,TRUE,$tags);
            }

            if ($_SESSION['OBJ_user']->allow_access('phpwsbb', 'delete_threads') && $this->isModerator) {
                $delete = $_SESSION['translate']->it('Delete Topic');
                $tags['DELETE'] = $this->createLink($delete, array('PHPWSBB_MAN_OP'=>'delete',
                  'PHPWS_MAN_ITEMS[]'=>$this->getId()), 'delete.png', 16, 16);
            }

            if ($_SESSION['OBJ_user']->allow_access('phpwsbb', 'hide_threads') && $this->isModerator) {
                if ($this->isHidden()) {
                    $showthread = $_SESSION['translate']->it('Show Topic');
                    $tags['HIDE'] = $this->createLink($showthread, array('PHPWSBB_MAN_OP'=>'showthread',
                      'PHPWS_MAN_ITEMS[]'=>$this->getId()), 'hideshow.png', 16, 14);
                } else {
                    $hidethread = $_SESSION['translate']->it('Hide Topic');
                    $tags['HIDE'] = $this->createLink($hidethread, array('PHPWSBB_MAN_OP'=>'hidethread',
                      'PHPWS_MAN_ITEMS[]'=>$this->getId()), 'hideshow.png', 16, 14);
                }
            }

            if ($_SESSION['OBJ_user']->allow_access('phpwsbb', 'sticky_threads') && $this->isModerator) {
                if ($this->_sticky) {
                    $unstick = $_SESSION['translate']->it('Unstick Topic');
                    $tags['STICKY'] = $this->createLink($unstick, array('PHPWSBB_MAN_OP'=>'unstick',
                      'PHPWS_MAN_ITEMS[]'=>$this->getId()), 'sticky.png', 16, 16);
                } else {
                    $stick = $_SESSION['translate']->it('Stick Topic');
                    $tags['STICKY'] = $this->createLink($stick, array('PHPWSBB_MAN_OP'=>'stick',
                      'PHPWS_MAN_ITEMS[]'=>$this->getId()), 'sticky.png', 16, 16);
                }
            }
        }

        if ($_SESSION['PHPWSBB_Manager']->_allow_user_monitors) {
            if (isset($_SESSION['OBJ_user']->username))
                if ($this->monitored) {
                    $unmonitor = $_SESSION['translate']->it('Stop Monitor');
                    $tags['MONITOR'] = $this->createLink($unmonitor, array('PHPWSBB_MAN_OP'=>'unmonitor',
                      'PHPWS_MAN_ITEMS[]'=>$this->getId()), 'unmonitor.png', 16, 16);
                } else {
                    $monitor = $_SESSION['translate']->it('Monitor');
                    $tags['MONITOR'] = $this->createLink($monitor, array('PHPWSBB_MAN_OP'=>'monitor',
                      'PHPWS_MAN_ITEMS[]'=>$this->getId()), 'monitor.png', 16, 16);
                }
        }

        // Need to cycle through all messages and view
        $content = '';
        $bg = NULL;

				/* Retrieve all messages for this thread */
			  $sql = 'SELECT * FROM mod_phpwsbb_messages WHERE id IN ('.implode(',', $this->messages).') ORDER BY created';
			  if($result = $GLOBALS['core']->query($sql, 1)) {
					  while($row=$result->fetchrow(DB_FETCHMODE_ASSOC)) {
								$message = new PHPWSBB_Message(null, $this->isModerator, $row);
								PHPWS_WizardBag::toggle($bg, ' class="bg_light"');
								$content .= $message->_view($bg, $this->_replies);
					  }
				}
				unset($result);

        $tags['AUTHOR'] = $_SESSION['translate']->it('Author');
        $tags['MESSAGE'] = $_SESSION['translate']->it('Message');
        if($_SESSION['OBJ_user']->user_id) {
            $mysettings = $_SESSION['translate']->it('Settings');
            $tags['MYPROFILE'] = $this->createLink($mysettings, array('PHPWSBB_MAN_OP'=>'usersettings',
              'PHPWS_MAN_ITEMS[]'=>$this->getId()), 'mysettings.png', 16, 16);
        }

        // Update Views stat
        $this->updateViews();
            
        return '<a name="bb-top" /></a>'
            . PHPWS_Template::processTemplate($tags, 'phpwsbb', 'view_thread_top.tpl')
            . $content
            . PHPWS_Template::processTemplate($tags, 'phpwsbb', 'view_thread_bottom.tpl');

    }// END FUNC _view


    function updateViews() {
        if ($_SESSION['PHPWSBB_Manager']->_use_views) {
            if ($_SESSION['PHPWSBB_Manager']->_use_low_priority) {
                $GLOBALS['core']->query('UPDATE LOW_PRIORITY ' . $GLOBALS['core']->tbl_prefix . 'mod_phpwsbb_threads SET views = views + 1 WHERE id=' . $this->getId());
            } else {
                $GLOBALS['core']->query('UPDATE ' . $GLOBALS['core']->tbl_prefix . 'mod_phpwsbb_threads SET views = views + 1 WHERE id=' . $this->getId());
            }
        }
    }
    
    function updateThread($new = FALSE, $suppress_sets = FALSE) {
        if (!empty($this->_id)) {
            $new = TRUE;
            $sql = 'SELECT id FROM ' . $GLOBALS['core']->tbl_prefix . 'mod_phpwsbb_messages WHERE tid=' . $this->getId() . ' ORDER BY created;';
            $this->messages = $GLOBALS['core']->getCol($sql);
            $this->_replies = count($this->messages) - 1;
            if ($this->_replies < 0) {
                /*
                 * This means the only message in the thread was deleted via
                 * the message delete inteface.  Kill this thread.
                 */
                $this->kill();

                // Need to remove all monitors
                $GLOBALS['core']->sqlDelete('mod_phpwsbb_monitors', 'thread_id', $this->getId());

                // Need to update forum counts
                $forum = new PHPWSBB_Forum($this->_fid);
                $forum->updateForum();

                 return;
            } else {
                // Update thread label with label of root message
                $message = new PHPWSBB_Message($this->messages[0]);
                $this->setLabel($message->getLabel());

                // Update lastpost with created of last message
                $message = new PHPWSBB_Message($this->messages[count($this->messages)-1]);
                $this->_lastpost = $message->_created;
                $this->_lastpost_post_id = $message->getId();
            }

            // Update owner to guestname of root message if empty
            //  This is a backport for a fix that was implemented in 0.3.1
            if ($this->getOwner() == '')
                if (!empty($message->_guestname))
                    $this->_owner = $message->_guestname;

        } else {
            $this->_replies = 0;
        }
        $this->commit(null, $suppress_sets);

        $forum = new PHPWSBB_Forum($this->_fid);
        $forum->updateForum($suppress_sets);

        // Send email to monitors for new messages
        if (!isset($_SESSION['PHPWSBB_Manager']))
            echo '<h3>phpwsbb manager not set!</h3>';
        if (!isset($_SESSION['OBJ_user']))
            echo '<h3>session user not set!</h3>';

        // This switch allows mass updates to occur without sending a mass email
        if(isset($_REQUEST['run_silent']))
            return;

        $user = $_SESSION['PHPWSBB_Manager']->_load_user_settings($_SESSION['OBJ_user']->user_id);
        if (isset($user['suspendmonitors']) && $_SESSION['PHPWSBB_Manager']->_allow_user_monitors 
          && $new && !$user['suspendmonitors']) {
            $sql = "SELECT email FROM " . $GLOBALS['core']->tbl_prefix . "mod_phpwsbb_monitors a," . $GLOBALS['core']->tbl_prefix . "mod_users b WHERE a.thread_id=" . $this->getId() . " AND b.user_id=a.user_id and a.user_id != '" . $_SESSION['OBJ_user']->user_id . "'";
            $emails = $GLOBALS['core']->getCol($sql);
    
            $admin_email = $_SESSION['PHPWSBB_Manager']->_admin_email;
            if (empty($admin_email)) {
                $user_contact = NULL;
                extract(PHPWS_User::getSettings());
                $admin_email = $user_contact;
            }
            $from = '"' . $_SESSION['translate']->it('Bulletin Board Admin') . '" <'.$admin_email.'>';
            $subject = $_SESSION['translate']->it('Reply to "[var1]"', $this->getLabel(0));
            $message = $_SESSION['PHPWSBB_Manager']->_email_text;
            // Replace [name] and [url] in $message
            $message = str_replace('[name]', '"' . strip_tags($this->getLabel()) . '"', $message);
            // Got rid of the brackets so that the link isn't broken in email messages
            $message = str_replace('[url]', 'http://' . PHPWS_HOME_HTTP . 'index.php?module=phpwsbb&PHPWSBB_MAN_OP=view&PHPWS_MAN_ITEMS=' . $this->getId(), $message);
    
            foreach ($emails as $email) {
                $mail_object =& Mail::factory('mail');
                $headers['From'] = $from;
                $headers['Subject'] = $subject;
                // $headers['To'] = $email;
                $mail_object->send($email, $headers, $message);
            }
    
            if (isset($user['monitordefault']) && !$this->monitored && $user['monitordefault'])
                $this->_monitor();
        }

    }// END FUNC updateThread


    function _delete() {
        if (!($_SESSION['OBJ_user']->allow_access('phpwsbb', 'delete_threads') && $this->isModerator)) {
            $message = $_SESSION['translate']->it('Access to delete this topic was denied due to lack of proper permissions.');
            $error = new PHPWS_Error('phpwsbb', 'PHPWSBB_Thread::_delete()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        if (isset($_REQUEST['option_yes'])) {
            $this->kill();

            // Need to delete all messages
            $GLOBALS['core']->sqlDelete('mod_phpwsbb_messages', 'tid', $this->getId());
            // Need to remove all monitors
            $GLOBALS['core']->sqlDelete('mod_phpwsbb_monitors', 'thread_id', $this->getId());

            $forum = new PHPWSBB_Forum($this->_fid);
            $forum->updateForum();

            $message = $_SESSION['translate']->it('The topic [var1] was successfully deleted from the database.', '<b><i>' . $this->getLabel() . '</i></b>');
            $_SESSION['PHPWSBB_Manager']->notice = new PHPWS_Message($message, 'CNT_phpwsbb');
            $_REQUEST['PHPWSBB_MAN_OP'] = 'list';
            $_SESSION['PHPWSBB_Manager']->action();

        } elseif (isset($_REQUEST['option_no'])) {
            $message = $_SESSION['translate']->it('No topic was deleted from the database.');
            $_SESSION['PHPWSBB_Manager']->notice = new PHPWS_Message($message, 'CNT_phpwsbb');
            $_REQUEST['PHPWSBB_MAN_OP'] = 'list';
            $_SESSION['PHPWSBB_Manager']->action();

        } else {
            $form = new EZform('PHPWSBB_Thread_delete');
            $form->add('module', 'hidden', 'phpwsbb');
            $form->add('PHPWSBB_THREAD_OP', 'hidden', 'delete');

            $form->add('option_yes', 'submit', $_SESSION['translate']->it('Yes'));
            $form->add('option_no', 'submit', $_SESSION['translate']->it('No'));

            $tags = array();
            $tags = $form->getTemplate();
            $tags['MESSAGE'] = $_SESSION['translate']->it('Are you sure you want to delete this topic?');
            
            $GLOBALS['CNT_phpwsbb']['title'] = $_SESSION['translate']->it('Delete Topic Confirmation');
            $GLOBALS['CNT_phpwsbb']['content'] = PHPWS_Template::processTemplate($tags, 'phpwsbb', 'delete.tpl');
        }


    }// END FUNC _delete


    function _lock($unlock = FALSE) {
        if (!($_SESSION['OBJ_user']->allow_access('phpwsbb', 'lock_threads') && $this->isModerator)) {
            $message = $_SESSION['translate']->it('Access to lock this topic was denied due to lack of proper permissions.');
            $error = new PHPWS_Error('phpwsbb', 'PHPWSBB_Thread::_lock()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        if ($unlock && $this->_locked)
            $this->_locked = 0;
        elseif (!$unlock && !$this->_locked)
            $this->_locked = 1;
        else {
            if ($this->_locked)
                $GLOBALS['CNT_phpwsbb']['content'] .= '<div class="errortext">' . $_SESSION['translate']->it('This topic is already locked.') . '</div><br />';
            else
                $GLOBALS['CNT_phpwsbb']['content'] .= '<div class="errortext">' . $_SESSION['translate']->it('This topic is already unlocked.') . '</div><br />';

            $_REQUEST['PHPWSBB_THREAD_OP'] = 'view';
            $this->action();
            return;
        }

        $error = $this->commit();
        if (PHPWS_Error::isError($error)) {
            if ($unlock)
                $message = $_SESSION['translate']->it('The topic could not be unlocked.');
            else
                $message = $_SESSION['translate']->it('The topic could not be locked.');
            $error = new PHPWS_Error('phpwsbb', $message, 'continue', 0);
            $error->message('CNT_phpwsbb');

            $_REQUEST['PHPWSBB_THREAD_OP'] = 'view';
            $this->action();
            return;
        } else {
            if ($unlock)
            	$GLOBALS['CNT_phpwsbb']['content'] .= $_SESSION['translate']->it('The topic was successfully unlocked.') . '<br />';
            else
            	$GLOBALS['CNT_phpwsbb']['content'] .= $_SESSION['translate']->it('The topic was successfully locked.') . '<br />';
            $_REQUEST['PHPWSBB_THREAD_OP'] = 'view';
            $this->action();
        }

    }// END FUNC _lock


    function _stick($unstick = FALSE) {
        if (!($_SESSION['OBJ_user']->allow_access('phpwsbb', 'sticky_threads') && $this->isModerator)) {
            $message = $_SESSION['translate']->it('Access to stick this topic was denied due to lack of proper permissions.');
            $error = new PHPWS_Error('phpwsbb', 'PHPWSBB_Thread::_stock()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        if ($unstick && $this->_sticky)
            $this->_sticky = 0;
        elseif (!$unstick && !$this->_sticky)
            $this->_sticky = 1;
        else {
            if ($this->_sticky)
                $GLOBALS['CNT_phpwsbb']['content'] .= '<div class="errortext">' . $_SESSION['translate']->it('This topic is already sticky.') . '</div><br />';
            else
                $GLOBALS['CNT_phpwsbb']['content'] .= '<div class="errortext">' . $_SESSION['translate']->it('This topic is already not sticky.') . '</div><br />';

            $_REQUEST['PHPWSBB_THREAD_OP'] = 'view';
            $this->action();
            return;
        }

        $error = $this->commit();
        if (PHPWS_Error::isError($error)) {
            if ($unstick)
            	$message = $_SESSION['translate']->it('The topic could not be unstuck.');
            else
            	$message = $_SESSION['translate']->it('The topic could not be stuck.');	
            $error = new PHPWS_Error('phpwsbb', $message, 'continue', 0);
            $error->message('CNT_phpwsbb');

            $_REQUEST['PHPWSBB_THREAD_OP'] = 'view';
            $this->action();
            return;
        } else {
            if ($unstick)
            	$GLOBALS['CNT_phpwsbb']['content'] .= $_SESSION['translate']->it('The topic was successfully unstuck.') . '<br />';
            else
            	$GLOBALS['CNT_phpwsbb']['content'] .= $_SESSION['translate']->it('The topic was successfully stuck.') . '<br />';

            $_REQUEST['PHPWSBB_THREAD_OP'] = 'view';
            $this->action();
        }

    }// END FUNC _stick


    function _monitor() {
        if ($_SESSION['PHPWSBB_Manager']->_allow_user_monitors) {
            if (empty($_SESSION['OBJ_user']->user_id)) {
                $content = '<div class="errortext">' . $_SESSION['translate']->it('You must be a registered user and logged in to monitor topics.') . '</div>';
            } else {
                $data = array();
                $data['thread_id'] = $this->getId();
                $data['user_id'] = $_SESSION['OBJ_user']->user_id;
                if ($GLOBALS['core']->sqlInsert($data, 'mod_phpwsbb_monitors', TRUE, FALSE, FALSE, FALSE)) {
                    $content = '<p>' . $_SESSION['translate']->it('You are now monitoring the topic "[var1]" and will be emailed when there is a new reply.', PHPWS_Text::parseOutput($this->getLabel())) . '</p>';
                    $this->monitored = TRUE;
                } else {
                    $content = '<div class="errortext">' . $_SESSION['translate']->it('There was a problem adding the monitor entry.  Please try again or contact the system administrator.') . '</div>';
                }
                $content .= '<p>' . $this->createLink($_SESSION['translate']->it('Back to the topic.'),
                  array('PHPWSBB_MAN_OP'=>'view','PHPWS_MAN_ITEMS[]'=>$this->getId())) . '&nbsp;|&nbsp;';
                $content .= $this->createLink($_SESSION['translate']->it('Back to the forum.'),
                  array('PHPWSBB_MAN_OP'=>'viewforum','PHPWS_MAN_ITEMS[]'=>$this->_fid)) . '</p>';
            }
            return $content;
        }
    }// END FUNC _monitor


    function _unmonitor() {
        if ($_SESSION['PHPWSBB_Manager']->_allow_user_monitors) {
            $data = array();
            $data['thread_id'] = $this->getId();
            $data['user_id'] = $_SESSION['OBJ_user']->user_id;
            if ($GLOBALS['core']->sqlDelete('mod_phpwsbb_monitors', $data)) {
                $content = '<p>' . $_SESSION['translate']->it('Monitor successfully removed.') . '</p>';
                $this->monitored = FALSE;
            } else {
                $content = '<div class="errortext">' . $_SESSION['translate']->it('There was a problem removing the monitor entry.  Please try again or contact the system administrator.') . '</div>';
            }
            $content .= '<p>' . $this->createLink($_SESSION['translate']->it('Back to the topic.'),
                array('PHPWSBB_MAN_OP'=>'view','PHPWS_MAN_ITEMS[]'=>$this->getId())) . '&nbsp;|&nbsp;';
            $content .= $this->createLink($_SESSION['translate']->it('Back to the forum.'),
                array('PHPWSBB_MAN_OP'=>'viewforum','PHPWS_MAN_ITEMS[]'=>$this->_fid)) . '</p>';
            return $content;
        }
    }// END FUNC _unmonitor


    function _move() {
        $oldfid = $this->_fid;
        $this->_fid = $_REQUEST['Thread_fid'];
        if ($oldfid == $this->_fid) {
            $GLOBALS['CNT_phpwsbb']['content'] = '<div class="errortext">' . $_SESSION['translate']->it('You tried to move it to current forum.') . '</div>';
            $_REQUEST['PHPWSBB_THREAD_OP'] = 'view';
            $this->action();
            return;
        }
            //return '<div class="errortext">' . $_SESSION['translate']->it('You tried to move it to current forum.') . '</div>';
        $this->commit();

        // Update Forum Stats
        $forum = new PHPWSBB_Forum($oldfid);
        $forum->updateForum();
        $forum = new PHPWSBB_Forum($this->_fid);
        $forum->updateForum();
        $this->forumlabel = $forum->getLabel();

        $GLOBALS['CNT_phpwsbb']['content'] = $_SESSION['translate']->it('Topic moved to [var1].', $this->getForumLabel(TRUE));
        $_REQUEST['PHPWSBB_THREAD_OP'] = 'view';
        $this->action();

        //return '<p>' . $_SESSION['translate']->it('Topic moved to [var1].', $this->getForumLabel(TRUE)) . '</p>';
    }// END FUNC _move


    function action() {
	$title =  null;
        switch($_REQUEST['PHPWSBB_THREAD_OP']) {
            case 'view':
                $title = $_SESSION['translate']->it('View Topic');
                $content = $_SESSION['PHPWSBB_Manager']->_menu(NULL, $this);
                $content .= $this->_view();
                PHPWS_Layout::addPageTitle($this->getLabel(0));
                break;

            case 'delete':
                $this->_delete();
                break;

            case 'lock':
                $this->_lock();
                break;

            case 'unlock':
                $this->_lock(TRUE);
                break;

            case 'stick':
                $this->_stick();
                break;

            case 'unstick':
                $this->_stick(TRUE);
                break;

            case 'monitor':
                $title = $_SESSION['translate']->it('Monitor');
                $content = $_SESSION['PHPWSBB_Manager']->_menu(NULL, $this);
                $content .= $this->_monitor();
                break;

            case 'unmonitor':
                $title = $_SESSION['translate']->it('Monitor');
                $content = $_SESSION['PHPWSBB_Manager']->_menu(NULL, $this);
                $content .= $this->_unmonitor();
                break;

            case 'move':
                //$title = $_SESSION['translate']->it('Topic Moved');
                //$content = $_SESSION['PHPWSBB_Manager']->_menu(NULL, $this) . 
                $this->_move();
                break;

            default:
                $title = 'NO OP';
                $content = 'This function is not yet implemented';
        }

        if (isset($content)) {
            $GLOBALS['CNT_phpwsbb']['title'] = $title;
            $GLOBALS['CNT_phpwsbb']['content'] .= $content;
        }
    }// END FUNC _action


    function getLabel($prefix = TRUE) {
        $lab = parent::getLabel();
        if ($prefix) {
            if ($this->isHidden())
                $lab = '<img src="./images/phpwsbb/hideshow.png" border="0" alt="' . $_SESSION['translate']->it('Hidden') . '" title="' . $_SESSION['translate']->it('Hidden') . '" />&nbsp;' . $lab;
            if ($this->_locked)
                $lab = '<img src="./images/phpwsbb/locked.png" border="0" alt="' . $_SESSION['translate']->it('Locked') . '" title="' . $_SESSION['translate']->it('Locked') . '" />&nbsp;' . $lab;
            if ($this->_sticky) 
                $lab = '<img src="./images/phpwsbb/sticky.png" border="0" alt="' . $_SESSION['translate']->it('Sticky') . '" title="' . $_SESSION['translate']->it('Sticky') . '" />&nbsp;' . $lab;
        }

        return $lab;
    }

    function getLastPost() {
        if ($this->_lastpost) {
            $poster = $this->getEditor();
            if(empty($poster))
                $poster = $_SESSION['translate']->it('Guest');

            $retstring = NULL;
            //$retstring .= '<font size="-2">';
            $retstring .= $_SESSION['translate']->it('[var1]<br />by [var2]'
                , '<a href="./index.php?module=phpwsbb&amp;PHPWSBB_MAN_OP=view&amp;PHPWS_MAN_ITEMS='.$this->getId().'#'.$this->_lastpost_post_id.'">'
                    .date(PHPWS_DATE_FORMAT . " " . PHPWS_TIME_FORMAT, $this->_lastpost)
                    .'</a>'
                , $poster);
            //$retstring .= '</font>';
            return $retstring;
        } else return NULL;
    }// END FUNC getLastPost


    function getForumLabel($link=FALSE) {
        $ret = $this->forumlabel;
        if ($link) {
            $ret = PHPWS_Text::moduleLink($this->forumlabel, 'phpwsbb', array('PHPWSBB_MAN_OP'=>'viewforum', 'PHPWS_MAN_ITEMS'=>$this->_fid));
        }
        return $ret;
    }
    /**
     * Quick link function for phpwsBB
     * Based on PHPWS_Text:imageLink by Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
     * @param string title String to appear as the 'click on' word(s) or image alt
     * @param array getVars Associative array of GET variable to append to the link
     * @param string image Name of image file will automatically add path info
     * @param string height Height of image, requires $image
     * @param string width Width of image, requres $image
     * @param string link Change default link
     * @param string target Window target
     * @param class Stylesheet style
     * @return string
     */
    function createLink($title, $getVars=NULL, $image=NULL, $height=NULL, $width=NULL, $link=NULL, $target=NULL, $class=NULL) {
        if(!is_null($link))
            $link = '<a href="' . $link;
        else
            $link = '<a href="./index.php?module=phpwsbb';

        if (is_array($getVars)){
            foreach ($getVars as $var_name=>$value){
                $link .= '&amp;';
                $link .= $var_name . '=' . $value;
                $i = 1;
            }
        }

        if ($target == 'blank' || $target === TRUE)
            $linkTarget = ' target="_blank" ';
        elseif ($target == 'index')
            $linkTarget = ' target="index" ';
        else
            $linkTarget = NULL;

        $link .= '"';

        if(isset($class))
            $link .= ' class="' . $class . '"';
        if (!is_null($image) && !is_null($width) && !is_null($height)) {
            $image = './images/phpwsbb/' . $image;
            $img = '<img src="' . $image . '" width="' . $width . '" height="' .
              $height . '" border="0" alt="' . $title . '" title="' . $title .
              '" /> ' . $title;
            if($link !== '-1')
                return $link . $linkTarget . '>' . $img . '</a>';
            else
                return $img;
        } else {
            return $link . $linkTarget . '>' . $title . '</a>';
        }
    }// END FUNC bbLink()

}// END CLASS PHPWSBB_Thread
?>

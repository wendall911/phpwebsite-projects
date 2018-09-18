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
 * @version     $Id: Message.php,v 1.89 2006/03/01 18:14:52 singletrack Exp $
 */

require_once(PHPWS_SOURCE_DIR.'core/Item.php');
require_once(PHPWS_SOURCE_DIR.'core/EZform.php');
require_once(PHPWS_SOURCE_DIR.'core/Error.php');
require_once(PHPWS_SOURCE_DIR.'core/Text.php');
require_once(PHPWS_SOURCE_DIR.'core/WizardBag.php');
require_once(PHPWS_SOURCE_DIR.'core/Message.php');
require_once(PHPWS_SOURCE_DIR.'mod/boost/class/Boost.php');
require_once(PHPWS_SOURCE_DIR.'mod/phpwsbb/class/Thread.php');

/* PEAR mail class */
require_once('Mail.php');

class PHPWSBB_Message extends PHPWS_Item {

    /**
     * The user_id of the user who currently owns this item.
     *
     * @var     integer
     * @example $this->_owner_id = 43;
     * @access  private
     */
    var $_owner_id = NULL;

    /**
     * The thread id of this message
     *
     * @var      integer
     * @access   private
     */
    var $_tid = 0;

    /**
     * The name used by guest poster
     *
     * @var      string
     * @access   private
     */
    var $_guestname = NULL;

    /**
     * The email address  used by guest poster
     *
     * @var      string
     * @access   private
     */
    var $_guestemail = NULL;

    /**
     * The body of the message
     *
     * @var      string
     * @access   private
     */
    var $_body = NULL;

    /**
     * Forum Moderator flag
     *
     * @var      bool
     * @access   private
     */
    var $isModerator = FALSE;


    /**
     * Constructor
     *
     * @param int $MESSAGE_ID optional
     */
    function PHPWSBB_Message($MESSAGE_ID = NULL, $isModerator=FALSE, $message_data=null) {
        /* These variable are excluded when calling commit() */
        $exclude = array('isModerator');
        $this->addExclude($exclude);

        /* Set database table */
        $this->setTable('mod_phpwsbb_messages');

        /* Populate this object if ID exists */
        if (isset($MESSAGE_ID)) {
            $this->setId($MESSAGE_ID);
            $this->init();
        }
        /* otherwise, populate this object if data exists */
        if (isset($message_data)) {
            $this->init($message_data);
        }
        $this->isModerator = $isModerator;

    }// END FUNC PHPWSBB_Message


    /**
     * Builds display page for message
     */
    function _view($bg, $thread_messagecount=null) {
        // If thread_messagecount wasn't given, retrieve it now
        if($thread_messagecount == null) {
        		$thread = new PHPWSBB_Thread($this->_tid);
        		$thread_messagecount = $thread->_replies;
        }
        $tags = array();

        // Load all user information for this message
        $tags = $_SESSION['PHPWSBB_Manager']->_load_user_settings($this->_owner_id);
        $tags['BG']         = $bg;
        $tags['QUOTE']      = PHPWSBB_Thread::createLink($_SESSION['translate']->it('Quote'), array('PHPWSBB_MAN_OP'=>'reply', 'PHPWS_MAN_ITEMS'=>$this->_tid, 'reply_to'=>$this->getId()), 'quote_message.png', 16, 16);
        $tags['NAV']        = PHPWSBB_Thread::createLink($_SESSION["translate"]->it('Back to top'),NULL,'back_to_top.png',16,16,'#bb-top');
        $tags['ID']         = $this->getId();
        $tags['OWNER']      = $this->getOwner();
        $tags['EDITOR']     = $this->getEditor();
        $tags['IP']         = $this->getIp();
        $tags['CREATED']    = $_SESSION['translate']->it('Posted on').': '.$this->getCreated();
        $tags['UPDATED']    = $_SESSION['translate']->it('Updated on').': '.$this->getUpdated();
        $tags['SUBJECT']    = PHPWS_Text::parseOutput($this->getLabel());
        // Format all message quoting
        $this->_body = preg_replace(array('/\[quote="(.*)"\]/isU', '/\[quote(?!=".*").*\]/isU', '/\[\/quote\]/isU'), array('<fieldset>&#013;<legend><b>\1</b></legend>&#013;\2', '<fieldset>&#013;', '</fieldset>&#013;'), $this->_body);
        $tags['BODY']       = PHPWS_Text::parseOutput($this->_body);
        $tags['TID']        = $this->_tid;
        $tags['REPORT']     = PHPWSBB_Thread::createLink($_SESSION['translate']->it('Report this post to administrator'),
          array('PHPWSBB_MAN_OP'=>'report','PHPWS_MAN_ITEMS[]'=>$this->getId()),'report.png',16,16);

        $poster = $this->getOwner();
        if (is_null($poster)) {
            $poster = PHPWS_Text::parseOutput($this->_guestname) . ' (' . $_SESSION['translate']->it('GUEST') . ')';
            $posteremail = PHPWS_Text::parseOutput($this->_guestemail);
        } else {
            if ($_SESSION['OBJ_user']->username && $_SESSION['OBJ_user']->username!=$this->getOwner()) {
                if (in_array('notes', $_SESSION['PHPWSBB_Manager']->installed_modules)) {
		                $tags['NOTE'] = '<a href="index.php?module=notes&amp;NOTE_op=new_note&amp;NOTE_toUser=' . $this->getOwner() . '"><img src="./images/phpwsbb/new_note.png" border="0" height="16" width="16" alt="' . $_SESSION['translate']->it('Send note to poster') . '" title="' . $_SESSION['translate']->it('Send note to poster') . '" />&nbsp;' . $_SESSION['translate']->it('Note to [var1]',$this->getOwner()) . '</a>';
                }
            }

            if (in_array('phpwscontacts', $_SESSION['PHPWSBB_Manager']->installed_modules)) {
                require_once(PHPWS_SOURCE_DIR.'mod/phpwscontacts/class/Contact.php');
                // Find contact owned by this username
                $sql = "select id from " . $GLOBALS["core"]->tbl_prefix . "mod_phpwscontacts_contacts where owner='" . $this->getOwner() . "' and mine=1";
                $results = $GLOBALS['core']->getCol($sql);
                if ($results) {
                    //$poster = '<a href="index.php?module=phpwscontacts&amp;CONTACTS_MAN_OP=view&amp;PHPWS_MAN_ITEMS[]=' . $results[0] . '">$poster</a>';
                    $contact = new CONTACTS_Contact($results[0]);
                    $poster = '<a href="index.php?module=phpwscontacts&amp;CONTACTS_MAN_OP=view&amp;PHPWS_MAN_ITEMS[]=' . $contact->getId() . '">' . $contact->getListFirstName() . ' ' . $contact->getListLastName() . '</a>';
                }
            }
        }

        $tags['POSTER'] = $poster;
        $tags['POSTED'] = $_SESSION['translate']->it('Posted by [var1] on [var2]', $poster, $this->getCreated());

        // Present edit info
        if ($this->getCreated() != $this->getUpdated())
            $tags['EDITED'] = $_SESSION['translate']->it('Edited by [var1] on [var2]', $this->getEditor(), $this->getUpdated());

        if (($_SESSION['OBJ_user']->allow_access('phpwsbb', 'edit_messages') && $this->isModerator) || ($this->getOwner() && ($_SESSION['OBJ_user']->username == $this->getOwner())))
            $tags['EDIT'] = '<a href="./index.php?module=phpwsbb&amp;PHPWSBB_MAN_OP=edit&amp;PHPWS_MAN_ITEMS[]=' . $this->getId() . '"><img src="./images/phpwsbb/edit.png" border="0" height="16" width="16" alt="' . $_SESSION['translate']->it('Edit') . '" title="' . $_SESSION['translate']->it('Edit') . '" />&nbsp;' . $_SESSION['translate']->it('Edit') . '</a>';

        if (($_SESSION['OBJ_user']->allow_access('phpwsbb', 'delete_messages') && $this->isModerator) || ($this->getOwner() && ($_SESSION['OBJ_user']->username == $this->getOwner())))
            $tags['DELETE'] = '<a href="./index.php?module=phpwsbb&amp;PHPWSBB_MAN_OP=deletemessage&amp;PHPWS_MAN_ITEMS[]=' . $this->getId() . '"><img src="./images/phpwsbb/delete.png" border="0" height="16" width="16" alt="' . $_SESSION['translate']->it('Delete') . '" title="' . $_SESSION['translate']->it('Delete') . '" />&nbsp;' . $_SESSION['translate']->it('Delete') . '</a>';

        if ($_SESSION['OBJ_user']->allow_access('phpwsbb', 'hide_messages') && $this->isModerator) {
            if ($this->isHidden())
                $tags['HIDE'] = '<a href="./index.php?module=phpwsbb&amp;PHPWSBB_MAN_OP=showmessage&amp;PHPWS_MAN_ITEMS[]=' . $this->getId() . '"><img src="./images/phpwsbb/hideshow.png" border="0" height="16" width="16" alt="' . $_SESSION['translate']->it('Show Message') . '" title="' . $_SESSION['translate']->it("Show Message") . '" />&nbsp;' . $_SESSION['translate']->it('Show Message') . '</a>';
            else
                $tags['HIDE'] = '<a href="./index.php?module=phpwsbb&amp;PHPWSBB_MAN_OP=hidemessage&amp;PHPWS_MAN_ITEMS[]=' . $this->getId() . '"><img src="./images/phpwsbb/hideshow.png" border="0" height="16" width="16" alt="' . $_SESSION['translate']->it('Hide Message') . '" title="' . $_SESSION['translate']->it('Hide Message') . '" />&nbsp;' . $_SESSION['translate']->it('Hide Message') . '</a>';
        }

        if (($_SESSION['OBJ_user']->allow_access('phpwsbb', 'fork_messages') && $this->isModerator) && ($thread_messagecount > 0))
            $tags['FORK'] = '<a href="./index.php?module=phpwsbb&amp;PHPWSBB_MAN_OP=fork&amp;PHPWS_MAN_ITEMS[]=' . $this->getId() . '"><img src="./images/phpwsbb/fork.png" border="0" height="16" width="16" alt="' . $_SESSION['translate']->it('Fork') . '" title="' . $_SESSION['translate']->it('Fork') . '" />&nbsp;' . $_SESSION['translate']->it('Fork') . '</a>';

        if ($_SESSION['OBJ_user']->allow_access('phpwsbb', 'view_ips') && $this->isModerator) {
            $tags['IP_TEXT'] = $_SESSION['translate']->it('IP Address');
            $tags['IPADDR'] = $this->getIp();
        }

        if ($_SESSION['OBJ_user']->allow_access('phpwsbb', 'edit_users') && ($this->getOwner())) 
            $tags['EDIT_USER'] = '<a href="./index.php?module=phpwsbb&amp;PHPWSBB_MAN_OP=usersettings&amp;user=' . $this->_owner_id . '"><img src="./images/phpwsbb/mysettings.png" border="0" height="16" width="16" alt="' . $_SESSION['translate']->it('Edit [var1]', $this->getOwner()) . '" title="' . $_SESSION['translate']->it('Edit [var1]', $this->getOwner()) . '" />&nbsp;' . $_SESSION['translate']->it('Edit [var1]', $this->getOwner()) . '</a>';

        if ($_SESSION['OBJ_user']->allow_access('phpwsbb', 'ban') && $this->isModerator) {
            $match = array('ip'=>$this->getIp(),'username'=>$this->getOwner());
            $results = $GLOBALS['core']->sqlSelect('mod_phpwsbb_banned',$match,NULL,NULL,NULL,'or');
            if ($results) {
                $ub = FALSE;
                $ib = FALSE;
                foreach ($results as $rec) {
                    if ($rec['ip'] == $this->getIp())
                        $ib = TRUE;
                    if ($this->getOwner() && ($rec['username'] == $this->getOwner()))
                        $ub = TRUE;
                }

                if ($ib)
                    $tags['BAN_IP'] = ' <a href="./index.php?module=phpwsbb&amp;PHPWSBB_MAN_OP=unbanip&amp;PHPWS_MAN_ITEMS[]=' . $this->getIp() . '"><img src="./images/phpwsbb/ok.png" border="0" height="16" width="16" alt="' . $_SESSION['translate']->it('Unban this IP') . '" title="' . $_SESSION['translate']->it('Unban this IP') . '" />&nbsp;' . $_SESSION['translate']->it('Unban this IP') . '</a>';
                else
                    $tags['BAN_IP'] = '<a href="./index.php?module=phpwsbb&amp;PHPWSBB_MAN_OP=banip&amp;PHPWS_MAN_ITEMS[]=' . $this->getIp() . '"><img src="./images/phpwsbb/ban.png" border="0" height="16" width="16" alt="' . $_SESSION['translate']->it('Ban this IP') . '" title="' . $_SESSION['translate']->it('Ban this IP') . '" />&nbsp;' . $_SESSION['translate']->it('Ban this IP') . '</a>';

                if ($this->getOwner())
                    if ($ub)
                        $tags['BAN_USERNAME'] = '<a href="./index.php?module=phpwsbb&amp;PHPWSBB_MAN_OP=unbanusername&amp;PHPWS_MAN_ITEMS[]=' . $this->getOwner() . '"><img src="./images/phpwsbb/ok.png" border="0" height="16" width="16" alt="' . $_SESSION['translate']->it('Unban this Poster') . '" title="' . $_SESSION['translate']->it('Unban this Poster') . '" />&nbsp;' . $_SESSION['translate']->it('Allow [var1] (Banned)', $this->getOwner()) . '</a>';
                    else
                        $tags['BAN_USERNAME'] = '<a href="./index.php?module=phpwsbb&amp;PHPWSBB_MAN_OP=banusername&amp;PHPWS_MAN_ITEMS[]=' . $this->getOwner() . '"><img src="./images/phpwsbb/ban.png" border="0" height="16" width="16" alt="' . $_SESSION['translate']->it('Ban this Poster') . '" title="' . $_SESSION['translate']->it('Ban this Poster') . '" />&nbsp;' . $_SESSION['translate']->it('Ban [var1]', $this->getOwner()) . '</a>';
            } else {
                $tags['BAN_IP'] = '<a href="./index.php?module=phpwsbb&amp;PHPWSBB_MAN_OP=banip&amp;PHPWS_MAN_ITEMS[]=' . $this->getIp() . '"><img src="./images/phpwsbb/ban.png" border="0" height="16" width="16" alt="' . $_SESSION['translate']->it('Ban this IP') . '" title="' . $_SESSION['translate']->it('Ban this IP') . '" />&nbsp;' . $_SESSION['translate']->it('Ban this IP') . '</a>';
                if ($this->getOwner())
                    $tags['BAN_USERNAME'] = '<a href="./index.php?module=phpwsbb&amp;PHPWSBB_MAN_OP=banusername&amp;PHPWS_MAN_ITEMS[]=' . $this->getOwner() . '"><img src="./images/phpwsbb/ban.png" border="0" height="16" width="16" alt="' . $_SESSION['translate']->it('Ban this Poster') . '" title="' . $_SESSION['translate']->it('Ban this Poster') . '" />&nbsp;' . $_SESSION['translate']->it('Ban [var1]', $this->getOwner()) . '</a>';
            }
        }

        return PHPWS_Template::processTemplate($tags, 'phpwsbb', 'view.tpl');

    }// END FUNC _view


    /**
     * Displays message edit screen
     */
    function _edit() {
        if (!empty($this->_id) && !$_SESSION['OBJ_user']->allow_access('phpwsbb', 'edit_messages') && !$this->isModerator && ($_SESSION['OBJ_user']->username != $this->getOwner())) {
            $message = $_SESSION['translate']->it('Access to edit message [var1] was denied due to lack of proper permissions.', $this->_id);
            $error = new PHPWS_Error('phpwsbb', 'PHPWSBB_Message::_edit()', $message, 'exit', 1);
            $error->message();
            return FALSE;

        }

        // Check if username or IP is banned
        if ($this->getIp() == NULL)
            $this->setIp();
        $match = array('ip'=>$this->getIp(),'username'=>$_SESSION['OBJ_user']->username);
        $results = $GLOBALS['core']->sqlSelect('mod_phpwsbb_banned',$match,NULL,NULL,NULL,'or');
        if ($results) {
            $message = $_SESSION['translate']->it('Access to edit message [var1] was denied due to your IP Address or username being banned.', $this->_id);
            $error = new PHPWS_Error('phpwsbb', 'PHPWSBB_Message::_edit()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        $thread = NULL;
        if (isset($this->_tid) && !empty($this->_tid)) {
            // Need to see if thread is locked
            $thread = new PHPWSBB_Thread($this->_tid);
            if ($thread->_locked && !$_SESSION['OBJ_user']->allow_access('phpwsbb', 'edit_messages') && !$this->isModerator) {
                $message = '<div class="errortext">' . $_SESSION['translate']->it('This thread has been locked.') . '</div>';
                $GLOBALS['CNT_phpwsbb']['content'] .= $message;
                return;
            } else {
                // Default topic to re: current topic
                if (is_null($this->getLabel())) {
                    $label = $_SESSION['translate']->it('Re') . ': ' . $thread->getLabel(FALSE);
                    $this->setLabel(PHPWS_Text::parseOutput($label));
                }
            }
        }

        
        /* Variable to set tab order */
        $tabs = 1;

        /* Create form */
        $form = new EZform('PHPWSBB_Message_edit');

        /* Guest information, if not registered user */
        if (empty($_SESSION['OBJ_user']->user_id)) {
            $form->add('Message_guestname', 'text', $this->_guestname);
            $form->setSize('Message_guestname', 33);
            $form->setMaxSize('Message_guestname', 50);
            $form->setTab('Message_guestname', $tabs);
            $tabs++;

            $form->add('Message_guestemail', 'text', $this->_guestemail);
            $form->setSize('Message_guestemail', 33);
            $form->setMaxSize('Message_guestemail', 80);
            $form->setTab('Message_guestemail', $tabs);
            $tabs++;
        }

        /* Message Subject */
        $form->add('Message_label', 'text', $this->getLabel());
        $form->setSize('Message_label', 33);
        $form->setTab('Message_label', $tabs);
        $tabs++;

        /* Message Body */
        // If this is a reply to a specific message, quote it
        if (isset($_REQUEST['reply_to']) && is_numeric($_REQUEST['reply_to'])) {
            $result = $GLOBALS['core']->quickFetch('SELECT owner,guestname,body FROM mod_phpwsbb_messages WHERE id='.$_REQUEST['reply_to'], true);
            $poster = $result['owner'];
            if (is_null($poster))   
                $poster = $result['guestname'] . ' (' . $_SESSION['translate']->it('GUEST') . ')';
            $this->_body = '[quote="'.$poster.'"]'.PHPWS_Text::parseOutput(trim($result['body']))."\n[/quote]\n\n";
        }
        $form->add('Message_body', 'textarea', $this->_body);
        $form->setRows('Message_body',10);
        $form->setCols('Message_body',70);
        $form->setId('Message_body');
        $form->setTab('Message_body', $tabs);
        $tabs++;

        /* Save Button */
        if (isset($this->_id))
            $form->add('Message_save', 'submit', $_SESSION['translate']->it('Update Post'));
        else
            if (isset($this->_tid) && !empty($this->_tid))
                $form->add('Message_save', 'submit', $_SESSION['translate']->it('Post Reply'));
            else
                $form->add('Message_save', 'submit', $_SESSION['translate']->it('Post'));

        $form->setTab('Message_save', $tabs);
        $tabs++;

        /* Preview Button */
        $form->add('Message_preview', 'submit', $_SESSION['translate']->it('Preview'));
        $form->setTab('Message_preview', $tabs);
        $tabs++;

        /* Module Information */
        $form->add('module', 'hidden', 'phpwsbb');
        $form->add('PHPWSBB_ITEMS[]', 'hidden', $this->getId());
        $form->add('PHPWSBB_MESSAGE_OP', 'hidden', 'save');
        $form->add('Message_tid', 'hidden', $this->_tid);
        if (isset($_REQUEST['fid']))
            $form->add('Message_fid', 'hidden', $_REQUEST['fid']);
        elseif (isset($_REQUEST['Message_fid']))
            $form->add('Message_fid', 'hidden', $_REQUEST['Message_fid']);

        $tags = array();
        $tags = $form->getTemplate();

        if(isset($_REQUEST['Message_preview'])) {
            unset($_REQUEST['Message_preview']);
            $tags['PREVIEW_TEXT'] = $_SESSION['translate']->it('Preview');
            $tags['PREVIEW'] = $this->_preview();
        }

        if (empty($_SESSION['OBJ_user']->user_id)) {
            $tags['GUESTNAME_TEXT'] = $_SESSION['translate']->it('Guest Name');
            $tags['GUESTEMAIL_TEXT'] = $_SESSION['translate']->it('Guest Email');
        }

        $tags['LABEL_TEXT'] = $_SESSION['translate']->it('Subject');
        $tags['BODY_TEXT'] = $_SESSION['translate']->it('Body');

        if ($_SESSION['OBJ_user']->js_on)
            $tags['MESSAGE_BODY'] = PHPWS_WizardBag::js_insert('wysiwyg', 'PHPWSBB_Message_edit', 'Message_body') . $tags['MESSAGE_BODY'];

        $content = '<a name="bb-top"></a>'.PHPWS_Template::processTemplate($tags, 'phpwsbb', 'edit.tpl');
        if ($thread) {
            $content .= $thread->_view(TRUE);
        }

        return $content;

    }// END FUNC _edit


    /**
     * Preview the message whilst editing.
     */
    function _preview() {
        $content = PHPWS_Text::parseOutput(trim($_REQUEST['Message_body']));
        return $content;
    }// END FUNC _preview


    /**
     * Reads variables off $_REQUEST array, saves to database,
     * creating new message and thread when necessary.
     */
    function _save() {
        if (isset($_POST['PHPWSBB_ITEMS'])
          && sizeof($_POST['PHPWSBB_ITEMS']) > 0 && is_array($_POST['PHPWSBB_ITEMS']))
            $this->PHPWSBB_Message($_POST['PHPWSBB_ITEMS'][0]);
        if (!empty($this->_id) && !$_SESSION['OBJ_user']->allow_access('phpwsbb', 'edit_messages') && !$this->isModerator && ($_SESSION['OBJ_user']->username != $this->getOwner())) {
            $message = '<div class="errortext">' . $_SESSION['translate']->it('Access to save message [var1] was denied due to lack of proper permissions.', $this->_id) . '</div>';
            $error = new PHPWS_Error('phpwsbb', 'PHPWSBB_Manager::_accessDenied()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        if (isset($_REQUEST['Message_label'])) {
            if (preg_match("/\bhref/i",$_REQUEST['Message_label'])) {
	            $message = '<div class="errortext">' . $_SESSION['translate']->it('Anchor (href) tags are not allowed in the message subject.') . '</div>';
	            $_SESSION['PHPWSBB_Manager']->notice = new PHPWS_Message($message, 'CNT_phpwsbb');
	            $_REQUEST['PHPWSBB_MESSAGE_OP'] = 'edit';
	            $this->action();
	            return;
            } else
	            $error = $this->setLabel(PHPWS_Text::parseInput($_REQUEST['Message_label']));
        }

        if (isset($_REQUEST['Message_body']))
	        $this->_body = PHPWS_Text::parseInput($_REQUEST['Message_body']);

        if (isset($_REQUEST['Message_preview'])) {
	        $_REQUEST['PHPWSBB_MESSAGE_OP'] = 'edit';
	        $this->action();
	        return;
        }

	    if (PHPWS_Error::isError($error)) {
	        $message = '<div class="errortext">' . $_SESSION['translate']->it('You must have a subject for your message.') . '</div>';
	        $_SESSION['PHPWSBB_Manager']->notice = new PHPWS_Message($message, 'CNT_phpwsbb');
	        $_REQUEST['PHPWSBB_MESSAGE_OP'] = 'edit';
	        $this->action();
	        return;
	    }

        if (isset($_REQUEST['Message_guestname'])) {
            $this->_guestname = PHPWS_Text::parseInput($_REQUEST['Message_guestname']);
            $result = $GLOBALS['core']->sqlSelect('mod_users', NULL, NULL, 'username');
            if ($result)
                foreach ($result as $row) {
                    if (strtolower($this->_guestname) == strtolower($row['username'])) {
                        $message = $_SESSION['translate']->it('We are sorry, but "[var1]" is the username of a registered user.', $this->_guestname);
                        $GLOBALS['CNT_phpwsbb']['content'] .= '<div class="errortext">' . $message . '</div>';
            
                        $_REQUEST['PHPWSBB_MESSAGE_OP'] = 'edit';
                        $this->action();
                        return;
                    }
                }
        }
            
        if (isset($_REQUEST['Message_guestemail']))
            $this->_guestemail = PHPWS_Text::parseInput($_REQUEST['Message_guestemail']);

        if (!empty($_REQUEST['Message_tid']))
            $this->_tid = $_REQUEST['Message_tid'];
        elseif (empty($this->_tid)) {
            // Need to create thread
            $thread = new PHPWSBB_Thread;
            $thread->setLabel($this->getLabel());

            // Setting thread owner if anonymous
            if (!empty($this->_guestname))
                $thread->_owner = $this->_guestname;

            $thread->_fid = $_REQUEST['Message_fid'];

            $thread->commit();
            $this->_tid = $thread->getId();
        }

        $new = FALSE;
        if(empty($this->_id)) {
            $new = TRUE;
            /* Set the owner_id */
            if($_SESSION['OBJ_user']->user_id)
                $this->_owner_id = $_SESSION['OBJ_user']->user_id;
            else
                $this->_owner_id = '';
        }

        $error = $this->commit();
        if (PHPWS_Error::isError($error)) {
            $message = $_SESSION['translate']->it('The message could not be posted.');
            $error = new PHPWS_Error('phpwsbb', $message, 'continue', 0);
            $error->message('CNT_phpwsbb');

            $_REQUEST['PHPWSBB_MESSAGE_OP'] = 'edit';
            $this->action();
            return;
        } else {
            $GLOBALS['CNT_phpwsbb']['content'] .= $_SESSION['translate']->it('Your message was successfully posted.') . '<br />&#013;';

            // Update user statistics
            if($new)
                $GLOBALS['core']->query('UPDATE mod_phpwsbb_user_info SET posts=posts+1 WHERE user_id='.$_SESSION['OBJ_user']->user_id, true);

            // Update thread object with new statistics
            $thread = new PHPWSBB_Thread($this->_tid);
            $thread->updateThread($new);

            // 2004/03/17 DTS:  Should this just be done in updateThread()?
            if ($_SESSION['PHPWSBB_Manager']->_monitor_posts) {
                include_once('Mail.php');
                $admin_email = $_SESSION['PHPWSBB_Manager']->_admin_email;
                if (empty($admin_email)) {
                    $user_contact = NULL;
                    extract(PHPWS_User::getSettings());
                    $admin_email = $user_contact;
                }

                $from = '"' . $_SESSION['translate']->it('Bulletin Board Admin') . '" <'.$admin_email.'>';
                $subject = $_SESSION['translate']->it('Bulletin Board Post: "[var1]"', PHPWS_Text::parseOutput($this->getLabel(0)));
                $message = $_SESSION['PHPWSBB_Manager']->_email_text;
                // Replace [name] and [url] in $message
                $message = str_replace('[name]', '"' . $this->getLabel() . '"', $message);
                // Got rid of the brackets so that the link isn't broken in email messages
                $message = str_replace('[url]', 'http://' . PHPWS_HOME_HTTP . 'index.php?module=phpwsbb&PHPWSBB_MAN_OP=view&PHPWS_MAN_ITEMS=' . $thread->getId(), $message);
        
                $mail_object =& Mail::factory('mail');
                $headers['From'] = $from;
                // $headers['To'] = $admin_email;
                $headers['Subject'] = $subject;
                $mail_object->send($admin_email, $headers, $message);

            }

            $GLOBALS['CNT_phpwsbb']['content'] .= '<p><a href="./index.php?module=phpwsbb&amp;PHPWSBB_MAN_OP=view&amp;PHPWS_MAN_ITEMS[]=' . $thread->getId() . '">' . $_SESSION['translate']->it('Back to the topic.') . '</a>&nbsp;|&nbsp;';
            $GLOBALS['CNT_phpwsbb']['content'] .= '<a href="./index.php?module=phpwsbb&amp;PHPWSBB_MAN_OP=viewforum&amp;PHPWS_MAN_ITEMS[]=' . $thread->_fid . '">' . $_SESSION['translate']->it('Back to the forum.') . '</a></p>';
            $GLOBALS['CNT_phpwsbb']['title'] = $_SESSION['translate']->it('Message Posted');
        }

    }// END FUNC _save


    /**
     * Spins current message off into new threads of it's own.
     */
    function _fork() {
        if (empty($this->_id) && !($_SESSION['OBJ_user']->allow_access('phpwsbb', 'fork_messages') && $this->isModerator)) {
            $message = $_SESSION['translate']->it('Access to fork message [var1] was denied due to lack of proper permissions.', $this->_id);
            $error = new PHPWS_Error('phpwsbb', 'PHPWSBB_Manager::_accessDenied()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        // Need to create thread
        $oldtid = $this->_tid;
        $oldthread = new PHPWSBB_Thread($this->_tid);

        if (!$oldthread->_replies > 0) {
            $message = $_SESSION['translate']->it('Can\'t fork message [var1] as it contains no replies.', $this->_tid);
            $error = new PHPWS_Error('phpwsbb', 'PHPWSBB_Manager::_accessDenied()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        $thread = new PHPWSBB_Thread;
        $thread->setLabel($this->getLabel());
        $thread->_fid = $oldthread->_fid;
        $thread->commit();
        $this->_tid = $thread->getId();

        $error = $this->commit();
        if (PHPWS_Error::isError($error)) {
            $message = $_SESSION['translate']->it('The message could not be forked.');
            $error = new PHPWS_Error('phpwsbb', $message, 'continue', 0);
            $error->message('CNT_phpwsbb');

            $_REQUEST['PHPWSBB_MESSAGE_OP'] = 'edit';
            $this->action();
            return;
        } else {
            $GLOBALS['CNT_phpwsbb']['content'] .= $_SESSION['translate']->it('Your message was successfully forked.') . '<br />';

            // Update old and new thread objects with new statistics
            $thread = new PHPWSBB_Thread($oldtid);
            $thread->updateThread();
            $thread = new PHPWSBB_Thread($this->_tid);
            $thread->updateThread();

            $_REQUEST['PHPWSBB_THREAD_OP'] = 'view';
            $thread->action();
        }
    }// END FUNC _fork


    /**
     * Presents confirmation page and eventually deletes message,
     * deleting thread if this is only message.
     */
    function _delete() {
        if (!$_SESSION['OBJ_user']->allow_access('phpwsbb', 'delete_messages') && !$this->isModerator && ($_SESSION['OBJ_user']->username != $this->getOwner())) {
            $message = $_SESSION['translate']->it('Access to delete this message was denied due to lack of proper permissions.');
            $error = new PHPWS_Error('phpwsbb', 'PHPWSBB_Message::_delete()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        if (isset($_REQUEST['option_yes'])) {
            $this->kill();

            // Update thread object with new statistics
            $thread = new PHPWSBB_Thread($this->_tid);
            $thread->updateThread();

            $message = $_SESSION['translate']->it('The message [var1] by [var2] was successfully deleted from the database.', '<b><i>' . $this->getLabel() . '</i></b>', $this->getOwner());
            $_SESSION['PHPWSBB_Manager']->notice = new PHPWS_Message($message, 'CNT_phpwsbb');
            //$_REQUEST['PHPWS_MAN_ITEMS'][] = $this->_tid;
            $_REQUEST['PHPWSBB_MAN_OP'] = 'list';
            $_SESSION['PHPWSBB_Manager']->action();

        } elseif (isset($_REQUEST['option_no'])) {
            $message = $_SESSION['translate']->it('No message was deleted from the database.');
            $_SESSION['PHPWSBB_Manager']->notice = new PHPWS_Message($message, 'CNT_phpwsbb');
            //$_REQUEST['PHPWS_MAN_ITEMS'][0] = $this->_tid;
            $_REQUEST['PHPWSBB_MAN_OP'] = 'list';
            $_SESSION['PHPWSBB_Manager']->action();

        } else {
            $title = $_SESSION['translate']->it('Delete Message Confirmation');

            $form = new EZform('PHPWSBB_Message_delete');
            $form->add('module', 'hidden', 'phpwsbb');
            $form->add('PHPWSBB_MESSAGE_OP', 'hidden', 'delete');

            $form->add('option_yes', 'submit', $_SESSION['translate']->it('Yes'));
            $form->add('option_no', 'submit', $_SESSION['translate']->it('No'));

            $tags = array();
            $tags = $form->getTemplate();
            $tags['MESSAGE'] = $_SESSION['translate']->it('Are you sure you want to delete this message?');
            
            $content = PHPWS_Template::processTemplate($tags, 'phpwsbb', 'delete.tpl');
            $_SESSION['OBJ_layout']->popbox($title, $content, NULL, 'CNT_phpwsbb');
        }
    }// END FUNC _delete


    /**
     * Interface and action to report naughty messages to admin.
     */
    function _report() {

        $content = null;   

        $poster = $this->getOwner();
        if (is_null($poster))
            $poster = $this->_guestname . ' (' . $_SESSION['translate']->it('GUEST') . ')';

        if (isset($_REQUEST['Message_id'])) {
            $admin_email = $_SESSION['PHPWSBB_Manager']->_admin_email;
            if (empty($admin_email)) {
                $user_contact = NULL;
                extract(PHPWS_User::getSettings());
                $admin_email = $user_contact;
            }
            $from = '"' . $_SESSION['translate']->it('Bulletin Board Admin') . '" <'.$admin_email.'>';
            $subject = $_SESSION['translate']->it('Reported BB Post: "[var1]"', $this->getLabel());
            $message = str_replace('[url]', 'http://' . PHPWS_HOME_HTTP . 'index.php?module=phpwsbb&PHPWSBB_MAN_OP=view&PHPWS_MAN_ITEMS=' . $this->getId(), $message);
            $message = $_SESSION['translate']->it('A bulletin board user reported a bulletin board post for review.  Please visit [var1] to view the post and take action.','http://' . PHPWS_HOME_HTTP . 'index.php?module=phpwsbb&PHPWSBB_MAN_OP=view&PHPWS_MAN_ITEMS[]=' . $this->_tid . '#' . $_REQUEST['Message_id']);
            $message .= "\n\nReason:\n\n" . $_REQUEST['Message_reason'];
            $message .= "\n\nOriginal Message:\n\n";
            $message .= 'Poster: ' . $poster . "\n";
            $message .= 'Subject: ' . $this->getLabel() . "\n";
            $message .= "Body:\n\n" . $this->_body . "\n";
            $mail_object =& Mail::factory('mail');
            $headers['From'] = $from;
            $headers['Subject'] = $subject;
            // $headers['To'] = $email;
            $mail_object->send($admin_email, $headers, $message);

            $message = $_SESSION['translate']->it('The message [var1] by [var2] was reported to the site administrator.', '<b><i>' . $this->getLabel() . '</i></b>', $this->getOwner());
            $_SESSION['PHPWSBB_Manager']->notice = new PHPWS_Message($message, 'CNT_phpwsbb');
            $_REQUEST['PHPWSBB_MAN_OP'] = 'list';
            $_SESSION['PHPWSBB_Manager']->action();
        } else {

            /* Variable to set tab order */
            $tabs = 1;

            /* Create form */
            $form = new EZform('PHPWSBB_Message_report');
    
            /* Report Reason */
            $form->add('Message_reason', 'textarea');
            $form->setRows('Message_reason',10);
            $form->setCols('Message_reason',70);
            $form->setTab('Message_reason', $tabs);
            $tabs++;
    
            /* Save Button */
            $form->add('Message_save', 'submit', $_SESSION['translate']->it('Send Report'));
            $form->setTab('Message_save', $tabs);
            $tabs++;
    
            /* Module Information */
            $form->add('module', 'hidden', 'phpwsbb');
            $form->add('PHPWSBB_MESSAGE_OP', 'hidden', 'report');
            $form->add('Message_id', 'hidden', $this->getId());
    
            $tags = array();
            $tags = $form->getTemplate();
    
            $tags['REASON_TEXT'] = $_SESSION['translate']->it('Reason');
            $tags['POSTER_TEXT'] = $_SESSION['translate']->it('Poster');
            $tags['BODY_TEXT'] = $_SESSION['translate']->it('Message');
            $tags['POSTER'] = $poster;
            $tags['BODY'] = $this->_body;
    
            $content = PHPWS_Template::processTemplate($tags, 'phpwsbb', 'report.tpl');
        }

        return $content;

    }// END FUNC _report


    /**
     * Reads action off of $_REQUEST var and acts accordingly
     */
    function action() {
        if (PHPWS_Message::isMessage($_SESSION['PHPWSBB_Manager']->notice)) {
	      $_SESSION['PHPWSBB_Manager']->notice->display();
	      $_SESSION['PHPWSBB_Manager']->notice = null;
	    }

        $forum = NULL;
        $thread = NULL;
        if(isset($this->_tid) && !empty($this->_tid)) {
                $thread = new PHPWSBB_Thread($this->_tid);
                $forum = new PHPWSBB_Forum($thread->_fid);
        } else {
            if (isset($_REQUEST['fid']))
                $forum = new PHPWSBB_Forum($_REQUEST['fid']);
            elseif (isset($_REQUEST['Message_fid']))
                $forum = new PHPWSBB_Forum($_REQUEST['Message_fid']);
        }

        switch($_REQUEST['PHPWSBB_MESSAGE_OP']) {
            case 'edit':
                if (isset($this->_id))
                    $title = $_SESSION['translate']->it('Edit Message');
                else
                    if (isset($this->_tid) && !empty($this->_tid))
                        $title = $_SESSION['translate']->it('Reply to Message');
                    else
                        $title = $_SESSION['translate']->it('New Message');

                $content = $_SESSION['PHPWSBB_Manager']->_menu($forum, $thread);
                $content .= $this->_edit();
                break;
            case 'save':
                $this->_save();
                break;
            case 'view':
                $content = $_SESSION['PHPWSBB_Manager']->_menu($forum, $thread);
                $content .= $this->_view();
                break;
            case 'delete':
                $this->_delete();
                break;
            case 'fork':
                $this->_fork();
                break;
            case 'report':
                $title = $_SESSION['translate']->it('Report Message');
                $content = $_SESSION['PHPWSBB_Manager']->_menu($forum, $thread);
                $content .= $this->_report();
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

    
    /**
     * Returns message label with any applicable prefixes
     */
    function getLabel($prefix = TRUE) {
        $lab = parent::getLabel();
        if ($prefix) {
            if ($this->isHidden())
                $lab = '<img src="./images/phpwsbb/hideshow.png" border="0" height="16" width="16" alt="' . $_SESSION['translate']->it('Hidden') . '" title="' . $_SESSION['translate']->it('Hidden') . '" />&nbsp;' . $lab;
        }
        return $lab;
    }// END FUNC getLabel


}// END CLASS PHPWSBB_Message
?>

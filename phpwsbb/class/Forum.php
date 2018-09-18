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
 * @version     $Id: Forum.php,v 1.46 2006/03/06 00:59:37 singletrack Exp $
 */

/** 
 * @since 0.6.0
 */
require_once(PHPWS_SOURCE_DIR . 'core/Item.php');
require_once(PHPWS_SOURCE_DIR . 'core/EZform.php');
require_once(PHPWS_SOURCE_DIR . 'core/Error.php');
require_once(PHPWS_SOURCE_DIR . 'core/Text.php');
require_once(PHPWS_SOURCE_DIR . 'mod/phpwsbb/class/Thread.php');
require_once(PHPWS_SOURCE_DIR . 'mod/users/class/Users.php');

/* PEAR mail class */
require_once('Mail.php');

class PHPWSBB_Forum extends PHPWS_Item {

    /**
     * Description of this forum
     *
     * @var      string
     * @access   private
     */
    var $_description = NULL;

    /**
     * Number of threads to this forum
     *
     * @var      integer
     * @access   private
     */
    var $_threads = 0;

    /**
     * Sort order of this forum
     *
     * @var      integer
     * @access   private
     */
    var $_sortorder = 0;

    /**
     * unix timestamp of last post
     *
     * @var      integer
     * @access   private
     */
    var $_lastpost = 0;

    /**
    * # of posts in this forum
    *
    * @var      string
    * @access   private
    */
    var $_posts;

    /**
    * Description of the last thread posted to
    *
    * @var      string
    * @access   private
    */
    var $_lastpost_topic_label;

    /**
    * ID of the last thread posted to
    *
    * @var      integer
    * @access   private
    */
    var $_lastpost_topic_id;

    /**
    * ID of the last post
    *
    * @var      integer
    * @access   private
    */
    var $_lastpost_post_id;

    /**
    * IDs of this forum's moderators (separated by commas)
    *
    * @var      string
    * @access   private
    */
    var $_moderators;

    /**
    * Restrict access to these groups
    *
    * @var      string
    * @access   private
    */
	var $_groups;
	
    /**
     * Forum Moderator flag
     *
     * @var      bool
     * @access   public
     */
    var $isModerator = FALSE;

    function PHPWSBB_Forum($FORUM_ID = NULL) {
        /* These variable are excluded when calling commit() */
        $exclude = array('isModerator');
        $this->addExclude($exclude);

        /* Set database table */
        $this->setTable('mod_phpwsbb_forums');

        /* Populate this object if ID exists */
        if(is_numeric($FORUM_ID)) {
            if(isset($FORUM_ID)) {
                $this->setId($FORUM_ID);
                $this->init();
            }
        } elseif (is_array($FORUM_ID)) {
            $this->init($FORUM_ID);
        }

        /* To perform a moderation action, user must either be a moderator of this forum or be able to edit forums (supermoderator) */
        $this->isModerator = $_SESSION['OBJ_user']->allow_access('phpwsbb', 'edit_forums')
            || ($_SESSION['OBJ_user']->user_id && !empty($this->_moderators) && in_array($_SESSION['OBJ_user']->user_id, explode(',', $this->_moderators)));
    }// END FUNC PHPWSBB_Forum


    function _view($ro = FALSE) {
        if($this->isHidden() && !$_SESSION['OBJ_user']->allow_access('phpwsbb', 'hide_forums')) {
            $content = '<div class="errortext">' . $_SESSION['translate']->it('There is nothing here to see.') . '</div>';
            return $content;
        }
		
		// Restricted Group Access
		if(!empty($this->_groups)) {
			$restricted = TRUE;
			foreach($this->_groups as $group) {
				if($_SESSION['OBJ_user']->userInGroup($group)) { $restricted = FALSE; break; }
			}
			if(($restricted) && !$_SESSION['OBJ_user']->allow_access('phpwsbb', 'edit_forums')) {
	            $content = '<div class="errortext">' . $_SESSION['translate']->it('Sorry, you do not have access to this forum.') . '</div>';
    	        return $content;
			}		
		}

        $tags = array();
        if($_SESSION['OBJ_fatcat'] && $_SESSION['PHPWSBB_Manager']->_show_categories) {
            $cat = $_SESSION['OBJ_fatcat']->fatcatLinks($this->getId());
            if(isset($cat) && !empty($cat)) {
                $tags['CATEGORY_TEXT'] = $_SESSION['translate']->it('Category');
                $tags['CATEGORY'] = $cat;
            }
    
            // Not sure if we want this
            $_SESSION['OBJ_fatcat']->whatsRelated($this->getId());
        }


        $tags['ID']         = $this->getId();
        $tags['OWNER']      = $this->getOwner();
        $tags['EDITOR']     = $this->getEditor();
        $tags['IP']         = $this->getIp();
        $tags['CREATED']    = $this->getCreated();
        $tags['UPDATED']    = $this->getUpdated();
        $tags['LABEL']      = PHPWS_Text::parseOutput($this->getLabel());
        $tags['THREADS']    = $this->_threads;
        $tags['DESCRIPTION']= PHPWS_Text::parseOutput($this->_description);

        if(!empty($this->_moderators)) {
            if($tmp = $GLOBALS['core']->getCol('SELECT username FROM mod_users WHERE user_id IN ('.$this->_moderators.')', TRUE))
                $tags['MODERATORS'] = implode(', ', $tmp);
            else
                $tags['MODERATORS'] = $_SESSION['translate']->it('None'); 
            $tags['MODERATORS_LABEL'] = $_SESSION['translate']->it('Forum Moderators');
        }

        if(!$ro) {
            $tags['NEW'] = PHPWSBB_Thread::createLink($_SESSION['translate']->it('New Topic'),
             array('PHPWSBB_MAN_OP'=>'new','fid'=>$this->getId()),'new_message.png',16,16);

            if($_SESSION['OBJ_user']->allow_access('phpwsbb', 'edit_forums'))
                $tags['EDIT'] = PHPWSBB_Thread::createLink($_SESSION['translate']->it('Edit Forum'),
                  array('PHPWSBB_MAN_OP'=>'editforum','PHPWS_MAN_ITEMS[]'=>$this->getId()),
                  'edit.png', 16,16);
                
            if($_SESSION['OBJ_user']->allow_access('phpwsbb', 'delete_forums'))
                $tags['DELETE'] = PHPWSBB_Thread::createLink($_SESSION['translate']->it('Delete Forum'),
                  array('PHPWSBB_MAN_OP'=>'deleteforum','PHPWS_MAN_ITEMS[]'=>$this->getId()),
                  'delete.png',16,16);

            if($_SESSION['OBJ_user']->allow_access('phpwsbb', 'hide_threads') && $this->isModerator) {
                if ($this->isHidden()) {
                    $showforum = $_SESSION['translate']->it('Show Forum');
                    $tags['HIDE'] = PHPWSBB_Thread::createLink($showforum, array('PHPWSBB_MAN_OP'=>'showforum',
                      'PHPWS_MAN_ITEMS[]'=>$this->getId()), 'hideshow.png', 16, 16);
                } else {
                    $hideforum = $_SESSION['translate']->it('Hide Forum');
                    $tags['HIDE'] = PHPWSBB_Thread::createLink($hideforum, array('PHPWSBB_MAN_OP'=>'hideforum',
                      'PHPWS_MAN_ITEMS[]'=>$this->getId()), 'hideshow.png', 16, 16);
                }
            }
        }

        return PHPWS_Template::processTemplate($tags, 'phpwsbb', 'view_forum_top.tpl')
            . $_SESSION['PHPWSBB_Manager']->_listThreads($this->getId())
            . PHPWS_Template::processTemplate($tags, 'phpwsbb', 'view_forum_bottom.tpl');

    }// END FUNC _view


    function _edit() {
        if(!$_SESSION['OBJ_user']->allow_access('phpwsbb', 'edit_forums')) {
            $message = $_SESSION['translate']->it('Access to edit forum [var1] was denied due to lack of proper permissions.', $this->_id);
            $error = new PHPWS_Error('phpwsbb', 'PHPWSBB_Forum::_edit()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        /* Variable to set tab order */
        $tabs = 1;

        /* Create form */
        $form = new EZform('PHPWSBB_Forum_edit');

        /* Forum Label */
        $form->add('Forum_label', 'text', $this->getLabel(FALSE));
        $form->setSize('Forum_label', 33);
        $form->setTab('Forum_label', $tabs);
        $tabs++;

        /* Forum Description */
        $form->add('Forum_description', 'textarea', $this->_description);
        $form->setRows('Forum_description',10);
        $form->setWidth('Forum_description',60);
        $form->setTab('Forum_description', $tabs);
        $tabs++;

        /* Forum Sort Order */
        $form->add('Forum_sortorder', 'text', $this->_sortorder);
        $form->setSize('Forum_sortorder', 5);
        $form->setTab('Forum_sortorder', $tabs);
        $tabs++;

        /* Save Button */
        $form->add('Forum_save', 'submit', $_SESSION['translate']->it('Save'));
        $form->setTab('Forum_save', $tabs);
        $tabs++;

        /* Module Information */
        $form->add('module', 'hidden', 'phpwsbb');
        $form->add('PHPWSBB_ITEMS[]', 'hidden', $this->getId());
        $form->add('PHPWSBB_FORUM_OP', 'hidden', 'save');

        $tags = array();
        $tags = $form->getTemplate();

        /* FatCat Category */
        if($_SESSION['OBJ_fatcat']) {
            $tags['CATEGORIES_TEXT'] = $_SESSION['translate']->it('Category');
            $tags['CATEGORIES'] = $_SESSION['OBJ_fatcat']->showSelect($this->getId(), 'multiple', NULL, 'phpwsbb', NULL, FALSE);
        }

        $tags['LABEL_TEXT'] = $_SESSION['translate']->it('Name');
        $tags['DESCRIPTION_TEXT'] = $_SESSION['translate']->it('Description');
        $tags['SORTORDER_TEXT'] = $_SESSION['translate']->it('Sort Order');
        $tags['SORTORDER_HELP'] = CLS_help::show_link('phpwsbb', 'sortorder');

        /* Moderator List */
        // Display list of automatic moderators (those that can edit forums)
	      $super_mods = $GLOBALS['core']->getCol('SELECT u.username FROM ' . $GLOBALS['core']->tbl_prefix . 'mod_users AS u LEFT JOIN ' . $GLOBALS['core']->tbl_prefix . 'mod_user_uservar AS v ON ( v.user_id = u.user_id) WHERE u.deity OR (u.admin_switch AND v.varName="MOD_phpwsbb" AND v.varValue LIKE "%edit_forums%") GROUP BY u.username ORDER BY u.username ASC'); 
	      if($super_mods) {
	          $tags['S_MODERATORS_LABEL'] = $_SESSION['translate']->it('These users have moderation capabilities even if they\'re not selected');
	          $tags['S_MODERATORS'] = implode(', ', $super_mods);
        }
        // Create list of eligible moderators (those that can edit messages)
	      $eligible_mods = $GLOBALS['core']->query('SELECT u.username,u.user_id FROM ' . $GLOBALS['core']->tbl_prefix . 'mod_users AS u LEFT JOIN ' . $GLOBALS['core']->tbl_prefix . 'mod_user_uservar AS v ON ( v.user_id = u.user_id) WHERE u.deity OR (u.admin_switch AND v.varName="MOD_phpwsbb" AND (v.varValue LIKE "%edit_forums%" OR v.varValue LIKE "%edit_messages%")) GROUP BY u.username ORDER BY u.username ASC');
	      if($eligible_mods) {
	          $tags['MODERATORS_LABEL'] = $_SESSION['translate']->it('Choose moderators for this forum');
	          $tags['MODERATORS'] = '';
            // Create a checkbox for every user on the list 
            $mod_arr = explode(',', $this->_moderators);
        		while ($row = $eligible_mods->fetchrow(DB_FETCHMODE_ASSOC)) {
	              if(in_array($row['user_id'], $mod_arr))
  	                $checked = 'checked="checked" ';
	              else
  	                $checked = '';
	              $tags['MODERATORS'] .= '<label><input name="phpwsBB_moderator[]" value="'.$row['user_id'].'" '.$checked.'type="checkbox"> '.$row['username'].'</label><br />';
        		}
        }

		// Limit user access to this group
        $tags['GROUPS_LABEL'] = $_SESSION['translate']->it('Limit access to these groups');
        $tags['GROUPS'] = $this->showGroups();

        if($_SESSION['OBJ_user']->js_on)
            $tags['FORUM_DESCRIPTION'] = PHPWS_WizardBag::js_insert('wysiwyg', 'PHPWSBB_Forum_edit', 'Forum_description') . $tags['FORUM_DESCRIPTION'];

        $content = PHPWS_Template::processTemplate($tags, 'phpwsbb', 'editforum.tpl');

        return $content;

    }


    function _save() {
        if (isset($_POST['PHPWSBB_ITEMS'])
          && sizeof($_POST['PHPWSBB_ITEMS']) > 0 && is_array($_POST['PHPWSBB_ITEMS']))
            $this->PHPWSBB_Forum($_POST['PHPWSBB_ITEMS'][0]);
        if(!empty($this->_id) && !($_SESSION['OBJ_user']->allow_access('phpwsbb', 'edit_messages') && $this->isModerator) && ($_SESSION['OBJ_user']->username != $this->getOwner())) {
            $message = $_SESSION['translate']->it('Access to save message [var1] was denied due to lack of proper permissions.', $this->_id);
            $error = new PHPWS_Error('phpwsbb', 'PHPWSBB_Manager::_accessDenied()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }
        if(isset($_REQUEST['Forum_label']))
            $error = $this->setLabel(PHPWS_Text::parseInput($_REQUEST['Forum_label']));

        if(isset($_REQUEST['Forum_description']))
            $this->_description = PHPWS_Text::parseInput($_REQUEST['Forum_description']);

        if(isset($_REQUEST['Forum_sortorder']))
            $this->_sortorder = PHPWS_Text::parseInput($_REQUEST['Forum_sortorder']);

        if(PHPWS_Error::isError($error)) {
            $message = $_SESSION['translate']->it('You must have a name for your forum.');
            $_SESSION['PHPWSBB_Manager']->notice = new PHPWS_Message($message, 'CNT_phpwsbb');
            $_REQUEST['PHPWSBB_FORUM_OP'] = 'edit';
            $this->action();
            return;
        }

        if(isset($_POST['phpwsBB_moderator']) && is_array($_POST['phpwsBB_moderator'])) 
            $this->_moderators = implode(',', $_POST['phpwsBB_moderator']);
        else
            $this->_moderators = '';
        
        // Set group access    
		if(isset($_POST['phpwsBB_groups']) && is_array($_POST['phpwsBB_groups'])) {
			$this->_groups = $_POST['phpwsBB_groups'];
		} else {
			$this->_groups = NULL;
		}
				
        /* Hacks to get around some of Eloi's DDL */
        if (empty($this->_posts))
            $this->_posts = 0;

        if (empty($this->_lastpost_topic_label))
            $this->_lastpost_topic_label = '';

        if (empty($this->_lastpost_topic_id))
            $this->_lastpost_topic_id = 0;

        if (empty($this->_lastpost_post_id))
            $this->_lastpost_post_id = 0;

        $error = $this->commit();
        if(PHPWS_Error::isError($error)) {
            $message = $_SESSION['translate']->it('The forum could not be saved to the database.');
            $error = new PHPWS_Error('phpwsbb', $message, 'continue', 0);
            $error->message('CNT_phpwsbb');

            $_REQUEST['PHPWSBB_FORUM_OP'] = 'edit';
            $this->action();
            return;
        } else {
            // FatCat
            if($_SESSION['OBJ_fatcat'])
                /* Got rid of the brackets so that the link isn't broken in email messages */
                $_SESSION['OBJ_fatcat']->saveSelect($this->getLabel(), 'index.php?module=phpwsbb&amp;PHPWSBB_MAN_OP=viewforum&amp;PHPWS_MAN_ITEMS=' . $this->getId(), $this->getId());

            $content = '<p>' . $_SESSION['translate']->it('Your forum was successfully saved.') . '</p>';
            $content .= '<a href="./index.php?module=phpwsbb&amp;PHPWSBB_MAN_OP=list">' . $_SESSION['translate']->it('Back to Forums') . '</a>';
            return $content;
        }

    }// END FUNC _save


    function updateForum($suppress_sets = FALSE) {
        if(!empty($this->_id)) {
            /* Develop total replies and extended thread information */
            $sql = 'SELECT COUNT(id), SUM(replies) FROM ' . $GLOBALS['core']->tbl_prefix . 'mod_phpwsbb_threads WHERE fid=' . $this->getId() . ' ORDER BY lastpost DESC';
            $t = $GLOBALS['core']->getRow($sql);
            $this->_threads = $t['COUNT(id)'];

            /* Replies doesn't include the original post in the thread, add # of threads */
            $this->_posts = $t['SUM(replies)'] + $this->_threads;

            $sql = 'SELECT id FROM ' . $GLOBALS['core']->tbl_prefix . 'mod_phpwsbb_threads WHERE fid=' . $this->getId() . ' ORDER BY lastpost DESC';
            $t = $GLOBALS['core']->getOne($sql);
            $thread = new PHPWSBB_Thread($t);
            $this->_lastpost = $thread->_lastpost;
            $this->_lastpost_topic_label = $thread->getLabel(false);
            $this->_lastpost_topic_id = $thread->getId();
            $this->_lastpost_post_id = $thread->_lastpost_post_id;
        } else {
            $this->_threads = 0;
        }
        $this->commit(null, $suppress_sets);

    }// END FUNC updateForum


    function _delete() {
        if(!$_SESSION['OBJ_user']->allow_access('phpwsbb', 'delete_forums')) {
            $message = $_SESSION['translate']->it('Access to delete this forum was denied due to lack of proper permissions.');
            $error = new PHPWS_Error('phpwsbb', 'PHPWSBB_Forum::_delete()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        if(isset($_REQUEST['option_yes'])) {
            $this->kill();

            // Remove FatCat entry
            if($_SESSION['OBJ_fatcat'])
                $_SESSION['OBJ_fatcat']->purge($this->getId(), 'phpwsbb');

            $sql = 'SELECT id FROM ' . $GLOBALS['core']->tbl_prefix . 'mod_phpwsbb_threads WHERE fid=' . $this->getId() . ' ORDER BY created;';
            $results = $GLOBALS['core']->getCol($sql);
            foreach($results as $tid) {
                $GLOBALS['core']->sqlDelete('mod_phpwsbb_messages', 'tid', $tid);
                $GLOBALS['core']->sqlDelete('mod_phpwsbb_threads', 'id', $tid);
            }

            $message = $_SESSION['translate']->it('The forum [var1] was successfully deleted from the database.', '<b><i>' . $this->getLabel() . '</i></b>');
            $_SESSION['PHPWSBB_Manager']->notice = new PHPWS_Message($message, 'CNT_phpwsbb');
            $_REQUEST['PHPWSBB_MAN_OP'] = 'list';
            $_SESSION['PHPWSBB_Manager']->action();

        } elseif(isset($_REQUEST['option_no'])) {
            $message = $_SESSION['translate']->it('No forum was deleted from the database.');
            $_SESSION['PHPWSBB_Manager']->notice = new PHPWS_Message($message, 'CNT_phpwsbb');
            $_REQUEST['PHPWSBB_MAN_OP'] = 'list';
            $_SESSION['PHPWSBB_Manager']->action();

        } else {
            $form = new EZform('PHPWSBB_Forum_delete');
            $form->add('module', 'hidden', 'phpwsbb');
            $form->add('PHPWSBB_FORUM_OP', 'hidden', 'delete');

            $form->add('option_yes', 'submit', $_SESSION['translate']->it('Yes'));
            $form->add('option_no', 'submit', $_SESSION['translate']->it('No'));

            $tags = array();
            $tags = $form->getTemplate();
            $tags['MESSAGE'] = $_SESSION['translate']->it('Are you sure you want to delete this forum and all its threads and messages?');
            
            $GLOBALS['CNT_phpwsbb']['title'] = $_SESSION['translate']->it('Delete Forum Confirmation');
            $GLOBALS['CNT_phpwsbb']['content'] = PHPWS_Template::processTemplate($tags, 'phpwsbb', 'delete.tpl');
        }

    }// END FUNC _delete


    function action() {
        switch($_REQUEST['PHPWSBB_FORUM_OP']) {
            case 'view':
                $title = $_SESSION['translate']->it('Forum Topics');
                $content = $_SESSION['PHPWSBB_Manager']->_menu($this);
                $content .= $this->_view();
                PHPWS_Layout::addPageTitle($this->getLabel(0));
                break;

            case 'delete':
                $this->_delete();
                break;

            case 'edit':
                $title = $_SESSION['translate']->it('Add/Edit Forum');
                $content = $_SESSION['PHPWSBB_Manager']->_menu($this);
                $content .= $this->_edit();
                break;

            case 'save':
                $title = $_SESSION['translate']->it('Saving Forum');
                $content = $_SESSION['PHPWSBB_Manager']->_menu($this);
                $content .= $this->_save();
                break;

            default:
                $title = 'PHPWSBB_Forum: NO OP';
                $content = 'This function is not yet implemented';
        }

        if(isset($content)) {
            $GLOBALS['CNT_phpwsbb']['title'] = $title;
            $GLOBALS['CNT_phpwsbb']['content'] .= $content;
        }
    }// END FUNC _action

    function getLabel($prefix = TRUE) {
        $lab = parent::getLabel();
        if($prefix)
            if($this->isHidden())
                $lab = '[' . $_SESSION['translate']->it('HIDDEN') . '] ' . $lab;

        return $lab;
    }

    function getLastPost() {
		// Restricted Group Access
		if(!empty($this->_groups)) {
			$restricted = TRUE;
			foreach($this->_groups as $group) {
				if($_SESSION['OBJ_user']->userInGroup($group)) { $restricted = FALSE; break; }
			}
			if(($restricted) && !$_SESSION['OBJ_user']->allow_access('phpwsbb', 'edit_forums')) {
	            $content = '<div class="errortext">' . $_SESSION['translate']->it('Restricted') . '</div>';
    	        return $content;
			}		
		}
        if($this->_lastpost) {
            $poster = $this->getEditor();
            if(empty($poster))
                $poster = $_SESSION['translate']->it('Guest');

            $retstring = NULL;
            $retstring .= $_SESSION['translate']->it('[var1]<br />in [var2]<br />by [var3]'
                , date(PHPWS_DATE_FORMAT . ' ' . PHPWS_TIME_FORMAT, $this->_lastpost)
                , '<a href="./index.php?module=phpwsbb&amp;PHPWSBB_MAN_OP=view&amp;PHPWS_MAN_ITEMS=' . $this->_lastpost_topic_id . '#' . $this->_lastpost_post_id . '">' . $this->_lastpost_topic_label . '</a>'
                , $poster);
            return $retstring;
        } else return NULL;
    }// END FUNC getLastPost
    
    // Show a select box with users that are/can be Moderators
    function showModerators() {
    }// END FUNC showModerators

    // Show a select box with user groups for restricted access
    function showGroups() {
		if (!($groups = PHPWS_User_Groups::loadAllGroups())){
		  $content = $_SESSION["translate"]->it("No groups found");
		} else {
			$groupArray = array();
			foreach ($groups as $info){
	            $groupArray[$info->group_id] = $info->group_name;
			}
			$content = PHPWS_Form::formMultipleSelect("phpwsBB_groups", $groupArray, $this->_groups, NULL, 1, 5);
		}
		return $content;
    }// END FUNC showGroups

	function getListID() {
		return $this->getId();
	}
	
	function getListOwner() {
		return $this->_owner;
	}
	
	function getListLabel() {
		return $this->getLabel();
	}
	
	function getListDescription() {
		return $this->_description;
	}
	
	function getListThreads() {
		return $this->_threads;
	}
	
	function getListPosts() {
		return $this->_posts;
	}
	
	function getListLastpost() {
		return $this->getLastPost();
	}

}// END CLASS PHPWSBB_Forum
?>
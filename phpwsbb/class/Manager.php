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
 * @version     $Id: Manager.php,v 1.76 2007/01/09 12:11:22 singletrack Exp $
 */

/** 
 * @since 0.6.0
 */
require_once(PHPWS_SOURCE_DIR . 'core/Manager.php');
require_once(PHPWS_SOURCE_DIR . 'core/EZform.php');
require_once(PHPWS_SOURCE_DIR . 'core/Text.php');
require_once(PHPWS_SOURCE_DIR . 'core/Message.php');
require_once(PHPWS_SOURCE_DIR . 'mod/phpwsbb/class/Forum.php');
require_once(PHPWS_SOURCE_DIR . 'mod/phpwsbb/class/Thread.php');
require_once(PHPWS_SOURCE_DIR . 'mod/phpwsbb/class/Message.php');
require_once(PHPWS_SOURCE_DIR . 'mod/help/class/CLS_help.php');

require_once(PHPWS_SOURCE_DIR . 'mod/fatcat/class/CategoryView.php');


class PHPWSBB_Manager extends PHPWS_Manager {

    var $notice = NULL;

    /**
     * Flag 1/0 to allow anonymous posts
     *
     * @var      bit
     * @access   private
     */
    var $_allow_anon_posts;

    /**
     * Flag 1/0 to allow anonymous viewing of posts
     *
     * @var      bit
     * @access   private
     */
    var $_allow_anon_view;

    /**
     * Bulletin board administrator email
     *
     * @var      string
     * @access   private
     */
    var $_admin_email;

    /**
     * Text to send to monitors
     *
     * @var      string
     * @access   private
     */
    var $_email_text;

    /**
     * Flag to have admin monitor posts
     *
     * @var      int
     * @access   private
     */
    var $_monitor_posts;

    /**
     * Flag to enable/disable user monitors site-wide
     *
     * @var      int
     * @access   private
     */
    var $_allow_user_monitors;

    /**
     * Flag to have show forums block
     *
     * @var      int
     * @access   private
     */
    var $_showforumsblock;

    /**
     * Title for forums block
     *
     * @var      string
     * @access   private
     */
    var $_forumsblocktitle;

    /**
     * Flag to have show latest threads block
     *
     * @var      int
     * @access   private
     */
    var $_showlatestthreadsblock;

    /**
     * Title for latest threads block
     *
     * @var      string
     * @access   private
     */
    var $_latestthreadsblocktitle;

    /**
     * Maximum number of latest threads to show in block
     *
     * @var      int
     * @access   private
     */
    var $_maxlatestthreads;

    /**
     * Flag to take phpwsbb offline
     *
     * @var      int
     * @access   private
     */
    var $_bboffline;

    /**
    * Allows user-selected Avatars to be displayed in messages
    *
    * @var      int
    * @access   private
    */
    var $_use_avatars;

    /**
    * Allows user-selected Avatar URLs to be displayed in messages
    *
    * @var      int
    * @access   private
    */
    var $_use_offsite_avatars;

    /**
    * Allows user-created Signatures to be displayed in messages
    *
    * @var      int
    * @access   private
    */
    var $_use_signatures;

    /**
    * Maximum allowed avatar height
    *
    * @var      int
    * @access   private
    */
    var $_max_avatar_height;

    /**
    * Maximum allowed avatar width
    *
    * @var      int
    * @access   private
    */
    var $_max_avatar_width;

    /**
    * Maximum allowed avatar filesize (in Kb)
    *
    * @var      int
    * @access   private
    */
    var $_max_avatar_size;

    /**
    * Show category features
    *
    * @var      bit
    * @access   private
    */
    var $_show_categories;
    
    /**
    * Stores the last page viewed for possible recall
    *
    * @var      array
    * @access   public
    */
    var $last_viewed;

    /**
    * Stores the user ranking system
    *
    * @var      array
    * @access   private
    */
    var $_user_ranks;


    /**
     * The forum object
     *
     * @var      PHPWSBB_Forum
     * @access   public
     */
    var $forum = NULL;


    /**
     * The thread object
     *
     * @var      PHPWSBB_Thread
     * @access   public
     */
    var $thread = NULL;

    /**
     * The message object
     *
     * @var      PHPWSBB_Message
     * @access   public
     */
    var $message = NULL;

    /**
     * Internal flag to determine when the user logs in as a member
     *
     * @var      boolean
     * @access   public
     */
    var $logged_in = NULL;

    /**
     * List of all modules currently installed.  For interoperability.
     *
     * @var      array
     * @access   public
     */
    var $installed_modules = NULL;
    
    function PHPWSBB_Manager() {
        $this->setModule('phpwsbb');
        $this->setRequest('PHPWSBB_MAN_OP');
        $this->init();

        // Settings
        $result = $GLOBALS['core']->sqlSelect('mod_phpwsbb_settings');
        $this->_allow_anon_posts = $result[0]['allow_anon_posts'];
        $this->_allow_anon_view = $result[0]['allow_anon_view'];
        $this->_admin_email = $result[0]['admin_email'];
        $this->_email_text = $result[0]['email_text'];
        $this->_monitor_posts = $result[0]['monitor_posts'];
        $this->_allow_user_monitors = $result[0]['allow_user_monitors'];
        $this->_showforumsblock = $result[0]['showforumsblock'];
        $this->_showlatestthreadsblock = $result[0]['showlatestthreadsblock'];
        $this->_latestthreadsblocktitle = $result[0]['latestthreadsblocktitle'];
        $this->_forumsblocktitle = $result[0]['forumsblocktitle'];
        $this->_maxlatestthreads = $result[0]['maxlatestthreads'];
        $this->_bboffline = $result[0]['bboffline'];
        $this->_use_avatars         = $result[0]['use_avatars'];
        $this->_use_offsite_avatars = $result[0]['use_offsite_avatars'];
        $this->_use_signatures      = $result[0]['use_signatures'];
        $this->_max_avatar_height   = $result[0]['max_avatar_height'];
        $this->_max_avatar_width    = $result[0]['max_avatar_width'];
        $this->_max_avatar_size     = $result[0]['max_avatar_size'];
        $this->_use_views           = $result[0]['use_views'];
        $this->_use_low_priority    = $result[0]['use_low_priority'];
        $this->_show_categories     = $result[0]['show_categories'];
        // Default page
        $this->last_viewed = array('PHPWSBB_MAN_OP'=>'list');
        if($_SESSION['OBJ_user']->username) 
        	$this->_update_userlog();
        else
        	$this->logged_in = false;
        $this->installed_modules = $GLOBALS['core']->listModules(1);
    }// END FUNC PHPWSBB_Manager


    function _menu($forum = NULL, $thread = NULL) {
        $tags = array();

        if($_SESSION['OBJ_user']->allow_access('phpwsbb', 'edit_settings'))
            $tags['SETTINGS'] = PHPWSBB_Thread::createLink($_SESSION['translate']->it('Admin'), array('PHPWSBB_MAN_OP'=>'getsettings'), 'gear.png', 16, 16);

        /* "Edit Profile" and "All New Posts" button */
        if($_SESSION['OBJ_user']->user_id) {
            $mysettings = $_SESSION['translate']->it('Settings');
            $tags['MYPROFILE'] = PHPWSBB_Thread::createLink($mysettings, array('PHPWSBB_MAN_OP'=>'usersettings'), 'mysettings.png', 16, 16);
            $newposts = $_SESSION['translate']->it('New Posts');
            $tags['NEW_POSTS'] = PHPWSBB_Thread::createLink($newposts, array('PHPWSBB_MAN_OP'=>'getnew'), 'new_messages.png', 16, 16);
        }

        /* "Categories" button */
        if($_SESSION['OBJ_fatcat'] && $this->_show_categories) {
            $categories = $_SESSION['translate']->it('Categories');
            $tags['CATEGORIES'] = PHPWSBB_Thread::createLink($categories, array('PHPWSBB_MAN_OP'=>'categories'), 'view_tree.png', 16, 16);
        }

        $tags['LIST'] = PHPWSBB_Thread::createLink($_SESSION['translate']->it('Forums'), array('PHPWSBB_MAN_OP'=>'list'), 'view_text.png', 16, 16);

        if (isset($forum))
            $tags['FORUM'] = PHPWS_Text::moduleLink(PHPWS_Text::parseOutput($forum->getLabel()), 'phpwsbb', array('PHPWSBB_MAN_OP'=>'viewforum', 'PHPWS_MAN_ITEMS'=>$forum->getId()));

        if (isset($thread)) {
            $tags['THREAD'] = PHPWS_Text::moduleLink(PHPWS_Text::parseOutput($thread->getLabel()), 'phpwsbb', array('PHPWSBB_MAN_OP'=>'view', 'PHPWS_MAN_ITEMS'=>$thread->getId()));
            if(!isset($forum)) {
                $forum = new PHPWSBB_Forum($thread->_fid);
                $tags['FORUM'] = PHPWS_Text::moduleLink(PHPWS_Text::parseOutput($forum->getLabel()), 'phpwsbb', array('PHPWSBB_MAN_OP'=>'viewforum', 'PHPWS_MAN_ITEMS'=>$forum->getId()));
            }
        }
    
        if(isset($_SESSION['PHPWSBB_Message'])) {
            $tags['PHPWSBB_MESSAGE'] = $_SESSION['PHPWSBB_Message'];
            $_SESSION['PHPWSBB_Message'] = NULL;
        }

        return PHPWS_Template::processTemplate($tags, 'phpwsbb', 'menu.tpl');

    }// END FUNC _menu


    function _list() {
        if(!$this->_allow_anon_view && !$_SESSION['OBJ_user']->username) {
          $_SESSION['OBJ_user']->jumpURL = './index.php?module=phpwsbb&PHPWSBB_MAN_OP=list';
          $_SESSION['OBJ_user']->error[] = $_SESSION['translate']->it('Anonymous viewing of this bulletin board has been disabled. You must log-in to view this bulletin board.'); 
          $_SESSION['OBJ_user']->routeLogin();
		  exit();
		}

        $GLOBALS['CNT_phpwsbb']['title'] = $_SESSION['translate']->it('Bulletin Board Forums');

        $GLOBALS['CNT_phpwsbb']['content'] .= $this->_menu();
        
        PHPWS_Layout::addPageTitle($_SESSION['translate']->it('Bulletin Board Forums'));

        /* "New forum" button */
        if($_SESSION['OBJ_user']->allow_access('phpwsbb', 'edit_forums')) {
            $newforum = $_SESSION['translate']->it('New Forum');
            $tags['NEW'] = PHPWSBB_Thread::createLink($newforum, array('PHPWSBB_MAN_OP'=>'editforum'), 'new_forum.png', 16, 16);
        } else $tags = array();
        
        $this->setClass('PHPWSBB_Forum');
        $this->setTable('mod_phpwsbb_forums');
        $this->setOrder('sortorder ASC, label ASC');
        if(!$_SESSION['OBJ_user']->allow_access('phpwsbb', 'hide_forums'))
            $this->setSort('hidden=0');

        $GLOBALS['CNT_phpwsbb']['content'] .= PHPWS_Template::processTemplate($tags, 'phpwsbb', 'forums_menu.tpl');
        $GLOBALS['CNT_phpwsbb']['content'] .= PHPWS_Text::profanityFilter($this->getList('forums', NULL, FALSE));
    }// END FUNC _list


    function _listThreads($fid) {
        if(!$this->_allow_anon_view && !$_SESSION['OBJ_user']->username) {
            $GLOBALS['CNT_phpwsbb']['title'] = $_SESSION['translate']->it('Anonymous Viewing Denied');
            $GLOBALS['CNT_phpwsbb']['content'] .= $_SESSION['translate']->it('Anonymous viewing of this bulletin board has been disabled. You must log-in to view this bulletin board.');
            return FALSE;
        }
        
        $this->setClass('PHPWSBB_Thread');
        $this->setTable('mod_phpwsbb_threads');
        $this->setOrder('sticky DESC, lastpost DESC');
        if(!$_SESSION['OBJ_user']->allow_access('phpwsbb', 'hide_threads'))
            $this->setSort('fid='.$fid.' AND hidden=0');
        else
            $this->setSort('fid='.$fid);

        return PHPWS_Text::profanityFilter($this->getList('threads', NULL, FALSE));
    }// END FUNC _listThreads


    function _listNewThreads($since) {
        if(!$_SESSION['OBJ_user']->username) {
          $_SESSION['OBJ_user']->jumpURL = './index.php?module=phpwsbb&PHPWSBB_MAN_OP=getnew';
          $_SESSION['OBJ_user']->error[] = $_SESSION['translate']->it('The "New Posts" feature is only available to registered users of this site.  You must log-in to view this page.'); 
          $_SESSION['OBJ_user']->routeLogin();
		  exit();
        }
        
        $this->setClass('PHPWSBB_Thread');
        $this->setTable('mod_phpwsbb_threads');
        $this->setOrder('sticky DESC, lastpost DESC');
        if(!$_SESSION['OBJ_user']->allow_access('phpwsbb', 'hide_threads'))
            $this->setSort('lastpost > '.$since.' AND hidden=0');
        else
            $this->setSort('lastpost > '.$since);

        return PHPWS_Text::profanityFilter($this->getList('threads', NULL, FALSE));
    }// END FUNC _listNewThreads

	// List threads started by a particular user
    function _listUserThreads($user = NULL) {
        if(!$_SESSION['OBJ_user']->username) {
          $_SESSION['OBJ_user']->jumpURL = './index.php?module=phpwsbb&PHPWSBB_MAN_OP=userthreads';
          $_SESSION['OBJ_user']->error[] = $_SESSION['translate']->it('The "User Threads" feature is only available to registered users of this site.  You must log-in to view this page.'); 
          $_SESSION['OBJ_user']->routeLogin();
		  exit();
        }
        
		if (!$user) $user = $_SESSION['OBJ_user']->username;
        $this->setClass('PHPWSBB_Thread');
        $this->setTable('mod_phpwsbb_threads');
        $this->setOrder('sticky DESC, lastpost DESC');
        if(!$_SESSION['OBJ_user']->allow_access('phpwsbb', 'hide_threads'))
            $this->setSort('owner = "'.$user.'" AND hidden=0');
        else
            $this->setSort('owner = "'.$user.'"');

        return PHPWS_Text::profanityFilter($this->getList('threads', NULL, FALSE));
    }// END FUNC _listUserThreads

	// List threads with zero replies
    function _listZero() {
        if(!$_SESSION['OBJ_user']->username) {
          $_SESSION['OBJ_user']->jumpURL = './index.php?module=phpwsbb&PHPWSBB_MAN_OP=userthreads';
          $_SESSION['OBJ_user']->error[] = $_SESSION['translate']->it('The "No replies" feature is only available to registered users of this site.  You must log-in to view this page.'); 
          $_SESSION['OBJ_user']->routeLogin();
		  exit();
        }
        
        $this->setClass('PHPWSBB_Thread');
        $this->setTable('mod_phpwsbb_threads');
        $this->setOrder('sticky DESC, lastpost DESC');
        if(!$_SESSION['OBJ_user']->allow_access('phpwsbb', 'hide_threads'))
            $this->setSort('replies=0 AND hidden=0');
        else
            $this->setSort('replies=0');

        return PHPWS_Text::profanityFilter($this->getList('threads', NULL, FALSE));
    }// END FUNC _listZero

	// List Locked threads
    function _listLocked() {
        if(!$_SESSION['OBJ_user']->username) {
          $_SESSION['OBJ_user']->jumpURL = './index.php?module=phpwsbb&PHPWSBB_MAN_OP=userthreads';
          $_SESSION['OBJ_user']->error[] = $_SESSION['translate']->it('The "Locked Threads" feature is only available to registered users of this site.  You must log-in to view this page.'); 
          $_SESSION['OBJ_user']->routeLogin();
		  exit();
        }
        
        $this->setClass('PHPWSBB_Thread');
        $this->setTable('mod_phpwsbb_threads');
        $this->setOrder('sticky DESC, lastpost DESC');
        if(!$_SESSION['OBJ_user']->allow_access('phpwsbb', 'hide_threads'))
            $this->setSort('locked=1 AND hidden=0');
        else
            $this->setSort('locked=1');

        return PHPWS_Text::profanityFilter($this->getList('threads', NULL, FALSE));
    }// END FUNC _listLocked

    // Displays a submenu for viewing new posts since last visit, today, this week...
    function _newMenu() {
        $tags = array();

        $tags['LABEL'] = $_SESSION['translate']->it('New Posts') . ':';
        $tags['LASTVISIT'] = PHPWSBB_Thread::createLink($_SESSION['translate']->it('Since Last Visit'), array('PHPWSBB_MAN_OP'=>'getnew'), 'lastvisit.png', 16, 16);
        $tags['TODAY'] = PHPWSBB_Thread::createLink($_SESSION['translate']->it('Today'), array('PHPWSBB_MAN_OP'=>'today'), 'today.png', 16, 16);
        $tags['WEEK'] = PHPWSBB_Thread::createLink($_SESSION['translate']->it('This Week'), array('PHPWSBB_MAN_OP'=>'week'), '7days.png', 16, 16);

        return implode($tags, "&#160;");

    }// END FUNC _newMenu

    // Displays a submenu for viewing thread analysis...
    function _analMenu() {
        $tags = array();

        $tags['LABEL'] = $_SESSION['translate']->it('Thread Analysis') . ':';
    	$tags['LOCKED'] = PHPWSBB_Thread::createLink($_SESSION['translate']->it('Locked Threads'), array('PHPWSBB_MAN_OP'=>'lockedthreads'), 'locked.png', 16, 16);
    	$tags['ZERO'] = PHPWSBB_Thread::createLink($_SESSION['translate']->it('Empty Threads'), array('PHPWSBB_MAN_OP'=>'zerothreads'), 'no_new_messages.png', 16, 16);
		$tags['USER'] = PHPWSBB_Thread::createLink($_SESSION['translate']->it('My Threads'), array('PHPWSBB_MAN_OP'=>'userthreads','user'=>$_SESSION['OBJ_user']->username), 'new_messages.png', 16, 16);

        return implode($tags, "&#160;");

    }// END FUNC _analMenu

    function _categories() {
        if(!$this->_allow_anon_view && !$_SESSION['OBJ_user']->username) {
          $_SESSION['OBJ_user']->jumpURL = './index.php?module=phpwsbb&PHPWSBB_MAN_OP=categories';
          $_SESSION['OBJ_user']->error[] = $_SESSION['translate']->it('Anonymous viewing of this bulletin board has been disabled. You must log-in to view this bulletin board.'); 
          $_SESSION['OBJ_user']->routeLogin();
		  exit();
		}

        $GLOBALS['CNT_phpwsbb']['title'] = $_SESSION['translate']->it('Bulletin Board Forums');

        $GLOBALS['CNT_phpwsbb']['content'] .= $this->_menu();

        $categoryView = new CategoryView;
        $categoryView->setModule('phpwsbb');
        $categoryView->setOp('PHPWSBB_MAN_OP=categories');
        if(!isset($_REQUEST['category'])) {
            $content = $categoryView->categoriesMainListing();
        } else {
            $content = $categoryView->categoriesSCView();
        }
        
        $GLOBALS['CNT_phpwsbb']['content'] .= $content;      
    }

    function _viewForum() {
        if(!$this->_allow_anon_view && !$_SESSION['OBJ_user']->username) {
          if(isset($_REQUEST['PHPWS_MAN_ITEMS'][0])) {
            $_SESSION['OBJ_user']->jumpURL = './index.php?module=phpwsbb&PHPWSBB_MAN_OP=viewforum&PHPWS_MAN_ITEMS[]=' . $_REQUEST['PHPWS_MAN_ITEMS'][0];
          } else {
            $_SESSION['OBJ_user']->jumpURL = './index.php?module=phpwsbb&PHPWSBB_MAN_OP=viewforum';
          }  
          $_SESSION['OBJ_user']->error[] = $_SESSION['translate']->it('Anonymous viewing of this forum has been disabled. You must log-in to view this forum.'); 
          $_SESSION['OBJ_user']->routeLogin();
        }

        if(isset($_REQUEST['PHPWS_MAN_ITEMS'][0])) {
            $this->forum = new PHPWSBB_Forum($_REQUEST['PHPWS_MAN_ITEMS'][0]);
            // Save this URL in case the page needs to be restored
            $this->last_viewed = array('PHPWSBB_MAN_OP'=>'viewforum', 'PHPWS_MAN_ITEMS'=>$_REQUEST['PHPWS_MAN_ITEMS'][0]);
        }

        $_REQUEST['PHPWSBB_FORUM_OP'] = 'view';
    }// END FUNC _viewForum

    function _view() {
        if(!$this->_allow_anon_view && !$_SESSION['OBJ_user']->username) {
          $_SESSION['OBJ_user']->jumpURL = './index.php?module=phpwsbb&PHPWSBB_MAN_OP=view&PHPWS_MAN_ITEMS[]=' . $_REQUEST['PHPWS_MAN_ITEMS'][0];
          $_SESSION['OBJ_user']->error[] = $_SESSION['translate']->it('Anonymous viewing of this forum has been disabled. You must log-in to view this forum.'); 
          $_SESSION['OBJ_user']->routeLogin();
        }

        $this->thread = new PHPWSBB_Thread($_REQUEST['PHPWS_MAN_ITEMS'][0]);
        $_REQUEST['PHPWSBB_THREAD_OP'] = 'view';
    }// END FUNC _view


    function _stick() {
        $this->thread = new PHPWSBB_Thread($_REQUEST['PHPWS_MAN_ITEMS'][0]);
        $_REQUEST['PHPWSBB_THREAD_OP'] = 'stick';
    }// END FUNC _stick


    function _unstick() {
        $this->thread = new PHPWSBB_Thread($_REQUEST['PHPWS_MAN_ITEMS'][0]);
        $_REQUEST['PHPWSBB_THREAD_OP'] = 'unstick';
    }// END FUNC _stick

    function _monitor() {
        $this->thread = new PHPWSBB_Thread($_REQUEST['PHPWS_MAN_ITEMS'][0]);
        $_REQUEST['PHPWSBB_THREAD_OP'] = 'monitor';
    }// END FUNC _stick


    function _unmonitor() {
        $this->thread = new PHPWSBB_Thread($_REQUEST['PHPWS_MAN_ITEMS'][0]);
        $_REQUEST['PHPWSBB_THREAD_OP'] = 'unmonitor';
    }// END FUNC _stick


    function _lock() {
        $this->thread = new PHPWSBB_Thread($_REQUEST['PHPWS_MAN_ITEMS'][0]);
        $_REQUEST['PHPWSBB_THREAD_OP'] = 'lock';
    }// END FUNC _lock


    function _unlock() {
        $this->thread = new PHPWSBB_Thread($_REQUEST['PHPWS_MAN_ITEMS'][0]);
        $_REQUEST['PHPWSBB_THREAD_OP'] = 'unlock';
    }// END FUNC _lock

    function _editForum() {
        if (isset($_REQUEST['PHPWS_MAN_ITEMS']))
            $this->forum = new PHPWSBB_Forum($_REQUEST['PHPWS_MAN_ITEMS'][0]);
        else
            $this->forum = new PHPWSBB_Forum();
        $_REQUEST['PHPWSBB_FORUM_OP'] = 'edit';
    }// END FUNC _editForum

    function _deleteForum() {
        $this->forum = new PHPWSBB_Forum($_REQUEST['PHPWS_MAN_ITEMS'][0]);
        $_REQUEST['PHPWSBB_FORUM_OP'] = 'delete';
    }// END FUNC _deleteForum

    function _delete() {
        $this->thread = new PHPWSBB_Thread($_REQUEST['PHPWS_MAN_ITEMS'][0]);
        $_REQUEST['PHPWSBB_THREAD_OP'] = 'delete';
    }// END FUNC _delete


    function _deleteMessage() {
        $this->message = new PHPWSBB_Message($_REQUEST['PHPWS_MAN_ITEMS'][0]);
        $_REQUEST['PHPWSBB_MESSAGE_OP'] = 'delete';
    }// END FUNC _deleteMessage

    function _reportMessage() {
        if(!$this->_allow_anon_posts && !$_SESSION['OBJ_user']->username) {
          $_SESSION['OBJ_user']->jumpURL = './index.php?module=phpwsbb&PHPWSBB_MAN_OP=report&PHPWS_MAN_ITEMS[]=' . $_REQUEST['PHPWS_MAN_ITEMS'][0];
          $_SESSION['OBJ_user']->error[] = $_SESSION['translate']->it('You must log-in to report a post to the administrator.'); 
          $_SESSION['OBJ_user']->routeLogin();
        }
        $this->message = new PHPWSBB_Message($_REQUEST['PHPWS_MAN_ITEMS'][0]);
        $_REQUEST['PHPWSBB_MESSAGE_OP'] = 'report';
    }// END FUNC _reportMessage


    function _new() {
        if(!$this->_allow_anon_posts && !$_SESSION['OBJ_user']->username) {
          $_SESSION['OBJ_user']->jumpURL = './index.php?module=phpwsbb&PHPWSBB_MAN_OP=new&fid=' . $_REQUEST['fid'];
          $_SESSION['OBJ_user']->error[] = $_SESSION['translate']->it('Anonymous posting to this bulletin board has been disabled.  You must log-in to post to this bulletin board.'); 
          $_SESSION['OBJ_user']->routeLogin();
        }
        $this->message = new PHPWSBB_Message;
        $_REQUEST['PHPWSBB_MESSAGE_OP'] = 'edit';
    }// END FUNC _new

    function _reply() {
        if(!$this->_allow_anon_posts && !$_SESSION['OBJ_user']->username) {
          $_SESSION['OBJ_user']->jumpURL = './index.php?module=phpwsbb&PHPWSBB_MAN_OP=reply&PHPWS_MAN_ITEMS[]=' . $_REQUEST['PHPWS_MAN_ITEMS'][0];
          $_SESSION['OBJ_user']->error[] = $_SESSION['translate']->it('Anonymous posting to this bulletin board has been disabled.  You must log-in to post to this bulletin board.'); 
          $_SESSION['OBJ_user']->routeLogin();
        }
        $this->message = new PHPWSBB_Message;
        $this->message->_tid = $_REQUEST['PHPWS_MAN_ITEMS'][0];
        $_REQUEST['PHPWSBB_MESSAGE_OP'] = 'edit';
    }// END FUNC _reply


    function _edit() {
        $this->message = new PHPWSBB_Message($_REQUEST['PHPWS_MAN_ITEMS'][0]);
        $_REQUEST['PHPWSBB_MESSAGE_OP'] = 'edit';
    }// END FUNC _edit


    function _fork() {
        $this->message = new PHPWSBB_Message($_REQUEST['PHPWS_MAN_ITEMS'][0]);
        $_REQUEST['PHPWSBB_MESSAGE_OP'] = 'fork';
    }// END FUNC _fork
    

    function _accessDenied() {
        $message = $_SESSION['translate']->it('Access was denied due to lack of proper permissions.');

        $error = new PHPWS_Error('phpwsbb', 'PHPWSBB_Manager::_accessDenied()', $message, 'exit', 1);
        $error->message();
    }// END FUNC _accessDenied


    function search($where) {
        $array = array();

        if(!$this->_allow_anon_view && !$_SESSION['OBJ_user']->username) {
            $GLOBALS['CNT_phpwsbb']['title'] = $_SESSION['translate']->it('Anonymous Viewing Denied');
            $GLOBALS['CNT_phpwsbb']['content'] = $_SESSION['translate']->it('Anonymous viewing of this bulletin board has been disabled.  You must log-in to view this bulletin board.');
        } else {
            $sql = 'SELECT tid, label FROM ' . $GLOBALS['core']->tbl_prefix . 'mod_phpwsbb_messages '. $where;
            $result = $GLOBALS['core']->query($sql);
    
            if($result) {
                while($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
                    $array[$row['tid']] = $row['label'];
                }
            }
        }

        return $array;
    }// END FUNC search

    function _banUsername() {
        if(!$_SESSION['OBJ_user']->allow_access('phpwsbb', 'ban')) {
            $message = $_SESSION['translate']->it('Access to ban usernames was denied due to lack of proper permissions.');
            $error = new PHPWS_Error('phpwsbb', 'PHPWSBB_Manager::_banIPAddress()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        $data['username'] = $_REQUEST['PHPWS_MAN_ITEMS'][0];
        $result = $GLOBALS['core']->sqlInsert($data,'mod_phpwsbb_banned');

        $GLOBALS['CNT_phpwsbb']['content'] .= '<b>' . $_SESSION['translate']->it('Username [var1] has been banned.', $_REQUEST['PHPWS_MAN_ITEMS'][0]) . '</b>';
        
    }// END FUNC _banUsername

    function _unbanUsername() {
        if(!$_SESSION['OBJ_user']->allow_access('phpwsbb', 'ban')) {
            $message = $_SESSION['translate']->it('Access to ban usernames was denied due to lack of proper permissions.');
            $error = new PHPWS_Error('phpwsbb', 'PHPWSBB_Manager::_banIPAddress()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        $data['username'] = $_REQUEST['PHPWS_MAN_ITEMS'][0];
        $result = $GLOBALS['core']->sqlDelete('mod_phpwsbb_banned',$data);

        $GLOBALS['CNT_phpwsbb']['content'] .= '<b>' . $_SESSION['translate']->it('Username [var1] has been re-allowed.', $_REQUEST['PHPWS_MAN_ITEMS'][0]) . '</b>';
        
    }// END FUNC _unbanUsername

    function _banIPAddress() {
        if(!$_SESSION['OBJ_user']->allow_access('phpwsbb', 'ban')) {
            $message = $_SESSION['translate']->it('Access to ban IP addresses was denied due to lack of proper permissions.');
            $error = new PHPWS_Error('phpwsbb', 'PHPWSBB_Manager::_banIPAddress()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        $data['ip'] = $_REQUEST['PHPWS_MAN_ITEMS'][0];
        $result = $GLOBALS['core']->sqlInsert($data,'mod_phpwsbb_banned');

        $GLOBALS['CNT_phpwsbb']['content'] .= '<b>' . $_SESSION['translate']->it('IP Address [var1] has been banned.', $_REQUEST['PHPWS_MAN_ITEMS'][0]) . '</b>';
        
    }// END FUNC _banIPAddress

    function _unbanIPAddress() {
        if(!$_SESSION['OBJ_user']->allow_access('phpwsbb', 'ban')) {
            $message = $_SESSION['translate']->it('Access to ban IP addresses was denied due to lack of proper permissions.');
            $error = new PHPWS_Error('phpwsbb', 'PHPWSBB_Manager::_banIPAddress()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        $data['ip'] = $_REQUEST['PHPWS_MAN_ITEMS'][0];
        $result = $GLOBALS['core']->sqlDelete('mod_phpwsbb_banned',$data);

        $GLOBALS['CNT_phpwsbb']['content'] .= '<b>' . $_SESSION['translate']->it('IP Address [var1] has been re-allowed.', $_REQUEST['PHPWS_MAN_ITEMS'][0]) . '</b>';
        
    }// END FUNC _unbanIPAddress


    function _updateAllThreads() {
        if(!$_SESSION['OBJ_user']->allow_access('phpwsbb', 'edit_messages')) {
            $message = $_SESSION['translate']->it('Access to update topics was denied due to lack of proper permissions.');
            $error = new PHPWS_Error('phpwsbb', 'PHPWSBB_Manager::_updateAllThreads()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }
             
        $GLOBALS['CNT_phpwsbb']['title'] = $_SESSION['translate']->it('Updating All Topics');
        $GLOBALS['CNT_phpwsbb']['content'] .= $this->_menu();

        $sql = 'SELECT id FROM ' . $GLOBALS['core']->tbl_prefix . 'mod_phpwsbb_threads ORDER BY updated ASC';
        $result = $GLOBALS['core']->getCol($sql);
        if($result) {
            foreach($result as $rowid) {
                // Update thread label with label of root message
                $tmpthread = new PHPWSBB_Thread($rowid);
                $tmpthread->updateThread();
                sleep(1);
            }
        }

        $GLOBALS['CNT_phpwsbb']['content'] .= $_SESSION['translate']->it('Complete!');
    }// END FUNC _updateAllThreads



    /**
    * Show user settings form
    *
    * @author Don Seiler <don@NOSPAM.seiler.us>
  	* @module PHPWSBB
  	* @param int $id : User Id
  	* @return none
    */
    function _getUserSettings($id=null) {
        if(!$_SESSION['OBJ_user']->username && !($id && $_SESSION['OBJ_user']->allow_access('phpwsbb', 'edit_users'))) {
            $message = $_SESSION['translate']->it('Access to edit settings was denied.');
            $error = new PHPWS_Error('phpwsbb', 'PHPWSBB_Manager::_getSettings()', $message, 'exit', 1);
            return FALSE;
        }

        // Load all information for the user
        if (!$id) 
            $id = $_SESSION['OBJ_user']->user_id;
        $user = $this->_load_user_settings($id);
        $tabs = 1;
        $form = new EZform('PHPWSBB_Settings_edit');

        $form->add('SETTINGS_monitordefault', 'checkbox');
        $form->setMatch('SETTINGS_monitordefault', $user['monitordefault']);
        $form->setTab('SETTINGS_monitordefault', $tabs);
        $tabs++;

        $form->add('SETTINGS_suspendmonitors', 'checkbox');
        $form->setMatch('SETTINGS_suspendmonitors', $user['suspendmonitors']);
        $form->setTab('SETTINGS_suspendmonitors', $tabs);
        $tabs++;

        $form->add('SETTINGS_location', 'text', $user['location']);
        $form->setMaxSize('SETTINGS_location', 50);
        $form->setWidth('SETTINGS_location', 70);
        $form->setTab('SETTINGS_location', $tabs++);
        if ($this->_use_signatures)
        {
          $form->add('SETTINGS_signature', 'textarea', $user['signature']);
          $form->setWidth('SETTINGS_signature', 100);
          $form->setRows('SETTINGS_signature', 4);
          $form->setTab('SETTINGS_signature', $tabs++);
        }
        if ($this->_use_avatars)
        {
            // If an avatar was selected from the gallery, place its information into the form.
            if (isset($_POST['IMGLib_selected_image']))
            {
                $user['avatar_file'] = $_POST['IMGLib_selected_image'];
                $user['avatar_dir']  = $_POST['IMGLib_selected_gallery'];
            }
            // If a local avatar is selected, show it
            if (!empty($user['avatar_dir']) && !empty($user['avatar_file']))
            {
                $user['AVATAR'] = '<img src="images/phpwsbb/'.$user['avatar_dir'].$user['avatar_file'].'" alt="" title="" border="0" />'; 
                $form->add('IMGLib_current_image', 'hidden', $user['avatar_file']);
                $form->add('IMGLib_current_gallery', 'hidden', $user['avatar_dir']);
            }
          
            if ($this->_use_offsite_avatars) 
            {
                // If a remote avatar is selected, show it
                if(empty($user['avatar_dir']) && !empty($user['avatar_file'])) 
                    $form->add('SETTINGS_avatar_file', 'text', $user['avatar_file']);
                // otherwise, display an empty textfield
                else 
                    $form->add('SETTINGS_avatar_file', 'text');
            
                $form->setMaxSize('SETTINGS_avatar_file', 100);
                $form->setWidth('SETTINGS_avatar_file', 70);
                $form->add('SETTINGS_avatar_file_oldvalue', 'hidden', $user['avatar_file']);
            }
            // If any type of avatar was selected, prepare hidden image values
            if (!empty($user['avatar_file'])) 
            {
                $form->add('SETTINGS_delete_image', 'checkbox');
                $form->add('avatar_file', 'hidden', $user['avatar_file']);
                $form->add('avatar_dir', 'hidden', $user['avatar_dir']);
            }
            $form->add('IMGLib_return_data', 'hidden', 'edit');
            $form->add('IMGLib_op', 'submit', $_SESSION['translate']->it('Show Gallery'));
        }

        $form->add('SETTINGS_save', 'submit', $_SESSION['translate']->it('Save'));

        $form->add('module', 'hidden', 'phpwsbb');
        $form->add('user', 'hidden', $id);
        $form->add('PHPWSBB_MAN_OP', 'hidden', 'saveusersettings');

        $tags = array();
        $tags = $form->getTemplate();
        $tags['MONITORS_TITLE'] = $_SESSION['translate']->it('Monitors');
        $tags['MONITORDEFAULT_TEXT'] = $_SESSION['translate']->it('Automatically monitor threads you post to?');
        if(!$this->_allow_user_monitors)
            $tags['MONITORDEFAULT_WARNING'] = $_SESSION['translate']->it('Monitoring has been disabled by the administrator.');

        $tags['SUSPENDMONITORS_TEXT'] = $_SESSION['translate']->it('Suspend monitors');
        $tags['SUSPENDMONITORS_HELP'] = CLS_help::show_link('phpwsbb', 'suspend_monitors');
        $tags['REMOVEALLMONITORS'] = '<a href="./index.php?module=phpwsbb&amp;PHPWSBB_MAN_OP=removeallmonitors">' . $_SESSION['translate']->it('Remove all monitors') . '</a>';
        $tags['REMOVEALLMONITORS_HELP'] = CLS_help::show_link('phpwsbb', 'remove_all_monitors');

        $tags['EXIT_LINK'] = PHPWS_Text::moduleLink($_SESSION['translate']->it('Back to Forums'), 'phpwsbb', $this->last_viewed);
        $tags['PERSONAL_TITLE'] = $_SESSION['translate']->it('Personal Info');
        if ($this->_use_signatures) {
            $tags['SIGNATURE_LABEL'] = $_SESSION['translate']->it('Your Signature');
            $tags['SIGNATURE_HELP'] = $_SESSION['translate']->it('This is a block of text that is automatically added to posts you make. There is a 255 character limit');
        }
        $tags['LOCATION_LABEL'] = $_SESSION['translate']->it('Your Location');
        if ($this->_use_avatars)
        {
            $tags['AVATAR_TITLE'] = $_SESSION['translate']->it('Avatar Settings');
            if(!empty($user['avatar_file'])) { 
                $tags['CURRENT_AVATAR_LABEL'] = $_SESSION['translate']->it('Current Avatar');
                $tags['CURRENT_AVATAR_IMG'] = $user['AVATAR'];
                $tags['REMOVE_AVATAR_LABEL'] = $_SESSION['translate']->it('Remove Avatar');
                if (!empty($user['avatar_dir'])) 
                    $tags['CURRENT_AVATAR_TEXT'] = $_SESSION['translate']->it('Gallery Avatar "[var1]" is currently selected', preg_replace("/[^a-zA-Z0-9]/", ' ', str_replace(strrchr($user['avatar_file'], '.'), '', $user['avatar_file'])));
                elseif (empty($user['avatar_dir']))  
                    $tags['CURRENT_AVATAR_TEXT'] = $_SESSION['translate']->it('A Remote Avatar is currently selected');
            }
          
            $tags['SELECT_AVATAR_LABEL'] = $_SESSION['translate']->it('Pick a Local Avatar');
            $tags['SELECT_AVATAR_HELP'] = $_SESSION['translate']->it('Choose an avatar from one of our galleries that will represent you on the online forums');
          
            if ($this->_use_offsite_avatars)
            {
                $tags['REMOTE_AVATAR_LABEL'] = $_SESSION['translate']->it('Link to off-site Avatar');
                $tags['REMOTE_AVATAR_HELP'] = $_SESSION['translate']->it('Enter a URL to an online avatar image.  The image must be no greater than [var1] pixels high by [var2] pixels wide, and its filesize can be no greater than [var3]Kb.', $this->_max_avatar_height, $this->_max_avatar_width, $this->_max_avatar_size);
            }
        }

        if ($id==$_SESSION['OBJ_user']->user_id) 
            $title = $_SESSION['translate']->it('User Settings');
        else 
            $title = $_SESSION['translate']->it('User Settings for "[var1]"', $user['username']);
        $content = $this->_menu() . PHPWS_Template::processTemplate($tags, 'phpwsbb', 'edit_user_settings.tpl');

        $GLOBALS['CNT_phpwsbb']['title'] = $title;
        $GLOBALS['CNT_phpwsbb']['content'] .= $content;

    }// END FUNC _getUserSettings


    /**
    * Save user settings
    *
    * @author Don Seiler <don@NOSPAM.seiler.us>
    */
    function _saveUserSettings() {
        $data = array();

        $data['monitordefault'] = 0;
        if(isset($_REQUEST['SETTINGS_monitordefault']))
            $data['monitordefault'] = 1;

        $data['suspendmonitors'] = 0;
        if(isset($_REQUEST['SETTINGS_suspendmonitors']))
            $data['suspendmonitors'] = 1;
        
        $data['location'] = '';
        if(isset($_POST['SETTINGS_location']))
            $data['location'] = PHPWS_Text::parseInput($_POST['SETTINGS_location']);
        
        $data['signature'] = '';
        if(isset($_POST['SETTINGS_signature']))
            $data['signature'] = PHPWS_Text::parseInput($_POST['SETTINGS_signature']);
        
        if($this->_use_avatars) {
            // If desired, remove all image information
            if(isset($_POST['SETTINGS_delete_image']))
                $data['avatar_file'] = $data['avatar_dir'] = '';
            // otherwise, if a gallery directory was given, save the gallery avatar
            elseif (isset($_POST['avatar_dir'])) {
                $data['avatar_file'] = $_POST['avatar_file'];
                $data['avatar_dir']  = $_POST['avatar_dir'];
            }
            // otherwise, if an offsite avatar is specified, save just the image url
            elseif($this->_use_offsite_avatars && isset($_POST['SETTINGS_avatar_file'])) {
                $url = str_replace(array('http://',' ','<','>'), '', $_POST['SETTINGS_avatar_file']);
                if($url!=$_POST['SETTINGS_avatar_file_oldvalue']) {
                    $data['avatar_file'] = $url;
                    $data['avatar_dir']  = '';
                }
            }
        }

        if($GLOBALS['core']->sqlUpdate($data, 'mod_phpwsbb_user_info', 'user_id', $_POST['user'])) {
            $title = $_SESSION['translate']->it('Settings Saved');
            $content = $this->_menu() . $_SESSION['translate']->it("Your settings have been successfully saved.") . "<br /><br />";
            $content .= PHPWS_Text::moduleLink($_SESSION['translate']->it('Back to Forums'), 'phpwsbb', $this->last_viewed);
        } else {
            $title = $_SESSION['translate']->it('Error Saving Settings');
            $content = $this->_menu() . $_SESSION['translate']->it('There was an error saving the settings.');
        }

        // Title & Content display is handled in the calling procedure
		    return array('title'=>$title, 'content'=>$content);
    }// END FUNC _saveUserSettings


    /**
    * Show settings form
    *
    * @author Don Seiler <don@NOSPAM.seiler.us>
    */
    function _getSettings() {
        if(!$_SESSION['OBJ_user']->allow_access('phpwsbb', 'edit_settings')) {
            $message = $_SESSION['translate']->it('Access to edit settings was denied.');
            $error = new PHPWS_Error('phpwsbb', 'PHPWSBB_Manager::_getSettings()', $message, 'exit', 1);
            return FALSE;
        }

        $tabs = 1;
        $form = new EZform('PHPWSBB_Settings_edit');

        $form->add('SETTINGS_allow_anon_posts', 'checkbox');
        $form->setMatch('SETTINGS_allow_anon_posts', $this->_allow_anon_posts);
        $form->setTab('SETTINGS_allow_anon_posts', $tabs);
        $tabs++;

        $form->add('SETTINGS_allow_anon_view', 'checkbox');
        $form->setMatch('SETTINGS_allow_anon_view', $this->_allow_anon_view);
        $form->setTab('SETTINGS_allow_anon_view', $tabs);
        $tabs++;

        $form->add('SETTINGS_views', 'checkbox');
        $form->setMatch('SETTINGS_views', $this->_use_views);
        $form->setTab('SETTINGS_views', $tabs);
        $tabs++;

        $form->add('SETTINGS_views_lowpri', 'checkbox');
        $form->setMatch('SETTINGS_views_lowpri', $this->_use_low_priority);
        $form->setTab('SETTINGS_views_lowpri', $tabs);
        $tabs++;

        $form->add('SETTINGS_admin_email', 'text', $this->_admin_email);
        $form->setTab('SETTINGS_admin_email', $tabs);
        $tabs++;

        $form->add('SETTINGS_monitor_posts', 'checkbox');
        $form->setMatch('SETTINGS_monitor_posts', $this->_monitor_posts);
        $form->setTab('SETTINGS_monitor_posts', $tabs);
        $tabs++;

        $form->add('SETTINGS_allow_user_monitors', 'checkbox');
        $form->setMatch('SETTINGS_allow_user_monitors', $this->_allow_user_monitors);
        $form->setTab('SETTINGS_allow_user_monitors', $tabs);
        $tabs++;

        $form->add('SETTINGS_email_text', 'textarea', $this->_email_text);
        $form->setCols('SETTINGS_email_text', 40);
        $form->setRows('SETTINGS_email_text', 8);
        $form->setTab('SETTINGS_email_text', $tabs);
        $tabs++;

        $form->add('SETTINGS_showforumsblock', 'checkbox');
        $form->setMatch('SETTINGS_showforumsblock', $this->_showforumsblock);
        $form->setTab('SETTINGS_showforumsblock', $tabs);
        $tabs++;

        $form->add('SETTINGS_forumsblocktitle', 'text', $this->_forumsblocktitle);
        $form->setTab('SETTINGS_forumsblocktitle', $tabs);
        $tabs++;

        $form->add('SETTINGS_showlatestthreadsblock', 'checkbox');
        $form->setMatch('SETTINGS_showlatestthreadsblock', $this->_showlatestthreadsblock);
        $form->setTab('SETTINGS_showlatestthreadsblock', $tabs);
        $tabs++;

        $form->add('SETTINGS_latestthreadsblocktitle', 'text', $this->_latestthreadsblocktitle);
        $form->setTab('SETTINGS_latestthreadsblocktitle', $tabs);
        $tabs++;

        $form->add('SETTINGS_maxlatestthreads', 'text', $this->_maxlatestthreads);
        $form->setSize('SETTINGS_maxlatestthreads', 5);
        $form->setTab('SETTINGS_maxlatestthreads', $tabs);
        $tabs++;

        $form->add('SETTINGS_bboffline', 'checkbox');
        $form->setMatch('SETTINGS_bboffline', $this->_bboffline);
        $form->setTab('SETTINGS_bboffline', $tabs);
        $tabs++;

        $form->add('SETTINGS_use_avatars', 'checkbox');
        $form->setMatch('SETTINGS_use_avatars', $this->_use_avatars);
        $form->setTab('SETTINGS_use_avatars', $tabs);
        $tabs++;

        $form->add('SETTINGS_use_offsite_avatars', 'checkbox');
        $form->setMatch('SETTINGS_use_offsite_avatars', $this->_use_offsite_avatars);
        $form->setTab('SETTINGS_use_offsite_avatars', $tabs);
        $tabs++;

        $form->add('SETTINGS_show_categories', 'checkbox');
        $form->setMatch('SETTINGS_show_categories', $this->_show_categories);
        $form->setTab('SETTINGS_show_categories', $tabs);
        $tabs++;

        $form->add('SETTINGS_use_signatures', 'checkbox');
        $form->setMatch('SETTINGS_use_signatures', $this->_use_signatures);
        $form->setTab('SETTINGS_use_signatures', $tabs);
        $tabs++;

        $form->add('SETTINGS_save', 'submit', $_SESSION['translate']->it('Save'));
        $form->setTab('SETTINGS_save', $tabs);

        $form->add('module', 'hidden', 'phpwsbb');
        $form->add('PHPWSBB_MAN_OP', 'hidden', 'savesettings');

        $tags = array();
        $tags = $form->getTemplate();
        $tags['STATUS_TITLE'] = $_SESSION['translate']->it('Status');
        $tags['ANON_TITLE'] = $_SESSION['translate']->it('Anonymous Users');
        $tags['STATS_TITLE'] = $_SESSION['translate']->it('Statistics');
        $tags['MONITORS_TITLE'] = $_SESSION['translate']->it('Monitors');
        $tags['BLOCKS_TITLE'] = $_SESSION['translate']->it('Blocks');
        $tags['CATEGORIES_TITLE'] = $_SESSION['translate']->it('Categories');
        $tags['AVATARS_TITLE'] = $_SESSION['translate']->it('Avatar Settings');
        $tags['PERSONAL_TITLE'] = $_SESSION['translate']->it('Personal Info');
        $tags['ANONPOSTS_TEXT'] = $_SESSION['translate']->it('Allow Anonymous Posts by default');
        $tags['ANONVIEW_TEXT'] = $_SESSION['translate']->it('Allow Anonymous Viewing of Posts by default');
        $tags['VIEWS_TEXT'] = $_SESSION['translate']->it('Record and show number of times a thread is viewed');
        $tags['VIEWS_LOWPRI_TEXT'] = $_SESSION['translate']->it('Use low priority stats updates (MySQL only)');
        $tags['ADMIN_EMAIL_TEXT'] = $_SESSION['translate']->it('Bulletin Board Admin Email');
        $tags['MONITOR_POSTS_TEXT'] = $_SESSION['translate']->it('Monitor Posts');
        $tags['ALLOW_USER_MONITORS_TEXT'] = $_SESSION['translate']->it('Allow User Monitors');
        $tags['BBOFFLINE_TEXT'] = $_SESSION['translate']->it('Take Bulletin Board Offline');
        $tags['EMAIL_TEXT_TEXT'] = $_SESSION['translate']->it('Email Notification Text');
        $tags['VIEWS_HELP'] = CLS_help::show_link('phpwsbb', 'views');
        $tags['VIEWS_LOWPRI_HELP'] = CLS_help::show_link('phpwsbb', 'views_lowpri');
        $tags['ADMIN_EMAIL_HELP'] = CLS_help::show_link('phpwsbb', 'admin_email');
        $tags['EMAIL_TEXT_HELP'] = CLS_help::show_link('phpwsbb', 'email_text');
        $tags['MONITOR_POSTS_HELP'] = CLS_help::show_link('phpwsbb', 'monitor_posts');
        $tags['BBOFFLINE_HELP'] = CLS_help::show_link('phpwsbb', 'bboffline');
        $tags['SHOWFORUMSBLOCK_TEXT'] = $_SESSION['translate']->it('Show Forums Block');
        $tags['FORUMSBLOCKTITLE_TEXT'] = $_SESSION['translate']->it('Forums Block Title');
        $tags['SHOWLATESTTHREADSBLOCK_TEXT'] = $_SESSION['translate']->it('Show Latest Threads Block');
        $tags['LATESTTHREADSBLOCKTITLE_TEXT'] = $_SESSION['translate']->it('Latest Threads Block Title');
        $tags['MAXLATESTTHREADS_TEXT'] = $_SESSION['translate']->it('Maximum Number of Threads to Show in Latest Threads Block');
        $tags['SHOW_CATEGORIES_TEXT'] = $_SESSION['translate']->it('Show Category related features');
        $tags['USE_AVATARS_TEXT'] = $_SESSION['translate']->it('Allow user-selected Avatars to be displayed in messages');
        $tags['USE_OFFSITE_AVATARS_TEXT'] = $_SESSION['translate']->it('Allow user-selected Avatar URLs to be displayed in messages');
        $tags['USE_OFFSITE_AVATARS_HELP'] = $_SESSION['translate']->it('Not recommended').CLS_help::show_link('phpwsbb', 'remote_avatars');
        $tags['USE_SIGNATURES_TEXT'] = $_SESSION['translate']->it('Allow user-created Signatures to be displayed in messages');

        // Create form table to manage user ranking information
        $tags['RANK_SPECIAL_ROWS'] = $tags['RANK_MEMBER_ROWS'] = '';
        $opts['find'] = array('RANKROW_RANK_ID', 'RANKROW_RANK_TITLE', 'RANKROW_RANK_MIN', 'RANKROW_RANK_SPECIAL');
        // Load user ranking system
        $this->_load_user_ranks();
        foreach ($this->_user_ranks as $rank) {
            $tbl = new EZform('PHPWSBB_Settings_edit');
            $tbl->add('RANKROW_RANK_TITLE', 'text', $rank['rank_title']);
            $tbl->setWidth('RANKROW_RANK_TITLE', 100);
            $tbl->add('RANKROW_RANK_MIN', 'text', $rank['rank_min'].' ');
            $tbl->setSize('RANKROW_RANK_MIN', 5);
            $tbl->add('RANKROW_RANK_SPECIAL', 'checkbox');
            $tbl->setMatch('RANKROW_RANK_SPECIAL', $rank['rank_special']);
            $row = PHPWS_Template::processTemplate($tbl->getTemplate(), 'phpwsbb', 'rank_tbl_row.tpl');
            $p = 'PHPWSBB_RANK['.$rank['rank_id'].'][';
            $opts['replace'] = array($p.'rank_id]', $p.'rank_title]', $p.'rank_min]', $p.'rank_special]');
            if($rank['rank_special'])
                $tags['RANK_SPECIAL_ROWS'] .= str_replace($opts['find'], $opts['replace'] , $row);
            else
                $tags['RANK_MEMBER_ROWS']  .= str_replace($opts['find'], $opts['replace'] , $row);
        }
        // Create input form for a new user rank.
        $tbl = new EZform('PHPWSBB_Settings_edit');
        $tbl->add('RANKROW_RANK_TITLE', 'text');
        $tbl->setWidth('RANKROW_RANK_TITLE', 100);
        $tbl->add('RANKROW_RANK_MIN', 'text', 0);
        $tbl->setSize('RANKROW_RANK_MIN', 5);
        $tbl->add('RANKROW_RANK_SPECIAL', 'checkbox');
        $tbl->setMatch('RANKROW_RANK_SPECIAL', $rank['rank_special']);
        $row = PHPWS_Template::processTemplate($tbl->getTemplate(), 'phpwsbb', 'rank_tbl_row.tpl');
        $tags['RANK_NEW_ROW'] = str_replace('RANKROW', 'PHPWSBB_NEW' , $row);
        unset($tbl);
        // Rank table text
        $tags['RANK_TABLE_TEXT'] = $_SESSION['translate']->it('Member Ranks');
        $tags['RANK_TABLE_HELP'] = $_SESSION['translate']->it('This is the current member ranking system.<br />Don\'t worry about the order - the Rank types will re-order themselves by posting level.<br />To delete a Rank, just leave the name blank.');
        $tags['RANK_TITLE_LABEL'] = $_SESSION['translate']->it('Rank Name');
        $tags['RANK_MIN_LABEL'] = $_SESSION['translate']->it('Minimum Posts');
        $tags['RANK_SPECIAL_LABEL'] = $_SESSION['translate']->it('Special Rank?');
        $tags['RANK_MEMBER_TITLE'] = $_SESSION['translate']->it('Member Ranks');
        $tags['RANK_SPECIAL_TITLE'] = $_SESSION['translate']->it('Special Ranks');
        $tags['RANK_NEW_TITLE'] = $_SESSION['translate']->it('Add a new rank by entering its information here.');

        $title = $_SESSION['translate']->it('Admin Settings');
        $content = $this->_menu() . PHPWS_Template::processTemplate($tags, 'phpwsbb', 'edit_settings.tpl');

        $GLOBALS['CNT_phpwsbb']['title'] = $title;
        $GLOBALS['CNT_phpwsbb']['content'] .= $content;

    }// END FUNC _getSettings


    /**
    * Save settings
    *
    * @author Don Seiler <don@NOSPAM.seiler.us>
    */
    function _saveSettings() {
        if(isset($_REQUEST['SETTINGS_allow_anon_posts']))
            $this->_allow_anon_posts = 1;
        else
            $this->_allow_anon_posts = 0;

        if(isset($_REQUEST['SETTINGS_allow_anon_view']))
            $this->_allow_anon_view = 1;
        else
            $this->_allow_anon_view = 0;

        if(isset($_REQUEST['SETTINGS_views']))
            $this->_use_views = 1;
        else
            $this->_use_views = 0;

        if(isset($_REQUEST['SETTINGS_views_lowpri']))
            $this->_use_low_priority = 1;
        else
            $this->_use_low_priority = 0;

        if(isset($_REQUEST['SETTINGS_monitor_posts']))
            $this->_monitor_posts = 1;
        else
            $this->_monitor_posts = 0;

        if(isset($_REQUEST['SETTINGS_allow_user_monitors']))
            $this->_allow_user_monitors = 1;
        else
            $this->_allow_user_monitors = 0;

        if(isset($_REQUEST['SETTINGS_bboffline']))
            $this->_bboffline = 1;
        else
            $this->_bboffline = 0;

        if(isset($_REQUEST['SETTINGS_showforumsblock']))
            $this->_showforumsblock = 1;
        else
            $this->_showforumsblock = 0;

        if(isset($_REQUEST['SETTINGS_showlatestthreadsblock']))
            $this->_showlatestthreadsblock = 1;
        else
            $this->_showlatestthreadsblock = 0;
        if(isset($_REQUEST['SETTINGS_use_avatars']))
            $this->_use_avatars = 1;
        else
            $this->_use_avatars = 0;

        if(isset($_REQUEST['SETTINGS_use_offsite_avatars']))
            $this->_use_offsite_avatars = 1;
        else
            $this->_use_offsite_avatars = 0;

        if(isset($_REQUEST['SETTINGS_use_signatures']))
            $this->_use_signatures = 1;
        else
            $this->_use_signatures = 0;

        if(isset($_REQUEST['SETTINGS_show_categories']))
            $this->_show_categories = 1;
        else
            $this->_show_categories = 0;

        // If changes to the user ranks were posted, save them now.
        if(isset($_POST['PHPWSBB_RANK'])) {
            $data = array();
            foreach($_POST['PHPWSBB_RANK'] as $rank_id=>$rank) {
                if(!empty($rank['rank_title'])) {
                    $data['rank_title'] = $rank['rank_title'];
                    $data['rank_min'] = $rank['rank_min'];
                    if(isset($rank['rank_special'])) 
                        $data['rank_special'] = 1;
                    else
                        $data['rank_special'] = 0;
                    $GLOBALS['core']->sqlUpdate($data, 'mod_phpwsbb_user_ranks', 'rank_id', $rank_id);
                }
                else {
                    // Erase rank if title is empty
                    $GLOBALS['core']->sqlDelete('mod_phpwsbb_user_ranks', 'rank_id', $rank_id);
                }
            }
        }
        // If a new user rank is requested, create it.
        if(!empty($_POST['PHPWSBB_NEW_RANK_TITLE'])) {
            $data = array();
            $data['rank_title'] = $_POST['PHPWSBB_NEW_RANK_TITLE'];
            if(!empty($_POST['PHPWSBB_NEW_RANK_MIN']))
                $data['rank_min'] = $_POST['PHPWSBB_NEW_RANK_MIN'];
            else
                $data['rank_min'] = 0;
            if(!empty($_POST['PHPWSBB_NEW_RANK_SPECIAL'])) 
                $data['rank_special'] = 1;
            else
                $data['rank_special'] = 0;
            $GLOBALS['core']->sqlInsert($data, 'mod_phpwsbb_user_ranks', 'rank_id');
        }
        // Now, reload the rank data into this class
        $this->_load_user_ranks();

        $this->_admin_email = PHPWS_Text::parseInput($_REQUEST['SETTINGS_admin_email']);
        $this->_email_text = PHPWS_Text::parseInput($_REQUEST['SETTINGS_email_text']);
        $this->_forumsblocktitle = PHPWS_Text::parseInput($_REQUEST['SETTINGS_forumsblocktitle']);
        $this->_latestthreadsblocktitle = PHPWS_Text::parseInput($_REQUEST['SETTINGS_latestthreadsblocktitle']);
        $this->_maxlatestthreads = PHPWS_Text::parseInput($_REQUEST['SETTINGS_maxlatestthreads']);

        $data = array();
        $data['allow_anon_posts'] = $this->_allow_anon_posts;
        $data['allow_anon_view'] = $this->_allow_anon_view;
        $data['use_views'] = $this->_use_views;
        $data['use_low_priority'] = $this->_use_low_priority;
        $data['admin_email'] = $this->_admin_email;
        $data['email_text'] = $this->_email_text;
        $data['monitor_posts'] = $this->_monitor_posts;
        $data['allow_user_monitors'] = $this->_allow_user_monitors;
        $data['bboffline'] = $this->_bboffline;
        $data['showforumsblock'] = $this->_showforumsblock;
        $data['showlatestthreadsblock'] = $this->_showlatestthreadsblock;
        $data['forumsblocktitle'] = $this->_forumsblocktitle;
        $data['latestthreadsblocktitle'] = $this->_latestthreadsblocktitle;
        $data['maxlatestthreads'] = $this->_maxlatestthreads;
        $data['use_avatars'] = $this->_use_avatars;
        $data['use_offsite_avatars'] = $this->_use_offsite_avatars;
        $data['use_signatures'] = $this->_use_signatures;
        $data['show_categories'] = $this->_show_categories;

        if($GLOBALS['core']->sqlUpdate($data, 'mod_phpwsbb_settings')) {
            $title = $_SESSION['translate']->it('Settings Saved');
            $content = $this->_menu() . $_SESSION['translate']->it('Your settings have been successfully saved.') . "<br />";
            $content .= PHPWS_Text::moduleLink($_SESSION['translate']->it('Back to Forums'), 'phpwsbb', $this->last_viewed);
        } else {
            $title = $_SESSION['translate']->it('Error Saving Settings');
            $content = $this->_menu() . $_SESSION['translate']->it('There was an error saving the settings.');
        }

        $GLOBALS['CNT_phpwsbb']['title'] = $title;
        $GLOBALS['CNT_phpwsbb']['content'] .= $content;
        $_SESSION['PHPWSBB_Manager'] = NULL;

    }// END FUNC _saveSettings


    function _removeAllMonitors() {

        if (isset($_REQUEST['option_yes'])) {

            // do the deed here
            $data = array();
            $data['user_id'] = $_SESSION['OBJ_user']->user_id;
            $GLOBALS['core']->sqlDelete('mod_phpwsbb_monitors', $data);

            $message = $_SESSION['translate']->it('Your monitors have been cleared.');
            $_SESSION['PHPWSBB_Manager']->notice = new PHPWS_Message($message, 'CNT_phpwsbb');
            $_REQUEST['PHPWSBB_MAN_OP'] = 'list';
            $_SESSION['PHPWSBB_Manager']->action();

        } elseif (isset($_REQUEST['option_no'])) {
            $message = $_SESSION['translate']->it('No monitors were cleared.');
            $_SESSION['PHPWSBB_Manager']->notice = new PHPWS_Message($message, 'CNT_phpwsbb');
            $_REQUEST['PHPWSBB_MAN_OP'] = 'list';
            $_SESSION['PHPWSBB_Manager']->action();

        } else {
            $title = $_SESSION['translate']->it('Clear Monitor Confirmation');

            $form = new EZform('PHPWSBB_Message_delete');
            $form->add('module', 'hidden', 'phpwsbb');
            $form->add('PHPWSBB_MAN_OP', 'hidden', 'removeallmonitors');

            $form->add('option_yes', 'submit', $_SESSION['translate']->it('Yes'));
            $form->add('option_no', 'submit', $_SESSION['translate']->it('No'));

            $tags = array();
            $tags = $form->getTemplate();
            $tags['MESSAGE'] = $_SESSION['translate']->it('Are you sure you want to clear ALL of your monitors?');

            $content = PHPWS_Template::processTemplate($tags, 'phpwsbb', 'delete.tpl');
            $_SESSION['OBJ_layout']->popbox($title, $content, NULL, 'CNT_phpwsbb');
        }

    }// END FUNC _removeAllMonitors
    

    function offline() {
        // Need to refresh this from database every time
        $result = $GLOBALS['core']->sqlSelect('mod_phpwsbb_settings');
        $this->_bboffline = $result[0]['bboffline'];

        if($this->_bboffline)
            return TRUE;
        else
            return FALSE;
    }// END FUNC offline


    function _recalc_users() {
        if(!$_SESSION['OBJ_user']->allow_access('phpwsbb', 'edit_forums')) {
            $message = $_SESSION['translate']->it('Access to update user posts counts was denied due to lack of proper permissions.');
            $error = new PHPWS_Error('phpwsbb', 'PHPWSBB_Manager::_recalc_users()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        } else {
            /* Resets all post count to 0 so they'll be recalculated on user_load */
            $GLOBALS['core']->query('UPDATE mod_phpwsbb_user_info SET posts=0', true);
            $GLOBALS['CNT_phpwsbb']['title'] = $_SESSION['translate']->it('Bulletin Board Forums');
            $GLOBALS['CNT_phpwsbb']['content'] = $_SESSION['translate']->it('All user post counts have been reset.');
        }        
    }// END FUNC recalc_users


    function action() {
        if(PHPWS_Message::isMessage($this->notice)) {
            $this->notice->display();
            $this->notice = null;
        }

        $this->managerAction();

        switch($_REQUEST['PHPWSBB_MAN_OP']) {
            case 'editforum':
                $this->_editForum();
                break;

            case 'deleteforum':
                $this->_deleteForum();
                break;

            case 'viewforum':
                $this->_viewForum();
                break;

            case 'new':
                $this->_new();
                break;

            case 'reply':
                $this->_reply();
                break;

            case 'lock':
                $this->_lock();
                break;

            case 'unlock':
                $this->_unlock();
                break;

            case 'fork':
                $this->_fork();
                break;

            case 'unstick':
                $this->_unstick();
                break;

            case 'stick':
                $this->_stick();
                break;

            case 'unmonitor':
                $this->_unmonitor();
                break;

            case 'monitor':
                $this->_monitor();
                break;

            case 'deletemessage':
                $this->_deleteMessage();
                break;

            case 'report':
                $this->_reportMessage();
                break;

            case 'access_denied':
                $this->_accessDenied();
                break;

            case 'update_all_threads':
                $this->_updateAllThreads();
                break;

            case 'getsettings':
                $this->_getSettings();
                break;

            case 'savesettings':
                $this->_saveSettings();
                break;

            case 'usersettings':
                PHPWS_Layout::addPageTitle($_SESSION['translate']->it('Bulletin Board Settings'));
                if (isset($_REQUEST['user'])) 
                    $this->_getUserSettings((int)$_REQUEST['user']);
                else
                    $this->_getUserSettings();
                break;

            case 'saveusersettings':
                $result = $this->_saveUserSettings();
                $GLOBALS['CNT_phpwsbb']['title'] = $result['title'];
                $GLOBALS['CNT_phpwsbb']['content'] .= $result['content'];
                break;

            case 'removeallmonitors':
                $this->_removeAllMonitors();
                break;

            case 'banusername':
                $this->_banUsername();
                $this->_list();
                break;

            case 'unbanusername':
                $this->_unbanUsername();
                $this->_list();
                break;

            case 'banip':
                $this->_banIPAddress();
                $this->_list();
                break;

            case 'unbanip':
                $this->_unbanIPAddress();
                $this->_list();
                break;

            case 'showforum':
                $this->setTable('mod_phpwsbb_forums');
                $this->_doMassUpdate('hidden',0);
                $this->_list();
                break;

            case 'hideforum':
                $this->setTable('mod_phpwsbb_forums');
                $this->_doMassUpdate('hidden',1);
                $this->_list();
                break;

            case 'showthread':
                $this->setTable('mod_phpwsbb_threads');
                $this->_doMassUpdate('hidden',0);
                $this->_list();
                break;

            case 'hidethread':
                $this->setTable('mod_phpwsbb_threads');
                $this->_doMassUpdate('hidden',1);
                $this->_list();
                break;

            case 'showmessage':
                $this->setTable('mod_phpwsbb_messages');
                $this->_doMassUpdate('hidden',0);
                $this->_list();
                break;

            case 'hidemessage':
                $this->setTable('mod_phpwsbb_messages');
                $this->_doMassUpdate('hidden',1);
                $this->_list();
                break;

            case 'view':  // already handled in [/core/Manager.php]->manageraction() 
                /* Save this URL in case the page needs to be restored */
                $this->last_viewed = array('PHPWSBB_MAN_OP'=>'view', 'PHPWS_MAN_ITEMS'=>$_REQUEST['PHPWS_MAN_ITEMS'][0]);
                break;

            case 'categories':
                PHPWS_Layout::addPageTitle($_SESSION['translate']->it('Bulletin Board Categories'));
                $this->_categories();
                break;    

            case 'analysis':
                PHPWS_Layout::addPageTitle($_SESSION['translate']->it('Thread Analysis'));
                $GLOBALS['CNT_phpwsbb']['title'] = $_SESSION['translate']->it('Thread Analysis');
				$GLOBALS['CNT_phpwsbb']['content'] = $this->_menu() . $this->_analmenu();
                break;    

            case 'recalc_users':
                $this->_recalc_users();
                $this->_list();
                break;

             case 'getnew': 
                if (!$since = $GLOBALS['core']->getOne('SELECT last_on FROM mod_phpwsbb_user_info WHERE user_id = '.$_SESSION['OBJ_user']->user_id, 1))
                    $since = time();
                $GLOBALS['CNT_phpwsbb']['title'] = $_SESSION['translate']->it('New Posts Since My Last Visit on [var1]', date(PHPWS_DATE_FORMAT . ' ' . PHPWS_TIME_FORMAT, $since));
                $GLOBALS['CNT_phpwsbb']['content'] = $this->_menu() . $this->_newMenu() . $this->_listNewThreads($since);
                PHPWS_Layout::addPageTitle($_SESSION['translate']->it('New Posts'));
                break;

             case 'today': 
                $since = strtotime("00:00 today");
                $GLOBALS['CNT_phpwsbb']['title'] = $_SESSION['translate']->it('Today\'s Posts');
                $GLOBALS['CNT_phpwsbb']['content'] = $this->_menu() . $this->_newMenu() .  $this->_listNewThreads($since);
                PHPWS_Layout::addPageTitle($_SESSION['translate']->it('Today\'s Posts'));
                break;

             case 'week': 
                $since = strtotime("last monday");
                $GLOBALS['CNT_phpwsbb']['title'] = $_SESSION['translate']->it('This Week\'s Posts');
                $GLOBALS['CNT_phpwsbb']['content'] = $this->_menu() . $this->_newMenu() .  $this->_listNewThreads($since);
                PHPWS_Layout::addPageTitle($_SESSION['translate']->it('This Week\'s Posts'));
                break;
			
			case 'userthreads':
                if (isset($_REQUEST['user'])) {
	            	$GLOBALS['CNT_phpwsbb']['title'] = $_SESSION['translate']->it('Threads started by [var1]', $_REQUEST['user']);
					$GLOBALS['CNT_phpwsbb']['content'] = $this->_menu() . $this->_analMenu() . $this->_listUserThreads($_REQUEST['user']);
				} else {
	            	$GLOBALS['CNT_phpwsbb']['title'] = $_SESSION['translate']->it('My Threads');
					$GLOBALS['CNT_phpwsbb']['content'] = $this->_menu() . $this->_analMenu() . $this->_listUserThreads();
				}
	            break;

			case 'zerothreads':
	            $GLOBALS['CNT_phpwsbb']['title'] = $_SESSION['translate']->it('Threads with no replies');
				$GLOBALS['CNT_phpwsbb']['content'] = $this->_menu() . $this->_analMenu() . $this->_listZero();
	            break;

			case 'lockedthreads':
	            $GLOBALS['CNT_phpwsbb']['title'] = $_SESSION['translate']->it('Locked Threads');
				$GLOBALS['CNT_phpwsbb']['content'] = $this->_menu() . $this->_analMenu() . $this->_listLocked();
	            break;

        }

    }// END FUNC action


	/* Load user ranking data from the table.
	*
	* @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
	* @module PHPWSBB
	* @param none
	* @return none
	*/
	function _load_user_ranks ()
	{
        $results = $GLOBALS['core']->sqlSelect('mod_phpwsbb_user_ranks', NULL, NULL, 'rank_min');
        if($results) 
            $this->_user_ranks = $results;
        else
            $this->_user_ranks = NULL;
	}

	/**
	* Retrieves a user's forum information.
	*
	* @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
	* @module PHPWSBB
	* @param mixed $id : (int)User Id or (array)Stored User Information
	* @return none
	*/
	function _load_user_settings ($id)
	{
	    // If this is not a member, mark them as a guest
	    if(!$id) {
          return array();
      // If user data was passed in, use it
      } elseif(is_array($id) && !empty($id['user_id'])) {
          $result = $id;
	    } else
	    		$result = $GLOBALS['core']->getRow('SELECT * FROM ' . $GLOBALS['core']->tbl_prefix . 'mod_phpwsbb_user_info AS i LEFT JOIN ' . $GLOBALS['core']->tbl_prefix . 'mod_users AS u ON ( u.user_id = i.user_id ) LEFT JOIN ' . $GLOBALS['core']->tbl_prefix . 'mod_user_uservar AS v ON ( v.user_id = u.user_id AND v.varName="MOD_phpwsbb") WHERE i.user_id = '.$id);

      // If user's post count is set to 0, recalculate it.
      if($result['posts']==0) {
          // Develop correct user post counts
          $result['posts'] = $data['posts'] = $GLOBALS['core']->getOne('SELECT COUNT(owner_id) FROM mod_phpwsbb_messages WHERE owner_id='.$id, 1);
          $GLOBALS['core']->sqlUpdate($data, 'mod_phpwsbb_user_info',  'user_id', $id);
      }

      $result['POSTS'] = $_SESSION['translate']->it('Posts').': '.$result['posts'];
      $result['MONITORDEFAULT'] = $result['monitordefault'];
      $result['SUSPENDMONITORS'] = $result['suspendmonitors'];

      if (!empty($result['location'])) 
          $result['FROM'] = $_SESSION['translate']->it('From').': '.PHPWS_Text::parseOutput($result['location']);

      $result['AVATAR'] = null;
      if (!empty($result['avatar_file']) && $this->_use_avatars) {
          // Gallery Avatar
          if (!empty($result['avatar_dir']))
              $result['AVATAR'] = '<img src="images/phpwsbb/'.$result['avatar_dir'].$result['avatar_file'].'" alt="" title="" border="0" />';
          // Offsite Avatar
          elseif ($this->_use_offsite_avatars && empty($result['avatar_dir']))
              $result['AVATAR'] = '<img src="http://'.$result['avatar_file'].'" alt="" title="" border="0" />';
      }

      if (!empty($result['signature']) && $this->_use_signatures) 
          $result['SIGNATURE'] = PHPWS_Text::parseOutput($result['signature']);

      // Load user ranking system
      if(empty($this->_user_ranks))
          $this->_load_user_ranks();
      // Determine user's rank
      if($result['posts']&& $this->_user_ranks) {
          $isSpecialuser = (isset($result['deity']) && $result['deity']) || (isset($result['varValue']) && (stristr($result['varValue'], 'edit_forums') || stristr($result['varValue'], 'edit_messages')));
          foreach ($this->_user_ranks as $rank) 
              if(($rank['rank_min']<=$result['posts'])
                  && (($rank['rank_special'] && $isSpecialuser) 
                  || (!$rank['rank_special'] && !$isSpecialuser))) {
                  $result['RANK'] = $rank['rank_title'];
              }
      }
      return $result;
  }

	/**
	* Updates a user's log data.
	*
	* @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
	* @module PHPWSBB
	* @param none
	* @return none
	*/
  function _update_userlog() {
      // If no user record exists, create one
	    if(!$result = $GLOBALS['core']->getRow('SELECT * FROM ' . $GLOBALS['core']->tbl_prefix . 'mod_phpwsbb_user_info WHERE user_id = '.$_SESSION['OBJ_user']->user_id)) {
	        $arr['session_start'] = time();
	        $arr['user_id'] = $_SESSION['OBJ_user']->user_id;
	        $GLOBALS['core']->sqlInsert($arr, 'mod_phpwsbb_user_info', FALSE, FALSE, FALSE, FALSE);
	    }
    	$GLOBALS['core']->query('UPDATE mod_phpwsbb_user_info SET last_on = session_start, session_start = '.time().' WHERE user_id ='.$_SESSION['OBJ_user']->user_id, 1, 1);
    	$this->logged_in = true;
  }// END FUNC _update_userlog

}
?>

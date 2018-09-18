<?php

/**
 * RSS.php
 * Defines RSS class for RSS Feeds module
 *
 * This class is an extension of the PHPWS_Item class defined in core/Item.php.
 * Its function is to create, edit, delete, and view one of the items in a list.
 * The class defines additional variables to the standard ones handled automatically
 * by PHPWS_Item, defines a constructor, _edit(), _delete(), and _view() function, and
 * an action() switch that handles other procedures (here, a save() function) that
 * are needed.
 *
 * The listing of these items is handled by an extension of the RSS_Manager and 
 * RSS_ObjManager classes, defined in mod/phpwsrssfeeds/class/RSSManager.php and 
 * mod/phpwsrssfeeds/class/RSSObjManager.php.
 *
 * @version $Id: RSS.php,v 1.55 2005/03/15 16:20:37 wendall911 Exp $
 * @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
 * @package phpwsrssfeeds
 */
 
require_once(PHPWS_SOURCE_DIR.'core/Item.php');
 
class RSS extends PHPWS_Item {
     
    /**
     * title of this feed is stored in the item's Label field
     */
     
    /**
     * RSS Feed Channel URL
     * @var string
     */
    var $_channel;
    /**
     * PHPWS_Template tags input cache for current feed
     * @var string
     */
    var $_content;
    /**
     * Footer for this feed.
     * @var string
     */
    var $_footer;
    /**
     * Show Channel Title setting
     * @var int
     */
    var $_show_title;
    /**
     * Show Channel image setting
     * @var int
     */
    var $_show_image;
    /**
     * Show Channel Title as link setting
     * @var int
     */
    var $_show_url;
    /**
     * Show Channel description setting
     * @var string
     */
    var $_show_desc;
    /**
     * Show 'View Details' option for feeds
     * @var boolean
     */
    var $_show_details;
    /**
     * Timestamp for last update
     * @var string
     */
    var $_cached;
    /**
     * Message displayed when feed contains no content.
     * @var string
     */
    var $_empty_feed_msg;
    /**
     * Setting to display Feed in main table cell on home page
     * @var int
     */
    var $_home;
    /**
     * Setting to display Feed in Block
     * @var int
     */
    var $_block;
    /**
     * Setting for Link target
     * @var string
     */
    var $_target;
    /**
     * Setting for cache expiration
     * @var string
     */
    var $_expire;

    /**
     * maximum number of items for display for full view and block
     * @var string
     */
    var $_max_items;

    /**
     * list of modules needing to be active for feed to appear
     * @var array
     */
    var $_allow_view;

    /**
     * list of modules needing to be active for feed to appear
     * @var array
     */
    var $_pm_allow;

    /**
     * feed item sort order
     * @var string
     */
    var $_asc_desc;

    /**
     * feed item organization
     * @var string
     */
    var $_orgn_by;

    /**
     * Calendar feed days ahead by date
     * @var string
     */
    var $_days_ahead;
    
    /**
     * Show expired items
     * @var bool
     */
    var $_show_expired;

    /**
     * channel variables
     * @var string
     */

    var $channel_title;
    var $channel_link;
    var $channel_desc;
    var $channel_image;
    var $channel_copy;
    var $channel_item_title;
    var $channel_item_desc;
    var $channel_item_author;
    var $channel_item_link;
    var $_error = NULL;

    /**
     * Constructor for the RSS class
     * Sets all the RSS attributes
     *
     * @param int $id id of the rss feed to be constructed; NULL if new rss
     */
     
    function RSS($id = NULL) {
        /* These vars are excluded on commit() */
        $exclude = array('channel_title','channel_link','_error',
          'channel_copy','channel_item_title','channel_item_desc',
          'channel_item_author','channel_item_link','channel_image');
        $this->addExclude($exclude);
        /* point to the rss data table */
        $this->setTable('mod_phpwsrssfeeds_feeds');
        
        /* if the item exists, initialize its variables from the database */
        if (is_numeric($id)) {
            /* setId returns a PHPWS_Error object */
            $this->_error = $this->setId($id);
            if (!is_object($this->_error)) {
                /* initialize variables */
                $this->_error = $this->init($id);
            }
        } elseif(is_array($id)) {
            $this->_error = $this->init($id);
        } else {
            $this->_error = NULL;
            $this->_channel = 'http://';
            $this->_content = '';
            $this->_footer = NULL;
            $this->_show_title = 0;
            $this->_show_image = 0;
            $this->_show_url = 0;
            $this->_show_desc = 0;
            $this->_show_details = 1;
            $this->_cached = '0000-00-00 00:00:00';
            $this->_home = 0;
            $this->_block = 0;
            $this->_target = '_blank';
            $this->_expire = '60';
            $this->_max_items = '20';
            $this->_allow_view = array();
            $this->_pm_allow = array();
            $this->_asc_desc = 'ASC';
            $this->_orgn_by = 'Default';
            $this->_days_ahead = 0;
            $this->_show_expired = 1;
        }
    } //END function RSS

    function getRSS($settings = NULL) {
        $instance = new RSS($settings);
        return $instance;
    }//End function getRSS()

    /**
     * Edit a RSS object's data
     *
     * @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
     */
    function _edit($id = NULL) {
        if (!is_null($id))
            $this->RSS($id);
        //Standard error checking
        if (is_object($this->_error))
            return FALSE;

        $tags = array();

        $newrss = ($this->getId() == NULL);
        // Set up the edit form
        $form = new EZform('RSS_EDIT');

        $tab = 1;

        $form->add('TITLE', 'text', PHPWS_Text::parseOutput($this->getLabel()));
        $form->setSize('TITLE', 30);
        $form->setMaxSize('TITLE', 255);
        $form->setTab('TITLE', $tab++);

        $form->add('CHANNEL', 'text', $this->_channel);
        $form->setSize('CHANNEL', 30);
        $form->setMaxSize('CHANNEL', 255);
        $form->setTab('CHANNEL', $tab++);

        $form->add('FOOTER', 'text', PHPWS_Text::parseOutput($this->_footer));
        $form->setSize('FOOTER', 30);
        $form->setMaxSize('FOOTER', 255);
        $form->setTab('FOOTER', $tab++);

        $form->add('EXPIRE', 'text', $this->_expire);
        $form->setSize('EXPIRE', 5);
        $form->setMaxSize('EXPIRE', 255);
        $form->setTab('EXPIRE', $tab++);

        $form->add('MAX_ITEMS', 'text', $this->_max_items);
        $form->setSize('MAX_ITEMS', 2);
        $form->setMaxSize('MAX_ITEMS', 255);
        $form->setTab('MAX_ITEMS', $tab++);

        $options = array('_blank' => '_blank', '_self' => '_self');
        $form->add('TARGET', 'select', $options);
        $form->setMatch('TARGET', $this->_target);
        $form->setTab('TARGET', $tab++);

        $form->add('SHOW_TITLE', 'radio', array(1, 0));
        $form->setMatch('SHOW_TITLE', $this->_show_title);
        $form->setTab('SHOW_TITLE', $tab++);

        $form->add('SHOW_URL', 'radio', array(1, 0));
        $form->setMatch('SHOW_URL', $this->_show_url);
        $form->setTab('SHOW_URL', $tab++);

        $form->add('SHOW_IMAGE', 'radio', array(1, 0));
        $form->setMatch('SHOW_IMAGE', $this->_show_image);
        $form->setTab('SHOW_IMAGE', $tab++);

        $form->add('SHOW_DESC', 'radio', array(1, 0));
        $form->setMatch('SHOW_DESC', $this->_show_desc);
        $form->setTab('SHOW_DESC', $tab++);

        $form->add('SHOW_DETAILS', 'radio', array(1, 0));
        $form->setMatch('SHOW_DETAILS', $this->_show_details);
        $form->setTab('SHOW_DETAILS', $tab++);

        $form->add('HOME', 'checkbox');
        $form->setMatch('HOME', $this->_home);
        $form->setTab('HOME', $tab++);

        $form->add('BLOCK', 'checkbox');
        $form->setMatch('BLOCK', $this->_block);
        $form->setTab('BLOCK', $tab++);

	if(empty($this->_empty_feed_msg))
	  $this->_empty_feed_msg = $_SESSION["translate"]->it("Feed Contains No Content");

	$form->add('EMPTY_FEED_MSG', 'text');
	$form->setValue('EMPTY_FEED_MSG', $this->_empty_feed_msg);
	$form->setTab('EMPTY_FEED_MSG', $tab++);

        $form->add('ALLOW_VIEW', 'multiple', RSS_common::get_modules_allowed());
        if (is_array($this->_allow_view))
            $form->setMatch('ALLOW_VIEW', $this->_allow_view, FALSE);
        if(version_compare($GLOBALS["core"]->version,"0.10.0") > 0)
            $form->setOptgroups('ALLOW_VIEW', array('label', 'line'));
        $form->setSize('ALLOW_VIEW', 10);
        $form->setTab('ALLOW_VIEW', $tab++);
         
        if ($GLOBALS['core']->moduleExists('pagemaster')) {
            $pages = $this->getPMOptions();
            if (is_array($pages)) {
                $form->add('PM_ALLOW', 'multiple', $pages);
                if(version_compare($GLOBALS["core"]->version,"0.10.0") > 0)
                    $form->setOptgroups('PM_ALLOW', array('label', 'line'));
                $form->setTab('PM_ALLOW', $tab++);
                $form->setSize('PM_ALLOW', 10);
                if (is_array($this->_pm_allow))
                $form->setMatch('PM_ALLOW', $this->_pm_allow, FALSE);
            }
        }
        $fatcat = FALSE;
        if ($GLOBALS['core']->moduleExists('fatcat')) {
            $fatcat = TRUE;
            $form->add('fatcatProcess', 'hidden', '1');
            $children = $_SESSION['OBJ_fatcat']->getCategoryList();
            if ($children) {
                if (!$newrss)
                    $match = $_SESSION['OBJ_fatcat']->getModulesCategories('phpwsrssfeeds', $this->getId());
                $sticky = 0;
                if (!$newrss && $GLOBALS['core']->getOne('select element_id from mod_fatcat_elements where module_id='.$this->getId().' and module_title="phpwsrssfeeds" and rating=999', TRUE))
                    $sticky = 1;
                $form->add('CATEGORY', 'multiple', $children);
                if (!$newrss)
                    $form->setMatch('CATEGORY', $match, FALSE);
                $form->setSize('CATEGORY', 10);
                $form->setTab('CATEGORY', $tab++);
                 
                $form->add('STICKY', 'checkbox');
                if (!$newrss)
                    $form->setMatch('STICKY', $sticky);
                $form->setTab('STICKY', $tab++);
                 
                $tags['STICKY_LABEL'] = $_SESSION['translate']->it('Sticky');
                $tags['STICKY_HELP'] = RSS_common::show_link('phpwsrssfeeds', 'rssSticky');
            } else {
                if ($_SESSION['OBJ_user']->allow_access('fatcat'))
                    $catMessage = RSS_common::_errorMessage(PHPWS_Text::moduleLink($_SESSION['translate']->it('No Categories Available'), 'fatcat', array('fatcat[admin]' => 'menu')));
                else
                    $catMessage = RSS_common::_errorMessage($_SESSION['translate']->it('No Categories Available'));
            }
        }

        $orgn = array('Default' => $_SESSION['translate']->it('Default'),
          'Date' => $_SESSION['translate']->it('Date'), 
          'Alpha' => $_SESSION['translate']->it('Alphabetical'));
        $form->add('ORGN_BY', 'select', $orgn);
        $form->setMatch('ORGN_BY', $this->_orgn_by);
        $form->setTab('ORGN_BY', $tab++);

        $asc_desc = array('DESC' => $_SESSION['translate']->it('DESC'),
          'ASC' => $_SESSION['translate']->it('ASC'));
        $form->add('ASC_DESC', 'select', $asc_desc);
        $form->setMatch('ASC_DESC', $this->_asc_desc);
        $form->setTab('ASC_DESC', $tab++);
        
        $form->add('SHOW_EXPIRED', 'checkbox');
        $form->setMatch('SHOW_EXPIRED', $this->_show_expired);
        $form->setTab('SHOW_EXPIRED', $tab++);

        $ahead[0] = $_SESSION['translate']->it('Today');
        $ahead[1] = '1 '.$_SESSION['translate']->it('Day');
        for($i = 2; $i < 15; $i++)
            $ahead[$i] = $i.' '.$_SESSION['translate']->it('Days');
        $ahead[30] = '30 '.$_SESSION['translate']->it('Days');
        $ahead[90] = '90 '.$_SESSION['translate']->it('Days');
        $ahead[180] = '180 '.$_SESSION['translate']->it('Days');
        $ahead[365] = '1 '.$_SESSION['translate']->it('Year');
        $ahead['Any'] = $_SESSION['translate']->it('Any');

        $form->add('DAYS_AHEAD', 'select', $ahead);
        $form->setMatch('DAYS_AHEAD', $this->_days_ahead);

        $form->add('SUBMIT_BUTTON', 'submit', $this->getId() ? 
          $_SESSION['translate']->it('Update') : $_SESSION['translate']->it('Save'));
        $form->setTab('SUBMIT_BUTTON', $tab++);
        $form->add('module', 'hidden', 'phpwsrssfeeds');
        $form->add('CACHED', 'hidden', date('Y-m-d G:i:s'));
        $form->add('PHPWS_MAN_ITEMS[]', 'hidden', $this->getId());
        $form->add('RSS_MAN_op', 'hidden', 'save');

        $tags = $form->getTemplate(true, true, $tags);

        // include category selection menu, with help if fatcat exists
        if ($fatcat) {
            if (isset($catMessage))
                $tags['CATEGORY'] = $catMessage;
            $tags['CATEGORY_HELP'] = RSS_common::show_link('phpwsrssfeeds', 'rssCategory');
            $tags['CATEGORY_LABEL'] = $_SESSION['translate']->it('Category');
        }

        // vary title for new and edited listings
        if ($this->getId()) {
            $tags['EDIT_TITLE'] = $_SESSION['translate']->it('Edit RSS Feed');
        } else {
            $tags['EDIT_TITLE'] = $_SESSION['translate']->it('Add RSS Feed');
        }
        /* include help links also, to display help for each field to be completed */
        $tags['TITLE_LABEL'] = $_SESSION['translate']->it('Channel Title');
        $tags['TITLE_HELP'] = RSS_common::show_link('phpwsrssfeeds', 'rssTitle');
        $tags['CHANNEL_LABEL'] = $_SESSION['translate']->it('Channel URL');
        $tags['CHANNEL_HELP'] = RSS_common::show_link('phpwsrssfeeds', 'rssChannel');
        $tags['FOOTER_LABEL'] = $_SESSION['translate']->it('Footer');
        $tags['FOOTER_HELP'] = RSS_common::show_link('phpwsrssfeeds', 'rssFooter');
        $tags['EXPIRE_LABEL'] = $_SESSION['translate']->it('Expiration');
        $tags['EXPIRE_HELP'] = RSS_common::show_link('phpwsrssfeeds', 'rssExpire');
        $tags['MAX_ITEMS_LABEL'] = $_SESSION['translate']->it('Max Number Items');
        $tags['MAX_ITEMS_HELP'] = RSS_common::show_link('phpwsrssfeeds', 'rssMaxItems');
        $tags['SHOW_TITLE_LABEL'] = $_SESSION['translate']->it('Show Channel Title');
        $tags['SHOW_TITLE_HELP'] = RSS_common::show_link('phpwsrssfeeds', 'showTitle');
        $tags['SHOW_IMAGE_LABEL'] = $_SESSION['translate']->it('Show Channel Image');
        $tags['SHOW_IMAGE_HELP'] = RSS_common::show_link('phpwsrssfeeds', 'showImage');
        $tags['SHOW_URL_LABEL'] = $_SESSION['translate']->it('Show Channel Title as Link');
        $tags['SHOW_URL_HELP'] = RSS_common::show_link('phpwsrssfeeds', 'showURL');
        $tags['SHOW_DESC_LABEL'] = $_SESSION['translate']->it('Show Channel Description');
        $tags['SHOW_DESC_HELP'] = RSS_common::show_link('phpwsrssfeeds', 'showDesc');
        $tags['SHOW_DETAILS_HELP'] = RSS_common::show_link('phpwsrssfeeds', 'showDetails');
        $tags['SHOW_DETAILS_LABEL'] = $_SESSION['translate']->it('Show Channel Details Link');
        $tags['HOME_LABEL'] = $_SESSION['translate']->it('Show on Home Page');
        $tags['HOME_HELP'] = RSS_common::show_link('phpwsrssfeeds', 'showHome');
        $tags['BLOCK_LABEL'] = $_SESSION['translate']->it('Show in Block');
        $tags['BLOCK_HELP'] = RSS_common::show_link('phpwsrssfeeds', 'showBlock');
        $tags['ALLOW_VIEW_LABEL'] = $_SESSION['translate']->it('Allow Block View');
        $tags['ALLOW_VIEW_HELP'] = RSS_common::show_link('phpwsrssfeeds', 'allowView');
        $tags['PM_ALLOW_LABEL'] = $_SESSION['translate']->it('Allow Block View with Web Pages');
        $tags['PM_ALLOW_HELP'] = RSS_common::show_link('phpwsrssfeeds', 'pmView');
        $tags['TARGET_LABEL'] = $_SESSION['translate']->it('Target Window');
        $tags['TARGET_HELP'] = RSS_common::show_link('phpwsrssfeeds', 'rssTarget');
        $tags['YES_LBL'] = $_SESSION['translate']->it('Yes');
        $tags['NO_LBL'] = $_SESSION['translate']->it('No');
        $tags['ORGN_BY_LBL'] = $_SESSION['translate']->it('Organize Feed Items By');
        $tags['ORGN_BY_HELP'] = RSS_common::show_link('phpwsrssfeeds', 'orgnBy');
        $tags['SHOW_EXPIRED_LABEL'] = $_SESSION['translate']->it('Show Expired Items');
        $tags['SHOW_EXPIRED_HELP'] = RSS_common::show_link('phpwsrssfeeds', 'rssShowExpired');
        $tags['DAYS_AHEAD_HELP'] = RSS_common::show_link('phpwsrssfeeds', 'daysAhead');
        $tags['DAYS_AHEAD_LBL'] = $_SESSION['translate']->it('Days Ahead');

        $tags['EMPTY_FEED_MSG_HELP'] = RSS_common::show_link('phpwsrssfeeds', 'emptyFeed');
        $tags['EMPTY_FEED_MSG_LBL'] = $_SESSION['translate']->it("Empty Feed Message");

        if (isset($this->_id) && is_numeric($this->_id)) {
            $_SESSION['OBJ_menuman']->add_module_item('phpwsrssfeeds',
                '&amp;RSS_MAN_op=view&amp;PHPWS_MAN_ITEMS[]=' . $this->_id,
                './index.php?module=phpwsrssfeeds&amp;PHPWS_MAN_OP=edit&amp;PHPWS_MAN_ITEMS[]=' . $this->_id, 1);
        }

        $content = PHPWS_Template::processTemplate($tags, 'phpwsrssfeeds', 'edit.tpl');

        return $content;
    } //END function _edit

    /**
     * Asks for confirmation and, getting it, deletes the current item
     *
     * @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
     */
    function _delete($id) {
        if ($id == NULL)
            return FALSE;

        $this->RSS($id);
        //Standard error checking
        if (is_object($this->_error))
            return FALSE;

        $id = $this->getId();
        // have confirmation; remove category listing while we know the id
        $_SESSION['OBJ_fatcat']->deleteModuleElements('phpwsrssfeeds', $this->getLabel(), $id);

        //remove layout boxes set up for this item
        if ($this->_block == 1)
            PHPWS_Layout::dropBox('CNT_phpwsrssfeeds_'.$id);
        if ($this->_home == 1)
            PHPWS_Layout::dropBox('CNT_phpwsrssfeeds_home_'.$id);

        // delete the current item
        $this->_error = $this->kill();
        if (is_object($this->_error))
            return FALSE;

        return ' '.$this->getLabel();

    } //END function _delete()

    /**
     * Displays current item
     *
     * @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
     */
    function _view($id = NULL, $multi_id = NULL) {
        if (!is_null($id))
            $this->RSS($id);

        $message = NULL;
        //Check to see if feed exists and is active
        if (is_object($this->_error)) {
            $title = $_SESSION['translate']->it('Sorry, that is not a working RSS feed.');
            if (DEBUG_MODE)
                $message = RSS_common::_continueContent($this->_error->getMessage(), 'list', $title, 'RSS_MAN_op');
            else
                $message = RSS_common::_continueContent($_SESSION['translate']->it('Return to Feeds List'), 'list', $title, 'RSS_MAN_op');
            return $message;
        }
        //Return message that the feed is currently hidden.
        if ($this->_hidden && !$_SESSION['OBJ_user']->isDeity()) {
            $title = $_SESSION['translate']->it('Sorry, this feed is currently hidden from public view.');
            $message .= RSS_common::_continueContent($_SESSION['translate']->it('Return to Feeds List'), 'list', $title, 'RSS_MAN_op');
            return $message;
        }
        /**
         *Check to see if cache is expired and update feed
         *If the feed is down, try again in specified interval
         *May add notification if feed is down over a period of x intervals
         */
        if ($this->expired())
            $_SESSION['PHPWSRSSFEEDS']['update'][$this->getId()] = $this->getId();
        //$update = $this->update();

	$tags = $this->_content;
        $tags["CONTENT"] = $this->getView("full");

        if ($multi_id)
            $tags['FEED_HIDE_DETAILS'] = PHPWS_Text::moduleLink($_SESSION['translate']->it('Hide Details'), 'phpwsrssfeeds', array('RSS_MULTI_MAN_op' => 'view', 'PHPWS_MAN_ITEMS[]' => $multi_id));
        // get a list of the categories for this feed from FatCat
        if ($GLOBALS['core']->moduleExists('fatcat')) {
            $category = $_SESSION['OBJ_fatcat']->fatcatLinks($this->getId(), 'phpwsrssfeeds', $this->getLabel());
            if (!is_null($category)) {
                $tags['CATEGORY'] = $category;
                $tags['CATEGORY_LABEL'] = $_SESSION['translate']->it('Category');
            }
        }
        if ($_SESSION['OBJ_user']->allow_access('phpwsrssfeeds', 'edit'))
            $tags['EDIT'] = PHPWS_Text::moduleLink($_SESSION['translate']->it('Edit This Feed'), 'phpwsrssfeeds', array('RSS_MAN_op' => 'edit', 'PHPWS_MAN_ITEMS[]' => $this->_id));
        if ($multi_id)
            $content = PHPWS_Template::processTemplate($tags, 'phpwsrssfeeds', 'multi_rss/expanded_feed.tpl');
        else
            $content = PHPWS_Template::processTemplate($tags, "phpwsrssfeeds", "view.tpl");

        return $content;

    } //END function view

    function getView($mode) {
      $content = "";

      switch($mode) {
      case "full":
	if(is_array($this->_content['CONTENT'])) {
	  foreach($this->_content["CONTENT"] as $item) {	  
	    $content .= PHPWS_Template::processTemplate($item, "phpwsrssfeeds", "item.tpl");
	  }
	} else {
	  return $this->_content["CONTENT"];
	}
	break;
      case "block":
	if(is_array($this->_content['CONTENT'])) {
	  foreach($this->_content["CONTENT"] as $item) {	  
	    $content .= PHPWS_Template::processTemplate($item, "phpwsrssfeeds", "block_item.tpl");
	  }	
	} else {
	  return $this->_content['CONTENT'];
	}
	break;
      }
      return $content;
    }

    /**
     * Saves data for rss object to database
     *
     * @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
     */
    function _save() {
        if (isset($_POST['PHPWS_MAN_ITEMS'])
            && sizeof($_POST['PHPWS_MAN_ITEMS']) > 0 && is_array($_POST['PHPWS_MAN_ITEMS']))
        $this->RSS($_POST['PHPWS_MAN_ITEMS'][0]);

        //Standard error checking
        if (is_object($this->_error))
            return FALSE;

        $newrss = ($this->getId() == NULL);
        $error = FALSE;
        $message = array();

        // we MUST have a rss feed title, since this is the label for the data entry; errors if not
        if (!isset($_POST['TITLE']) || (trim($_POST['TITLE']) == '')) {
            $message['TITLE'] = $_SESSION['translate']->it('Please enter a RSS Feed title.');
            $error = TRUE;
        }

        //Check for users ignoring the http:// existing in the form field for channel
        if (!(preg_match('/^(http:\/\/)/i', $_POST['CHANNEL']) || preg_match('/^(https:\/\/)/i', $_POST['CHANNEL'])
          || preg_match('/^(file:\/\/)/i', $_POST['CHANNEL']))) {
            $message['CHANNEL'] = $_SESSION['translate']->it('Url must start with http:// or https:// (or file:// for a local file on your system)');
            $error = TRUE;
        }
        //Check to see a number has been entered in the expire column and if null reset to 60
        if (isset($_POST['EXPIRE']) && !is_numeric($_POST['EXPIRE'])) {
            $message['EXPIRE'] = $_SESSION['translate']->it('Number of minutes before cache expires must be a number.');
            $error = TRUE;
        }
        if (!isset($_POST['EXPIRE'])) {
            $_POST['EXPIRE'] = 60;
            $message['EXPIRE'] = $_SESSION['translate']->it('Expiration minutes must be set.');
            $error = TRUE;
        }
        //Check to see if a number has been entered into max items if number is 0 or NULL reset to 10
        if (isset($_POST['MAX_ITEMS']) && !is_numeric($_POST['MAX_ITEMS'])) {
            $message['MAX_ITEMS'] = $_SESSION['translate']->it('Max number of items must be a number.');
            $_POST['MAX_ITEMS'] = 10;
            $error = TRUE;
        }
        if (!isset($_POST['MAX_ITEMS']) || @$_POST['MAX_ITEMS'] == 0) {
            $_POST['MAX_ITEMS'] = 10;
            $message['MAX_ITEMS'] = $_SESSION['translate']->it('Max number of items must be set to a number 1 or greater.');
            $error = TRUE;
        }
        /* Note that parseInput called this way allows the global set of HTML
         * tags to be included. RSS_common::get_htmlentities() is also called to
         * encode html entities for other views where parseOutput isn't called.
         */
        $this->setLabel(RSS_common::get_htmlentities(PHPWS_Text::parseInput(@$_POST['TITLE'])));
        $this->_channel = RSS_common::get_htmlentities(@$_POST['CHANNEL']);
        $this->_footer = RSS_common::get_htmlentities(PHPWS_Text::parseInput(@$_POST['FOOTER']));
        $this->_show_title = trim(@$_POST['SHOW_TITLE']);
        $this->_show_image = trim(@$_POST['SHOW_IMAGE']);
        $this->_show_url = trim(@$_POST['SHOW_URL']);
        $this->_show_desc = trim(@$_POST['SHOW_DESC']);
        $this->_show_details = trim(@$_POST['SHOW_DETAILS']);
        $current_home = $this->_home;
        $this->_home = trim(@$_POST['HOME']);
        //saves current status of view block in $current_block
        $current_block = $this->_block;
        $this->_block = trim(@$_POST['BLOCK']);
        $this->_target = trim(@$_POST['TARGET']);
        $this->_expire = @$_POST['EXPIRE'];
        $this->_cached = $_POST['CACHED'];
        $this->_max_items = @$_POST['MAX_ITEMS'];
        $this->_allow_view = @$_POST['ALLOW_VIEW'];
        $this->_pm_allow = @$_POST['PM_ALLOW'];
        $this->_asc_desc = @$_POST['ASC_DESC'];
        $this->_orgn_by = @$_POST['ORGN_BY'];
        $this->_show_expired = @$_POST['SHOW_EXPIRED'];
        $this->_days_ahead = @$_POST['DAYS_AHEAD'];

	if($this->_orgn_by != 'Date') {
	  $this->_days_ahead = 'Any';
	}
	
	if(empty($_POST["EMPTY_FEED_MSG"])) {
	  $this->_empty_feed_msg = $_SESSION["translate"]->it("Feed Contains No Content");
	} else {
	  $this->_empty_feed_msg = @$_POST["EMPTY_FEED_MSG"];
	}

    //Check if allow_url_fopen is On
    if (preg_match('/^(http:\/\/)/i', $_POST['CHANNEL']) || preg_match('/^(https:\/\/)/i', $_POST['CHANNEL'])) {
        if (ini_get("allow_url_fopen") == 0) {
            $error = TRUE;
            $message =  $_SESSION['translate']->it('Your PHP configuration value
              "allow_url_fopen" is set to "Off" before any feeds will work, you 
              need to change the value of "allow_url_fopen" to On in php.ini. If 
              you do not understand this message, please contact your system 
              administrator for assistance. Note:  This setting can only be set in 
              php.ini due to security reasons.');
        }
    }
        if (!$error) {
            $content = $this->getContent();

            if (!$content) {
                $message = $_SESSION['translate']->it('Sorry, that is not a working RSS feed.');
                $error = TRUE;
            } else {
                $this->_content = $content;
                if (isset($content['description']))
                $this->channel_desc = $content['description'];
                $this->_content = $this->buildView();
            }
        }

        if ($error) {
            $title = $_SESSION['translate']->it('Error');
            $content = RSS_common::_errorMessage($message, $title);
            $content .= $this->_edit();
        } else {
            // save the feed
            $this->commit();
            $_id = $this->getId();
            // save the category
            if (isset($_POST['fatcatProcess'])) {
                require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/RSS_Fatcat.php');
                $element_link = 'index.php?module=phpwsrssfeeds&amp;RSS_MAN_op=view&amp;PHPWS_MAN_ITEMS=' . $_id;
                RSS_Fatcat::saveSelect($this->getLabel(), $element_link, $_id);
            }
             
            //set the side block in layout
            if ($newrss && $this->_block == 1) {
                $_SESSION['OBJ_layout']->create_temp('phpwsrssfeeds', 'CNT_phpwsrssfeeds_'.$_id, 'right_col_mid');
                unset($_SESSION['PHPWSRSSFEEDS']['allow_view']);
            }
             
            if ($current_block == 0 && $this->_block == 1) {
                $_SESSION['OBJ_layout']->create_temp('phpwsrssfeeds', 'CNT_phpwsrssfeeds_'.$_id, 'right_col_mid');
                unset($_SESSION['PHPWSRSSFEEDS']['allow_view']);
            }
             
            if ($current_block == 1 && $this->_block == 0) {
                PHPWS_Layout::dropBox('CNT_phpwsrssfeeds_'.$_id);
                unset($_SESSION['PHPWSRSSFEEDS']['allow_view']);
            }
             
            //set the main item in layout
            if ($newrss && $this->_home == 1) {
                $_SESSION['OBJ_layout']->create_temp('phpwsrssfeeds', 'CNT_phpwsrssfeeds_home_'.$_id, 'body');
                unset($_SESSION['PHPWSRSSFEEDS']['allow_view']);
            }
            if ($current_home == 0 && $this->_home == 1) {
                $_SESSION['OBJ_layout']->create_temp('phpwsrssfeeds', 'CNT_phpwsrssfeeds_home_'.$_id, 'body');
                unset($_SESSION['PHPWSRSSFEEDS']['allow_view']);
            }
            if ($current_home == 1 && $this->_home == 0) {
                PHPWS_Layout::dropBox('CNT_phpwsrssfeeds_home_'.$_id);
                unset($_SESSION['PHPWSRSSFEEDS']['allow_view']);
            }
             
            // tell user data is saved or updated
            if ($newrss) {
                $title = $_SESSION['translate']->it('RSS Feed saved. ');
                $message = PHPWS_Text::moduleLink($_SESSION['translate']->it('Edit This Feed'), 'phpwsrssfeeds', array('RSS_MAN_op' => 'edit', 'PHPWS_MAN_ITEMS[]' => $this->_id));
            } else {
                $title = $_SESSION['translate']->it('RSS Feed updated. ');
                $message = PHPWS_Text::moduleLink($_SESSION['translate']->it('Edit This Feed'), 'phpwsrssfeeds', array('RSS_MAN_op' => 'edit', 'PHPWS_MAN_ITEMS[]' => $this->_id));
            }
            $content = RSS_common::_message($message, $title);
            $content .= $this->_view();
        }

        // set content display
        return $content;
    } //END function _save

    function _list() {
        require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/RSSList.php');
        $content = FALSE;
        $settings = array('_module' => 'phpwsrssfeeds',
            '_table' => 'mod_phpwsrssfeeds_feeds',
            '_request' => 'RSS_MAN_op');
        $listObj = RSSList::getRSSList($settings);
        $listObj->init();
        if ($_SESSION['OBJ_user']->allow_access('phpwsrssfeeds'))
            $content = $listObj->getList('admin', $_SESSION['translate']->it('RSS News Feeds'));
        else
            $content = $listObj->getList('user', $_SESSION['translate']->it('RSS News Feeds'));

        return $content;
    }//End function _list()

    function _hide($id = NULL) {
        require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/RSSList.php');
        if (is_null($id))
            return FALSE;
        $settings = array('_module' => 'phpwsrssfeeds',
            '_table' => 'mod_phpwsrssfeeds_feeds');
        $listObj = RSSList::getRSSList($settings);
        $hide = $listObj->_doMassUpdate('hidden', 1);
        if ($hide == FALSE)
            return FALSE;
        return $_SESSION['translate']->it('Items updated.');
    }//End fuction _hide()

    function _show($id = NULL) {
        require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/RSSList.php');
        if (is_null($id))
            return FALSE;
        $settings = array('_module' => 'phpwsrssfeeds',
            '_table' => 'mod_phpwsrssfeeds_feeds');
        $listObj = RSSList::getRSSList($settings);
        $show = $listObj->_doMassUpdate('hidden', 0);
        if ($show == FALSE)
            return FALSE;
        return $_SESSION['translate']->it('Items updated.');
    }//End fuction _show()
     
    /**
     * Gets raw contents of the RSS Feed...if it fails, return FALSE
     *
     * @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
     */
    function getContent() {
        //Add PEAR RSS Class
        require_once ('XML/RSS.php');
        //Get RSS Channel info an structure
        if (preg_match('/^(file:\/\/)/i', $this->_channel))
            $channel = preg_replace('/^(file:\/\/)/i', '', $this->_channel);
        else
            $channel = $this->_channel;
        $rss = & new XML_RSS($channel);
        
        $additionalTags = array('UPDATED', 'POSTED', 'DATETEXT');
        $rss->parse();
        $info = $rss->getChannelInfo();
        $structure = @$rss->getStructure();

        //Check to see if we got anything
        if (sizeof(array_merge($info, $structure)) == '0')
            return FALSE;
        //Now we'll parse the new input for problems
        $info = $this->clean($info);
        $feed = array();
        $num = NULL;

        // get first real item index
        for($i=0; $i < count($structure); $i++) {
            if($structure[$i]['type'] == 'item') 
                $num = $i;
        }

        //Sort default DESC
        if($this->_asc_desc == 'DESC' && $this->_orgn_by == 'Default'){
            $structure = array_reverse($structure);
        }

        //Sort alpha asc or desc
        if($this->_orgn_by == 'Alpha' && $this->_asc_desc == 'ASC') {
            usort($structure, array("RSS","strnatcasecmp"));
        }

        if($this->_orgn_by == 'Alpha' && $this->_asc_desc == 'DESC') {
            usort($structure, array("RSS","strnatcasecmp"));
            $structure = array_reverse($structure);
        }

        if($this->_orgn_by == 'Date' && (isset($structure[$num]['pubdate']) 
          || isset($structure[$num]['dc:date']))) {
            $extra = array();
            $timeTS = NULL;
            $timstamp_indexed = NULL;
            foreach ($structure as $key => $item){
                if($item['type'] !== 'item'){
                    $extra[$key] = $item;
                    continue;
                }

                if(isset($item['pubdate'])) {
                    $timeTS = RSS_Common::getTS($item['pubdate'], 'pubdate', FALSE);
                } else if(isset($item['dc:date'])) {
                    $timeTS = RSS_Common::getTS($item['dc:date'], 'dc:date', FALSE);
                } else {
                    continue;
                }

                if(is_array($timstamp_indexed)) {
                    while(array_key_exists($timeTS, $timstamp_indexed)) {
                        $timeTS += 1; // keep adding seconds tell unique
                    }
                }

                if($this->_days_ahead !== 'Any' 
                  || ($this->_days_ahead == 'Any' && !$this->_show_expired)){
                    $today_arr = explode(" ", date("n j Y"));
                    $today = date_calc::dateToDays($today_arr[1],$today_arr[0],$today_arr[2]);
                    $item_date_arr = explode(" ", date("n j Y", $timeTS));
                    $item_date = date_calc::dateToDays($item_date_arr[1],$item_date_arr[0],$item_date_arr[2]);

                    if(!$this->_show_expired && $item_date - $today < 0) {
                        continue;
                    }

                    if($this->_days_ahead == 0 && $today == $item_date) {
                        $timstamp_indexed[$timeTS] = $item;
                        continue;
                    }

                    if(($item_date - $today) <= $this->_days_ahead) {
                        $timstamp_indexed[$timeTS] = $item;
                    }

                } else {
                    $timstamp_indexed[$timeTS] = $item;
                }
            }

            $structure = array();
            if(!empty($timstamp_indexed)) {
                if($this->_asc_desc == 'DESC')
                    krsort($timstamp_indexed);
                if($this->_asc_desc == 'ASC')
                    ksort($timstamp_indexed);
                $structure = array_merge($extra,$timstamp_indexed);
            }

        }

        $items = 0;
        foreach($structure as $key => $value) {
            if ($items > $this->_max_items)
                break;

            $item = $this->clean($value);
            if (!empty($item)) {
                $feed[$key] = $item;
                if($items["type"] == 'item')
                    $items++;
            }
        }	

        //print_r($feed);
        //Check for errors and pass on
        $rss_feed = array_merge($info, $feed);
        if (sizeof($rss_feed) == '0')
            return FALSE;
        return $rss_feed;
    }//end function_getContent

    function buildView() {
        $tags = array();
        $tags['RSS_VIEW'] = PHPWS_Text::moduleLink($_SESSION['translate']->it('More Feeds'), 'phpwsrssfeeds', array('RSS_MAN_op' => 'list'));
        if ($this->_show_title)
            $tags['TITLE'] = PHPWS_Text::parseOutput($this->getLabel());
        if ($this->_show_title && $this->_show_url) {
            $tags['TITLE_LINK'] = RSS_common::get_htmlentities($this->_content['link']);
            $tags['TITLE_TARGET'] = $this->_target;
            $tags['END_ANCHOR'] = '';
        }

        if (isset($this->_show_image) && isset($this->_content[0]) && 
          $this->_content[0]['type'] == 'image') {
            $num = NULL;
            //add support for RSS 1.0 Dublin Core
            //add support for RSS 2.0 Garbage
            if (isset($this->_content[0]['url'])) /* this is a 2.0 feed */
                $num = 0;
            elseif(isset($this->_content[1]['url']))
                $num = 1;
            elseif(isset($this->_content[2]['url']))
                $num = 2;
            if ($num !== NULL) {
	      if($this->_show_image) {
                $tags['CHANNEL_IMG_URL'] = RSS_common::get_htmlentities($this->_content[$num]['link']);
                $tags['CHANNEL_IMG_TARGET'] = $this->_target;
                $tags['CHANNEL_IMAGE'] = RSS_common::get_htmlentities($this->_content[$num]['url']);
                $tags['CHANNEL_IMG_ALT'] = $this->_content[$num]['title'];
	      }
            }
        }

        if ($this->_show_desc)
            $tags['CHANNEL_DESC'] = $this->_content['description'];

        if ($this->_footer)
            $tags['CHANNEL_FOOTER'] = PHPWS_Text::parseOutput($this->_footer);
        $item_count = 0;
        $items = '';

	$allItems = array();
	foreach($this->_content as $item) {
	  if ($item_count == $this->_max_items)
	    break;
	  
	  if ($item['type'] == 'item') {
	    $next = array();
	    if($date = $this->getDate($item)) {
	      $next['DATE_TS']  = $date['ts'];
	      $next['DATE_ISO'] = $date['iso'];
	    } 

	    $next['ITEM_TITLE'] = @$item['title'];
	    $next['ITEM_LINK']  = RSS_common::get_htmlentities(@$item['link']);
	    if (isset($item['description']))
	      $next['ITEM_DESC'] = @$item['description'];
	    elseif(isset($item['content:encoded']))
	      $next['ITEM_DESC'] = $item['content:encoded'];
	    else
	      $next['ITEM_DESC'] = "";
	    
	    $next['TARGET'] = $this->_target;

	    $item_count++;
	    $allItems[] = $next;
	  }
	}

	if(!empty($allItems))
	  $tags['CONTENT'] = $allItems;
	else
	  $tags['CONTENT'] = $this->_empty_feed_msg;
        return $tags;

    } //END function buildView()

    function getDate($item) {
      // only supports dc_date at time
      $date = array();
      if(isset($item['dc:date'])) {
	
	$timeTS = RSS_Common::getTS($item["dc:date"], 'dc:date');
	//	echo "TIMESTAMP: $timeTS  M: " . $date[1] . " D: " . $date[2] . " Y: " . $date[0] . "<br />";
	$date["ts"]  = $timeTS;
	$date["iso"] = RSS_Common::iso8601_date($timeTS);
	return $date;

      } else if(isset($item['pubdate'])) {
	$timeTS = RSS_Common::getTS($item['pubdate'], 'pubdate');
	$date["ts"]  = $timeTS;
	$date["iso"] = RSS_Common::iso8601_date($timeTS);
	return $date;

      } else {
	return null;
      }
    }

    /**
     * Cleans feed array for input into database
     * Replaces lameBlogger() whch checked to see if input feed size was greater
     * than 65000 characters. I decided to let the database input fail if this
     * was the case. A check should be added to core/Item.php
     *
     * var array $input
     * @return array
     *
     * @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
     */

    function clean($input) {
        /*
         * Decode all encoded html entities so we can strip tags not allowed.
         */
        $cleaned = array();
        foreach($input as $key => $value) {
            //Decode encoded html
            $value = RSS_common::html_entity_decode($value, ENT_QUOTES, HTML_SPECIALCHARS);
            //Filter out unwanted tags and content
            $value = RSS_common::filterInput($value);
            if (!empty($value))
                $cleaned[$key] = $value;
        }
        return $cleaned;
    }//End function clean()
     
    /**
     * Sets up content for view in block
     *
     * @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
     */
    function showUserBox($multi_id = NULL, $item_id = NULL) {
        $confirm = FALSE;
        //Check to see if module allowed is current
        if (!$multi_id && is_array($this->_allow_view)) {
            $modules_allowed = RSS_common::get_modules_allowed();
            foreach($this->_allow_view as $num) {
                if (((isset($_REQUEST['module']) && ($_REQUEST['module'] == $modules_allowed[$num]))
                  || (!isset($_REQUEST['module']) && ('home' == $modules_allowed[$num]))))
                    $confirm = TRUE;
            }
        }

        //Check to see if pagemaster page allowed is current
        if (!$multi_id && !$confirm) {
            if (is_array($this->_pm_allow)) {
                if (isset($_REQUEST['module']) && !$_REQUEST['module'] == 'pagemaster')
                    return;

                foreach($this->_pm_allow as $num) {
                    if (isset($_REQUEST['PAGE_id']) && $_REQUEST['PAGE_id'] == $num)
                        $confirm = TRUE;

                    if (isset($_SESSION['SES_PM_page']) && is_object($_SESSION['SES_PM_page']) && is_a($_SESSION['SES_PM_page'], 'PHPWS_page') && $_SESSION['SES_PM_page']->id == $num) {
                        $confirm = TRUE;
                    }
                }
                if (!$confirm)
                    return;
            } else {
                return;
            }
        }
        /**
         *Check to see if cache is expired and update feed
         *If the feed is down, try again in specified interval
         *May add notification if feed is down over a period of x intervals
         */
        if ($this->expired()) {
            $_SESSION['PHPWSRSSFEEDS']['update'][$this->getId()] = $this->getId();
            //$this->update();
        }

        $tags = $this->_content;
        $tags['CONTENT'] = $this->getView("block");

        if ($_SESSION['OBJ_user']->allow_access('phpwsrssfeeds', 'edit'))
            $tags['EDIT'] = PHPWS_Text::moduleLink($_SESSION['translate']->it('Edit This Feed'), 'phpwsrssfeeds', array('RSS_MAN_op' => 'edit', 'PHPWS_MAN_ITEMS[]' => $this->_id));

        if ($this->_show_details == 1 && !$multi_id)
            $tags['VIEW_DETAILS'] = PHPWS_Text::moduleLink($_SESSION['translate']->it('View Details'), 'phpwsrssfeeds', array('RSS_MAN_op' => 'view', 'PHPWS_MAN_ITEMS[]' => $this->_id));
        elseif($this->_show_details == 1 && $multi_id && !is_null($item_id))
            $tags['VIEW_DETAILS'] = PHPWS_Text::moduleLink(
              $_SESSION['translate']->it('View Details'), 'phpwsrssfeeds', 
              array('RSS_MULTI_MAN_op' => 'view', 'PHPWS_MAN_ITEMS[]' => $multi_id, 
              'RSS_multi_expand' => $item_id));
        elseif($this->_show_details == 1 && $multi_id && is_null($item_id))
            $tags['VIEW_DETAILS'] = PHPWS_Text::moduleLink(
              $_SESSION['translate']->it('View Details'), 'phpwsrssfeeds', 
              array('RSS_MULTI_MAN_op' => 'view', 'PHPWS_MAN_ITEMS[]' => $multi_id, 
              'RSS_multi_expand' => 1));
         
        $content = PHPWS_Template::processTemplate($tags, 'phpwsrssfeeds', 'rss_block.tpl');
        if (!$multi_id)
            $content_var = 'CNT_phpwsrssfeeds_' . $this->getId();
        else
            $content_var = 'CNT_phpwsrssfeeds_multi_' . $multi_id;

        $GLOBALS[$content_var]['content'] = $content;
        return true;
    } //END function showUserBox

    function showHomeItems() {
        $content = $this->_view();
        $content_var = 'CNT_phpwsrssfeeds_home_' . $this->getId();
        $GLOBALS[$content_var]['content'] = $content;
        return true;
    }     
     
    /**
     * Returns return boolean TRUE if feed is expired.
     *
     * @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
     */
    function expired() {
        $date = $this->_cached;
        //$_POST data substr doesn't count the space :(
        $len = strlen($date);
        $month = substr($date, 5, 2);
        $day = substr($date, 8, 2);
        $year = substr($date, 0, 4);
        if ($len == 19) {
            $hour = substr($date, 11, 2);
            $min = substr($date, 14, 2);
            $sec = substr($date, 17, 2);
        } else {
            $hour = substr($date, 10, 2);
            $min = substr($date, 13, 2);
            $sec = substr($date, 16, 2);
        }
        $epoch = mktime($hour, $min, $sec, $month, $day, $year);
        $exp = mktime() - $epoch;
        $exp = floor($exp / 60);
        if ($exp >= $this->_expire)
            return TRUE;
        else
            return FALSE;
    }//END function expired

    
    /**
     * Get a list of pagemaster pages
     *
     * refreshes the list of available pagemaster pages
     */
    function getPMOptions($type=NULL) {
        if (is_null($type))
            $type = 'Block';
        $pageOptions = array();
        $text = $_SESSION['translate']->it("View $type with Web Pages:");
        $pageOptions['label'] = $text;
        $pageOptions['line'] = '----------------------------------------------';

        $sql = 'SELECT id, title FROM '. $GLOBALS['core']->tbl_prefix .'mod_pagemaster_pages';
        $page_result = $GLOBALS['core']->query($sql);
        while ($page = $page_result->fetchrow(DB_FETCHMODE_ASSOC)) {
            $pageOptions[$page['id']] = $page['title'];
        }

        return $pageOptions;
    }//END getPMOptions

    /**
     * Function called when feed is expired
     *
     */
    function update($ids) {
        if (isset($ids)) {
            ignore_user_abort(1);
            set_time_limit(15);
            foreach ($ids as $id) {
                $this->RSS($id);
                $feed = $this->getContent();
                //put logger here
                if (!$feed)
                    continue;
                $this->_cached = date('Y-m-d G:i:s');
                $this->_content = NULL;
                //fix for some problem in Item.php that doesn't do add slashes when I use this method
                $commitValues = get_object_vars($this);
                if (is_array($commitValues)) {
                    if (is_array($this->_exclude)) {
                        foreach($this->_exclude as $value) {
                            unset($commitValues[$value]);
                        }
                    }

                    foreach($commitValues as $key => $value) {
                        if (is_array($commitValues[$key])) {
                            $this->$key = serialize($commitValues[$key]);
                        } else {
                            $this->$key = $commitValues[$key];
                        }
                    }
                }
                //if feed has been updated, update var
                $this->_content = $feed;
                $this->channel_desc = $this->_content['description'];          
                $this->_content = $this->buildView();
                // save the feed
                $this->commit();
            }
        }
    }// function update()

    function update_server() {
        if(!isset($_SESSION['PHPWSRSSFEEDS']['update']) || isset($_POST['lay_quiet']))
            return;

        $posts = array ( 'module' => 'phpwsrssfeeds',
                         'RSS_MAN_op' => 'update',
                         'lay_quiet' => 1);
        $postValues = '';
        foreach( $posts AS $name => $value )
            $postValues .= urlencode( $name ) . "=" . urlencode( $value ) . '&';

        foreach($_SESSION['PHPWSRSSFEEDS']['update'] as $id)
            $postValues .= urlencode( 'UPDATE_ID[]' ) . "=" . urlencode( $id ) . '&';

        $postValues = substr( $postValues, 0, -1 );

        $agent = $_SERVER["HTTP_USER_AGENT"];
        $host = rtrim($GLOBALS['core']->home_http,'/');

        $request  = "POST /index.php HTTP/1.1\r\n";
        $request .= "Host: $host\r\n";
        $request .= "User-Agent: $agent\r\n";

        $len = strlen( $postValues );
        $request .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $request .= "Content-Length: $len\r\n";
        $request .= "\r\n";
        $request .= $postValues;

        if($socket  = @fsockopen( $host, 80, $errno, $errstr, 2 )) {
            fputs( $socket, $request );
            stream_set_blocking ($socket, 0);
            fclose( $socket );
        } else {
            require_once(PHPWS_SOURCE_DIR . 'mod/phpwsrssfeeds/class/RSSManager.php');
            $postArr = explode('&', $postValues);
            $ids = array();

            foreach($postArr as $post) {
                $opVal = explode('=', $post);	      
                if(stristr($opVal[0], 'UPDATE_ID')) {
                    $ids[] = $opVal[1];
                }
            }

            $manObj = RSS_Manager::getRSS_Manager('RSS');
            $manObj->updateReqFeeds($ids);
        }
    }//End function update()
    
    function strnatcasecmp($a, $b) {
       return strnatcasecmp($a['title'], $b['title']);
    }

    function getNoContentMsg() {
      return $this->_empty_feed_msg;
    }
}//End class RSS()
?>
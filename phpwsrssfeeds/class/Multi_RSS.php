<?php
 
/**
* Multi_RSS.php
* Defines Multi_RSS class for RSS Feeds module
*
* This class gives the ability to show multiple rss feeds on a single
* page.  It includes options to expand the items in the feed to show the
* detail.
*
* @version $Id: Multi_RSS.php,v 1.33 2005/03/14 19:34:08 wendall911 Exp $
* @author Darren Greene <dg49379@NOSPAM.users.sourceforge.net>
* @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
* @package phpwsrssfeeds
*/
require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/RSS.php');
 
class Multi_RSS extends PHPWS_Item {
    /**
    * Which RSS feeds in the Multi-View mode
    * @var array
    */
    var $_show_in_multiview;
     
    /**
    * maximum number of items for display in multiview mode
    * @var string
    */
    var $_max_multi_items;
     
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
    * list of modules needing to be active for feed to appear
    * @var array
    */
    var $allow_view;
     
    /**
    * list of modules needing to be active for feed to appear
    * @var array
    */
    var $pm_allow;
     
    /**
    * parsed
    * @var array
    */
    var $_content;
     
    /**
    * Multi_RSS variables
    * @var string
    */
    var $_availFeeds;
    var $_show_empty_feeds;
    var $_show_anchor_links;
    var $_show_cat_link;
    var $_sort_order;
    var $_expandedFeed;
    var $_error;
     
    function Multi_RSS($multi_id = NULL) {
        if (is_null($multi_id)) {
            $this->_show_in_multiview = array();
            $this->_max_multi_items = '10';
            $this->_home = 0;
            $this->_block = 0;
            $this->allow_view = array();
            $this->pm_allow = array();
            $this->_availFeeds = NULL;
            $this->_expandedFeed = NULL;
            $this->_content = NULL;
            $this->_error = NULL;
            $this->_show_empty_feeds = 1;
            $this->_sort_order = 0;
        }
         
        /* These vars are excluded on commit() */
        $exclude = array("_availFeeds", "_expandedFeed", "_content", "_error");
        $this->addExclude($exclude);
         
        /* point to the Multi_RSS data table */
        $this->setTable("mod_phpwsrssfeeds_multi");
         
        /* if the item exists, initialize its variables from the database */
        if (is_numeric($multi_id)) {
            /* setId returns a PHPWS_Error object */
            $this->_error = $this->setId($multi_id);
            if (!is_object($this->_error)) {
                /* initialize variables */
                $this->_error = $this->init($multi_id);
            }
        } elseif(is_array($multi_id)) {
            $this->_error = $this->init($multi_id);
        }
    }//End function Multi_RSS()
     
    function getMulti_RSS($settings = NULL) {
        $instance = new Multi_RSS($settings);
        return $instance;
    }//End function getRSS()
     
    function getAvailFeeds() {
        if ($this->_hidden || !$this->_approved)
            return FALSE;
         
        $feed_list = implode(" OR id = ", $this->_show_in_multiview);
        $sql = "SELECT id FROM mod_phpwsrssfeeds_feeds WHERE id = ".$feed_list;
         
        if (!isset($this->_sort_order) || $this->_sort_order == FALSE)
            $sql .= " ORDER BY label";
         
        $feeds = $GLOBALS["core"]->getAllAssoc($sql, TRUE);
         
        if (isset($this->_sort_order) && $this->_sort_order == TRUE) {
            $customSorted = array();
            foreach($this->_show_in_multiview as $feed) {
                foreach($feeds as $availFeeds) {
                    if ($feed == $availFeeds["id"]) {
                        $customSorted[] = array("id" => $feed);
                    }
                }
            }
            $this->_availFeeds = $customSorted;
        } else {
             
            $this->_availFeeds = $feeds;
        }
    }//End function getAvailFeeds()
     
    function getFeedLinks() {
        $links = "";
        if ($this->_availFeeds) {
            foreach($this->_availFeeds as $feed) {
                $feedObj = RSS::getRSS($feed["id"]);
                $tags = $feedObj->_content;
                if ($this->_show_empty_feeds == 0 && empty($tags["MULTI_CONTENT"]))
                    continue;
                if ($feedObj->_hidden == 0 && $feedObj->_approved == 1) {
                    $tags["FEED_LINK"] = htmlspecialchars($_SERVER["REQUEST_URI"])
                    ."#".RSS_common::get_htmlentities($feedObj->_label);
                    $tags["FEED_LINK_TITLE"] = RSS_common::get_htmlentities($feedObj->_label);
                    $tags["SEPERATOR"] = " | ";
                    $links .= PHPWS_Template::processTemplate($tags, "phpwsrssfeeds", "multi_rss/menu_avail_feeds.tpl");
                }
            }
        }
         
        if ($links == "")
            return FALSE;
         
        $links = rtrim($links);
        $links = rtrim($links, "|");
            return $links;
    }//End function getFeedLinks()

    function getUnexpandedFeeds($id) {
        $item_count = 0;
        $show_feeds = 0;
        $feeds = NULL;
         
        foreach($this->_availFeeds as $feed) {
            if ($id == $feed["id"])
                continue;
            $feedObj = RSS::getRSS($feed["id"]);
             
            //Standard error checking
            if (is_object($feedObj->_error))
                continue;
             
            if ($feedObj->_hidden || !$feedObj->_approved)
                continue;
             
            if ($feedObj->expired())
            $_SESSION['PHPWSRSSFEEDS']['update'][$feedObj->getId()] = $feedObj->getId();
            //$feedObj->update();
             
            $tags = $feedObj->_content;

            if ($this->_show_empty_feeds == 0 && empty($tags["CONTENT"]))
                continue;
             
            $item_count = 0;
            $items = "";
             
            if (is_array($tags["CONTENT"]) && !empty($tags["CONTENT"])) {
                foreach($tags["CONTENT"] as $item) {
		  if ($item_count == $this->_max_multi_items)
		    break;
		  $items .= PHPWS_Template::processTemplate($item, "phpwsrssfeeds", "multi_rss/unexpanded_feed_item.tpl");
		  $item_count++;
                }
                 
                $tags["FEED_LINKS"] = $items;
		unset($tags["CONTENT"]);
            } else {
	      $tags["FEED_MSG"] = $feedObj->getNoContentMsg();
            }
             
            $tags["BOOKMARK"] = "<a name=\"".$feedObj->getLabel()."\"></a>";
             
             
            if ($feedObj->_show_details == 1)
                $tags["EXPAND_FEED"] = PHPWS_Text::moduleLink($_SESSION["translate"]->it("View Details"), "phpwsrssfeeds", array("PHPWS_MAN_ITEMS%5B%5D" => $this->_id, "RSS_MULTI_MAN_op" => "view", "RSS_multi_expand" => $feed["id"]));
            else
                $tags["EXPAND_FEED"] = NULL;
             
            if ($feedObj->_footer && isset($feedObj->_footer))
                $tags["CHANNEL_FOOTER"] = PHPWS_Text::parseOutput($feedObj->_footer);
             
            if ($_SESSION['OBJ_user']->allow_access("phpwsrssfeeds", "edit"))
                $tags["EDIT"] = PHPWS_Text::moduleLink($_SESSION["translate"]->it("Edit This Feed"), "phpwsrssfeeds", array("RSS_MAN_op" => "edit", "PHPWS_MAN_ITEMS[]" => $feedObj->_id));
            $feeds .= PHPWS_Template::processTemplate($tags, "phpwsrssfeeds", "multi_rss/unexpanded_feed.tpl");

            if ($show_feeds % 2 == 1) {
                $clear["COMMENT"] = "<!-- Clear styles -->";
                $feeds .= PHPWS_Template::processTemplate($clear, "phpwsrssfeeds", "multi_rss/clear.tpl");
            }
             
            $show_feeds++;
        }
         
        $uTags["FEEDS"] = $feeds;
        return PHPWS_Template::processTemplate($uTags, "phpwsrssfeeds", "multi_rss/unexpanded_feed_ALL.tpl");
    }//End function getUnexpandedFeeds()
     
    function _view($id = NULL) {
        if (!is_null($id))
	  $this->Multi_RSS($id);

        //error checking
        if (is_object($this->_error)) {
            $title = $_SESSION['translate']->it("Sorry, that is not a working Multi-RSS feed.");
            if (DEBUG_MODE)
                $message = RSS_common::_continueContent($this->_error->getMessage(), "list", "RSS_MULTI_MAN_op", $title);
            else
                $message = RSS_common::_continueContent($_SESSION['translate']->it("Return to Multi-RSS Feeds List"), "list", "RSS_MULTI_MAN_op", $title);
            return $message;
        }
        $tags["FEED_LIST"] = PHPWS_Text::moduleLink($_SESSION["translate"]->it("More Feeds"), "phpwsrssfeeds", array("RSS_MAN_op" => "list"));
        if ($GLOBALS['core']->moduleExists("fatcat") && $this->_show_cat_link) {
            $tags["CATEGORY_LABEL"] = $_SESSION["translate"]->it("Category");
            $tags["CATEGORY"] = $_SESSION['OBJ_fatcat']->fatcatLinks($this->getId()+5000, "phpwsrssfeeds", PHPWS_Text::parseOutput($this->getLabel()));
        }
        $omit_item = '';
         
        $this->getAvailFeeds();
         
        if ($this->_show_anchor_links) {
            $tags["FEED_ITEMS"] = $this->getFeedLinks();
        }
         
        if (isset($_REQUEST["RSS_multi_expand"])) {
            $exfeedObj = new RSS($_REQUEST["RSS_multi_expand"]);
            $exfeedObj->init();
            $omit_item = $_REQUEST["RSS_multi_expand"];
            $tags["EXPANDED_FEED"] = $exfeedObj->_view($omit_item, $this->getId());
        }
         
        $tags["UNEXPANDED_FEEDS"] = $this->getUnexpandedFeeds($omit_item);
         
        if(empty($tags["EXPANDED_FEED"]) && empty($tags["UNEXPANDED_FEEDS"])) {
            $tags["FEED_ITEMS"] = $_SESSION["translate"]->it("Feed currently unavailable");
        }
         
        if ($_SESSION['OBJ_user']->allow_access("phpwsrssfeeds", "multi_edit"))
            $tags["EDIT"] = PHPWS_Text::moduleLink($_SESSION["translate"]->it("Edit This Multi-View Feed"), "phpwsrssfeeds", array("RSS_MULTI_MAN_op" => "edit", "PHPWS_MAN_ITEMS[]" => $this->_id));
         
        return PHPWS_Template::processTemplate($tags, "phpwsrssfeeds", "multi_rss/view.tpl");
    }//End function _view()
     
    function _sortMoveUp($upFeed) {
        $sorted = array();
         
        for($i = 0; $i < count($this->_show_in_multiview); $i++) {
            if ($this->_show_in_multiview[$i] == $upFeed && $i != 0)
                continue;
             
            if ($this->_show_in_multiview[$i+1] == $upFeed) {
                $sorted[] = $upFeed;
            }
             
            $sorted[] = $this->_show_in_multiview[$i];
        }
         
        $this->_show_in_multiview = $sorted;
        $this->_sort_order = 1;
        $this->commit();
    }//End function _sortMoveUp()
     
    function _sortMoveDown($downFeed) {
        $sorted = array();
         
        $skipped = false;
        for($i = 0; $i < count($this->_show_in_multiview); $i++) {
            if ($this->_show_in_multiview[$i] == $downFeed && $i != count($this->_show_in_multiview)-1) {
                $skipped = true;
                if (count($this->_show_in_multiview) > $i)
                    $sorted[] = $this->_show_in_multiview[$i + 1];
                else
                    $sorted[] = $downFeed;
                continue;
            }
             
            if ($skipped) {
                $sorted[] = $downFeed;
                $skipped = false;
            } else
                $sorted[] = $this->_show_in_multiview[$i];
        }
         
        $this->_show_in_multiview = $sorted;
        $this->_sort_order = 1;
        $this->commit();
    }//End function _sortMoveDown()
     
    function saveCustomSort($id) {
        if (!is_null($id)) {
            $this->Multi_RSS($id);
             
            if (isset($_REQUEST["sort_options"])) {
                if ($_REQUEST["sort_options"] == "custom") {
                    $this->_sort_order = 1;
                }
                else if($_REQUEST["sort_options"] == "alpha") {
                    $this->_sort_order = 0;
                }
                $this->commit();
                if (isset($_REQUEST["update_sort"]))
                    return $this->customSort($id);
                else if(isset($_REQUEST["edit"]))
                    return $this->_edit();
                else
                    return $this->_edit();
            }
        }
    }//End function saveCustomSort()
     
    function customSort() {
        if (!isset($this->_id))
        if (isset($_REQUEST["PHPWS_MAN_ITEMS"])
          && sizeof($_REQUEST["PHPWS_MAN_ITEMS"]) > 0 && is_array($_REQUEST["PHPWS_MAN_ITEMS"]))
            $this->Multi_RSS($_REQUEST["PHPWS_MAN_ITEMS"][0]);
        else
            return;
         
        $allFeeds = RSS_common::get_available_feeds();
         
        if (isset($_REQUEST["MOVE_UP"])) {
            $this->_sortMoveUp($_REQUEST["MOVE_UP"]);
        }
        else if (isset($_REQUEST["MOVE_DOWN"])) {
            $this->_sortMoveDown($_REQUEST["MOVE_DOWN"]);
        }
         
        $listFeedTags["FEED_LISTING"] = "";
        $feedCount = 0;
        foreach($this->_show_in_multiview as $feed) {
            $rowTags = array();
            $feedCount++;
            $rowTags["FEED_NAME"] = $allFeeds[$feed];
            if ($feedCount != 1)
                $rowTags["MOVE_UP_LINK"] = './index.php?module=phpwsrssfeeds&amp;RSS_MULTI_MAN_op=customSort&amp;PHPWS_MAN_ITEMS[]='.$this->_id.'&amp;MOVE_UP='.$feed;
             
            if ($feedCount !== count($this->_show_in_multiview)) {
                $rowTags["MOVE_DOWN_LINK"] = './index.php?module=phpwsrssfeeds&amp;RSS_MULTI_MAN_op=customSort&amp;PHPWS_MAN_ITEMS[]='.$this->_id.'&amp;MOVE_DOWN='.$feed;
            }
            $listFeedTags["FEED_LISTING"] .= PHPWS_Template::processTemplate($rowTags, "phpwsrssfeeds", "multi_rss/sort_ind_feed.tpl");
             
        }
         
        $listFeedTags["ORDER_HDR"] = $_SESSION["translate"]->it("Order");
        $listFeedTags["FEED_NAME_HDR"] = $_SESSION["translate"]->it("Feeds");
         
        $form = new EZform("multiview_sort");
        $form->add("module", "hidden", "phpwsrssfeeds");
        $form->add("RSS_MULTI_MAN_op", "hidden", "saveCustomSort");
        $form->add("PHPWS_MAN_ITEMS[]", "hidden", $this->_id);
        $form->add("sort_options", "radio", array("alpha", "custom"));
        if (isset($_REQUEST["edit"]))
            $form->add("edit", "hidden", true);
        $form->add("finished", "submit", $_SESSION["translate"]->it("Finished"));
         
        if (isset($this->_sort_order) && $this->_sort_order == 1)
            $form->setMatch("sort_options", "custom");
        else
            $form->setMatch("sort_options", "alpha");
         
        $form->add("update_sort", "submit", $_SESSION["translate"]->it("Update"));
        $tags = $form->getTemplate();
         
        $tags["TITLE"] = $_SESSION["translate"]->it("Multiview Ordering for: ") . $this->_label . ".";
        $tags["SORT_OPTION_ALPHA"] = $_SESSION["translate"]->it("Sort alphabetically by feed titles.");
        $tags["SORT_OPTION_CUSTOM"] = $_SESSION["translate"]->it("Specify a custom ordering of feeds.");
         
        $tags["FEED_LISTING"] = PHPWS_Template::processTemplate($listFeedTags, "phpwsrssfeeds", "multi_rss/sort_list.tpl");
        return PHPWS_Template::processTemplate($tags, "phpwsrssfeeds", "multi_rss/sort.tpl");
    }//End function customSort()
     
    /**
    * Edit a RSS object's data
    *
    * @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
    */
    function _edit($id = NULL) {
        if (!is_null($id))
            $this->Multi_RSS($id);
         
        //Standard error checking
        if (is_object($this->_error))
            return FALSE;
         
        //Check for available feeds, if none are available, return error
        $feeds = RSS_common::get_available_feeds();
        if ($feeds == FALSE)
            return RSS_common::_errorMessage(PHPWS_Text::moduleLink($_SESSION["translate"]->it("No Feeds Available"), "phpwsrssfeeds", array('RSS_MAN_op' => 'list')));
         
        $tags = array();
         
        // Set up the edit form
        $form = new EZform("MULTI_EDIT");
         
        $tab = 1;
         
        $form->add("TITLE", "text", PHPWS_Text::parseOutput($this->getLabel()));
        $form->setSize("TITLE", 33);
        $form->setMaxSize("TITLE", 255);
        $form->setTab("TITLE", $tab++);
         
        $form->add("MAX_MULTI_ITEMS", "text", $this->_max_multi_items);
        $form->setSize("MAX_MULTI_ITEMS", 2);
        $form->setMaxSize("MAX_MULTI_ITEMS", 255);
        $form->setTab("MAX_MULTI_ITEMS", $tab++);
         
        if (!is_array($this->_show_in_multiview))
            $this->_show_in_multiview = array();
        else {
            $selFeeds = array();
            foreach($this->_show_in_multiview as $feed) {
                $selFeeds[$feed] = $feeds[$feed];
            }
        }
         
        $sort = 1;
        if ($this->_sort_order == 1)
            $sort = 0;
         
        $tags["SHOW_MULTIVIEW"] = PHPWS_WizardBag::js_insert("swapper", "MULTI_EDIT", NULL, 0, array("SHOW_MULTIVIEW_OPTIONS" => $feeds,
            "SHOW_MULTIVIEW_SEL" => $selFeeds,
            "sorting" => $sort));
         
        $form->add("SORT_OPTION", "submit", "Order Feeds");
        $form->setTab("SORT_OPTION", $tab++);
        $form->setExtra("SORT_OPTION",
            "onclick=\"selectAll(this.form.elements['SHOW_MULTIVIEW_SEL[]'])\"");
         
        $form->add("SUBMIT_BUTTON", "submit", $this->getId() ? 
          $_SESSION["translate"]->it("Update") : $_SESSION["translate"]->it("Save"));
        $form->setTab("SUBMIT_BUTTON", $tab++);
        $form->setExtra("SUBMIT_BUTTON",
          "onclick=\"selectAll(this.form.elements['SHOW_MULTIVIEW_SEL[]'])\"");
         
        $form->add("HOME", "checkbox");
        $form->setMatch("HOME", $this->_home);
        $form->setTab("HOME", $tab++);
         
        $form->add("BLOCK", "checkbox");
        $form->setMatch("BLOCK", $this->_block);
        $form->setTab("BLOCK", $tab++);
         
        $form->add("ALLOW_VIEW", "multiple", RSS_common::get_modules_allowed('Feed'));
        if (is_array($this->allow_view))
            $form->setMatch("ALLOW_VIEW", $this->allow_view, FALSE);
        if(version_compare($GLOBALS["core"]->version,"0.10.0") > 0)
            $form->setOptgroups("ALLOW_VIEW", array("label", "line"));
        $form->setSize("ALLOW_VIEW", 10);
        $form->setTab("ALLOW_VIEW", $tab++);
         
        if ($GLOBALS['core']->moduleExists("pagemaster")) {
            $pages = RSS::getPMOptions('Feed');
            if (is_array($pages)) {
                $form->add("PM_ALLOW", "multiple", $pages);
                if(version_compare($GLOBALS["core"]->version,"0.10.0") > 0)
                    $form->setOptgroups("PM_ALLOW", array("label", "line"));
                $form->setTab("PM_ALLOW", $tab++);
                $form->setSize("PM_ALLOW", 10);
                if (is_array($this->pm_allow))
                $form->setMatch("PM_ALLOW", $this->pm_allow, FALSE);
            }
        }
         
        $fatcat = FALSE;
        if ($GLOBALS['core']->moduleExists("fatcat")) {
            $fatcat = TRUE;
            $fatId = $this->getId()+5000;
            $form->add("fatcatProcess", "hidden", "1");
            $children = $_SESSION['OBJ_fatcat']->getCategoryList();
            if ($children) {
                $match = $_SESSION['OBJ_fatcat']->getModulesCategories("phpwsrssfeeds", $fatId);
                $sticky = 0;
                if ($GLOBALS['core']->getOne("select element_id from mod_fatcat_elements where module_id=$fatId and module_title='phpwsrssfeeds' and rating=999", TRUE))
                    $sticky = 1;
                $form->add("CATEGORY", "multiple", $children);
                $form->setMatch("CATEGORY", $match, FALSE);
                $form->setSize("CATEGORY", 10);
                $form->setTab("CATEGORY", $tab++);
                 
                $form->add("STICKY", "checkbox");
                $form->setMatch("STICKY", $sticky);
                $form->setTab("STICKY", $tab++);
                 
                $tags["STICKY_LABEL"] = $_SESSION['translate']->it("Sticky");
                $tags["STICKY_HELP"] = RSS_common::show_link("phpwsrssfeeds", "rssSticky");
                 
            } else {
                if ($_SESSION['OBJ_user']->allow_access("fatcat"))
                    $catMessage = RSS_common::_errorMessage(PHPWS_Text::moduleLink($_SESSION["translate"]->it("No Categories Available"), "fatcat", array('fatcat[admin]' => 'menu')));
                else
                    $catMessage = RSS_common::_errorMessage($_SESSION["translate"]->it("No Categories Available"));
            }
        }
         
        $form->add("SHOW_EMPTY_FEEDS", "checkbox");
        $form->setMatch("SHOW_EMPTY_FEEDS", $this->_show_empty_feeds);
        $form->setTab("SHOW_EMPTY_FEEDS", $tab++);

        $form->add("SHOW_ANCHOR_LINKS", "checkbox");
        $form->setMatch("SHOW_ANCHOR_LINKS", $this->_show_anchor_links);
        $form->setTab("SHOW_ANCHOR_LINKS", $tab++);
         
        $form->add("SHOW_CAT_LINK", "checkbox");
        $form->setMatch("SHOW_CAT_LINK", $this->_show_cat_link);
        $form->setTab("SHOW_CAT_LINK", $tab++);

        $form->add("SUBMIT_BUTTON", "submit", $this->getId() ? 
          $_SESSION["translate"]->it("Update") : $_SESSION["translate"]->it("Save"));
        $form->setTab("SUBMIT_BUTTON", $tab++);
        $form->setExtra("SUBMIT_BUTTON",
            "onclick=\"selectAll(this.form.elements['SHOW_MULTIVIEW_SEL[]'])\"");
         
        $form->add("module", "hidden", "phpwsrssfeeds");
        $form->add("PHPWS_MAN_ITEMS[]", "hidden", $this->getId());
        $form->add("RSS_MULTI_MAN_op", "hidden", "save");

        $tags = $form->getTemplate(true, true, $tags);
         
        if ($this->getId())
            $tags["UPDATE_BUTTON"] = $tags["SUBMIT_BUTTON"];
         
        // include category selection menu, with help if fatcat exists
        if ($fatcat) {
            if (isset($catMessage))
                $tags["CATEGORY"] = $catMessage;
            $tags["CATEGORY_HELP"] = RSS_common::show_link("phpwsrssfeeds", "rssCategory");
            $tags["CATEGORY_LABEL"] = $_SESSION['translate']->it("Category");
        }
         
        // vary title for new and edited listings
        if ($this->getId()) {
            $tags["EDIT_TITLE"] = $_SESSION["translate"]->it("Edit RSS Feed");
        } else {
            $tags["EDIT_TITLE"] = $_SESSION["translate"]->it("Add RSS Feed");
        }
         
        /* include help links also, to display help for each field to be completed */
        $tags["TITLE_LABEL"] = $_SESSION['translate']->it("Channel Title");
        $tags["TITLE_HELP"] = RSS_common::show_link("phpwsrssfeeds", "rssTitle");
        $tags["MAX_MULTI_ITEMS_LABEL"] = $_SESSION['translate']->it("Max Number of Items in Multi-View");
        $tags["MAX_MULTI_ITEMS_HELP"] = RSS_common::show_link("phpwsrssfeeds", "rssMaxMultiItems");
        $tags["SHOW_MULTIVIEW_HELP"] = RSS_common::show_link("phpwsrssfeeds", "showMultiView");
        $tags["SHOW_MULTIVIEW_LABEL"] = $_SESSION['translate']->it("Select feeds for Multi-View");
        $tags["HOME_LABEL"] = $_SESSION['translate']->it("Show on Home Page");
        $tags["HOME_HELP"] = RSS_common::show_link("phpwsrssfeeds", "showHome");
        $tags["BLOCK_LABEL"] = $_SESSION['translate']->it("Allow Feed Placement");
        $tags["BLOCK_HELP"] = RSS_common::show_link("phpwsrssfeeds", "showBlock");
        $tags["ALLOW_VIEW_LABEL"] = $_SESSION['translate']->it("View with Modules");
        $tags["ALLOW_VIEW_HELP"] = RSS_common::show_link("phpwsrssfeeds", "allowView");
        $tags["PM_ALLOW_LABEL"] = $_SESSION['translate']->it("View with Web Pages");
        $tags["PM_ALLOW_HELP"] = RSS_common::show_link("phpwsrssfeeds", "pmView");
        $tags["SHOW_EMPTY_FEED_LBL"] = $_SESSION['translate']->it("Show Empty Feeds");
        $tags["SHOW_EMPTY_FEED_HELP"] = RSS_common::show_link("phpwsrssfeeds", "emptyFeed");
        $tags["SHOW_ANCHOR_LINKS_HELP"] = RSS_common::show_link("phpwsrssfeeds", "anchorLinks");
        $tags["SHOW_ANCHOR_LINKS_LBL"] = $_SESSION['translate']->it("Show Bookmarked Links");
        $tags["SHOW_CAT_LINK_HELP"] = RSS_common::show_link("phpwsrssfeeds", "catLink");
        $tags["SHOW_CAT_LINK_LBL"] = $_SESSION['translate']->it("Show Category Link");
        $tags['YES'] = $_SESSION["translate"]->it("Yes");
        $tags['NO'] = $_SESSION["translate"]->it("No");
         
        if (isset($this->_id) && is_numeric($this->_id) && $GLOBALS['core']->moduleExists("menuman")) {
            $_SESSION['OBJ_menuman']->add_module_item("phpwsrssfeeds",
              "&amp;RSS_MULTI_MAN_op=view&amp;PHPWS_MAN_ITEMS[]=" . $this->_id,
              "./index.php?module=phpwsrssfeeds&amp;RSS_MULTI_MAN_op=edit&amp;PHPWS_MAN_ITEMS[]=" . $this->_id, 1);
        }
         
        $content = PHPWS_Template::processTemplate($tags, "phpwsrssfeeds", "multi_rss/edit.tpl");
         
        return $content;
    } //END function _edit
     
    /**
    * Saves data for rss object to database
    *
    * @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
    */
    function _save() {
        if (isset($_POST["PHPWS_MAN_ITEMS"])
          && sizeof($_POST["PHPWS_MAN_ITEMS"]) > 0 && is_array($_POST["PHPWS_MAN_ITEMS"]))
            $this->Multi_RSS($_POST["PHPWS_MAN_ITEMS"][0]);
         
        //Standard error checking
        if (is_object($this->_error))
            return FALSE;
        //Check to see if save was a get request. If so, display error.
        if (isset($_GET['module']))
        return FALSE;
        $newrss = ($this->getId() == NULL);
        $error = FALSE;
        $message = '';
         
        // we MUST have a rss feed title, since this is the label for the data entry; errors if not
        if (!isset($_POST["TITLE"]) || (trim($_POST["TITLE"]) == "")) {
            $message["TITLE"] = $_SESSION['translate']->it("Please enter a RSS Feed title.");
            $error = TRUE;
        }
        // $_show_in_multiview must be an array or there is nothing to display
         
        if (!isset($_POST["SHOW_MULTIVIEW_SEL"]) || !is_array($_POST["SHOW_MULTIVIEW_SEL"])) {
            $this->_show_in_multiview = array();
            $message["SHOW_MULTIVIEW"] = $_SESSION['translate']->it("Please select feeds for inclusion.");
            $error = TRUE;
        } else {
            $this->_show_in_multiview = $_POST["SHOW_MULTIVIEW_SEL"];
        }
        //Check to see if a number has been entered into max items if number is 0 or NULL reset to 10
        if (isset($_POST["MAX_MULTI_ITEMS"]) && !is_numeric($_POST["MAX_MULTI_ITEMS"])) {
            $message["MAX_MULTI_ITEMS"] = $_SESSION['translate']->it("Max number of items must be a number.");
            $_POST["MAX_MULTI_ITEMS"] = 10;
            $error = TRUE;
        }
        if (!isset($_POST["MAX_MULTI_ITEMS"]) || @$_POST["MAX_MULTI_ITEMS"] == 0) {
            $_POST["MAX_MULTI_ITEMS"] = 10;
            $message["MAX_MULTI_ITEMS"] = $_SESSION['translate']->it("Max number of items must be set to a number 1 or greater.");
            $error = TRUE;
        }
        // populate the current vars in any case
        // note that parseInput called this way allows the global set of HTML tags to be included
        $this->setLabel(RSS_common::get_htmlentities(PHPWS_Text::parseInput(@$_POST["TITLE"])));
         
        $current_home = $this->_home;
        $this->_home = trim(@$_POST["HOME"]);
        //saves current status of view block in $current_block
        $current_block = $this->_block;
        $this->_block = trim(@$_POST["BLOCK"]);
        $this->_max_multi_items = @$_POST["MAX_MULTI_ITEMS"];
        $this->allow_view = @$_POST["ALLOW_VIEW"];
        $this->pm_allow = @$_POST["PM_ALLOW"];
         
        if (isset($_POST["SHOW_EMPTY_FEEDS"]))
            $this->_show_empty_feeds = @$_POST["SHOW_EMPTY_FEEDS"];
        else
            $this->_show_empty_feeds = 0;
         
        if (isset($_POST["SHOW_ANCHOR_LINKS"]))
            $this->_show_anchor_links = @$_POST["SHOW_ANCHOR_LINKS"];
        else
            $this->_show_anchor_links = 0;
         
        if (isset($_POST["SHOW_CAT_LINK"]))
            $this->_show_cat_link = @$_POST["SHOW_CAT_LINK"];
        else
            $this->_show_cat_link = 0;
         
        if ($error) {
            $title = $_SESSION['translate']->it("Error");
            $content = RSS_common::_errorMessage($message, $title) . $this->_edit();
        } else {
            // save the feed
            $this->commit();
            $_id = $this->getId();
            // save the category
            if (isset($_POST["fatcatProcess"])) {
                require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/RSS_Fatcat.php');
                $element_link = "index.php?module=phpwsrssfeeds&amp;RSS_MULTI_MAN_op=sView&amp;PHPWS_MAN_ITEMS[]=" . $_id;
                RSS_Fatcat::saveSelect($this->getLabel(), $element_link, $this->getId()+5000);
            }
             
            //set the side block in layout
            if ($newrss && $this->_block == 1) {
                $_SESSION['OBJ_layout']->create_temp("phpwsrssfeeds", 
                  "CNT_phpwsrssfeeds_multi_".$_id, 'center_col_mid');
                unset($_SESSION["PHPWSRSSFEEDS"]["allow_view"]);
            }
            if ($current_block == 0 && $this->_block == 1) {
                $_SESSION['OBJ_layout']->create_temp("phpwsrssfeeds", 
                  "CNT_phpwsrssfeeds_multi_".$_id, 'center_col_mid');
                unset($_SESSION["PHPWSRSSFEEDS"]["allow_view"]);
            }
            if ($current_block == 1 && $this->_block == 0) {
                PHPWS_Layout::dropBox("CNT_phpwsrssfeeds_multi_".$_id);
                unset($_SESSION["PHPWSRSSFEEDS"]["allow_view"]);
            }
             
            //set the main item in layout
            if ($newrss && $this->_home == 1) {
                $_SESSION['OBJ_layout']->create_temp("phpwsrssfeeds", "CNT_phpwsrssfeeds_multi_home_".$_id, 'body');
                unset($_SESSION["PHPWSRSSFEEDS"]["allow_view"]);
            }
            if ($current_home == 0 && $this->_home == 1) {
                $_SESSION['OBJ_layout']->create_temp("phpwsrssfeeds", "CNT_phpwsrssfeeds_multi_home_".$_id, 'body');
                unset($_SESSION["PHPWSRSSFEEDS"]["allow_view"]);
            }
            if ($current_home == 1 && $this->_home == 0) {
                PHPWS_Layout::dropBox("CNT_phpwsrssfeeds_multi_home_".$_id);
                unset($_SESSION["PHPWSRSSFEEDS"]["allow_view"]);
            }
             
            // tell user data is saved or updated
            if ($newrss) {
                $title = $_SESSION['translate']->it("Multi-View Feed saved. ");
                $message = PHPWS_Text::moduleLink($_SESSION["translate"]->it("Edit This Multi-View Feed"), "phpwsrssfeeds", array("RSS_MULTI_MAN_op" => "edit", "PHPWS_MAN_ITEMS[]" => $this->_id));
            } else {
                $title = $_SESSION['translate']->it("Multi-RSS Feed updated. ");
                $message = PHPWS_Text::moduleLink($_SESSION["translate"]->it("Edit This Multi-View Feed"), "phpwsrssfeeds", array("RSS_MULTI_MAN_op" => "edit", "PHPWS_MAN_ITEMS[]" => $this->_id));
            }
            $content = RSS_common::_message($message, $title);
            if (isset($_POST["SORT_OPTION"]))
                $content .= $this->customSort();
            else
                $content .= $this->_view();
        }
        // set content display
        return $content;
         
    } //END function _save
     
    /**
    * Asks for confirmation and, getting it, deletes the current position listing
    *
    * @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
    */
    function _delete($id) {
        if ($id == NULL)
            return FALSE;
         
        $this->Multi_RSS($id);
        //Standard error checking
        if (is_object($this->_error))
            return FALSE;
         
        $id = $this->getId();
        // have confirmation; remove category listing while we know the id
        $_SESSION['OBJ_fatcat']->deleteModuleElements("phpwsrssfeeds", $this->getLabel(), $id+5000);
         
        //remove layout boxes set up for this item
        if ($this->_block == 1)
        PHPWS_Layout::dropBox("CNT_phpwsrssfeeds_multi_".$id);
        if ($this->_home == 1)
        PHPWS_Layout::dropBox("CNT_phpwsrssfeeds_multi_home_".$id);
         
        // delete the current item
        $this->_error = $this->kill();
        if (is_object($this->_error))
            return FALSE;
         
        return ' '.$this->getLabel();
         
    } //END function _delete()
     
    function _list() {
        require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/RSSList.php');
        $content = FALSE;
        $settings = array("_module" => "phpwsrssfeeds",
            "_table" => "mod_phpwsrssfeeds_multi",
            "_request" => "RSS_MULTI_MAN_op");
        $listObj = RSSList::getRSSList($settings);
        $listObj->init();
        if ($_SESSION["OBJ_user"]->allow_access("phpwsrssfeeds"))
            $content = $listObj->getList("multi_admin", $_SESSION["translate"]->it("Multi-View RSS News Feeds"));
        else
            $content = $listObj->getList("multi", $_SESSION["translate"]->it("Multi-View News Feeds"));
         
        return $content;
    }//End function _list()
     
    function _hide($id = NULL) {
        require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/RSSList.php');
        if (is_null($id))
            return FALSE;
        $settings = array("_module" => "phpwsrssfeeds",
          "_table" => "mod_phpwsrssfeeds_multi");
        $listObj = RSSList::getRSSList($settings);
        $hide = $listObj->_doMassUpdate("hidden", 1);
        if ($hide == FALSE)
            return FALSE;
        return $_SESSION['translate']->it('Items updated.');
    }//End fuction _hide()
     
    function _show($id = NULL) {
        require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/RSSList.php');
        if (is_null($id))
            return FALSE;
        $settings = array("_module" => "phpwsrssfeeds",
            "_table" => "mod_phpwsrssfeeds_multi");
        $listObj = RSSList::getRSSList($settings);
        $show = $listObj->_doMassUpdate("hidden", 0);
        if ($show == FALSE)
            return FALSE;
        return $_SESSION['translate']->it('Items updated.');
    }//End fuction _show()
     
    function showHomeItems() {
        $content = $this->_view();
        $content_var = "CNT_phpwsrssfeeds_multi_home_" . $this->getId();
        $GLOBALS[$content_var]['title'] = $_SESSION['translate']->it($this->_label);
        $GLOBALS[$content_var]['content'] = $content;
        return true;
    }
    
    /**
     * Sets up content for view in block
     *
     * @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
     * @ref RSS:showUserBox()
     *
     */
    function showUserBox() {
	if (RSS_Common::canDisplay($this->allow_view, $this->pm_allow)) {
	  $content_var = 'CNT_phpwsrssfeeds_multi_' . $this->_id;
	  $GLOBALS[$content_var]['content'] = $this->_view();
	  $GLOBALS[$content_var]['title'] = $this->_label;
	  return TRUE;
        } else {
	  return FALSE;
	}
         
    }//End function showUserBox()
     
}//End class Multi_RSS
?>
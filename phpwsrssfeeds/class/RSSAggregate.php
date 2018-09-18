<?php

/**
 * RSS_Aggregate.php
 * Defines RSS_Aggregate class for RSS Feeds module
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
 * @version $Id: RSSAggregate.php,v 1.7 2005/03/14 19:34:10 wendall911 Exp $
 * @author Darren Greene <dg49379@NOSPAM.users.sourceforge.net>
 * @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
 * @package phpwsrssfeeds
 */
 
require_once(PHPWS_SOURCE_DIR.'core/Item.php');
require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/RSS.php');
require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/RSS_common.php');

 
class RSS_Aggregate extends PHPWS_Item {

    /**
     * title of this feed is stored in the item's Label field
     */

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
    var $_allow_view;

    /**
     * list of modules needing to be active for feed to appear
     * @var array
     */
    var $_pm_allow;

    /**
     * Which RSS feeds in the aggregate mode
     * @var array
     */
    var $_show_in_aggregate;
    
    /**
     * Type of aggregation, date, alpha, etc.
     * @var sting
     */
    var $_type;
    
    /**
     * Sets order to asc or desc
     * @var string
     */
    var $_asc_desc;

    var $_error = NULL;

    /**
     * Constructor for the RSS class
     * Sets all the RSS attributes
     *
     * @param int $id id of the rss feed to be constructed; NULL if new rss
     */
     
    function RSS_Aggregate($id = NULL) {
        /* These vars are excluded on commit() */
        $exclude = array('_error');
        $this->addExclude($exclude);
        /* point to the rss_aggregate data table */
        $this->setTable('mod_phpwsrssfeeds_aggregate');
        
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
            $this->_type = 'Date';
            $this->_asc_desc = 'ASC';
        }
    } //END function RSS_Aggregate

    /**
     * Edit a RSS_Aggregate object's data
     *
     * @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
     */
    function _edit($id = NULL) {
        if (!is_null($id))
            $this->RSS_Aggregate($id);
        //Standard error checking
        if (is_object($this->_error))
            return FALSE;
        //Check for available feeds, if none are available, return error
        $feeds = RSS_common::get_available_feeds();
        if ($feeds == FALSE)
            return RSS_common::_errorMessage(PHPWS_Text::moduleLink($_SESSION["translate"]->it("No Feeds Available"), "phpwsrssfeeds", array('RSS_MAN_op' => 'list')));

        $tags = array();
        $newaggregate = ($this->getId() == NULL);
        // Set up the edit form
        $form = new EZform('AGGREGATE_EDIT');

        $tab = 1;

        $form->add("TITLE", "text", PHPWS_Text::parseOutput($this->getLabel()));
        $form->setSize("TITLE", 33);
        $form->setMaxSize("TITLE", 255);
        $form->setTab("TITLE", $tab++);
         
        if (!is_array($this->_show_in_aggregate)) {
            $this->_show_in_aggregate = array();
	    $selFeeds = array();
        } else {
            $selFeeds = array();
            foreach($this->_show_in_aggregate as $feed) {
                $selFeeds[$feed] = $feeds[$feed];
            }
        }
         
        $tags["SHOW_AGGREGATE"] = PHPWS_WizardBag::js_insert("swapper", "AGGREGATE_EDIT", NULL, 0, array("SHOW_AGGREGATE_OPTIONS" => $feeds,
            "SHOW_AGGREGATE_SEL" => $selFeeds));
         
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
         
        $form->add("ALLOW_VIEW", "multiple", RSS_common::get_modules_allowed());
        if (is_array($this->_allow_view))
            $form->setMatch("ALLOW_VIEW", $this->_allow_view, FALSE);
        if(version_compare($GLOBALS["core"]->version,"0.10.0") > 0)
            $form->setOptgroups("ALLOW_VIEW", array("label", "line"));
        $form->setSize("ALLOW_VIEW", 10);
        $form->setTab("ALLOW_VIEW", $tab++);

        $type = array('Date' => $_SESSION['translate']->it('Date'));
        $form->add('TYPE', 'select', $type);
        $form->setMatch('TYPE', $this->_type);
        $form->setTab('TYPE', $tab++);

        $asc_desc = array('DESC' => $_SESSION['translate']->it('DESC'),
          'ASC' => $_SESSION['translate']->it('ASC'));
        $form->add('ASC_DESC', 'select', $asc_desc);
        $form->setMatch('ASC_DESC', $this->_asc_desc);
        $form->setTab('ASC_DESC', $tab++);
        
        if ($GLOBALS['core']->moduleExists("pagemaster")) {
            $pages = RSS::getPMOptions();
            if (is_array($pages)) {
                $form->add("PM_ALLOW", "multiple", $pages);
                if(version_compare($GLOBALS["core"]->version,"0.10.0") > 0)
                    $form->setOptgroups("PM_ALLOW", array("label", "line"));
                $form->setTab("PM_ALLOW", $tab++);
                $form->setSize("PM_ALLOW", 10);
                if (is_array($this->_pm_allow))
                $form->setMatch("PM_ALLOW", $this->_pm_allow, FALSE);
            }
        }
         
        $form->add("SUBMIT_BUTTON", "submit", $this->getId() ? 
          $_SESSION["translate"]->it("Update") : $_SESSION["translate"]->it("Save"));
        $form->setTab("SUBMIT_BUTTON", $tab++);
        $form->setExtra("SUBMIT_BUTTON",
            "onclick=\"selectAll(this.form.elements['SHOW_AGGREGATE_SEL[]'])\"");
         
        $form->add("module", "hidden", "phpwsrssfeeds");
        $form->add("PHPWS_MAN_ITEMS[]", "hidden", $this->getId());
        $form->add("RSS_AGGR_MAN_op", "hidden", "save");

        $tags = $form->getTemplate(true, true, $tags);
         
        if ($this->getId())
            $tags["UPDATE_BUTTON"] = $tags["SUBMIT_BUTTON"];
         
        // vary title for new and edited listings
        if ($this->getId()) {
            $tags["EDIT_TITLE"] = $_SESSION["translate"]->it("Edit RSS Feed");
        } else {
            $tags["EDIT_TITLE"] = $_SESSION["translate"]->it("Add RSS Feed");
        }
         
        /* include help links also, to display help for each field to be completed */
        $tags["TITLE_LABEL"] = $_SESSION['translate']->it("Channel Title");
        $tags["TITLE_HELP"] = RSS_common::show_link("phpwsrssfeeds", "rssTitle");
        $tags["SHOW_AGGREGATE_HELP"] = RSS_common::show_link("phpwsrssfeeds", "showMultiView");
        $tags["SHOW_AGGREGATE_LABEL"] = $_SESSION['translate']->it("Select feeds for Multi-View");
        $tags["HOME_LABEL"] = $_SESSION['translate']->it("Show on Home Page");
        $tags["HOME_HELP"] = RSS_common::show_link("phpwsrssfeeds", "showHome");
        $tags["BLOCK_LABEL"] = $_SESSION['translate']->it("Show in Block");
        $tags["BLOCK_HELP"] = RSS_common::show_link("phpwsrssfeeds", "showBlock");
        $tags["ALLOW_VIEW_LABEL"] = $_SESSION['translate']->it("Allow Block View");
        $tags["ALLOW_VIEW_HELP"] = RSS_common::show_link("phpwsrssfeeds", "allowView");
        $tags["PM_ALLOW_LABEL"] = $_SESSION['translate']->it("Allow Block View with Web Pages");
        $tags["PM_ALLOW_HELP"] = RSS_common::show_link("phpwsrssfeeds", "pmView");
        $tags['TYPE_LBL'] = $_SESSION['translate']->it('Aggregate Feed Items By');
        $tags["SHOW_CAT_LINK_HELP"] = RSS_common::show_link("phpwsrssfeeds", "catLink");
        $tags["SHOW_CAT_LINK_LBL"] = $_SESSION['translate']->it("Show Category Link");
        $tags['YES'] = $_SESSION["translate"]->it("Yes");
        $tags['NO'] = $_SESSION["translate"]->it("No");
         
        if (isset($this->_id) && is_numeric($this->_id) && $GLOBALS['core']->moduleExists("menuman")) {
            $_SESSION['OBJ_menuman']->add_module_item("phpwsrssfeeds",
              "&amp;RSS_MULTI_MAN_op=view&amp;PHPWS_MAN_ITEMS[]=" . $this->_id,
              "./index.php?module=phpwsrssfeeds&amp;RSS_MULTI_MAN_op=edit&amp;PHPWS_MAN_ITEMS[]=" . $this->_id, 1);
        }
         
        $content = PHPWS_Template::processTemplate($tags, "phpwsrssfeeds", "aggregate/edit.tpl");
         
        return $content;
    }//End function _edit()

    /**
     * Asks for confirmation and, getting it, deletes the current item
     *
     * @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
     */
    function _delete($id) {
        if (!is_null($id))
            $this->RSS_Aggregate($id);
        //Standard error checking
        if (is_object($this->_error))
            return FALSE;

        $id = $this->getId();
        // have confirmation; remove category listing while we know the id
        $_SESSION['OBJ_fatcat']->deleteModuleElements("phpwsrssfeeds", $this->getLabel(), $id+10000);
         
        //remove layout boxes set up for this item
        if ($this->_block == 1)
        PHPWS_Layout::dropBox("CNT_phpwsrssfeeds_aggr_".$id);
        if ($this->_home == 1)
        PHPWS_Layout::dropBox("CNT_phpwsrssfeeds_aggr_home_".$id);
         
        // delete the current item
        $this->_error = $this->kill();
        if (is_object($this->_error))
            return FALSE;
         
        return ' '.$this->getLabel();
    }//End function _delete()

    /**
     * Displays current item
     *
     * @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
     */
    function _view($id = NULL) {
        if (!is_null($id))
            $this->RSS_Aggregate($id);
        //Return message that the feed is currently hidden.
        if ($this->_hidden && !$_SESSION['OBJ_user']->isDeity()) {
            $title = $_SESSION['translate']->it('Sorry, this feed is currently hidden from public view.');
            $message .= RSS_common::_continueContent($_SESSION['translate']->it('Return to Feeds List'), 'list', $title, 'RSS_AGGR_MAN_op');
            return $message;
        }
	

	$allBlogs = array();
		
	foreach($this->_show_in_aggregate as $feed_id) {
	  $feedObj = RSS::getRSS($feed_id);

	  //Standard error checking
	  if (is_object($feedObj->_error))
	    continue;
	  
	  if($feedObj->_hidden || !$feedObj->_approved)
	    continue;

	  if($feedObj->expired())
	    $_SESSION['PHPWSRSSFEEDS']['update'][$feedObj->getId()] = $feedObj->getId();
	  
	  $tags = $feedObj->_content;

	  $today = mktime(0,0,0);
	  $endDate = NULL;
	  /*
	  if($this->_days_ahead != 0) {
	    $endDate = mktime(12,12,12,date("m"), date("d")+$this->_days_ahead, date("Y"));
	  }
	  */

	  if(isset($tags["CONTENT"]) && is_array($tags["CONTENT"])) {
	    foreach($tags["CONTENT"] as $item) {	      
	      if(isset($item["DATE_TS"])) {
		// remove any time information from ts
		$date = mktime(12,12,12, date('m', $item['DATE_TS']), 
			       date('d', $item['DATE_TS']), 
			       date('Y', $item['DATE_TS']));

		if(isset($allBlogs[$date][$feedObj->_label]["items"])) {	      
		
		  $arr = $allBlogs[$date][$feedObj->_label]["items"];
		  $arr[count($arr) + 1] = $item;
		  $allBlogs[$date][$feedObj->_label]["items"] = $arr;
		} else {
		  $allBlogs[$date][$feedObj->_label]["items"] = array($item);
		}

		$allBlogs[$date][$feedObj->_label]["id"] = $feedObj->_id;	      
		$allBlogs[$date][$feedObj->_label]["max_items"] = $feedObj->_max_items;	      

		// add title to array
		if($feedObj->_show_title) {
		  
		  if($feedObj->_show_url && isset($tags["TITLE_LINK"])) 
		    $allBlogs[$date][$feedObj->_label]["title_link"] = $tags["TITLE_LINK"];

		  if(isset($tags["TITLE_TARGET"]))
		    $allBlogs[$date][$feedObj->_label]["title_target"] = $tags["TITLE_TARGET"];		  
		} else {
		    $allBlogs[$date][$feedObj->_label]["no_title"] = TRUE;		  
		}

		// add image to array
		if($feedObj->_show_image) {
		  if(isset($tags["CHANNEL_IMAGE"])) {
		    $allBlogs[$date][$feedObj->_label]["image_src"] = $tags["CHANNEL_IMAGE"]; 		    
		  }

		  if(isset($tags["CHANNEL_IMG_URL"])) {
		    $allBlogs[$date][$feedObj->_label]["image_url"] = $tags["CHANNEL_IMG_URL"]; 		    
		  }

		  if(isset($tags["CHANNEL_IMG_ALT"])) {
		    $allBlogs[$date][$feedObj->_label]["image_alt"] = $tags["CHANNEL_IMG_ALT"]; 		    		    
		  }

		  if(isset($tags["CHANNEL_IMG_TARGET"])) {
		    $allBlogs[$date][$feedObj->_label]["image_target"] = $tags["CHANNEL_IMG_TARGET"]; 		    		    
		  }

		} else {
		  $allBlogs[$date][$feedObj->_label]["no_image"] = TRUE;		  		  
		}

		if($feedObj->_show_desc) {
		  $allBlogs[$date][$feedObj->_label]["description"] = $tags["CHANNEL_DESC"];
		} 
	      }
	    }
	  }
	}	
    
    if($this->_asc_desc == 'ASC')
	    ksort($allBlogs);
    elseif($this->_asc_desc == 'DESC')
        krsort($allBlogs);
	
	$content = "";
	//	echo PHPWS_Debug::testArray($allBlogs);
	//	echo PHPWS_Debug::testArray($allBlogs);
	foreach($allBlogs as $date=>$feed) {
	  if(isset($date) && $date != date("l, F j, Y", $date)) {
	    $dateTags["DATE"] = date("l, F j, Y", $date);
	    if($dateTags["DATE"] == date("l, F j, Y"))
	      $dateTags["TODAY"] = $_SESSION["translate"]->it("Today");
	    else
	      $dateTags["TODAY"] = NULL;

	    $content .= PHPWS_Template::processTemplate($dateTags, "phpwsrssfeeds", "multi_rss/date_blog/date.tpl");
	  }
	  
	  $feedTags = array();
	  $numItems = 0;
	  foreach($feed as $title=>$feedContent) {
	    if($numItems > $feedContent["max_items"]) 
	      break;
	    
	    if(!isset($feedContent["no_title"])) {
	      if(isset($feedContent["title_link"])) {
		$feedTags["FEED_TITLE"] = "<a href=\"" . $feedContent["title_link"] . "\"";
		if(isset($feedContent["title_target"]))
		  $feedTags["FEED_TITLE"] .= " target=\"" . $feedContent["title_target"] . "\" ";
		
		$feedTags["FEED_TITLE"] .= '>' . $title . '</a>';
		
	      } else {
		$feedTags["FEED_TITLE"] = $title;
	      }
	    } else {
	      $feedTags["FEED_TITLE"] = NULL;
	    }
	    
	    if(isset($feedContent["image_src"])) {
	      if(isset($feedContent["image_url"])) {
		$target = $alt = "";
		if(isset($feedContent["IMAGE_target"])) 
		  $target = " target=\"". $feedContent["IMAGE_target"] . "\"";
		
		if(isset($feedContent["IMAGE_alt"])) 
		  $alt = " alt=\"". $feedContent["IMAGE_alt"] . "\"";

		$image = "<img border=\"0\" $target $alt src=\"" . $feedContent["image_src"] . "\" />";				
		$feedTags["FEED_IMAGE"] = "<a href=\"".$feedContent["image_url"]."\">".$image."</a>";

		} else {
		  $feedTags["FEED_IMAGE"] = "<img src=\"" . $feedContent["image_src"] . "\" />";				
		}

	    } 

	    if(isset($feedContent["description"])) {
	      $feedTags["FEED_DESC"] = $feedContent["description"];
	    }

	    $feedTags["FEED_CONTENT"] = "";
	    $itemCount = 0;

	    foreach($feedContent["items"] as $item) {	  
	      $itemCount++;
	      /*
	      if($itemCount > $this->_max_multi_items) 
		break;
	      */
	      if(isset($item["MODULE"]) && $item["MODULE"] == "calendar") {
		$item["ITEM_TITLE"] = substr($item["ITEM_TITLE"], strpos($item["ITEM_TITLE"], '-')+2);
	      }
	      
	      $itemTags["ITEM_TITLE"] = $item["ITEM_TITLE"];
	      $itemTags["ITEM_DESC"]  = $item["ITEM_DESC"];
	      
	      $feedTags["FEED_CONTENT"] .= PHPWS_Template::processTemplate($itemTags,"phpwsrssfeeds", "multi_rss/date_blog/item.tpl");
	    }
	    
	    if($_SESSION['OBJ_user']->allow_access("phpwsrssfeeds", "edit"))
	      $feedTags["EDIT_FEED"] = PHPWS_Text::moduleLink($_SESSION["translate"]->it("Edit This Feed"), "phpwsrssfeeds", array("RSS_MAN_op"=>"edit", "PHPWS_MAN_ITEMS[]"=>$feedContent["id"]));
	    
	    $content .= PHPWS_Template::processTemplate($feedTags, "phpwsrssfeeds", "multi_rss/date_blog/feed.tpl");
	  }
	}

	$tags["CONTENT"] = $content;

	if($_SESSION['OBJ_user']->allow_access("phpwsrssfeeds", "aggr_edit"))
	  $tags["EDIT_AGGR"] = PHPWS_Text::moduleLink($_SESSION["translate"]->it("Edit This Aggregation Feed"), "phpwsrssfeeds", array("RSS_AGGR_MAN_op"=>"edit", "PHPWS_MAN_ITEMS[]"=>$this->_id));

	return PHPWS_Template::processTemplate($tags, "phpwsrssfeeds", "multi_rss/date_blog/view.tpl");
    }//End function _view()





   /**
    * Saves data for rss object to database
    *
    * @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
    */
    function _save() {
        if (isset($_POST["PHPWS_MAN_ITEMS"])
          && sizeof($_POST["PHPWS_MAN_ITEMS"]) > 0 && is_array($_POST["PHPWS_MAN_ITEMS"]))
            $this->RSS_Aggregate($_POST["PHPWS_MAN_ITEMS"][0]);
         
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

        if (!isset($_POST["SHOW_AGGREGATE_SEL"]) || !is_array($_POST["SHOW_AGGREGATE_SEL"])) {
            $this->_show_in_aggregate = array();
            $message["SHOW_AGGREGATE"] = $_SESSION['translate']->it("Please select feeds for inclusion.");
            $error = TRUE;
        } else {
            $this->_show_in_aggregate = $_POST["SHOW_AGGREGATE_SEL"];
        }

        // populate the current vars in any case
        // note that parseInput called this way allows the global set of HTML tags to be included
        $this->setLabel(RSS_common::get_htmlentities(PHPWS_Text::parseInput(@$_POST["TITLE"])));
        $this->_type = $_POST['TYPE'];
        $this->_asc_desc = $_POST['ASC_DESC'];
        $current_home = $this->_home;

        $this->_home = trim(@$_POST["HOME"]);
        //saves current status of view block in $current_block
        $current_block = $this->_block;
        $this->_block = trim(@$_POST["BLOCK"]);
        $this->_allow_view = @$_POST["ALLOW_VIEW"];
        $this->_pm_allow = @$_POST["PM_ALLOW"];
         
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
                $element_link = "index.php?module=phpwsrssfeeds&amp;RSS_AGGR_MAN_op=sView&amp;PHPWS_MAN_ITEMS[]=" . $_id;
                RSS_Fatcat::saveSelect($this->getLabel(), $element_link, $this->getId()+5000);
            }
             
            //set the side block in layout
            if ($newrss && $this->_block == 1) {
                $_SESSION['OBJ_layout']->create_temp("phpwsrssfeeds", 
                  "CNT_phpwsrssfeeds_aggr_".$_id, 'center_col_mid');
                unset($_SESSION["PHPWSRSSFEEDS"]["allow_view"]);
            }
            if ($current_block == 0 && $this->_block == 1) {
                $_SESSION['OBJ_layout']->create_temp("phpwsrssfeeds", 
                  "CNT_phpwsrssfeeds_aggr_".$_id, 'center_col_mid');
                unset($_SESSION["PHPWSRSSFEEDS"]["allow_view"]);
            }
            if ($current_block == 1 && $this->_block == 0) {
                PHPWS_Layout::dropBox("CNT_phpwsrssfeeds_aggr_".$_id);
                unset($_SESSION["PHPWSRSSFEEDS"]["allow_view"]);
            }
             
            //set the main item in layout
            if ($newrss && $this->_home == 1) {
                $_SESSION['OBJ_layout']->create_temp("phpwsrssfeeds", "CNT_phpwsrssfeeds_aggr_home_".$_id, 'body');
                unset($_SESSION["PHPWSRSSFEEDS"]["allow_view"]);
            }
            if ($current_home == 0 && $this->_home == 1) {
                $_SESSION['OBJ_layout']->create_temp("phpwsrssfeeds", "CNT_phpwsrssfeeds_aggr_home_".$_id, 'body');
                unset($_SESSION["PHPWSRSSFEEDS"]["allow_view"]);
            }
            if ($current_home == 1 && $this->_home == 0) {
                PHPWS_Layout::dropBox("CNT_phpwsrssfeeds_aggr_home_".$_id);
                unset($_SESSION["PHPWSRSSFEEDS"]["allow_view"]);
            }
             
            // tell user data is saved or updated
            if ($newrss) {
                $title = $_SESSION['translate']->it("Aggregation Feed saved. ");
                $message = PHPWS_Text::moduleLink($_SESSION["translate"]->it("Edit This Aggregation Feed"), "phpwsrssfeeds", array("RSS_AGGR_MAN_op" => "edit", "PHPWS_MAN_ITEMS[]" => $this->_id));
            } else {
                $title = $_SESSION['translate']->it("Aggregation Feed updated. ");
                $message = PHPWS_Text::moduleLink($_SESSION["translate"]->it("Edit This Aggregation Feed"), "phpwsrssfeeds", array("RSS_AGGR_MAN_op" => "edit", "PHPWS_MAN_ITEMS[]" => $this->_id));
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
        $settings = array("_module" => "phpwsrssfeeds",
            "_table" => "mod_phpwsrssfeeds_aggregate",
            "_request" => "RSS_AGGR_MAN_op");
        $listObj = RSSList::getRSSList($settings);
        $listObj->init();
        if ($_SESSION["OBJ_user"]->allow_access("phpwsrssfeeds"))
            $content = $listObj->getList("aggr_admin", $_SESSION["translate"]->it("Aggregated News Feeds"));
        else
            $content = $listObj->getList("aggr", $_SESSION["translate"]->it("Aggregated News Feeds"));
         
        return $content;
    }//End function _list()

    function _hide($id = NULL) {
        require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/RSSList.php');
        if (is_null($id))
            return FALSE;
        $settings = array('_module' => 'phpwsrssfeeds',
            '_table' => 'mod_phpwsrssfeeds_aggregate');
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
            '_table' => 'mod_phpwsrssfeeds_aggregate');
        $listObj = RSSList::getRSSList($settings);
        $show = $listObj->_doMassUpdate('hidden', 0);
        if ($show == FALSE)
            return FALSE;
        return $_SESSION['translate']->it('Items updated.');
    }//End fuction _show()


    function showUserBox() {
      if (RSS_Common::canDisplay($this->_allow_view, $this->_pm_allow)) {
	$content_var = 'CNT_phpwsrssfeeds_aggr_' . $this->_id;
	$GLOBALS[$content_var]['content'] = $this->_view();
	$GLOBALS[$content_var]['title'] = $this->_label;
	return TRUE;
      } else {
	return FALSE;
      }
    }

    function showHomeItems() {
      $content = $this->_view();
      $content_var = "CNT_phpwsrssfeeds_aggr_home_" . $this->getId();
      $GLOBALS[$content_var]['title'] = $_SESSION['translate']->it($this->_label);
      $GLOBALS[$content_var]['content'] = $content;
      return true;
    }
}//End class RSS_Aggregate
?>
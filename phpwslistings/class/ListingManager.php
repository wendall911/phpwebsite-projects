<?php
/**
* SEILER_ListingManager for phpwsListings
*
* SEILER_ListingManager is the manager class
* for the phpwsListings module
*
* @author Don Seiler <don@seiler.us>
* @version $Id: ListingManager.php,v 1.72 2004/01/03 04:35:20 wendall911 Exp $
* @package phpwsListings
*/

require_once(PHPWS_SOURCE_DIR.'core/Form.php');

class SEILER_ListingManager {
    var $Listing_block;
    var $_paginateLimit;
    var $_showMenu;
    var $_showFeature;
    var $_showBlock;
    var $_numBlock;
    var $_blockImagesOnly;
    var $_imageSizeLimit;
    var $_imageMaxWidth;
    var $_imageMaxHeight;
    var $_imageWidthRedim;
    var $_imageHeightRedim;
    var $_listingImageLimit;
    var $_allowedImageTypes;
    var $_featureTitle;
    var $_blockTitle;
    var $_useExpiration;
    var $_customLayout;

    var $_formelements = array();
    var $_classes = array();

    function SEILER_ListingManager() {
        include(PHPWS_SOURCE_DIR . "mod/phpwslistings/conf/config.php");
        $this->_allowedImageTypes = explode(",", $allowedImageTypes);
        foreach($this->_allowedImageTypes as $key=>$type)
            $this->_allowedImageTypes[$key] = trim($type);

        $this->loadSettings();
        $this->loadClasses();
        $this->loadFormElements();

    }

    function menu() {
        if(!$_SESSION["OBJ_user"]->username)
            return;

        $agent = new SEILER_ListingAgent($_SESSION["OBJ_user"]->user_id);
        if(!$agent->_active)
            $tags["NOAGENT"] = "<font class=\"errortext\">" . $_SESSION["translate"]->it("You are not an active agent.  Please contact the website administrator to change this.") . "</font>";

        if(!count($this->_classes))
            $tags["NOCLASSES"] = "<font class=\"errortext\">" . $_SESSION["translate"]->it("You have no classes set up in the system.  You must have at least one class.  Please ask the phpwsListings manager to add one through the \"Create Class\" interface.") . "</font>";
        //if($_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_listing"))
        if($agent->_active) {
            if(count($this->_classes) > 1) {
                $tags["NEW_LISTING"] = SEILER_ListingClass::showSelect("CLASS_id",NULL); 
                $tags["NEW_LISTING"] .= PHPWS_Form::formSubmit($_SESSION["translate"]->it("Create Listing"), "LISTINGS_op");
            } else {
                $tags["NEW_LISTING"] = "<a href=\"index.php?module=phpwslistings&LISTINGS_op=" . $_SESSION["translate"]->it("Create Listing") . "&CLASS_id=" . $this->_classes[0]->_id . "\">Create Listing</a>";
            }
        }

        //if($_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_listing"))
            //$tags["LIST_LISTINGS"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Manage Listings"), "LISTINGS_op");
            $tags["LIST_LISTINGS"] = "<a href=\"index.php?module=phpwslistings&LISTINGS_op=" . $_SESSION["translate"]->it("Manage Listings") . "\">Manage Listings</a>";

        if($_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_class")) {
            $tags["NEW_CLASS"] = "<a href=\"index.php?module=phpwslistings&LISTINGS_op=" . $_SESSION["translate"]->it("Create Listing Class") . "\">Create Listing Class</a>";
            //$tags["NEW_CLASS"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Create Listing Class"), "LISTINGS_op");
            $tags["LIST_CLASSES"] = "<a href=\"index.php?module=phpwslistings&LISTINGS_op=" . $_SESSION["translate"]->it("Manage Listing Classes") . "\">Manage Listing Classes</a>";

            //$tags["LIST_CLASSES"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Manage Listing Classes"), "LISTINGS_op");
            $tags["CLASSES_HELP"] = $_SESSION["OBJ_help"]->show_link("phpwslistings", "classes");
        }


        if($_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_element")){
            //$tags["NEW_ELEMENT"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Create Form Element"), "LISTINGS_op");
            $tags["NEW_ELEMENT"] = "<a href=\"index.php?module=phpwslistings&LISTINGS_op=" . $_SESSION["translate"]->it("Create Form Element") . "\">Create Form Element</a>";

            //$tags["LIST_ELEMENTS"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Manage Form Elements"), "LISTINGS_op");
            $tags["LIST_ELEMENTS"] = "<a href=\"index.php?module=phpwslistings&LISTINGS_op=" . $_SESSION["translate"]->it("Manage Form Elements") . "\">Manage Form Elements</a>";
        }

        if($_SESSION["OBJ_user"]->allow_access("phpwslistings", "modify_settings")) {
            //$tags["SETTINGS"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Settings"), "LISTINGS_op");
            $tags["SETTINGS"] = "<a href=\"index.php?module=phpwslistings&LISTINGS_op=" . $_SESSION["translate"]->it("Settings") . "\">Settings</a>";
        }

        if($_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_agent")){
            //$tags["LIST_AGENTS"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Manage Agents"), "LISTINGS_op");
            $tags["LIST_AGENTS"] = "<a href=\"index.php?module=phpwslistings&LISTINGS_op=" . $_SESSION["translate"]->it("Manage Agents") . "\">Manage Agents</a>";
        } else {
            //$tags["LIST_AGENTS"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Edit Agent"), "LISTINGS_op");
            $tags["LIST_AGENTS"] = "<a href=\"index.php?module=phpwslistings&LISTINGS_op=" . $_SESSION["translate"]->it("Edit Agent") . "\">Edit Agent</a>";

	        // $this->_agents[] =  PHPWS_Form::makeForm("phpwslistings_agents", "index.php", $elements);

        }

        if($_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_agency")) {
            //$tags["NEW_AGENCY"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Create Agency"), "LISTINGS_op");
            $tags["NEW_AGENCY"] = "<a href=\"index.php?module=phpwslistings&LISTINGS_op=" . $_SESSION["translate"]->it("Create Agency") . "\">Create Agency</a>";

            //$tags["LIST_AGENCIES"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Manage Agencies"), "LISTINGS_op");
            $tags["LIST_AGENCIES"] = "<a href=\"index.php?module=phpwslistings&LISTINGS_op=" . $_SESSION["translate"]->it("Manage Agencies") . "\">Manage Agencies</a>";
        }



    $elements[0] = PHPWS_Template::processTemplate($tags, "phpwslistings", "menu.tpl");
    $elements[0] .= PHPWS_Form::formHidden("module", "phpwslistings");

    $title = $_SESSION["translate"]->it("Listings Menu");
    $content = PHPWS_Form::makeForm("phpwslistings_menu", "index.php", $elements);

    $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
  }


    function listListings() {
        //if(!isset($_REQUEST["LISTING_paging"])) {
            $this->_listings = array();
            $match_column = NULL;
            $match_value = NULL;
            if(!$_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_listing")) {
                $match_column = "agent_id";
                $match_value = $_SESSION["OBJ_user"]->user_id;
                $match_where = "WHERE $match_column='$match_value'";
            }
            $sql_statement = "SELECT id,title,creationdate,agent_id,active FROM mod_listings $match_where ORDER BY creationdate ASC";
            $result = $GLOBALS["core"]->query($sql_statement, TRUE);

            if($result) {
              while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)){
                  extract($row);
                    if(($_SESSION["OBJ_user"]->user_id != $row["agent_id"]) && !$_SESSION["OBJ_user"]->allow_access("phpwslistings")) {
                        $agent = new SEILER_ListingAgent($row["agent_id"]);
                        if(!$agent->_active)
                            continue;
                    }
                    
	                $tags["TITLE"] = "<a href=\"index.php?module=phpwslistings&amp;LISTING_id=" . $row["id"] . "&amp;LISTINGS_op=view\">" . $row["title"] . "</a>";

	                if($_SESSION["OBJ_user"]->allow_access("phpwslistings", "activate_listing") || ($row["agent_id"] == $_SESSION["OBJ_user"]->user_id))
                        if($row["active"])
	                        $tags["SHOW_HIDE"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Hide"), "LISTINGS_op");
                        else
	                        $tags["SHOW_HIDE"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Show"), "LISTINGS_op");
	                else
	                    $tags["SHOW_HIDE"] = NULL;

	                if($_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_listing") || ($row["agent_id"] == $_SESSION["OBJ_user"]->user_id))
	                    $tags["EDIT"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Edit"), "LISTINGS_op");
	                else
	                    $tags["EDIT"] = NULL;

	                if($_SESSION["OBJ_user"]->allow_access("phpwslistings", "delete_listing") || ($row["agent_id"] == $_SESSION["OBJ_user"]->user_id))
	                    $tags["DELETE"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Delete"), "LISTINGS_op");
	                else
	                    $tags["DELETE"] = NULL;

	                $elements[0] = PHPWS_Form::formHidden("module", "phpwslistings");
	                $elements[0] .= PHPWS_Form::formHidden("LISTING_id", $row["id"]);
	                $elements[0] .= PHPWS_Template::processTemplate($tags, "phpwslistings", "list_item.tpl");

	                $this->_listings[] =  PHPWS_Form::makeForm("phpwslistings_menu", "index.php", $elements);
	            }
            }
        //}

        if(count($this->_listings)) {
            $pageData = PHPWS_Array::paginateDataArray($this->_listings, "./index.php?module=phpwslistings&amp;LISTINGS_op=" . $_SESSION["translate"]->it("Manage Listings") . "&amp;LISTING_paging=1", 20, TRUE, array("<b>[ ", " ]</b>"), NULL, 10);

            $tags = array();
            $tags["TITLE"] = "<b>" . $_SESSION["translate"]->it("Title") . "</b>";
            $tags["CREATIONDATE"] = "<b>" . $_SESSION["translate"]->it("Date Listed") . "</b>";
            $tags["USERID"] = "<b>" . $_SESSION["translate"]->it("Listed by") . "</b>";
            $tags["SHOW_HIDE"] = "<b>" . $_SESSION["translate"]->it("Action") . "</b>";

            $tags2["LIST_ITEMS"] = PHPWS_Template::processTemplate($tags, "phpwslistings", "list_item.tpl");
            $tags2["LIST_ITEMS"] .= $pageData[0];

            $content = "";
            if(sizeof($this->_listings) > 20)
	            $content .= "<center>$pageData[1]<br />$pageData[2] " . $_SESSION["translate"]->it("Listings") . "</center><br />";

            $content .= PHPWS_Template::processTemplate($tags2, "phpwslistings", "list.tpl");
        } else {
            $content = $_SESSION["translate"]->it("No listings found!");
        }

        $title = $_SESSION["translate"]->it("Listing List");
        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
    }// END FUNC listListings()


    function getSettings() {
        $tags["PAGINATE_LIMIT_LABEL"] = $_SESSION["translate"]->it("Features / Results Per Page");
        $tags["PAGINATE_LIMIT"] = PHPWS_Form::formTextField("LISTING_paginateLimit", $this->_paginateLimit, 5, 5);
        $tags["USE_EXPIRATION"] = PHPWS_Form::formCheckBox("LISTING_useExpiration", 1, $this->_useExpiration, NULL, $_SESSION["translate"]->it("Use Expiration Date"));
        $tags["SHOW_MENU"] = PHPWS_Form::formCheckBox("LISTING_showMenu", 1, $this->_showMenu, NULL, $_SESSION["translate"]->it("Show Listings Menu"));
        $tags["SHOW_FEATURE"] = PHPWS_Form::formCheckBox("LISTING_showFeature", 1, $this->_showFeature, NULL, $_SESSION["translate"]->it("Show Feature Listings"));
        $tags["SHOW_BLOCK"] = PHPWS_Form::formCheckBox("LISTING_showBlock", 1, $this->_showBlock, NULL, $_SESSION["translate"]->it("Show Block"));
        $tags["NUM_BLOCK_LABEL"] = $_SESSION["translate"]->it("Number of listings in block");
        $tags["NUM_BLOCK"] = PHPWS_Form::formTextField("LISTING_numBlock", $this->_numBlock, 5, 5);
        $tags["BLOCK_IMAGES_ONLY"] = PHPWS_Form::formCheckBox("LISTING_blockImagesOnly", 1, $this->_blockImagesOnly, NULL, $_SESSION["translate"]->it("Display only listings with images in block?"));
        $tags["IMAGESIZE_LIMIT_LABEL"] = $_SESSION["translate"]->it("Image size limit (kb)");
        $tags["IMAGESIZE_LIMIT"] = PHPWS_Form::formTextField("LISTING_imageSizeLimit", $this->_imageSizeLimit, 10, 10);
        $tags["IMAGE_MAX_WIDTH_LABEL"] = $_SESSION["translate"]->it("Maximum Image Width (pixels)");
        $tags["IMAGE_MAX_WIDTH"] = PHPWS_Form::formTextField("LISTING_imageMaxWidth", $this->_imageMaxWidth, 10, 10);
        $tags["IMAGE_MAX_HEIGHT_LABEL"] = $_SESSION["translate"]->it("Maximum Image Height (pixels)");
        $tags["IMAGE_MAX_HEIGHT"] = PHPWS_Form::formTextField("LISTING_imageMaxHeight", $this->_imageMaxHeight, 10, 10);
        $tags["IMAGE_WIDTH_REDIM_LABEL"] = $_SESSION["translate"]->it("Image Width Resize (pixels)");
        $tags["IMAGE_WIDTH_REDIM"] = PHPWS_Form::formTextField("LISTING_imageWidthRedim", $this->_imageWidthRedim, 10, 10);
        $tags["IMAGE_HEIGHT_REDIM_LABEL"] = $_SESSION["translate"]->it("Image Height Resize (pixels)");
        $tags["IMAGE_HEIGHT_REDIM"] = PHPWS_Form::formTextField("LISTING_imageHeightRedim", $this->_imageHeightRedim, 10, 10);
        $tags["LISTING_IMAGE_LIMIT_LABEL"] = $_SESSION["translate"]->it("Maximum Images Per Listing");
        $tags["LISTING_IMAGE_LIMIT"] = PHPWS_Form::formTextField("LISTING_listingImageLimit", $this->_listingImageLimit, 5, 5);
        $tags["FEATURE_TITLE_LABEL"] = $_SESSION["translate"]->it("Featured Listings Title");
        $tags["FEATURE_TITLE"] = PHPWS_Form::formTextField("LISTING_featureTitle", $this->_featureTitle, 20, 80);
        $tags["BLOCK_TITLE_LABEL"] = $_SESSION["translate"]->it("Random Block Title");
        $tags["BLOCK_TITLE"] = PHPWS_Form::formTextField("LISTING_blockTitle", $this->_blockTitle, 20, 80);
        $tags["CUSTOM_SETTINGS_LABEL"] = $_SESSION["translate"]->it("Advanced Layout Settings");
        $tags["ADVANCED_LAYOUT_HELP"] = $_SESSION["OBJ_help"]->show_link("phpwslistings", "advanced_layout");
        $tags["USE_CUSTOM_LAYOUT"] = PHPWS_Form::formCheckBox("LISTING_customLayout", 1, $this->_customLayout, NULL, $_SESSION["translate"]->it("Use Custom Layout"));
        $tags["SUBMIT"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Save Settings"), "LISTINGS_op");

        $elements[0] = PHPWS_Form::formHidden("module", "phpwslistings");
        $elements[0] .= PHPWS_Template::processTemplate($tags, "phpwslistings", "settings.tpl");

        $title = $_SESSION["translate"]->it("Listing Settings");
        $content = PHPWS_Form::makeForm("phpwslistings_settings", "index.php", $elements);

        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
    }


    function setSettings() {
        if(!$_SESSION["OBJ_user"]->allow_access("phpwslistings", "modify_settings"))
            return;

        $this->_paginateLimit = $_POST["LISTING_paginateLimit"];
        $this->_customLayout = $_POST["LISTING_customLayout"];
        $this->_useExpiration = $_POST["LISTING_useExpiration"];
        $this->_showMenu = $_POST["LISTING_showMenu"];
        $this->_showFeature = $_POST["LISTING_showFeature"];
        $this->_showBlock = $_POST["LISTING_showBlock"];
        $this->_numBlock = $_POST["LISTING_numBlock"];
        if(isset($_POST["LISTING_blockImagesOnly"]))
            $this->_blockImagesOnly = $_POST["LISTING_blockImagesOnly"];
        else
            $this->_blockImagesOnly = 0;
        $this->_imageSizeLimit = $_POST["LISTING_imageSizeLimit"];
        $this->_imageMaxWidth = $_POST["LISTING_imageMaxWidth"];
        $this->_imageMaxHeight = $_POST["LISTING_imageMaxHeight"];
        $this->_imageWidthRedim = $_POST["LISTING_imageWidthRedim"];
        $this->_imageHeightRedim = $_POST["LISTING_imageHeightRedim"];
        $this->_listingImageLimit = $_POST["LISTING_listingImageLimit"];
        $this->_featureTitle = $_POST["LISTING_featureTitle"];
        $this->_blockTitle = $_POST["LISTING_blockTitle"];

        $data["paginate_limit"] = $this->_paginateLimit;
        $data["use_expiration"] = $this->_useExpiration;
        $data["show_menu"] = $this->_showMenu;
        $data["show_feature"] = $this->_showFeature;
        $data["show_block"] = $this->_showBlock;
        $data["num_block"] = $this->_numBlock;
        $data["block_images_only"] = $this->_blockImagesOnly;
        $data["imagesize_limit"] = $this->_imageSizeLimit;
        $data["image_max_width"] = $this->_imageMaxWidth;
        $data["image_max_height"] = $this->_imageMaxHeight;
        $data["image_width_redim"] = $this->_imageWidthRedim;
        $data["image_height_redim"] = $this->_imageHeightRedim;
        $data["listing_image_limit"] = $this->_listingImageLimit;
        $data["feature_title"] = $this->_featureTitle;
        $data["block_title"] = $this->_blockTitle;
        $data["custom_layout"] = $this->_customLayout;
    
        $GLOBALS["core"]->sqlUpdate($data, "mod_listings_settings");

        $title = $_SESSION["translate"]->it("Settings Saved!");
        $content = $_SESSION["translate"]->it("Your settings have successfully been saved!");

        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
    }


    function isAllowedImageType($type) {
        return in_array($type, $this->_allowedImageTypes);
    }



    function listAgents() {
        //if(!isset($_REQUEST["LISTING_paging"])) {
            $this->_agents = array();
            $result = $GLOBALS["core"]->sqlSelect("mod_users", NULL, NULL, "username ASC");

            $sresult = array();
            if($result) {
                foreach($result as $row) {
                    $user = new PHPWS_User((int)$row["user_id"]);
                    $lname = $user->getUserVar("last_name", $user->user_id, "phpwslistings");

                    // Stick no-names at the bottom
                    if(empty($lname))
                        $lname = "_" . $user->username;
                    $sort = $lname . $user->user_id;
                    $sresult[$sort] = $row;
                }
                ksort($sresult);
                reset($sresult);
                $result = array();
                foreach ($sresult as $row)
                    array_push($result, $row);
            }

            if($result) {
	            foreach($result as $row) {
                    $user = new PHPWS_User((int)$row["user_id"]);

                    // Skip inactive unless admin
                    if(!$user->getUserVar("active", $user->user_id, "phpwslistings") && !$_SESSION["OBJ_user"]->allow_access("phpwslistings"))
                        continue;

                    $uname = $user->username;
                    $fname = $user->getUserVar("first_name", $user->user_id, "phpwslistings");
                    $lname = $user->getUserVar("last_name", $user->user_id, "phpwslistings");
                    if(!empty($fname) && !empty($lname))
                        $name = $lname . ", " . $fname;
                    else
                        $name = $uname;
	                $tags["NAME"] = "<a href=\"index.php?module=phpwslistings&amp;AGENT_id=" . $user->user_id . "&amp;LISTINGS_user_op=viewagent\">" . $name . "</a>";

                    if($user->getUserVar("agency_id",$user->user_id,"phpwslistings")) {
                        $agency = new SEILER_ListingAgency($user->getUserVar("agency_id",$user->user_id,"phpwslistings"));

                        if( !is_null( $agency->getName() ) )
                            $tags["AGENCY"] = "<a href=\"index.php?module=phpwslistings&amp;AGENCY_id=" . $agency->_id . "&amp;LISTINGS_user_op=viewagency\">" . $agency->getName() . "</a>";
                        else
                            $tags["AGENCY"] = "N/A";
                    } else
                        $tags["AGENCY"] = "N/A";

                    if($user->getUserVar("phone",$user->user_id,"phpwslistings"))
                        $tags["PHONE"] = $user->getUserVar("phone",$user->user_id,"phpwslistings");
                    else
                        $tags["PHONE"] = "N/A";

	                if($_SESSION["OBJ_user"]->allow_access("phpwslistings", "activate_agent"))
                        if($user->getUserVar("active",$user->user_id,"phpwslistings"))
	                        $tags["ACTIVATE"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Deactivate Agent"), "LISTINGS_op");
                        else
	                        $tags["ACTIVATE"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Activate Agent"), "LISTINGS_op");
	                else
	                    $tags["ACTIVATE"] = NULL;

	                if($_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_agent"))
	                    $tags["EDIT"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Edit Agent"), "LISTINGS_op");
	                else
	                    $tags["EDIT"] = NULL;


	                $elements[0] = PHPWS_Form::formHidden("module", "phpwslistings");
	                $elements[0] .= PHPWS_Form::formHidden("AGENT_id", $user->user_id);
	                $elements[0] .= PHPWS_Template::processTemplate($tags, "phpwslistings", "list_agent.tpl");

	                $this->_agents[] =  PHPWS_Form::makeForm("phpwslistings_agents", "index.php", $elements);
	            }
            }
        //}

        if(count($this->_agents)) {
            $link = "./index.php?module=phpwslistings&amp;LISTINGS_op=" . $_SESSION["translate"]->it("Manage Agents") . "&amp;LISTING_paging=1";
            $pageData = PHPWS_Array::paginateDataArray($this->_agents, $link, 20, TRUE, array("<b>[ ", " ]</b>"), NULL, 10);

            $tags = array();
            $tags["NAME"] = "<b>" . $_SESSION["translate"]->it("Name") . "</b>";
            $tags["AGENCY"] = "<b>" . $_SESSION["translate"]->it("Agency") . "</b>";
            $tags["PHONE"] = "<b>" . $_SESSION["translate"]->it("Phone") . "</b>";
	        if($_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_agent"))
                $tags["EDIT"] = "<b>" . $_SESSION["translate"]->it("Action") . "</b>";

            $tags2["LIST_ITEMS"] = PHPWS_Template::processTemplate($tags, "phpwslistings", "list_agent.tpl");
            $tags2["LIST_ITEMS"] .= $pageData[0];

            $content = "";
            if(sizeof($this->_agents) > 20)
	            $content .= "<center>$pageData[1]<br />$pageData[2] " . $_SESSION["translate"]->it("Agents") . "</center><br />";

            $content .= PHPWS_Template::processTemplate($tags2, "phpwslistings", "list.tpl");
        } else {
            $content = $_SESSION["translate"]->it("No agents found!");
        }

        $title = $_SESSION["translate"]->it("Agent List");
        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
    }// END FUNC listAgents()


    function listClasses() {
        //if(!isset($_REQUEST["LISTING_paging"])) {
            $this->classes = array();

	        foreach($this->_classes as $class) {
	            $tags["NAME"] = $class->_name;

                if($_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_class"))
                    $tags["EDIT"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Edit Listing Class"), "LISTINGS_op");
                else
                    $tags["EDIT"] = NULL;


                if($_SESSION["OBJ_user"]->allow_access("phpwslistings", "delete_class"))
                    $tags["DELETE"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Delete Listing Class"), "LISTINGS_op");
                else
                    $tags["DELETE"] = NULL;

                $elements[0] = PHPWS_Form::formHidden("module", "phpwslistings");
                $elements[0] .= PHPWS_Form::formHidden("CLASS_id", $class->_id);
                $elements[0] .= PHPWS_Template::processTemplate($tags, "phpwslistings", "list_class.tpl");

                $this->classes[] =  PHPWS_Form::makeForm("phpwslistings_classes", "index.php", $elements);
            }
        //}

        if(count($this->classes)) {
            $pageData = PHPWS_Array::paginateDataArray($this->classes, "./index.php?module=phpwslistings&amp;LISTINGS_op=" . $_SESSION["translate"]->it("Manage Listing Classes") . "&amp;LISTING_paging=1", 20, TRUE, array("<b>[ ", " ]</b>"), NULL, 10);

            $tags = array();
            $tags["NAME"] = "<b>" . $_SESSION["translate"]->it("Name") . "</b>";
            $tags["EDIT"] = "<b>" . $_SESSION["translate"]->it("Action") . "</b>";

            $tags2["LIST_ITEMS"] = PHPWS_Template::processTemplate($tags, "phpwslistings", "list_class.tpl");
            $tags2["LIST_ITEMS"] .= $pageData[0];

            $content = "";
            if(sizeof($this->classes) > 20)
	            $content .= "<center>$pageData[1]<br />$pageData[2] " . $_SESSION["translate"]->it("Listing Classes") . "</center><br />";

            $content .= PHPWS_Template::processTemplate($tags2, "phpwslistings", "list.tpl");
        } else {
            $content = $_SESSION["translate"]->it("No listing classes found!");
        }

        $title = $_SESSION["translate"]->it("Listing Class List");
        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
    }// END FUNC listClasses()


    function listFormElements() {
        //if(!isset($_REQUEST["LISTING_paging"])) {
            $this->formelements = array();

	        foreach($this->_formelements as $element) {
	            $tags["FIELD_NAME"] = "<a href=\"index.php?module=phpwslistings&amp;ELEMENT_id=" . $element->_id . "&amp;LISTINGS_op=viewelement\">" . $element->_field_name . "</a>";
	            $tags["FIELD_TYPE"] = $element->_field_type;
                $class = new SEILER_ListingClass($element->_class_id);
	            $tags["CLASS"] = $class->_name;

                if($_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_element"))
                    $tags["EDIT"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Edit Form Element"), "LISTINGS_op");
                else
                    $tags["EDIT"] = NULL;


                if($_SESSION["OBJ_user"]->allow_access("phpwslistings", "delete_element"))
                    $tags["DELETE"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Delete Form Element"), "LISTINGS_op");
                else
                    $tags["DELETE"] = NULL;

                $elements[0] = PHPWS_Form::formHidden("module", "phpwslistings");
                $elements[0] .= PHPWS_Form::formHidden("ELEMENT_id", $element->_id);
                $elements[0] .= PHPWS_Template::processTemplate($tags, "phpwslistings", "list_element.tpl");

                $this->formelements[] =  PHPWS_Form::makeForm("phpwslistings_elements", "index.php", $elements);
            }
        //}

        if(count($this->formelements)) {
            $pageData = PHPWS_Array::paginateDataArray($this->formelements, "./index.php?module=phpwslistings&amp;LISTINGS_op=" . $_SESSION["translate"]->it("Manage Form Elements") . "&amp;LISTING_paging=1", 20, TRUE, array("<b>[ ", " ]</b>"), NULL, 10);

            $tags = array();
            $tags["FIELD_NAME"] = "<b>" . $_SESSION["translate"]->it("Field Name") . "</b>";
            $tags["FIELD_TYPE"] = "<b>" . $_SESSION["translate"]->it("Field Type") . "</b>";
            $tags["CLASS"] = "<b>" . $_SESSION["translate"]->it("Class") . "</b>";
            $tags["EDIT"] = "<b>" . $_SESSION["translate"]->it("Action") . "</b>";

            $tags2["LIST_ITEMS"] = PHPWS_Template::processTemplate($tags, "phpwslistings", "list_element.tpl");
            $tags2["LIST_ITEMS"] .= $pageData[0];

            $content = "";
            if(sizeof($this->formelements) > 20)
	            $content .= "<center>$pageData[1]<br />$pageData[2] " . $_SESSION["translate"]->it("Form Elements") . "</center><br />";

            $content .= PHPWS_Template::processTemplate($tags2, "phpwslistings", "list.tpl");
        } else {
            $content = $_SESSION["translate"]->it("No form elements found!");
        }

        $title = $_SESSION["translate"]->it("Form Element List");
        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
    }// END FUNC listFormElements()


    function listAgencies() {
        //if(!isset($_REQUEST["LISTING_paging"])) {
            $this->_agencies = array();
            $mc = NULL;
            $mv = NULL;
	        if(!$_SESSION["OBJ_user"]->allow_access("phpwslistings")){
                $mc = "active";
                $mv = "1";
            }
            $result = $GLOBALS["core"]->sqlSelect("mod_listings_agencies", $mc, $mv, "name ASC");

            if($result) {
                foreach($result as $row) {
                    $tags["NAME"] = "<a href=\"index.php?module=phpwslistings&amp;AGENCY_id=" . $row["id"] . "&amp;LISTINGS_op=viewagency\">" . $row["name"] . "</a>";

                    if($_SESSION["OBJ_user"]->allow_access("phpwslistings", "activate_agency"))
                        if($row["active"])
                            $tags["ACTIVATE"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Hide Agency"), "LISTINGS_op");
                        else
                            $tags["ACTIVATE"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Show Agency"), "LISTINGS_op");
                    else
                        $tags["ACTIVATE"] = NULL;

                    if($_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_agency"))
                        $tags["EDIT"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Edit Agency"), "LISTINGS_op");
                    else
                        $tags["EDIT"] = NULL;

                    if($_SESSION["OBJ_user"]->allow_access("phpwslistings", "delete_agency"))
                        $tags["DELETE"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Delete Agency"), "LISTINGS_op");
                    else
                        $tags["DELETE"] = NULL;

                    $elements[0] = PHPWS_Form::formHidden("module", "phpwslistings");
                    $elements[0] .= PHPWS_Form::formHidden("AGENCY_id", $row["id"]);
                    $elements[0] .= PHPWS_Template::processTemplate($tags, "phpwslistings", "list_agency.tpl");

                    $this->_agencies[] =  PHPWS_Form::makeForm("phpwslistings_menu", "index.php", $elements);
                }
            }
        //}

        if(count($this->_agencies)) {
            $pageData = PHPWS_Array::paginateDataArray($this->_agencies, "./index.php?module=phpwslistings&amp;LISTINGS_op=" . $_SESSION["translate"]->it("Manage Agencies") . "&amp;LISTING_paging=1", 20, TRUE, array("<b>[ ", " ]</b>"), NULL, 10);

            $tags = array();
            $tags["NAME"] = "<b>" . $_SESSION["translate"]->it("Name") . "</b>";
	        if($_SESSION["OBJ_user"]->allow_access("phpwslistings"))
                $tags["ACTIVATE"] = "<b>" . $_SESSION["translate"]->it("Action") . "</b>";

            $tags2["LIST_ITEMS"] = PHPWS_Template::processTemplate($tags, "phpwslistings", "list_agency.tpl");
            $tags2["LIST_ITEMS"] .= $pageData[0];

            $content = "";
            if(sizeof($this->_agencies) > 20)
                $content .= "<center>$pageData[1]<br />$pageData[2] " . $_SESSION["translate"]->it("Agencies") . "</center><br />";

            $content .= PHPWS_Template::processTemplate($tags2, "phpwslistings", "list.tpl");
        } else {
            $content = $_SESSION["translate"]->it("No agencies found!");
        }

        $title = $_SESSION["translate"]->it("Agency List");
        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
    }// END FUNC listAgencies()

    /**
    * Displays agent mini-view
    *
    * Displays agent list with thumbnails and short description
    */
    function showAgents($agency_id=NULL) {

	    $GLOBALS["CNT_phpwslistings"]["title"] = $_SESSION["translate"]->it("View Agents");

        /*
        $match_columns = array("active" => 1,"sold" => 0);
        if($feature)
            $match_columns["feature"] = 1;
        elseif(!is_null($class_id)) {
            $match_columns["class_id"] = $class_id;
        }
        */

        $result = $GLOBALS["core"]->sqlSelect("mod_users", NULL, NULL, "username ASC");
        $sresult = array();
        if($result) {
            foreach($result as $row) {
                $user = new PHPWS_User((int)$row["user_id"]);
                $lname = $user->getUserVar("last_name", $user->user_id, "phpwslistings");

                // Stick no-names at the bottom
                if(empty($lname))
                    $lname = "_" . $user->username;
                $sort = $lname . $user->user_id;
                $sresult[$sort] = $row;
            }
            ksort($sresult);
            reset($sresult);
            $result = array();
            foreach ($sresult as $row)
                array_push($result, $row);
        }

        $matches = array();
        if($result) {
            foreach($result as $row) {
                $agent = new SEILER_ListingAgent((int)$row["user_id"]);
                if(!$agent->_active)
                    continue;
                if(!is_null($agency_id))
                    if($agency_id != $agent->_agency_id)
                        continue;
                array_push($matches, $agent);
            }
        }

        // Paginate
        if(count($matches)) {
            $link = "./index.php?module=phpwslistings&amp;LISTINGS_user_op=listagents";
            $pageData = $this->paginateListingArray($matches, $link, TRUE, array("<b>[ ", " ]</b>"), NULL, 10);

            if(sizeof($matches) > $this->_paginateLimit)
                $GLOBALS["CNT_phpwslistings"]["content"] = "<center>$pageData[1]<br />$pageData[2] " . $_SESSION["translate"]->it("View Agents") . "</center><br />";

            for($i=0; $i<count($pageData[0]); $i++) {
                if($i)
                    $GLOBALS["CNT_phpwslistings"]["content"] .= "<hr size=\"1\" noshade />";
                $pageData[0][$i]->view("small");
            }

        } else
            $GLOBALS["CNT_phpwslistings"]["content"] = $_SESSION["translate"]->it("No agents found!");
    }// END FUNC showAgents

    /**
    * Searches listing
    *
    * Searches listings for criteria matching on REQUEST
    */
    function search() {
	    $GLOBALS["CNT_phpwslistings"]["title"] = $_SESSION["translate"]->it("Search Results");
        if($this->_useExpiration) { 
            $where["expired"] = "&& expiration > " . date('Y-m-d');
        }
        if(isset($_REQUEST["LISTING_agent_id"]) && !empty($_REQUEST["LISTING_agent_id"])){
            $page["agtid"] = "&amp;LISTING_agent_id=" . $_REQUEST["LISTING_agent_id"];
            $where["agtid"] = "&& agent_id = " . $_REQUEST["LISTING_agent_id"];
         }
        if(isset($_REQUEST["LISTING_agency_id"]) && !empty($_REQUEST["LISTING_agency_id"])){
            $page["agencyid"] = "&amp;LISTING_agency_id=" . $_REQUEST["LISTING_agency_id"];
            $agency_result = $GLOBALS["core"]->sqlSelect("mod_user_uservar", "varName", "agency_id");
            $where["agencyid"] = "&&";
            foreach($agency_result as $row) {
                if($row["varValue"] == $_REQUEST["LISTING_agency_id"])
                    $where["agencyid"] .= " agent_id = '" . $row["user_id"] . "' ||";
            }
                $where["agencyid"] = rtrim($where["agencyid"], " ||");
        }
        if(isset($_REQUEST["LISTING_title"]) && !empty($_REQUEST["LISTING_title"])){
            $page["title"] = "&amp;LISTING_title=" . $_REQUEST["LISTING_title"];
            $where["title"] = "&& ( title like '%" . $_REQUEST["LISTING_title"] . "%' )";
        }
        if(isset($_REQUEST["LISTING_minprice"]) && !empty($_REQUEST["LISTING_minprice"])){
            $page["minprice"] = "&amp;LISTING_minprice=" . $_REQUEST["LISTING_minprice"];
            $where["minprice"] = "&& price >= " . $_REQUEST["LISTING_minprice"];
        }
        if(isset($_REQUEST["LISTING_maxprice"]) && !empty($_REQUEST["LISTING_maxprice"])){
            $page["maxprice"] = "&amp;LISTING_maxprice=" . $_REQUEST["LISTING_maxprice"];
            $where["maxprice"] = "&& price <= " . $_REQUEST["LISTING_maxprice"];
        }

        // Cycle through formelements
        for($i=0; $i<count($this->_formelements); $i++) {
            $value = '';
            $name = $this->_formelements[$i]->_field_name;
            if(isset($_REQUEST["LISTING_" . $name]) && !empty($_REQUEST["LISTING_" . $name])):
                $check_empty = "";
                if(($this->_formelements[$i]->_field_type == "multiple") ||
                        ($this->_formelements[$i]->_field_type == "checkbox")){
                    if(!is_array($_REQUEST["LISTING_" . $name])):
                        $value = $this->unhtmlentities($_REQUEST["LISTING_" . $name]);
                        $criteria[$name] = unserialize($value);
                    else:
                        foreach($_REQUEST["LISTING_" . $name] as $val){
                            $check_empty .= $val;
                        }
                        if(!empty($check_empty))
                            $criteria[$name] = $_REQUEST["LISTING_" . $name];
                    endif;
                }else{
                    if(!is_array($_REQUEST["LISTING_" . $name])):
                        $criteria[$name] = $_REQUEST["LISTING_" . $name];
                    else:
                        foreach($_REQUEST["LISTING_" . $name] as $val){
                            $check_empty .= $val;
                        }
                        if(!empty($check_empty))
                            $criteria[$name] = $_REQUEST["LISTING_" . $name];
                    endif;
                }
            endif;
        }

        if(isset($_REQUEST["LISTING_CLASS_id"]) && !empty($_REQUEST["LISTING_CLASS_id"])){
            $sql_statement = "SELECT id,title,price,creationdate,agent_id,listelements,expiration FROM mod_listings WHERE active=1 && sold=0 && class_id = " . $_REQUEST["LISTING_CLASS_id"] . " " . $where["agtid"] . $where["agencyid"] . $where["minprice"] . $where["maxprice"] . $where["title"] . $where["expired"];
        }else{        
            $sql_statement = "SELECT id,title,price,creationdate,agent_id,listelements,expiration FROM mod_listings WHERE active=1 && sold=0 " . $where["agtid"] . $where["agencyid"] . $where["minprice"] . $where["maxprice"] . $where["title"] . $where["expired"];
        }
        $result = $GLOBALS["core"]->query($sql_statement, TRUE);

        $matches = array();
        if($result) {
            while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)){
                extract($row);
                $listing = new SEILER_Listing($row["id"]); 
                $agent = new SEILER_Listing($listing->_agent_id);
                if(isset($criteria)){
	                  if($listing->match($criteria))
                        array_push($matches,$listing);
                }else{
                        array_push($matches,$listing);
                }
            }

            // Sort matches
            if(isset($_REQUEST["LISTING_sortby"])){
                $sortby = $_REQUEST["LISTING_sortby"];
                $page["sortby"] = "&amp;LISTING_sortby=" . $sortby;
            }
            if(isset($_REQUEST["LISTING_sortorder"])){
                $sortorder = $_REQUEST["LISTING_sortorder"];
                $page["sortorder"] = "&amp;LISTING_sortorder=" . $sortorder;
            }
            if(!empty($sortby)) {
                $a = 10000;
                $smatches = array();
                foreach($matches as $listing) {
                    switch($sortby) {
                        case "title":
                            $smatches[$listing->_title . $a++] = $listing;
                            break;
                        case "price":
                            $smatches[$listing->_price . $a++] = $listing;
                            break;
                        case "creationdate":
                            $smatches[$listing->_creationdate . $a++] = $listing;
                            break;
                        default:
                            for($i=0; $i<count($this->_formelements); $i++) {
                                if($sortby == $this->_formelements[$i]->_field_name)
                                    $smatches[$listing->_listelements[$this->_formelements[$i]->_field_name] . $a++] = $listing;
                            }
                    }
                }
    
                if($sortorder == "ASC")
                    ksort($smatches);
                else
                    krsort($smatches);
    
                reset($smatches);
    
                $matches = array();
                foreach ($smatches as $listing)
                    array_push($matches, $listing);
            }

            // Paginate
            if(count($matches)) {
                if(isset($_REQUEST["LISTING_CLASS_id"]) && !empty($_REQUEST["LISTING_CLASS_id"])){
                    $link = "./index.php?module=phpwslistings&LISTINGS_user_op=" . $_SESSION["translate"]->it("Search Listings") . "&amp;LISTING_CLASS_id=" . $_REQUEST["LISTING_CLASS_id"] . $page["agtid"] . $page["agencyid"] . $page["minprice"] . $page["maxprice"] . $page["title"] . $page["sortorder"] . $page["sortby"];
                }else{        
                    $link = "./index.php?module=phpwslistings&LISTINGS_user_op=" . $_SESSION["translate"]->it("Search Listings") . $page["agtid"] . $page["agencyid"] . $page["minprice"] . $page["maxprice"] . $page["title"] . $page["sortorder"] . $page["sortby"];
                }
                if(isset($criteria)){
                    foreach($criteria as $key => $value) {
                        if(!is_array($value)){
                            $link .= "&amp;LISTING_" . $key . "=" . $value;
                        }else{
                            $link .= "&amp;LISTING_" . $key . "=" . htmlspecialchars(serialize($value));
                        }
                    }
                }

                $pageData = $this->paginateListingArray($matches, $link, TRUE, array("<b>[ ", " ]</b>"), NULL, 10);

                if(sizeof($matches) > $this->_paginateLimit)
	                $GLOBALS["CNT_phpwslistings"]["content"] = "<center>$pageData[1]<br />$pageData[2] " . $_SESSION["translate"]->it("Search Results") . "</center><br />";

                for($i=0; $i<count($pageData[0]); $i++) {
                    if($i)
                        $GLOBALS["CNT_phpwslistings"]["content"] .= "<hr size=\"1\" noshade />";
                    $pageData[0][$i]->view("small");
                }
    
            } else {
	            $GLOBALS["CNT_phpwslistings"]["content"] = $_SESSION["translate"]->it("No matches found!");
            }
        }
    }


    function loadFormElements() {
        $this->_formelements = array();
        $tmp_elements = $GLOBALS["core"]->sqlSelect("mod_listings_formelements",NULL,NULL,"class_id,rank");
        if(is_null($tmp_elements)) {
            return TRUE;
        } elseif($tmp_elements == FALSE) {
            return FALSE;
        }

        for($i=0; $i<count($tmp_elements); $i++)
            array_push($this->_formelements, new SEILER_ListingElement($tmp_elements[$i]["id"]));

        return TRUE;
    }

    function unhtmlentities ($string)  {
        $trans_tbl = get_html_translation_table (HTML_ENTITIES);
        $trans_tbl = array_flip ($trans_tbl);
        $ret = strtr ($string, $trans_tbl);
        $ret = preg_replace('/&#(\d+);/me',"chr('\\1')",$ret);
        return str_replace('\\', '', $ret);
    }
    
    function loadSettings() {
        $result = $GLOBALS["core"]->sqlSelect("mod_listings_settings");

        $this->_paginateLimit = $result[0]["paginate_limit"];
        $this->_showMenu = $result[0]["show_menu"];
        $this->_showFeature = $result[0]["show_feature"];
        $this->_showBlock = $result[0]["show_block"];
        $this->_numBlock = $result[0]["num_block"];
        $this->_blockImagesOnly = $result[0]["block_images_only"];
        $this->_imageSizeLimit = $result[0]["imagesize_limit"];
        $this->_imageMaxWidth = $result[0]["image_max_width"];
        $this->_imageMaxHeight = $result[0]["image_max_height"];
        $this->_imageWidthRedim = $result[0]["image_width_redim"];
        $this->_imageHeightRedim = $result[0]["image_height_redim"];
        $this->_listingImageLimit = $result[0]["listing_image_limit"];
        $this->_featureTitle = $result[0]["feature_title"];
        $this->_blockTitle = $result[0]["block_title"];
        $this->_useExpiration = $result[0]["use_expiration"];
        $this->_customLayout = $result[0]["custom_layout"];
    }


    function loadClasses() {
        $this->_classes = array();
        $tmp_classes = $GLOBALS["core"]->sqlSelect("mod_listings_classes",NULL,NULL,"name");

        if(is_null($tmp_classes)) {
            return TRUE;
        } elseif($tmp_classes == FALSE) {
            return FALSE;
        }

        for($i=0; $i<count($tmp_classes); $i++)
            array_push($this->_classes, new SEILER_ListingClass($tmp_classes[$i]["id"]));

        return TRUE;
    }


    function &getClasses() {
        return $this->_classes;
    }


    function &getFormElements() {
        return $this->_formelements;
    }

    /**
    * paginateListingArray
    *
    * This function will paginate an array of data. While using this function remember to always pass it the same content array
    * and DO NOT alter array during usage unless you are starting back at zero.
    *
    * This function is based on phpws core's paginateDataArray, but without
    * the guff.
    *
    * @author Don Seiler <don@seier.us>
    * @param  array    $content        Rows of data to be displayed
    * @param  integer  $default_limit  Number of items to show per page
    * @param  boolean  $make_sections  Flag controls weather section links show up
    * @param  resource $curr_sec_decor HTML to decorate the current section
    * @param  string   $link_class     Style sheet class to use for navigation links
    * @param  integer  $break_point    Number of sections at which the section display will insert ... to show range
    * @return array 0=>string of rows to be displayed, 1=>navigation links for the paginated data, 2=>current section information
    * @access public
    */

    function paginateListingArray($content, $link_back, $make_sections=FALSE, $curr_sec_decor = array("<b>[ ", " ]</b>"), $link_class=NULL, $break_point=20){
    
        $limit = $this->_paginateLimit;
    
        if(isset($_REQUEST['PLA_start'])){
            $start = $_REQUEST['PLA_start'];
        } else {
            $start = 0;
        }
    
        if(isset($_REQUEST['PLA_section'])){
            $current_section = $_REQUEST['PLA_section'];
        } else {
            $current_section = 1;
        }
  
        if(is_array($content)){
            $numrows = count($content);
            $sections = ceil($numrows / $limit);
            // $content_keys = array_keys($content);
            $array_of_items = array();
            $nav_links = "";
            $item_count = 0;
            $pad = 3;
      
            /*
            reset($content_keys);
            for($x = 0; $x < $start; $x++){
                next($content_keys);
            }
            */
            //while((list($content_key, $content_value) = each($content_keys)) && (($item_count < $limit) && (($start + $item_count) < $numrows ))){
            while(($item_count < $limit) && (($start + $item_count) < $numrows )){
                $index = $start + $item_count;
                array_push($array_of_items, $content[$index]);
                $item_count++;
            }

            if($start == 0){
                $nav_links = "&#60;&#60;\n";
            } else {
                $nav_links = "<a href=\"" . $link_back . "&#38;PLA_start=" . ($start - $limit) . "&#38;PLA_section=" . ($current_section - 1). "\" class=\"" . $link_class . "\" alt=\"&#60;&#60;\">&#60;&#60;</a>\n";
            }
      
            if($make_sections && ($sections <= $break_point)){
                for($x = 1; $x <= $sections; $x++){
                    if($x == $current_section){
                        $nav_links .= "&#160;" . $curr_sec_decor[0] . $x . $curr_sec_decor[1] . "&#160;\n";
                    } else {
                        $nav_links .= "&#160;<a href=\"" . $link_back . "&#38;PLA_start=" . ($limit * ($x - 1)) . "&#38;PLA_section=" . $x . "\" class=\"" . $link_class . "\" alt=\"" . $x . "\">" . $x . "</a>&#160;\n";
                    }
                }
            } else if($make_sections && ($sections > $break_point)){
                for($x = 1; $x <= $sections; $x++){
                    if($x == $current_section){
                        $nav_links .= "&#160;" . $curr_sec_decor[0] . $x . $curr_sec_decor[1] . "&#160;\n";
                    } else if($x == 1 || $x == 2){
                        $nav_links .= "&#160;<a href=\"" . $link_back . "&#38;PLA_start=" . ($limit * ($x - 1)) . "&#38;PLA_section=" . $x . "\" class=\"" . $link_class . "\" alt=\"" . $x . "\">" . $x . "</a>&#160;\n";
                    } else if(($x == $sections) || ($x == ($sections - 1))){
                        $nav_links .= "&#160;<a href=\"" . $link_back . "&#38;PLA_start=" . ($limit * ($x - 1)) . "&#38;PLA_section=" . $x . "\" class=\"" . $link_class . "\" alt=\"" . $x . "\">" . $x . "</a>&#160;\n";
                    } else if(($current_section == ($x - $pad)) || ($current_section == ($x + $pad))){
                        $nav_links .= "&#160;<b>. . .</b>&#160;";
                    } else if(($current_section > ($x - $pad)) && ($current_section < ($x + $pad))){
                        $nav_links .= "&#160;<a href=\"" . $link_back . "&#38;PLA_start=" . ($limit * ($x - 1)) . "&#38;PLA_section=" . $x . "\" class=\"" . $link_class . "\" alt=\"" . $x . "\">" . $x . "</a>&#160;\n";
                    }
                }
            } else {
                $nav_links .= "&#160;&#160;\n";
            }

            if(($start + $limit) >= $numrows){
                $nav_links .= "&#62;&#62;\n";
                $section_info = ($start + 1) . " - " . ($start + $item_count) . " of " . $numrows . "\n";
            } else {
                $nav_links .= "<a href=\"" . $link_back . "&#38;PLA_start=" . ($start + $limit) . "&#38;PLA_section=" . ($current_section + 1) . "\" class=\"" . $link_class . "\" alt=\"&#62;&#62\">&#62;&#62;</a>\n";
                $section_info = ($start + 1) . " - " . ($start + $limit) . " of " . $numrows . "\n";
            }
      
      
        } else {
            exit("Argument 1 to function paginateDataArray not an array.");
        }
    

        return array(0=>$array_of_items, 1=>$nav_links, 2=>$section_info);

    }// END FUNC paginateListingArray()


    function emailForm() {
        $listing_id = $_REQUEST["LISTING_id"];
        $type = $_REQUEST["EMAIL_type"];
        $listing = new SEILER_Listing($listing_id);
        //$agent = new SEILER_ListingAgent($listing->_agent_id);
        $recip_name = NULL;
        $recip_email = NULL;
        if($type == "agent") {
            $agent = new SEILER_ListingAgent($listing->_agent_id);
            $recip_name = $agent->getName();
            $recip_email = $agent->getEmail();
        }

        $tags["SENDER_NAME_LABEL"] = $_SESSION["translate"]->it("Your name");
        $tags["SENDER_NAME"] = PHPWS_Form::formTextField("EMAIL_sender_name", NULL, 33, 255);
        $tags["SENDER_EMAIL_LABEL"] = $_SESSION["translate"]->it("Your email");
        $tags["SENDER_EMAIL"] = PHPWS_Form::formTextField("EMAIL_sender_email", NULL, 33, 255);
        if($type == "friend") {
            $tags["RECIPIENT_NAME_LABEL"] = $_SESSION["translate"]->it("Friend's name");
            $tags["RECIPIENT_EMAIL_LABEL"] = $_SESSION["translate"]->it("Friend's email");
            $tags["RECIPIENT_NAME"] = PHPWS_Form::formTextField("EMAIL_recipient_name", $recip_name, 33, 255);
            $tags["RECIPIENT_EMAIL"] = PHPWS_Form::formTextField("EMAIL_recipient_email", $recip_email, 33, 255);
        } elseif($type == "agent") {
            $tags["RECIPIENT_NAME_LABEL"] = $_SESSION["translate"]->it("Agent's name");
            $tags["RECIPIENT_EMAIL_LABEL"] = $_SESSION["translate"]->it("Agent's email");
            $tags["RECIPIENT_NAME"] = PHPWS_Form::formHidden("EMAIL_recipient_name", $recip_name) . $recip_name;
            $tags["RECIPIENT_EMAIL"] = PHPWS_Form::formHidden("EMAIL_recipient_email", $recip_email) . $recip_email;
            
        }

        $tags["COMMENTS_LABEL"] = $_SESSION["translate"]->it("Comments");
        $tags["COMMENTS"] = PHPWS_Form::formTextArea("EMAIL_comments", NULL, 5, 40);
        $tags["SUBMIT_BUTTON"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Send Email"), "LISTINGS_user_op");

        $elements[0] = PHPWS_Form::formHidden("module", "phpwslistings");
        $elements[0] .= PHPWS_Form::formHidden("LISTING_id", $listing_id);
        $elements[0] .= PHPWS_Form::formHidden("EMAIL_type", $type);
        $elements[0] .= PHPWS_Template::processTemplate($tags, "phpwslistings", "emailform.tpl");


        $title = $_SESSION["translate"]->it("Send Listing Email for") . " " . $listing->_title;
        $content = PHPWS_Form::makeForm("phpwslistings_email", "index.php", $elements, "post", FALSE, TRUE);
        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
        
    }// END FUNC emailForm


    function sendEmail()
    {
        include('Mail.php');
        $from = "\"" . $_REQUEST["EMAIL_sender_name"] . "\" <" . $_REQUEST["EMAIL_sender_email"] . ">";
        $to = "\"" . $_REQUEST["EMAIL_recipient_name"] . "\" <" . $_REQUEST["EMAIL_recipient_email"] . ">";
        $subject = "Listing from " . $_SESSION["OBJ_layout"]->page_title;
        if($_REQUEST["EMAIL_type"] == "agent")
            $subject = "Listing Inquiry from " . $_REQUEST["EMAIL_sender_name"];

        $link = "http://" . PHPWS_HOME_HTTP . "index.php?module=phpwslistings&LISTINGS_user_op=view&LISTING_id=" . $_REQUEST["LISTING_id"];
        $message = "";
        if($_REQUEST["EMAIL_type"] == "friend")
            $message .= $_REQUEST["EMAIL_sender_name"] . " wanted you to see a listing from our website!\n";
        elseif($_REQUEST["EMAIL_type"] == "agent")
            $message .= $_REQUEST["EMAIL_sender_name"] . ", " . $_REQUEST["EMAIL_sender_email"] . ", requested some more information about the listing found at the link below.\n";
        $message .= "\nPlease visit:\n$link\n";
        $message .= "\n" . $_SESSION["translate"]->it("Comments") . ":\n" . $_REQUEST["EMAIL_comments"];

        $headers['From'] = $from;
        $headers['To'] = $to;
        $headers['Subject'] = $subject;
        $mail_object =& Mail::factory('mail');

        if ($mail_object->send($to, $headers, $message)) {
            // Success
            $title = $_SESSION["translate"]->it("Email sent");
            $content = $_SESSION["translate"]->it("Your email was successfully sent.");
            $content .= "  <a href=\"./index.php?module=phpwslistings&LISTINGS_user_op=view&LISTING_id=" . $_REQUEST["LISTING_id"] . "\">" . $_SESSION["translate"]->it("Go back to your listing.") . "</a>";

        } else {
            // Failure
            $title = "<font class=\"errortext\">" . $_SESSION["translate"]->it("ERROR!") . "</font>";
            $content = $_SESSION["translate"]->it("There was a problem sending your email.") . " " . $_SESSION["translate"]->it("If the problem persists contact your system administrator.");
        }
        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
    }// END FUNC sendEmail
     
    function AgentCheck(){
        $results = $GLOBALS["core"]->sqlSelect("mod_users");
        foreach($results as $row){
            if($row["user_id"])
                $where[$row["user_id"]] = "agent_id<>" . $row["user_id"];
        }
        if(is_array($where)){
            $where = implode("&&", $where);
            $sql_statement = "SELECT agent_id FROM mod_listings WHERE " . $where;
            $agt_results = $GLOBALS["core"]->query($sql_statement, TRUE);
            if($agt_results) {
                while ($row = $agt_results->fetchRow(DB_FETCHMODE_ASSOC)){
                    extract($row);
                    if(!empty($row["agent_id"]) && $agent != $row["agent_id"]){
                        $agent = $row["agent_id"];
                        $sql_update = "UPDATE mod_listings SET agent_id='0' WHERE agent_id=" . $agent;
                        $GLOBALS["core"]->query($sql_update, TRUE);
                    }
                }
            }
        }
    } 

        /**
    * Displays feature listing
    *
    * Displays feature listing on home page body
    */
    function showListings($feature = FALSE, $class_id = NULL) {
        if(isset($_REQUEST["LISTING_feature"]) && !empty($_REQUEST["LISTING_feature"]))
            $feature = TRUE;
        if($feature && !$this->_showFeature)
            return;           
        if($feature)
	        $GLOBALS["CNT_phpwslistings"]["title"] = $this->_featureTitle;
        else
	        $GLOBALS["CNT_phpwslistings"]["title"] = $_SESSION["translate"]->it("View Listings");

        $match_columns = array("active" => 1,"sold" => 0);
        if($feature)
            $match_columns["feature"] = 1;
        elseif(!is_null($class_id)) {
            $match_columns["class_id"] = $class_id;
        }

        $result = $GLOBALS["core"]->sqlSelect("mod_listings",$match_columns);
        $matches = array();
        if($result) {
            foreach($result as $row) {
                //if(!$_SESSION["OBJ_user"]->allow_access("phpwslistings")) {
                $agent = new SEILER_ListingAgent($row["agent_id"]);
                if(!$agent->_active)
                    continue;
                //}

                // Create listing object
                $listing = new SEILER_Listing($row["id"]); 

                // Skip expired listings
                if($listing->expired())
                    continue;

	            //$listing->view("small");
                array_push($matches, $listing);
            }
        }

        // Paginate
        if(count($matches)) {
            if(!is_null($class_id)):
                $link = "./index.php?module=phpwslistings&amp;LISTINGS_user_op=showlistings&amp;LISTING_feature=$feature&amp;CLASS_id=" . $_REQUEST["CLASS_id"];
            else:
                $link = "./index.php?module=phpwslistings&amp;LISTINGS_user_op=showlistings&amp;LISTING_feature=$feature";
            endif;

            $pageData = $this->paginateListingArray($matches, $link, TRUE, array("<b>[ ", " ]</b>"), NULL, 10);

            if(sizeof($matches) > $this->_paginateLimit)
                $GLOBALS["CNT_phpwslistings"]["content"] = "<center>$pageData[1]<br />$pageData[2] " . $_SESSION["translate"]->it("View Listings") . "</center><br />";

            for($i=0; $i<count($pageData[0]); $i++) {
                if($i)
                    $GLOBALS["CNT_phpwslistings"]["content"] .= "<hr size=\"1\" noshade />";
                $pageData[0][$i]->view("small");
            }

        } else {
            if($feature)
                $GLOBALS["CNT_phpwslistings"]["content"] = $_SESSION["translate"]->it("No featured listings found!");
            else
                $GLOBALS["CNT_phpwslistings"]["content"] = $_SESSION["translate"]->it("No listings found!");
        }
    }// END FUNC showListings
    
        /**
    * Displays block
    *
    * Displays block on home page containt listings
    */
    function showBlockListings() {
        if(!$this->_showBlock)
            return;

        $match = array("active","sold");
        $values = array("1","0");
        $result = $GLOBALS["core"]->sqlSelect("mod_listings","active","1");
        $used = array();
        if($result) {
            $num = count($result);
            $block = "";
            for($i=0; $i < $this->_numBlock; $i++) {
                $index = rand(0, $num - 1);

                if($i >= $num) {
                    break;
                } elseif(in_array($index,$used)) {
                    // If we've already done this, pick a new one
                    $i--;
                    continue;
                } else {
                    $agent = new SEILER_ListingAgent($result[$index]["agent_id"]);
                    if(!$agent->_active)
                        continue;

                    array_push($used,$index);
                }

                $id = $result[$index]["id"];
                $bl = new SEILER_Listing($id); 
                if($bl->expired()) {
                    // Pop the last entry off, should be ours
                    array_pop($used);
                    continue;
                }

                if($i)
                  $tags["DIVIDER"] = "<hr size=\"1\" noshade />";
  
	            $block = $bl->renderBlock();
              $tags["THUMB"] = $block["thumb"];
              $tags["TITLE"] = $block["title"];
              $tags["PRICE"] = $block["price"];
              $tags["PRICE_LABEL"] = $block["price_label"];
              $tags["DETAILS"] = $block["details"];
              $GLOBALS["CNT_phpwslistings_box"]["content"] .= PHPWS_Template::processTemplate($tags,"phpwslistings", "block.tpl");
            }
        }
        if(sizeof($used) == 0){
            $block["details"] = $_SESSION["translate"]->it("No listings found.");
            $tags["DETAILS"] = $block["details"];
            $GLOBALS["CNT_phpwslistings_box"]["content"] = PHPWS_Template::processTemplate($tags,"phpwslistings", "block.tpl");
        }
            
	    $GLOBALS["CNT_phpwslistings_box"]["title"] = $this->_blockTitle;
      
    }// END FUNC showBlockListings
    
        /**
    * Displays menu block
    *
    * Displays block with basic phpws menu items
    */
    function showMenuBlock() {
        if(!$this->_showMenu)
            return;

        $block = "";
	    $block .= "<a href=\"index.php?module=home\">Home</a>";
        if(count($this->_classes) > 1) {
            if(isset($_REQUEST["CLASS_id"]))
                $block .= "<br />" . SEILER_ListingClass::showSelect("CLASS_id",$_REQUEST["CLASS_id"],FALSE); 
            else
                $block .= "<br />" . SEILER_ListingClass::showSelect("CLASS_id",NULL,FALSE); 
            $block .= "<br />" . PHPWS_Form::formSubmit($_SESSION["translate"]->it("View Listings"), "LISTINGS_user_op");
            $block .= "<br />" . PHPWS_Form::formSubmit($_SESSION["translate"]->it("Search"), "LISTINGS_user_op");
        } elseif(count($this->_classes) == 1) {
	        $block .= "<br /><a href=\"index.php?module=phpwslistings&amp;LISTINGS_user_op=showlistings&amp;CLASS_id=" . $this->_classes[0]->_id . "\">View Listings</a>";
	        $block .= "<br /><a href=\"index.php?module=phpwslistings&amp;LISTINGS_user_op=search&amp;CLASS_id=" . $this->_classes[0]->_id . "\">Search Listings</a>";
        }
	    $block .= "<br /><a href=\"index.php?module=phpwslistings&amp;LISTINGS_user_op=listagents\">View Agents</a>";

        if($_SESSION["OBJ_user"]->username) {
            $agent = new SEILER_ListingAgent($_SESSION["OBJ_user"]->user_id);

            if($agent->_active)
                $block .= "<br /><a href=\"index.php?module=phpwslistings&amp;LISTINGS_op=" . $_SESSION["translate"]->it("Manage Listings") . "\">My Listings</a>";
        }

        $tags["MENUBLOCK"] = $block;

        $elements[0] = PHPWS_Template::processTemplate($tags, "phpwslistings", "menublock.tpl");
        $elements[0] .= PHPWS_Form::formHidden("module", "phpwslistings");
	    $GLOBALS["CNT_phpwslistings_menubox"]["title"] = $_SESSION["translate"]->it("Listings Menu");
        //$GLOBALS["CNT_phpwslistings_menubox"]["content"] = PHPWS_Template::processTemplate($tags,"phpwslistings", "menublock.tpl");
        $GLOBALS["CNT_phpwslistings_menubox"]["content"] = PHPWS_Form::makeForm("phpwslistings_menublock", "index.php", $elements);

    }
}// END CLASS PHPWS_ListingManager

?>

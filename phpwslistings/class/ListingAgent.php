<?php
/**
* SEILER_ListingAgent for phpwsListings
*
* SEILER_ListingAgent represents a listing agent,
* which in our case is tied to a phpws user id.
*
* @author Don Seiler <don@seiler.us>
* @version $Id: ListingAgent.php,v 1.23 2003/11/21 20:22:36 wendall911 Exp $
* @package phpwsListings
*/

class SEILER_ListingAgent {

    var $_user_id = NULL;
    var $_username = NULL;
    var $_first_name = NULL;
    var $_last_name = NULL;
    var $_email = NULL;
    var $_phone = NULL;
    var $_mobile = NULL;
    var $_fax = NULL;
    var $_homepage = NULL;
    var $_bio = NULL;
    var $_agency_id = NULL;
    var $_agency = NULL;
    var $_image = NULL;
    var $_active = 1;
    var $_new = TRUE;

    function SEILER_ListingAgent($AGENT_id = NULL) {
        if($AGENT_id === NULL) {
            $this->_new = TRUE;

            $this->_image = new SEILER_ListingBaseImage("agents");
        } else {
            $this->_user_id = $AGENT_id;
            $agentUser = new PHPWS_User($this->_user_id);
            $this->_username = $agentUser->username;
            $this->_active = $agentUser->getUserVar("active",$this->_user_id,"phpwslistings");
            $this->_first_name = $agentUser->getUserVar("first_name",$this->_user_id,"phpwslistings");
            $this->_last_name = $agentUser->getUserVar("last_name",$this->_user_id,"phpwslistings");
            // $this->_image = $agentUser->getUserVar("image",$this->_user_id,"phpwslistings");
            $this->_image = new SEILER_ListingBaseImage("agents", serialize($agentUser->getUserVar("image",$this->_user_id,"phpwslistings")));
            $this->_phone = $agentUser->getUserVar("phone",$this->_user_id,"phpwslistings");
            $this->_mobile = $agentUser->getUserVar("mobile",$this->_user_id,"phpwslistings");
            $this->_fax = $agentUser->getUserVar("fax",$this->_user_id,"phpwslistings");
            $this->_homepage = $agentUser->getUserVar("homepage",$this->_user_id,"phpwslistings");
            $this->_email = $agentUser->email;
            $this->_bio = $agentUser->getUserVar("bio",$this->_user_id,"phpwslistings");
            $this->_agency_id = $agentUser->getUserVar("agency_id",$this->_user_id,"phpwslistings");
            if($this->_agency_id)
                $this->_agency = new SEILER_ListingAgency($this->_agency_id);

        }
    }

    function view($type=NULL) {
        if(!$this->_active && !$_SESSION["OBJ_user"]->allow_access("phpwslistings"))
            return;

        $tags["USER_ID"] = $this->_user_id;
        $tags["FIRST_NAME"] = $this->_first_name;
        $tags["LAST_NAME"] = $this->_last_name;
        $tags["EMAIL"] = $this->_email;
        if($this->_image)
            $tags["IMAGE"] = $this->_image->renderFull();

        $tags["PHONE_LABEL"] = $_SESSION["translate"]->it("Phone");
        $tags["PHONE"] = $this->_phone;
        $tags["MOBILE_LABEL"] = $_SESSION["translate"]->it("Mobile");
        $tags["MOBILE"] = $this->_mobile;
        $tags["FAX_LABEL"] = $_SESSION["translate"]->it("Fax");
        $tags["FAX"] = $this->_fax;
        $tags["HOMEPAGE_LABEL"] = $_SESSION["translate"]->it("Homepage");
        $tags["HOMEPAGE"] = $this->_homepage;
        if(($this->_agency_id) && !is_null($this->_agency->getName())) {
            $tags["AGENCY_LABEL"] = $_SESSION["translate"]->it("Agency");
            $tags["AGENCY"] = "<a href=\"./index.php?module=phpwslistings&amp;LISTINGS_user_op=viewagency&amp;AGENCY_id=" . $this->_agency_id . "\">" . $this->_agency->getName() . "</a>";
        }
        $tags["BIO"] = $this->_bio;

        $tags["MYLISTINGS"] = "<a href=\"./index.php?module=phpwslistings&amp;LISTING_agent_id=" . $this->_user_id . "&amp;LISTINGS_user_op=" . $_SESSION["translate"]->it("Search Listings") . "\">" . $_SESSION["translate"]->it("View My Listings") . "</a>";

        if(!is_null($type) && ($type == "small")) {
            if($this->_image)
                $tags["IMAGE"] = $this->_image->renderThumb();
            $tags["SEEDETAILS"] = "<a href=\"./index.php?module=phpwslistings&amp;AGENT_id=" . $this->_user_id . "&amp;LISTINGS_user_op=viewagent\">" . $_SESSION["translate"]->it("See My Details") . "</a>";
            $title = NULL;
            $content = PHPWS_Template::processTemplate($tags, "phpwslistings", "view_agent_small.tpl");
        } else {
            $title = $_SESSION["translate"]->it("View Agent");
            $content = PHPWS_Template::processTemplate($tags, "phpwslistings", "view_agent.tpl");
        }

        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
    }

    function formatDate($in_date) {
        return substr(preg_replace("/[^0-9]/", "", $in_date), 0, 8);
    }

    function edit() {
        $tags["USERNAME_LABEL"] = $_SESSION["translate"]->it("Username");
        $tags["USERNAME"] = $this->_username;
        $tags["FIRST_NAME_LABEL"] = "<font color=\"red\"><b>*</b></font> " . $_SESSION["translate"]->it("First Name");
        $tags["FIRST_NAME"] = PHPWS_Form::formTextField("AGENT_first_name", $this->_first_name, 33, 255);
        $tags["LAST_NAME_LABEL"] = "<font color=\"red\"><b>*</b></font> " . $_SESSION["translate"]->it("Last Name");
        $tags["LAST_NAME"] = PHPWS_Form::formTextField("AGENT_last_name", $this->_last_name, 33, 255);
        $tags["PHONE_LABEL"] = $_SESSION["translate"]->it("Phone");
        $tags["PHONE"] = PHPWS_Form::formTextField("AGENT_phone", $this->_phone, 33, 255);
        $tags["MOBILE_LABEL"] = $_SESSION["translate"]->it("Mobile");
        $tags["MOBILE"] = PHPWS_Form::formTextField("AGENT_mobile", $this->_mobile, 33, 255);
        $tags["FAX_LABEL"] = $_SESSION["translate"]->it("Fax");
        $tags["FAX"] = PHPWS_Form::formTextField("AGENT_fax", $this->_fax, 33, 255);
        $tags["HOMEPAGE_LABEL"] = $_SESSION["translate"]->it("Homepage");
        $tags["HOMEPAGE"] = PHPWS_Form::formTextField("AGENT_homepage", $this->_homepage, 33, 255);
        $tags["BIO_LABEL"] = $_SESSION["translate"]->it("Bio");
        $tags["BIO"] = PHPWS_Form::formTextArea("AGENT_bio", $this->_bio, 5, 40);
        $tags["AGENCY_LABEL"] = $_SESSION["translate"]->it("Agency");
        $tags["AGENCY"] = SEILER_ListingAgency::showSelect("AGENT_agency_id", $this->_agency_id);


        $tags["IMAGE_LABEL"] = $_SESSION["translate"]->it("Image");
        $tags["IMAGE"] = PHPWS_Form::formFile("AGENT_image");

        if(!$this->_image->isEmpty()) {
            $tags["IMAGE"] .= "<br />" . $this->_image->renderFull();
            $tags["REMOVE_LABEL"] = $_SESSION["translate"]->it("Remove Image");
            $tags["REMOVE_CHECK"] = PHPWS_Form::formCheckBox("AGENT_remove_image");
        }

        $tags["SUBMIT_BUTTON"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Save Agent"), "LISTINGS_op");

        $elements[0] = PHPWS_Form::formHidden("module", "phpwslistings");
        $elements[0] .= PHPWS_Template::processTemplate($tags, "phpwslistings", "edit_agent.tpl");

        $title = $_SESSION["translate"]->it("Edit Agent");

        $content = PHPWS_Form::makeForm("phpwslistings_edit", "index.php", $elements, "post", FALSE, TRUE);
        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
    }

    function save() {
        if($_REQUEST["LISTINGS_op"] == $_SESSION["translate"]->it("Save Agent") && !$_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_agent") && ($_SESSION["OBJ_user"]->user_id != $this->_user_id)) {
            $this->_error("access_denied");
            return;
        }

        $agentUser = new PHPWS_User($this->_user_id);

        if(!empty($_POST["AGENT_phone"])) {
            $this->_phone = PHPWS_Text::parseInput($_POST["AGENT_phone"]);
            if(!$agentUser->setUserVar("phone", $this->_phone, $this->_user_id, "phpwslistings")) {
	            $this->_error("save_failed");
	            $this->edit();
	            return;
            }
        } else
            $agentUser->dropUserVar("phone", $this->_user_id, "phpwslistings");

        if(!empty($_POST["AGENT_fax"])) {
            $this->_fax = PHPWS_Text::parseInput($_POST["AGENT_fax"]);
            if(!$agentUser->setUserVar("fax", $this->_fax, $this->_user_id, "phpwslistings")) {
	            $this->_error("save_failed");
	            $this->edit();
	            return;
            }
        } else
            $agentUser->dropUserVar("fax", $this->_user_id, "phpwslistings");

        if(!empty($_POST["AGENT_mobile"])) {
            $this->_mobile = PHPWS_Text::parseInput($_POST["AGENT_mobile"]);
            if(!$agentUser->setUserVar("mobile", $this->_mobile, $this->_user_id, "phpwslistings")) {
	            $this->_error("save_failed");
	            $this->edit();
	            return;
            }
        } else
            $agentUser->dropUserVar("mobile", $this->_user_id, "phpwslistings");

        if(!empty($_POST["AGENT_homepage"])) {
            $this->_homepage = PHPWS_Text::parseInput($_POST["AGENT_homepage"]);
            if(!$agentUser->setUserVar("homepage", $this->_homepage, $this->_user_id, "phpwslistings")) {
	            $this->_error("save_failed");
	            $this->edit();
	            return;
            }
        } else
            $agentUser->dropUserVar("homepage", $this->_user_id, "phpwslistings");

        if(!empty($_POST["AGENT_agency_id"])) {
            $this->_agency_id = PHPWS_Text::parseInput($_POST["AGENT_agency_id"]);
            if(!$agentUser->setUserVar("agency_id", $this->_agency_id, $this->_user_id, "phpwslistings")) {
	            $this->_error("save_failed");
	            $this->edit();
	            return;
            }
        } else
            $agentUser->dropUserVar("agency_id", $this->_user_id, "phpwslistings");

        if(!empty($_POST["AGENT_bio"])) {
            $this->_bio = PHPWS_Text::parseInput($_POST["AGENT_bio"]);
            if(!$agentUser->setUserVar("bio", $this->_bio, $this->_user_id, "phpwslistings")) {
	            $this->_error("save_failed");
	            $this->edit();
	            return;
            }
        } else
            $agentUser->dropUserVar("bio", $this->_user_id, "phpwslistings");


        if($_POST["AGENT_first_name"]) {
            $this->_first_name = PHPWS_Text::parseInput($_POST["AGENT_first_name"]);
            if(!$agentUser->setUserVar("first_name", $this->_first_name, $this->_user_id, "phpwslistings")) {
	            $this->_error("save_failed");
	            $this->edit();
	            return;
            }
        } else {
            $this->_error("no_first_name");
            $this->edit();
            return;
        }
        if($_POST["AGENT_last_name"]) {
            $this->_last_name = PHPWS_Text::parseInput($_POST["AGENT_last_name"]);
            if(!$agentUser->setUserVar("last_name", $this->_last_name, $this->_user_id, "phpwslistings")) {
	            $this->_error("save_failed");
	            $this->edit();
	            return;
            }
        } else {
            $this->_error("no_last_name");
            $this->edit();
            return;
        }

        $img = $this->_image->save($_FILES["AGENT_image"], "$this->_first_name $this->_last_name");
        if(!is_null($img) && ($img == FALSE)){
            // $this->_error("image_upload");
            $this->edit();
            return;
        } else
            if(!is_null($img))
                if(!$agentUser->setUserVar("image", $img, $this->_user_id, "phpwslistings")){
	                $this->_error("save_failed");
	                $this->edit();
	                return;
                }

        if ($this->_image && isset($_POST["AGENT_remove_image"])) {
            $this->_image->delete();
            $agentUser->dropUserVar("image", $this->_user_id, "phpwslistings");
        }


	    $title = $_SESSION["translate"]->it("Agent Saved");
	    $content = $_SESSION["translate"]->it("The agent was successfully saved.");
        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
    }


    function getName() {
        return $this->_first_name . " " . $this->_last_name;
    }// END FUNC getName()


    function getEmail() {
        return $this->_email;
    }// END FUNC getEmail()


    function activate() {
        if(!$_SESSION["OBJ_user"]->allow_access("phpwslistings", "activate_agent")) {
            $this->_error("access_denied");
            return;
        }

        PHPWS_WizardBag::toggle($this->_active);
        $agentUser = new PHPWS_User($this->_user_id);
        $agentUser->setUserVar("active", $this->_active, $this->_user_id, "phpwslistings");
        
    }


    function _error($type) {
        $title = "<font class=\"errortext\">" . $_SESSION["translate"]->it("ERROR!") . "</font>";

        switch($type) {
            case "no_first_name":
                $content = $_SESSION["translate"]->it("You did not provide a first name for your agent.");
                break;

            case "no_last_name":
                $content = $_SESSION["translate"]->it("You did not provide a last name for your agent.");
                break;

            case "save_failed":
                $content = $_SESSION["translate"]->it("There was a problem saving your agent.") . " " . $_SESSION["translate"]->it("If the problem persists contact your system administrator.");
                break;

            case "update_failed":
                $content = $_SESSION["translate"]->it("There was a problem updating your agent.") . " " . $_SESSION["translate"]->it("If the problem persists contact your system administrator.");
                break;

            case "not_allowed_type":
                include(PHPWS_SOURCE_DIR . "mod/phpwslistings/conf/config.php");
                $content = $_SESSION["translate"]->it("The file you uploaded is not an allowed type on this server") . ": <b>" . $_FILES["AGENT_image"]["type"] . "</b><br />" .  $_SESSION["translate"]->it("The allowed types are") . ": <b>$allowedImageTypes</b>";
                break;

            case "image_upload":
                $content = "<b>" . $_SESSION["translate"]->it("There was a problem uploading the image you specified.  Check your permissions.") . "</b>";
                break;

            case "access_denied":
                $content = "<b>" . $_SESSION["translate"]->it("ACCESS DENIED!") . "</b>";
                break;
        }

        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
    }

}// END CLASS SEILER_ListingAgent

?>

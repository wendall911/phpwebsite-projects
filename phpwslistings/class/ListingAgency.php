<?php
/**
* SEILER_ListingAgency for phpwsListings
*
* SEILER_ListingAgency represents an agency to
* which an agent would belong.  Allows for
* sorting and searching agency-wide.
*
* @author Don Seiler <don@seiler.us>
* @version $Id: ListingAgency.php,v 1.15 2003/11/21 20:22:36 wendall911 Exp $
* @package phpwsListings
*/

class SEILER_ListingAgency {
    var $_id = NULL;
    var $_name = NULL;
    var $_description = NULL;
    var $_image = NULL;
    var $_active = 1;
    var $_new = TRUE;

    function SEILER_ListingAgency($AGENCY_id = NULL) {
        if($AGENCY_id == NULL) {
            $this->_new = TRUE;

            $this->_image = new SEILER_ListingBaseImage("agencies");
        
        } else {
            $result = $GLOBALS["core"]->sqlSelect("mod_listings_agencies", "id", $AGENCY_id);
            $this->_new = FALSE;
            $this->_id = $result[0]["id"];
            $this->_name = $result[0]["name"];
            $this->_description = $result[0]["description"];
            $this->_image = new SEILER_ListingBaseImage("agencies",$result[0]["image"]);
            $this->_active = $result[0]["active"];
        }
    }// END FUNC SEILER_ListingAgency()


    function getName() {
        return $this->_name;
    }// END FUNC getName()


    function view() {
        if(!$this->_active && !$_SESSION["OBJ_user"]->allow_access("phpwslistings"))
            return;
        $tags["NAME"] = $this->_name;
        $tags["DESCRIPTION"] = $this->_description;
        if($this->_image)
            $tags["IMAGE"] .= "<br />" . $this->_image->renderFull();

        $tags["MYLISTINGS"] = "<a href=\"./index.php?module=phpwslistings&amp;LISTING_agency_id=" . $this->_id . "&amp;LISTINGS_user_op=" . $_SESSION["translate"]->it("Search Listings") . "\">" . $_SESSION["translate"]->it("View Our Listings") . "</a>";


        $content = PHPWS_Template::processTemplate($tags, "phpwslistings", "view_agency.tpl");
        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
    }// END FUNC view()


    function edit() {
        $tags["NAME_LABEL"] = $_SESSION["translate"]->it("Name");
        $tags["NAME"] = PHPWS_Form::formTextField("AGENCY_name", $this->_name, 33, 255);
        $tags["DESCRIPTION_LABEL"] = $_SESSION["translate"]->it("Description");
        $tags["DESCRIPTION"] = PHPWS_WizardBag::js_insert("wysiwyg", "agency_edit", "AGENCY_description") . PHPWS_Form::formTextArea("AGENCY_description", $this->_description, 5, 40);

        $tags["IMAGE_LABEL"] = $_SESSION["translate"]->it("Image");
        $tags["IMAGE"] = PHPWS_Form::formFile("AGENCY_image");

        if(!$this->_image->isEmpty()) {
            $tags["IMAGE"] .= "<br />" . $this->_image->renderFull();
            $tags["REMOVE_LABEL"] = $_SESSION["translate"]->it("Remove Image");
            $tags["REMOVE_CHECK"] .= PHPWS_Form::formCheckBox("AGENCY_remove_image");
        }

        $tags["IMAGE_ALT_LABEL"] = $_SESSION["translate"]->it("Short Description");
        $tags["IMAGE_ALT"] = PHPWS_Form::formTextField("AGENCY_alt", $this->_image->getAlt(), 33, 100);

        $tags["SUBMIT_BUTTON"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Save Agency"), "LISTINGS_op");

        $elements[0] = PHPWS_Form::formHidden("module", "phpwslistings");
        $elements[0] .= PHPWS_Template::processTemplate($tags, "phpwslistings", "edit_agency.tpl");

        if($this->_new) $title = $_SESSION["translate"]->it("New Agency");
        else $title = $_SESSION["translate"]->it("Edit Agency");

        $content = PHPWS_Form::makeForm("agency_edit", "index.php", $elements, "post", FALSE, TRUE);
        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");

    }// END FUNC edit()


    function save() {
        if($_REQUEST["LISTINGS_op"] == $_SESSION["translate"]->it("Save Agency") && !$_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_agency")) {
            $this->_error("access_denied");
            return;
        }

        $this->_description = PHPWS_Text::parseInput($_POST["AGENCY_description"]);
        $data["description"] = $this->_description;

        if($_POST["AGENCY_name"]) {
            $this->_name = PHPWS_Text::parseInput($_POST["AGENCY_name"]);
            $data["name"] = $this->_name;
        } else {
            $this->_error("no_name");
            $this->edit();
            return;
        }

        $data["image"] = $this->_image->save($_FILES["AGENCY_image"],$_POST["AGENCY_alt"]);
        if(!is_null($data["image"]) && ($data["image"] == FALSE)) {
            // $this->_error("image_upload");
            $this->edit();
            return;
        }
        
        if($this->_image && isset($_POST["AGENCY_remove_image"]))
            $data["image"] = $this->_image->delete();

        if($this->_new) {
            if($this->add($data)) {
                $title = $_SESSION["translate"]->it("Agency Saved");
                $content = $_SESSION["translate"]->it("Your agency was successfully saved.");
            } else {
                $this->_error("save_failed");
                $this->edit();
                return;
            }
        } elseif($this->_id) {
            if($this->update($data)) {
                $title = $_SESSION["translate"]->it("Agency Updated");
                $content = $_SESSION["translate"]->it("Your agency was successfully updated.");
            } else {
                $this->_error("update_failed");
                $this->edit();
                return;
            }
        }

        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
        
    }// END FUNC save()


    function delete() {
        if(!$_SESSION["OBJ_user"]->allow_access("phpwslistings", "delete_agency")) {
            $this->_error("access_denied");
            return;
        }

        if(isset($_POST["yes"])){
            $this->_image->delete();
            $GLOBALS["core"]->sqlDelete("mod_listings_agencies", "id", $this->_id);
            $title = $_SESSION["translate"]->it("Agency Deleted!");
            $content = $_SESSION["translate"]->it("The agency was successfully <b>deleted</b>.");
        } elseif (isset($_POST["no"])) {
            $title = $_SESSION["translate"]->it("Agency Kept!");
            $content = $_SESSION["translate"]->it("You have chosen <b>not</b> to delete the agency.");
        } else {
            $elements[0] = PHPWS_Form::formHidden("module", "phpwslistings");
            $elements[0] .= PHPWS_Form::formHidden("LISTINGS_op", $_SESSION["translate"]->it("Delete Agency"));
            $elements[0] .= PHPWS_Form::formHidden("AGENCY_id", $this->_id);
            $elements[0] .= PHPWS_Form::formSubmit($_SESSION["translate"]->it("Yes"), "yes");
            $elements[0] .= PHPWS_Form::formSubmit($_SESSION["translate"]->it("No"), "no");

            $title = $_SESSION["translate"]->it("Delete Agency?");
            $content = $_SESSION["translate"]->it("Are you sure you wish to delete this agency?");
            $content .= PHPWS_Form::makeForm("agency_delete", "index.php", $elements);
        }
        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");

    }// END FUNC delete()


    function add($data) {
        if($_REQUEST["LISTINGS_op"] == $_SESSION["translate"]->it("Save Agency") && !$_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_agency")) {
            $this->_error("access_denied");
            return;
        }

        $this->_id = $GLOBALS["core"]->sqlInsert($data, "mod_listings_agencies", FALSE, TRUE);

        if($this->_id)
            return TRUE;
        else
            return FALSE;

    }// END FUNC add()


    function update($data) {
        if(!$_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_agency")) {
            $this->_error("access_denied");
            return;
        }

        if($GLOBALS["core"]->sqlUpdate($data, "mod_listings_agencies", "id", $this->_id))
            return TRUE;
        else
            return FALSE;

    }// END FUNC update()


    function activate() {
        if(!$_SESSION["OBJ_user"]->allow_access("phpwslistings", "activate_agency")) {
            $this->_error("access_denied");
            return;
        }

        PHPWS_WizardBag::toggle($this->_active);
        $data["active"] = $this->_active;
        $this->update($data);
    }// END FUNC activate()


    function showSelect($fieldname = NULL, $agency_id = NULL) {
        if($_SESSION["OBJ_user"]->allow_access("phpwslisings","edit_agencies"))
            $errorMessage = "<span class=\"errortext\">" . PHPWS_Text::moduleLink($_SESSION["translate"]->it("No Agencies Available"), "phpwslistings", array("LISTINGS_op"=>$_SESSION["translate"]->it("Create Agency"))) . "</span>";
        else
            $errorMessage = "<span class=\"errortext\">" . $_SESSION["translate"]->it("No Agencies Available") . "</span>";

        $result = $GLOBALS["core"]->sqlSelect("mod_listings_agencies", NULL, NULL, "name");
        $agencies[] = " ";
        if($result) {
            foreach($result as $row)
                $agencies[$row["id"]] = $row["name"];

            return PHPWS_Form::formSelect($fieldname, $agencies, $agency_id, FALSE, TRUE);
        } else {
            return $errorMessage;
        }
    }// END FUNC showSelect()


    function _error($type) {
        $title = "<font class=\"errortext\">" . $_SESSION["translate"]->it("ERROR!") . "</font>";

        switch($type) {
            case "no_name":
                $content = $_SESSION["translate"]->it("You did not provide a name for your agency.");
                break;

            case "no_alt":
                $content = $_SESSION["translate"]->it("You must provide a short description for the image you supplied.");
                break;
            case "save_failed":
                $content = $_SESSION["translate"]->it("There was a problem saving your agency.") . " " . $_SESSION["translate"]->it("If the problem persists contact your system administrator.");
                break;

            case "update_failed":
                $content = $_SESSION["translate"]->it("There was a problem updating your agency.") . " " . $_SESSION["translate"]->it("If the problem persists contact your system administrator.");
                break;
            case "image_upload":
                $content = "<b>" . $_SESSION["translate"]->it("There was a problem uploading the image you specified.  Check your permissions.") . "</b>";
                break;

            case "access_denied":
            $content = "<b>" . $_SESSION["translate"]->it("ACCESS DENIED!") . "</b>";
            break;
        }

        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
    }// END FUNC _error()

}// END CLASS SEILER_ListingAgency

?>

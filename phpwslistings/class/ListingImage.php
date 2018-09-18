<?php
/**
* SEILER_ListingImage for phpwsListings
*
* SEILER_ListingImage represents an image for
* a listing item.
*
* @author Don Seiler <don@seiler.us>
* @version $Id: ListingImage.php,v 1.26 2003/11/21 20:22:36 wendall911 Exp $
* @package phpwsListings
*/
class SEILER_ListingImage {

    var $_id = NULL;
    var $_caption = NULL;
    var $_image = NULL;
    var $_description = NULL;
    var $_listing_id = NULL;
    var $_agent_id = NULL;
    var $_rank = NULL;
    var $_new = TRUE;


    /**
    * Default constructor
    *
    * Populates ListingImage variables if an id is
    * passed in.
    *
    * @param integer    $IMAGE_id id of the ListingImage
    * @access public
    */
    function SEILER_ListingImage($IMAGE_id = NULL) {
        if (is_null($IMAGE_id)) {
            $this->_new = TRUE;
            $this->_image = new SEILER_ListingBaseImage("");

        } else {
            $result = $GLOBALS["core"]->sqlSelect("mod_listings_images", "id", $IMAGE_id);

            $this->_new = FALSE;
            $this->_id = $result[0]["id"];
            $this->_caption = $result[0]["caption"];
            $this->_image = new SEILER_ListingBaseImage("",$result[0]["image"]);
            $this->_description = $result[0]["description"];
            $this->_listing_id = $result[0]["listing_id"];
            $this->_agent_id = $result[0]["agent_id"];
            $this->_rank = $result[0]["rank"];

        }
    }


    /**
    * Sets listing id
    *
    * Sets listing id for new images
    *
    * @access public
    */
    function setIDVars($listing_id, $agent_id) {
        $this->_listing_id = $listing_id;
        $this->_agent_id = $agent_id;
    }


    /**
    * Thumbnail display
    *
    * Returns img tag string
    *
    * @access public
    */
    function renderThumb($edit = FALSE) {
        $imgtag = "";
        if($edit) {
            $imgtag .= "<a href=\"./index.php?module=phpwslistings&LISTINGS_op=editimage&IMAGE_id=$this->_id\">";
            $imgtag .= $this->_image->renderThumb();
            $imgtag .= "</a><br /><br />";
        } else {
            $imgtag .= "<a href=\"./index.php?module=phpwslistings&LISTINGS_user_op=viewfullimage&IMAGE_id=$this->_id\">";
            $imgtag .= $this->_image->renderThumb();
            $imgtag .= "</a>";
        }

        return $imgtag;
    }


    /**
    * Full display
    *
    * Returns img tag string
    *
    * @access public
    */
    function renderFull() {
        $imgtag = $this->_image->renderFull();
        $imgtag .= "<br /><div align=\"center\">" . $this->_caption . "</div><br />";

        return $imgtag;
    }
    
    /**
    * Full Custom Layout display
    *
    * Returns img tag string
    *
    * @access public
    */
    function renderCustFull() {
        $imgtag["image"] = $this->_image->renderFull();
        $imgtag["caption"] = $this->_caption;

        return $imgtag;
    }

    /**
    * Full display with description
    *
    * Displays image info
    *
    * @access public
    */
    function view() {
        $tags["IMAGE"] = $this->_image->renderFull();

        $tags["CAPTION"] = $this->_caption;
        $tags["DESCRIPTION_LABEL"] = $_SESSION["translate"]->it("Description");
        $tags["DESCRIPTION"] = $this->_description;
        $tags["LISTINGLINK"] = "<a href=\"index.php?module=phpwslistings&amp;LISTING_id=" . $this->_listing_id . "&amp;LISTINGS_user_op=view\">Visit Listing</a>";

        $title = $_SESSION["translate"]->it("Viewing Image") . ": " . $this->_caption;
        $content = PHPWS_Template::processTemplate($tags, "phpwslistings", "view_image.tpl");
        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
    }


    /**
    * Edit screen
    *
    * Displays screen interface to edit data
    *
    * @access public
    */
    function edit() {
        $tags["IMAGE_LABEL"] = $_SESSION["translate"]->it("Image");
        $tags["IMAGE"] = PHPWS_Form::formFile("LISTINGS_image");

        $tags["CAPTION_LABEL"] = $_SESSION["translate"]->it("Caption");
        $tags["CAPTION"] = PHPWS_Form::formTextField("LISTINGS_caption",$this->_caption,33,255);
        $tags["DESCRIPTION_LABEL"] = $_SESSION["translate"]->it("Description");
        $tags["DESCRIPTION"] = PHPWS_Form::formTextField("LISTINGS_description",$this->_description,33,100);

        $tags["RANK_LABEL"] = $_SESSION["translate"]->it("Rank");
        $tags["RANK"] = PHPWS_Form::formTextField("LISTINGS_rank",$this->_rank,5,5);
        $tags["RANK_NOTE"] = $_SESSION["translate"]->it("Main images must be ranked 1");

        if(!$this->_new)
            $tags["IMAGE"] = $this->_image->renderFull();

        $tags["SUBMIT_BUTTON"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Save Image"), "LISTINGS_op");
        if(!$this->_new)
            $tags["DELETE"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Delete Image"), "LISTINGS_op");


        $elements[0] = PHPWS_Form::formHidden("module","phpwslistings");
        $elements[0] .= PHPWS_Form::formHidden("listing_id",$this->_listing_id);
        $elements[0] .= PHPWS_Form::formHidden("LISTINGS_agent_id",$this->_agent_id);
        $elements[0] .= PHPWS_Template::processTemplate($tags, "phpwslistings", "edit_image.tpl");

        if($this->_new) $title = $_SESSION["translate"]->it("Add Image");
        else $title = $_SESSION["translate"]->it("Edit Image");

        $content = PHPWS_Form::makeForm("phpwslistings_edit","index.php", $elements, "post", FALSE, TRUE);
        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
    }


    /**
    * Save data
    *
    * Saves Image data to database if user has rights
    *
    * @access public
    * @see add(), update()
    */
    function save() {
        if($_REQUEST["LISTINGS_op"] == $_SESSION["translate"]->it("Save Image") && !$_SESSION["OBJ_user"]->allow_access("phpwslistings","edit_image") && !$_SESSION["OBJ_user"]->allow_access("phpwslistings","add_image") && ($_SESSION["OBJ_user"]->user_id != $this->_agent_id)) {
            $this->_error("access_denied");
            return;
        }

        $this->_listing_id = PHPWS_Text::parseInput($_POST["listing_id"]);
        $data["listing_id"] = $this->_listing_id;

        $this->_agent_id = PHPWS_Text::parseInput($_POST["LISTINGS_agent_id"]);
        $data["agent_id"] = $this->_agent_id;

        $this->_rank = PHPWS_Text::parseInput($_POST["LISTINGS_rank"]);
        $data["rank"] = $this->_rank;

        $this->_caption = PHPWS_Text::parseInput($_POST["LISTINGS_caption"]);
        $data["caption"] = $this->_caption;
        $this->_description = PHPWS_Text::parseInput($_POST["LISTINGS_description"]);
        $data["description"] = $this->_description;

        if($_FILES["LISTINGS_image"]["name"]) {
            $data["image"] = $this->_image->save($_FILES["LISTINGS_image"],$this->_caption);
            if(!is_null($data["image"]) && ($data["image"] == FALSE)) {
                // $this->_error("image_uploaded");
                $this->edit();
                return;
            } elseif (is_null($data["image"])) {
                $this->_error("no_image");
                $this->edit();
                return;
            }
        }

        if($this->_new) {
            if($this->add($data)) {
                $title = $_SESSION["translate"]->it("Image Saved");
                $content = $_SESSION["translate"]->it("Your image was successfully saved.");
                $content .= "  <a href=\"./index.php?module=phpwslistings&LISTINGS_op=" . $_SESSION["translate"]->it("Edit") . "&LISTING_id=" . $this->_listing_id . "\">" . $_SESSION["translate"]->it("Go back to your listing.") . "</a>";
            } else {
                $this->_error("save_failed");
                $this->edit();
                return;
            }
        } elseif($this->_id) {
            if($this->update($data)) {
                $title = $_SESSION["translate"]->it("Image Updated");
                $content = $_SESSION["translate"]->it("Your image was successfully updated.");
                $content .= "  <a href=\"./index.php?module=phpwslistings&LISTINGS_op=" . $_SESSION["translate"]->it("Edit") . "&LISTING_id=" . $this->_listing_id . "\">" . $_SESSION["translate"]->it("Go back to your listing.") . "</a>";
            } else {
                $this->_error("update_failed");
                $this->edit();
                return;
            }
        }

        $_SESSION["OBJ_layout"]->popbox($title,$content,NULL,"CNT_phpwslistings");
    }


    /**
    * Delete Image
    *
    * Deletes record from database
    *
    * @access public
    */
    function delete($final = FALSE) {
        if(!$_SESSION["OBJ_user"]->allow_access("phpwslistings","delete_image") && ($_SESSION["OBJ_user"]->user_id != $this->_agent_id)) {
            $this->_error("access_denied");
            return;
        }

        if(isset($_POST["yes"])) {
            $GLOBALS["core"]->sqlDelete("mod_listings_images","id",$this->_id);
            $this->_image->delete();

            $title = $_SESSION["translate"]->it("Image Deleted!");
            $content = $_SESSION["translate"]->it("The image was successfully <b>deleted</b>.");
            if(!$final)
                $content .= "  <a href=\"./index.php?module=phpwslistings&LISTINGS_op=" . $_SESSION["translate"]->it("Edit") . "&LISTING_id=" . $this->_listing_id . "\">" . $_SESSION["translate"]->it("Go back to your listing.") . "</a>";
        } elseif (isset($_POST["no"])) {
            $title = $_SESSION["translate"]->it("Image Kept!");
            $content = $_SESSION["translate"]->it("You have chosen <b>not</b> to delete the image.");
            if(!$final)
                $content .= "  <a href=\"./index.php?module=phpwslistings&LISTINGS_op=" . $_SESSION["translate"]->it("Edit") . "&LISTING_id=" . $this->_listing_id . "\">" . $_SESSION["translate"]->it("Go back to your listing.") . "</a>";
        } else {
            $Images[0] = PHPWS_Form::formHidden("module","phpwslistings");
            $Images[0] .= PHPWS_Form::formHidden("LISTINGS_op", $_SESSION["translate"]->it("Delete Image"));
            $Images[0] .= PHPWS_Form::formHidden("IMAGE_id", $this->_id);
            $Images[0] .= PHPWS_Form::formSubmit($_SESSION["translate"]->it("Yes"), "yes");
            $Images[0] .= PHPWS_Form::formSubmit($_SESSION["translate"]->it("No"), "no");
            
            $title = $_SESSION["translate"]->it("Delete Image?");
            $content = $_SESSION["translate"]->it("Are you sure you wish to delete this image?");
            $content .= PHPWS_Form::makeForm("phpwslistings_Image_delete", "index.php", $Images);
        }

        $_SESSION["OBJ_layout"]->popbox($title,$content,NULL,"CNT_phpwslistings");
    }


    /**
    * Adds record
    *
    * Inserts record into database
    *
    * @access public
    * @see save()
    */
    function add($data) {
        if(!$_SESSION["OBJ_user"]->allow_access("phpwslistings", "add_image") && ($_SESSION["OBJ_user"]->user_id != $this->_agent_id)) {
            $this->_error("access_denied");
            return;
        }

        $this->_id = $GLOBALS["core"]->sqlInsert($data, "mod_listings_images", FALSE, TRUE);

        if($this->_id)
            return TRUE;
        else
            return FALSE;
    }


    /**
    * Updates record
    *
    * Updates existing Image record
    *
    * @access public
    * @see save()
    */
    function update($data) {
        if(!$_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_image") && ($_SESSION["OBJ_user"]->user_id != $this->_agent_id)) {
            $this->_error("access_denied");
            return;
        }

        if($GLOBALS["core"]->sqlUpdate($data,"mod_listings_images","id",$this->_id))
            return TRUE;
        else
            return FALSE;
    }


    function _error($type) {
        $title = "<font class=\"errortext\">" . $_SESSION["translate"]->it("ERROR!") . "</font>";

        switch($type) {
            case "no_image":
                $content = $_SESSION["translate"]->it("No image was specified.");
                break;
            case "delete_image_files":
                $content = $_SESSION["translate"]->it("Unable to delete image and thumbnail files.");
                break;
            case "file_too_large":
                $content = $_SESSION["translate"]->it("The image file is too large.") . "  " . $_SESSION["translate"]->it("The maximum file size allowed is") . " <b>" . $_SESSION["SES_LISTING_MANAGER"]->_imageSizeLimit . " kb.</b>";
                break;
            case "not_allowed_type":
                include(PHPWS_SOURCE_DIR . "mod/phpwslistings/conf/config.php");
                $content = $_SESSION["translate"]->it("The file you uploaded is not an allowed type on this server") . ": <b>" . $_FILES["LISTINGS_image"]["type"] . "</b><br />" .  $_SESSION["translate"]->it("The allowed types are") . ": <b>$allowedImageTypes</b>";
                break;
            case "save_failed":
                $content = $_SESSION["translate"]->it("There was a problem saving your image.") . " " . $_SESSION["translate"]->it("If the problem persists contact your system administrator.");
                break;

            case "update_failed":
                $content = $_SESSION["translate"]->it("There was a problem updating your image.") . " " . $_SESSION["translate"]->it("If the problem persists contact your system administrator.");
                break;

            case "access_denied":
                $content = "<b>" . $_SESSION["translate"]->it("ACCESS DENIED!") . "</b>";
                break;

            case "gif2png_missing":
                include(PHPWS_SOURCE_DIR . "mod/phpwslistings/conf/config.php");
                $content = "<b>" . $_SESSION["translate"]->it("You are missing the gif2png program.") . "  ";
                $content .= $_SESSION["translate"]->it("Your configuration indicates it should be located at") . " " . $gif2png . ".  ";
                $content .= $_SESSION["translate"]->it("Either update your \$gif2png variable or have your system administrator install gif2png.") . "</b>";
                $content .= "  ";
                $content .= $_SESSION["translate"]->it("Until then, you will not be able to use gif images.  We suggest using png or jpeg images instead.") . "</b>";
                break;

        }

        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
    }


}// END CLASS SEILER_ListingImage

?>

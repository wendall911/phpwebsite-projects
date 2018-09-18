<?php
/**
* SEILER_ListingElement for phpwsListings
*
* SEILER_ListingElement represents a generic element
* that is the essense of phpwsListings.  It allows
* the website maintainer to use the one engine to list
* any type item.
*
* @author Don Seiler <don@seiler.us>
* @version $Id: ListingElement.php,v 1.25 2003/11/21 20:22:36 wendall911 Exp $
* @package phpwsListings
*/
class SEILER_ListingElement {

    var $_id = NULL;
    var $_class_id = NULL;
    var $_field_type = NULL;
    var $_field_name = NULL;
    var $_field_caption = NULL;
    var $_default_text = NULL;
    var $_field_elements = NULL;
    var $_rank = 0;
    var $_required = 0;
    var $_agentonly = 0;
    var $_display_on_browse = NULL;

    var $_new = 1;
    var $_class = NULL;


    /**
    * Default constructor
    *
    * Populates ListingElement variables if an id is
    * passed in.
    *
    * @param integer    $ELEMENT_id id of the ListingElement
    * @access public
    */
    function SEILER_ListingElement($ELEMENT_id = NULL) {
        if ($ELEMENT_id === NULL) {
            $this->_new = TRUE;

        } else {
            $result = $GLOBALS["core"]->sqlSelect("mod_listings_formelements", "id", $ELEMENT_id);

            $this->_new = FALSE;
            $this->_id = $result[0]["id"];
            $this->_class_id = $result[0]["class_id"];
            $this->_field_type = $result[0]["field_type"];
            $this->_field_name = $result[0]["field_name"];
            $this->_field_caption = $result[0]["field_caption"];
            $this->_default_text = $result[0]["default_text"];
            $this->_field_elements = $result[0]["field_elements"];
            $this->_rank = $result[0]["rank"];
            $this->_required = $result[0]["required"];
            $this->_agentonly = $result[0]["agentonly"];
            $this->_display_on_browse = $result[0]["display_on_browse"];

            $this->_class = new SEILER_ListingClass($this->_class_id);
        }
    }


    /**
    * Read-only display
    *
    * Feeds variables into template for read-only display
    *
    * @access public
    */
    function view() {
        if (!$_SESSION["OBJ_user"]->allow_access("phpwslitings"))
            return;

        $tags["CLASS_LABEL"] = $_SESSION["translate"]->it("Class");
        $tags["CLASS"] = $this->_class->getName();
        $tags["FIELD_TYPE_LABEL"] = $_SESSION["translate"]->it("Field Type");
        $tags["FIELD_TYPE"] = $this->_field_type;
        $tags["FIELD_NAME_LABEL"] = $_SESSION["translate"]->it("Field Name");
        $tags["FIELD_NAME"] = $this->_field_name;
        $tags["FIELD_CAPTION_LABEL"] = $_SESSION["translate"]->it("Field Caption");
        $tags["FIELD_CAPTION"] = $this->_field_caption;
        $tags["DEFAULT_TEXT_LABEL"] = $_SESSION["translate"]->it("Default Text");
        $tags["DEFAULT_TEXT"] = $this->_default_text;
        $tags["FIELD_ELEMENTS_LABEL"] = $_SESSION["translate"]->it("Field Elements");
        $tags["FIELD_ELEMENTS"] = $this->_field_elements;
        $tags["RANK_LABEL"] = $_SESSION["translate"]->it("Rank");
        $tags["RANK"] = $this->_rank;
        $tags["REQUIRED_LABEL"] = $_SESSION["translate"]->it("Required");
        $tags["REQUIRED"] = $this->_required;
        $tags["AGENTONLY_LABEL"] = $_SESSION["translate"]->it("Agent Only");
        $tags["AGENTONLY"] = $this->_agentonly;
        $tags["DISPLAY_ON_BROWSE_LABEL"] = $_SESSION["translate"]->it("Display on Browse");
        $tags["DISPLAY_ON_BROWSE"] = $this->_display_on_browse;

        $title = $_SESSION["translate"]->it("Form Elements");
        $content = PHPWS_Template::processTemplate($tags, "phpwslistings", "view_element.tpl");
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

        $bitarray = array(   
                        "0"=>"No",
                        "1"=>"Yes");
                        
        $field_types = array(   
                        "blank"=>"Blank Line",
                        "text"=>"Text",
                        "textarea"=>"Text Area",
                        "select"=>"Select List",
                        "multiple"=>"Select Multiple",
                        "radio"=>"Radio Buttons",
                        "checkbox"=>"Check Box" );
        
        $tags["CLASS_LABEL"] = $_SESSION["translate"]->it("Class");
        $tags["CLASS"] = SEILER_ListingClass::showSelect("ELEMENT_class_id",$this->_class_id, TRUE);
        $tags["FIELD_TYPE_LABEL"] = $_SESSION["translate"]->it("Field Type");
        $tags["FIELD_TYPE_HELP"] = $_SESSION["OBJ_help"]->show_link("phpwslistings", "field_type");
        $tags["FIELD_TYPE"] = PHPWS_Form::formSelect("ELEMENT_field_type",$field_types,$this->_field_type,FALSE,TRUE);
        $tags["FIELD_NAME_LABEL"] = $_SESSION["translate"]->it("Field Name");
        $tags["FIELD_NAME_HELP"] = $_SESSION["OBJ_help"]->show_link("phpwslistings", "field_name");
        $tags["FIELD_NAME"] = PHPWS_Form::formTextField("ELEMENT_field_name",$this->_field_name,20,20);
        $tags["FIELD_CAPTION_LABEL"] = $_SESSION["translate"]->it("Field Caption");
        $tags["FIELD_CAPTION_HELP"] = $_SESSION["OBJ_help"]->show_link("phpwslistings", "field_caption");
        $tags["FIELD_CAPTION"] = PHPWS_Form::formTextField("ELEMENT_field_caption",$this->_field_caption,20,80);
        $tags["DEFAULT_TEXT_LABEL"] = $_SESSION["translate"]->it("Default Text");
        $tags["DEFAULT_TEXT_HELP"] = $_SESSION["OBJ_help"]->show_link("phpwslistings", "default_text");
        $tags["DEFAULT_TEXT"] = PHPWS_Form::formTextField("ELEMENT_default_text",$this->_default_text,35,255);
        $tags["FIELD_ELEMENTS_LABEL"] = $_SESSION["translate"]->it("Field Elements");
        $tags["FIELD_ELEMENTS_HELP"] = $_SESSION["OBJ_help"]->show_link("phpwslistings", "field_elements");
        $tags["FIELD_ELEMENTS_SEPWARN"] = $_SESSION["translate"]->it("Separated with double-bars: \"apples||oranges||bananas\"");
        $tags["FIELD_ELEMENTS"] = PHPWS_Form::formTextField("ELEMENT_field_elements", $this->_field_elements,35,5000);
        $tags["RANK_LABEL"] = $_SESSION["translate"]->it("Rank");
        $tags["RANK_HELP"] = $_SESSION["OBJ_help"]->show_link("phpwslistings", "rank");
        $tags["RANK"] = PHPWS_Form::formTextField("ELEMENT_rank",$this->_rank,5,5);
        $tags["REQUIRED_LABEL"] = $_SESSION["translate"]->it("Required");
        $tags["REQUIRED_HELP"] = $_SESSION["OBJ_help"]->show_link("phpwslistings", "required");
        $tags["REQUIRED"] = PHPWS_Form::formSelect("ELEMENT_required",$bitarray,$this->_required,FALSE,TRUE);
        $tags["AGENTONLY_LABEL"] = $_SESSION["translate"]->it("Agent Only");
        $tags["AGENTONLY_HELP"] = $_SESSION["OBJ_help"]->show_link("phpwslistings", "agent_only");
        $tags["AGENTONLY"] = PHPWS_Form::formSelect("ELEMENT_agentonly",$bitarray,$this->_agentonly,FALSE,TRUE);
        $tags["DISPLAY_ON_BROWSE_LABEL"] = $_SESSION["translate"]->it("Display on Browse");
        $tags["DISPLAY_ON_BROWSE_HELP"] = $_SESSION["OBJ_help"]->show_link("phpwslistings", "display_on_browse");
        $tags["DISPLAY_ON_BROWSE"] = $this->_display_on_browse;
        $tags["DISPLAY_ON_BROWSE"] = PHPWS_Form::formSelect("ELEMENT_display_on_browse",$bitarray,$this->_display_on_browse,FALSE,TRUE);

        $tags["SUBMIT_BUTTON"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Save Element"), "LISTINGS_op");

        $elements[0] = PHPWS_Form::formHidden("module","phpwslistings");
        $elements[0] .= PHPWS_Template::processTemplate($tags, "phpwslistings", "edit_element.tpl");

        if($this->_new) $title = $_SESSION["translate"]->it("Create Form Element");
        else $title = $_SESSION["translate"]->it("Edit Form Element");

        $content = PHPWS_Form::makeForm("phpwslistings_edit","index.php", $elements, "post", FALSE, TRUE);
        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
    }


    /**
    * Save data
    *
    * Saves element data to database if user has rights
    *
    * @access public
    * @see add(), update()
    */
    function save() {
        if($_REQUEST["LISTINGS_op"] == $_SESSION["translate"]->it("Save Form Element") && !$_SESSION["OBJ_user"]->allow_access("phpwslistings","edit_element")) {
            $this->_error("access_denied");
            return;
        }

        if(empty($_POST["ELEMENT_class_id"])) {
            $this->_error("no_class");
            $this->edit();
            return;
        } else {
            $this->_class_id = PHPWS_Text::parseInput($_POST["ELEMENT_class_id"]);
            $data["class_id"] = $this->_class_id;
        }
        $this->_field_name = PHPWS_Text::parseInput($_POST["ELEMENT_field_name"]);
        $data["field_name"] = $this->_field_name;
        $this->_field_type = PHPWS_Text::parseInput($_POST["ELEMENT_field_type"]);
        $data["field_type"] = $this->_field_type;
        $this->_field_caption = PHPWS_Text::parseInput($_POST["ELEMENT_field_caption"]);
        $data["field_caption"] = $this->_field_caption;
        $this->_default_text = PHPWS_Text::parseInput($_POST["ELEMENT_default_text"]);
        $data["default_text"] = $this->_default_text;
        $this->_field_elements = PHPWS_Text::parseInput($_POST["ELEMENT_field_elements"]);
        $data["field_elements"] = $this->_field_elements;
        $this->_rank = PHPWS_Text::parseInput($_POST["ELEMENT_rank"]);
        $data["rank"] = $this->_rank;
        $this->_required = PHPWS_Text::parseInput($_POST["ELEMENT_required"]);
        $data["required"] = $this->_required;
        $this->_agentonly = PHPWS_Text::parseInput($_POST["ELEMENT_agentonly"]);
        $data["agentonly"] = $this->_agentonly;
        $this->_display_on_browse = PHPWS_Text::parseInput($_POST["ELEMENT_display_on_browse"]);
        $data["display_on_browse"] = $this->_display_on_browse;

        if($this->_new) {
            if($this->add($data)) {
                $title = $_SESSION["translate"]->it("Form Element Saved");
                $content = $_SESSION["translate"]->it("Your element was successfully saved.");
            } else {
                $this->_error("save_failed");
                $this->edit();
                return;
            }
        } elseif($this->_id) {
            if($this->update($data)) {
                $title = $_SESSION["translate"]->it("Form Element Updated");
                $content = $_SESSION["translate"]->it("Your element was successfully updated.");
            } else {
                $this->_error("update_failed");
                $this->edit();
                return;
            }
        }

        $_SESSION["OBJ_layout"]->popbox($title,$content,NULL,"CNT_phpwslistings");
    }


    /**
    * Delete element
    *
    * Deletes record from database
    *
    * @access public
    */
    function delete() {
        if(!$_SESSION["OBJ_user"]->allow_access("phpwslistings","delete_element")) {
            $this->_error("access_denied");
            return;
        }

        if(isset($_POST["yes"])) {
            $GLOBALS["core"]->sqlDelete("mod_listings_formelements","id",$this->_id);

            $title = $_SESSION["translate"]->it("Form Element Deleted!");
            $content = $_SESSION["translate"]->it("The form element was successfully <b>deleted</b>.");
        } elseif (isset($_POST["no"])) {
            $title = $_SESSION["translate"]->it("Form Element Kept!");
            $content = $_SESSION["translate"]->it("You have chosen <b>not</b> to delete the announcement.");
        } else {
            $elements[0] = PHPWS_Form::formHidden("module","phpwslistings");
            $elements[0] .= PHPWS_Form::formHidden("LISTINGS_op", $_SESSION["translate"]->it("Delete Form Element"));
            $elements[0] .= PHPWS_Form::formHidden("ELEMENT_id", $this->_id);
            $elements[0] .= PHPWS_Form::formSubmit($_SESSION["translate"]->it("Yes"), "yes");
            $elements[0] .= PHPWS_Form::formSubmit($_SESSION["translate"]->it("No"), "no");
            
            $title = $_SESSION["translate"]->it("Delete Form Element?");
            $content = $_SESSION["translate"]->it("Are you sure you wish to delete this form element?");
            $content .= PHPWS_Form::makeForm("phpwslistings_element_delete", "index.php", $elements);
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
        if(!$_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_element")) {
            $this->_error("access_denied");
            return;
        }

        $this->_id = $GLOBALS["core"]->sqlInsert($data, "mod_listings_formelements", FALSE, TRUE);

        if($this->_id)
            return TRUE;
        else
            return FALSE;
    }


    /**
    * Updates record
    *
    * Updates existing element record
    *
    * @access public
    * @see save()
    */
    function update($data) {
        if(!$_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_element")) {
            $this->_error("access_denied");
            return;
        }

        if($GLOBALS["core"]->sqlUpdate($data,"mod_listings_formelements","id",$this->_id))
            return TRUE;
        else
            return FALSE;
    }


    /**
    * Displays form element
    *
    * Displays editable form element
    *
    * @access public
    */
    function renderFormElement($value)
    {
        // If element is agent-only and user isn't an active agent, return
        if($this->_agentonly && !$_SESSION["OBJ_user"]->getUserVar("active",$_SESSION["OBJ_user"]->user_id,"phpwslistings"))
            return;
        $formelement = "";

        if($this->_field_type != "blank") {

            if($this->_required == 1){
                if($this->_customLayout !== 1):
                    $formelement["required"] = "<font color=\"red\"><b>*</b></font>";
                else:
                    $formelement["required"] = "*";
                endif;
            }
            $formelement["label"] = $this->_field_caption;
            $field_name = "LISTING_" . $this->_field_name;
            if(empty($value))
                $value = $this->_default_text;
    
            switch($this->_field_type) {
                case "radio":
                    $formelement["value"] .= "<br />";
                    $field_elements = explode("||",$this->_field_elements);
                    foreach($field_elements as $el)
                        $formelement["value"] .= PHPWS_Form::formRadio($field_name,$el,$value,NULL,$el) . "<br />";
                    break;
                case "text":
                    $formelement["value"] = PHPWS_Form::formTextField($field_name,$value);
                    break;
                case "textarea":
                    $formelement["value"] = "<br />" . PHPWS_Form::formTextArea($field_name,$value);
                    break;
                case "checkbox":
                    $formelement["value"] .= "<br />";
                    $field_elements = explode("||",$this->_field_elements);
                    $field_values = explode("||",$value);
                    $field_name .= "[]";
                    foreach($field_elements as $el)
                        if(in_array($el,$field_values))
                            $formelement["value"] .= PHPWS_Form::formCheckBox($field_name,$el,$el,NULL,$el) . "<br />";
                        else
                            $formelement["value"] .= PHPWS_Form::formCheckBox($field_name,$el,NULL,NULL,$el) . "<br />";
                    break;
                case "select":
                    $field_elements = explode("||",$this->_field_elements);
                    $formelement["value"] = PHPWS_Form::formSelect($field_name,$field_elements,$value,TRUE);
                    break;
                case "multiple":
                    $field_elements = explode("||",$this->_field_elements);
                    $field_values = explode("||",$value);
                    $formelement ["value"] = "<br />" . PHPWS_Form::formMultipleSelect($field_name,$field_elements,$field_values,TRUE);
                    break;
            }
        }

        return $formelement;

    }


    /**
    * Displays form element for read-only
    *
    * Displays read-only form element
    *
    * @access public
    */
    function displayFormElement($value)
    {
        // If element is agent-only and user isn't an active agent, return
        if($this->_agentonly && !$_SESSION["OBJ_user"]->getUserVar("active",$_SESSION["OBJ_user"]->user_id,"phpwslistings"))
            return;

        if(empty($value))
            return;

        $formelement = "";
        if($this->_field_type != "blank") {
            $formelement["label"] = $this->_field_caption;

            switch($this->_field_type) {
                case "checkbox":
                case "multiple":
                    $formelement["value"] .= "<ul>";
                    $field_values = explode("||",$value);
                    foreach ($field_values as $val)
                        $formelement["value"] .= "<li>$val</li>";
                    $formelement["value"] .= "</ul>";
                    break;
                case "textarea":
                    $formelement["value"] = "<br />$value";
                    break;
                default:
                    $formelement["value"] = "$value";
            }
        }

        return $formelement;

    }


    /**
    * Displays form element for read-only browsing
    *
    * Displays read-only form element
    *
    * @access public
    */
    function displayBrowseElement($value)
    {
        // If element is agent-only and user isn't an active agent, return
        if($this->_agentonly && !$_SESSION["OBJ_user"]->getUserVar("active",$_SESSION["OBJ_user"]->user_id,"phpwslistings"))
            return;
            
        if(empty($value))
            return;
            
        if(!$this->_display_on_browse)
            return;
            
            $formelement = "";
            $formelement["label"] = $this->_field_caption;
            switch($this->_field_type) {
                case "checkbox":
                case "multiple":
                    $formelement["value"] .= "<ul>";
                    $field_values = explode("||",$value);
                    foreach ($field_values as $val)
                        $formelement["value"] .= "<li>$val</li>";
                    $formelement["value"] .= "</ul>";
                    break;
                default:
                    $formelement["value"] = "$value";
            }

        return $formelement;

    }// END FUNC displayBrowseElement()


    function _error($type) {
        $title = "<font class=\"errortext\">" . $_SESSION["translate"]->it("ERROR!") . "</font>";

        switch($type) {
            case "access_denied":
                $content = $_SESSION["translate"]->it("ACCESS DENIED!");
                break;
            case "save_failed":
                $content = $_SESSION["translate"]->it("There was a problem saving your form element.") . " " . $_SESSION["translate"]->it("If the problem persists contact your system administrator.");
                break;
            case "update_failed":
                $content = $_SESSION["translate"]->it("There was a problem updating your form element.") . " " . $_SESSION["translate"]->it("If the problem persists contact your system administrator.");
                break;
            case "no_class":
                $content = $_SESSION["translate"]->it("You did not specify a class for this form element.");
                break;
        }

        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");

    }// END FUNC _error()

}// END CLASS SEILER_ListingElement

?>

<?php
/**
* SEILER_Listing for phpwsListings
*
* SEILER_Listing represents a listing item
*
* @author Don Seiler <don@seiler.us>
* @version $Id: Listing.php,v 1.61 2003/12/18 20:17:23 wendall911 Exp $
* @package phpwsListings
*/

require_once(PHPWS_SOURCE_DIR.'core/Text.php');
require_once(PHPWS_SOURCE_DIR.'core/Form.php');
require_once(PHPWS_SOURCE_DIR.'mod/help/class/CLS_help.php');

class SEILER_Listing {

    var $_id = NULL;
    var $_class_id = NULL;
    var $_agent_id = NULL;
    var $_title = NULL;
    var $_notes = NULL;
    var $_creationdate = 0;
    var $_lastmodified = 0;
    var $_active = 0;
    var $_hits = 0;
    var $_sold = 0;
    var $_solddate = 0;
    var $_price = 0;
    var $_feature = 1;
    var $_expiration = NULL;
    var $_tmplistelements = NULL;
    var $_listelements = array();
    var $_formelements;
    var $_images = array();
    var $_new = FALSE;
    var $_class = NULL;

    function SEILER_Listing($LISTING_id = NULL) {
        $this->_formelements =& $_SESSION["SES_LISTING_MANAGER"]->getFormElements();

        if($LISTING_id === NULL) {
            $this->_new = TRUE;

            $this->_solddate = date("Y-m-d");
            $this->_agent_id = $_SESSION["OBJ_user"]->user_id;
            if($_SESSION["SES_LISTING_MANAGER"]->_useExpiration) {
                $year = date("Y");
                $year += 5;
                $month = date("m");
                $day = date("d");
                $this->_expiration = "$year-$month-$day";
            }
        } else {
            $result = $GLOBALS["core"]->sqlSelect("mod_listings", "id", $LISTING_id);
            
            $this->_new = FALSE;
            $this->_id = $result[0]["id"];
            $this->_class_id = $result[0]["class_id"];
            $this->_agent_id = $result[0]["agent_id"];
            $this->_title = $result[0]["title"];
            $this->_notes = $result[0]["notes"];
            $this->_creationdate = $result[0]["creationdate"];
            $this->_expiration = $result[0]["expiration"];
            $this->_lastmodified = $result[0]["lastmodified"];
            $this->_active = $result[0]["active"];
            $this->_hits = $result[0]["hits"];
            $this->_price = $result[0]["price"];
            $this->_feature = $result[0]["feature"];
            $this->_sold = $result[0]["sold"];
            $this->_solddate = $result[0]["solddate"];
            
            $this->_class = new SEILER_ListingClass($this->_class_id);

            if(is_null($this->_expiration))
                if($_SESSION["SES_LISTING_MANAGER"]->_useExpiration) {
                    $year = date("Y");
                    $year += 5;
                    $month = date("m");
                    $day = date("d");
                    $this->_expiration = "$year-$month-$day";
                }
                
            if (!empty($this->_lastmodified)){
                $lmtsmonth  = substr($this->_lastmodified,4,2);
                $lmtsday    = substr($this->_lastmodified,6,2);
                $lmtsyear   = substr($this->_lastmodified,0,4);
                $lmtshour   = substr($this->_lastmodified,8,2);
                $lmtsmin    = substr($this->_lastmodified,10,2);
                $lmtssec    = substr($this->_lastmodified,12,2);
                $lmtsepoch  = mktime($lmtshour,$lmtsmin,$lmtssec,$lmtsmonth,$lmtsday,$lmtsyear);
                $this->_lastmodified   = date ('Y-m-d H:m:s T', $lmtsepoch);
            }
            
            $this->_tmplistelements = $result[0]["listelements"];
            if(!$this->loadListElements()) {
                $this->_error("load_list_elements");
                return;
            }

            if(!$this->loadImages()) {
                $this->_error("load_images");
                return;
            }
        }
    }


    function setClassId($CLASS_id = NULL) {
        if(!empty($CLASS_id)) {
            $this->_class_id = $CLASS_id;
            $this->_class = new SEILER_ListingClass($this->_class_id);
        } else {
            $this->_error("no_class");
            return;
        }
    }


    function view($type) {
        if(!$this->_active && !$_SESSION["OBJ_user"]->allow_access("phpwslistings") && ($this->_agent_id != $_SESSION["OBJ_user"]->user_id))
            return;

        if($_SESSION["OBJ_user"]->allow_access("phpwslistings") || ($this->_agent_id == $_SESSION["OBJ_user"]->user_id))
            $tags["EDITLINK"] = "<a href=\"index.php?module=phpwslistings&amp;LISTINGS_op=" . $_SESSION["translate"]->it("Edit") . "&amp;LISTING_id=" . $this->_id . "\">" . $_SESSION["translate"]->it("Edit Listing") . "</a>";

        $tags["AGENT_ID"] = $this->_agent_id;
        $agentUser = new PHPWS_User($this->_agent_id);
        $agency_id = $agentUser->getUserVar("agency_id",$agentUser->user_id,"phpwslistings");
        if(!empty($agency_id)) {
            $agency = new SEILER_ListingAgency($agency_id);
            $tags["AGENCY_LABEL"] = $_SESSION["translate"]->it("Agency");
            $tags["AGENCYLINK"] = "<a href=\"index.php?module=phpwslistings&amp;AGENCY_id=" . $agency->_id . "&amp;LISTINGS_user_op=viewagency\">" . $agency->_name . "</a>";  
        }
        $tags["AGENT_EMAIL"] = $agentUser->email;
        $tags["AGENT_LABEL"] = $_SESSION["translate"]->it("Agent");
        $tags["LISTEDON_LABEL"] = $_SESSION["translate"]->it("Listed on");
        $tags["UPDATEDON_LABEL"] = $_SESSION["translate"]->it("Updated on");
        $agentFName = $agentUser->getUserVar("first_name",$agentUser->user_id,"phpwslistings");
        $agentLName = $agentUser->getUserVar("last_name",$agentUser->user_id,"phpwslistings");
        if(empty($agentFName) || empty($agentLName))
            $agentName = $agentUser->username;
        else
            $agentName = $agentFName . " " . $agentLName;

        $tags["AGENTLINK"] = "<a href=\"index.php?module=phpwslistings&amp;AGENT_id=" . $agentUser->user_id . "&amp;LISTINGS_user_op=viewagent\">" . $agentName . "</a>";  
        $tags["TITLE"] = $this->_title;
        if(!empty($this->_notes)) {
            $tags["NOTES_LABEL"] = $_SESSION["translate"]->it("Notes");
            $tags["NOTES"] = $this->_notes;
        }
        $tags["CREATIONDATE"] = $this->_creationdate;
        $tags["LASTMODIFIED"] = $this->_lastmodified;

        $tags["EXPIRATION_LABEL"] = $_SESSION["translate"]->it("Expiration Date");
        $tags["EXPIRATION"] = $this->_expiration;

        $tags["HITS"] = $this->_hits;

        if($this->_class->_usePrice) {
            $tags["PRICE_LABEL"] = $_SESSION["translate"]->it("Price");
            $tags["PRICE_SIGN"] = $_SESSION["translate"]->it("$");
            $tags["PRICE"] = number_format($this->_price);
        }

        if($this->_feature)
            $tags["FEATURE"] = $_SESSION["translate"]->it("Featured");

        if($this->_sold) {
            $tags["TITLE"] = $this->_title . " - " . $_SESSION["translate"]->it("SOLD!");
            $tags["SOLD"] = $_SESSION["translate"]->it("Sold on ") . $this->_solddate;
        }


        $print_icon = "<a href=\"index.php?module=phpwslistings&amp;LISTINGS_user_op=view_printable&amp;LISTING_id=" . $this->_id . "&amp;lay_quiet=1\" target=\"_blank\"><img src=\"mod/phpwslistings/img/icon_print.png\" border=\"0\" alt=\"" . $_SESSION["translate"]->it("Printable Version") . "\"/>" . $_SESSION["translate"]->it("Printable Version") . "</a>";
        $tags["PRINTABLE"] = $print_icon;
        $email_icon = "<a href=\"index.php?module=phpwslistings&amp;LISTINGS_user_op=email&amp;EMAIL_type=friend&amp;LISTING_id=" . $this->_id . "\"><img src=\"mod/phpwslistings/img/icon_email.png\" border=\"0\" alt=\"" . $_SESSION["translate"]->it("Send to a Friend") . "\"/>" . $_SESSION["translate"]->it("Send to a Friend") . "</a>";
        $tags["EMAIL_FRIEND"] = $email_icon;
        $email_icon = "<a href=\"index.php?module=phpwslistings&amp;LISTINGS_user_op=email&amp;EMAIL_type=agent&amp;LISTING_id=" . $this->_id . "\"><img src=\"mod/phpwslistings/img/icon_email.png\" border=\"0\" alt=\"" . $_SESSION["translate"]->it("Contact Agent") . "\"/>" . $_SESSION["translate"]->it("Contact Agent") . "</a>";
        $tags["EMAIL_AGENT"] = $email_icon;
        
        if($type == "printable") {
            $formelements = "";
            for($i=0; $i<count($this->_formelements); $i++) {
                if(isset($this->_listelements[$this->_formelements[$i]->_field_name])) {
                    $field_value = $this->_listelements[$this->_formelements[$i]->_field_name];
                    $formelement[$i] = $this->_formelements[$i]->displayFormElement($field_value);
                    $label = $formelement[$i]["label"];
                    $value = $formelement[$i]["value"];
                    if($_SESSION["SES_LISTING_MANAGER"]->_customLayout == 1):
                        $rank = $this->_formelements[$i]->_rank;
                        $element_label = "FORMELEMENT".$rank."_LABEL";
                        $element = "FORMELEMENT$rank";
                        $tags[$element_label] = $label;
                        $tags[$element] = $value;
                    else:
                        $item = $formelement[$i];
                        if(is_array($item)){
                            $element .= "<b>".$label."</b>: ".$value."<br />";
                        }
                    endif;                    
                }
            }
            if($element):
                $tags["FORMELEMENTS"] = $element;
            endif;
            $tags["PAGE_TITLE"] = $this->_title;
            $tags["STYLESHEET"]="http://".$_SESSION["OBJ_layout"]->theme_address."style.css";
   
            $main_image = "";
            $thumb_images = "";
            for($i=0; $i<count($this->_images); $i++) {
                if($this->_images[$i]->_rank == 1):
                    $main_image = $this->_images[$i]->renderFull();
                else:
                    $thumb_image[$i] = $this->_images[$i]->renderThumb();
                    if($_SESSION["SES_LISTING_MANAGER"]->_customLayout == 1){
                        $rank = $this->_images[$i]->_rank;
                        $value = $thumb_image[$i];
                        $element = "THUMBIMAGE$rank";
                        $tags[$element] = $value;
                    }
                endif;
            }
            if((is_array($thumb_image)) && ($_SESSION["SES_LISTING_MANAGER"]->_customLayout !== 1)):
                $value = implode('<br /><br />', $thumb_image);
                $tags["THUMBIMAGES"] = $value;
            endif;
            $tags["MAINIMAGE"] = $main_image;
            if($_SESSION["SES_LISTING_MANAGER"]->_customLayout == 1){
                echo PHPWS_Template::processTemplate($tags, "phpwslistings", "cust_view_printable.tpl");
            }else{
                echo PHPWS_Template::processTemplate($tags, "phpwslistings", "view_printable.tpl");
            }
        } else {
            /* Full view of the listing */
            if($type == "full") {
                if($this->_class->_useMortCalc) {
                    $mortcalcurl = "mod/phpwslistings/mortgage_calculator.php?listing_price=" . $this->_price;
                    $tags["MORTCALC"] = "<font size=\"-2\"><a href=\"$mortcalcurl\" target=\"_BLANK\" onClick=\"window.open('$mortcalcurl','_BLANK','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=550,height=600');return false;\">" . $_SESSION["translate"]->it("Mortgage Calculator") . "</a><br />(" . $_SESSION["translate"]->it("Opens New Window") . ")</font>";
                }
                $formelements = "";
                for($i=0; $i<count($this->_formelements); $i++) {
                    if(isset($this->_listelements[$this->_formelements[$i]->_field_name])) {
                        $field_value = $this->_listelements[$this->_formelements[$i]->_field_name];
                        $formelement[$i] = $this->_formelements[$i]->displayFormElement($field_value);
                        $value = $formelement[$i]["value"];
                        $label = $formelement[$i]["label"];
                        if($_SESSION["SES_LISTING_MANAGER"]->_customLayout == 1):
                            $rank = $this->_formelements[$i]->_rank;
                            $element_label = "FORMELEMENT".$rank."_LABEL";
                            $element = "FORMELEMENT$rank";
                            $tags[$element_label] = $label;
                            $tags[$element] = $value;
                        else:
                            $item = $formelement[$i];
                            if(is_array($item)){
                                $element .= "<b>".$label."</b>: ".$value."<br />";
                            }
                        endif;                    
                    }
                }
                
                if($element):
                $tags["FORMELEMENTS"] = $element;
                endif;
                
                $main_image = "";
                $thumb_images = "";
                for($i=0; $i<count($this->_images); $i++) {
                    if($this->_images[$i]->_rank == 1):
                        if($_SESSION["SES_LISTING_MANAGER"]->_customLayout == 1){
                            $main_image = $this->_images[$i]->renderCustFull();
                            $tags["MAINIMAGE_CAPTION"] = $main_image["caption"];
                            $tags["MAINIMAGE"] = $main_image["image"];
                        }else{
                            $main_image = $this->_images[$i]->renderFull();
                            $tags["MAINIMAGE"] = $main_image;
                        }
                    else:
                        $thumb_image[$i] = $this->_images[$i]->renderThumb();
                        if($_SESSION["SES_LISTING_MANAGER"]->_customLayout == 1){
                            $rank = $this->_images[$i]->_rank;
                            $value = $thumb_image[$i];
                            $element = "THUMBIMAGE$rank";
                            $tags[$element] = $value;
                        }
                    endif;
                }
                if((is_array($thumb_image)) && ($_SESSION["SES_LISTING_MANAGER"]->_customLayout !== 1)):
                    $value = implode('<br /><br />', $thumb_image);
                    $tags["THUMBIMAGES"] = $value;
                endif;
                
                if($_SESSION["SES_LISTING_MANAGER"]->_customLayout == 1):
                    $content = PHPWS_Template::processTemplate($tags, "phpwslistings", "cust_view_full.tpl");
                else:
                    $content = PHPWS_Template::processTemplate($tags, "phpwslistings", "view_full.tpl");
                endif;
    
    
            } elseif ($type = "small") {
                if(!$this->_active)
	                return;
    
                /* Summarized view of listings, user on home page */
                $main_image = "";
                for($i=0; $i<count($this->_images); $i++) {
                    if($this->_images[$i]->_rank == 1)
                        $main_image = $this->_images[$i]->renderThumb();
                }
                $tags["MAINIMAGE"] = $main_image;
    
                $formelements = "";
                for($i=0; $i<count($this->_formelements); $i++) {
                    if(isset($this->_listelements[$this->_formelements[$i]->_field_name])) {
                        $field_value = $this->_listelements[$this->_formelements[$i]->_field_name];
                        $formelement[$i] = $this->_formelements[$i]->displayBrowseElement($field_value);
                        $label = $formelement[$i]["label"];
                        $value = $formelement[$i]["value"];
                        if($_SESSION["SES_LISTING_MANAGER"]->_customLayout == 1):
                            $rank = $this->_formelements[$i]->_rank;
                            $element_label = "FORMELEMENT".$rank."_LABEL";
                            $element = "FORMELEMENT$rank";
                            $tags[$element_label] = $label;
                            $tags[$element] = $value;
                        else:
                            $item = $formelement[$i];
                            if(is_array($item)){
                                $element .= $label.": ".$value."<br />";
                            }
                        endif;                    
                    }
                }
                if($element):
                    $tags["FORMELEMENTS"] = $element;
                endif;
                $tags["READ_MORE"] = "<a href=\"index.php?module=phpwslistings&amp;LISTINGS_user_op=view&amp;LISTING_id=" . $this->_id .  "\">See details</a>";
                if($_SESSION["SES_LISTING_MANAGER"]->_customLayout == 1):
                    $content = PHPWS_Template::processTemplate($tags, "phpwslistings", "cust_view_small.tpl");
                else:
                    $content = PHPWS_Template::processTemplate($tags, "phpwslistings", "view_small.tpl");
                endif;
            }
            $title = NULL;
            $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
        }
    }


    function renderBlock() {
        $content = "";
        if((count($this->_images) > 0) || (!$_SESSION["SES_LISTING_MANAGER"]->_blockImagesOnly)) {
            $content["thumb"] = $this->getMainThumb();
            $content["title"] = $this->_title;
            if($this->_class->_usePrice){
                $content["price_label"] = $_SESSION["translate"]->it("Price"); 
                $content["price"] = $_SESSION["translate"]->it("$") . number_format($this->_price,2);
            }
            $content["details"] = "<a href=\"index.php?module=phpwslistings&amp;LISTINGS_user_op=view&amp;LISTING_id=" . $this->_id .  "\">" . $_SESSION["translate"]->it("See details!") . "</a>";
        }
        return $content;
    }
    

    function formatDate($in_date) {
        return substr(preg_replace("/[^0-9]/", "", $in_date), 0, 8);
    }

    function edit() {
        if(is_null($this->_class))
            return;

        $tags["CLASS_LABEL"] = $_SESSION["translate"]->it("Class");
        $tags["CLASS"] = PHPWS_Form::formHidden("LISTING_class_id", $this->_class_id) . "<b>" . $this->_class->_name . "</b>";
        $tags["AGENTID_LABEL"] = $_SESSION["translate"]->it("Agent");
        $agents = $this->_getUsers();
        if($_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_listing"))
            $tags["AGENTID"] = PHPWS_Form::formSelect("LISTING_agent_id", $agents, $this->_agent_id, FALSE, TRUE);
        else
            $tags["AGENTID"] = PHPWS_Form::formHidden("LISTING_agent_id", $this->_agent_id) . "<b>" . $agents[$this->_agent_id] . "</b>";
        $tags["TITLE_LABEL"] = $_SESSION["translate"]->it("Title");
        $tags["TITLE"] = PHPWS_Form::formTextField("LISTING_title", $this->_title, 33, 80);
        $tags["NOTES_LABEL"] = $_SESSION["translate"]->it("Notes");
        $tags["NOTES"] = PHPWS_WizardBag::js_insert("wysiwyg", "phpwslistings_edit", "LISTING_notes") . PHPWS_Form::formTextArea("LISTING_notes", $this->_notes, 5, 40);
        if($_SESSION["SES_LISTING_MANAGER"]->_useExpiration) {
            $tags["EXPIRATION_LABEL"] = $_SESSION["translate"]->it("Expiration Date");
            $tags["EXPIRATION"] = PHPWS_Form::formDate("LISTING_expiration", $this->formatDate($this->_expiration));
        }

        if($this->_class->_usePrice) {
            $tags["PRICE_LABEL"] = $_SESSION["translate"]->it("Price");
            $tags["PRICE_SIGN"] = $_SESSION["translate"]->it("$");
            $tags["PRICE"] = PHPWS_Form::formTextField("LISTING_price", $this->_price, 10, 20);
        }

        $soldtrlabel = $_SESSION["translate"]->it("Sold");
        $tags["SOLD"] = PHPWS_Form::formCheckbox("LISTING_sold", 1, $this->_sold, NULL, $soldtrlabel);
        if($_SESSION["OBJ_user"]->allow_access("phpwslistings","feature_listing")){
            $featuretrlabel = $_SESSION["translate"]->it("Feature");
            $tags["FEATURE"] = PHPWS_Form::formCheckbox("LISTING_feature", 1, $this->_feature, NULL, $featuretrlabel);
        }

        if(!$this->_new) {
            if(count($this->_images) < $_SESSION["SES_LISTING_MANAGER"]->_listingImageLimit)
                $tags["ADDIMAGELINK"] = "<a href=\"./index.php?module=phpwslistings&LISTINGS_op=addimage&LISTING_id=" . $this->_id . "&LISTING_agent_id=" . $this->_agent_id . "\">" . $_SESSION["translate"]->it("Add Image") . "</a>";

            if(count($this->_images))
                $tags["EDITIMAGELABEL"] = $_SESSION["translate"]->it("Click image to edit");
            $thumb_images = "";
            for($i=0; $i<count($this->_images); $i++) {
                $thumb_images .= $this->_images[$i]->renderThumb(TRUE);
            }
            $tags["EDITIMAGES"] = $thumb_images;
        }

        $tags["SOLDDATE_LABEL"] = $_SESSION["translate"]->it("Date Sold");
        $tags["SOLDDATE"] = PHPWS_Form::formDate("LISTING_solddate", $this->formatDate($this->_solddate));

        // Magic Time
        $formelements = "";
        for($i=0; $i<count($this->_formelements); $i++) {
            if($this->_formelements[$i]->_class_id == $this->_class_id) {
                $value = "";
                if(!$this->_new)
                    if(isset($this->_listelements[$this->_formelements[$i]->_field_name]))
                        $value = $this->_listelements[$this->_formelements[$i]->_field_name];
    
                $formelement[$i] = $this->_formelements[$i]->renderFormElement($value);
                $item = $formelement[$i];
                if(is_array($item)){
                    $element .= implode('', $item)."<br />";
                }
            }
        }
        if($element):
            $tags["FORMELEMENTS"] = $element;
        endif;
        $tags["SUBMIT_BUTTON"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Save"), "LISTINGS_op");

        $elements[0] = PHPWS_Form::formHidden("module", "phpwslistings");
        $elements[0] .= PHPWS_Template::processTemplate($tags, "phpwslistings", "edit.tpl");

        if($this->_new) $title = $_SESSION["translate"]->it("Create Listing");
        else $title = $_SESSION["translate"]->it("Edit Listing");

        $content = PHPWS_Form::makeForm("phpwslistings_edit", "index.php", $elements, "post", FALSE, TRUE);
        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
    }

    function save() {
        if($_REQUEST["LISTINGS_op"] == $_SESSION["translate"]->it("Save") && !$_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_listing") && ($this->_agent_id != $_SESSION["OBJ_user"]->user_id)) {
            $this->_error("access_denied");
            return;
        }

        $this->_class_id = PHPWS_Text::parseInput($_POST["LISTING_class_id"]);
        $data["class_id"] = $this->_class_id;
        $this->_agent_id = PHPWS_Text::parseInput($_POST["LISTING_agent_id"]);
        $data["agent_id"] = $this->_agent_id;
        $this->_notes = PHPWS_Text::parseInput($_POST["LISTING_notes"]);
        $data["notes"] = $this->_notes;
        $this->_lastmodified = date('Y-m-d H:m:s');
        $data["lastmodified"] = $this->_lastmodified;
        $this->_price = PHPWS_Text::parseInput($_POST["LISTING_price"]);

        // Need to handle number formatting
        $lastperiod = strpos($this->_price, ".");
        $lastcomma = strpos($this->_price, ",");
        if($lastperiod && $lastcomma) {
            if($lastperiod > $lastcomma) {
                // period is decimal point
                // strip out commas
                $this->_price = preg_replace("/,/", "", $this->_price);
            } else {
                // comma is decimal point
                // strip out periods, then replace comma with period
                $this->_price = preg_replace("/\./", "", $this->_price);
                $this->_price = preg_replace("/,/", ".", $this->_price);
            }
        } elseif($lastperiod) {
            // period is decimal point or there is no decimal point
            // don't need to do anything
        } elseif($lastcomma) {
            // comma is decimal point or there is no decimal point
            // replace comma with period
            $this->_price = preg_replace("/,/", ".", $this->_price);
        }

        $data["price"] = $this->_price;

        if(isset($_POST["LISTING_sold"])) {
            $this->_sold = 1;
            $this->_solddate = $_POST["LISTING_solddate_year"] . "-" . $_POST["LISTING_solddate_month"] . "-" . $_POST["LISTING_solddate_day"] . " 00:00:00";
        } else {
            $this->_sold = 0;
            $this->_solddate = NULL;
        }

        $data["sold"] = $this->_sold;
        $data["solddate"] = $this->_solddate;

        if($_SESSION["SES_LISTING_MANAGER"]->_useExpiration)
            $this->_expiration = $_POST["LISTING_expiration_year"] . "-" . $_POST["LISTING_expiration_month"] . "-" . $_POST["LISTING_expiration_day"] . " 00:00:00";
        else
            $this->_expiration = NULL;
        $data["expiration"] = $this->_expiration;

        $data["listelements"] = $this->serializeListElements();
        if($data["listelements"] == "error"){
            $this->_error("no_required_formelement"); 
            $this->edit();
            return;
        }   

        if($_SESSION["OBJ_user"]->allow_access("phpwslistings","feature_listing")){
            if(isset($_POST["LISTING_feature"]))
                $this->_feature = 1;
            else 
                $this->_feature = 0;
            $data["feature"] = $this->_feature;
        }

        if($_POST["LISTING_title"]) {
            $this->_title = PHPWS_Text::parseInput($_POST["LISTING_title"]);
            $data["title"] = $this->_title;
        } else {
            $this->_error("no_title");
            $this->edit();
            return;
        }

        if($this->_new) {
            if($this->add($data)) {
	            $title = $_SESSION["translate"]->it("Listing Saved");
	            $content = $_SESSION["translate"]->it("Your listing was successfully saved.");
                $content .= "  <a href=\"./index.php?module=phpwslistings&LISTINGS_op=" . $_SESSION["translate"]->it("Edit") . "&LISTING_id=" . $this->_id . "\">" . $_SESSION["translate"]->it("Go back to your listing.") . "</a>";

            } else {
	            $this->_error("save_failed");
	            $this->edit();
	            return;
            }
        } elseif ($this->_id) {
            if($this->update($data)) {
	            $title = $_SESSION["translate"]->it("Listing Updated");
	            $content = $_SESSION["translate"]->it("Your listing was successfully updated.");
                $content .= "  <a href=\"./index.php?module=phpwslistings&LISTINGS_op=" . $_SESSION["translate"]->it("Edit") . "&LISTING_id=" . $this->_id . "\">" . $_SESSION["translate"]->it("Go back to your listing.") . "</a>";
            } else {
	            $this->_error("update_failed");
	            $this->edit();
	            return;
            }
        }


        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
    }

    function delete() {
        if(!$_SESSION["OBJ_user"]->allow_access("phpwslistings", "delete_listing") && ($this->_agent_id != $_SESSION["OBJ_user"]->user_id)) {
            $this->_error("access_denied");
            return;
        }

        if(isset($_POST["yes"])){
            for($i=0; $i<count($this->_images); $i++) {
                $this->_images[$i]->delete(TRUE);
            }
            $GLOBALS["core"]->sqlDelete("mod_listings", "id", $this->_id);
            $title = $_SESSION["translate"]->it("Listing Deleted!");
            $content = $_SESSION["translate"]->it("The listing was successfully <b>deleted</b>.");
        } elseif (isset($_POST["no"])) {
            $title = $_SESSION["translate"]->it("Listing Kept!");
            $content = $_SESSION["translate"]->it("You have chosen <b>not</b> to delete the listing.");
        } else {
            $elements[0] = PHPWS_Form::formHidden("module", "phpwslistings");
            $elements[0] .= PHPWS_Form::formHidden("LISTINGS_op", "Delete");
            $elements[0] .= PHPWS_Form::formHidden("LISTING_id", $this->_id);
            $elements[0] .= PHPWS_Form::formSubmit($_SESSION["translate"]->it("Yes"), "yes");
            $elements[0] .= PHPWS_Form::formSubmit($_SESSION["translate"]->it("No"), "no");

            $title = $_SESSION["translate"]->it("Delete Listing?");
            $content = $_SESSION["translate"]->it("Are you sure you wish to delete this listing?");
            $content .= PHPWS_Form::makeForm("phpwslistings_delete", "index.php", $elements);
        }
        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
    }

  function add($data) {
    if($_REQUEST["LISTINGS_op"] == $_SESSION["translate"]->it("Save") && !$_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_listing") && ($this->_agent_id != $_SESSION["OBJ_user"]->user_id)) {
      $this->_error("access_denied");
      return;
    }

    $this->_creationdate = date('Y-m-d H:m:s');
    $data["creationdate"] = $this->_creationdate;

    $this->_id = $GLOBALS["core"]->sqlInsert($data, "mod_listings", FALSE, TRUE);


    if($this->_id)
      return TRUE;
    else
      return FALSE;
  }

  function update($data) {
    if(!$_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_listing") && !isset($data["hits"]) && ($this->_agent_id != $_SESSION["OBJ_user"]->user_id)) {
      $this->_error("access_denied");
      return;
    }

    if($GLOBALS["core"]->sqlUpdate($data, "mod_listings", "id", $this->_id))
      return TRUE;
    else
      return FALSE;
  }

  function hit() {
    $this->_hits++;
    $data["hits"] = $this->_hits;
    $this->update($data);
  }

  function showHide() {
    if(!$_SESSION["OBJ_user"]->allow_access("phpwslistings", "activate_listing") && ($_SESSION["OBJ_user"]->user_id != $this->_agent_id)) {
      $this->_error("access_denied");
      return;
    }

    PHPWS_WizardBag::toggle($this->_active);
    $data["active"] = $this->_active;
    $this->update($data);
  }

  /**
   * Returns an indexed array of all the current groups in the database
   *
   * @return array $users An array of all groups
   * @access private
   * @see    edit()
   */
  function _getUsers() {
    /* Grab all users from database */
    $result = $GLOBALS["core"]->sqlSelect("mod_users", NULL, NULL, "username");

    /* Add blank user */
    $users[] = " ";

    /* Create user array */
    if($result)
        foreach($result as $resultRow) {
            $agentUser = new PHPWS_User($resultRow["user_id"]);
            $agentFName = $agentUser->getUserVar("first_name",$agentUser->user_id,"phpwslistings");
            $agentLName = $agentUser->getUserVar("last_name",$agentUser->user_id,"phpwslistings");
            if(empty($agentFName) || empty($agentLName))
                $users[$resultRow["user_id"]] = $resultRow["username"];
            else
                $users[$resultRow["user_id"]] = $agentFName . " " . $agentLName;
        }

    return $users;
  }// END FUNC _getUsers()



    function _error($type) {
        $title = "<font class=\"errortext\">" . $_SESSION["translate"]->it("ERROR!") . "</font>";

        switch($type) {
            case "no_class":
                $content = $_SESSION["translate"]->it("You did not select a class for your listing.");
                break;

            case "no_title":
                $content = $_SESSION["translate"]->it("You did not provide a title for your listing.");
                break;

            case "no_required_formelement":
                $content = $_SESSION["translate"]->it("You are missing a required field.  Please make sure all required fields (marked with a \"<font color=\"red\"><b>*</b></font>\") are completed.");
                break;
    
            case "save_failed":
                $content = $_SESSION["translate"]->it("There was a problem saving your listing.") . " " . $_SESSION["translate"]->it("If the problem persists contact your system administrator.");
                break;

            case "update_failed":
                $content = $_SESSION["translate"]->it("There was a problem updating your listing.") . " " . $_SESSION["translate"]->it("If the problem persists contact your system administrator.");
                break;

            case "access_denied":
                $content = "<b>" . $_SESSION["translate"]->it("ACCESS DENIED!") . "</b>";
                break;
            case "load_form_elements":
                $content = "<b>" . $_SESSION["translate"]->it("Unable to load form elements!") . "</b>";
                break;
            case "load_list_elements":
                $content = "<b>" . $_SESSION["translate"]->it("Unable to load list item elements!") . "</b>";
                break;
            case "load_images":
                $content = "<b>" . $_SESSION["translate"]->it("Unable to load listing images!") . "</b>";
                break;
        }

        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
    }// END FUNC _error()


    function loadListElements() {
        $this->_listelements = unserialize($this->_tmplistelements);
        if(!is_array($this->_listelements))
            $this->_listelements = array();

        return TRUE;
    }


    function loadImages() {
        $this->_images = array();
        if(($tmp_images = $GLOBALS["core"]->sqlSelect("mod_listings_images","listing_id",$this->_id,"rank",NULL,NULL,NULL,NULL,FALSE)) == NULL) {
            return TRUE;
        } elseif(($tmp_images = $GLOBALS["core"]->sqlSelect("mod_listings_images","listing_id",$this->_id,"rank",NULL,NULL,NULL,NULL,FALSE)) == FALSE) {
            return FALSE;
        }


        for($i=0; $i<count($tmp_images); $i++)
            array_push($this->_images, new SEILER_ListingImage($tmp_images[$i]["id"]));

        return TRUE;
    }


    function serializeListElements() {
        if(count($this->_formelements)) {
            for($i=0; $i<count($this->_formelements); $i++) {
                if($this->_formelements[$i]->_class_id == $this->_class_id) {
                    $field_name = $this->_formelements[$i]->_field_name;
                    $form_field_name = "LISTING_" . $field_name;
                    $field_value = $_POST[$form_field_name];  
                    if($this->_formelements[$i]->_required) {
                        if(!isset($field_value) || empty($field_value)) {
                            return "error";
                        }
                    }
                    if(is_array($field_value)) {
                        $value = "";
                        while(list($foo,$bar) = each($field_value)) {
                            $value = "$value||$bar";
                        }
                        $value = substr($value,2);
                        $insert[$field_name] = PHPWS_Text::parseInput($value);
                    } else {
                        $field_value = PHPWS_Text::parseInput($field_value);  
                        $insert[$field_name] = $field_value;
                    }
                }
            }
    
            return serialize($insert);
        } else {
            return NULL;
        }
    }


    function getRandomThumb() {
        $this->loadImages();
        if(count($this->_images)) {
            $i = rand(0,count($this->_images) - 1);
            return $this->_images[$i]->renderThumb();
        }
    }

    function getMainThumb() {
        $this->loadImages();
        if(count($this->_images)) {
            return $this->_images[0]->renderThumb();
        }
    }


    function match($criteria) {
        // Compare elements in $criteria against elements of this listing.
        // Return true if match, else false

        for($i=0; $i<count($this->_formelements); $i++) {
            $name = $this->_formelements[$i]->_field_name;
            if(array_key_exists($name, $criteria)) {
                $value = $this->_listelements[$this->_formelements[$i]->_field_name];
                if(empty($value))
                    return FALSE;
                if($this->_formelements[$i]->_field_type == "text") {
                    // If text field, need to determine if value is supposed to be a number
                    if(is_numeric($criteria[$name])) {
                        if(is_numeric($value)) {
                            if($criteria[$name] < $value)
                                return FALSE;
                        } else {
                            // if one is numeric but other is not
                            return FALSE;
                        }
                    } else {
                        if(!substr_count(strtolower($value), strtolower($criteria[$name])))
                            return FALSE;
                    }
                } elseif(($this->_formelements[$i]->_field_type == "multiple") ||
                        ($this->_formelements[$i]->_field_type == "checkbox")) {
                    $hasMatch = FALSE;
                    foreach ($criteria[$name] as $v) {
                        if(preg_match("/||/", $value)){
                            $values = explode("||", $value);
                            foreach ($values as $val) {
                                if(strtolower($v) == strtolower($val))
                                    $hasMatch = TRUE;
                            }
                        } else {
                            // Need exact match
                            if(strtolower($criteria[$name]) == strtolower($value))
                                $hasMatch = TRUE;
                        }
                    }
                    if(!$hasMatch)
                        return FALSE;
                } else {
                    // Need exact match
                    if(strtolower($criteria[$name]) != strtolower($value))
                        return FALSE;
                }
            }

        }

        return TRUE;
    }


    function searchForm() {

        $tags["AGENTID_LABEL"] = $_SESSION["translate"]->it("Agent");
        $tags["AGENTID"] = PHPWS_Form::formSelect("LISTING_agent_id", $this->_getUsers(), NULL, FALSE, TRUE);

        $tags["AGENCY_LABEL"] = $_SESSION["translate"]->it("Agency");
        $tags["AGENCY"] = SEILER_ListingAgency::showSelect("LISTING_agency_id");

        $tags["TITLE_LABEL"] = $_SESSION["translate"]->it("Title");
        $tags["TITLE"] = PHPWS_Form::formTextField("LISTING_title", NULL, 33, 255);
        /*
        $tags["NOTES_LABEL"] = $_SESSION["translate"]->it("Notes");
        $tags["NOTES"] = PHPWS_WizardBag::js_insert("wysiwyg", "phpwslistings_edit", "LISTING_notes") . PHPWS_Form::formTextArea("LISTING_notes", $this->_notes, 5, 40);
        */

        if($this->_class->_usePrice) {
            $tags["MAX_PRICE_LABEL"] = $_SESSION["translate"]->it("Maximum Price");
            $tags["MIN_PRICE_LABEL"] = $_SESSION["translate"]->it("Minimum Price");
            $tags["PRICE_SIGN"] = $_SESSION["translate"]->it("$");
            $tags["MAX_PRICE"] = PHPWS_Form::formTextField("LISTING_maxprice", NULL, 10, 255);
            $tags["MIN_PRICE"] = PHPWS_Form::formTextField("LISTING_minprice", NULL, 10, 255);
        }

        /*
        $soldtrlabel = $_SESSION["translate"]->it("Sold");
        $tags["SOLD"] = PHPWS_Form::formCheckbox("LISTING_sold", 1, NULL, NULL, $soldtrlabel);

        $tags["SOLDDATE_LABEL"] = $_SESSION["translate"]->it("Date Sold");
        $tags["SOLDDATE"] = PHPWS_Form::formDate("LISTING_solddate", $this->formatDate($this->_solddate));
        */


        // Magic Time
        $formelements = "";
        for($i=0; $i<count($this->_formelements); $i++) {
            if($this->_formelements[$i]->_class_id == $this->_class_id) {
                $formelement[$i] = $this->_formelements[$i]->renderFormElement(NULL);
                $label = $formelement[$i]["label"];
                $value = $formelement[$i]["value"];
                $required = $formelement[$i]["required"];
                if($_SESSION["SES_LISTING_MANAGER"]->_customLayout == 1):
                    $rank = $this->_formelements[$i]->_rank;
                    $element_required = "FORMELEMENT_REQUIRED$rank";
                    $element_label = "FORMELEMENT".$rank."_LABEL";
                    $element = "FORMELEMENT$rank";
                    $tags[$element_required] = $required;
                    $tags[$element_label] = $label;
                    $tags[$element] = $value;
                 else:
                    $item = $formelement[$i];
                        if(is_array($item)){
                            if (!empty($required)):
                                $element .= "<font color=\"red\"><b>".$required."</b></font>".$label." :".$value."<br />";
                            else:
                                $element .= $label." :".$value."<br />";
                            endif;
                        }
                endif;                    
            }
        }
        if($element):
        $tags["FORMELEMENTS"] = $element;
        endif;
        $tags["SORT_LABEL"] = $_SESSION["translate"]->it("Sort By");
        $sortelements = array(
            "" => "",
            "title" => $_SESSION["translate"]->it("Title"),
            "creationdate" => $_SESSION["translate"]->it("Listing Date"),
            "price" => $_SESSION["translate"]->it("Price")
        );
        for($i=0; $i<count($this->_formelements); $i++) {
            // Don't show agent only fields unless user is an agent
            if($this->_formelements[$i]->_field_type != "blank")
                if((!$this->_formelements[$i]->_agentonly))
                    $sortelements[$this->_formelements[$i]->_field_name] = $this->_formelements[$i]->_field_caption;
        }
        $tags["SORT_BY"] = PHPWS_Form::formSelect("LISTING_sortby", $sortelements);
        $sortorders = array(
            "ASC" => "Ascending",
            "DESC" => "Descending"
        );
        $tags["SORT_ORDER"] = PHPWS_Form::formSelect("LISTING_sortorder", $sortorders,"ASC");


        $tags["SUBMIT_BUTTON"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Search Listings"), "LISTINGS_user_op");

        $elements[0] = PHPWS_Form::formHidden("module", "phpwslistings");
        $elements[0] .= PHPWS_Form::formHidden("LISTING_CLASS_id", $this->_class_id);
        if($_SESSION["SES_LISTING_MANAGER"]->_customLayout == 1){
            $elements[0] .= PHPWS_Template::processTemplate($tags, "phpwslistings", "cust_searchform.tpl");
        }else{
            $elements[0] .= PHPWS_Template::processTemplate($tags, "phpwslistings", "searchform.tpl");
        }

        $title = $_SESSION["translate"]->it("Search Listings");
        $content = PHPWS_Form::makeForm("phpwslistings_search", "index.php", $elements, "post", FALSE, TRUE);
        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
    }


    /**
     * Indicates if a listing is expired or not
     *
     * @return boolean TRUE if localtime is past
     *                 midnight on $this->_expiration
     * @access public
     */
    function expired() {
        if($_SESSION["SES_LISTING_MANAGER"]->_useExpiration) {
            // expiration is in YYYY-MM-DD format
            $yyyy = substr($this->_expiration, 0, 4);
            $mm = substr($this->_expiration, 5, 2);
            $dd = substr($this->_expiration, 8, 2);
            $exp = mktime(23,59,59,$mm,$dd,$yyyy);
    
            if(mktime() > $exp)
                return TRUE;
            else
                return FALSE;
        } else
            return FALSE;
    }

}// END CLASS PHPWS_Listing

?>

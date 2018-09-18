<?php
/**
* SEILER_ListingClass for phpwsListings
*
* SEILER_ListingClass represents an class to
* which an agent would belong.  Allows for
* sorting and searching class-wide.
*
* @author Don Seiler <don@seiler.us>
* @version $Id: ListingClass.php,v 1.15 2003/11/21 20:22:36 wendall911 Exp $
* @package phpwsListings
*/

class SEILER_ListingClass {
    var $_id = NULL;
    var $_name = NULL;
    var $_active = 1;
    var $_defaultClass = 0;
    var $_useMortCalc = 0;
    var $_usePrice = 1;
    var $_new = TRUE;

    function SEILER_ListingClass($CLASS_id = NULL) {
        if($CLASS_id == NULL) {
            $this->_new = TRUE;
        } else {
            $result = $GLOBALS["core"]->sqlSelect("mod_listings_classes", "id", $CLASS_id);
            $this->_new = FALSE;
            $this->_id = $result[0]["id"];
            $this->_name = $result[0]["name"];
            $this->_useMortCalc = $result[0]["use_mortcalc"];
            $this->_usePrice = $result[0]["use_price"];
            $this->_active = $result[0]["active"];
            $this->_defaultClass = $result[0]["default_class"];
        }
    }// END FUNC SEILER_ListingClass()


    function getName() {
        return $this->_name;
    }// END FUNC getName()


    function edit() {
        $tags["NAME_LABEL"] = $_SESSION["translate"]->it("Name");
        $tags["NAME"] = PHPWS_Form::formTextField("CLASS_name", $this->_name, 33, 255);
       $tags["USE_MORTCALC"] = PHPWS_Form::formCheckBox("CLASS_useMortCalc", 1, $this->_useMortCalc,NULL,$_SESSION["translate"]->it("Use Mortgage Calculator"));

        $tags["USE_PRICE"] = PHPWS_Form::formCheckBox("CLASS_usePrice", 1, $this->_usePrice, NULL, $_SESSION["translate"]->it("Use Price"));

        $tags["DEFAULT"] = PHPWS_Form::formCheckBox("CLASS_defaultClass", 1, $this->_defaultClass, NULL, $_SESSION["translate"]->it("Default"));


        $tags["SUBMIT_BUTTON"] = PHPWS_Form::formSubmit($_SESSION["translate"]->it("Save Class"), "LISTINGS_op");

        $elements[0] = PHPWS_Form::formHidden("module", "phpwslistings");
        $elements[0] .= PHPWS_Template::processTemplate($tags, "phpwslistings", "edit_class.tpl");

        if($this->_new) $title = $_SESSION["translate"]->it("New Class");
        else $title = $_SESSION["translate"]->it("Edit Class");

        $content = PHPWS_Form::makeForm("class_edit", "index.php", $elements, "post", FALSE, TRUE);
        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");

    }// END FUNC edit()


    function save() {
        if($_REQUEST["LISTINGS_op"] == $_SESSION["translate"]->it("Save Class") && !$_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_class")) {
            $this->_error("access_denied");
            return;
        }

        if($_POST["CLASS_name"]) {
            $this->_name = PHPWS_Text::parseInput($_POST["CLASS_name"]);
            $data["name"] = $this->_name;
        } else {
            $this->_error("no_name");
            $this->edit();
            return;
        }

        if(isset($_POST["CLASS_useMortCalc"]))
            $this->_useMortCalc = $_POST["CLASS_useMortCalc"];
        else
            $this->_useMortCalc = 0;
        $data["use_mortcalc"] = $this->_useMortCalc;

        if(isset($_POST["CLASS_usePrice"]))
            $this->_usePrice = $_POST["CLASS_usePrice"];
        else
            $this->_usePrice = 0;
        $data["use_price"] = $this->_usePrice;

        if(sizeof($_SESSION["SES_LISTING_MANAGER"]->_classes) == 0)
            $this->_defaultClass = 1;
        else {
            if(isset($_POST["CLASS_defaultClass"]))
                $this->_defaultClass = $_POST["CLASS_defaultClass"];
            else
                $this->_defaultClass = 0;
        }
        $data["default_class"] = $this->_defaultClass;


        if($this->_new) {
            if($this->add($data)) {
                $title = $_SESSION["translate"]->it("Class Saved");
                $content = $_SESSION["translate"]->it("Your class was successfully saved.");
                $content .= "  <a href=\"index.php?module=phpwslistings&LISTINGS_op=menu\">" . $_SESSION["translate"]->it("IMPORTANT: Click here to repopulate the menu class array.") . "</a>";
            } else {
                $this->_error("save_failed");
                $this->edit();
                return;
            }
        } elseif($this->_id) {
            if($this->update($data)) {
                $title = $_SESSION["translate"]->it("Class Updated");
                $content = $_SESSION["translate"]->it("Your class was successfully updated.");
                $content .= "  <a href=\"index.php?module=phpwslistings&LISTINGS_op=menu\">" . $_SESSION["translate"]->it("IMPORTANT: Click here to repopulate the menu class array.") . "</a>";
            } else {
                $this->_error("update_failed");
                $this->edit();
                return;
            }
        }

        // Clear out other default markers and set this as the only one
        if($this->_defaultClass) {
            if(!$GLOBALS["core"]->sqlUpdate(array("default_class"=>0), "mod_listings_classes", "id", $this->_id, "!=")) {
                $this->_error("update_failed");
                $this->edit();
                return;
            }
        }

        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
        
    }// END FUNC save()


    function delete() {
        if(!$_SESSION["OBJ_user"]->allow_access("phpwslistings", "delete_class")) {
            $this->_error("access_denied");
            return;
        }

        if(isset($_POST["yes"])){
            $GLOBALS["core"]->sqlDelete("mod_listings_classes", "id", $this->_id);
            $title = $_SESSION["translate"]->it("Class Deleted!");
            $content = "<p>" . $_SESSION["translate"]->it("The class was successfully <b>deleted</b>.") . "</p>";
            $content .= "  <a href=\"index.php?module=phpwslistings&LISTINGS_op=menu\">" . $_SESSION["translate"]->it("IMPORTANT: Click here to repopulate the menu class array.") . "</a>";

            /*
             * If this delete leaves only one class, mark that class
             * as default.
             */
            if(sizeof($_SESSION["SES_LISTING_MANAGER"]->_classes) == 2) {
                if(!$GLOBALS["core"]->sqlUpdate(array("default_class"=>1), "mod_listings_classes")) {
                    $this->_error("update_failed");
                    $this->edit();
                    return;
                }
            } elseif($this->_defaultClass)
                $content .= "<p>" . $_SESSION["translate"]->it("Default class deleted.  Be sure to select another class as your default class.") . "</p>";

        } elseif (isset($_POST["no"])) {
            $title = $_SESSION["translate"]->it("Class Kept!");
            $content = $_SESSION["translate"]->it("You have chosen <b>not</b> to delete the class.");
        } else {
            $elements[0] = PHPWS_Form::formHidden("module", "phpwslistings");
            $elements[0] .= PHPWS_Form::formHidden("LISTINGS_op", $_SESSION["translate"]->it("Delete Listing Class"));
            $elements[0] .= PHPWS_Form::formHidden("CLASS_id", $this->_id);
            $elements[0] .= PHPWS_Form::formSubmit($_SESSION["translate"]->it("Yes"), "yes");
            $elements[0] .= PHPWS_Form::formSubmit($_SESSION["translate"]->it("No"), "no");

            $title = $_SESSION["translate"]->it("Delete Class?");
            $content = $_SESSION["translate"]->it("Are you sure you wish to delete this class?");
            $content .= PHPWS_Form::makeForm("class_delete", "index.php", $elements);
        }
        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");

    }// END FUNC delete()


    function add($data) {
        if($_REQUEST["LISTINGS_op"] == $_SESSION["translate"]->it("Save Class") && !$_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_class")) {
            $this->_error("access_denied");
            return;
        }

        $this->_id = $GLOBALS["core"]->sqlInsert($data, "mod_listings_classes", FALSE, TRUE);

        if($this->_id)
            return TRUE;
        else
            return FALSE;

    }// END FUNC add()


    function update($data) {
        if(!$_SESSION["OBJ_user"]->allow_access("phpwslistings", "edit_class")) {
            $this->_error("access_denied");
            return;
        }

        if($GLOBALS["core"]->sqlUpdate($data, "mod_listings_classes", "id", $this->_id))
            return TRUE;
        else
            return FALSE;

    }// END FUNC update()


    function activate() {
        if(!$_SESSION["OBJ_user"]->allow_access("phpwslistings", "activate_class")) {
            $this->_error("access_denied");
            return;
        }

        PHPWS_WizardBag::toggle($this->_active);
        $data["active"] = $this->_active;
        $this->update($data);
    }// END FUNC activate()


    function showSelect($field_name = NULL, $class_id = NULL, $showNameIfHidden = FALSE) {
        $classes[] = " ";
        $default_id = NULL;

        $classeslist =& $_SESSION["SES_LISTING_MANAGER"]->getClasses();
        if(count($classeslist) > 1) {
            foreach($classeslist as $class) {
                $classes[$class->_id] = $class->_name;
                if($class->_defaultClass)
                    $default_id = $class->_id;
            }

            if(is_null($class_id))
                $class_id = $default_id;

            return PHPWS_Form::formSelect($field_name, $classes, $class_id, FALSE, TRUE);
        } elseif(count($classeslist) == 1) {
            if($showNameIfHidden)
                return $classeslist[0]->_name . " " . PHPWS_Form::formHidden($field_name, $classeslist[0]->_id);
            else
                return PHPWS_Form::formHidden($field_name, $classeslist[0]->_id);
        }
    }// END FUNC showSelect()


    function _error($type) {
        $title = "<font class=\"errortext\">" . $_SESSION["translate"]->it("ERROR!") . "</font>";

        switch($type) {
            case "no_name":
                $content = $_SESSION["translate"]->it("You did not provide a name for your class.");
                break;

            case "save_failed":
                $content = $_SESSION["translate"]->it("There was a problem saving your class.") . " " . $_SESSION["translate"]->it("If the problem persists contact your system administrator.");
                break;

            case "update_failed":
                $content = $_SESSION["translate"]->it("There was a problem updating your class.") . " " . $_SESSION["translate"]->it("If the problem persists contact your system administrator.");
                break;

            case "access_denied":
            $content = "<b>" . $_SESSION["translate"]->it("ACCESS DENIED!") . "</b>";
            break;
        }

        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
    }// END FUNC _error()

}// END CLASS SEILER_ListingClass

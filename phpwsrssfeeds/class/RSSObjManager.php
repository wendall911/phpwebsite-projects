<?php
    /**
     * RSSObjManager.php
     * Provides generic item object handling.
     *
     * Loosely based on core/Manager
     * by Steven Levin <steven@NOSPAM.tux.appstate.edu> and
     * Adam Morton <adam@NOSPAM.tux.appstate.edu>
     * @author Wendall Cada <wendall911@users.sourceforge.net>
     * $Id: RSSObjManager.php,v 1.5 2004/11/26 17:29:05 wendall911 Exp $
     */
     
    require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/RSS_common.php');
     
    class RSS_ObjManager {
         
        /**
         * The name of the current module.
         *
         * @var    string
         * @access private
         */
        var $_module;
         
        /**
         * The name of the table used by this object
         *
         * @var    string
         * @access private
         */
        var $_table;
         
        /**
         * The name of the request variable to pass the action.
         *
         * @var    string
         * @access private
         */
        var $_request;
         
        /**
         * The class name of the oject to instantiate
         *
         * @var    string
         * @access private
         */
        var $_class;
         
        /**
         * The name of the functions to be passed to the item object.
         *
         * @varstring
         * @access private
         */
        var $_listFunction;
        var $_viewFunction;
        var $_editFunction;
        var $_deleteFunction;
        var $_saveFunction;
        var $_showFunction;
        var $_hideFunction;
        var $_approveFunction;
         
        /**
         * Current class object
         *
         * @var object
         * @access private
         */
        var $_classObj;
         
        /**
         * The name of the item request variable to pass the action.
         *
         * @var    string
         * @access private
         */
        var $_itemRequest;
         
        /**
         * The name of the item request variable to pass the action.
         *
         * @var    array
         * Array can include view, edit, add, delete, menu, link, show_hide, approve
         * @access private
         */
        var $_checkPerms;
         
        /**
         * Titles for each action
         *
         * @var array
         * Array can include list, view, edit, add, delete, menu, link, show_hide, approve
         * @access private
         */
        var $_titles;
         
        /**
         * Constructor for RSS list manager object
         * var array $settings User defined settings for this class.
         * NOTE: Don't pass objects to this class.
         *
         */
        function RSS_ObjManager($settings = NULL) {
            $this->_class = NULL;
            $this->_module = NULL;
            $this->_table = NULL;
            $this->_request = NULL;
            $this->_listFunction = "_list";
            $this->_viewFunction = "_view";
            $this->_editFunction = "_edit";
            $this->_deleteFunction = "_delete";
            $this->_saveFunction = "_save";
            $this->_showFunction = "_show";
            $this->_hideFunction = "_hide";
            $this->_approveFunction = "_approve";
            $this->_classObj = NULL;
            $this->_itemRequest = 'PHPWS_MAN_ITEMS';
            $this->_checkPerms = array();
            $this->_titles = array();
            //Settings constructor
            if (is_array($settings)) {
                foreach ($settings as $setting => $value)
                    $this->$setting = $value;
            }
        } // END function RSS_ListManager()
         
        function getRSS_ObjManager($settings = NULL) {
            $instance = new RSS_ObjManager($settings);
            return $instance;
        }
        //End function getRSSObjManager()
         
        /**
         * This function executes commands on the current object and returns the
         * content
         *
         * Executes various commands for the manager like link, hide, approve
         * and show.
         * Commands like list, add, edit, delete, save and  view must be set by
         * the programmer in the constructor of the child manager class.
         *
         * @var String $action Sets action request for the function call for target
         * Item object
         * @return mixed  Returns any output recieved during execution of an action.
         * @access public
         */
        function managerAction($action) {
            $function = NULL;
            $content = NULL;
            $list = FALSE;
            $doMassUpdate = FALSE;
            $error = FALSE;
            $debug_message = NULL;
            //Mandatory security check on every manager function
            if ($error == FALSE && isset($this->_checkPerms[$action])) {
                if ($this->_checkPerms[$action] == 1)
                    $perms = NULL;
                else
                    $perms = $this->_checkPerms[$action];
                 
                if (!$_SESSION["OBJ_user"]->allow_access($this->_module, $perms))
                    return $this->accessDenied();
            }
            //set action function based on type
            switch($action) {
                case "list":
                    $function = $this->_listFunction;
                break;
                 
                case "edit":
                    $function = $this->_editFunction;
                break;
                 
                case "delete":
                    if (isset($_POST["YES_BUTTON"]) && isset($_POST[$this->_itemRequest])) {
                        if (isset($_POST[$this->_itemRequest][1]))
                            $content = $_SESSION["translate"]->it("Selected items deleted:");
                        else
                            $content = $_SESSION["translate"]->it("Selected item deleted:");
                        $list = TRUE;
                        $function = $this->_deleteFunction;
                    } elseif (isset($_POST["NO_BUTTON"])) {
                        // tell user all is still okay
                        if (isset($_POST[$this->_itemRequest][1]))
                            return RSS_common::_continueContent($_SESSION["translate"]->it("Selected items NOT deleted."), "list", $this->_request);
                        else
                            return RSS_common::_continueContent($_SESSION["translate"]->it("Selected item NOT deleted."), "list", $this->_request);
                    } elseif (isset($_POST[$this->_itemRequest])
                      && sizeof($_POST[$this->_itemRequest]) > 0 && is_array($_POST[$this->_itemRequest])) {
                        return $this->confirmDelete();
                    } else {
                        $error = TRUE;
                        //Check to see if delete was a get request. If so, display error.
                        if (isset($_GET['module']))
                            $debug_message = "Only POST data can be used to delete items";
                        else
                            $debug_message = "No delete parameters set.";
                    }
                break;
                
                case "view":
                    $function = $this->_viewFunction;
                break;
                 
                case "add":
                    $function = $this->_editFunction;
                break;
                 
                case "sView":
                    $function = $this->_viewFunction;
                break;
                 
                case "customSort":
                    // only for multiview
                    $function = "customSort";
                break;
                 
                case "saveCustomSort":
                    $function = "saveCustomSort";
                break;
                 
                case "save":
                    //Check to see if save was a get request. If so, display error.
                    if (isset($_GET['module'])) {
                        $error = TRUE;
                        $debug_message = "Only POST data can be used to save items";
                    }
                    $function = $this->_saveFunction;
                break;
                 
                case "menu_link":
                    return $this->linkItem('list', 'menu_link');
                 
                case "link":
                    return $this->linkItem('view', 'menu_link', $_REQUEST[$this->_itemRequest][0]);
                 
                case "hide":
                    $doMassUpdate = TRUE;
                    $list = TRUE;
                    $function = $this->_hideFunction;
                break;
                 
                case "show":
                    $doMassUpdate = TRUE;
                    $list = TRUE;
                    $function = $this->_showFunction;
                break;
                 
                case "approve":
                    $doMassUpdate = TRUE;
                    $list = TRUE;
                    $function = $this->_approveFunction;
                break;
            }
            if ($error == FALSE && is_null($function)) {
                $debug_message = $this->_class." ".$action." not set!";
                $error = TRUE;
            }
            if ($error == FALSE && !is_callable(array($this->_class, $function))) {
                $debug_message = $this->_class."::".$function."() doesn't exist!";
                $error = TRUE;
            }
            if ($error == FALSE && $action == 'save')
                if (!$_SESSION["OBJ_user"]->allow_access($this->_module))
                    return $this->accessDenied();
             
            if ($error == FALSE && isset($_REQUEST[$this->_itemRequest])
              && sizeof($_REQUEST[$this->_itemRequest]) > 0) {
                if (!is_array($_REQUEST[$this->_itemRequest])) {
                    $this->_classObj = new $this->_class($_REQUEST[$this->_itemRequest]);
                    $return_item = $this->_classObj->$function();
                    if ($return_item == FALSE)
                        $error = TRUE;
                    $content .= $return_item;
                } elseif(is_array($_REQUEST[$this->_itemRequest])) {
                    $this->_classObj = new $this->_class();
                    if (!$doMassUpdate) {
                        $return_items = '';
                        foreach($_REQUEST[$this->_itemRequest] as $item) {
                            $return_item = $this->_classObj->$function($item);
                            if ($return_item == FALSE) {
                                $error = TRUE;
                                break;
                            } else
                                $return_items .= $return_item;
                        }
                        $content .= $return_items;
                    } else {
                        $return = $this->_classObj->$function($_REQUEST[$this->_itemRequest]);
                        if ($return == FALSE)
                            $error = TRUE;
                        else
                            $content .= $return;
                    }
                }
            } elseif ($error == FALSE) {
                $this->_classObj = new $this->_class();
                $return_item = $this->_classObj->$function();
                if ($return_item == FALSE)
                    $error = TRUE;
                $content .= $return_item;
            }
            if ($error == TRUE) {
                if (@$_SESSION['PHPWS_Debug']->isActive() && is_object($this->_classObj->_error)) {
                    $debug_message = $this->_classObj->_error->getMessage();
                    $content .= RSS_common::_errorMessage($debug_message, "Error", 1, $this->_module, "RSS_ObjManager::managerAction");
                } elseif (@$_SESSION['PHPWS_Debug']->isActive() && !is_null($debug_message)) {
                    $content .= RSS_common::_errorMessage($debug_message, "Error", 1, $this->_module, "RSS_ObjManager::managerAction");
                } else
                    $content .= RSS_common::_errorMessage("Sorry, there was a problem with that request.");
            }
            if ($list == TRUE) {
                if (!is_object($this->_classObj))
                    $this->_classObj = new $this->_class();
                $function = $this->_listFunction;
                $content .= $this->_classObj->$function();
            }
             
            return $content;
             
        }// END FUNC managerAction()
         
        /**
         *
         * Linker code for linking module items
         * @param  string $class Class name
         * @param  string $action Operational request action
         * @param  string $perms Permissions required to make request
         * @param  mixed Item ids
         * @access private
         *
         */
        function linkItem($action, $perms, $id = NULL) {
            if ($GLOBALS['core']->moduleExists("menuman")) {
                if ($_SESSION["OBJ_user"]->allow_access($this->_module, "menu_link")
                  && $_SESSION["OBJ_user"]->allow_access("menuman", "add_item")) {
                    if (isset($id) && sizeof($id) > 0) {
                        $_SESSION['OBJ_menuman']->add_module_item($this->_module,
                          "&amp;".$this->_request."=".$action."&amp;".$this->_itemRequest."=" .$id, "./index.php?module=".$this->_module."&amp;" .$this->_request."=".$action."&amp;".$this->_itemRequest."=".$id, 1);
                    } else {
                        $_SESSION['OBJ_menuman']->add_module_item($this->_module,
                          "&amp;".$this->_request."=".$action,
                          "./index.php?module=".$this->_module."&amp;" .$this->_request."=".$action, 1);
                    }
                } else {
                    return $this->accessDenied();
                }
            } else {
                return RSS_common::_errorMessage("You must install Menu Manager first!");
            }
        }//End function linkItem()
         
        /**
         * Function called to build the yes/no form to confirm deletion of items
         * @access private
         */
        function confirmDelete() {
            //Generate confirm form
            require_once(PHPWS_SOURCE_DIR.'core/Form.php');
            // ask if the user is sure
            $form = new EZform($this->_class."_DELETE");
            $form->add("YES_BUTTON", "submit", $_SESSION["translate"]->it("Yes"));
            $form->add("NO_BUTTON", "submit", $_SESSION["translate"]->it("No"));
            $form->add("module", "hidden", $this->_module);
            $form->add($this->_request, "hidden", "delete");
            // note must indicate with [] that the id variable is in an array
            // I'll also build a sql query for the title list
            $num = 0;
            foreach($_POST[$this->_itemRequest] as $id) {
                $man_item = "$this->_itemRequest[$num]";
                $form->add($man_item, "hidden", $id);
                $num++;
            }
            $item_list = implode(" OR id = ", $_POST[$this->_itemRequest]);
            $sql = "SELECT label FROM ".$this->_table." WHERE id = ".$item_list;
            $items = $GLOBALS["core"]->getAll($sql, TRUE);
             
            $tags = array();
            $tags = $form->getTemplate();
            $tags["MESSAGE"] = $_SESSION["translate"]->it("Delete ");
            foreach($items as $item) {
                $tags["MESSAGE"] .= $item['label'].", ";
            }
            $tags["MESSAGE"] = rtrim($tags["MESSAGE"], ", ");
            $tags["MESSAGE"] .= "?";
             
            return PHPWS_Template::processTemplate($tags, $this->_module, "confirm.tpl");
        }//End function confirmDelete()
         
        /**
         * Function called when user needs slapped down
         * @access private
         */
        function accessDenied() {
            $tags["MESSAGE"] = $_SESSION['translate']->it("Access Denied!");
            return PHPWS_Template::processTemplate($tags, $this->_module, "error.tpl");
        }//end function accessDenied
         
    } //END class RSS_ObjManager
?>
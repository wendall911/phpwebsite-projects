<?php
/*
* *********************************
* * ListManager Config File           *
* *********************************
* File Name: listmanager.php
* This file must be located in the conf/ directory for the module passed to
* RSSList
* For an example look in the docs/developer/ directory of your phpwebsite base
*
* @version $Id: RSSList.php,v 1.2 2004/11/23 21:55:12 wendall911 Exp $
* @author  Steven Levin <steven@NOSPAM.tux.appstate.edu>
* @author  Adam Morton <adam@NOSPAM.tux.appstate.edu>
* @modified Wendall Cada <wendall911@users.sourceforge.net>
*/
 
require_once(PHPWS_SOURCE_DIR . "core/Pager.php");
 
class RSSList {
     
    /**
     * The name of the current module extending manager.
     *
     * @var    string
     * @access private
     */
    var $_module;
     
    /**
     * The name of the table to pull items from.
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
     * The name of the request variable that contains the item id/ids
     *
     * @var    string
     * @access private
     */
    var $_itemRequest;
     
    /**
     * The lists defined in the current modules manager config file.
     *
     * @var    array
     * @access private
     * @see    init(), getList()
     */
    var $_lists;
     
    /**
     * The name of the tables to use for each list defined in the list array.
     *
     * @var    array
     * @access private
     * @see    init(), getList()
     */
    var $_tables;
     
    /**
     * The templates defined in the current modules manager config file.
     *
     * @var    array
     * @access private
     * @see    init(), getList()
     */
    var $_templates;
     
    /**
     * The columns defined in the current modules manager config file
     *
     * @var    array
     * @access private
     * @see    init(), getList(), getItems()
     */
    var $_listColumns;
     
    /**
     * The actions for a defined list.
     *
     * @var    array
     * @access private
     * @see    init(), getList()
     */
    var $_listActions;
     
    /**
     * The permissions for the actions of a defined list.
     *
     * @var    array
     * @access private
     * @see    init(), getList()
     */
    var $_listPermissions;
     
    /**
     * The pager settings for each of the lists defined.
     *
     * @var    array
     * @access private
     * @see    init(), getList()
     */
    var $_listPaging;
     
    /**
     * The values to use for the PHPWS_Items hidden and approved
     *
     * @var    array
     * @access private
     * @see    init(), getList()
     */
    var $_listValues;
     
    /**
     * An array of groups to limit item lists by.
     *
     * If this variable is set, PHPWS_Manager will limit it's item lists
     * by only showing the items that belong to a group in this array.
     *
     * @var    array
     * @access private
     */
    var $_groups;
     
    /**
     * An sql where clause used to sort items in lists
     *
     * @var    string
     * @access private
     * @see    getSort()
     */
    var $_sort;
     
    /**
     * Sql ORDER BY passed in by the developer
     *
     * @var    array
     * @access private
     * @see    getOrder()
     */
    var $_order;
     
    /**
     * Properties for the sql ORDER BY to order items in lists
     *
     * @var    array
     * @access private
     * @see    catchOrder(), getOrder
     */
    var $_overrideOrder;
     
    /**
     * Stores the pager objects
     *
     * @var    array
     * @access pivate
     * @see    getList()
     */
    var $_pagers;
     
    /**
     * Flag whether or not to add anchors to the list for linking
     *
     * @var    boolean
     * @access private
     */
    var $_anchor;
     
    /**
     * The name of the group function implemented in PHPWS_Manager's child class.
     *
     * This is used to call the appropriate group function to edit which groups have
     * access to the item.
     *
     * @var    string
     * @access private
     */
    var $_groupFunction;
     
    /**
     * The name of the current list being generated for the child
     *
     * Passed to getList() by programmer to reference list defined in manager config file
     *
     * @var    string
     * @access public
     * @see    getList(), getItems()
     */
     
    /**
     * Initializes this manager.
     *
     * @access public
     */
    function RSSList($settings = NULL) {
        $this->_module = NULL;
        $this->_table = NULL;
        $this->_request = NULL;
        $this->_itemRequest = 'PHPWS_MAN_ITEMS';
        $this->_lists = array();
        $this->_tables = array();
        $this->_groupFunction = "_group";
        $this->_templates = array();
        $this->_listColumns = array();
        $this->_listActions = array();
        $this->_listPermissions = array();
        $this->_listPaging = array();
        $this->_listValues = array();
        $this->_groups = array();
        $this->_sort = NULL;
        $this->_order = NULL;
        $this->_overrideOrder = NULL;
        $this->_pagers = array();
        $this->_anchor = FALSE;
         
        //Settings constructor
        if (is_array($settings)) {
            foreach ($settings as $setting => $value)
                $this->$setting = $value;
        }
         
    }//End function RSSList()
     
    function getRSSList($settings = NULL) {
        $instance = new RSSList($settings);
        return $instance;
    }
    //End function getRSSList()
     
    function init() {
        $config = PHPWS_SOURCE_DIR . "mod/" . $this->_module . "/conf/manager.php";
         
        if (!file_exists($config)) {
            $message = $_SESSION['translate']->it("Manager configuration file not found for module: [var1]", $this->_module);
            $error = new PHPWS_Error($this->_module, "RSSList::init()", $message, "exit", 1);
            $error->message(NULL);
        }
         
        include($config);
         
        if (!is_array($lists) || !is_array($templates)) {
            $message = $_SESSION['translate']->it("Manager configuration file is an improper format.");
            $error = new PHPWS_Error($this->_module, "RSSList::init()", $message, "exit", 1);
            $error->message(NULL);
        }
         
        if (isset($tables) && is_array($tables)) {
            $this->_tables = $tables;
        }
         
        $this->_lists = $lists;
        $this->_templates = $templates;
         
        if (isset($hiddenValues) && is_array($hiddenValues)) {
            $this->_listValues['hidden'] = $hiddenValues;
        }
        if (isset($approvedValues) && is_array($approvedValues)) {
            $this->_listValues['approved'] = $approvedValues;
        }
         
        foreach($this->_lists as $listName => $listClause) {
            $columns = $listName . "Columns";
            $actions = $listName . "Actions";
            $permissions = $listName . "Permissions";
            $paging = $listName . "Paging";
             
            if (!is_array($$columns)
              || (isset($$actions) && !is_array($$actions))
              || (isset($$permissions) && !is_array($$permissions))
              || (isset($$paging) && !is_array($$paging))) {
                $message = $_SESSION['translate']->it("Manager configuration file is an improper format.");
                $error = new PHPWS_Error($this->_module, "RSSList::init()", $message, "exit", 1);
                $error->message(NULL);
            }
             
            $this->_listColumns[$listName] = $$columns;
             
            if (isset($$actions)) {
                $this->_listActions[$listName] = $$actions;
            }
            if (isset($$permissions)) {
                $this->_listPermissions[$listName] = $$permissions;
            }
            if (isset($$paging)) {
                $this->_listPaging[$listName] = $$paging;
            }
        }
    }//End function init()

    /**
     * Returns a list of items based on the table currently set in this manager
     *
     * @param  string  $listName The name of the list wanting to be returned
     * @param  string  $title    The title of the list
     * @param  boolean $makeForm Flag whether or not to make a form out of the list
     * @access public
     */
    function getList($listName, $title = NULL, $makeForm = TRUE, $overRideOp = NULL) {
        $this->listName = $listName;
         
        if (!isset($this->_table) && !isset($this->_request)) {
            $message = $_SESSION['translate']->it("Manager was not fully initialized to get a list.");
            $error = new PHPWS_Error($this->_module, "RSSList::getList()", $message, "exit", 1);
            $error->message(NULL);
        }
         
        $theme = $_SESSION['OBJ_layout']->current_theme;
         
        $themeModuleRowTpl = PHPWS_HOME_DIR . "themes/$theme/templates/" . $this->_module . "/" . $this->_templates[$this->listName] . "/row.tpl";
        $moduleRowTpl = PHPWS_SOURCE_DIR . "mod/" . $this->_module . "/templates/" . $this->_templates[$this->listName] . "/row.tpl";
        $themeCoreRowTpl = PHPWS_HOME_DIR . "themes/$theme/templates/core/defaultRow.tpl";
        $coreRowTpl = PHPWS_SOURCE_DIR . "templates/defaultRow.tpl";
         
        $themeModuleListTpl = PHPWS_HOME_DIR . "themes/$theme/templates/" . $this->_module . "/" . $this->_templates[$this->listName] . "/list.tpl";
        $moduleListTpl = PHPWS_SOURCE_DIR . "mod/" . $this->_module . "/templates/" . $this->_templates[$this->listName] . "/list.tpl";
        $themeCoreListTpl = PHPWS_HOME_DIR . "themes/$theme/templates/core/defaultList.tpl";
        $coreListTpl = PHPWS_SOURCE_DIR . "templates/defaultList.tpl";
         
        if (file_exists($themeModuleRowTpl)) {
            $rowTpl = $themeModuleRowTpl;
        }
        else if(file_exists($moduleRowTpl)) {
            $rowTpl = $moduleRowTpl;
        }
        else if(file_exists($themeCoreRowTpl)) {
            $rowTpl = $themeCoreRowTpl;
        } else {
            $rowTpl = $coreRowTpl;
        }
         
        if (file_exists($themeModuleListTpl)) {
            $listTpl = $themeModuleListTpl;
        }
        else if(file_exists($moduleListTpl)) {
            $listTpl = $moduleListTpl;
        }
        else if(file_exists($themeCoreListTpl)) {
            $listTpl = $themeCoreListTpl;
        } else {
            $listTpl = $coreListTpl;
        }
         
        if (isset($_REQUEST['PHPWS_MAN_LIST']) && ($this->listName == $_REQUEST['PHPWS_MAN_LIST'])) {
            $this->catchOrder();
        }
         
        if (isset($overRideOp)) {
            $op = $overRideOp;
        } else {
            if (isset($this->_listPaging[$this->listName]["op"])) {
                $op = $this->_listPaging[$this->listName]["op"];
            }
        }
         
         
        if (isset($this->_listPaging[$this->listName]) && is_array($this->_listPaging[$this->listName])) {
            if (!isset($this->_pagers[$this->listName])) {
                $this->_pagers[$this->listName] = new PHPWS_Pager;
                $this->_pagers[$this->listName]->setLinkBack("./index.php?module=" . $this->_module . "&amp;" . $op . "&amp;PHPWS_MAN_PAGE=" . $this->listName);
                $this->_pagers[$this->listName]->setLimits($this->_listPaging[$this->listName]["limits"]);
                $this->_pagers[$this->listName]->makeArray(TRUE);
                 
                if ($this->_anchor) {
                    $this->_pagers[$this->listName]->setAnchor("#" . $this->listName);
                }
                 
                $this->_pagers[$this->listName]->limit = $this->_listPaging[$this->listName]["limit"];
            }
             
            $this->_pagers[$this->listName]->setData($this->getIds());
             
            if (isset($_REQUEST['PHPWS_MAN_PAGE']) && ($this->listName == $_REQUEST['PHPWS_MAN_PAGE'])) {
                $this->_pagers[$this->listName]->pageData();
            } else {
                $this->_pagers[$this->listName]->pageData(FALSE);
            }
             
            if (isset($this->_class)) {
                $items = $this->getItems($this->_pagers[$this->listName]->getData(), FALSE, TRUE);
            } else {
                $items = $this->getItems($this->_pagers[$this->listName]->getData());
            }
            $totalItems = $this->_pagers[$this->listName]->getNumRows();
        } else {
            if (isset($this->_class)) {
                $items = $this->getItems(NULL, FALSE, TRUE);
            } else {
                $items = $this->getItems();
            }
            $totalItems = sizeof($items);
        }
         
        /* Begin building main list tags array for processTemplate() */
        $listTags = array();
        $listTags["TITLE"] = $title;
        $listTags["ANCHOR"] = "<a name=\"" . $this->listName . "\" />";
         
        if ($makeForm) {
            $listTags["SELECT_LABEL"] = "&#160;";
        }
         
        $columns = 0;
        foreach($this->_listColumns[$this->listName] as $listColumn => $listLabel) {
            $column = strtoupper($listColumn);
            $key0 = $column . "_LABEL";
            $key1 = $column . "_ORDER_LINK";
             
            $listTags[$key0] = NULL;
            $listTags[$key1] = NULL;
             
            $listTags[$key0] = $listLabel;
             
            if (isset($overRideOp)) {
                $request = $overRideOp;
            }
            else if(isset($this->_listPaging[$this->listName]["op"])) {
                $request = $this->_listPaging[$this->listName]["op"];
            } else {
                $request = $this->_request . "=list";
            }
             
            if ($totalItems > 0) {
                $anchor = "";
                if ($this->_anchor) {
                    $anchor = "#" . $this->listName;
                }
                 
                if (isset($this->_overrideOrder[$this->listName][$listColumn][0]))
                    $overRide = $this->_overrideOrder[$this->listName][$listColumn][0];
                else
                    $overRide = "default";
                 
                if (isset($this->_listPaging[$this->listName]))
                switch($overRide) {
                    case 0:
                        $listTags[$key1] .= "<a href=\"./index.php?module=" . $this->_module . "&amp;" . $request . "&amp;PHPWS_MAN_LIST=" . $this->listName . "&amp;PHPWS_MAN_COLUMN=" . $listColumn . "&amp;PHPWS_MAN_ORDER=1&amp;" . "PHPWS_MAN_PAGE=". $this->listName . "&amp;" . "PAGER_limit=" . $this->_pagers[$this->listName]->limit . "&amp;" . "PAGER_start=" . $this->_pagers[$this->listName]->start . "&amp;" . "PAGER_section=" . $this->_pagers[$this->listName]->section . $anchor . "\">";
                        $listTags[$key1] .= "<img src=\"./images/core/list/sort_none.png\" border=\"0\" /></a>";
                    break;
                     
                    case 1:
                        $listTags[$key1] .= "<a href=\"./index.php?module=" . $this->_module . "&amp;" . $request . "&amp;PHPWS_MAN_LIST=" . $this->listName . "&amp;PHPWS_MAN_COLUMN=" . $listColumn . "&amp;PHPWS_MAN_ORDER=2&amp;" . "PHPWS_MAN_PAGE=". $this->listName . "&amp;" . "PAGER_limit=" . $this->_pagers[$this->listName]->limit . "&amp;" . "PAGER_start=" . $this->_pagers[$this->listName]->start . "&amp;" . "PAGER_section=" . $this->_pagers[$this->listName]->section . $anchor . "\">";
                        $listTags[$key1] .= "<img src=\"./images/core/list/up_pointer.png\" border=\"0\" /></a>";
                    break;
                     
                    case 2:
                        $listTags[$key1] .= "<a href=\"./index.php?module=" . $this->_module . "&amp;" . $request . "&amp;PHPWS_MAN_LIST=" . $this->listName . "&amp;PHPWS_MAN_COLUMN=" . $listColumn . "&amp;PHPWS_MAN_ORDER=0&amp;" . "PHPWS_MAN_PAGE=" . $this->listName . "&amp;" . "PAGER_limit=" . $this->_pagers[$this->listName]->limit . "&amp;" . "PAGER_start=" . $this->_pagers[$this->listName]->start . "&amp;" . "PAGER_section=" . $this->_pagers[$this->listName]->section . $anchor . "\">";
                        $listTags[$key1] .= "<img src=\"./images/core/list/down_pointer.png\" border=\"0\" /></a>";
                    break;
                     
                    default:
                        $listTags[$key1] .= "<a href=\"./index.php?module=" . $this->_module . "&amp;" . $request . "&amp;PHPWS_MAN_LIST=" . $this->listName . "&amp;PHPWS_MAN_COLUMN=" . $listColumn . "&amp;PHPWS_MAN_ORDER=1&amp;" . "PHPWS_MAN_PAGE=" . $this->listName . "&amp;" . "PAGER_limit=" . $this->_pagers[$this->listName]->limit . "&amp;" . "PAGER_start=" . $this->_pagers[$this->listName]->start . "&amp;" . "PAGER_section=" . $this->_pagers[$this->listName]->section . $anchor . "\">";
                        $listTags[$key1] .= "<img src=\"./images/core/list/sort_none.png\" border=\"0\" /></a>";
                }
            }
             
            $columns++;
        }
         
        /* Build each item's row */
        $listTags['LIST_ITEMS'] = NULL;
        if ($totalItems > 0) {
            foreach($items as $item) {
                $object = NULL;
                if (isset($this->_class)) {
                    $object = new $this->_class($item);
                    $className = get_class($object);
                    $classMethods = get_class_methods($className);
                    $objectVars = get_object_vars($object);
                     
                    if (is_array($objectVars)) {
                        $item = $objectVars;
                        foreach($item as $key => $value) {
                            if ($key[0] == "_") {
                                $key = substr($key, 1, strlen($key));
                                $item[$key] = $value;
                            }
                        }
                    }
                }
                 
                PHPWS_WizardBag::toggle($row_class, " class=\"bg_light\"");
                /* Build row tags array for processTemplate() */
                $rowTags = array();
                $rowTags["ROW_CLASS"] = $row_class;
                if ($makeForm) {
                    $rowTags["SELECT"] = PHPWS_Form::formCheckBox("PHPWS_MAN_ITEMS[]", $item["id"]);
                }
                 
                foreach($this->_listColumns[$this->listName] as $listColumn => $listLabel) {
                    $column = strtoupper($listColumn);
                    if ($listColumn == "created") {
                        /* Set created date using phpwebsite's default date and time formats */
                        $rowTags["CREATED"] = date(PHPWS_DATE_FORMAT . " " . PHPWS_TIME_FORMAT, $item["created"]);
                    }
                    else if($listColumn == "updated") {
                        /* Set updated date using phpwebsite's default date and time formats */
                        $rowTags["UPDATED"] = date(PHPWS_DATE_FORMAT . " " . PHPWS_TIME_FORMAT, $item["updated"]);
                    }
                    else if($listColumn == "hidden") {
                        /* Setting message depending if this item is hidden or not */
                        if (isset($this->_listValues['hidden'])) {
                            $rowTags["HIDDEN"] = $this->_listValues['hidden'][$item['hidden']];
                        } else {
                            if ($item["hidden"] == 1)
                                $rowTags["HIDDEN"] = $_SESSION['translate']->it("Hidden");
                            else
                                $rowTags["HIDDEN"] = $_SESSION['translate']->it("Visible");
                        }
                    }
                    else if($listColumn == "approved") {
                        /* Setting message depending if this item is approved or not */
                        if (isset($this->_listValues['hidden'])) {
                            $rowTags["APPROVED"] = $this->_listValues['approved'][$item['approved']];
                        } else {
                            if ($item["approved"] == 1)
                                $rowTags["APPROVED"] = $_SESSION['translate']->it("Approved");
                            else
                                $rowTags["APPROVED"] = $_SESSION['translate']->it("Unapproved");
                        }
                    }
                    else if($listColumn == "groups") {
                        $groups = unserialize($item["groups"]);
                        if (is_array($groups) && sizeof($groups) > 0) {
                            /* Set flag to check whether to add a comma or not */
                            $flag = FALSE;
                            /* Create a string of group names the current item belongs to */
                            foreach($groups as $group) {
                                if ($flag)
                                    $rowTags["GROUPS"] .= ", ";
                                 
                                $rowTags["GROUPS"] .= $group;
                                $flag = TRUE;
                            }
                        } else {
                            $rowTags["GROUPS"] = $_SESSION["translate"]->it("All");
                        }
                    } else {
                        $method = "get" . $listColumn;
                        if (is_object($object) && in_array($method, $classMethods)) {
                            $rowTags[$column] = PHPWS_Text::parseOutput($object->$method());
                        } else {
                            $rowTags[$column] = PHPWS_Text::parseOutput($item[$listColumn]);
                        }
                    }
                }
                 
                /* Process this item and concatenate onto the current list of items */
                $listTags["LIST_ITEMS"] .= PHPWS_Template::processTemplate($rowTags, "core", $rowTpl, FALSE);
            }
             
            if (isset($this->_listPaging[$this->listName]) && is_array($this->_listPaging[$this->listName]) && (sizeof($this->_listPaging[$this->listName]) > 0)) {
                $listTags['NAV_BACKWARD'] = $this->_pagers[$this->listName]->getBackLink($this->_listPaging[$this->listName]["back"]);
                $listTags['NAV_FORWARD'] = $this->_pagers[$this->listName]->getForwardLink($this->_listPaging[$this->listName]["forward"]);
                if (isset($this->_listPaging[$this->listName]["section"])) {
                    $listTags['NAV_SECTIONS'] = $this->_pagers[$this->listName]->getSectionLinks();
                }
                $listTags['NAV_LIMITS'] = $this->_pagers[$this->listName]->getLimitLinks();
                $listTags['NAV_INFO'] = $this->_pagers[$this->listName]->getSectionInfo();
            }
             
            $actions = array();
            if (isset($this->_listActions[$this->listName]) && is_array($this->_listActions[$this->listName])) {
                foreach($this->_listActions[$this->listName] as $actionString => $actionLabel) {
                    if (isset($this->_listPermissions[$this->listName][$actionString]))
                        $permission = $this->_listPermissions[$this->listName][$actionString];
                     
                    if (isset($permission)) {
                        if ($_SESSION['OBJ_user']->allow_access($this->_module, $permission)) {
                            $actions[$actionString] = $actionLabel;
                        }
                    } else {
                        $actions[$actionString] = $actionLabel;
                    }
                }
            }
             
            if ($makeForm) {
                /* Create action select and Go button */
                $listTags["ACTION_SELECT"] = PHPWS_Form::formSelect($this->_request, $actions);
                $listTags["ACTION_BUTTON"] = PHPWS_Form::formSubmit($_SESSION['translate']->it("Go"));
                $listTags["TOGGLE_ALL"] = PHPWS_WizardBag::js_insert("check_all", "PHPWS_MAN_LIST_" . $this->listName);
                 
                /* Add hidden variable to designate the current module */
                $elements[0] = PHPWS_Form::formHidden("module", $this->_module);
                $elements[0] .= PHPWS_Template::processTemplate($listTags, "core", $listTpl, FALSE);
                 
                /* Create final form and dump it into a content variable to be returned */
                $content = PHPWS_Form::makeForm("PHPWS_MAN_LIST_" . $this->listName, "index.php", $elements);
            } else {
                $content = PHPWS_Template::processTemplate($listTags, "core", $listTpl, FALSE);
            }
             
        } else {
            $listTags["LIST_ITEMS"] = "<tr><td colspan=\"" . $columns . "\">" . $_SESSION['translate']->it("No items for the current list.") . "</td></tr>";
            $content = PHPWS_Template::processTemplate($listTags, "core", $listTpl, FALSE);
        }
         
        /* reinitialize sort and order before next list */
        $this->_sort = NULL;
        $this->_order = NULL;
        $this->_class = NULL;
         
        return $content;
    }
    // END FUNC getList()
     
    /**
    * Creates a 2 dimensional array of items from the current table.
    *
    * This function creates an sql statement based on variables currently set in
    * this object.  The statement is then executed on the current table and it's
    * result is returned as the list of current items.
    *
    * @param  boolean $filterGroups Flag whether or not to filter items that are not
    *                 associated with a users group
    * @return mixed   A 2-dimentional array of items or FALSE on failure.
    * @access public
    * @see    getList()
    */
    function getItems($ids = NULL, $filterGroups = FALSE, $everything = FALSE) {
        if (isset($this->_table)) {
            $table = $this->_table;
        } else {
            $table = $this->_tables[$this->listName];
        }
         
        /* Make sure the table name is set before continuing */
        if (isset($table)) {
            if (is_array($this->_listColumns[$this->listName])) {
                if ($everything) {
                    $sql = "SELECT *";
                } else {
                    $sql = "SELECT id, ";
                    foreach($this->_listColumns[$this->listName] as $listColumn => $listLabel) {
                        if ($listColumn != "id" ) {
                            $sql .= $listColumn . ", ";
                        }
                    }
                     
                    $sql = substr($sql, 0, strlen($sql) - 2);
                }
                 
                $sql .= " FROM " . PHPWS_TBL_PREFIX . $table;
            } else {
                $error = new PHPWS_Error($this->_module, "RSSList:getItems()", "Format error in config file.", "exit", 1);
                $error->message(NULL);
            }
        } else {
            $error = new PHPWS_Error($this->_module, "RSSList:getItems()", "Table not set!", "exit", 1);
            $error->message(NULL);
        }
         
        $whereFlag = FALSE;
        $sort = $this->getSort();
        if (isset($sort)) {
            $sql .= $sort;
            $whereFlag = TRUE;
        }
         
        if (is_array($ids) && (sizeof($ids) > 0)) {
            if ($whereFlag) {
                $sql .= " AND (";
            } else {
                $sql .= " WHERE (";
            }
             
            foreach($ids as $id) {
                $sql .= " id='$id' OR ";
            }
            $sql = substr($sql, 0, strlen($sql)-4) . ")";
        }
         
        $order = $this->getOrder();
        if (isset($order)) {
            $sql .= $order;
        }
         
        /* Set associative mode for db and execute query */
        $GLOBALS["core"]->setFetchMode("assoc");
        $result = $GLOBALS["core"]->getAll($sql);
         
        if ($filterGroups) {
            $size = sizeof($result);
            for($i = 0; $i < $size; $i++) {
                $groups = unserialize($result[$i]['groups']);
                if (is_array($groups)) {
                    foreach($groups as $value) {
                        if (!$_SESSION['OBJ_user']->userInGroup($value)) {
                            unset($result[$i]);
                        }
                    }
                }
            }
             
            $result = PHPWS_Array::reIndex($result);
        }
         
        /* Return result of query */
        return $result;
    }
    // END FUNC getItems()
     
    /**
    * Catches an order changed passed by a list, this order will override
    * any order set by the pregrammer
    *
    * @access public
    * @see    getList()
    */
     
    function catchOrder() {
        unset($this->_overrideOrder[$_REQUEST['PHPWS_MAN_LIST']]);
        $this->_overrideOrder[$_REQUEST['PHPWS_MAN_LIST']][$_REQUEST['PHPWS_MAN_COLUMN']][0] = $_REQUEST['PHPWS_MAN_ORDER'];
        switch($this->_overrideOrder[$_REQUEST['PHPWS_MAN_LIST']][$_REQUEST['PHPWS_MAN_COLUMN']][0]) {
            case 0:
                $this->_overrideOrder[$_REQUEST['PHPWS_MAN_LIST']][$_REQUEST['PHPWS_MAN_COLUMN']][1] = NULL;
            break;
             
            case 1:
                $this->_overrideOrder[$_REQUEST['PHPWS_MAN_LIST']][$_REQUEST['PHPWS_MAN_COLUMN']][1] = $_REQUEST['PHPWS_MAN_COLUMN'] . " DESC";
            break;
             
            case 2:
                $this->_overrideOrder[$_REQUEST['PHPWS_MAN_LIST']][$_REQUEST['PHPWS_MAN_COLUMN']][1] = $_REQUEST['PHPWS_MAN_COLUMN'] . " ASC";
            break;
        }
        return TRUE;
    }
     
    function getSort() {
        if (isset($this->_lists[$this->listName])) {
            $sql = " WHERE (" . $this->_lists[$this->listName] . ")";
             
            if (isset($this->_sort)) {
                $sql .= " AND (" . $this->_sort . ")";
            }
             
            return $sql;
        }
        else if(isset($this->_sort)) {
            return " WHERE (" . $this->_sort . ")";
        } else {
            return NULL;
        }
    }
     
    function getOrder() {
        foreach($this->_listColumns[$this->listName] as $listColumn => $listLabel) {
            if (isset($this->_overrideOrder[$this->listName][$listColumn][1])) {
                $order = $this->_overrideOrder[$this->listName][$listColumn][1];
                break;
            }
        }
         
        if (isset($order)) {
            return " ORDER BY " . $order;
        }
        else if(isset($this->_order)) {
            return " ORDER BY " . $this->_order;
        } else {
            return NULL;
        }
    }
     
    function anchorOn() {
        $this->_anchor = TRUE;
    }
     
    function anchorOff() {
        $this->_anchor = FALSE;
    }
     
    function getTable() {
        return $this->_table;
    }
     
    function getIds() {
        if (isset($this->_table)) {
            $table = $this->_table;
        } else {
            $table = $this->_tables[$this->listName];
        }
         
        $sql = "SELECT id FROM " . PHPWS_TBL_PREFIX . $table;
         
        $sort = $this->getSort();
        if (isset($sort)) {
            $sql .= $sort;
        }
         
        $order = $this->getOrder();
        if (isset($order)) {
            $sql .= $order;
        }
         
        return $GLOBALS['core']->getCol($sql);
    }
     
    /**
    * Updates simple attributes for multiple items at once.
    *
    * This function is called when multiple items are requested to be hidden, approved, or
    * visable.  It simply creates an sql statement based on the type of request on the item
    * ids contained in the $_REQUEST[$this->_itemRequest] array and executes it on the database.
    *
    * @param  string  $column The name of the column to update.
    * @param  mixed   $value  The value to set the column to.
    * @return boolean TRUE on success and FALSE on failure.
    * @access privateinit();
    */
    function _doMassUpdate($column, $value) {
        if (isset($_REQUEST[$this->_itemRequest])
          && is_array($_REQUEST[$this->_itemRequest])
          && sizeof($_REQUEST[$this->_itemRequest]) > 0) {
            if (!isset($this->_table))
                return FALSE;
             
            /* Begin sql update statement */
            $sql = "UPDATE " . PHPWS_TBL_PREFIX . $this->_table . " SET $column='$value' WHERE id='";
             
            /* Set flag to know when to add sql for checking against extra ids */
            $flag = FALSE;
            foreach($_REQUEST[$this->_itemRequest] as $itemId) {
                if ($flag)
                    $sql .= " OR id='";
                 
                $sql .= $itemId . "'";
                $flag = TRUE;
            }
             
            /* Execute query and test for failure */
            $result = $GLOBALS["core"]->query($sql);
            if ($result)
                return TRUE;
            else
                return FALSE;
        }
    }// END FUNC _doMassUpdate()
     
}//End class RSSList
?>
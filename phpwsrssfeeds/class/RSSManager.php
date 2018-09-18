<?php
/**
 * class/RSSManager.php
 * includes class/RSSObjManager.php
 * @version $Id: RSSManager.php,v 1.24 2005/03/17 18:30:49 wendall911 Exp $
 * @author Wendall Cada <wendall911@users.sourceforge.net>
 *
 */
 
require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/RSSObjManager.php');
 
class RSS_Manager {
     
    /**
     * ObjManager Object
     *
     * @var object
     */
    var $_manObj;
     
    /**
     * Menu toggle array. Gives ability to turn menu on/off for each action request.
     *
     * @var    array
     * Array can include view, edit, add, delete, menu, link, show_hide, approve
     * @access private
     */
    var $_showMenu;
     
    /**
     * Array of menu items to be included in menu template and template name
     * see _menu(), setMenu
     *
     * @var Array
     */
    var $_menu;
     
    /**
     * Class name of current class
     *
     * @var String
     */
    var $_class;
     
    /**
     * Request var sent to action() from index.php
     *
     * @var string
     */
    var $_request;
     
    /**
     * Current module name
     *
     * @var string
     */
    var $_module;
     
    /**
     * Permissions array for the current object
     *
     * @var array
     */
    var $_checkPerms;
     
    function RSS_Manager($item_class = NULL) {
        $this->_class = $item_class;
        $this->_showMenu = array("edit" => 1, "add" => 1,
          "delete" => 1, "link" => 1, "menu_link" => 1,
          "show" => 1, "hide" => 1, "list" => 1, "save" => 1,
          "customSort" => 1, "saveCustomSort" => 1);
        $this->_menu = array();
        $this->_manObj = NULL;
        $this->_request = NULL;
        $this->_module = "phpwsrssfeeds";
        $this->_checkPerms = array();
    } // END function RSS_ListManager()
     
    /**
     * Initializes the ObjManager object.
     *
     * @access public
     */
    function init() {
        $defaultSettings = array("_module" => $this->_module,
          "_class" => $this->_class);
        $this->_checkPerms["RSS_BACKEND_MAN_op"] = array(
          "edit" => "editbackend", "add" => "addbackend",
          "delete" => "deletebackend", "list" => 1);
        $this->_checkPerms['RSS_MAN_op'] = array(
          "edit" => "edit", "add" => "add",
          "delete" => "delete", "link" => "link",
          "menu_link" => "menu_link",
          "show" => "show_hide", "hide" => "show_hide");
        $this->_checkPerms['RSS_MULTI_MAN_op'] = array(
          "edit" => "multi_edit", "add" => "multi_add",
          "delete" => "multi_delete", "link" => "multi_link",
          "menu_link" => "multi_menu_link",
          "show" => "multi_show_hide", "hide" => "multi_show_hide",
          "customSort" => 1, "saveCustomSort" => 1);
        $this->_checkPerms['RSS_AGGR_MAN_op'] = array(
          "edit" => "aggr_edit", "add" => "aggr_add",
          "delete" => "aggr_delete", "link" => "aggr_link",
          "menu_link" => "aggr_menu_link",
          "show" => "aggr_show_hide", "hide" => "aggr_show_hide");
        if ($this->_class == 'RSS') {
            require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/RSS.php');
            $objSettings = array("_request" => "RSS_MAN_op",
              "_table" => "mod_phpwsrssfeeds_feeds",
              "_checkPerms" => $this->_checkPerms['RSS_MAN_op'],
              "_titles" => array(
              "edit" => $_SESSION['translate']->it('Edit RSS Feed'),
              "add" => $_SESSION['translate']->it('Add RSS Feed'),
              "delete" => $_SESSION['translate']->it('Delete RSS Feed'),
              "link" => $_SESSION['translate']->it('Link RSS Feed'),
              "menu_link" => $_SESSION['translate']->it('Link RSS Feed List'),
              "save" => $_SESSION['translate']->it('RSS Feed Saved'),
              "show" => $_SESSION['translate']->it('RSS News Feeds List'),
              "hide" => $_SESSION['translate']->it('RSS News Feeds List'),
              "list" => $_SESSION['translate']->it('RSS News Feeds List')));
        } elseif($this->_class == 'Multi_RSS') {
            require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/Multi_RSS.php');
            $objSettings = array(
              "_request" => "RSS_MULTI_MAN_op",
              "_table" => "mod_phpwsrssfeeds_multi",
              "_checkPerms" => $this->_checkPerms['RSS_MULTI_MAN_op'],
              "_titles" => array(
              "edit" => $_SESSION['translate']->it('Edit Multi-View RSS Feed'),
              "add" => $_SESSION['translate']->it('Add Multi-View RSS Feed'),
              "delete" => $_SESSION['translate']->it('Delete Multi-View RSS Feed'),
              "link" => $_SESSION['translate']->it('Link Multi-View RSS Feed'),
              "save" => $_SESSION['translate']->it('Multi-View RSS Feed Saved'),
              "menu_link" => $_SESSION['translate']->it('Link Multi-View RSS Feed List'),
              "show" => $_SESSION['translate']->it('Multi-View News Feeds List'),
              "hide" => $_SESSION['translate']->it('Multi-View News Feeds List'),
              "list" => $_SESSION['translate']->it('Multi-View News Feeds List')));
        } elseif($this->_class == 'RSSFeeds_backend') {
            require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/backend.php');
            $objSettings = array("_request" => "RSS_BACKEND_MAN_op",
              "_table" => "mod_phpwsrssfeeds_backend",
              "_checkPerms" => $this->_checkPerms["RSS_BACKEND_MAN_op"],
              "_titles" => array(
              "edit" => $_SESSION['translate']->it('Edit Local RSS Feed'),
              "add" => $_SESSION['translate']->it('Add Local RSS Feed'),
              "delete" => $_SESSION['translate']->it('Delete Local RSS Feed'),
              "save" => $_SESSION['translate']->it('Local RSS Feed Saved'),
              "list" => $_SESSION['translate']->it('Manage Local Feeds')),
              "_viewFunction" => "CreateRDF");
        } elseif ($this->_class == 'RSS_Aggregate') {
            require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/RSSAggregate.php');
            $objSettings = array("_request" => "RSS_AGGR_MAN_op",
              "_table" => "mod_phpwsrssfeeds_aggregate",
              "_checkPerms" => $this->_checkPerms['RSS_AGGR_MAN_op'],
              "_titles" => array(
              "edit" => $_SESSION['translate']->it('Edit Aggregated Feed'),
              "add" => $_SESSION['translate']->it('Add Aggregated Feed'),
              "delete" => $_SESSION['translate']->it('Delete Aggregated Feed'),
              "link" => $_SESSION['translate']->it('Link Aggregated Feed'),
              "menu_link" => $_SESSION['translate']->it('Link Aggregated Feed List'),
              "save" => $_SESSION['translate']->it('Aggregated Feed Saved'),
              "show" => $_SESSION['translate']->it('Aggregated News Feeds List'),
              "hide" => $_SESSION['translate']->it('Aggregated News Feeds List'),
              "list" => $_SESSION['translate']->it('Aggregated News Feeds List')));
        }
        $settings = array_merge($defaultSettings, $objSettings);
        $this->_manObj = RSS_ObjManager::getRSS_ObjManager($settings);
    }//End function init()
     
    function initMenu() {
        $i = 0;
        //Set list items to appear in all menus
        $menu['ITEM'][$i++] = array('LIST_FEEDS', 'RSS_MAN_op', 'list', 'RSS Feeds');
        $menu['ITEM'][$i++] = array('MANAGE_BACKEND', 'RSS_BACKEND_MAN_op', 'list',
            'Local Feeds', 'MANAGE_BACKEND_HELP', 'manageBackend');
        $menu['ITEM'][$i++] = array('LIST_MULTI_VIEW', 'RSS_MULTI_MAN_op', 'list',
            'Multi-View');
        $menu['ITEM'][$i++] = array('LIST_AGGR_VIEW', 'RSS_AGGR_MAN_op', 'list',
            'Aggregation');
        if ($this->_class == 'RSS') {
            $menu['TEMPLATE'] = 'menu.tpl';
            $menu['ITEM'][$i++] = array('CREATE_MENU_LINK', 'RSS_MAN_op', 'menu_link',
                'Menu Link', 'CREATE_MENU_LINK_HELP', 'rssAddMenuLink');
            $menu['ITEM'][$i++] = array('ADD_RSS_FEED', 'RSS_MAN_op', 'add',
                'Add RSS Feed', 'ADD_RSS_HELP', 'rssAddFeed');
            $menu['ACT'][$i++] = array('EDIT_RSS_FEED', 'RSS_MAN_op', 'edit',
                'Edit RSS Feed');
        } elseif($this->_class == 'Multi_RSS') {
            $menu['TEMPLATE'] = 'multi_menu.tpl';
            $menu['ITEM'][$i++] = array('CREATE_MENU_LINK', 'RSS_MULTI_MAN_op',
                'menu_link', 'Menu Link', 'CREATE_MENU_LINK_HELP', 'rssAddMenuLink');
            $menu['ITEM'][$i++] = array('ADD_MULTI_FEED', 'RSS_MULTI_MAN_op', 'add',
                'Add Multi-View');
            $menu['ACT'][$i++] = array('EDIT_RSS_FEED', 'RSS_MULTI_MAN_op', 'edit',
                'Edit Multi-View');
        } elseif($this->_class == 'RSSFeeds_backend') {
            $menu['TEMPLATE'] = 'backend_menu.tpl';
            $menu['ITEM'][$i++] = array('ADD_BACKEND_FEED', 'RSS_BACKEND_MAN_op', 'add',
                'Add Local Feed');
            $menu['ACT'][$i++] = array('EDIT_RSS_FEED', 'RSS_BACKEND_MAN_op', 'edit',
                'Edit Local Feed');
        } elseif ($this->_class == 'RSS_Aggregate') {
            $menu['TEMPLATE'] = 'aggregate_menu.tpl';
            $menu['ITEM'][$i++] = array('CREATE_MENU_LINK', 'RSS_AGGR_MAN_op', 'menu_link',
                'Menu Link', 'CREATE_MENU_LINK_HELP', 'rssAddMenuLink');
            $menu['ITEM'][$i++] = array('ADD_AGGR_FEED', 'RSS_AGGR_MAN_op', 'add',
                'Add Aggregated Feed', 'ADD_RSS_HELP', 'rssAddFeed');
            $menu['ACT'][$i++] = array('EDIT_AGGR_FEED', 'RSS_AGGR_MAN_op', 'edit',
                'Edit Aggregated Feed');
        }
        $this->setMenu($menu);
    }//End function initMenu()
     
    function getRSS_Manager($settings = NULL) {
        $instance = new RSS_Manager($settings);
        return $instance;
    }//End function getRSSManager()
     
    /**
     * Set information for for menu configuration
     * We'll hold the menu template name in ['TEMPLATE'] and items in ['ITEM']
     * Array configuration is $array['TEMPLATE'] = 'menutemplate.tpl'
     * $array['ITEM'][$i] = array(tagname,request,requestaction,label,helptag,
     * helpitem);
     *
     * Add items to the array that you only want to appear as active labels when
     * actions are requested that have no associated menu item, but menu is set to
     * appear.
     * $array['ACT'][$i] = array(tagname,request,requestaction,label);
     * @return boolean TRUE on success and FALSE on failure
     * @access public
     */
    function setMenu($menu) {
        /* make sure that the parameter is an array*/
        if (is_array($menu)) {
            $this->_menu = $menu;
            return TRUE;
        } else {
            return FALSE;
        }
    }//End function setMenu()
     
    /**
     * Provides standard tableless menu
     * var string $class Allows other Classes to use menu function from
     * this class as a static class
     *
     * @author Wendall Cada <wendall911@users.sourceforge.net>
     */
    function _menu($menuAction = NULL) {
        $tags = array();
        $template = $this->_menu['TEMPLATE'];
         
        //Process Items now that we know what the template tags will be
        foreach ($this->_menu['ITEM'] as $item) {
            $request = $item[1];
            $action = $item[2];
            //Mandatory security check on every menu item
            if (isset($this->_checkPerms[$request][$action])) {
                $perms = FALSE;
                 
                if ($this->_checkPerms[$request][$action] == 1)
                    $perms = NULL;
                else
                    $perms = $this->_checkPerms[$request][$action];
                 
                if ($perms !== FALSE && !$_SESSION["OBJ_user"]->allow_access($this->_module, $perms))
                    continue;
            }
            $tag = $item[0];
            $label = $item[3];
            //Check to see if item needs toggled
            if (!is_null($menuAction) && isset($_REQUEST[$request])
              && $action == $menuAction) {
                $tags[$tag] = $label;
                $toggle = $tag."_ACT";
                $tags[$toggle] = " smalltext bg_light";
            } elseif(is_null($menuAction) && isset($_REQUEST[$request])
              && $action == $_REQUEST[$request]) {
                $tags[$tag] = $label;
                $toggle = $tag."_ACT";
                $tags[$toggle] = " smalltext bg_light";
            }
            else
            $tags[$tag] = PHPWS_Text::moduleLink($_SESSION["translate"]->it($label),
                $this->_module, array($request => $action));
            if (isset($item[4])) {
                $tags[$item[4]] = RSS_common::show_link($this->_module, $item[5]);
            }
        }
         
        //Process Active label vars
        foreach ($this->_menu['ACT'] as $item) {
            //Check to see if item is active
            if (isset($_REQUEST[$item[1]]) && $item[2] == $_REQUEST[$item[1]]) {
                $tags[$item[0]] = $item[3];
            }
        }

        return PHPWS_Template::processTemplate($tags, $this->_module, $template);
    }//End function menu()
     
    function updateReqFeeds($ids) {
        require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/RSS.php');
        $rssObj = new RSS();
        $rssObj->update($ids);
    }
     
    /**
     * Perform non-standard list manager actions
     *
     * var String $request Request var for manager action
     * See index.php
     * @author Wendall Cada <wendall911@users.sourceforge.net>
     */
    function action($request = NULL) {
        $menuAction = NULL;
        $this->_request = $request;
        /*check to see if a request has been made if not set to default class
         * and show list
         */
        if (is_null($this->_request)) {
            $action = 'list';
            $this->_class = 'RSS';
        } else
            $action = $_REQUEST[$this->_request];
            $content = NULL;
         
        //Check for deprecated actions
        if (isset($action) && $action == 'Backend' && isset($_REQUEST["RSS_id"])) {
            //Do what it used to do
            require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/backend.php');
            $rssBackendObj = new RSSFeeds_backend($_REQUEST["RSS_id"]);
            $rssBackendObj->CreateRDF();
        } elseif(isset($action) && $action == 'update' && isset($_POST["UPDATE_ID"])) {
            require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/RSS.php');
            $rssObj = new RSS();
            $rssObj->update($_POST["UPDATE_ID"]);
        } else {
            $this->init();
            if (isset($action)) {
                if ($action == 'delete' || $action == 'show' || $action == 'hide')
                $menuAction = 'list';
                 
                if (isset($this->_showMenu[$action]) && $this->_showMenu[$action] == TRUE) {
                    $this->initMenu();
                    $content = $this->_menu($menuAction);
                }
                 
                $content .= $this->_manObj->managerAction($action);
            }
            /* set display variables */
            $GLOBALS["CNT_phpwsrssfeeds"]["content"] = $content;

            if (isset($this->_manObj->_titles[$action]))
                $GLOBALS["CNT_phpwsrssfeeds"]["title"] = $_SESSION['translate']->it($this->_manObj->_titles[$action]);
            else if($this->_manObj->_class == 'Multi_RSS') {
                $GLOBALS["CNT_phpwsrssfeeds"]["title"] = $this->_manObj->_classObj->_label;
            } else if($this->_manObj->_class == 'RSS_Aggregate') {
                $GLOBALS["CNT_phpwsrssfeeds"]["title"] = $this->_manObj->_classObj->_label;
            }
        }
    } //END function action
     
    function search($where) {
        $sql = 'SELECT id, label, content FROM ' . $GLOBALS['core']->tbl_prefix . 'mod_phpwsrssfeeds_feeds ' . $where;
        $result = $GLOBALS["core"]->query($sql);
         
        $array = array();
        if ($result) {
            while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
                $array[$row["id"]] = $row["label"];
            }
        }
         
        return $array;
    }
     
} //END class RSS_Manager
?>
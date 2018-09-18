<?php
//@$Id: runtime.php,v 1.20 2005/03/31 20:50:04 wendall911 Exp $

require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/RSS_common.php');
require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/RSSAggregate.php');
require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/Multi_RSS.php');
/**
 *Runtime class for inc/runtime.php
 *
 * @author Wendall Cada <wendall911@users.sourceforge.net>
 */
class RSS_runtime {
    
    /**
     * Current class object
     *
     * @var object
     * @access private
     */
    var $_classObj;
    
    /**
     * The class name of the oject to instantiate
     *
     * @var    string
     * @access private
     */
    var $_class;

    /**
     * The name of the table to pull items from.
     *
     * @var    string
     * @access private
     */

    var $_table;

    function RSS_runtime($class,$table){
        $this->_classObj = NULL;
        $this->_class = $class;
        $this->_table = $table;
    }//End function RSS_runtime()

    /**
     * Display Feeds in block on main page
     *
     * @author Wendall Cada <wendall911@users.sourceforge.net>
     */
    function showUserBox() {
        $result = $this->allowView('block');
        if($result){
            $user_box = NULL;
            foreach($result as $block) {
                if($block['hidden'] == 0){
                    $this->_classObj = new $this->_class($block['id']);
                    $user_box .= $this->_classObj->showUserBox();
                }
            }
            return $user_box;
        } 
    }//END showUserBox

    function display() {
        $show = 0;
        $show = $this->showUserBox();
            if($GLOBALS['module'] == 'home') {
                $show .= $this->showHomeItems();
            }
        return $show;
    }//END function display()


    /**
     * Display Feeds on the home page
     *
     * @author Wendall Cada <wendall911@users.sourceforge.net>
     */
    function showHomeItems() {
        $result = $this->allowView('home');
        if($result){
            $home_items = NULL;
            foreach($result as $home) {
                if($home['hidden'] == 0){
                    $this->_classObj = new $this->_class($home['id']);
                    $home_items .= $this->_classObj->showHomeItems();
                }
            }
            return $home_items;
        }
    }//END showHomeItems

    function allowView($loc){
        if(!$GLOBALS["core"]->sqlTableExists($this->_table, TRUE))
            return;
        if(!isset($_SESSION["PHPWSRSSFEEDS"]["allow_view"][$this->_class][$loc])) {
            $sql_statement = "SELECT id,hidden,approved FROM "
              .$this->_table." WHERE " . $loc . " = '1';";
            $result = $GLOBALS["core"]->query($sql_statement, TRUE);
            $i = 0;
            if($result) {
                while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)){
                    $_SESSION["PHPWSRSSFEEDS"]["allow_view"][$this->_class][$loc][$i] = $row;
                    $i++;
                }
            }
        }
        if(!isset($_SESSION["PHPWSRSSFEEDS"]["allow_view"][$this->_class][$loc]))
        return;
      
        return $_SESSION["PHPWSRSSFEEDS"]["allow_view"][$this->_class][$loc];
    }// function allowView()

}//End class RSS_runtime
?>
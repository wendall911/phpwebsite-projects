<?php
/**
 * RSS_Fatcat.php
 * Alters fatcat functionality to allow for EZForm input from rssfeeds
 * Fatcat currently only allows for registering items with module and id as
 * parameters. I just added (int)5000 to each item in my second class to 
 * separate the items since fatcat doesn't actually use this param for anything
 * but identifying individual feeds
 *
 * @version $Id: RSS_Fatcat.php,v 1.2 2004/06/17 19:38:20 wendall911 Exp $
 * @author Wendall Cada <wendall911@users.sourceforge.net>
 *
 */

class RSS_Fatcat {
    
    /**
     * Categorizes an element from a module
     *
     * After using the showSelect function, you would call this function
     * to catch the data. If you are inserting data make sure you send the id
     * of the new element.
     * @author Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
     * @modified Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
     * @param string  title        Title of the element from your module
     * @param string  link         Link that will send a user to your module's element
     * @param int     module_id    ID number of your module's element
     * @param array   groups       Groups that an element may be viewed by
     * @param string  module_title Name of the module sending the information
     * @param string  href         If set to 'away', FatCat will assume the link leads offsite
     * @param int     rating       Number 1 - 100 that indicates the importance of an element
     * @param boolean active       If TRUE, enable the category element
     */
    function saveSelect($title, $link, $module_id, $groups=NULL, $module_title=NULL, $href=NULL, $rating=NULL, $active=TRUE){
        if (!is_numeric($module_id) || $module_id < 1)
            exit("saveSelect error: module id is not a number or is zero");

        if (!$GLOBALS["core"]->moduleExists($module_title) && !($module_title = $GLOBALS["core"]->current_mod))
            exit("saveSelect error: Unable to pull module information for <b>".$GLOBALS["core"]->current_mod."</b>");


        if (!isset($_POST["CATEGORY"]) || !($categories = $_POST["CATEGORY"])){
            if (isset($_POST["fatcatProcess"])){
                $_SESSION['OBJ_fatcat']->deleteModuleElements($module_title,$module_id);
                $_SESSION['OBJ_fatcat']->saveElement(0, strip_tags($title), strip_tags($link), $module_id, $module_title, $groups, $href, $rating, $active); 
            }
            return;
        } 

        $created = $_SESSION['OBJ_fatcat']->getCreationDate($module_title, $module_id);
        $_SESSION['OBJ_fatcat']->deleteModuleElements($module_title, $module_id);

        if (isset($_POST["STICKY"]))
            $rating = 999;
        else
            $rating = NULL;

        if (is_array($categories)){
            foreach ($categories as $cat_id)
                $_SESSION['OBJ_fatcat']->saveElement($cat_id, strip_tags($title), strip_tags($link), $module_id, $module_title, $groups, $href, $rating, $active, $created); 
        } elseif (is_numeric($categories))
            $_SESSION['OBJ_fatcat']->saveElement($categories, strip_tags($title), strip_tags($link), $module_id, $module_title, $groups, $href, $rating, $active, $created); 
  }//End function saveSelect()
  
}//End class RSS_Fatcat
?>
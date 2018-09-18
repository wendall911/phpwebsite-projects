<?php

/**
 * Default index.php file for rssfeeds
 * Provides the entry point for all accesses to the RSS Feeds module.
 * Execution normally arrives here with either a RSS_MAN_op parameter specifying
 * a list action, or a RSS_op parameter specifying an item action.
 *
 * @version $Id: index.php,v 1.16 2004/12/18 23:26:41 wendall911 Exp $
 * @author Wendall Cada <wendall911@users.sourceforge.net>
 *
 */

if (!isset($GLOBALS['core'])){
  header("location:../../");
  exit();
}

if (version_compare($GLOBALS['core']->version, '0.9.3-2') < 0 ){
    require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/inc/runtime.php');
}
require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/RSSManager.php');
require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/conf/rssfeeds.php');

if(isset($_REQUEST["module"]) && $_REQUEST["module"] == "phpwsrssfeeds") {
    /*	If there is an operational request for the RSSFeeds manager object, pass it on */
    if(isset($_REQUEST["RSS_MAN_op"])) {
        $manObj = RSS_Manager::getRSS_Manager('RSS');
        $manObj->action("RSS_MAN_op");
    } elseif(isset($_REQUEST["RSS_MULTI_MAN_op"])) {
        $manObj = RSS_Manager::getRSS_Manager('Multi_RSS');
        $manObj->action("RSS_MULTI_MAN_op");
    } elseif(isset($_REQUEST["RSS_BACKEND_MAN_op"])) {
        $manObj = RSS_Manager::getRSS_Manager('RSSFeeds_backend');
        $manObj->action("RSS_BACKEND_MAN_op");
    } elseif(isset($_REQUEST["RSS_AGGR_MAN_op"])) {
        $manObj = RSS_Manager::getRSS_Manager('RSS_Aggregate');
        $manObj->action("RSS_AGGR_MAN_op");
    } else {
        $manObj = RSS_Manager::getRSS_Manager();
        $manObj->action();
    }
}
?>
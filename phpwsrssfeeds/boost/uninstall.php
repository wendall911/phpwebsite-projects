<?php

/**
 * Uninstall file for RSSFeeds
 *
 * @version $Id: uninstall.php,v 1.8 2004/11/23 22:13:59 wendall911 Exp $
 * @author Wendall Cada <wendall911@users.sourceforge.net>
 */

/* return to home page if the user is not the Deity */
if (!$_SESSION["OBJ_user"]->isDeity()) {
	header("location:index.php");
	exit();
}

require_once(PHPWS_SOURCE_DIR.'mod/help/class/CLS_help.php');
require_once(PHPWS_SOURCE_DIR.'mod/language/class/Language.php');
require_once(PHPWS_SOURCE_DIR."core/File.php");

/* read sql install script, execute it, suppress errors */
if ($GLOBALS['core']->sqlImport(PHPWS_SOURCE_DIR."mod/phpwsrssfeeds/boost/uninstall.sql", TRUE, TRUE)) {

	/* report success */
	$content = $_SESSION['translate']->it("All RSS Feeds tables successfully removed.") . "<br />";
	$status = 1;
	
    /* uninstall Language */
    if(PHPWS_Language::uninstallLanguages('phpwsrssfeeds'))
        $content .= "RSS Feeds successfully updated the Language system.<br />";
	/* uninstall help system */
	if(CLS_help::uninstall_help("phpwsrssfeeds"))
		$content .= "RSS Feeds successfully updated the Help system.<br />";
    /*
    *  uninstall search 
    *  Done automagically with 0.9.3-3
    */
    if(version_compare($GLOBALS['core']->version, "0.9.3-3") < 0) {
        $GLOBALS['core']->sqlDelete("mod_search_register", "module", "phpwsrssfeeds");
    }
    /*Remove layout entries*/ 
    if($GLOBALS['core']->sqlDelete("mod_layout_box", "mod_title", "phpwsrssfeeds"))
        $content .= $_SESSION['translate']->it("RSS Feeds removed from Layout system.") . "<br />";
    /*remove rssfeeds image and file directories*/
    if(PHPWS_File::rmdir($GLOBALS['core']->home_dir . "images/phpwsrssfeeds/")) {
        $content .= "The phpwsRSSFeeds images directory was fully removed.<br />";
    } else {
        $content .= "The phpwsRSSFeeds images directory could not be removed.<br />";
    }
    if(PHPWS_File::rmdir($GLOBALS['core']->home_dir . "files/phpwsrssfeeds/")) {
        $content .= "The phpwsRSSFeeds files directory was fully removed.<br />";
    } else {
        $content .= "The phpwsRSSFeeds files directory could not be removed.<br />";
    }
} else
	$content .= $_SESSION['translate']->it("Unable to access the database.") . "<br />";

?>

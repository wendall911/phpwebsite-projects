<?php
/**
 * @version $Id: uninstall.php,v 1.8 2003/11/21 20:22:36 wendall911 Exp $
 */
if (!$_SESSION["OBJ_user"]->isDeity()){
    header("location:index.php");
    exit();
}

if($GLOBALS["core"]->sqlImport(PHPWS_SOURCE_DIR . "mod/phpwslistings/boost/uninstall.sql", 1, 1)) {
    $content .= "All phpwsListings tables successfully removed.<br />";
    $content .= "Removing images directory " . PHPWS_SOURCE_DIR . "images/phpwslistings<br />";

    PHPWS_File::rmdir(PHPWS_HOME_DIR . "images/phpwslistings/");

    $status = 1;

    $_SESSION["OBJ_help"]->uninstall_help("phpwslistings");
    $_SESSION["OBJ_user"]->dropUserModule("phpwslistings");

    $content .= "Setting manager to NULL ... ";
    $_SESSION["SES_LISTING_MANAGER"] = NULL;
    if(is_null($_SESSION["SES_LISTING_MANAGER"]))
        $content .= "Done<br />";
    else
        $content .= "NO<br />";

    $content .= "Unsetting manager ... ";
    unset($_SESSION["SES_LISTING_MANAGER"]);
    if(!isset($_SESSION["SES_LISTING_MANAGER"]))
        $content .= "Done<br />";
    else
        $content .= "NO<br />";

} else {
  $content .= "There was a problem accessing the database.<br />";
  $status = 0;
}

?>

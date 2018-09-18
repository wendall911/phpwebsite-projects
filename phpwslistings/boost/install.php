<?php
/**
 * @version $Id: install.php,v 1.9 2004/12/02 19:29:50 wendall911 Exp $
 */
if (!$_SESSION["OBJ_user"]->isDeity()){
    header("location:index.php");
    exit();
}

// Need to do core version check
if(version_compare($GLOBALS['core']->version , "0.9.2") < 0) {
    $content .= "This module requires a phpWebSite core version of 0.9.2 or greater to install.<br />";
    $content .= "<br />You are currently using phpWebSite core version " . $GLOBALS["core"]->version . ".<br />";
    return;
}


if ($status = $GLOBALS["core"]->sqlImport(PHPWS_SOURCE_DIR . "mod/phpwslistings/boost/install.sql", TRUE)){
    CLS_help::setup_help("phpwslistings");
    $content .= "All phpwsListings tables successfully written.<br />";
  
    /* Create image directories */
    mkdir(PHPWS_HOME_DIR . "images/phpwslistings");

    if(is_dir(PHPWS_HOME_DIR . "images/phpwslistings"))
        $content .= "phpwsListings image directory " . PHPWS_HOME_DIR . "images/phpwslistings successfully created!<br />";
    else
        $content .= "phpwsListings could not create the image directory: " . PHPWS_HOME_DIR . "images/phpwslistings<br />You will have to do this manually!<br />";

    mkdir(PHPWS_HOME_DIR . "images/phpwslistings/agents");
    if(is_dir(PHPWS_HOME_DIR . "images/phpwslistings/agents"))
        $content .= "phpwsListings image directory " . PHPWS_HOME_DIR . "images/phpwslistings/agents successfully created!<br />";
    else
        $content .= "phpwsListings could not create the image directory: " . PHPWS_HOME_DIR . "images/phpwslistings/agents<br />You will have to do this manually!<br />";

    mkdir(PHPWS_HOME_DIR . "images/phpwslistings/agencies");
    if(is_dir(PHPWS_HOME_DIR . "images/phpwslistings/agencies"))
        $content .= "phpwsListings image directory " . PHPWS_HOME_DIR . "images/phpwslistings/agencies successfully created!<br />";
    else
        $content .= "phpwsListings could not create the image directory: " . PHPWS_HOME_DIR . "images/phpwslistings/agencies<br />You will have to do this manually!<br />";

} else
    $content .= "There was a problem writing to the database.<br />";

?>

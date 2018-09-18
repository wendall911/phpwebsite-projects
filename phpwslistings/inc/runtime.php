<?php
/**
 * @version $Id: runtime.php,v 1.2 2003/12/10 06:28:47 wendall911 Exp $
 */
require_once(PHPWS_SOURCE_DIR.'mod/phpwslistings/class/runtime.php');

//Check to see if user has been deleted
Listing_runtime::AgentCheck();
Listing_runtime::showMenuBlock();

if($GLOBALS["module"] == "home") {
    Listing_runtime::showListings();
    Listing_runtime::showBlockListings();
}

?>
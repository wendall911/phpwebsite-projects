<?php
/**
 * @version $Id: runtime.php,v 1.2 2003/12/10 06:28:47 wendall911 Exp $
 */
require_once(PHPWS_SOURCE_DIR.'mod/phpwslistings/class/ListingManager.php');
require_once(PHPWS_SOURCE_DIR.'mod/phpwslistings/class/ListingClass.php');
require_once(PHPWS_SOURCE_DIR.'mod/phpwslistings/class/ListingElement.php');
require_once(PHPWS_SOURCE_DIR.'mod/phpwslistings/class/ListingAgent.php');
require_once(PHPWS_SOURCE_DIR.'mod/phpwslistings/class/ListingBaseImage.php');
require_once(PHPWS_SOURCE_DIR.'mod/phpwslistings/class/ListingAgency.php');
require_once(PHPWS_SOURCE_DIR.'mod/phpwslistings/class/Listing.php');
require_once(PHPWS_SOURCE_DIR.'mod/phpwslistings/class/ListingImage.php');

    $_SESSION["SES_LISTING_MANAGER"] = new SEILER_ListingManager;

/**
 *Runtime class for inc/runtime.php
 *
 * @author Wendall Cada <wendall911@users.sourceforge.net>
 */

class Listing_runtime{
    
    function showMenuBlock(){
        $_SESSION["SES_LISTING_MANAGER"]->showMenuBlock();
    }
    
    function showListings(){
        $_SESSION["SES_LISTING_MANAGER"]->showListings(TRUE);
    }
    
    function showBlockListings(){
        $_SESSION["SES_LISTING_MANAGER"]->showBlockListings();
    }
    
    function AgentCheck(){
        $this->Listing_block = new SEILER_ListingManager();
        $this->Listing_block->AgentCheck();
    }
}
?>
<?php
/**
 * @version $Id: boost.php,v 1.12 2004/12/02 20:51:14 wendall911 Exp $
 */
$mod_title = "phpwslistings";
$mod_pname = "phpwsListings";
$mod_directory = "phpwslistings";
$mod_filename = "index.php";
$admin_op = "&LISTINGS_op=menu";
$user_op = "&LISTINGS_op=menu";
$allow_view = "all";
$mod_icon = "phpwslistings.png";
$user_icon = "phpwslistings.png";
$priority = 50;
$admin_mod = 1;
$user_mod = 1;
$active = "on";
$version = "0.3.2";

$mod_class_files = array("ListingManager.php",
                        "ListingClass.php",
                        "Listing.php",
                        "ListingAgent.php",
                        "ListingAgency.php",
                        "ListingElement.php",
                        "ListingImage.php",
                        "ListingBaseImage.php");
$mod_sessions = array("SES_LISTING_MANAGER",
                        "SES_LISTING_CLASS",
                        "SES_LISTING",
                        "SES_LISTING_AGENT",
                        "SES_LISTING_AGENCY",
                        "SES_LISTING_ELEMENT",
                        "SES_LISTING_IMAGE");
?>

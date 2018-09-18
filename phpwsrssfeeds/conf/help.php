<?php

/**
 * RSSFeeds help file
 * This file contains the help content for the RSSFeeds module.  See the 
 * mod/help/docs/README for details.
 *
 * @version $Id: help.php,v 1.14 2005/03/14 19:34:10 wendall911 Exp $
 * @author Wendall Cada <wendall911@users.sourceforge.net>
 *
 */

/* help for Add RSS Feed button */
$rssSticky = $_SESSION['translate']->it("Sticky");
$rssSticky_content = $_SESSION['translate']->it("Sticky items will always appear in \"What's Related\".");
$rssCategory = $_SESSION['translate']->it("Category");
$rssCategory_content = $_SESSION['translate']->it("Choose one or more categories that best fit this channel.");
$rssTitle = $_SESSION['translate']->it("Channel Title");
$rssTitle_content = $_SESSION['translate']->it("Choose a title that best fits this RSS Feed.");
$rssChannel = $_SESSION['translate']->it("Channel URL");
$rssChannel_content = $_SESSION['translate']->it("Enter the URL for this RSS Feed.");
$rssFooter = $_SESSION['translate']->it("Footer");
$rssFooter_content = $_SESSION['translate']->it("Select a footer to appear at the bottom of this feed. You may need a disclaimer for some content, this is a good place for it.");
$showTitle = $_SESSION['translate']->it("Show Channel Title");
$showTitle_content = $_SESSION['translate']->it("Selecting Yes with this option shows the channel title for this RSS feed.");
$showImage = $_SESSION['translate']->it("Show Channel Image");
$showImage_content = $_SESSION['translate']->it("Selecting Yes with this option shows the channel image for this RSS feed.");
$showURL = $_SESSION['translate']->it("Show Channel Title as Link");
$showURL_content = $_SESSION['translate']->it("Selecting Yes with this option shows the channel title as a link.");
$showDesc = $_SESSION['translate']->it("Show Channel Description");
$showDesc_content = $_SESSION['translate']->it("Selecting Yes with this option shows the channel description.");
$showDetails = $_SESSION['translate']->it("Show Channel Details Link");
$showDetails_content = $_SESSION['translate']->it("Selecting Yes with this option shows the option to 'View Details' when viewing feeds in block.");
$showMultiView = $_SESSION['translate']->it("Show Multi-View RSS");
$showMultiView_content = $_SESSION['translate']->it("Select all feeds that you want to appear in this Multi-View item.");
$showHome = $_SESSION['translate']->it("Show on Home Page");
$showHome_content = $_SESSION['translate']->it("Checking this option will make this feed appear on the home page in the main area.");
$showBlock = $_SESSION['translate']->it("Allow Feed Placement");
$showBlock_content = $_SESSION['translate']->it("Checking this option allows item to be viewed with the selected Modules or Web Pages.");
$rssAddFeed = $_SESSION['translate']->it("Add RSS Feed");
$rssAddFeed_content = $_SESSION['translate']->it("Click this button to add a RSS Feed by filling in a form.  You can choose to hide the Feed for later display.");
$rssAddMenuLink = $_SESSION['translate']->it("Create Menu Link");
$rssAddMenuLink_content = "Click this button to create a menu item that links to (displays) this listing of available feeds.";
$rssExpire = $_SESSION['translate']->it("Expiration");
$rssExpire_content = $_SESSION['translate']->it("Select the number of minutes for the RSS Feed to expire from the cache.");
$rssMaxItems = $_SESSION['translate']->it("Max Number Items");
$rssMaxItems_content = $_SESSION['translate']->it("Select the maximum number of items to display for full and block views.");
$rssMaxMultiItems = $_SESSION['translate']->it("Max Number Items (Multi-View)");
$rssMaxMultiItems_content = $_SESSION['translate']->it("Select the maximum number of items to display for Multi-View items.");
$rssTarget = $_SESSION['translate']->it("Target Window");
$rssTarget_content = $_SESSION['translate']->it("Choose the target window for links to open in when an article or title is clicked.");
$allowView = $_SESSION['translate']->it("View with Modules");
$allowView_content = $_SESSION['translate']->it("The allow view option allows you to choose when the feed will show up on the page. Choose which modules need to be active in order for the feed to appear.");
$pmView = $_SESSION['translate']->it("View with Web Pages");
$pmView_content = $_SESSION['translate']->it("The view  with Web Pages option allows you to choose which pages the feed will show up on. Choose which pages need to be active in order for the feed to appear.");
$manageBackend = $_SESSION['translate']->it("Manage Local Feeds");
$manageBackend_content = $_SESSION['translate']->it("Click this link to create backed RSS feed files to share your site information with other websites and news aggregators.");
$backendNumItems = $_SESSION['translate']->it("Total Items in Feed");
$backendNumItems_content = $_SESSION['translate']->it("Here you can select the number of items you would like the feed to display. This will create a feed based on the most recent x number of items.");
$backendType = $_SESSION['translate']->it("News Feed Source");
$backendType_content = $_SESSION['translate']->it("Here you can select the data source of the feed you would like to create.");
$backendImage = $_SESSION['translate']->it("News Feed Image");
$backendImage_content = $_SESSION['translate']->it("Select the image you would like to use to create an icon that identifies your RSS feed.");
$emptyFeed = $_SESSION['translate']->it("Empty Feed");
$emptyFeed_content = $_SESSION['translate']->it("Select this option to hide any feeds in the Mulit-View that do not contain any content.");
$anchorLinks = $_SESSION['translate']->it("Bookmarked Links");
$anchorLinks_content = $_SESSION['translate']->it("Select this option to show bookmarked links at the top of the page that link to the individual feeds for the Multi-View.");
$catLink = $_SESSION["translate"]->it("Category Link");
$catLink_content = $_SESSION["translate"]->it("Select this option to enable a category link to see a listing of other feeds in the same category.");
$orgnBy = $_SESSION["translate"]->it("Organize Feed Items");
$orgnBy_content = $_SESSION["translate"]->it("Organize feed items by date, alphabetical or leave in imported order (default).");
$daysAhead = $_SESSION["translate"]->it("Number of Days Ahead");
$daysAhead_content = $_SESSION["translate"]->it("Select this option if the feed items pertain to specific days. This option is ideal for an phpWS calendar module feeds.  The 'Date' option must be selected under 'Organize Feed Items By' in order to use this field.'");
$rssShowExpired = $_SESSION['translate']->it("Show Expired Items");
$rssShowExpired_content = $_SESSION['translate']->it("Show items that are previous to today's date.");
$emptyFeed = $_SESSION['translate']->it("Empty Feed Message");
$emptyFeed_content = $_SESSION['translate']->it("Use this field to add a custom message to display when the feed contains no items to display.");
?>

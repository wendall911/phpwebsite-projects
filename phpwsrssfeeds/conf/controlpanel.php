<?php

/**
 * Control panel configuration file for RSS Feeds
 *
 * @version $Id: controlpanel.php,v 1.3 2004/04/03 23:47:35 wendall911 Exp $
 * @author Wendall Cada <wendall911@users.sourceforge.net>
 */
 
// Defines the access to and appearance of module listings.

$image['name'] = "rss.png";
$image['alt'] = "RSS News Feeds by Wendall Cada";

$link[] = array("label"=>"RSS News Feeds",
		"module"=>"phpwsrssfeeds",
		"description"=>"Link to and display RSS news feeds from other sites.",
		"url"=>"index.php?module=phpwsrssfeeds&amp;RSS_MAN_op=list",
		"image"=>$image,
		"admin"=>TRUE,
		"tab"=>"content");

?>

<?php
/*
 *@$Id: update.php,v 1.33 2005/03/31 20:47:38 wendall911 Exp $
 *
 */
if (!$_SESSION["OBJ_user"]->isDeity()){
  header("location:index.php");
  exit();
}

$status = 1;
require_once(PHPWS_SOURCE_DIR.'mod/help/class/CLS_help.php');
require_once(PHPWS_SOURCE_DIR.'mod/language/class/Language.php');
require_once(PHPWS_SOURCE_DIR.'core/File.php');

if(version_compare($currentVersion, "0.1.1") < 0) {
  if($GLOBALS['core']->sqlImport(PHPWS_SOURCE_DIR."mod/phpwsrssfeeds/boost/update011.sql", TRUE, TRUE)){
    /* update Language */
    if(PHPWS_Language::uninstallLanguages('phpwsrssfeeds') && PHPWS_Language::installLanguages('phpwsrssfeeds'))
        $content .= "RSS Feeds successfully updated the Language system.<br />";
	/* update help system */
	if(CLS_help::uninstall_help("phpwsrssfeeds") && CLS_help::setup_help("phpwsrssfeeds"))
		$content .= "RSS Feeds successfully updated the Help system.<br />";
    /* Create image directory */
    if(mkdir($GLOBALS['core']->home_dir . "images/phpwsrssfeeds"))
        $content .= "phpwsRSSFeeds image directory " . $GLOBALS['core']->home_dir . "images/phpwsrssfeeds successfully created!<br />";
    else
        $content .= "phpwsRSSFeeds could not create the image directory: " . PHPWS_HOME_DIR . "images/phpwsrssfeeds<br />You will have to do this manually!<br />";

    if(mkdir($GLOBALS['core']->home_dir . "images/phpwsrssfeeds/thumbs"))
        $content .= "phpwsRSSFeeds image directory " . $GLOBALS['core']->home_dir . "images/phpwsrssfeeds/thumbs successfully created!<br />";
    else
        $content .= "phpwsRSSFeeds could not create the image directory: " . PHPWS_HOME_DIR . "images/phpwsrssfeeds/thumbs<br />You will have to do this manually!<br />";
     /* Create file directory */
    if(mkdir($GLOBALS['core']->home_dir . "files/phpwsrssfeeds"))
        $content .= "phpwsRSSFeeds files directory " . $GLOBALS['core']->home_dir . "files/phpwsrssfeeds successfully created!<br />";
    else
        $content .= "phpwsRSSFeeds could not create the files directory: " . $GLOBALS['core']->home_dir . "files/phpwsrssfeeds<br />You will have to do this manually!<br />";

    $content .= "RSS News Feed Module Version 0.1.1<br />";
    $content .= "------------------------------------------------------<br />";
    $content .= "+ Added Announcement and Calendar RSS Feed creation.<br />";
    $content .= "+ Renamed language file so it loads properly. (English Only)<br />";
    $content .= "+ Minor bug fixes. Now registers with help system properly.<br />";
    $content .= "+ Completed permissions system.<br /><br />";
  }else
    $status = 0;
}

if(version_compare($currentVersion, "0.2.0") < 0){
    $content = "RSS News Feed Module Version 0.2.0<br />";
    $content .= "------------------------------------------------------<br />";
    $content .= "+ Several Bug fixes due to htmlentities not processed properly.<br />";
}
if(version_compare($currentVersion, "0.2.1") < 0){
    $content = "RSS News Feed Module Version 0.2.1<br />";
    $content .= "------------------------------------------------------<br />";
    $content .= "+ Several bug fixes. Should now work properly with branches.<br />";
    $content .= "+ Changed the appearance of edit view to be more understandable.<br />";
}
if(version_compare($currentVersion, "0.2.3") < 0){
    $content = "RSS News Feed Module Version 0.2.3<br />";
    $content .= "------------------------------------------------------<br />";
    $content .= "+ Made changes so module properly uninstalls under 0.9.3-3.<br />";
    $content .= "+ Minor changes for proper use with 0.9.3-3.<br />";
    $content .= "+ Hidden items are now hidden.<br />";
}
if(version_compare($currentVersion, "0.2.5") < 0){
    $content = "RSS News Feed Module Version 0.2.5<br />";
    $content .= "------------------------------------------------------<br />";
    $content .= "+ Fixed a couple well formed issues.<br />";
    $content .= "+ Minor speed improvements.<br />";
}
if(version_compare($currentVersion, "0.3.0") < 0){
    if(!$GLOBALS["core"]->sqlColumnExists("mod_phpwsrssfeeds_feeds", "show_details"))
        if(!$GLOBALS['core']->query("ALTER TABLE mod_phpwsrssfeeds_feeds ADD COLUMN `show_details` " . "SMALLINT NOT NULL DEFAULT 0 AFTER `show_desc`", TRUE)) {
            $status = 0;
            continue;
        }
    if(!$GLOBALS["core"]->sqlColumnExists("mod_phpwsrssfeeds_feeds", "max_items"))
        if(!$GLOBALS['core']->query("ALTER TABLE mod_phpwsrssfeeds_feeds ADD COLUMN `max_items` " . "SMALLINT NOT NULL DEFAULT 20 AFTER `expire`", TRUE)) {
            $status = 0;
            continue;
        }
    if(!$GLOBALS["core"]->sqlTableExists("mod_phpwsrssfeeds_multi", TRUE))
        if(!$GLOBALS['core']->sqlImport(PHPWS_SOURCE_DIR."mod/phpwsrssfeeds/boost/update030.sql", TRUE, TRUE)){
            $status = 0;
            continue;
        }
    $content = "RSS News Feed Module Version 0.3.0<br />";
    $content .= "------------------------------------------------------<br />";
    $content .= "+ Added support for multiple Multi-View Feeds. Courtesy Darren Greene.<br />";
    $content .= "+ Added support for Article Manager and Links manager to backend.<br />";
    $content .= "+ New backend additions courtesy David M Walker <datamgmt at users.sourceforge.net>.<br />";
    $content .= "+ Added ability to set the number of items that appear in a feed.<br />";
    $content .= "+ Added ability to hide the show details link.<br />";
    $content .= "+ Removed all tables from administrative items.<br />";
    $content .= "+ Many thanks to Mike Noyes for his debug theme.<br />";
}
if(version_compare($currentVersion, "0.3.1") < 0){
  if(!$GLOBALS["core"]->sqlColumnExists("mod_phpwsrssfeeds_multi", "show_empty_feeds"))
    if(!$GLOBALS['core']->query("ALTER TABLE mod_phpwsrssfeeds_multi ADD COLUMN `show_empty_feeds` " . "SMALLINT NOT NULL DEFAULT '1' AFTER `pm_allow`", TRUE)) {
      $status = 0;
      continue;
    }
  $content .= "Added Column for disabiling empty feeds in the Multi-View.<br />";
  $content .= "RSS Feeds successfully updated the Help system.<br />";
}

if(version_compare($currentVersion, "0.3.2") < 0){
  if(!$GLOBALS["core"]->sqlColumnExists("mod_phpwsrssfeeds_multi", "show_anchor_links"))
    if(!$GLOBALS['core']->query("ALTER TABLE mod_phpwsrssfeeds_multi ADD COLUMN `show_anchor_links` " . "SMALLINT NOT NULL DEFAULT '1' AFTER `show_empty_feeds`", TRUE)) {
      $status = 0;
      continue;
    }
  $content .= "Added Column for disabiling anchor links in the Multi-View.<br />";

  if(!$GLOBALS["core"]->sqlColumnExists("mod_phpwsrssfeeds_multi", "sort_order"))
    if(!$GLOBALS['core']->query("ALTER TABLE mod_phpwsrssfeeds_multi ADD COLUMN `sort_order` " . "TEXT AFTER `show_anchor_links`", TRUE)) {
      $status = 0;
      continue;
    }
  $content .= "Added Column for sorting items in the Multi-View.<br />";

  if(!$GLOBALS["core"]->sqlColumnExists("mod_phpwsrssfeeds_multi", "show_cat_link"))
    if(!$GLOBALS['core']->query("ALTER TABLE mod_phpwsrssfeeds_multi ADD COLUMN `show_cat_link` " . "TEXT AFTER `show_anchor_links`", TRUE)) {
      $status = 0;
      continue;
    }
  $content .= "Added Column for showing category link in the Multi-View.<br />";

}

if(version_compare($currentVersion, "0.3.5") < 0){

    if(!$GLOBALS["core"]->sqlColumnExists("mod_phpwsrssfeeds_feeds", "asc_desc"))
        if(!$GLOBALS['core']->query("ALTER TABLE mod_phpwsrssfeeds_feeds ADD 
          COLUMN `asc_desc` " . "VARCHAR(10) NOT NULL DEFAULT 'ASC' AFTER `max_items`",
          TRUE)) {
            $status = 0;
            continue;
        }

    if(!$GLOBALS["core"]->sqlColumnExists("mod_phpwsrssfeeds_feeds", "orgn_by"))
        if(!$GLOBALS['core']->query("ALTER TABLE mod_phpwsrssfeeds_feeds ADD 
          COLUMN `orgn_by` " . "VARCHAR(10) NOT NULL DEFAULT 'Default' AFTER `asc_desc`",
          TRUE)) {
            $status = 0;
            continue;
        }
    if(!$GLOBALS["core"]->sqlColumnExists("mod_phpwsrssfeeds_feeds", "days_ahead"))
        if(!$GLOBALS['core']->query("ALTER TABLE mod_phpwsrssfeeds_feeds ADD 
          COLUMN `days_ahead` " . "VARCHAR(20) DEFAULT '0' AFTER `orgn_by`",
          TRUE)) {
            $status = 0;
            continue;
        }
            
    if(!$GLOBALS["core"]->sqlColumnExists("mod_phpwsrssfeeds_feeds", "show_expired"))
        if(!$GLOBALS['core']->query("ALTER TABLE mod_phpwsrssfeeds_feeds ADD 
          COLUMN `show_expired` " . "INT NOT NULL DEFAULT '1' AFTER `days_ahead`",
          TRUE)) {
            $status = 0;
            continue;
        }

    $content .= "Added ability to order feeds by date and alphabetical, ascending
      and descending.<br />";

}

if(version_compare($currentVersion, "0.3.7") < 0){
    CLS_help::uninstall_help("phpwsrssfeeds");
    CLS_help::setup_help("phpwsrssfeeds");
    $content .= "This is a test release for ordering features.";
}

if(version_compare($currentVersion, "0.4.0") < 0){
    if(!$GLOBALS["core"]->sqlTableExists("mod_phpwsrssfeeds_aggregate", TRUE))
        if(!$GLOBALS['core']->sqlImport(PHPWS_SOURCE_DIR."mod/phpwsrssfeeds/boost/update040.sql", TRUE, TRUE)){
            $status = 0;
            continue;
        }

    if(!$GLOBALS["core"]->sqlColumnExists("mod_phpwsrssfeeds_feeds", "empty_feed_msg"))
        if(!$GLOBALS['core']->query("ALTER TABLE mod_phpwsrssfeeds_feeds ADD 
          COLUMN empty_feed_msg " . "VARCHAR(60) DEFAULT 'Feed Contains No Content' AFTER show_expired",
          TRUE)) {
            $status = 0;
            continue;
        }
    
    $content = "RSS News Feed Module Version 0.4.0<br />";
    $content .= "------------------------------------------------------<br />";
    $content .= "+ Added feed aggregation.<br />";
}

if(version_compare($currentVersion, "0.4.1") < 0){
    $content .= "Feeds now update in branch sites.";
    $content .= "Change block view in Multi-View feeds to full view.";
}

if(version_compare($currentVersion, "0.4.2") < 0){
  if(!$GLOBALS['core']->isHub)
    if(!PHPWS_File::fileCopy(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/templates/style.css',
      $GLOBALS['core']->home_dir . 'files/phpwsrssfeeds/','style.css',0,0)) {
        $content .= 'Unable to copy '.PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/templates/style.css to '
          .$GLOBALS['core']->home_dir . 'files/phpwsrssfeeds/ you will have to do
          this manually.';
        $status = 0;
        continue;
    }
    CLS_help::uninstall_help("phpwsrssfeeds");
    CLS_help::setup_help("phpwsrssfeeds");
    $content .= "Added support for using file:// for local feed files. Courtesy Tony Miller.<br />";
    $content .= "Fixed bug where css wasn't available in branch sites.";
}
?>

<?php
require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/runtime.php');
require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/conf/rssfeeds.php');

/* Set modules array for dynamic placement */
if(!isset($_SESSION["PHPWSRSSFEEDS"]["module_list"])){
    $_SESSION["PHPWSRSSFEEDS"]["module_list"] = $GLOBALS['core']->listModules();
}
$home_feeds = FALSE;
$home_multi = FALSE;
//show RSS block items for current page
$runtimeObj = new RSS_runtime('RSS','mod_phpwsrssfeeds_feeds');
$feeds = $runtimeObj->display();

//Show Multi-RSS block items for current page
$runtimeObj->RSS_runtime('Multi_RSS','mod_phpwsrssfeeds_multi');
$multi = $runtimeObj->display();

//Show Aggregation block items for current page
$runtimeObj->RSS_runtime('RSS_Aggregate','mod_phpwsrssfeeds_aggregate');
$aggr = $runtimeObj->display();

//Check to see if items need updated
if (isset($_SESSION['PHPWSRSSFEEDS']['update'])){
  RSS::update_server();
  unset($_SESSION['PHPWSRSSFEEDS']['update']);
}

if($feeds || $multi || $aggr || 
  (isset($_REQUEST['module']) && $_REQUEST['module'] == "phpwsrssfeeds")) {
    if (file_exists($_SESSION["OBJ_layout"]->theme_dir
      .'templates/phpwsrssfeeds/style.css'))
        $_SESSION["OBJ_layout"]->addStyle('@import url("'
          .$_SESSION["OBJ_layout"]->theme_address.'templates/phpwsrssfeeds/style.css");');
    elseif ($GLOBALS['core']->isHub)
        $_SESSION["OBJ_layout"]->addStyle('@import url("mod/phpwsrssfeeds/templates/style.css");');
    else
        $_SESSION["OBJ_layout"]->addStyle('@import url("files/phpwsrssfeeds/style.css");');
}

?>
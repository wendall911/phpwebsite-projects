<?php

$listTags                      = array();
$listTags['LABEL_LABEL']       = $_SESSION["translate"]->it("Forum");
$listTags['THREADS_LABEL']      = $_SESSION["translate"]->it("Topics");
$listTags['POSTS_LABEL']     = $_SESSION["translate"]->it("Posts");
$listTags['LASTPOST_LABEL']     = $_SESSION["translate"]->it("Last Post");

$class       = "PHPWSBB_Forum";
$table       = "mod_phpwsbb_forums";

$dbColumns   = array("id", "label", "description", "threads", "posts", "lastpost", "editor","lastpost_topic_label",
                     "lastpost_topic_id", "lastpost_post_id" );
$listColumns = array("id", "label", "description", "threads", "posts", "lastpost", "editor","lastpost_topic_label",
                     "lastpost_topic_id", "lastpost_post_id" );
$name        = "categories";
$where       = "hidden='0'";
$template    = "forums";

?>
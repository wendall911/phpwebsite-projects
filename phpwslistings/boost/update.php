<?php

// $Id: update.php,v 1.17 2004/12/02 20:51:14 wendall911 Exp $

if (!$_SESSION["OBJ_user"]->isDeity()){
    header("location:index.php");
    exit();
}

$status = 1;

if (version_compare($currentVersion, "0.2.0") < 0) {
    if ($status = $GLOBALS["core"]->sqlImport(PHPWS_SOURCE_DIR . "mod/phpwslistings/boost/update_020-010.sql", TRUE)){
        $content .= "All phpwsListings tables successfully updated.<br />";

        // Need to manually update these rows
        $class_id = $GLOBALS["core"]->sqlInsert(array("name"=>"Default","use_price"=>1,"active"=>1), "mod_listings_classes",FALSE,TRUE,FALSE,TRUE);
        $GLOBALS["core"]->sqlUpdate(array("class_id"=>$class_id), "mod_listings");
        $GLOBALS["core"]->sqlUpdate(array("class_id"=>$class_id), "mod_listings_formelements");
        $GLOBALS["core"]->sqlUpdate(array("feature_title"=>"Featured Listings","block_title"=>"Random Listings","show_menu"=>1), "mod_listings_settings");
        $content .= "All phpwsListings data successfully updated.<br />";

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
        $content .= "There was a problem writing to the database.<br />";
    }
}

if (version_compare($currentVersion, "0.3.0") < 0) {
    CLS_help::setup_help("phpwslistings");
    if ($status = $GLOBALS["core"]->query("ALTER TABLE mod_listings ADD expiration date", TRUE)) {
        if ($status = $GLOBALS["core"]->query("ALTER TABLE mod_listings_settings ADD use_expiration smallint", TRUE))
            $GLOBALS["core"]->sqlUpdate(array("use_expiration"=>0), "mod_listings_settings");
    } else {
        // Alter table didn't go well
    }

    // Adding custom_layout to settings
    $status = $GLOBALS["core"]->query("ALTER TABLE mod_listings_settings ADD custom_layout int", TRUE);
    $GLOBALS["core"]->sqlUpdate(array("custom_layout"=>0), "mod_listings_settings");

    // Added default field to mod_listings_classes
    $status = $GLOBALS["core"]->query("ALTER TABLE mod_listings_classes ADD default_class smallint", TRUE);

    if ($status = $GLOBALS["core"]->query("ALTER TABLE mod_listings ADD listelements text", TRUE)) {
        /*
         * Extract data from mod_listings_listelements
         * and save as serialized array in mod_listings.listelements
         */
        $elements = array();
        $results = $GLOBALS["core"]->sqlSelect("mod_listings_listelements");
        if($results) {
            foreach ($results as $row)
                $elements[$row["listing_id"]][$row["field_name"]] = $row["field_value"];
        }

        foreach ($elements as $id=>$info) {
            // Build serialized string for each listing id
            $s = serialize($info);

            // Update that record
            $GLOBALS["core"]->sqlUpdate(array("listelements"=>$s), "mod_listings","id",$id);
        }

        // Drop mod_listings_listelements table
        $GLOBALS["core"]->sqlDropTable("mod_listings_listelements");
    }
    if (version_compare($currentVersion, "0.3.1") < 0) {
        $content .= "phpwsListings Module Version 0.3.1<br />";
        $content .= "------------------------------------------------------<br />";
        $content .= "+ Updates complete for use with 0.9.3-2.<br />";
    }
    if (version_compare($currentVersion, "0.3.2") < 0) {
        CLS_help::uninstall_help("phpwslistings");
        CLS_help::setup_help("phpwslistings");
        $content .= "phpwsListings Module Version 0.3.2<br />";
        $content .= "------------------------------------------------------<br />";
        $content .= "+ Changed install and update code to new version_compare.<br />";
    }
}

?>

<?php
/**
* index.php for phpwsListings
*
* index.php is the interface for the phpwsListings module
* that the phpws core will deal with and pass all requests to.
* index.php then in turn knows which phpwsListings classes to call.
*
* @author Don Seiler <don@seiler.us>
* @version $Id: index.php,v 1.19 2003/11/21 20:21:02 wendall911 Exp $
* @package phpwsListings
*/

if (!isset($GLOBALS['core'])){
  header("location:../../");
  exit();
}

if ($GLOBALS['core']->version !== "0.9.3-2"){
    
    require_once(PHPWS_SOURCE_DIR.'mod/phpwslistings/class/runtime.php');

    //Check to see if user has been deleted
    Listing_runtime::AgentCheck();

    if($GLOBALS["module"] == "home") {
        Listing_runtime::showListings(TRUE);
        Listing_runtime::showBlockListings();
    }

    Listing_runtime::showMenuBlock();
}

/* Check to see if the manager exists and create it if it doesn't */
if(!isset($_SESSION["SES_LISTING_MANAGER"])) {
    $_SESSION["SES_LISTING_MANAGER"] = new SEILER_ListingManager;
}

if(isset($_REQUEST["module"]) && $_REQUEST["module"] == "phpwslistings") {
    //if($_REQUEST["LISTINGS_op"] && $_SESSION["OBJ_user"]->allow_access("phpwslistings"))
    if(isset($_REQUEST["LISTINGS_op"]))
        switch($_REQUEST["LISTINGS_op"]) {
            case "menu":
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_MANAGER"]->listListings();
                break;

            case $_SESSION["translate"]->it("Create Listing Class"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_CLASS"] = new SEILER_ListingClass;
                $_SESSION["SES_LISTING_CLASS"]->edit();
                break;

            case $_SESSION["translate"]->it("Edit Listing Class"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_CLASS"] = new SEILER_ListingClass($_REQUEST["CLASS_id"]);
                $_SESSION["SES_LISTING_CLASS"]->edit();
                break;

            case $_SESSION["translate"]->it("Delete Listing Class"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_CLASS"] = new SEILER_ListingClass($_REQUEST["CLASS_id"]);
                $_SESSION["SES_LISTING_CLASS"]->delete();
                $_SESSION["SES_LISTING_MANAGER"]->loadClasses();
                break;
    
            case $_SESSION["translate"]->it("Manage Listing Classes"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_MANAGER"]->listClasses();
                break;

            case $_SESSION["translate"]->it("Save Class"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_CLASS"]->save();
                $_SESSION["SES_LISTING_MANAGER"]->loadClasses();
                $_SESSION["SES_LISTING_MANAGER"]->listClasses();
                break;

            case $_SESSION["translate"]->it("Create Form Element"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_ELEMENT"] = new SEILER_ListingElement;
                $_SESSION["SES_LISTING_ELEMENT"]->edit();
                break;

            case $_SESSION["translate"]->it("Edit Form Element"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_ELEMENT"] = new SEILER_ListingElement($_REQUEST["ELEMENT_id"]);
                $_SESSION["SES_LISTING_ELEMENT"]->edit();
                break;

            case $_SESSION["translate"]->it("Delete Form Element"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_ELEMENT"] = new SEILER_ListingElement($_REQUEST["ELEMENT_id"]);
                $_SESSION["SES_LISTING_ELEMENT"]->delete();
                $_SESSION["SES_LISTING_MANAGER"]->loadFormElements();
                break;
    
            case $_SESSION["translate"]->it("Manage Form Elements"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_MANAGER"]->listFormElements();
                break;

            case $_SESSION["translate"]->it("Edit Agent"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                if(isset($_REQUEST["AGENT_id"]))
                    $agentid = $_REQUEST["AGENT_id"];
                else
                    $agentid = $_SESSION["OBJ_user"]->user_id;
                    $_SESSION["SES_LISTING_AGENT"] = new SEILER_ListingAgent($agentid);
                    $_SESSION["SES_LISTING_AGENT"]->edit();
                break;

            case $_SESSION["translate"]->it("Manage Agents"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_MANAGER"]->listAgents();
                break;

            case $_SESSION["translate"]->it("Create Listing"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING"] = new SEILER_Listing;
                $_SESSION["SES_LISTING"]->setClassId($_REQUEST["CLASS_id"]);
                $_SESSION["SES_LISTING"]->edit();
                break;

            case $_SESSION["translate"]->it("Manage Listings"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_MANAGER"]->listListings();
                break;

            case $_SESSION["translate"]->it("Settings"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_MANAGER"]->getSettings();
                break;

            case $_SESSION["translate"]->it("Save Settings"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_MANAGER"]->setSettings();
                $_SESSION["SES_LISTING_MANAGER"]->listListings();
                break;

            case $_SESSION["translate"]->it("Save"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING"]->save();
                $_SESSION["SES_LISTING_MANAGER"]->listListings();
                break;

            case $_SESSION["translate"]->it("Edit"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING"] = new SEILER_Listing($_REQUEST["LISTING_id"]);
                $_SESSION["SES_LISTING"]->edit();
                break;

            case $_SESSION["translate"]->it("Delete"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING"] = new SEILER_Listing($_REQUEST["LISTING_id"]);
                $_SESSION["SES_LISTING"]->delete();
                break;

            case $_SESSION["translate"]->it("Save Agent"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_AGENT"]->save();
                $_SESSION["SES_LISTING_MANAGER"]->listAgents();
                break;

            case $_SESSION["translate"]->it("Save Element"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_ELEMENT"]->save();
                $_SESSION["SES_LISTING_MANAGER"]->loadFormElements();
                $_SESSION["SES_LISTING_MANAGER"]->listFormElements();
                break;

            case $_SESSION["translate"]->it("Show"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING"] = new SEILER_Listing($_REQUEST["LISTING_id"]);
                $_SESSION["SES_LISTING"]->showHide();
                $_SESSION["SES_LISTING_MANAGER"]->listListings();
                break;

            case $_SESSION["translate"]->it("Hide"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING"] = new SEILER_Listing($_REQUEST["LISTING_id"]);
                $_SESSION["SES_LISTING"]->showHide();
                $_SESSION["SES_LISTING_MANAGER"]->listListings();
                break;

            case $_SESSION["translate"]->it("Activate Agent"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_AGENT"] = new SEILER_ListingAgent($_REQUEST["AGENT_id"]);
                $_SESSION["SES_LISTING_AGENT"]->activate();
                $_SESSION["SES_LISTING_MANAGER"]->listAgents();
                break;

            case $_SESSION["translate"]->it("Deactivate Agent"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_AGENT"] = new SEILER_ListingAgent($_REQUEST["AGENT_id"]);
                $_SESSION["SES_LISTING_AGENT"]->activate();
                $_SESSION["SES_LISTING_MANAGER"]->listAgents();
                break;

            case "view":
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING"] = new SEILER_Listing($_REQUEST["LISTING_id"]);
                $_SESSION["SES_LISTING"]->view("full");
                break;

            case "viewelement":
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_ELEMENT"] = new SEILER_ListingElement($_REQUEST["ELEMENT_id"]);
                $_SESSION["SES_LISTING_ELEMENT"]->view();
                break;
        
            case "viewagent":
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_AGENT"] = new SEILER_ListingAgent($_REQUEST["AGENT_id"]);
                $_SESSION["SES_LISTING_AGENT"]->view();
                break;
        
            case "viewfullimage":
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_IMAGE"] = new SEILER_ListingImage($_REQUEST["IMAGE_id"]);
                $_SESSION["SES_LISTING_IMAGE"]->view();
                break;
        
            case "addimage":
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_IMAGE"] = new SEILER_ListingImage();
                $_SESSION["SES_LISTING_IMAGE"]->setIDVars($_REQUEST["LISTING_id"],$_REQUEST["LISTING_agent_id"]);
                $_SESSION["SES_LISTING_IMAGE"]->edit();
                break;
        
            case "editimage":
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_IMAGE"] = new SEILER_ListingImage($_REQUEST["IMAGE_id"]);
                $_SESSION["SES_LISTING_IMAGE"]->edit();
                break;
        
            case $_SESSION["translate"]->it("Save Image"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_IMAGE"]->save();
                break;
        
            case $_SESSION["translate"]->it("Delete Image"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_IMAGE"]->delete();
                break;
        
            case $_SESSION["translate"]->it("Create Agency"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_AGENCY"] = new SEILER_ListingAgency;
                $_SESSION["SES_LISTING_AGENCY"]->edit();
                break;

            case $_SESSION["translate"]->it("Manage Agencies"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_MANAGER"]->listAgencies();
                break;
        
            case $_SESSION["translate"]->it("Save Agency"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_AGENCY"]->save();
                $_SESSION["SES_LISTING_MANAGER"]->listAgencies();
                break;

            case $_SESSION["translate"]->it("Edit Agency"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_AGENCY"] = new SEILER_ListingAgency($_REQUEST["AGENCY_id"]);
                $_SESSION["SES_LISTING_AGENCY"]->edit();
                break;

            case $_SESSION["translate"]->it("Delete Agency"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_AGENCY"] = new SEILER_ListingAgency($_REQUEST["AGENCY_id"]);
                $_SESSION["SES_LISTING_AGENCY"]->delete();
                break;
            case "viewagency":
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_AGENCY"] = new SEILER_ListingAgency($_REQUEST["AGENCY_id"]);
                $_SESSION["SES_LISTING_AGENCY"]->view();
                break;
            case $_SESSION["translate"]->it("Show Agency"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_AGENCY"] = new SEILER_ListingAgency($_REQUEST["AGENCY_id"]);
                $_SESSION["SES_LISTING_AGENCY"]->activate();
                $_SESSION["SES_LISTING_MANAGER"]->listAgencies();
                break;

            case $_SESSION["translate"]->it("Hide Agency"):
                $_SESSION["SES_LISTING_MANAGER"]->menu();
                $_SESSION["SES_LISTING_AGENCY"] = new SEILER_ListingAgency($_REQUEST["AGENCY_id"]);
                $_SESSION["SES_LISTING_AGENCY"]->activate();
                $_SESSION["SES_LISTING_MANAGER"]->listAgencies();
                break;
        }

        if(isset($_REQUEST["LISTINGS_user_op"]))
          switch($_REQUEST["LISTINGS_user_op"]) {
            case "view":
                $_SESSION["SES_LISTING"] = new SEILER_Listing($_REQUEST["LISTING_id"]);
                $_SESSION["SES_LISTING"]->view("full");
                $_SESSION["SES_LISTING"]->hit();
                break;
        
            case "view_printable":
                $_SESSION["SES_LISTING"] = new SEILER_Listing($_REQUEST["LISTING_id"]);
                $_SESSION["SES_LISTING"]->view("printable");
                break;
        
            case "viewfullimage":
                $_SESSION["SES_LISTING_IMAGE"] = new SEILER_ListingImage($_REQUEST["IMAGE_id"]);
                $_SESSION["SES_LISTING_IMAGE"]->view();
                break;
        
            case "viewagent":
                $_SESSION["SES_LISTING_AGENT"] = new SEILER_ListingAgent($_REQUEST["AGENT_id"]);
                $_SESSION["SES_LISTING_AGENT"]->view();
                break;
        
            case "listagents":
                $_SESSION["SES_LISTING_MANAGER"]->showAgents();
                break;
        
            case "viewagency":
                $_SESSION["SES_LISTING_AGENCY"] = new SEILER_ListingAgency($_REQUEST["AGENCY_id"]);
                $_SESSION["SES_LISTING_AGENCY"]->view();
                $_SESSION["SES_LISTING_MANAGER"]->showAgents($_REQUEST["AGENCY_id"]);
                break;
        
            case "listagencies":
                $_SESSION["SES_LISTING_MANAGER"]->listAgencies();
                break;
        
            case "search":
                case $_SESSION["translate"]->it("Search"):
                $_SESSION["SES_LISTING"] = new SEILER_Listing();
                $_SESSION["SES_LISTING"]->setClassId($_REQUEST["CLASS_id"]);
                $_SESSION["SES_LISTING"]->searchForm();
                break;
        
            case $_SESSION["translate"]->it("Search Listings"):
                $_SESSION["SES_LISTING_MANAGER"]->search();
                break;
        
            case "email":
                $_SESSION["SES_LISTING_MANAGER"]->emailForm();
                break;
        
            case $_SESSION["translate"]->it("Send Email"):
                $_SESSION["SES_LISTING_MANAGER"]->sendEmail();
                break;
        
            case "showlistings":
            case $_SESSION["translate"]->it("View Listings"):
                $_SESSION["SES_LISTING_MANAGER"]->showListings(FALSE,$_REQUEST["CLASS_id"]);
                break;
          }
}

?>

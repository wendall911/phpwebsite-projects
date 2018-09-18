<?php
/**
 * List configuration file
 * Settings file for class/RSSList.php
 * Eventually this will be the settings file for core/List.php.
 * Not moving there yet as it would break backward compatability.
 * Items moved from core/Manager.php as it isn't being used any longer.
 *
 * @version $Id: manager.php,v 1.11 2004/12/17 18:29:42 wendall911 Exp $
 * @author Wendall Cada <wendall911@users.sourceforge.net>
 *
 */

/* Translate column and action labels first */
$id = $_SESSION['translate']->it("ID");
$hidden = $_SESSION['translate']->it("Hidden");
$label = $_SESSION['translate']->it("Title");
$channel = $_SESSION['translate']->it("Channel");
$edit = $_SESSION['translate']->it("Edit");
$delete = $_SESSION['translate']->it("Delete");
$hide = $_SESSION['translate']->it("Hide");
$show = $_SESSION['translate']->it("Show");
$view = $_SESSION['translate']->it("View");
$link = $_SESSION['translate']->it("Link");
$desc = $_SESSION['translate']->it("Description");
$type = $_SESSION['translate']->it("Type");
$customSort = $_SESSION['translate']->it("Ordering");
$numitems = $_SESSION['translate']->it("Items");

/* define the lists manager will control and 
	the database constraint for identifying a list member */
$lists = array("admin"=>"approved='1'",
	       "user"=>"approved='1' AND hidden='0'",
	       "backend"=>"approved='1'",
	       "multi"=>"approved='1' AND hidden='0'",
	       "multi_admin"=>"approved='1'",
           "aggr"=>"approved='1' AND hidden='0'",
           "aggr_admin"=>"approved='1'");

/* For each list, indicate templates directory where the manager will find layout templates.
	Must have list.tpl and row.tpl for each list. */
$templates = array("admin"=>"listmanager",
		   "user"=>"listmanager/user",
		   "backend"=>"listmanager/backend",
		   "multi"=>"listmanager/multi_rss",
		   "multi_admin"=>"listmanager/multi_rss/admin",
           "aggr"=>"listmanager/aggregate",
           "aggr_admin"=>"listmanager/aggregate/admin/");

/*	for the admin list, identify the data fields to be displayed in each column and the text of 
	the heading for the column
*/ 
$adminColumns = array("label"=>$label,
		      "channel"=>$channel,
		      "hidden"=>$hidden,
		      "id"=>$id);

/*	for the admin list, identify the actions that should be available,
	and the menu label to be specified for each.
*/
$adminActions = array("edit"=>$edit,
		      "view"=>$view,
		      "hide"=>$hide,
		      "show"=>$show,
		      "link"=>$link,
		      "delete"=>$delete);

/*	Currently the admin list is used by the diety user only. The user list is used for Admin.
*/					
$adminPermissions = array("edit"=>NULL,
			  "view"=>NULL,
			  "hide"=>NULL,
			  "show"=>NULL,
			  "link"=>NULL,
			  "delete"=>NULL); 

/*	for the admin list, provide parameters for paging the list */
$adminPaging = array("op"=>"RSS_MAN_op=list",
		     "limit"=>10,
		     "section"=>TRUE,
		     "limits"=>array(5,10,25,50),	
		     "back"=>"&#60;&#60;",
		     "forward"=>"&#62;&#62;",
                     "anchor"=>FALSE);

/*	now do the same for the user display list */

$userColumns = array("label"=>$label,
		     "channel_desc"=>$desc);

$userActions = array("view"=>$view,
		     "edit"=>$edit,
		     "delete"=>$delete,
		     "hide"=>$hide,
		     "show"=>$show,
		     "link"=>$link);
//These permissions only are in effect if the Administrator box is checked
$userPermissions = array("view"=>NULL,
			 "edit"=>"edit",
			 "delete"=>"delete",
			 "hide"=>"show_hide",
			 "show"=>"show_hide",
			 "link"=>"link");
					
$userPaging = array("op"=>"RSS_MAN_op=list",
		    "limit"=>10,
		    "section"=>TRUE,
		    "limits"=>array(5,10,25,50),
		    "back"=>"&#60;&#60;",
		    "forward"=>"&#62;&#62;");

/*
 * backend
 */
$backendColumns = array("label"=>$label,
			"type"=>$type,
			"numitems"=>$numitems,
			"id"=>$id);

$backendActions = array("edit"=>$edit,
			"delete"=>$delete,
			"view"=>$view);

$backendPermissions = array("edit"=>"editbackend",
			    "delete"=>"deletebackend");

$backendPaging = array("op"=>"RSS_BACKEND_MAN_op=list",
		       "limit"=>10,
		       "section"=>TRUE,
		       "limits"=>array(5,10,25,50),
		       "back"=>"&#60;&#60;",
		       "forward"=>"&#62;&#62;");
                            
/*
 * Multi-View Admin Settings
 */
$multi_adminColumns = array("label"=>$label,
			    "hidden"=>$hidden,
			    "id"=>$id);

$multi_adminActions = array("edit"=>$edit,
			    "view"=>$view,
			    "customSort"=>$customSort,
			    "delete"=>$delete,
			    "hide"=>$hide,
			    "show"=>$show,
			    "link"=>$link);
//These permissions only are in effect if the Administrator box is checked
$multi_adminPermissions = array("view"=>NULL,
				"edit"=>"multi_edit",
				"customSort"=>NULL,
				"delete"=>"multi_delete",
				"hide"=>"multi_show_hide",
				"show"=>"multi_show_hide",
				"link"=>"multi_link");

$multi_adminPaging = array("op"=>"RSS_MULTI_MAN_op=list",
			   "limit"=>10,
			   "section"=>TRUE,
			   "limits"=>array(5,10,25,50),
			   "back"=>"&#60;&#60;",
			   "forward"=>"&#62;&#62;");

/*
 * Multi-View User Settings
 */
$multiColumns = array("label"=>$label,
		      "id"=>$id);

$multiActions = array("view"=>$view);

$multiPermissions = array("view"=>NULL);

$multiPaging = array("op"=>"RSS_MULTI_MAN_op=list",
		     "limit"=>10,
		     "section"=>TRUE,
		     "limits"=>array(5,10,25,50),
		     "back"=>"&#60;&#60;",
		     "forward"=>"&#62;&#62;");
             
/*
 * Aggregated Admin Settings
 */
$aggr_adminColumns = array("label"=>$label,
			    "hidden"=>$hidden,
			    "id"=>$id);

$aggr_adminActions = array("edit"=>$edit,
			    "view"=>$view,
			    "delete"=>$delete,
			    "hide"=>$hide,
			    "show"=>$show,
			    "link"=>$link);
//These permissions only are in effect if the Administrator box is checked
$aggr_adminPermissions = array("view"=>NULL,
				"edit"=>"aggr_edit",
				"delete"=>"aggr_delete",
				"hide"=>"aggr_show_hide",
				"show"=>"aggr_show_hide",
				"link"=>"aggr_link");

$multi_adminPaging = array("op"=>"RSS_AGGR_MAN_op=list",
			   "limit"=>10,
			   "section"=>TRUE,
			   "limits"=>array(5,10,25,50),
			   "back"=>"&#60;&#60;",
			   "forward"=>"&#62;&#62;");

/*
 * Aggregated User Settings
 */
$aggrColumns = array("label"=>$label,
		      "id"=>$id);

$aggrActions = array("view"=>$view);

$aggrPermissions = array("view"=>NULL);

$aggrPaging = array("op"=>"RSS_AGGR_MAN_op=list",
		     "limit"=>10,
		     "section"=>TRUE,
		     "limits"=>array(5,10,25,50),
		     "back"=>"&#60;&#60;",
		     "forward"=>"&#62;&#62;");
?>

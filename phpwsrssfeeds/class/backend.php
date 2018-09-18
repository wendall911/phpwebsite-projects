<?php
/**
 * backend.php
 * Defines RSS backend class for phpwsRSSFeeds module
 * Extends PHPWS_Item
 *
 * This class is used for functions to create, edit and delete local RSS feeds.
 * The class defines additional variables to the standard ones handled automatically
 * by PHPWS_Item, defines a constructor, edit(), delete(), and view() function, and
 * an action() switch that handles other procedures (here, a save() function) that
 * are needed.
 *
 * The listing of these items is handled by an extension of the PHPWS_Manager
 * class, defined in core/Manager.php.
 *
 * @version $Id: backend.php,v 1.39 2005/02/02 18:11:11 wendall911 Exp $
 * @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
 * @package phpwsrssfeeds
 */

require_once(PHPWS_SOURCE_DIR.'core/Item.php');
require_once(PHPWS_SOURCE_DIR.'core/File.php');

class RSSFeeds_backend extends PHPWS_Item{

    /**
     * Number of items to display in feed.
     *
     * @var      string
     * @access   private
     */
    var $_numitems;
  
    /**
     * Type of feed to create
     *
     * @var      string
     * @access   private
     */
    var $_type;
  
    /**
     * Type of feed to create
     *
     * @var      string
     * @access   private
     */
    var $_description;
  
    /**
     * Icon to be included in feed.
     *
     * @var array
     * @access   private
     */
    var $_image;
  
    var $_error;
  
    function RSSFeeds_backend($backend_id = NULL) {
        if(is_null($backend_id)){
            $this->_numitems = NULL;
            $this->_type = NULL;
            $this->_description = NULL;
            $this->_image = NULL;
            $this->_error = NULL;
        }
        /* These vars are excluded on commit() */
        $exclude = array("_error");
        $this->addExclude($exclude);
        /* point to the backend data table */
        $this->setTable("mod_phpwsrssfeeds_backend");
    
        /* if the item exists, initialize its variables from the database */
        if (is_numeric($backend_id)) {
            /* setId returns a PHPWS_Error object */
            $this->_error = $this->setId($backend_id);
            if(!is_object($this->_error)) {
                /* initialize variables */
                $this->_error = $this->init($backend_id);
            }
        } elseif(is_array($backend_id)) {
            $this->_error = $this->init($backend_id);
        }

    }// END RSSFeeds_backend

    /**
     * Edit a RSS object's data
     *
     * @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
     */
    function _edit($id = NULL) {
        if(!is_null($id))
            $this->RSSFeeds_backend($id);
        //Standard error checking
        if (is_object($this->_error))
            return FALSE;

        /* allow admin to add/edit a backend link to an rdf doc of site announcements
         * Check to see if Calendar, Article Man, Links and Announcements are installed
         * Later we will add the ability for these items to register with phpwsRSSFeeds
         */
        if ($GLOBALS['core']->moduleExists("calendar")){
            $options["Calendar"] = "Calendar";
        }
        if ($GLOBALS['core']->moduleExists("announce")){
            $options["Announcements"] = "Announcements";
        }
        if ($GLOBALS['core']->moduleExists("article")){
            $options["Articles"] = "Articles";
        }
        if ($GLOBALS['core']->moduleExists("linkman")){
            $options["Recent Links"] = "Recent Links";
            $options["Popular Links"] = "Popular Links";
        }
        if (count ($options) == 0 ){
            $title = $_SESSION["translate"]->it("Error! You must have announcements, articles, linkman or calendar module installed to utilize this feature.");
            $content = RSS_common::_continueContent(NULL, "list", "RSS_BACKEND_MAN_op", $title);
            return $content;
        }
        $tab = 1;
        $tags = array();
        // Set up the edit form
        $form = new EZform("RSS_BACKEND_EDIT");

        $form->add("TITLE", "text", PHPWS_Text::parseOutput($this->getLabel()));
        $form->setSize("TITLE", 33);
        $form->setMaxSize("TITLE", 255);
        $form->setTab("TITLE", $tab++);

        $form->add("DESCRIPTION", "text", PHPWS_Text::parseOutput($this->_description));
        $form->setSize("DESCRIPTION", 33);
        $form->setMaxSize("DESCRIPTION", 255);
        $form->setTab("DESCRIPTION", $tab++);

        $form->add("NUMITEMS", "text", $this->_numitems);
        $form->setSize("NUMITEMS", 2);
        $form->setMaxSize("NUMITEMS", 2);
        $form->setTab("NUMITEMS", $tab++);

        $form->add("TYPE", "select", $options);
        $form->setMatch("TYPE", $this->_type);
        $form->setTab("TYPE", $tab++);

        /* Image */
        $form->imageForm("BACKEND_IMAGE", PHPWS_HOME_DIR . "images/phpwsrssfeeds", $this->_image["name"]);
        $form->add("SUBMIT_BUTTON", "submit", $this->getId() ? $_SESSION["translate"]->it("Update") 
	      : $_SESSION["translate"]->it("Save"));
        $error = $form->getError();
        $form->setTab("SUBMIT_BUTTON", 3);
        $form->add("module", "hidden", "phpwsrssfeeds");
        $form->add("PHPWS_MAN_ITEMS[]", "hidden", $this->getId());
        $form->add("RSS_BACKEND_MAN_op", "hidden", "save");

        $tags = $form->getTemplate();
        if($error)
            $tags['NEW_BACKEND_IMAGE'] = $error;
        // vary title for new and edited listings
        if ($this->getId()) {
            $GLOBALS["CNT_phpwsrssfeeds"]["title"] = $_SESSION["translate"]->it("Edit Backend Feed");
        } else {
            $GLOBALS["CNT_phpwsrssfeeds"]["title"] = $_SESSION["translate"]->it("Add Backend Feed");
        }
        /* include help links also, to display help for each field to be completed */
        $tags["TITLE_LABEL"] = $_SESSION['translate']->it("Title");
        $tags["DESCRIPTION_LABEL"] = $_SESSION['translate']->it("Description");
        $tags["NUMITEMS_LABEL"] = $_SESSION['translate']->it("Total Items in Feed");
        $tags["NUMITEMS_HELP"] = RSS_common::show_link("phpwsrssfeeds", "backendNumItems");
        $tags["TYPE_LABEL"] = $_SESSION['translate']->it("News Feed Source");
        $tags["TYPE_HELP"] = RSS_common::show_link("phpwsrssfeeds", "backendType");
        $tags["BACKEND_IMAGE_LABEL"] = $_SESSION["translate"]->it("News Feed Image");
        $tags["BACKEND_IMAGE_HELP"] = RSS_common::show_link("phpwsrssfeeds", "backendImage");
        $tags["BACKEND_SELECT_LABEL"] = $_SESSION["translate"]->it("Select Existing Image");

        $content = PHPWS_Template::processTemplate($tags, "phpwsrssfeeds", "edit_backend.tpl");
        return $content;
    
    } //END function _edit
  
    /**
     * Saves data for rss backend object to database
     *
     * @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
     */
    function _save() {
        if (isset($_POST["PHPWS_MAN_ITEMS"]) 
          && sizeof($_POST["PHPWS_MAN_ITEMS"]) > 0
          && is_array($_POST["PHPWS_MAN_ITEMS"]))
            $this->RSSFeeds_backend($_POST["PHPWS_MAN_ITEMS"][0]);
    
        //Standard error checking
        if (is_object($this->_error))
            return FALSE;
        //Check to see if save was a get request. If so, display error.
        if(isset($_GET['module']))
            return FALSE;
        $newbackendrss = ($this->getId() == NULL);
        $error = FALSE;
        $message = array();
        // we MUST have a rss feed title, since this is the label for the data entry; errors if not
        if (!isset($_POST["TITLE"]) || (trim($_POST["TITLE"]) == "")) {
            $message["TITLE"] = $_SESSION['translate']->it("Please enter a RSS Feed title. ");
            $_POST["TITLE"] = $_SESSION['translate']->it("Please enter a RSS Feed title.");
            $error = TRUE;
        }
        // we must have a rss feed description to generate a valid RSS 1.0 feed
        if (!isset($_POST["DESCRIPTION"]) || (trim($_POST["DESCRIPTION"]) == "")) {
            $message["DESCRIPTION"] = $_SESSION['translate']->it("Please enter a RSS Feed description. ");
            $_POST["DESCRIPTION"] = $_SESSION['translate']->it("Please enter a RSS Feed description.");
            $error = TRUE;
        }
        if(!is_numeric($_POST["NUMITEMS"])){
            $message["NUMITEMS"] = $_SESSION['translate']->it("Please enter a number into the number of items field. Reset to 5. ");
            $this->_numitems = 5;
            $error = TRUE;
        }else{
            $this->_numitems = $_POST["NUMITEMS"];
        }
        // save the edited data in the database in either case
        // note that parseInput called this way allows the global set of HTML tags to be included

        $this->setLabel(PHPWS_Text::parseInput($_POST["TITLE"]));
        $this->_description = PHPWS_Text::parseInput($_POST["DESCRIPTION"]);
        $this->_type = trim($_POST["TYPE"]);
        //Save Image
        if(isset($_FILES["NEW_BACKEND_IMAGE"]) && !empty($_FILES["NEW_BACKEND_IMAGE"]["name"])) {
            $imgarray = NULL;
            $dir = "images/phpwsrssfeeds/";
            $imgarray = EZform::saveImage("NEW_BACKEND_IMAGE", PHPWS_HOME_DIR . $dir);
            if(PHPWS_Error::isError($imgarray)) {
                $message["NEW_BACKEND_IMAGE"] = $_SESSION['translate']->it("There is a problem with that image. ");
                $error = TRUE;
            }
            if(!$error){
                $maxWidth = "88";
                $maxHeight = "32";
                $thumbnail = PHPWS_File::makeThumbnail($imgarray["name"], PHPWS_HOME_DIR . $dir, PHPWS_HOME_DIR . $dir . "thumbs/",$maxWidth,$maxHeight);
                if(is_file(PHPWS_HOME_DIR . $dir . "thumbs/" . $thumbnail[0])) {
                    $imgarray["thumb_name"] = $thumbnail[0];
                    $imgarray["thumb_width"] = $thumbnail[1];
                    $imgarray["thumb_height"] = $thumbnail[2];
                }
                $this->_image = $imgarray;
            }
        } elseif(isset($_POST["REMOVE_BACKEND_IMAGE"])) {
            // Delete image
            $dir = PHPWS_HOME_DIR . "images/phpwsrssfeeds/";
            unlink($dir . $this->_image["name"]);
            unlink($dir . "thumbs/" . $this->_image["thumb_name"]);
            $this->_image = NULL;

            $message = $_SESSION["translate"]->it("The image for [var1] was removed.", PHPWS_Text::parseOutput($this->getLabel()));
        } elseif(isset($_POST["CURRENT_BACKEND_IMAGE"]) && ($_POST["CURRENT_BACKEND_IMAGE"] != "none") && ($_POST["CURRENT_BACKEND_IMAGE"] != $this->_image["name"])) {
            $dir = "images/phpwsrssfeeds/";
            $fulldir = PHPWS_HOME_DIR . $dir;

            $size = getimagesize($fulldir . $_POST["CURRENT_BACKEND_IMAGE"]);
            $imgarray["name"] = $_POST["CURRENT_BACKEND_IMAGE"];
            $imgarray["width"] = $size[0];
            $imgarray["height"] = $size[1];

            // Need to change to this thumb.
            // Assuming last 4 chars in 
            $tnbase = substr($imgarray["name"], 0, -4);
            $tnext = substr($imgarray["name"], -4);
            $imgarray["thumb_name"] = $tnbase . "_tn" . $tnext;

            $size = getimagesize($fulldir . "thumbs/" . $imgarray["thumb_name"]);
            $imgarray["thumb_width"] = $size[0];
            $imgarray["thumb_height"] = $size[1];

            $this->_image = $imgarray;
        }

        if ($newbackendrss && !$error){
            //Check if directory for backend feeds is writeable
            $dir = PHPWS_SOURCE_DIR.'files/phpwsrssfeeds/';
            if(!@is_writeable($dir)){
                $message["FILE_ERR0"] = "Cannot create backend file in files/phpwsrssfeeds. ";
                $message["FILE_ERR1"] = "Files directory is not writeable, or doesn't exist.";
                $error = TRUE;
            }
        }

        if ($error) {
            $title = $_SESSION['translate']->it("Error");
            $content = RSS_common::_errorMessage($message, $title) . $this->_edit();
        } else {
            // tell user data is saved or updated
            $this->commit();

            if(!isset($_POST["REMOVE_BACKEND_IMAGE"])){
                $location = "http://".PHPWS_HOME_HTTP.'files/phpwsrssfeeds/backend'.$this->_id.'.php';
                //Write out backend file now that we know the dir is writeable
                $content = '<?php header("Location: http://'.PHPWS_HOME_HTTP.'index.php?module=phpwsrssfeeds&RSS_BACKEND_MAN_op=view&lay_quiet=1&PHPWS_MAN_ITEMS[]='.$this->_id.'"); ?>';
                $file = $GLOBALS['core']->home_dir.'files/phpwsrssfeeds/backend'.$this->_id.'.php';
                PHPWS_File::writeFile($file,$content,TRUE,FALSE);
                if ($newbackendrss) {
                    $content = RSS_common::_continueContent($_SESSION['translate']->it("RSS Feed saved. It can be found at: ")
                      .PHPWS_Text::parseOutput($location), "list", "RSS_BACKEND_MAN_op");
                } else {
                    $content = RSS_common::_continueContent($_SESSION['translate']->it("RSS Feed updated. It can be found at: ")
                      .PHPWS_Text::parseOutput($location), "list", "RSS_BACKEND_MAN_op");
                }
            }else{
                $content = $message . $this->_edit();
            }	
        }
        // set content display
        return $content;

    } //END function _save

    /**
     * Asks for confirmation and, getting it, deletes the current position listing
     *
     * @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
     */
    function _delete() {
        if (isset($_POST["YES_BUTTON"])) {
            foreach($_POST["PHPWS_MAN_ITEMS"] as $item){
                $this->RSSFeeds_backend($item);
                //Standard error checking
                if (is_object($this->_error))
                    return FALSE;
                // delete feed from files
                unlink($GLOBALS['core']->home_dir.'files/phpwsrssfeeds/backend'.$this->_id.'.php');
                // delete the current item 
                $this->kill();
            }
            if(isset($_POST["PHPWS_MAN_ITEMS"][1]))
                $content = RSS_common::_continueContent($_SESSION["translate"]->it("RSS Feeds deleted."), "list", "RSS_BACKEND_MAN_op");
            else
                $content = RSS_common::_continueContent($_SESSION["translate"]->it("RSS Feed deleted."), "list", "RSS_BACKEND_MAN_op");

        } elseif (isset($_POST["NO_BUTTON"])) {
            /* tell user all is still okay */
            if(isset($_POST["PHPWS_MAN_ITEMS"][1]))
                $content = RSS_common::_continueContent($_SESSION["translate"]->it("RSS Feeds NOT deleted."), "list", "RSS_BACKEND_MAN_op");
            else
                $content = RSS_common::_continueContent($_SESSION["translate"]->it("RSS Feed NOT deleted."), "list", "RSS_BACKEND_MAN_op");

        } elseif (isset($_POST["PHPWS_MAN_ITEMS"]) 
	      && sizeof($_POST["PHPWS_MAN_ITEMS"]) > 0
	      && is_array($_POST["PHPWS_MAN_ITEMS"])) {
            require_once(PHPWS_SOURCE_DIR.'core/Form.php');
            /* ask if the user is sure */
            $form = new EZform("RSS_BACKEND_DELETE");
            $form->add("YES_BUTTON", "submit", $_SESSION["translate"]->it("Yes"));
            $form->add("NO_BUTTON", "submit", $_SESSION["translate"]->it("No"));
            $form->add("module", "hidden", "phpwsrssfeeds");
            $form->add("RSS_BACKEND_MAN_op", "hidden", "delete");
            // note must indicate with [] that the id variable is in an array
            // I'll also build a sql query for the title list
            $num = 0;
            foreach($_POST["PHPWS_MAN_ITEMS"] as $id){
                $man_item = "PHPWS_MAN_ITEMS[$num]";
                $form->add($man_item, "hidden", $id);
                $num++;
            }
            $item_list = implode(" OR id = ", $_POST["PHPWS_MAN_ITEMS"]);
            $sql = "SELECT label FROM mod_phpwsrssfeeds_backend WHERE id = ".$item_list;
            $items = $GLOBALS["core"]->getAll($sql, TRUE);

            $tags = array();
           $tags = $form->getTemplate();
           $tags["MESSAGE"] = $_SESSION["translate"]->it("Delete ");
           foreach($items as $item){
               $tags["MESSAGE"] .= $item['label'].", ";
           }
           $tags["MESSAGE"] = rtrim($tags["MESSAGE"], ", ");
           $tags["MESSAGE"] .= "?";

           $content = PHPWS_Template::processTemplate($tags, "phpwsrssfeeds", "confirm.tpl");

          } else {
              $content = RSS_common::_errorMessage("Sorry, there was a problem with that request.");
          }

          return $content;

    }//END function delete

    function _list(){
        require_once(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/class/RSSList.php');
        $content = FALSE;
        $settings = array("_module" => "phpwsrssfeeds",
          "_table" => "mod_phpwsrssfeeds_backend",
          "_request" => "RSS_BACKEND_MAN_op");
        $listObj = RSSList::getRSSList($settings);
        $listObj->init();
        if ($_SESSION["OBJ_user"]->allow_access("phpwsrssfeeds"))
            $content = $listObj->getList("backend", $_SESSION["translate"]->it("Local RSS News Feeds"));

        return $content;
    }//End function _list()

    function CreateRDF($id = NULL){
        $_REQUEST["lay_quiet"] = 1;
        if(!is_null($id))
            $this->RSSFeeds_backend($id);
        //Standard error checking
        if (is_object($this->_error))
            return FALSE;
        $source_http = $GLOBALS['core']->home_http;
        $page_title = RSS_common::get_htmlentities($this->_label);
        $todays_date = date('Ymd');
        $todays_date_time = date('Y-m-d H:i:s');
        if($this->_type == 'Announcements'){
            $sql_statement = "SELECT id,subject,summary,dateUpdated,expiration FROM mod_announce WHERE expiration > '$todays_date_time' and approved=1 and active=1 ORDER BY id DESC limit $this->_numitems";
            $result = $GLOBALS["core"]->query($sql_statement, TRUE);
        }elseif($this->_type == 'Articles'){
            $sql_statement = "SELECT id,title,summary,updated_date FROM mod_article WHERE ( expiration_date IS NULL OR expiration_date > '$todays_date_time' ) AND publication_date < '$todays_date_time' AND approved=1 AND mainarticle=0 ORDER BY updated_date DESC limit $this->_numitems";
            $result = $GLOBALS["core"]->query($sql_statement, TRUE);
        }elseif($this->_type == 'Recent Links'){
            $sql_statement = "SELECT id,title,description,datePosted,url FROM mod_linkman_links WHERE active=1 ORDER BY datePosted DESC limit $this->_numitems";
            $result = $GLOBALS["core"]->query($sql_statement, TRUE);
        }elseif($this->_type == 'Popular Links'){
            $sql_statement = "SELECT id,title,description,datePosted,url FROM mod_linkman_links WHERE active=1 ORDER BY hits DESC limit $this->_numitems";
            $result = $GLOBALS["core"]->query($sql_statement, TRUE);
        }elseif($this->_type == "Calendar"){
            $result = $this->daysAhead();
        }
        if (!$result){
            echo "The rdf feed is unavailable at this time!";
            die();
        }
        header("Content-Type: text/xml");
        $iso8601_date = RSS_common::iso8601_date(time());
        $about = 'http://'.$source_http.'files/phpwsrssfeeds/backend'.$this->_id.'.php';
        $feed_description = RSS_common::get_htmlentities(PHPWS_Text::parseOutput($this->_description),ENT_QUOTES);
        $tags = array();
        $rdf_li = '';

        $tags['ABOUT'] = $about;
        $tags['PAGE_TITLE'] = $page_title;
        $tags['SOURCE_HTTP'] = $source_http;
        $tags['FEED_DESCRIPTION'] = $feed_description;
        $tags['ISO8601_DATE'] = $iso8601_date;

        if($this->_type == 'Announcements'){
            while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)){
                extract($row);
                $rdf['RESOURCE'] = 'http://'.$source_http.'index.php?module=announce&amp;ANN_user_op=view&amp;ANN_id='.$id;
                $rdf_li .= PHPWS_Template::processTemplate($rdf, 'phpwsrssfeeds', 'backend_rdf_li.tpl');
            }
        }elseif($this->_type == 'Articles'){
            while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)){
                extract($row);
                $rdf['RESOURCE'] = 'http://'.$source_http.'index.php?module=article&amp;view=news';
                $rdf_li .= PHPWS_Template::processTemplate($rdf, 'phpwsrssfeeds', 'backend_rdf_li.tpl');
            }
        }elseif($this->_type == 'Recent Links'){
            while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)){
                $url = NULL;
                extract($row);
                $rdf['RESOURCE'] = $url;
                $rdf_li .= PHPWS_Template::processTemplate($rdf, 'phpwsrssfeeds', 'backend_rdf_li.tpl');
            }
        }elseif($this->_type == 'Popular Links'){
            while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)){
                extract($row);
                $rdf['RESOURCE'] = $url;
                $rdf_li .= PHPWS_Template::processTemplate($rdf, 'phpwsrssfeeds', 'backend_rdf_li.tpl');
            }
        }elseif($this->_type == "Calendar"){
            for ($i = 0; $i <= count($result); $i++) 
                for($n = 0;$n <= count(@$result[$i]['data']); $n++){
                    $id = @$result[$i]['data'][$n]['id'];
                    if($id) {
                      $rdf['RESOURCE'] = 'http://'.$source_http.'index.php?module=calendar&amp;calendar[view]=event&amp;id='.$id;
                      $rdf_li .= PHPWS_Template::processTemplate($rdf, 'phpwsrssfeeds', 'backend_rdf_li.tpl');
		    }
                }
        }
        $tags['RDF_LI'] = $rdf_li;

        if(isset($this->_image['thumb_name'])){
            $url = 'http://'.$source_http.'images/phpwsrssfeeds/thumbs/'.$this->_image['thumb_name'];
            $tags['RESOURCE_THUMB_URL'] = $url;
            $tags['ABOUT_THUMB_URL'] = $url;
        }
        
        $tags['RDF_ITEMS'] = '';
        if($this->_type == 'Announcements'){
            $result2 = $GLOBALS["core"]->query($sql_statement, TRUE);
            while ($row = $result2->fetchRow(DB_FETCHMODE_ASSOC)){
                $dateUpdated = NULL;
                extract($row);
                $subject = PHPWS_Text::parseOutput($subject);
                $subject  = RSS_common::get_htmlentities($subject, ENT_QUOTES);
                $summary = PHPWS_Text::parseOutput($summary);
                $summary = RSS_common::get_htmlentities($summary, ENT_QUOTES);
                $item['ABOUT'] = 'http://'.$source_http.'index.php?module=announce&amp;ANN_user_op=view&amp;ANN_id='.$id;
                $item['TITLE'] = $subject;
                $item['LINK'] = 'http://'.$source_http.'index.php?module=announce&amp;ANN_user_op=view&amp;ANN_id='.$id;
                $item['DESCRIPTION'] = $summary;
                $tzd = date('O',time());
                $tzd = substr(chunk_split($tzd, 3, ':'),0,6);
                $item['DCDATE'] = RSS_common::cnvphpwsDate($dateUpdated);
                $tags['RDF_ITEMS'] .= PHPWS_Template::processTemplate($item, 'phpwsrssfeeds', 'backend_item.tpl');

            }
        }elseif($this->_type == 'Articles'){
            $result2 = $GLOBALS["core"]->query($sql_statement, TRUE);
            while ($row = $result2->fetchRow(DB_FETCHMODE_ASSOC)){
                $title = NULL;
                extract($row);
                $subject = PHPWS_Text::parseOutput($title);
                $subject = RSS_common::get_htmlentities($subject, ENT_QUOTES);
                $summary = PHPWS_Text::parseOutput($summary);
                $summary = RSS_common::get_htmlentities($summary, ENT_QUOTES);
                $item['ABOUT'] = 'http://'.$source_http.'index.php?module=article&amp;view='.$id;
                $item['TITLE'] = $subject;
                $item['LINK'] = 'http://'.$source_http.'index.php?module=article&amp;view='.$id;
                $item['DESCRIPTION'] = $summary;
		$item['DESCRIPTION'] .= '&lt;br /&gt;Updated: '.$dateUpdated;
                $tags['RDF_ITEMS'] .= PHPWS_Template::processTemplate($item, 'phpwsrssfeeds', 'backend_item.tpl');
            }
        }elseif($this->_type == 'Recent Links'){
            $result2 = $GLOBALS["core"]->query($sql_statement, TRUE);
            while ($row = $result2->fetchRow(DB_FETCHMODE_ASSOC)){
                $description = NULL;
                $datePosted = NULL;
                extract($row);
                $subject = PHPWS_Text::parseOutput($title);
                $subject = RSS_common::get_htmlentities($subject, ENT_QUOTES);
                $summary = PHPWS_Text::parseOutput($description);
                $summary = RSS_common::get_htmlentities($summary, ENT_QUOTES);
                $item['ABOUT'] = $url;
                $item['TITLE'] = $subject;
                $item['LINK'] = $url;
                $item['DESCRIPTION'] = $summary;
		$item['DESCRIPTION'] .= '&lt;br /&gt;Posted: '.$datePosted;
                $tags['RDF_ITEMS'] .= PHPWS_Template::processTemplate($item, 'phpwsrssfeeds', 'backend_item.tpl');
            }
        }elseif($this->_type == 'Popular Links'){
            $result2 = $GLOBALS["core"]->query($sql_statement, TRUE);
            while ($row = $result2->fetchRow(DB_FETCHMODE_ASSOC)){
                extract($row);
                $subject = PHPWS_Text::parseOutput($title);
                $subject = RSS_common::get_htmlentities($subject, ENT_QUOTES);
                $summary = PHPWS_Text::parseOutput($description);
                $summary = RSS_common::get_htmlentities($summary, ENT_QUOTES);
                $item['ABOUT'] = $url;
                $item['TITLE'] = $subject;
                $item['LINK'] = $url;
                $item['DESCRIPTION']  = $summary;
		$item['DESCRIPTION'] .= '&lt;br /&gt;Posted: '.$datePosted;
                $tags['RDF_ITEMS'] .= PHPWS_Template::processTemplate($item, 'phpwsrssfeeds', 'backend_item.tpl');
            }
        }elseif($this->_type == "Calendar"){

            foreach($result as $calFeed) {
                for($n = 0;$n <= count($calFeed['data']); $n++){
                    $id = @$calFeed['data'][$n]['id'];
                    if($id){
                        $title = PHPWS_Text::parseOutput($calFeed['data'][$n]['title']);
                        $title  = RSS_common::get_htmlentities($title, ENT_QUOTES);
                        $description = PHPWS_Text::parseOutput($calFeed['data'][$n]['description']);
                        $description  = RSS_common::get_htmlentities($description, ENT_QUOTES);
                        $item['ABOUT'] = 'http://'.$source_http.'index.php?module=calendar&amp;calendar[view]=event&amp;id='.$id;
                        $item['TITLE'] = $title;
                        $item['LINK'] = 'http://'.$source_http.'index.php?module=calendar&amp;calendar[view]=event&amp;id='.$id;
                        $item['DESCRIPTION'] = $description;
                        $startDate = $calFeed['data'][$n]['startDate'];
                        $endDate = $calFeed['data'][$n]['endDate'];
                        $startTime = sprintf("%04d",$calFeed['data'][$n]['startTime']);
                        $endTime = sprintf("%04d",$calFeed['data'][$n]['endTime']);
                        $endRepeat = $calFeed['data'][$n]['endRepeat'];
                        $startYear = substr($startDate,0,4);
                        $startMonth =substr($startDate,4,2);
                        $startDay = substr($startDate,6,2);
                        $endYear = substr($endDate,0,4);
                        $endMonth = substr($endDate,4,2);
                        $endDay = substr($endDate,6,2);
                        $repeatYear = substr($endRepeat,0,4);
                        $repeatMonth = substr($endRepeat,4,2);
                        $repeatDay = substr($endRepeat,6,2);
                        $startHour = substr($startTime,0,2);
                        $startMinute = substr($startTime,2,2);
                        $endMinute = substr($endTime,2,2);
                        $endHour = substr($endTime,0,2);

                        $startTS = mktime(0,0,0,$startMonth, $startDay, $startYear);
                        $endTS   = mktime(0,0,0,$endMonth, $endDay, $endYear);
                        $repeatTS = mktime(0,0,0, $repeatMonth, $repeatDay, $repeatYear);
			
			
                        if($calFeed['data'][$n]["eventType"]=="allday"){			  			  
			  $item['DESCRIPTION'] .= "&lt;br /&gt;".$calFeed['FullDate']. "&lt;br /&gt;All Day Event";
			  
                        }else{
                            //Determine AM or PM and set vars
                            if($startHour >= 12)
                                $sampm = ' p.m.';
                            else
                                $sampm = ' a.m.';
                            if($endHour >= 12)
                                $eampm = ' p.m.';
                            else
                                $eampm = ' a.m.';
                            //Convert hour to 12 hour format
                            $endHour = ($endHour + 1) > 12 ? $endHour - 12 : $endHour;
                            $endHour = sprintf("%02d", $endHour==0 ? 12 : $endHour);
                            $deadline = ltrim($endHour,"\0x00").":".$endMinute;
                            if($calFeed['data'][$n]["eventType"]=="deadline"){
                                $item['DESCRIPTION'] .= "&lt;br /&gt;".
                                  "The deadline is ".
                                  $deadline.$eampm." on ".$calFeed['FullDate'].".";
                            }

                              if($calFeed['data'][$n]["eventType"]=="interval" && ($endRepeat)){
                                  $startHour = ($startHour + 1) > 12 ? $startHour - 12 : $startHour;
                                  $startHour = sprintf("%02d", $startHour==0 ? 12 : $startHour);
                                  $starts = ltrim($startHour,"\0x00").":".$startMinute;
                                  $endHour = ($endHour + 1) > 12 ? $endHour - 12 : $endHour;
                                  $endHour = sprintf("%02d", $endHour==0 ? 12 : $endHour);
                                  $ends = ltrim($endHour,"\0x00").":".$endMinute;

                                  $item['DESCRIPTION'] .= "&lt;br /&gt;Event is from ".$starts.$sampm." to ".$ends.$eampm . " on ".$calFeed['FullDate'].".";
                                  $dcdate = $calFeed["dcdate"];
                              }

                              if($calFeed['data'][$n]["eventType"]=="interval" && (!$endRepeat)){
                                  $startHour = ($startHour + 1) > 12 ? $startHour - 12 : $startHour;
                                  $startHour = sprintf("%02d", $startHour==0 ? 12 : $startHour);
                                  $starts = ltrim($startHour,"\0x00").":".$startMinute;
                                  $endHour = ($endHour + 1) > 12 ? $endHour - 12 : $endHour;
                                  $endHour = sprintf("%02d", $endHour==0 ? 12 : $endHour);
                                  $ends = ltrim($endHour,"\0x00").":".$endMinute;

                                  if($endTS != $startTS) {
                                      $item['DESCRIPTION'] .= "&lt;br /&gt;Begins at  "
                                        .$starts.$sampm." on ".$startMonth."/".$startDay."/".$startYear." and ends at ".$ends.$eampm." on ".$endMonth."/".$endDay."/".$endYear.".";		  
                                  } else {
                                      $item['DESCRIPTION'] .= "&lt;br /&gt;Event begins at  "
                                        .$starts.$sampm." and ends at ".$ends.$eampm." on ".
                                        $startMonth."/".$startDay."/".$startYear.".";
                                  }
                              }
                              if($calFeed['data'][$n]["eventType"]=="start"){
                                  $startHour = ($startHour + 1) > 12 ? $startHour - 12 : $startHour;
                                  $startHour = sprintf("%02d", $startHour==0 ? 12 : $startHour);
                                  $starts = ltrim($startHour,"\0x00").":".$startMinute;
                                  $endHour = ($endHour + 1) > 12 ? $endHour - 12 : $endHour;
                                  $endHour = sprintf("%02d", $endHour==0 ? 12 : $endHour);
                                  $ends = ltrim($endHour,"\0x00").":".$endMinute;

                                  $item['DESCRIPTION'] .= "&lt;br /&gt;&lt;br /&gt;Event begins at "
                                    .$starts.$sampm." on ".$startYear."-".$startMonth."-".$startDay.".";
                              }
                          }

                          $dcdate = RSS_common::cnvphpwsDate($calFeed["dcdate"]);
                          $item['DCDATE'] = $dcdate;
                          $tags['RDF_ITEMS'] .= PHPWS_Template::processTemplate($item, 'phpwsrssfeeds', 'backend_item.tpl');
                    }
                }
            }
        }

        echo PHPWS_Template::processTemplate($tags, 'phpwsrssfeeds', 'backend_feed.tpl');
    }//end function CreateRDF()

    /**
     * Return array of upcoming events based on current calendar Coming Soon... settings
     *
     * @param  string  $year 	date string for year YYYY
     * @param  string  $month date string for month MM
     * @param  string  $day date string for day DD
     * @modified Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
     */
    function daysAhead($year=NULL, $month=NULL, $day=NULL){
        require_once (PHPWS_SOURCE_DIR . "/mod/calendar/class/Display.php");
        require_once (PHPWS_SOURCE_DIR . "/mod/calendar/class/Calendar.php");
        $GLOBALS["calendarSettings"] = PHPWS_Calendar::getSettings();
        $settings = $GLOBALS["calendarSettings"];
        if (!($viewAhead = $settings["daysAhead"]))
            return NULL;

        if (is_null($year))
            $year = date("Y");

        if (is_null($month))
            $month = date("m");

        if (is_null($day))
            $day = date("d");

        //stub for cache data return, will generate everything for now
        $date = new Date;
        $date->setMonth($month);
        $date->setYear($year);
        $date->setDay($day);

        $end = $date;
        $end->addSeconds(86400 * $viewAhead);

        $eventList = PHPWS_Calendar::loadEvents($date, $end);
        $eventCount = 0;
        $i = 0;
        $maxnum = 0;
        $eventdata = NULL;
        while($end->after($date)) {
            if ($events = PHPWS_Calendar::getEvents($date, $eventList)){
                $thisdate = $date->format("%a. %e %b. %Y");
                $dcDate   = $date->format("%Y-%m-%d");
                $num = 0;
                foreach ($events as $time=>$weekday){
                    foreach ($weekday as $itemid=>$event){
                        if($maxnum == $this->_numitems)
                            return $eventdata;
                        $eventdata[$i]['FullDate'] = $thisdate;
                        $eventdata[$i]['dcdate']   = $dcDate;
                        $eventdata[$i]['data'][$num]= $event;
                        $num++;
                        $maxnum++;
                    }
                }
            }
            $i++;
            $date = $date->getNextDay();
        }
        return $eventdata;
    }//end function daysAhead()

}
?>
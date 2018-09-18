<?php

/**
 * Install file for RSSFeeds
 *
 * @version $Id: install.php,v 1.13 2005/03/31 20:47:36 wendall911 Exp $
 * @author Wendall Cada <wendall911@users.sourceforge.net>
 */

/* return to home page if the user is not the Deity */
if (!$_SESSION['OBJ_user']->isDeity()){
	header('location:index.php');
	exit();
}

// Need to do core version check
if(version_compare($GLOBALS['core']->version, '0.9.3-1') < 0) {
    $content .= $_SESSION['translate']->it('This module requires a phpWebSite core version of 0.9.3-1 or greater to install.').'<br />';
    $content .= $_SESSION['translate']->it('You are currently using phpWebSite core version ') . $GLOBALS['core']->version . '.<br />';
    return;
}

require_once(PHPWS_SOURCE_DIR.'mod/help/class/CLS_help.php');
require_once(PHPWS_SOURCE_DIR . 'core/File.php');
$status = 1;
//Create status messages for create_dir()
$error[0] = $_SESSION['translate']->it(' was successfully created!').'<br />';
$error[1] = $_SESSION['translate']->it(' exists but is not writable. You will have to change the permissions
 to 775 or 777. Please just refresh your browser after you have changed the
 directory permissions to continue.').'<br />';
$error[2] = $_SESSION['translate']->it(' needs to be created to continue. Please make sure you have write
 permissions enabled. Either 775 or 777. Please refresh your browser after you
 have created this directory with the proper permissions.').'<br />';
$error[3] = $_SESSION['translate']->it(' is not writable. Installer was unable to change permissions. You
 will have to change this directory to 775 or 777 permissions manually. Please
 refresh your browser after you have changed the directory permissions to
 continue.').'<br />';
$error[4] = $_SESSION['translate']->it(' needs to be created to continue. Please make sure you have write
 permissions enabled. Either 775 or 777. Please refresh your browser after you
 have created this directory with the proper permissions.').'<br />';
$error[5] = $_SESSION['translate']->it(' will have to be created manually with
 permissions set to 777 because of safe_mode restrictions. Please refresh your
 browser after you have created this directory with the proper permissions to
 continue.').'<br />';
 
//Create image directory
$dir = $GLOBALS['core']->home_dir . 'images/phpwsrssfeeds';
$dir_status = create_dir($dir);
if($dir_status[1] !== 0)
    $status = 0;
$content .= $dir . $error[$dir_status[1]];

//Create thumbnail directory
$dir = $GLOBALS['core']->home_dir . 'images/phpwsrssfeeds/thumbs';
$dir_status = create_dir($dir);
if($status && $dir_status[1] !== 0)
    $status = 0;
$content .= $dir . $error[$dir_status[1]];

//Create files directory
$dir = $GLOBALS['core']->home_dir . 'files/phpwsrssfeeds';
$dir_status = create_dir($dir);
if($status && $dir_status[1] !== 0)
    $status = 0;
$content .= $dir . $error[$dir_status[1]];

//Move stylesheet over to branch
if($status && !$GLOBALS['core']->isHub)
    if(!PHPWS_File::fileCopy(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/templates/style.css',
      $GLOBALS['core']->home_dir . 'files/phpwsrssfeeds/','style.css',0,0)) {
        $content .= 'Unable to copy '.PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/templates/style.css to '
          .$GLOBALS['core']->home_dir . 'files/phpwsrssfeeds/ you will have to do
          this manually.';
        $status = 0;
    }

if(!$status)
    return;
/* read sql install script, execute it, suppress errors */
if ($GLOBALS['core']->sqlImport(PHPWS_SOURCE_DIR.'mod/phpwsrssfeeds/boost/install.sql', TRUE, TRUE)) {

	/* if successful, report the fact */
	$content .= $_SESSION['translate']->it('All RSS Feeds tables successfully written.') . '<br />';

	/* register the module with the help system if it's loaded */
	if(CLS_help::setup_help('phpwsrssfeeds'))
		$content .= $_SESSION['translate']->it('RSS Feeds successfully registered with Help system.') . '<br />';
    if (version_compare($GLOBALS['core']->version , '0.9.3-2') < 0){	
        /* register the module with the search system */
        /* comment out this section if you don't want search included */
        $search['module'] = 'phpwsrssfeeds';
        $search['search_class'] = 'RSS_ListManager';
        $search['search_function'] = 'search';
        $search['search_cols'] = 'label or content';
        $search['view_string'] = '&amp;RSS_MAN_op=sView&amp;RSS_id=';
        $search['show_block'] = 1;
        $search['block_title'] = $_SESSION['translate']->it('News Feeds');
        if($GLOBALS['core']->version == '0.9.3-2'){
            if(!$GLOBALS['core']->sqlInsert($search, 'mod_search_register')) {
                $content .= $_SESSION['translate']->it('RSS Feeds NOT registered with Search system.') . '<br />';
            } else {
                $content .= $_SESSION['translate']->it('RSS Feeds successfully registered with Search system.') . '<br />';
            }
        }
    }// end of search section
} else {
	$content .= $_SESSION['translate']->it('Unable to write to the database.') . '<br />';
	$status = 0;
}

/*
 * Checks to see if the file/directory exists, tries to create the directory 
 * with proper permissions if it doesn't
 * @param string $dir name of directory
 * @param decimal $perms permissions to set for file. If not set defaults 02775
 * if using the sticky bit, the perms must be preceeded by a 0. 0755 works 2755
 * doesn't, so you must use 02755
 * @return array Returns file perms and status codes:
 * 0 - Directory successfully created.
 * 1 - Directory exists but is not writable.
 * 2 - Parent directory does not exist.
 * 3 - Parent directory is not writable and chmod didn't fix the issue.
 * 4 - Directory could not be created
 * 5 - safe_mode detected. Manual directory creation.
 * @access   public
 */
function create_dir($dir,$perms=02775) {
    if(!file_exists($dir)) {
        if(ini_get('safe_mode'))
            return array(NULL,5);
        //First check permissions of parent
        $parent_dir = dirname($dir);
        if(!file_exists($parent_dir))
            return array(NULL,2);
        //Check if writable, if not, try to make it so, else return error 3
        if(!is_writable($parent_dir)){
            if(!PHPWS_File::setFilePermissions($parent_dir,$perms))
                return array(substr(sprintf('%o', fileperms($parent_dir)), -4),3);
            if(!PHPWS_File::makeDir($dir,$perms))
                return array(NULL,4);
        } else {
            if(!PHPWS_File::makeDir($dir,$perms))
                return array(NULL,4);
        }
    } else {
        //Check if writable, if not, try to make it so, else return error 3
        if(!is_writable($dir)) {
            if(ini_get('safe_mode'))
                return array(NULL,1);
            if(!PHPWS_File::setFilePermissions($dir,$perms))
                return array(substr(sprintf('%o', fileperms($dir)), -4),1);
        }
    }
    return array(substr(sprintf('%o', fileperms($dir)), -4),0);
}//End function create_dir()
?>
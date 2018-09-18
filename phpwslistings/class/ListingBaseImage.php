<?php
/**
* SEILER_ListingBaseImage for phpwsListings
*
* SEILER_ListingBaseImage represents an image for any phpwsListings object
*
* @author Don Seiler <don@seiler.us>
* @version $Id: ListingBaseImage.php,v 1.12 2003/11/21 20:22:36 wendall911 Exp $
* @package phpwsListings
*/
class SEILER_ListingBaseImage {

    var $_image = array();
    var $_imagedir = NULL;
    var $_imageurl = "images/phpwslistings/";
    var $_new = TRUE;


    function SEILER_ListingBaseImage($imagesubdir = NULL, $image = NULL) {
        if(isset($image)) {
            $this->_new = FALSE;
            $this->_image = unserialize($image);
        }

        $this->_imagedir = PHPWS_HOME_DIR . "images/phpwslistings/";
        $this->_imagedir .= $imagesubdir . "/";
        $this->_imageurl .= $imagesubdir . "/";
    }


    function isEmpty() {
        if($this->_image)
            return FALSE;
        else
            return TRUE;
    }


    function getSerializedImage() {
        return serialize($this->_image);
    }

    /**
    * Thumbnail display
    *
    * Returns img tag string
    *
    * @access public
    */
    function renderThumb() {
        $imgtag = "<img src=\"" . $this->_imageurl . $this->_image["thumb_name"] . "\" width=\"" . $this->_image["thumb_width"] . "\" height=\"" . $this->_image["thumb_height"] . "\" alt=\"" . $this->_image["alt"] . "\">";
        return $imgtag;
    }


    /**
    * Full display
    *
    * Returns img tag string
    *
    * @access public
    */
    function renderFull() {
        $imgtag = "<img src=\"" . $this->_imageurl. $this->_image["name"] . "\" width=\"" . $this->_image["width"] . "\" height=\"" . $this->_image["height"] . "\" alt=\"" . $this->_image["alt"] . "\">";
        return $imgtag;
    }


    function getAlt() {
        if(isset($this->_image["alt"]))
            return $this->_image["alt"];
        else
            return NULL;
    }


    /**
    * Save data
    *
    * Saves Image data to database if user has rights
    *
    * @access public
    * @see add(), update()
    */
    function save($image = array(), $alt = NULL) {

        if(!empty($image["name"])) {
            if(!$_SESSION["SES_LISTING_MANAGER"]->isAllowedImageType($image["type"])) {
                $this->_error("not_allowed_type");
                return FALSE;
            }

            if($image["type"] == "image/gif") {
                // Test for gif2png
                include(PHPWS_SOURCE_DIR . "mod/phpwslistings/conf/config.php");
                if(!file_exists($gif2png)) {
                    $this->_error("gif2png_missing");
                    return FALSE;
                }
            }

            if(!$this->checkFileSize($image["tmp_name"])) {
                $this->_error("file_too_large");
                return FALSE;
            }

            $file= $image["name"];

            // If the name of a new image is taken, append digits
            // until an available name is found
            if($this->_new) {
                if(file_exists($this->_imagedir . $file)) {
                    for($i=1;;$i++) {
                        if(!file_exists($imagedir . $i . "_" . $file)) {
                            $file = $i . "_" . $file;
                            $image["name"] = $file;
                            break;
                        }
                    }
                }
            }

            if(is_uploaded_file($image["tmp_name"])) {
                if(move_uploaded_file($image["tmp_name"], $this->_imagedir . $file)) {
                    // Change permissions to 644 (some webhosts don't do this)
                    chmod($this->_imagedir . $file, 0644);

                    // Convert gifs to pngs without prejudice
                    if($image["type"] == "image/gif") {
                        $pngfilename = basename($file, ".gif") . ".png";
    
                        // If the name of a new image is taken, append digits
                        // until an available name is found
                        if($this->_new) {
                            if(file_exists($this->_imagedir . $pngfilename)) {
                                for($i=1;;$i++) {
                                    if(!file_exists($this->_imagedir . $i . "_" . $pngfilename)) {
                                        $pngfilename = $i . "_" . $pngfilename;
                                        $image["name"] = $pngfilename;
                                        $newfile = basename($pngfilename, ".png") . ".gif";
                                        rename($this->_imagedir . $file, $this->_imagedir . $newfile);
                                        $file = $newfile;
                                        break;
                                    }
                                }
                            }
                        }
        
                        $imagepath = $this->_imagedir . $file;
                        $cmd = $gif2png . " -d -O " . $imagepath;
                        passthru($cmd, $return);
    
                        $image["name"] = $pngfilename;
                        $image["type"] = "image/png";
                    } 
    
                    if(isset($this->_image["name"]) && ($this->_image["name"] != $image["name"])) {
                        unlink($this->_imagedir . $this->_image["name"]);
                        unlink($this->_imagedir . $this->_image["thumb_name"]);
                    }

                    $image_size = getimagesize($this->_imagedir . $image["name"]);

                    $this->_image["name"] = $image["name"];
                    $this->_image["type"] = $image["type"];
                    $this->_image["width"] = $image_size[0];
                    $this->_image["height"] = $image_size[1];

                    $this->resizeIfNeeded();

                } else {
                    $this->_error("image_upload");
                    return FALSE;
                }

                $this->resize(TRUE, NULL, NULL, 100);
                $this->_image["alt"] = $alt;

                return $this->getSerializedImage();
            } else {
                return NULL;
            }
        }
    }


    /**
    * Delete Image
    *
    * Deletes record from database
    *
    * @access public
    */
    function delete() {
        if(!(unlink($this->_imagedir . $this->_image["name"]) && unlink($this->_imagedir . $this->_image["thumb_name"]))) {
            $this->_error("delete_image_files");
            return;
        } else {
            $this->_image = array();
        }
        return $this->getSerializedImage();
    }



    /**
    * Creates thumbnail
    *
    * Creates thumbnail of new image
    *
    * @access public
    # @see add()
    */
    function resize($thumb = TRUE, $ratio = NULL, $height = NULL, $width = NULL) {
        $orig_image = $this->_imagedir . $this->_image["name"];
        $src_height = $this->_image["height"];
        $src_width = $this->_image["width"];

        if(empty($ratio)) {
            // Find ratio
            $ratio = 1;
            if(empty($width) && empty($height)){
                // error
            } elseif(!empty($width))
                if($src_width > $width) {
                    $ratio = $width/$src_width;
                }
            elseif(!empty($height))
                if($src_height > $height) {
                    $ratio = $height/$src_height;
                }
        }

        $dst_height = $ratio * $src_height;
        $dst_width = $ratio * $src_width;


        switch($this->_image["type"]) {
            case "image/x-png":
            case "image/png":
                $src_image = imagecreatefrompng($orig_image);
                break;
            case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
                $src_image = imagecreatefromjpeg($orig_image);
                break;
        }

        if($thumb) {
            $this->_image["thumb_name"] = "thumb_" . $this->_image["name"];
            $this->_image["thumb_height"] = $dst_height;
            $this->_image["thumb_width"] = $dst_width;
            $new_image = $this->_imagedir . $this->_image["thumb_name"];
        } else {
            $this->_image["height"] = $dst_height;
            $this->_image["width"] = $dst_width;
            $new_image = $orig_image;
        }
    
        $dst_image = NULL;
        if($this->chkgd2())
            $dst_image = imagecreatetruecolor($dst_width,$dst_height);
        else
            $dst_image = imagecreate($dst_width,$dst_height);
        imagecopyresized(   $dst_image,
                            $src_image,
                            0, 0, 0, 0,
                            $dst_width,
                            $dst_height,
                            $src_width,
                            $src_height);

        switch($this->_image["type"]) {
            case "image/x-png":
            case "image/png":
                imagepng($dst_image, $new_image);
                break;
            case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
                imagejpeg($dst_image, $new_image, 100);
                break;
        }

        imagedestroy($src_image);
        imagedestroy($dst_image);

        return TRUE;
    }

    /**
    * Resizes image
    *
    * Checks image size against settings and resizes if needed
    *
    * @access public
    # @see add()
    */
    function resizeIfNeeded() {

        $src_height = $this->_image["height"];
        $src_width = $this->_image["width"];

        if($src_height > $_SESSION["SES_LISTING_MANAGER"]->_imageMaxHeight) {
            $ratio = $_SESSION["SES_LISTING_MANAGER"]->_imageHeightRedim / $src_height;
            $this->resize(FALSE, $ratio);
        } elseif($src_width > $_SESSION["SES_LISTING_MANAGER"]->_imageMaxWidth) {
            $ratio = $_SESSION["SES_LISTING_MANAGER"]->_imageWidthRedim / $src_width;
            $this->resize(FALSE, $ratio);
        } else {
            return;
        }
    }

    function checkFileSize($file) {
        $kb = 1024;
        $max = $_SESSION["SES_LISTING_MANAGER"]->_imageSizeLimit * $kb;
        //$file = $image["tmp_name"];
        $size = filesize($file);

        if($size > $max)
            return false;
        else
            return true;
    }


    function _error($type) {
        $title = "<font class=\"errortext\">" . $_SESSION["translate"]->it("ERROR!") . "</font>";

        switch($type) {
            case "delete_image_files":
                $content = $_SESSION["translate"]->it("Unable to delete image and thumbnail files.");
                break;
            case "file_too_large":
                $content = $_SESSION["translate"]->it("The image file is too large.") . "  " . $_SESSION["translate"]->it("The maximum file size allowed is") . " <b>" . $_SESSION["SES_LISTING_MANAGER"]->_imageSizeLimit . " kb.</b>";
                break;
            case "not_allowed_type":
                include(PHPWS_SOURCE_DIR . "mod/phpwslistings/conf/config.php");
                $content = $_SESSION["translate"]->it("The file you uploaded is not an allowed type on this server") . ": <b>" . $_FILES["LISTINGS_image"]["type"] . "</b><br />" .  $_SESSION["translate"]->it("The allowed types are") . ": <b>$allowedImageTypes</b>";
                break;
            case "save_failed":
                $content = $_SESSION["translate"]->it("There was a problem saving your image.") . " " . $_SESSION["translate"]->it("If the problem persists contact your system administrator.");
                break;

            case "update_failed":
                $content = $_SESSION["translate"]->it("There was a problem updating your image.") . " " . $_SESSION["translate"]->it("If the problem persists contact your system administrator.");
                break;

            case "image_upload":
                $content = "<b>" . $_SESSION["translate"]->it("There was a problem uploading the image you specified.  Check your permissions.") . "</b>";
                break;

            case "access_denied":
                $content = "<b>" . $_SESSION["translate"]->it("ACCESS DENIED!") . "</b>";
                break;

            case "gif2png_missing":
                include(PHPWS_SOURCE_DIR . "mod/phpwslistings/conf/config.php");
                $content = "<p><b>" . $_SESSION["translate"]->it("You are missing the gif2png program.") . "  ";
                $content .= $_SESSION["translate"]->it("Your configuration indicates it should be located at") . " " . $gif2png . ".  ";
                $content .= $_SESSION["translate"]->it("Either update your \$gif2png variable or have your system administrator install gif2png.") . "</p>";
                $content .= "<p>" . $_SESSION["translate"]->it("Until then, you will not be able to use gif images.  We suggest using png or jpeg images instead.") . "</b></p>";
                break;

        }

        $_SESSION["OBJ_layout"]->popbox($title, $content, NULL, "CNT_phpwslistings");
    }


    function chkgd2(){
        if(function_exists("gd_info")) {
            $gdver = gd_info();
            if(strstr($gdver["GD Version"], "1.") != FALSE) {
                return FALSE;
            } else {
                return TRUE;
            }
        } else {
            ob_start();
            phpinfo(8);
            $phpinfo=ob_get_contents();
            ob_end_clean();
            $phpinfo=strip_tags($phpinfo);
            $phpinfo=stristr($phpinfo,"gd version");
            $phpinfo=stristr($phpinfo,"version");
            $end=strpos($phpinfo," ");
            $phpinfo=substr($phpinfo,0,$end);
            $phpinfo=substr($phpinfo,7);
            if(version_compare("2.0", "$phpinfo")==1)
                return FALSE;
            else
                return TRUE;
        }
    }// END FUNC chkgd2()

}// END CLASS SEILER_ListingBASEImage

?>

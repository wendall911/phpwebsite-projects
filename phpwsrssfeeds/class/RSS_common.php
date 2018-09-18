<?php
/**
 *
 * Common functions for all RSS classes
 * @$Id: RSS_common.php,v 1.14 2005/03/15 16:20:38 wendall911 Exp $
 * @author Wendall Cada <wendall911@users.sourceforge.net>
 *
 */

class RSS_common {
    
	/**
	 * Return content for a display with a message and a continue button
	 *
	 * @param  string  $message 	The string to display above the continue button, already translated
	 * @param  string  $action 		The $request action to perform when Continue is pressed
	 * @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
     * Moved to RSS_common
	 */
	function _continueContent($message, $action, $request, $title = NULL) {
		$form = new EZform("RSS_CONTINUE");
		$form->add("module", "hidden", "phpwsrssfeeds");
		$form->add($request, "hidden", $action);
		$form->add("CONTINUE_BUTTON", "submit", $_SESSION["translate"]->it("Continue"));
		
		$tags = array();
		$tags = $form->getTemplate();
        if($title)
            $tags["TITLE"] = $title;
		$tags["MESSAGE"] = $_SESSION["translate"]->it($message);
		$content = PHPWS_Template::processTemplate($tags, "phpwsrssfeeds", "continue.tpl");

		return $content;
	
	} //END function _continueContent
    
    /**
     * Function called when attempt to access an item that doesn't exist or an 
     * error is returned.
     * $message mixed string or array containing error message or messages
     * $title string Contains title of error message.
     *
     */
    function _errorMessage($message,$title = NULL, $debug = FALSE, $module = NULL, $function = NULL) {
        if($debug == TRUE && !is_null($module) && !is_null($function)) {
            $tags['MODULE_LABEL'] = $_SESSION['translate']->it("Module");
            $tags['MODULE'] = $module;
            $tags['FUNCTION_LABEL'] = $_SESSION['translate']->it("Function");
            $tags['FUNCTION'] = $function;
            $tags['DATE'] = date(PHPWS_DATE_FORMAT . " "  . PHPWS_TIME_FORMAT, time());
        }
        if(is_array($message)){
            $messages = NULL;
            foreach($message as $item){
                $tags["MESSAGE"] = $item;
                $messages .= PHPWS_Template::processTemplate($tags, "phpwsrssfeeds", "error.tpl");
            }
            $tags["MESSAGE"] = $messages;
        } else {
            $tags["MESSAGE"] = $message;
        }
       if($title)
            $tags["TITLE"] = $title;
        $content = PHPWS_Template::processTemplate($tags, "phpwsrssfeeds", "error.tpl");
        return $content;
    }//end function _errorMessage()

    /**
     * Function called when a generic message needs returned
     *
     */
    function _message($message,$title = NULL) {
        if($title)
            $tags["TITLE"] = $title;
        $tags["MESSAGE"] = $message;
        $content = PHPWS_Template::processTemplate($tags, "phpwsrssfeeds", "message.tpl");
        return $content;
    }//end function _message()
    
    /**
     * Double encoding protection modified from note at 
     * php.net/manual/en/function.htmlspecialchars.php
     * Given a string, this function first strips out all encoded characters, then
     * encodes the string, safely returning an encoded string without double-encoding.
     *
     */
    function get_htmlentities($given, $quote_style = ENT_QUOTES){
        $return = RSS_common::smart_mdash_entities($given);
        return htmlentities(RSS_common::html_entity_decode($return, $quote_style), $quote_style);
    }//End function get_htmlentities()

    /**
     * Double encoding protection taken from php.net/manual/en/function.htmlspecialchars.php
     * Needed for older versions of PHP that do not have this function built-in.
     *
     */
    function html_entity_decode($html, $quote = ENT_QUOTES, $table = HTML_ENTITIES) {
        $trans_table = get_html_translation_table($table, $quote);
        if( isset($trans_table["'"]) && $trans_table["'"] != '&#039;' ) {
            $trans_table["'"] = '&#039;';
        }
        return strtr($html, array_flip($trans_table));
    }//End function html_entity_decode()

    /**
     * Fixes "smart quotes" and mdash chars that are missed by htmlentities()
     * These characters are commonly used in M$ word and cause problems. 
     * This function replaces the smart quotes with single quotes and emdash
     * with the appropriate html entities
     */
     
    function smart_mdash_entities($html) {
        $chars=array(chr(145),chr(146),chr(147),chr(148),chr(151));
        $replacements=array("'","'","'","'",'&mdash;');
        return str_replace($chars,$replacements,$html);
    }//End function smart_mdash_entities()

    /**
     * Removes bad html tags from html as well as checking for malformed xhtml
     * Derived from kses 0.2.0 (GPL) 
     * kses is an HTML/XHTML filter
     * Copyright (C) 2002, 2003  Ulf Harnhammar
     * E-mail:      metaur at users dot sourceforge dot net
     * Web page:    http://sourceforge.net/projects/kses
     * Using this function as a hack for html tidy. Just checking for basic 
     * mistakes made by users inputting html content. This is so I can still 
     * allow html content in feeds without breaking valid feeds.
     *
     * Allowing everything except tables and images for now. May put this into a
     * config file.
     *
     * @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
     * @param  string $text Text to be parsed
     * @return string Parsed text
     * @access public
     */
    function filterInput($string, $allowed_tags = NULL, $allowed_protocols = NULL){
        if(!isset($allowed_protocols))
            $allowed_protocols = array('http', 'https', 'ftp', 'news', 'nntp', 
              'telnet', 'gopher', 'mailto');
        if(!isset($allowed_tags))
            $allowed_tags = array('b' => array(),
              'i' => array(),
              'a' => array('href' => 1, 'title' => 1),
              'p' => array('align' => 1),
              'br' => array(),
              'u' => array(),
              'ul' => array(),
              'ol' => array(),
              'li' => array(),
              'dd' => array(),
              'dt' => array(),
              'dl' => array(),
              'div' => array('align' => 1),
              'span' => array('align' => 1),
              'blockquote' => array(),
              'pre' => array());
               
        //Returns a string with backslashes removed if magic slashes is on.
        $string = PHPWS_Text::stripslashes($string);
        //Remove NULL characters
        $string = RSS_common::rm_null($string);
        //Remove all javascript references
        $string = RSS_common::rm_js_entities($string);
        //Normalize the string
        $string = RSS_common::normalize_entities($string);
        return RSS_common::repairString($string, $allowed_tags, $allowed_protocols);
    }//End function filterInput()

    /*
    * This function searches for HTML tags, no matter how malformed. It also
    * matches stray ">" characters.
    */
    function repairString($string, $allowed_html, $allowed_protocols){
      return preg_replace('%(<'.'[^>]*'.'(>|$)'.'|>)%e',
        "RSS_common::trimTags('\\1', \$allowed_html, ".'$allowed_protocols)', $string);
    }//End function repairString()

    /*
    * This function does a lot of work. It rejects some very malformed things
    * like <:::>. It returns an empty string, if the element isn't allowed (look
    * ma, no strip_tags()!). Otherwise it splits the tag into an element and an
    * attribute list.
    */
    function trimTags($string, $allowed_html, $allowed_protocols){
        $string = PHPWS_Text::stripslashes($string);

        if (substr($string, 0, 1) != '<')
            return '&gt;';
        $matches = NULL;
        if (!preg_match('%^<\s*(/\s*)?([a-zA-Z0-9]+)([^>]*)>?$%', $string, $matches))
            return '';

        $slash = trim($matches[1]);
        $elem = $matches[2];
        $attrlist = $matches[3];

        if (!isset($allowed_html[strtolower($elem)]) || 
          !is_array($allowed_html[strtolower($elem)]))
            return '';

        return RSS_common::rm_attr("$slash$elem", $attrlist, $allowed_html,$allowed_protocols);
    }//End function trimTags()

    /*
     * This function removes all attributes, if none are allowed for this element.
     * If some are allowed it calls split_hair() to split them further, and then it
     * builds up new HTML code from the data that split_hair() returns. It also
     * removes "<" and ">" characters, if there are any left. One more thing it
     * does is to check if the tag has a closing XHTML slash, and if it does,
     * it puts one in the returned code as well.
     */
    function rm_attr($element, $attr, $allowed_html, $allowed_protocols){
        //Is there a closing XHTML slash at the end of the attributes?
        $xhtml_slash = '';
        if (preg_match('%\s/\s*$%', $attr))
            $xhtml_slash = ' /';
        if (strtolower($element) == 'br')
            $xhtml_slash = ' /';
        //Are any attributes allowed at all for this element?

        if (isset($allowed_html[strtolower($element)]) && 
          count($allowed_html[strtolower($element)]) == 0)
            return "<$element$xhtml_slash>";

        //Split it
        $attrarr = RSS_common::split_hair($attr, $allowed_protocols);

        /* Go through $attrarr, and save the allowed attributes for this element
         * in $attr2
         */
        $attr2 = '';

        foreach ($attrarr as $arreach){
            $current = '';
            if(isset($allowed_html[strtolower($element)]
              [strtolower($arreach['name'])]))
                $current = $allowed_html[strtolower($element)]
                  [strtolower($arreach['name'])];
            if ($current == '')
                continue;

            if (!is_array($current))
                $attr2 .= ' '.$arreach['whole'];
            else {
                $ok = true;
                foreach ($current as $currkey => $currval)
                    if (!RSS_common::check_attr_val($arreach['value'], $currkey, $currval)){ 
                        $ok = false; 
                        break; 
                    }

                if ($ok)
                    $attr2 .= ' '.$arreach['whole'];
            }
        }
        
        $attr2 = preg_replace('/[<>]/', '', $attr2);

        return "<$element$attr2$xhtml_slash>";
}//End function rm_attr()

    /*
     * This function does a lot of work. It parses an attribute list into an array
     * with attribute data, and tries to do the right thing even if it gets weird
     * input. It will add quotes around attribute values that don't have any quotes
     * or apostrophes around them, to make it easier to produce HTML code that will
     * conform to W3C's HTML specification. It will also remove bad URL protocols
     * from attribute values.
     */
    function split_hair($attr, $allowed_protocols){
        $attrarr = array();
        $mode = 0;
        $attrname = '';
        $match = NULL;
        while (strlen($attr) != 0){
            $working = 0;

            switch ($mode){
                case 0:
                    if (preg_match('/^([-a-zA-Z]+)/', $attr, $match)){
                        $attrname = $match[1];
                        $working = $mode = 1;
                        $attr = preg_replace('/^[-a-zA-Z]+/', '', $attr);
                    }
                    break;

                case 1:
                    if (preg_match('/^\s*=\s*/', $attr)){
                        $working = 1; $mode = 2;
                        $attr = preg_replace('/^\s*=\s*/', '', $attr);
                        break;
                    }

                    if (preg_match('/^\s+/', $attr)){
                        $working = 1; $mode = 0;
                        $attrarr[] = array
                        ('name'  => $attrname,
                         'value' => '',
                         'whole' => $attrname);
                        $attr = preg_replace('/^\s+/', '', $attr);
                    }
                    break;

                case 2:
                    if (preg_match('/^"([^"]*)"(\s+|$)/', $attr, $match)){
                        $thisval = RSS_common::bad_protocol($match[1], $allowed_protocols);
                        $attrarr[] = array
                        ('name'  => $attrname,
                         'value' => $thisval,
                         'whole' => "$attrname=\"$thisval\"");
                        $working = 1; $mode = 0;
                        $attr = preg_replace('/^"[^"]*"(\s+|$)/', '', $attr);
                        break;
                    }

                    if (preg_match("/^'([^']*)'(\s+|$)/", $attr, $match)){
                        $thisval = RSS_common::bad_protocol($match[1], $allowed_protocols);

                        $attrarr[] = array
                        ('name'  => $attrname,
                         'value' => $thisval,
                         'whole' => "$attrname='$thisval'");
                        $working = 1; $mode = 0;
                        $attr = preg_replace("/^'[^']*'(\s+|$)/", '', $attr);
                        break;
                    }

                    if (preg_match("%^([^\s\"']+)(\s+|$)%", $attr, $match)){
                        $thisval = RSS_common::bad_protocol($match[1], $allowed_protocols);

                        $attrarr[] = array
                        ('name'  => $attrname,
                         'value' => $thisval,
                         'whole' => "$attrname=\"$thisval\"");
                        $working = 1; $mode = 0;
                        $attr = preg_replace("%^[^\s\"']+(\s+|$)%", '', $attr);
                    }
                    break;
            }

            if ($working == 0){
                $attr = RSS_common::html_error($attr);
                $mode = 0;
            }
        }

        if ($mode == 1)
            $attrarr[] = array
              ('name'  => $attrname,
               'value' => '',
               'whole' => $attrname);

        return $attrarr;
    }//End function split_hair()

    /*
     * This function performs different checks for attribute values. The currently
     * implemented checks are "maxlen" and "maxval".
     */
    function check_attr_val($value, $checkname, $checkvalue){
        $ok = true;

        switch (strtolower($checkname)){
            case 'maxlen':
                /*
                  * The maxlen check makes sure that the attribute value has a length not
                  * greater than the given value. This can be used to avoid Buffer Overflows
                  * in WWW clients and various Internet servers.
                  */
        
                if (strlen($value) > $checkvalue)
                    $ok = false;
                break;

            case 'maxval':
                /*
                 * The maxval check does two things: it checks that the attribute value is
                 * an integer from 0 and up, without an excessive amount of zeroes or
                 * whitespace (to avoid Buffer Overflows). It also checks that the attribute
                 * value is not greater than the given value.
                 * This check can be used to avoid Denial of Service attacks.
                 */

                if (!preg_match('/^\s{0,6}[0-9]{1,6}\s{0,6}$/', $value))
                    $ok = false;
                if ($value > $checkvalue)
                    $ok = false;
                break;
        }

        return $ok;
    }//End function check_attr_val()
    
    function rm_null($string){
        $string = preg_replace('/\0+/', '', $string);
        $string = preg_replace('/(\\\\0)+/', '', $string);
        return $string;
    }//End function rm_null()
    
    /*
     * This function removes all non-allowed protocols from the beginning of
     * $string. It ignores whitespace and the case of the letters, and it does
     * understand HTML entities. It does its work in a while loop, so it won't be
     * fooled by a string like "javascript:javascript:alert(57)".
     */
     function bad_protocol($string, $allowed_protocols){
         $string = RSS_common::rm_null($string);
         $string2 = $string.'a';

         while ($string != $string2){
             $string2 = $string;
             $string = RSS_common::bad_protocol_once($string, $allowed_protocols);
         }

         return $string;
     }

    /*
     * This function removes the HTML JavaScript entities found in early versions of
     * Netscape 4.
     */
    function rm_js_entities($string){
        return preg_replace('%&\s*\{[^}]*(\}\s*;?|$)%', '', $string);
    }//End function rm_js_entities()

    /*
     * This function deals with parsing errors in kses_hair(). The general plan is
     * to remove everything to and including some whitespace, but it deals with
     * quotes and apostrophes as well.
     */
    function html_error($string){
        return preg_replace('/^("[^"]*("|$)|\'[^\']*(\'|$)|\S)*\s*/', '', $string);
    }//End function html_error()

    /*
     * This function searches for URL protocols at the beginning of $string, while
     * handling whitespace and HTML entities.
     */
    function bad_protocol_once($string, $allowed_protocols){
        $string = preg_replace('/^((&[^;]*;|[\sA-Za-z0-9])*)'.
          '(:|&#58;|&#[Xx]3[Aa];)\s*/e',
          'RSS_common::bad_protocol_onceAgain("\\1", $allowed_protocols)', $string);
        return $string;
    }//End function bad_protocol_once()

    /*
     * This function processes URL protocols, checks to see if they're in the white
     * list or not, and returns different data depending on the answer.
     */
    function bad_protocol_onceAgain($string, $allowed_protocols){
        $string2 = preg_replace('/\s/', '', $string);
        $string2 = RSS_common::decode_entities($string2);
        $string2 = RSS_common::rm_null($string2);
        $string2 = strtolower($string2);

        $allowed = false;
        foreach ($allowed_protocols as $one_protocol)
            if (strtolower($one_protocol) == $string2){
                $allowed = true;
                break;
            }

            if ($allowed)
                return "$string2:";
            else
                return '';
    }//End function bad_protocol_onceAgain()

    /*
     * This function normalizes HTML entities. It will convert "AT&T" to the correct
     * "AT&amp;T", "&#00058;" to "&#58;", "&#XYZZY;" to "&amp;#XYZZY;" and so on.
     */
    function normalize_entities($string){
        //Disarm all entities by converting & to &amp;

        $string = str_replace('&', '&amp;', $string);

        //Change back the allowed entities in our entity white list

        $string = preg_replace('/&amp;([A-Za-z][A-Za-z0-9]{0,19});/',
          '&\\1;', $string);
         $string = preg_replace('/&amp;#0*([0-9]{1,5});/e',
           'RSS_common::reduceNumEntities("\\1")', $string);
         $string = preg_replace('/&amp;#([Xx])0*(([0-9A-Fa-f]{2}){1,2});/',
           '&#\\1\\2;', $string);

         return $string;
    }//End function normalize_entities()

    /*
     * This function helps normalize_entities() to only accept 16 bit values
     * and nothing more for &#number; entities.
     */
    function reduceNumEntities($i){
        return (($i > 65535) ? "&amp;#$i;" : "&#$i;");
    }//End function reduceNumEntities()

    /*
     * This function decodes numeric HTML entities (&#65; and &#x41;). It doesn't
     * do anything with other entities like &auml;, but we don't need them in the
     * URL protocol white listing system anyway.
     */
    function decode_entities($string){
        $string = preg_replace('/&#([0-9]+);/e', 'chr("\\1")', $string);
        $string = preg_replace('/&#[Xx]([0-9A-Fa-f]+);/e', 'chr(hexdec("\\1"))',
          $string);

        return $string;
    }//End function decode_entities()
    
    /**
     * Shows the popwindow link in amy module that calls help
     *
     * @author Jeremy Agee <jagee@NOSPAM.tux.appstate.edu>
     * @modified Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
     * Removed requirement of little orange bubble
     * @param string module_name : name of the module you need the help item from
     * @param string label_name_id : This is the individual id the auther uses to access the help item
     * @return none
     */
    function show_link($module_name, $label_name_id){
        $sql = "select reg_id from mod_help_reg where mod_name = '$module_name' and active = '1';";    
        $row_reg = $GLOBALS["core"]->quickFetch($sql, TRUE);
        if($row_reg) {
            $sql = "select * from mod_help where reg_id = '".$row_reg["reg_id"]."' and label_name_id = '$label_name_id';";

            $row_h = $GLOBALS["core"]->quickFetch($sql, TRUE);
            if (!isset($row_h) || $row_h['label_name_id'] != $label_name_id){
                echo("show_link could not locate a help label '<b>$label_name_id</b>' for the '<b>$module_name</b>' module.");
                return NULL;
            }
            if($row_h["active"] == 1) {
                include(PHPWS_SOURCE_DIR."mod/".$module_name."/conf/help_config.php");
                if($_SESSION["OBJ_user"]->js_on) {
                    $window_array = array(
                      "type"=>"link",
				  "url"=>"./index.php?module=help&amp;help_op=show_help&amp;module_name=".$module_name."&amp;label_name_id=$label_name_id&amp;hreg_id=".$row_reg["reg_id"]."&amp;lay_quiet=1",
				  "label"=>"$help_graphic",
			  	  "window_title"=>"help",
				  "scrollbars"=>"yes",
				  "width"=>"400",
				  "height"=>"300",
				  "toolbar"=>"no"
				  );
                } else {
                    return "<a href=\"./index.php?module=help&amp;help_op=show_help&amp;module_name=".$module_name."&amp;label_name_id=".$label_name_id."&amp;hreg_id=".$row_reg["reg_id"]."&amp;lay_quiet=1\" target=\"_blank\">$help_graphic</a>";
                }
                $help_link = PHPWS_WizardBag::js_insert("window", NULL, NULL, NULL, $window_array);
                return $help_link;
            }
        }
    }//End function show_link()
    
    /**
     * Function taken from user comments on php.net
     * @param  string  $time
     * @modified Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
     */
    function iso8601_date($time) {
        $tzd = date('O',$time);
        $tzd = substr(chunk_split($tzd, 3, ':'),0,6);
        $date = date('Y-m-d\TH:i:s', $time) . $tzd;
        return $date; 
    }//End function iso8601_date()

    function cnvphpwsDate($date) {
        return RSS_common::iso8601_date(strtotime($date)-date('Z'));
    }//End function cnvphpwsDate()
    
    function cnvPubDate($date, $dayOnly=TRUE) {
        if($dayOnly)
            $date = str_replace('00:00:00', '12:12:12', $date);
        $timeTS = strtotime($date);
        $timeTS = mktime(12,12,12,date('m', $timeTS), date('d',$timeTS), date('Y', $timeTS));
        return $timeTS;
    }
    
    function cnvDcdate($date, $dayOnly=TRUE) {
      $date = explode('-', $date);      

      if($dayOnly || stristr($date[2], ':') === FALSE) {
	$timeTS = mktime(12,12,12, $date[1], $date[2], $date[0]);
      } else {
	$time = explode(':', $date[2]);
    $regs = NULL;
	eregi('([0-9]*)([a-z])([0-9]*)', $time[0], $regs);
	//hour stored in reg[3]
	//min stored in time[1]
	//sec stored in time[2]
	
	$timeTS = mktime($regs[3],$time[1],$time[2], $date[1], $date[2], $date[0]);
      }

      return $timeTS;
    }

    function getTS($date, $format, $dayOnly=TRUE) {
      if($format == 'pubdate') {
	return RSS_common::cnvPubDate($date, $dayOnly);
      } else if($format == 'dc:date') {
	return RSS_common::cnvDcdate($date, $dayOnly);
      }
    }

    /**
    * returns array of current feeds
    *
    * @author Wendall Cada <wendall911@NOSPAM.users.sourceforge.net>
    */
    function get_available_feeds() {
        $sql = "SELECT id,label FROM mod_phpwsrssfeeds_feeds WHERE hidden='0' && approved='1'";
        foreach($GLOBALS["core"]->getAssoc($sql, TRUE) as $key => $value)
            $feeds[$key] = PHPWS_Text::parseOutput($value);
        if (isset($feeds))
            return $feeds;
        else
            return FALSE;
    }//End function get_available_feeds()

    /**
     * get_modules_allowed
     *
     * listing of allowed modules
     *
     * @return array listing
     */
    function get_modules_allowed($type=NULL) {
        if (is_null($type))
            $type = 'Block';
        if (isset($_SESSION['PHPWSRSSFEEDS']['module_list']))
            $modulesAllowed = $_SESSION['PHPWSRSSFEEDS']['module_list'];
        if (!isset($modulesAllowed)) {
            $modulesAllowed = $GLOBALS['core']->listModules();
            $_SESSION['PHPWSRSSFEEDS']['module_list'] = $modulesAllowed;
        }

        $text = $_SESSION['translate']->it("View $type with Modules:");
        $options = array('label' => $text,
            'line' => '----------------------------------------------');
        array_push($options, 'home');
        $modulesAllowed = array_merge($options, $modulesAllowed);

        return $modulesAllowed;
    }
    //END function get_modules_allowed
    
    function canDisplay($modAllow, $pmAllow) {
      //Check to see if module allowed is current
      if (is_array($modAllow)) {
	$modules_allowed = RSS_common::get_modules_allowed();
	foreach($modAllow as $num) {
	  if (((isset($_REQUEST['module']) && ($_REQUEST['module'] == $modules_allowed[$num]))
	       || (!isset($_REQUEST['module']) && ("home" == $modules_allowed[$num]))))
	    return TRUE;
	}
      }
      
      //Check to see if pagemaster page allowed is current
      if (is_array($pmAllow)) {
	if (isset($_REQUEST['module']) && !$_REQUEST['module'] == 'pagemaster')
	    return TRUE;

	foreach($pmAllow as $num) {
	  if (isset($_REQUEST['PAGE_id']) && $_REQUEST['PAGE_id'] == $num)
	    return TRUE;
	  
	  if (isset($_SESSION["SES_PM_page"]) && is_object($_SESSION["SES_PM_page"]) && is_a($_SESSION["SES_PM_page"], "PHPWS_page") && $_SESSION["SES_PM_page"]->id == $num)
	    return TRUE;
	  
	}
      } else {
	return FALSE;
      }

      return FALSE;
    }

}//End class RSS_common
?>
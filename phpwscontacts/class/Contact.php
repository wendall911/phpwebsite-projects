<?php
/**
 * phpwsContacts
 *
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version     $Id: Contact.php,v 1.76 2004/09/29 21:02:12 rizzo Exp $
 */

/**
 * @since 0.5.5
 */
require_once(PHPWS_SOURCE_DIR . 'core/Item.php');
require_once(PHPWS_SOURCE_DIR . 'core/EZform.php');
require_once(PHPWS_SOURCE_DIR . 'core/Error.php');
require_once(PHPWS_SOURCE_DIR . 'core/Text.php');
require_once(PHPWS_SOURCE_DIR . 'core/WizardBag.php');
require_once(PHPWS_SOURCE_DIR . 'mod/help/class/CLS_help.php');
     
// Include PEAR objects
require_once('Date.php');
require_once('Contact_Vcard_Build.php');
require_once('Contact_Vcard_Parse.php');


/**
 * Contact object
 *
 * @package     phpwscontacts
 * @version     $Id: Contact.php,v 1.76 2004/09/29 21:02:12 rizzo Exp $
 * @author      Don Seiler <don@seiler.us>
 * @access      public
 */
class CONTACTS_Contact extends PHPWS_Item {

    /**
     * Image information for the contact
     *
     * @var      string
     * @access   private
     */
    var $_image = NULL;

    /**
     * The given (first) name of the contact
     *
     * @var      string
     * @access   private
     */
    var $_firstname = NULL;


    /**
     * The middle name of the contact
     *
     * @var      string
     * @access   private
     */
    var $_middlename = NULL;


    /**
     * The family (last) name of the contact
     *
     * @var      string
     * @access   private
     */
    var $_lastname = NULL;


    /**
     * The maiden name of the contact
     *
     * @var      string
     * @access   private
     */
    var $_maidenname = NULL;


    /**
     * The prefix (Dr. , Mr. , Fr. , etc) of the contact
     *
     * @var      string
     * @access   private
     */
    var $_prefix = NULL;


    /**
     * The suffix (Jr. , Sr. , III, M.D., PhD) of the contact
     *
     * @var      string
     * @access   private
     */
    var $_suffix = NULL;


    /**
     * The gender (M or F)
     *
     * @var      string
     * @access   private
     */
    var $_gender = NULL;


    /**
     * The email address of the contact
     *
     * @var      string
     * @access   private
     */
    var $_email = NULL;


    /**
     * Home phone number
     *
     * @var      string
     * @access   private
     */
    var $_phone_home = NULL;


    /**
     * Pager number
     *
     * @var      string
     * @access   private
     */
    var $_phone_pager = NULL;


    /**
     * Mobile phone number
     *
     * @var      string
     * @access   private
     */
    var $_phone_mobile = NULL;


    /**
     * Work phone number
     *
     * @var      string
     * @access   private
     */
    var $_phone_work = NULL;


    /**
     * Fax phone number
     *
     * @var      string
     * @access   private
     */
    var $_phone_fax = NULL;


    /**
     * Other phone number
     *
     * @var      string
     * @access   private
     */
    var $_phone_other = NULL;


    /**
     * Company name
     *
     * @var      string
     * @access   private
     */
    var $_company_name = NULL;


    /**
     * Contact's title/position within company
     *
     * @var      string
     * @access   private
     */
    var $_company_title = NULL;


    /**
     * Company street address
     *
     * @var      string
     * @access   private
     */
    var $_company_street = NULL;


    /**
     * Company city
     *
     * @var      string
     * @access   private
     */
    var $_company_city = NULL;


    /**
     * Company state
     *
     * @var      string
     * @access   private
     */
    var $_company_state = NULL;


    /**
     * Company zip/postal code
     *
     * @var      string
     * @access   private
     */
    var $_company_zip = NULL;


    /**
     * Company country
     *
     * @var      string
     * @access   private
     */
    var $_company_country = NULL;


    /**
     * Company website
     *
     * @var      string
     * @access   private
     */
    var $_company_website = NULL;


    /**
     * personal street address
     *
     * @var      string
     * @access   private
     */
    var $_personal_street = NULL;


    /**
     * personal city
     *
     * @var      string
     * @access   private
     */
    var $_personal_city = NULL;


    /**
     * personal state
     *
     * @var      string
     * @access   private
     */
    var $_personal_state = NULL;


    /**
     * personal zip/postal code
     *
     * @var      string
     * @access   private
     */
    var $_personal_zip = NULL;


    /**
     * personal country
     *
     * @var      string
     * @access   private
     */
    var $_personal_country = NULL;


    /**
     * personal website
     *
     * @var      string
     * @access   private
     */
    var $_personal_website = NULL;


    /**
     * The birthday of the contact
     *
     * @var      string
     * @access   private
     */
    var $_str_birthday = NULL;


    /**
     * The date of death of the contact
     *
     * @var      string
     * @access   private
     */
    var $_str_deathday = NULL;


    /**
     * The anniversary of the contact
     *
     * @var      string
     * @access   private
     */
    var $_str_anniversary = NULL;


    /**
     * Alternate Email addresses
     *
     * @var      string
     * @access   private
     */
    var $_altemail1 = NULL;
    var $_altemail2 = NULL;


    /**
     * Comments for the Contact
     *
     * @var      string
     * @access   private
     */
    var $_comments = NULL;

    /**
     * Contact visibility
     *
     * 0 = Public (default)
     * 1 = Protected
     * 2 = Private
     *
     * @var      int
     * @access   private
     */
    var $_visibility = NULL;

    /**
     * Flag if contact is info for owner
     *
     * @var      int
     * @access   private
     */
    var $_mine = 0;

    /**
     * Custom fields
     *
     * @var      string
     * @access   private
     */
    var $_custom1 = NULL;
    var $_custom2 = NULL;
    var $_custom3 = NULL;
    var $_custom4 = NULL;

    var $visibilities = array();
    var $genders = array();


    function CONTACTS_Contact($CONTACT_ID = NULL) {
        /* These vars are excluded on commit() */
        $exclude = array('visibilities',
                         'genders');
        $this->addExclude($exclude);
        $this->visibilities = array(
                                '0'=>$_SESSION['translate']->it('Public'),
                                '1'=>$_SESSION['translate']->it('Protected'),
                                '2'=>$_SESSION['translate']->it('Private')
                              );

        $this->genders = array(
                '',
                'M'=>$_SESSION['translate']->it('Male'),
                'F'=>$_SESSION['translate']->it('Female')
            );


        $this->setTable('mod_phpwscontacts_contacts');

        if (isset($CONTACT_ID)) {
            if (is_numeric($CONTACT_ID)) {
                $this->setId($CONTACT_ID);
                $this->init();
            } elseif (is_array($CONTACT_ID)) {
                $this->init($CONTACT_ID);
            }
        } else {
            if ($_REQUEST['CONTACTS_MAN_OP'] == 'edit') {
                // Find contact owned by this username
                $sql = "select id from " . $GLOBALS['core']->tbl_prefix . "mod_phpwscontacts_contacts where owner='" . $_SESSION['OBJ_user']->username . "' order by mine desc, created asc";
                $results = $GLOBALS['core']->getCol($sql);
                if (sizeof($results) > 0) {
                    $this->setId($results[0]);
                    $this->init();
                } else {
                    $this->setLabel($_SESSION['OBJ_user']->username);
                    $this->setOwner($_SESSION['OBJ_user']->username);

                    // Do I want to set this to 1?  Don't want to do this if user is admin who can own multiple contacts.
                    $this->_mine = 1;
                }
            }
        }
    }// END FUNC CONTACTS_Contact


    function _view() {
        $tags = array();

        if ($_SESSION['OBJ_fatcat']) {
            $cat = $_SESSION['OBJ_fatcat']->fatcatLinks($this->getId());
            if (isset($cat) && !empty($cat)) {
                $tags['CATEGORY_TEXT'] = $_SESSION['translate']->it('Category');
                $tags['CATEGORY'] = $cat;
            }

            // Not sure if we want this
            $_SESSION['OBJ_fatcat']->whatsRelated($this->getId());
        }

        if (!empty($this->_image)) {
            $tags['IMAGE'] = '<img src="images/phpwscontacts/' . $this->_image['name'] . '" alt="' . $this->getLabel() . '" border="0" />';
            if (isset($this->_image['thumb_name']))
                $tags['THUMBNAIL'] = '<img src="images/phpwscontacts/thumbs/' . $this->_image['thumb_name'] . '" alt="' . $this->getLabel() . '" border="0" />';
        }

        if (!empty($this->_visibility)) {
            $tags['VISIBILITY_TEXT'] = $_SESSION['translate']->it('Visbility');
            $tags['VISIBILITY'] = $this->visibilities[$this->_visibility];
        }

        if (!empty($this->_prefix)) {
            $tags['NAME_TEXT'] = $_SESSION['translate']->it('Name');
            $tags['PREFIX_TEXT'] = $_SESSION['translate']->it('Prefix');
            $tags['PREFIX'] = PHPWS_Text::parseOutput($this->_prefix);
        }
        if (!empty($this->_lastname)) {
            $tags['NAME_TEXT'] = $_SESSION['translate']->it('Name');
            $tags['LASTNAME'] = PHPWS_Text::parseOutput($this->_lastname);
            $tags['LASTNAME_TEXT'] = $_SESSION['translate']->it('Last');
        }
        if (!empty($this->_firstname)) {
            $tags['NAME_TEXT'] = $_SESSION['translate']->it('Name');
            $tags['FIRSTNAME_TEXT'] = $_SESSION['translate']->it('First');
            $tags['FIRSTNAME'] = PHPWS_Text::parseOutput($this->_firstname);
        }
        if (!empty($this->_middlename)) {
            $tags['NAME_TEXT'] = $_SESSION['translate']->it('Name');
            $tags['MIDDLENAME_TEXT'] = $_SESSION['translate']->it('Middle');
            $tags['MIDDLENAME'] = PHPWS_Text::parseOutput($this->_middlename);
        }
        if (!empty($this->_maidenname)) {
            $tags['NAME_TEXT'] = $_SESSION['translate']->it('Name');
            $tags['MAIDENNAME_TEXT'] = $_SESSION['translate']->it('Maiden');
            $tags['MAIDENNAME'] = PHPWS_Text::parseOutput($this->_maidenname);
        }
        if (!empty($this->_suffix)) {
            $tags['NAME_TEXT'] = $_SESSION['translate']->it('Name');
            $tags['SUFFIX_TEXT'] = $_SESSION['translate']->it('Suffix');
            $tags['SUFFIX'] = PHPWS_Text::parseOutput($this->_suffix);
        }

        if (!empty($this->_email)) {
            $tags['NAME_TEXT'] = $_SESSION['translate']->it('Name');
            $tags['EMAIL_TEXT'] = $_SESSION['translate']->it('Email');
            $tags['EMAIL'] = PHPWS_Text::parseOutput($this->_email);
        }

        if (!empty($this->_gender)) {
            $tags['NAME_TEXT'] = $_SESSION['translate']->it('Name');
            $tags['GENDER_TEXT'] = $_SESSION['translate']->it('Gender');
            $tags['GENDER'] = $this->genders[$this->_gender];
        }

        if (!empty($this->_phone_home)) {
            $tags['PHONE_HOME'] = PHPWS_Text::parseOutput($this->_phone_home);
            $tags['PHONE_HOME_TEXT'] = $_SESSION['translate']->it('Home');
            $tags['PHONE_TEXT'] = $_SESSION['translate']->it('Phone');
        }
        if (!empty($this->_phone_pager)) {
            $tags['PHONE_PAGER'] = PHPWS_Text::parseOutput($this->_phone_pager);
            $tags['PHONE_PAGER_TEXT'] = $_SESSION['translate']->it('Pager');
            $tags['PHONE_TEXT'] = $_SESSION['translate']->it('Phone');
        }
        if (!empty($this->_phone_mobile)) {
            $tags['PHONE_MOBILE'] = PHPWS_Text::parseOutput($this->_phone_mobile);
            $tags['PHONE_MOBILE_TEXT'] = $_SESSION['translate']->it('Mobile');
            $tags['PHONE_TEXT'] = $_SESSION['translate']->it('Phone');
        }
        if (!empty($this->_phone_work)) {
            $tags['PHONE_WORK'] = PHPWS_Text::parseOutput($this->_phone_work);
            $tags['PHONE_WORK_TEXT'] = $_SESSION['translate']->it('Work');
            $tags['PHONE_TEXT'] = $_SESSION['translate']->it('Phone');
        }
        if (!empty($this->_phone_fax)) {
            $tags['PHONE_FAX'] = PHPWS_Text::parseOutput($this->_phone_fax);
            $tags['PHONE_FAX_TEXT'] = $_SESSION['translate']->it('Fax');
            $tags['PHONE_TEXT'] = $_SESSION['translate']->it('Phone');
        }
        if (!empty($this->_phone_other)) {
            $tags['PHONE_OTHER'] = PHPWS_Text::parseOutput($this->_phone_other);
            $tags['PHONE_OTHER_TEXT'] = $_SESSION['translate']->it('Other');
            $tags['PHONE_TEXT'] = $_SESSION['translate']->it('Phone');
        }

        if (!empty($this->_company_name)) {
            $tags['COMPANY_TEXT'] = $_SESSION['translate']->it('Work');
            $tags['COMPANY_NAME'] = PHPWS_Text::parseOutput($this->_company_name);
            $tags['COMPANY_NAME_TEXT'] = $_SESSION['translate']->it('Company');
        }
        if (!empty($this->_company_title)) {
            $tags['COMPANY_TEXT'] = $_SESSION['translate']->it('Work');
            $tags['COMPANY_TITLE'] = PHPWS_Text::parseOutput($this->_company_title);
            $tags['COMPANY_TITLE_TEXT'] = $_SESSION['translate']->it('Title');
        }
        if (!empty($this->_company_street)) {
            $tags['COMPANY_TEXT'] = $_SESSION['translate']->it('Work');
            $tags['COMPANY_ADDRESS_TEXT'] = $_SESSION['translate']->it('Address');
            $tags['COMPANY_STREET'] = PHPWS_Text::parseOutput($this->_company_street);
            $tags['COMPANY_STREET_TEXT'] = $_SESSION['translate']->it('Street');
        }
        if (!empty($this->_company_city)) {
            $tags['COMPANY_TEXT'] = $_SESSION['translate']->it('Work');
            $tags['COMPANY_ADDRESS_TEXT'] = $_SESSION['translate']->it('Address');
            $tags['COMPANY_CITY'] = PHPWS_Text::parseOutput($this->_company_city);
            $tags['COMPANY_CITY_TEXT'] = $_SESSION['translate']->it('City');
        }
        if (!empty($this->_company_state)) {
            $tags['COMPANY_TEXT'] = $_SESSION['translate']->it('Work');
            $tags['COMPANY_ADDRESS_TEXT'] = $_SESSION['translate']->it('Address');
            $tags['COMPANY_STATE'] = PHPWS_Text::parseOutput($this->_company_state);
            $tags['COMPANY_STATE_TEXT'] = $_SESSION['translate']->it('State');
        }
        if (!empty($this->_company_zip)) {
            $tags['COMPANY_TEXT'] = $_SESSION['translate']->it('Work');
            $tags['COMPANY_ADDRESS_TEXT'] = $_SESSION['translate']->it('Address');
            $tags['COMPANY_ZIP'] = PHPWS_Text::parseOutput($this->_company_zip);
            $tags['COMPANY_ZIP_TEXT'] = $_SESSION['translate']->it('Zip/Postal');
        }
        if (!empty($this->_company_country)) {
            $tags['COMPANY_TEXT'] = $_SESSION['translate']->it('Work');
            $tags['COMPANY_ADDRESS_TEXT'] = $_SESSION['translate']->it('Address');
            $tags['COMPANY_COUNTRY'] = PHPWS_Text::parseOutput($this->_company_country);
            $tags['COMPANY_COUNTRY_TEXT'] = $_SESSION['translate']->it('Country');
        }
        if (!empty($this->_company_website)) {
            $tags['COMPANY_TEXT'] = $_SESSION['translate']->it('Work');
            $tags['COMPANY_WEBSITE'] = PHPWS_Text::parseOutput($this->_getWebsite($this->_company_website));
            $tags['COMPANY_WEBSITE_TEXT'] = $_SESSION['translate']->it('Website');
        }


        if (!empty($this->_personal_street)) {
            $tags['PERSONAL_TEXT'] = $_SESSION['translate']->it('Personal');
            $tags['PERSONAL_ADDRESS_TEXT'] = $_SESSION['translate']->it('Address');
            $tags['PERSONAL_STREET'] = PHPWS_Text::parseOutput($this->_personal_street);
            $tags['PERSONAL_STREET_TEXT'] = $_SESSION['translate']->it('Street');
        }
        if (!empty($this->_personal_city)) {
            $tags['PERSONAL_TEXT'] = $_SESSION['translate']->it('Personal');
            $tags['PERSONAL_ADDRESS_TEXT'] = $_SESSION['translate']->it('Address');
            $tags['PERSONAL_CITY'] = PHPWS_Text::parseOutput($this->_personal_city);
            $tags['PERSONAL_CITY_TEXT'] = $_SESSION['translate']->it('City');
        }
        if (!empty($this->_personal_state)) {
            $tags['PERSONAL_TEXT'] = $_SESSION['translate']->it('Personal');
            $tags['PERSONAL_ADDRESS_TEXT'] = $_SESSION['translate']->it('Address');
            $tags['PERSONAL_STATE'] = PHPWS_Text::parseOutput($this->_personal_state);
            $tags['PERSONAL_STATE_TEXT'] = $_SESSION['translate']->it('State');
        }
        if (!empty($this->_personal_zip)) {
            $tags['PERSONAL_TEXT'] = $_SESSION['translate']->it('Personal');
            $tags['PERSONAL_ADDRESS_TEXT'] = $_SESSION['translate']->it('Address');
            $tags['PERSONAL_ZIP'] = PHPWS_Text::parseOutput($this->_personal_zip);
            $tags['PERSONAL_ZIP_TEXT'] = $_SESSION['translate']->it('Zip/Postal');
        }
        if (!empty($this->_personal_country)) {
            $tags['PERSONAL_TEXT'] = $_SESSION['translate']->it('Personal');
            $tags['PERSONAL_ADDRESS_TEXT'] = $_SESSION['translate']->it('Address');
            $tags['PERSONAL_COUNTRY'] = PHPWS_Text::parseOutput($this->_personal_country);
            $tags['PERSONAL_COUNTRY_TEXT'] = $_SESSION['translate']->it('Country');
        }
        if (!empty($this->_personal_website)) {
            $tags['PERSONAL_TEXT'] = $_SESSION['translate']->it('Personal');
            $tags['PERSONAL_WEBSITE'] = PHPWS_Text::parseOutput($this->_getWebsite($this->_personal_website));
            $tags['PERSONAL_WEBSITE_TEXT'] = $_SESSION['translate']->it('Website');
        }


        if (!empty($this->_str_birthday)) {
            $birthday = new Date($this->_str_birthday);
            $tags['PERSONAL_TEXT'] = $_SESSION['translate']->it('Personal');
            $tags['BIRTHDAY'] = $birthday->format('%B %d, %Y');
            $tags['BIRTHDAY_TEXT'] = $_SESSION['translate']->it('Born');
        }

        if (!empty($this->_str_deathday)) {
            $birthday = new Date($this->_str_deathday);
            $tags['PERSONAL_TEXT'] = $_SESSION['translate']->it('Personal');
            $tags['DEATHDAY'] = $birthday->format('%B %d, %Y');
            $tags['DEATHDAY_TEXT'] = $_SESSION['translate']->it('Died');
            $tags['DECEASED'] = $_SESSION['translate']->it('Deceased');
        }

        if (!empty($this->_str_anniversary)) {
            $anniversary = new Date($this->_str_anniversary);
            $tags['PERSONAL_TEXT'] = $_SESSION['translate']->it('Personal');
            $tags['ANNIVERSARY'] = $anniversary->format('%B %d, %Y');
            $tags['ANNIVERSARY_TEXT'] = $_SESSION['translate']->it('Anniversary');
        }

        if (!empty($this->_altemail1)) {
            $tags['PERSONAL_TEXT'] = $_SESSION['translate']->it('Personal');
            $tags['ALTEMAIL1'] = PHPWS_Text::parseOutput($this->_altemail1);
            $tags['ALTEMAIL1_TEXT'] = $_SESSION['translate']->it('Alt Email 1');
        }

        if (!empty($this->_altemail2)) {
            $tags['PERSONAL_TEXT'] = $_SESSION['translate']->it('Personal');
            $tags['ALTEMAIL2'] = PHPWS_Text::parseOutput($this->_altemail2);
            $tags['ALTEMAIL2_TEXT'] = $_SESSION['translate']->it('Alt Email 2');
        }

        if (!empty($this->_comments)) {
            $tags['PERSONAL_TEXT'] = $_SESSION['translate']->it('Personal');
            $tags['COMMENTS_TEXT'] = $_SESSION['translate']->it('Comments');
            $tags['COMMENTS'] = PHPWS_Text::parseOutput($this->_comments);
        }

        if (!empty($this->_custom1)) {
            $tags['CUSTOM1_TEXT'] = $_SESSION['CONTACTS_Manager']->_custom1label;
            $tags['CUSTOM1'] = PHPWS_Text::parseOutput($this->_custom1);
        }

        if (!empty($this->_custom2)) {
            $tags['CUSTOM2_TEXT'] = $_SESSION['CONTACTS_Manager']->_custom2label;
            $tags['CUSTOM2'] = PHPWS_Text::parseOutput($this->_custom2);
        }

        if (!empty($this->_custom3)) {
            $tags['CUSTOM3_TEXT'] = $_SESSION['CONTACTS_Manager']->_custom3label;
            $tags['CUSTOM3'] = PHPWS_Text::parseOutput($this->_custom3);
        }

        if (!empty($this->_custom4)) {
            $tags['CUSTOM4_TEXT'] = $_SESSION['CONTACTS_Manager']->_custom4label;
            $tags['CUSTOM4'] = PHPWS_Text::parseOutput($this->_custom4);
        }

        $tags['VCARD'] = '<a href="index.php?module=phpwscontacts&amp;CONTACTS_Contact_OP=vcard">' . $_SESSION['translate']->it('Get vCard') . '</a>';

        if (($_SESSION['OBJ_user']->username == $this->getOwner()) || $_SESSION['OBJ_user']->allow_access('phpwscontacts','edit_contacts'))
            $tags['EDIT'] = '<a href="index.php?module=phpwscontacts&amp;CONTACTS_MAN_OP=edit&amp;PHPWS_MAN_ITEMS[]=' . $this->getId() . '">' . $_SESSION['translate']->it('Edit') . '</a>';

        return PHPWS_Template::processTemplate($tags, 'phpwscontacts', 'view.tpl');
    }// END FUNC _view

    
    function _edit() {
        if (($_SESSION['OBJ_user']->username != $this->getOwner()) && !$_SESSION['OBJ_user']->allow_access('phpwscontacts','edit_contacts')) {
            $message = $_SESSION['translate']->it('Access to edit contact was denied due to lack of proper permissions.');
            $error = new PHPWS_Error('phpwscontacts', 'CONTACTS_Contact::_edit()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }


        /* Create form */
        $form = new EZform('CONTACTS_Contact_edit');
        $tabs = 1;


        /* Contact Prefix */
        $form->add('Contact_prefix', 'text', $this->_prefix);
        $form->setSize('Contact_prefix', 5);
        $form->setTab('Contact_prefix', $tabs);
        $tabs++;

        /* Contact Given Name */
        $form->add('Contact_firstname', 'text', $this->_firstname);
        $form->setSize('Contact_firstname', 20);
        $form->setTab('Contact_firstname', $tabs);
        $tabs++;

        /* Contact Middle Name */
        $form->add('Contact_middlename', 'text', $this->_middlename);
        $form->setSize('Contact_middlename', 15);
        $form->setTab('Contact_middlename', $tabs);
        $tabs++;

        /* Contact Family Name */
        $form->add('Contact_lastname', 'text', $this->_lastname);
        $form->setSize('Contact_lastname', 25);
        $form->setTab('Contact_lastname', $tabs);
        $tabs++;

        /* Contact Maiden Name */
        $form->add('Contact_maidenname', 'text', $this->_maidenname);
        $form->setSize('Contact_maidenname', 25);
        $form->setTab('Contact_maidenname', $tabs);
        $tabs++;

        /* Contact Suffix */
        $form->add('Contact_suffix', 'text', $this->_suffix);
        $form->setSize('Contact_suffix', 5);
        $form->setTab('Contact_suffix', $tabs);
        $tabs++;

        if ($_SESSION['OBJ_user']->allow_access('phpwscontacts','edit_contacts')) {
            $users = array();
            $sql = 'SELECT username FROM ' . $GLOBALS['core']->tbl_prefix . 'mod_users order by username';
            $result = $GLOBALS['core']->query($sql);

            if ($result) {
                while($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
                    $users[$row['username']] = $row['username'];
                }
            }
            $form->add('Contact_owner', 'select', $users);
            $form->setMatch('Contact_owner', $this->getOwner());
            $form->setTab('Contact_owner', $tabs);
            $tabs++;
        }

        /* Contact Gender */
        $form->add('Contact_gender', 'select', $this->genders);
        $form->setMatch('Contact_gender', $this->_gender);
        $form->setTab('Contact_gender', $tabs);
        $tabs++;

        /* Contact Email */
        $form->add('Contact_email', 'text', $this->_email);
        $form->setSize('Contact_email', 33);
        $form->setTab('Contact_email', $tabs);
        $tabs++;

        /* Home Phone */
        $form->add('Contact_phone_home', 'text', $this->_phone_home);
        $form->setSize('Contact_phone_home', 20);
        $form->setTab('Contact_phone_home', $tabs);
        $tabs++;

        /* Pager Phone */
        $form->add('Contact_phone_pager', 'text', $this->_phone_pager);
        $form->setSize('Contact_phone_pager', 20);
        $form->setTab('Contact_phone_pager', $tabs);
        $tabs++;

        /* Mobile Phone */
        $form->add('Contact_phone_mobile', 'text', $this->_phone_mobile);
        $form->setSize('Contact_phone_mobile', 20);
        $form->setTab('Contact_phone_mobile', $tabs);
        $tabs++;

        /* Work Phone */
        $form->add('Contact_phone_work', 'text', $this->_phone_work);
        $form->setSize('Contact_phone_work', 20);
        $form->setTab('Contact_phone_work', $tabs);
        $tabs++;

        /* Fax Phone */
        $form->add('Contact_phone_fax', 'text', $this->_phone_fax);
        $form->setSize('Contact_phone_fax', 20);
        $form->setTab('Contact_phone_fax', $tabs);
        $tabs++;

        /* Other Phone */
        $form->add('Contact_phone_other', 'text', $this->_phone_other);
        $form->setSize('Contact_phone_other', 20);
        $form->setTab('Contact_phone_other', $tabs);
        $tabs++;

        /* Company Name */
        $form->add('Contact_company_name', 'text', $this->_company_name);
        $form->setSize('Contact_company_name', 30);
        $form->setTab('Contact_company_name', $tabs);
        $tabs++;

        /* Company Title/Position */
        $form->add('Contact_company_title', 'text', $this->_company_title);
        $form->setSize('Contact_company_title', 20);
        $form->setTab('Contact_company_title', $tabs);
        $tabs++;

        /* Company Address */
        $form->add('Contact_company_street', 'textarea', $this->_company_street);
        $form->setRows('Contact_company_street', 2);
        $form->setCols('Contact_company_street', 50);
        $form->setTab('Contact_company_street', $tabs);
        $tabs++;

        /* Company City */
        $form->add('Contact_company_city', 'text', $this->_company_city);
        $form->setSize('Contact_company_city', 20);
        $form->setTab('Contact_company_city', $tabs);
        $tabs++;

        /* Company State */
        $form->add('Contact_company_state', 'text', $this->_company_state);
        $form->setSize('Contact_company_state', 10);
        $form->setTab('Contact_company_state', $tabs);
        $tabs++;

        /* Company Zip/Postal Code */
        $form->add('Contact_company_zip', 'text', $this->_company_zip);
        $form->setSize('Contact_company_zip', 10);
        $form->setTab('Contact_company_zip', $tabs);
        $tabs++;

        /* Company Country */
        $form->add('Contact_company_country', 'text', $this->_company_country);
        $form->setSize('Contact_company_country', 20);
        $form->setTab('Contact_company_country', $tabs);
        $tabs++;

        /* Company Website */
        $form->add('Contact_company_website', 'text', $this->_getWebsite($this->_company_website));
        $form->setSize('Contact_company_website', 40);
        $form->setTab('Contact_company_website', $tabs);
        $tabs++;

        /* Personal Address */
        $form->add('Contact_personal_street', 'textarea', $this->_personal_street);
        $form->setRows('Contact_personal_street', 2);
        $form->setCols('Contact_personal_street', 50);
        $form->setTab('Contact_personal_street', $tabs);
        $tabs++;

        /* Personal City */
        $form->add('Contact_personal_city', 'text', $this->_personal_city);
        $form->setSize('Contact_personal_city', 20);
        $form->setTab('Contact_personal_city', $tabs);
        $tabs++;

        /* Personal State */
        $form->add('Contact_personal_state', 'text', $this->_personal_state);
        $form->setSize('Contact_personal_state', 10);
        $form->setTab('Contact_personal_state', $tabs);
        $tabs++;

        /* Personal Zip/Postal Code */
        $form->add('Contact_personal_zip', 'text', $this->_personal_zip);
        $form->setSize('Contact_personal_zip', 10);
        $form->setTab('Contact_personal_zip', $tabs);
        $tabs++;

        /* Personal Country */
        $form->add('Contact_personal_country', 'text', $this->_personal_country);
        $form->setSize('Contact_personal_country', 20);
        $form->setTab('Contact_personal_country', $tabs);
        $tabs++;

        /* Personal Website */
        $form->add('Contact_personal_website', 'text', $this->_getWebsite($this->_personal_website));
        $form->setSize('Contact_personal_website', 40);
        $form->setTab('Contact_personal_website', $tabs);
        $tabs++;

        /* Contact Birthday */
        if (empty($this->_str_birthday))
            $this->_str_birthday = NULL;
        $form->dateForm('Contact_birthday', $this->_str_birthday, 1900, date('Y'), TRUE, TRUE);
        $tabs++;

        /* Contact Date of Death */
        if (empty($this->_str_deathday))
            $this->_str_deathday = NULL;
        $form->dateForm('Contact_deathday', $this->_str_deathday, 1900, date('Y'), TRUE, TRUE);
        $tabs++;

        /* Contact Anniversary */
        if (empty($this->_str_anniversary))
            $this->_str_anniversary = NULL;
        $form->dateForm('Contact_anniversary', $this->_str_anniversary, 1900, date('Y'), TRUE, TRUE);
        $tabs++;

        /* Alternate Email 1 */
        $form->add('Contact_altemail1', 'text', $this->_altemail1);
        $form->setSize('Contact_altemail1', 20);
        $form->setTab('Contact_altemail1', $tabs);
        $tabs++;

        /* Alternate Email 2 */
        $form->add('Contact_altemail2', 'text', $this->_altemail2);
        $form->setSize('Contact_altemail2', 20);
        $form->setTab('Contact_altemail2', $tabs);
        $tabs++;

        /* Custom Field 1 */
        $form->add('Contact_custom1', 'text', $this->_custom1);
        $form->setSize('Contact_custom1', 20);
        $form->setTab('Contact_custom1', $tabs);
        $tabs++;

        /* Custom Field 2 */
        $form->add('Contact_custom2', 'text', $this->_custom2);
        $form->setSize('Contact_custom2', 20);
        $form->setTab('Contact_custom2', $tabs);
        $tabs++;

        /* Custom Field 3 */
        $form->add('Contact_custom3', 'text', $this->_custom3);
        $form->setSize('Contact_custom3', 20);
        $form->setTab('Contact_custom3', $tabs);
        $tabs++;

        /* Custom Field 4 */
        $form->add('Contact_custom4', 'text', $this->_custom4);
        $form->setSize('Contact_custom4', 20);
        $form->setTab('Contact_custom4', $tabs);
        $tabs++;

        /* Image */
        $form->imageForm('Contact_image', $GLOBALS['core']->home_dir . 'images/phpwscontacts/', $this->_image['name']);

        /* Comments */
        $form->add('Contact_comments', 'textarea', $this->_comments);
        $form->setRows('Contact_comments', 5);
        $form->setCols('Contact_comments', 40);
        $form->setTab('Contact_comments', $tabs);
        $tabs++;

        /* Visibility */
        $form->add('Contact_visibility', 'select', $this->visibilities);
        $form->setMatch('Contact_visibility', $this->_visibility);
        $form->setTab('Contact_visibility', $tabs);
        $tabs++;

        /* Mine */
        $form->add('Contact_mine', 'checkbox');
        $form->setMatch('Contact_mine', $this->_mine);
        $form->setTab('Contact_mine', $tabs);
        $tabs++;

        /* Save Button */
        $form->add('Contact_save', 'submit', $_SESSION['translate']->it('Save'));
        $form->setTab('Contact_save', $tabs);

        /* Module Information */
        $form->add('module', 'hidden', 'phpwscontacts');
        $form->add('CONTACTS_Contact_OP', 'hidden', 'save');

        $tags = array();
        $tags = $form->getTemplate();

        /* FatCat Category */
        if ($_SESSION['OBJ_fatcat']) {
            $tags['CATEGORIES_TEXT'] = $_SESSION['translate']->it('Category');
            $tags['CATEGORIES'] = $_SESSION['OBJ_fatcat']->showSelect($this->getId(), 'multiple', NULL, 'phpwscontacts', NULL, FALSE);
        }

        $tags['PRIMARY_TEXT'] = $_SESSION['translate']->it('Primary Information');
        $tags['NAME_TEXT'] = $_SESSION['translate']->it('Name');
        $tags['FIRSTNAME_TEXT'] = $_SESSION['translate']->it('First');
        $tags['MIDDLENAME_TEXT'] = $_SESSION['translate']->it('Middle');
        $tags['LASTNAME_TEXT'] = $_SESSION['translate']->it('Last');
        $tags['MAIDENNAME_TEXT'] = $_SESSION['translate']->it('Maiden');
        $tags['PREFIX_TEXT'] = $_SESSION['translate']->it('Prefix');
        $tags['SUFFIX_TEXT'] = $_SESSION['translate']->it('Suffix');
        $tags['EMAIL_TEXT'] = $_SESSION['translate']->it('Email');
        $tags['GENDER_TEXT'] = $_SESSION['translate']->it('Gender');
        if ($_SESSION['OBJ_user']->allow_access('phpwscontacts','edit_contacts'))
            $tags['OWNER_TEXT'] = $_SESSION['translate']->it('Owner');

        $tags['PHONE_TEXT'] = $_SESSION['translate']->it('Phone Numbers');
        $tags['PHONE_HOME_TEXT'] = $_SESSION['translate']->it('Home');
        $tags['PHONE_MOBILE_TEXT'] = $_SESSION['translate']->it('Mobile');
        $tags['PHONE_PAGER_TEXT'] = $_SESSION['translate']->it('Pager');
        $tags['PHONE_WORK_TEXT'] = $_SESSION['translate']->it('Work');
        $tags['PHONE_FAX_TEXT'] = $_SESSION['translate']->it('Fax');
        $tags['PHONE_OTHER_TEXT'] = $_SESSION['translate']->it('Other');

        $tags['COMPANY_TEXT'] = $_SESSION['translate']->it('Work Information');
        $tags['COMPANY_NAME_TEXT'] = $_SESSION['translate']->it('Company');
        $tags['COMPANY_TITLE_TEXT'] = $_SESSION['translate']->it('Title');
        $tags['COMPANY_ADDRESS_TEXT'] = $_SESSION['translate']->it('Address');
        $tags['COMPANY_STREET_TEXT'] = $_SESSION['translate']->it('Street');
        $tags['COMPANY_CITY_TEXT'] = $_SESSION['translate']->it('City');
        $tags['COMPANY_STATE_TEXT'] = $_SESSION['translate']->it('State');
        $tags['COMPANY_ZIP_TEXT'] = $_SESSION['translate']->it('Zip/Postal');
        $tags['COMPANY_COUNTRY_TEXT'] = $_SESSION['translate']->it('Country');
        $tags['COMPANY_WEBSITE_TEXT'] = $_SESSION['translate']->it('Website');

        $tags['PERSONAL_TEXT'] = $_SESSION['translate']->it('Personal Information');
        $tags['PERSONAL_ADDRESS_TEXT'] = $_SESSION['translate']->it('Address');
        $tags['PERSONAL_STREET_TEXT'] = $_SESSION['translate']->it('Street');
        $tags['PERSONAL_CITY_TEXT'] = $_SESSION['translate']->it('City');
        $tags['PERSONAL_STATE_TEXT'] = $_SESSION['translate']->it('State');
        $tags['PERSONAL_ZIP_TEXT'] = $_SESSION['translate']->it('Zip/Postal');
        $tags['PERSONAL_COUNTRY_TEXT'] = $_SESSION['translate']->it('Country');
        $tags['PERSONAL_WEBSITE_TEXT'] = $_SESSION['translate']->it('Website');

        $tags['BIRTHDAY_TEXT'] = $_SESSION['translate']->it('Born');
        $tags['DEATHDAY_TEXT'] = $_SESSION['translate']->it('Died');
        $tags['ANNIVERSARY_TEXT'] = $_SESSION['translate']->it('Anniversary');

        $tags['ALTEMAIL1_TEXT'] = $_SESSION['translate']->it('Alt Email 1');
        $tags['ALTEMAIL2_TEXT'] = $_SESSION['translate']->it('Alt Email 2');
        $tags['COMMENTS_TEXT'] = $_SESSION['translate']->it('Comments');
        $tags['VISIBILITY_TEXT'] = $_SESSION['translate']->it('Visibility');
        $tags['VISIBILITY_HELP'] = CLS_help::show_link('phpwscontacts', 'visibility');
        $tags['MINE_TEXT'] = $_SESSION['translate']->it('My Contact');
        $tags['MINE_HELP'] = CLS_help::show_link('phpwscontacts', 'mine');

        $tags['IMAGE_TEXT'] = $_SESSION['translate']->it('Image');
        
        if (empty($_SESSION['CONTACTS_Manager']->_custom1label))
            $tags['CONTACT_CUSTOM1'] = NULL;
        else
            $tags['CUSTOM1_TEXT'] = $_SESSION['CONTACTS_Manager']->_custom1label;

        if (empty($_SESSION['CONTACTS_Manager']->_custom2label))
            $tags['CONTACT_CUSTOM2'] = NULL;
        else
            $tags['CUSTOM2_TEXT'] = $_SESSION['CONTACTS_Manager']->_custom2label;

        if (empty($_SESSION['CONTACTS_Manager']->_custom3label))
            $tags['CONTACT_CUSTOM3'] = NULL;
        else
            $tags['CUSTOM3_TEXT'] = $_SESSION['CONTACTS_Manager']->_custom3label;
            
        if (empty($_SESSION['CONTACTS_Manager']->_custom4label))
            $tags['CONTACT_CUSTOM4'] = NULL;
        else
            $tags['CUSTOM4_TEXT'] = $_SESSION['CONTACTS_Manager']->_custom4label;

        return PHPWS_Template::processTemplate($tags, 'phpwscontacts', 'edit.tpl');

    }// END FUNC _edit


    function _save() {
        if (($_SESSION['OBJ_user']->username != $this->getOwner()) && !$_SESSION['OBJ_user']->allow_access('phpwscontacts','edit_contacts')) {
            $message = $_SESSION['translate']->it('Access to save contact was denied due to lack of proper permissions.');
            $error = new PHPWS_Error('phpwscontacts', 'CONTACTS_Contact::_save()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        if (isset($_REQUEST['Contact_lastname']))
            $this->_lastname = PHPWS_Text::parseInput($_REQUEST['Contact_lastname']);
        if (isset($_REQUEST['Contact_middlename']))
            $this->_middlename = PHPWS_Text::parseInput($_REQUEST['Contact_middlename']);
        else
            $this->_middlename = "";

        if (isset($_REQUEST['Contact_firstname']))
            $this->_firstname = PHPWS_Text::parseInput($_REQUEST['Contact_firstname']);
        if (isset($_REQUEST['Contact_maidenname']))
            $this->_maidenname = PHPWS_Text::parseInput($_REQUEST['Contact_maidenname']);
        else
            $this->_maidenname = "";

        $label = $this->_lastname . ', ' . $this->_firstname . ' ' . $this->_middlename;
        if (isset($this->_maidenname) && !empty($this->_maidenname))
            $label .= ' (' . $this->_maidenname . ')';
        $this->setLabel($label);


        if (isset($_REQUEST['Contact_prefix']))
            $this->_prefix = PHPWS_Text::parseInput($_REQUEST['Contact_prefix']);
        if (isset($_REQUEST['Contact_suffix']))
            $this->_suffix = PHPWS_Text::parseInput($_REQUEST['Contact_suffix']);

        if (isset($_REQUEST['Contact_email']))
            $this->_email = PHPWS_Text::parseInput($_REQUEST['Contact_email']);

        /* May need to set to NULL if '' */
        if (isset($_REQUEST['Contact_gender']))
            $this->_gender = $_REQUEST['Contact_gender'];

        if (isset($_REQUEST['Contact_owner'])) 
            $this->_owner = $_REQUEST['Contact_owner'];

        if (isset($_REQUEST['Contact_phone_home']))
            $this->_phone_home = PHPWS_Text::parseInput($_REQUEST['Contact_phone_home']);
        if (isset($_REQUEST['Contact_phone_pager']))
            $this->_phone_pager = PHPWS_Text::parseInput($_REQUEST['Contact_phone_pager']);
        if (isset($_REQUEST['Contact_phone_mobile']))
            $this->_phone_mobile = PHPWS_Text::parseInput($_REQUEST['Contact_phone_mobile']);
        if (isset($_REQUEST['Contact_phone_work']))
            $this->_phone_work = PHPWS_Text::parseInput($_REQUEST['Contact_phone_work']);
        if (isset($_REQUEST['Contact_phone_fax']))
            $this->_phone_fax = PHPWS_Text::parseInput($_REQUEST['Contact_phone_fax']);
        if (isset($_REQUEST['Contact_phone_other']))
            $this->_phone_other = PHPWS_Text::parseInput($_REQUEST['Contact_phone_other']);

        if (isset($_REQUEST['Contact_company_name']))
            $this->_company_name = PHPWS_Text::parseInput($_REQUEST['Contact_company_name']);
        if (isset($_REQUEST['Contact_company_title']))
            $this->_company_title = PHPWS_Text::parseInput($_REQUEST['Contact_company_title']);
        if (isset($_REQUEST['Contact_company_street']))
            $this->_company_street = PHPWS_Text::parseInput($_REQUEST['Contact_company_street']);
        if (isset($_REQUEST['Contact_company_city']))
            $this->_company_city = PHPWS_Text::parseInput($_REQUEST['Contact_company_city']);
        if (isset($_REQUEST['Contact_company_state']))
            $this->_company_state = PHPWS_Text::parseInput($_REQUEST['Contact_company_state']);
        if (isset($_REQUEST['Contact_company_zip']))
            $this->_company_zip = PHPWS_Text::parseInput($_REQUEST['Contact_company_zip']);
        if (isset($_REQUEST['Contact_company_country']))
            $this->_company_country = PHPWS_Text::parseInput($_REQUEST['Contact_company_country']);
        if (isset($_REQUEST['Contact_company_website']))
            $this->_company_website = $this->_getWebsite(PHPWS_Text::parseInput($_REQUEST['Contact_company_website']));

        if (isset($_REQUEST['Contact_personal_street']))
            $this->_personal_street = PHPWS_Text::parseInput($_REQUEST['Contact_personal_street']);
        if (isset($_REQUEST['Contact_personal_city']))
            $this->_personal_city = PHPWS_Text::parseInput($_REQUEST['Contact_personal_city']);
        if (isset($_REQUEST['Contact_personal_state']))
            $this->_personal_state = PHPWS_Text::parseInput($_REQUEST['Contact_personal_state']);
        if (isset($_REQUEST['Contact_personal_zip']))
            $this->_personal_zip = PHPWS_Text::parseInput($_REQUEST['Contact_personal_zip']);
        if (isset($_REQUEST['Contact_personal_country']))
            $this->_personal_country = PHPWS_Text::parseInput($_REQUEST['Contact_personal_country']);
        if (isset($_REQUEST['Contact_personal_website']))
            $this->_personal_website = $this->_getWebsite(PHPWS_Text::parseInput($_REQUEST['Contact_personal_website']));

        if (isset($_REQUEST['Contact_birthday_YEAR']) && !empty($_REQUEST['Contact_birthday_YEAR'])) {
            $year = PHPWS_Text::parseInput($_REQUEST['Contact_birthday_YEAR']);
            $month = PHPWS_Text::parseInput($_REQUEST['Contact_birthday_MONTH']);
            $day = PHPWS_Text::parseInput($_REQUEST['Contact_birthday_DAY']);
            $this->_str_birthday = sprintf('%04d', $year) . sprintf('%02d', $month) . sprintf('%02d', $day) . '000000';
        } else
            $this->_str_birthday = NULL;

        if (isset($_REQUEST['Contact_deathday_YEAR']) && !empty($_REQUEST['Contact_deathday_YEAR'])) {
            $year = PHPWS_Text::parseInput($_REQUEST['Contact_deathday_YEAR']);
            $month = PHPWS_Text::parseInput($_REQUEST['Contact_deathday_MONTH']);
            $day = PHPWS_Text::parseInput($_REQUEST['Contact_deathday_DAY']);
            $this->_str_deathday = sprintf('%04d', $year) . sprintf('%02d', $month) . sprintf('%02d', $day) . '000000';
        } else
            $this->_str_deathday = NULL;

        if (isset($_REQUEST['Contact_anniversary_YEAR']) && !empty($_REQUEST['Contact_anniversary_YEAR'])) {
            $year = PHPWS_Text::parseInput($_REQUEST['Contact_anniversary_YEAR']);
            $month = PHPWS_Text::parseInput($_REQUEST['Contact_anniversary_MONTH']);
            $day = PHPWS_Text::parseInput($_REQUEST['Contact_anniversary_DAY']);
            $this->_str_anniversary = sprintf('%04d', $year) . sprintf('%02d', $month) . sprintf('%02d', $day) . '000000';
        } else
            $this->_str_anniversary = NULL;

        if (isset($_REQUEST['Contact_altemail1']))
            $this->_altemail1 = PHPWS_Text::parseInput($_REQUEST['Contact_altemail1']);
        if (isset($_REQUEST['Contact_altemail2']))
            $this->_altemail2 = PHPWS_Text::parseInput($_REQUEST['Contact_altemail2']);
        if (isset($_REQUEST['Contact_custom1']))
            $this->_custom1 = PHPWS_Text::parseInput($_REQUEST['Contact_custom1']);
        if (isset($_REQUEST['Contact_custom2']))
            $this->_custom2 = PHPWS_Text::parseInput($_REQUEST['Contact_custom2']);
        if (isset($_REQUEST['Contact_custom3']))
            $this->_custom3 = PHPWS_Text::parseInput($_REQUEST['Contact_custom3']);
        if (isset($_REQUEST['Contact_custom4']))
            $this->_custom4 = PHPWS_Text::parseInput($_REQUEST['Contact_custom4']);
        if (isset($_REQUEST['Contact_comments']))
            $this->_comments = PHPWS_Text::parseInput($_REQUEST['Contact_comments']);

        if (isset($_FILES['NEW_Contact_image']) && !empty($_FILES['NEW_Contact_image']['name'])) {
            $imgarray = NULL;
            $dir = 'images/phpwscontacts/';
            $imgarray = EZform::saveImage('NEW_Contact_image', $GLOBALS['core']->home_dir . $dir);
            if (PHPWS_Error::isError($imgarray) || !is_array($imgarray)) {
                $imgarray->message('CNT_phpwscontacts');
            }

            $thumbnail = PHPWS_File::makeThumbnail($imgarray['name'], $dir, $dir . 'thumbs/');
            if (PHPWS_Error::isError($thumbnail) || !is_array($thumbnail)) {
                $thumbnail->message('CNT_phpwscontacts');
            }

            if (is_file($GLOBALS['core']->home_dir . $dir . 'thumbs/' . $thumbnail[0])) {
                $imgarray['thumb_name'] = $thumbnail[0];
                $imgarray['thumb_width'] = $thumbnail[1];
                $imgarray['thumb_height'] = $thumbnail[2];
            }

            $this->_image = $imgarray;
        } elseif (isset($_REQUEST['REMOVE_Contact_image'])) {
            // Delete image
            $dir = $GLOBALS['core']->home_dir . 'images/phpwscontacts/';
            unlink($dir . $this->_image['name']);
            if (isset($this->_image['thumb_name']))
                unlink($dir . 'thumbs/' . $this->_image['thumb_name']);
            $this->_image = NULL;

            $GLOBALS['CNT_phpwscontacts']['content'] .= $_SESSION['translate']->it('The image for [var1] was removed.', $this->getLabel());
        } elseif (isset($_REQUEST['CURRENT_Contact_image']) && ($_REQUEST['CURRENT_Contact_image'] != 'none') && ($_REQUEST['CURRENT_Contact_image'] != $this->_image['name'])) {
            $dir = 'images/phpwscontacts';
            $fulldir = $GLOBALS['core']->home_dir . $dir;

            $size = getimagesize($fulldir . $_REQUEST['CURRENT_Contact_image']);
            $imgarray['name'] = $_REQUEST['CURRENT_Contact_image'];
            $imgarray['width'] = $size[0];
            $imgarray['height'] = $size[1];

            // Need to change to this thumb.
            // Assuming last 4 chars in 
            $tnbase = substr($imgarray['name'], 0, -4);
            $tnext = substr($imgarray['name'], -4);
            $imgarray['thumb_name'] = $tnbase . '_tn' . $tnext;

            $size = getimagesize($fulldir . 'thumbs/' . $imgarray['thumb_name']);
            $imgarray['thumb_width'] = $size[0];
            $imgarray['thumb_height'] = $size[1];

            $this->_image = $imgarray;
        }

        if (isset($_REQUEST['Contact_visibility']))
            $this->_visibility = $_REQUEST['Contact_visibility'];

        if (isset($_REQUEST['Contact_mine']))
            $this->_mine = 1;
        else
            $this->_mine = 0;

        $error = $this->commit();

        if (PHPWS_Error::isError($error)) {
            $message = $_SESSION['translate']->it('The contact could not be saved to the database.');
            $error = new PHPWS_Error('phpwscontacts', $message, 'continue', 0);
            $error->message('CNT_phpwscontacts');

            $_REQUEST['CONTACTS_Contact_OP'] = 'edit';
            $this->action();
            return;
        } else {
            // FatCat
            if ($_SESSION['OBJ_fatcat'])
                $_SESSION['OBJ_fatcat']->saveSelect($this->getLabel(), 'index.php?module=phpwscontacts&amp;CONTACTS_MAN_OP=view&PHPWS_MAN_ITEMS[]=' . $this->getId(), $this->getId());

            // There can be only one, set all others to mine=0 where owner=this->getOwner()
            if ($this->_mine == 1)
                $GLOBALS['core']->sqlUpdate(array('mine'=>0), 'mod_phpwscontacts_contacts', array('id'=>$this->getId(),'owner'=>$this->getOwner()), NULL,array('id'=>'!=','owner'=>'='));

            $message = $_SESSION['translate']->it('The contact was successfully saved.');
            return $message . $this->_view();

        }


    }// END FUNC _save


    function _delete() {
        if (!$_SESSION['OBJ_user']->allow_access('phpwscontacts', 'delete_contacts')) {
            $message = $_SESSION['translate']->it('Access to delete contact was denied due to lack of proper permissions.');
            $error = new PHPWS_Error('phpwscontacts', 'CONTACTS_Contact::_delete()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        $content = null;

        if (isset($_REQUEST['Contact_yes'])) {
            $this->kill();

            // Remove FatCat entry
            if ($_SESSION['OBJ_fatcat'])
                $_SESSION['OBJ_fatcat']->purge($this->getId(), 'phpwscontacts');

            $content .= $_SESSION['translate']->it('The contact [var1] was successfully deleted from the database.', '<b><i>' . $this->getLabel() . '</i></b>');
        } elseif (isset($_REQUEST['Contact_no'])) {
            $content .= $_SESSION['translate']->it('No contact was deleted from the database.');
        } else {
            $form = new EZform('CONTACTS_Contact_delete');
            $form->add('module', 'hidden', 'phpwscontacts');
            $form->add('CONTACTS_Contact_OP', 'hidden', 'delete');

            $form->add('Contact_yes', 'submit', $_SESSION['translate']->it('Yes'));
            $form->add('Contact_no', 'submit', $_SESSION['translate']->it('No'));

            $tags = array();
            $tags = $form->getTemplate();
            $tags['MESSAGE'] = $_SESSION['translate']->it('Are you sure you want to delete this contact?');
            
            $content .= PHPWS_Template::processTemplate($tags, 'phpwscontacts', 'delete.tpl');
        }

        return $content;

    }// END FUNC _delete


    function _vcard() {
        $v = new Contact_Vcard_Build();

        $v->setFormattedName($this->_firstname . ' ' . $this->_lastname);
        $v->setName($this->_lastname, $this->_firstname, $this->_middlename, $this->_prefix, $this->_suffix);

        if (!empty($this->_email)) {
            $v->addEmail($this->_email);
            $v->addParam('TYPE', 'PREF');
            $v->addParam('TYPE', 'INTERNET');
        }

        // Need to find out what types I should use
        if (!empty($this->_altemail1)) {
            $v->addEmail($this->_altemail1);
            $v->addParam('TYPE', 'INTERNET');
        }
        if (!empty($this->_altemail2)) {
            $v->addEmail($this->_altemail2);
            $v->addParam('TYPE', 'INTERNET');
        }

        if (!empty($this->_str_birthday)) {
            $birthday = new Date($this->_str_birthday);
            $v->setBirthday($birthday->format('%Y-%m-%d'));
        }

        if (!empty($this->_phone_home)) {
            $v->addTelephone($this->_phone_home);
            $v->addParam('TYPE', 'HOME');
        }
        if (!empty($this->_phone_pager)) {
            $v->addTelephone($this->_phone_pager);
            $v->addParam('TYPE', 'PAGER');
        }
        if (!empty($this->_phone_mobile)) {
            $v->addTelephone($this->_phone_mobile);
            $v->addParam('TYPE', 'CELL');
        }
        if (!empty($this->_phone_work)) {
            $v->addTelephone($this->_phone_work);
            $v->addParam('TYPE', 'WORK');
        }
        if (!empty($this->_phone_fax)) {
            $v->addTelephone($this->_phone_fax);
            $v->addParam('TYPE', 'FAX');
        }

        if (!empty($this->_company_name))
            $v->addOrganization($this->_company_name);

        if (!empty($this->_company_title))
            $v->setTitle($this->_company_title);

        if (!empty($this->_company_street) ||
            !empty($this->_company_city) ||
            !empty($this->_company_state) ||
            !empty($this->_company_zip) ||
            !empty($this->_company_country)) {
            $v->addAddress('', '', $this->_company_street, $this->_company_city, $this->_company_state, $this->_company_zip, $this->_company_country);
            $v->addParam('TYPE', 'WORK');
        }
        if (!empty($this->_company_website)) {
            $v->setURL($this->_getWebsite($this->_company_website));
            $v->addParam('TYPE', 'WORK');
        }

        if (!empty($this->_personal_street) ||
            !empty($this->_personal_city) ||
            !empty($this->_personal_state) ||
            !empty($this->_personal_zip) ||
            !empty($this->_personal_country)) {
            $v->addAddress('', '', $this->_personal_street, $this->_personal_city, $this->_personal_state, $this->_personal_zip, $this->_personal_country);
            $v->addParam('TYPE', 'HOME');
        }
        if (!empty($this->_personal_website)) {
            $v->setURL($this->_getWebsite($this->_personal_website));
            $v->addParam('TYPE', 'HOME');
        }

        if (!empty($this->_comments))
            $v->setNote($this->_comments);
        
        $output = $v->fetch();
        $filename = $this->_lastname . '_' . $this->_firstname . '.vcf';

        
        Header('Content-Disposition: attachment; filename=' . $filename);
        Header('Content-Length: ' . strlen($output));
        Header('Connection: close');
        Header('Content-Type: text/x-vCard; name=' . $filename);
        
        echo $output;
        exit();
    }// END FUNC _vcard


    function _import() {
        /* Create form */
        $form = new EZform('CONTACTS_Contact_import');
        $tabs = 1;


        /* Contact Prefix */
        $form->add('Contact_vcard', 'file');
        $form->setTab('Contact_vcard', $tabs);
        $tabs++;

        /* Save Button */
        $form->add('Contact_save', 'submit', $_SESSION['translate']->it('Import vCard'));
        $form->setTab('Contact_save', $tabs);

        /* Module Information */
        $form->add('module', 'hidden', 'phpwscontacts');
        $form->add('CONTACTS_Contact_OP', 'hidden', 'parse');

        $tags = array();
        $tags = $form->getTemplate();

        $tags['VCARD_TEXT'] = $_SESSION['translate']->it('vCard');

        return PHPWS_Template::processTemplate($tags, 'phpwscontacts', 'import.tpl');

    }// END FUNC _import


    function _parse() {
        if (isset($_FILES['Contact_vcard']) && !empty($_FILES['Contact_vcard']['name'])) {
            $parse = new Contact_Vcard_Parse();
            $cardinfo = $parse->fromFile($_FILES['Contact_vcard']['tmp_name']);

            foreach(array_keys($cardinfo[0]) as $datatype) {
                if ($datatype == 'N') {
                    $this->_lastname = $cardinfo[0][$datatype][0]['value'][0][0];
                    $this->_firstname = $cardinfo[0][$datatype][0]['value'][1][0];
                    $this->_middlename = $cardinfo[0][$datatype][0]['value'][2][0];
                    $this->_prefix = $cardinfo[0][$datatype][0]['value'][3][0];
                    $this->_suffix = $cardinfo[0][$datatype][0]['value'][4][0];
                } elseif ($datatype == 'ADR') {
                    foreach($cardinfo[0][$datatype] as $addresses) {
                        if ($addresses['param']['TYPE'][0] == 'HOME') {
                            $this->_personal_street = $addresses['value'][2][0];
                            $this->_personal_city = $addresses['value'][3][0];
                            $this->_personal_state = $addresses['value'][4][0];
                            $this->_personal_zip = $addresses['value'][5][0];
                            $this->_personal_country = $addresses['value'][6][0];
                        } elseif ($addresses['param']['TYPE'][0] == 'WORK') {
                            $this->_company_street = $addresses['value'][2][0];
                            $this->_company_city = $addresses['value'][3][0];
                            $this->_company_state = $addresses['value'][4][0];
                            $this->_company_zip = $addresses['value'][5][0];
                            $this->_company_country = $addresses['value'][6][0];
                        }
                    }
                } elseif ($datatype == 'TEL') {
                    foreach($cardinfo[0][$datatype] as $phones) {
                        if ($phones['param']['TYPE'][0] == 'HOME') {
                            $this->_phone_home = $phones['value'][0][0];
                        } elseif ($phones['param']['TYPE'][0] == 'WORK') {
                            $this->_phone_work = $phones['value'][0][0];
                        } elseif ($phones['param']['TYPE'][0] == 'PAGER') {
                            $this->_phone_pager = $phones['value'][0][0];
                        } elseif ($phones['param']['TYPE'][0] == 'CELL') {
                            $this->_phone_mobile = $phones['value'][0][0];
                        } elseif ($phones['param']['TYPE'][0] == 'FAX') {
                            $this->_phone_fax = $phones['value'][0][0];
                        } else {
                            $this->_phone_other = $phones['value'][0][0];
                        }
                    }
                } elseif ($datatype == 'EMAIL') {
                    foreach($cardinfo[0][$datatype] as $emails) {
                        foreach($emails['param']['TYPE'] as $type) {
                            if ($type == 'PREF') {
                                if (!empty($this->_email))
                                    if (empty($this->_altemail1))
                                        $this->_altemail1 = $this->_email;
                                    elseif (empty($this->_altemail2))
                                        $this->_altemail2 = $this->_email;

                                $this->_email = $emails['value'][0][0];
                                break;
                            } elseif (empty($this->_email)) {
                                $this->_email = $emails['value'][0][0];
                            } elseif (empty($this->_altemail1)) {
                                $this->_altemail1 = $emails['value'][0][0];
                            } elseif (empty($this->_altemail2)) {
                                $this->_altemail2 = $emails['value'][0][0];
                            }
                        }
                    }
                } elseif ($datatype == 'ORG') {
                    $this->_company_name = $cardinfo[0][$datatype][0]['value'][0][0];
                } elseif ($datatype == 'TITLE') {
                    $this->_company_title = $cardinfo[0][$datatype][0]['value'][0][0];
                } elseif (($datatype == 'ROLE') && empty($this->_company_title)) {
                    $this->_company_title = $cardinfo[0][$datatype][0]['value'][0][0];
                } elseif ($datatype == 'NOTE') {
                    $this->_comments = $cardinfo[0][$datatype][0]['value'][0][0];
                } elseif ($datatype == 'URL') {
                    foreach($cardinfo[0][$datatype] as $urls) {
                        foreach($urls['param']['TYPE'] as $type) {
                            if ($type == 'HOME') {
                                $this->_personal_website = $urls['value'][0][0];
                            } elseif ($type == 'WORK') {
                                $this->_company_website = $urls['value'][0][0];
                            }
                        }
                    }
                } elseif ($datatype == 'BDAY') {
                    $birthday = new Date($cardinfo[0][$datatype][0]['value'][0][0] . ' 00:00:00');
                    $this->_str_birthday = $birthday->getDate(DATE_FORMAT_TIMESTAMP);
                }
            }

            $this->setLabel($this->_lastname . ', ' . $this->_firstname . ' ' . $this->_middlename);
            $this->_visibility = 0;

            $error = $this->commit();

            if (PHPWS_Error::isError($error)) {
                $message = $_SESSION['translate']->it('The contact could not be saved to the database.');
                $error = new PHPWS_Error('phpwscontacts', $message, 'continue', 0);
                $error->message('CNT_phpwscontacts');
    
                $_REQUEST['CONTACTS_Contact_OP'] = 'edit';
                $this->action();
                return;
            } else {
                // FatCat
                //if ($_SESSION['OBJ_fatcat'])
                    //$_SESSION['OBJ_fatcat']->saveSelect($this->getLabel(), 'index.php?module=phpwscontacts&amp;CONTACTS_MAN_OP=view&PHPWS_MAN_ITEMS[]=' . $this->getId(), $this->getId());
    
                $message = $_SESSION['translate']->it('The contact was successfully saved.');
                return $message . $this->_view();
    
            }
        }
    }// END FUNC _parse

    function action() {
        switch($_REQUEST['CONTACTS_Contact_OP']) {
            case 'view':
                $title = $_SESSION['translate']->it('View Contact Information');
                $content = $_SESSION['CONTACTS_Manager']->_menu() . $this->_view();
                break;

            case 'viewprintable':
                $title = NULL;
                $content = $this->_view();
                break;

            case 'edit':
                $title = $_SESSION['translate']->it('Edit Contact Information');
                $content = $_SESSION['CONTACTS_Manager']->_menu() . $this->_edit();
                break;
            
            case 'save':
                $title = $_SESSION['translate']->it('Save Contact Information');
                $content = $_SESSION['CONTACTS_Manager']->_menu() . $this->_save();
                break;

            case 'delete':
                $title = $_SESSION['translate']->it('Delete Contact Information');
                $content = $_SESSION['CONTACTS_Manager']->_menu() . $this->_delete();
                break;

            case 'vcard':
                $this->_vcard();
                break;

            case 'import':
                $title = $_SESSION['translate']->it('Import vCard');
                $content = $_SESSION['CONTACTS_Manager']->_menu() . $this->_import();
                break;

            case 'parse':
                $title = $_SESSION['translate']->it('Import vCard');
                $content = $_SESSION['CONTACTS_Manager']->_menu() . $this->_parse();
                break;

            default:
                $title = 'NO OP';
                $content = 'This function is not yet implemented.';
        }

        if (isset($content)) {
            $GLOBALS['CNT_phpwscontacts']['title'] = $title;
            $GLOBALS['CNT_phpwscontacts']['content'] .= $content;
        }
    }// END FUNC action


    function isVisible() {
        if ($this->getOwner() == $_SESSION['OBJ_user']->username)
            return TRUE;
        elseif ($this->_visibility == 0)
            return TRUE;
        elseif ($this->_visibility == 1) {
            if ($_SESSION['OBJ_user']->username)
                return TRUE;
            else
                return FALSE;
        } elseif ($this->_visibility == 2) {
            if ($_SESSION['OBJ_user']->allow_access('phpwscontacts','view_privates'))
                return TRUE;
            else
                return FALSE;
        } else
            return FALSE;
    }// END FUNC isVisible


    function printCSVHeader() {
        $content = NULL;

        $content .= '"' . $_SESSION['translate']->it('Last Name') . '",';
        $content .= '"' . $_SESSION['translate']->it('First Name') . '",';
        $content .= '"' . $_SESSION['translate']->it('Middle Name') . '",';
        $content .= '"' . $_SESSION['translate']->it('Prefix') . '",';
        $content .= '"' . $_SESSION['translate']->it('Suffix') . '",';
        if ($_SESSION['OBJ_fatcat'])
            $content .= '"' . $_SESSION['translate']->it('Category') . '",';
        $content .= '"' . $_SESSION['translate']->it('E-Mail') . '",';
        $content .= '"' . $_SESSION['translate']->it('Home Phone') . '",';
        $content .= '"' . $_SESSION['translate']->it('Pager') . '",';
        $content .= '"' . $_SESSION['translate']->it('Mobile Phone') . '",';
        $content .= '"' . $_SESSION['translate']->it('Work Phone') . '",';
        $content .= '"' . $_SESSION['translate']->it('Fax') . '",';
        $content .= '"' . $_SESSION['translate']->it('Other Phone') . '",';
        $content .= '"' . $_SESSION['translate']->it('Company Name') . '",';
        $content .= '"' . $_SESSION['translate']->it('Company Title') . '",';
        $content .= '"' . $_SESSION['translate']->it('Company Street Address') . '",';
        $content .= '"' . $_SESSION['translate']->it('Company City') . '",';
        $content .= '"' . $_SESSION['translate']->it('Company State') . '",';
        $content .= '"' . $_SESSION['translate']->it('Company Zip') . '",';
        $content .= '"' . $_SESSION['translate']->it('Company Country') . '",';
        $content .= '"' . $_SESSION['translate']->it('Company Website') . '",';
        $content .= '"' . $_SESSION['translate']->it('Home Street Address') . '",';
        $content .= '"' . $_SESSION['translate']->it('Home City') . '",';
        $content .= '"' . $_SESSION['translate']->it('Home State') . '",';
        $content .= '"' . $_SESSION['translate']->it('Home Zip') . '",';
        $content .= '"' . $_SESSION['translate']->it('Home Country') . '",';
        $content .= '"' . $_SESSION['translate']->it('Personal Website') . '",';
        $content .= '"' . $_SESSION['translate']->it('Born') . '",';
        $content .= '"' . $_SESSION['translate']->it('Died') . '",';
        $content .= '"' . $_SESSION['translate']->it('Anniversary') . '",';
        $content .= '"' . $_SESSION['translate']->it('Alt Email 1') . '",';
        $content .= '"' . $_SESSION['translate']->it('Alt Email 2') . '",';
        if (!empty($_SESSION['CONTACTS_Manager']->_custom1label))
            $content .= '"' . $_SESSION['CONTACTS_Manager']->_custom1label . '",';
        if (!empty($_SESSION['CONTACTS_Manager']->_custom2label))
            $content .= '"' . $_SESSION['CONTACTS_Manager']->_custom2label . '",';
        if (!empty($_SESSION['CONTACTS_Manager']->_custom3label))
            $content .= '"' . $_SESSION['CONTACTS_Manager']->_custom3label . '",';
        if (!empty($_SESSION['CONTACTS_Manager']->_custom4label))
            $content .= '"' . $_SESSION['CONTACTS_Manager']->_custom4label . '",';
        $content .= '"' . $_SESSION['translate']->it('Comments') . '"';

        $content .= "\n";
        return $content;
    }// END FUNC printCSVHeader


    function printCSV() {
        $content = NULL;

        $content .= '"' . $this->_lastname . '",';
        $content .= '"' . $this->_firstname . '",';
        $content .= '"' . $this->_middlename . '",';
        $content .= '"' . $this->_prefix . '",';
        $content .= '"' . $this->_suffix . '",';
        if ($_SESSION['OBJ_fatcat']) {
            if (!($row = $_SESSION['OBJ_fatcat']->getModuleElements('phpwscontacts', $this->getId())))
                $content .= '",';
            else {
                $cats = NULL;
                foreach ($row as $element) {
                    $catid = $_SESSION['OBJ_fatcat']->getElementCatId($element['element_id'],'phpwscontacts');
                    $cat = new PHPWS_Fatcat_Category($catid);
                    if (isset($cats))
                        $cats .= '; ';
                    $cats .= $cat->title;
                }
                $content .= '"' . $cats . '",';
            }
        }
        $content .= '"' . $this->_email . '",';
        $content .= '"' . $this->_phone_home . '",';
        $content .= '"' . $this->_phone_pager . '",';
        $content .= '"' . $this->_phone_mobile . '",';
        $content .= '"' . $this->_phone_work . '",';
        $content .= '"' . $this->_phone_fax . '",';
        $content .= '"' . $this->_phone_other . '",';
        $content .= '"' . $this->_company_name . '",';
        $content .= '"' . $this->_company_title . '",';
        $content .= '"' . $this->stripLF($this->_company_street) . '",';
        $content .= '"' . $this->_company_city . '",';
        $content .= '"' . $this->_company_state . '",';
        $content .= '"' . $this->_company_zip . '",';
        $content .= '"' . $this->_company_country . '",';
        $content .= '"' . $this->_getWebsite($this->_company_website) . '",';
        $content .= '"' . $this->stripLF($this->_personal_street) . '",';
        $content .= '"' . $this->_personal_city . '",';
        $content .= '"' . $this->_personal_state . '",';
        $content .= '"' . $this->_personal_zip . '",';
        $content .= '"' . $this->_personal_country . '",';
        $content .= '"' . $this->_getWebsite($this->_personal_website) . '",';
        if (!empty($this->_str_birthday)) {
            $birthday = new Date($this->_str_birthday);
            $content .= '"' . $birthday->format('%Y/%m/%d') . '",';
        } else {
            $content .= '",';
        }
        if (!empty($this->_str_deathday)) {
            $deathday = new Date($this->_str_deathday);
            $content .= '"' . $deathday->format('%Y/%m/%d') . '",';
        } else {
            $content .= '",';
        }
        if (!empty($this->_str_anniversary)) {
            $anniversary = new Date($this->_str_anniversary);
            $content .= '"' . $anniversary->format('%Y/%m/%d') . '",';
        } else {
            $content .= '",';
        }
        $content .= '"' . $this->_altemail1 . '",';
        $content .= '"' . $this->_altemail2 . '",';
        if (!empty($_SESSION['CONTACTS_Manager']->_custom1label))
            $content .= '"' . $this->_custom1 . '",';
        if (!empty($_SESSION['CONTACTS_Manager']->_custom2label))
            $content .= '"' . $this->_custom2 . '",';
        if (!empty($_SESSION['CONTACTS_Manager']->_custom3label))
            $content .= '"' . $this->_custom3 . '",';
        if (!empty($_SESSION['CONTACTS_Manager']->_custom4label))
            $content .= '"' . $this->_custom4 . '",';
        $content .= '"' . $this->stripLF($this->_comments) . '"';

        $content .= "\n";
        return $content;
    }// END FUNC printCSV


    function stripLF($str) {
        $str = str_replace("\r\n", '; ', $str);
        $str = str_replace("\n", '; ', $str);
        return $str;
    }// END FUNC stripLF


    function getListID() {
        return $this->getId();
    }

    function getListName() {
        $name = NULL;
        if ($_SESSION['CONTACTS_Manager']->_sortbyfirstname) {
            $name .= $this->_firstname;
            /*
            if (isset($this->_middlename) && !empty($this->_middlename))
                $name .= ' ' . $this->_middlename;
            */
            $name .= ' ' . $this->_lastname;
        } else {
            $name .= $this->_lastname;
            $name .= ', ' . $this->_firstname;
            /*
            if (isset($this->_middlename) && !empty($this->_middlename))
                $name .= ' ' . $this->_middlename;
            */
        }
        return $name;
    }

    function getListFirstName() {
        return $this->_firstname;
    }

    function getListLastName() {
        return $this->_lastname;
    }

    function getListMaidenName() {
        if (isset($this->_maidenname) && !empty($this->_maidenname))
            return $this->_maidenname;
        else
            return NULL;
    }

    function getListEmail() {
        return $this->_email;
    }

    function getListPhone_Home() {
        if (isset($this->_phone_home) && !empty($this->_phone_home))
            return $this->_phone_home;
        else
            return NULL;
    }

    function getListPhone_Home_Label() {
        if (isset($this->_phone_home) && !empty($this->_phone_home))
            return $_SESSION['translate']->it('Home');
        else
            return NULL;
    }

    function getListPhone_Work() {
        if (isset($this->_phone_work) && !empty($this->_phone_work))
            return $this->_phone_work;
        else
            return NULL;
    }

    function getListPhone_Work_Label() {
        if (isset($this->_phone_work) && !empty($this->_phone_work))
            return $_SESSION['translate']->it('Work');
        else
            return NULL;
    }

    function getListPhone_Mobile() {
        if (isset($this->_phone_mobile) && !empty($this->_phone_mobile))
            return $this->_phone_mobile;
        else
            return NULL;
    }

    function getListPhone_Mobile_Label() {
        if (isset($this->_phone_mobile) && !empty($this->_phone_mobile))
            return $_SESSION['translate']->it('Mobile');
        else
            return NULL;
    }

    function getListEdit_Link() {
        if (isset($_SESSION['OBJ_user']->username) && (($_SESSION['OBJ_user']->username == $this->getOwner()) || $_SESSION['OBJ_user']->allow_access('phpwscontacts','edit_contacts')))
            return '<a href="index.php?module=phpwscontacts&amp;CONTACTS_MAN_OP=edit&amp;PHPWS_MAN_ITEMS[]=' . $this->getId() . '">' . $_SESSION['translate']->it('Edit') . '</a>';
        else
            return NULL;
    }

    function getListThumbnail() {
        if (!empty($this->_image)) {
            if (isset($this->_image['thumb_name'])) {
                return '<img src="images/phpwscontacts/thumbs/' . $this->_image['thumb_name'] . '" alt="' . $this->getLabel() . '" border="0" />';
            } else {
                return '<img src="images/phpwscontacts/' . $this->_image['name'] . '" alt="' . $this->getLabel() . '" border="0" />';
            }
        } else {
            return NULL;
        }
    }

    function getListCompany_Name() {
        return $this->_company_name;
    }

    function getListCompany_Title() {
        return $this->_company_title;
    }

    function getListCustom1() {
        return $this->_custom1;
    }

    function getListCustom2() {
        return $this->_custom2;
    }

    function getListCustom3() {
        return $this->_custom3;
    }

    function getListCustom4() {
        return $this->_custom4;
    }

    function getListDeceased() {
        if (isset($this->_str_deathday) && !empty($this->_str_deathday))
            return $_SESSION['translate']->it('Deceased');
        else
            return NULL;
    }


    /**
     * Returns website, prepending 'http://' if missing
     *
     * I've found that people just enter 'www.domain.com' for
     * their websites.  I'm going to make a large assumption
     * and just say that if it doesn't begin with 'http', then
     * I need to prepend http://.  I'm sure people will complain
     * about me ignoring other protocols.  I'll wait for the bugs to
     * fall in first.
     *
     * @access private
     * @returns string
     */
    function _getWebsite($url) {
        if (!empty($url)) {
            if (eregi("^http", $url)) {
                return $url;
            } else {
                return 'http://' . $url;
            }
        } else {
            return $url;
        }
    }

}// END CLASS CONTACTS_Contact

?>

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
 * @version     $Id: Manager.php,v 1.38 2004/09/15 01:13:34 rizzo Exp $
 */

/**
 * @since 0.6.0
 */
require_once(PHPWS_SOURCE_DIR . 'core/Manager.php');
require_once(PHPWS_SOURCE_DIR . 'core/EZform.php');
require_once(PHPWS_SOURCE_DIR . 'core/Text.php');
require_once(PHPWS_SOURCE_DIR . 'core/Message.php');
require_once(PHPWS_SOURCE_DIR . 'core/List.php');
require_once(PHPWS_SOURCE_DIR . 'mod/phpwscontacts/class/Contact.php');
require_once(PHPWS_SOURCE_DIR . 'mod/help/class/CLS_help.php');

class CONTACTS_Manager extends PHPWS_Manager {

    var $contact        = NULL;
    var $message        = NULL;
    var $row_class      = NULL;

    /**
     * Flag 1/0 to allow anonymous viewing
     *
     * @var      bit
     * @access   private
     */
    var $_allow_anon_view;


    /**
     * Flag 1/0 to sort by first name
     *
     * @var      bit
     * @access   private
     */
    var $_sortbyfirstname;


    /**
     * Custom field labels
     *
     * @var      string
     * @access   private
     */
    var $_custom1label;
    var $_custom2label;
    var $_custom3label;
    var $_custom4label;

    function CONTACTS_Manager() {
        $this->setModule('phpwscontacts');
        $this->setRequest('CONTACTS_MAN_OP');
        $this->init();

        // Settings
        $result = $GLOBALS['core']->sqlSelect('mod_phpwscontacts_settings');
        $this->_allow_anon_view = $result[0]['allow_anon_view'];
        $this->_sortbyfirstname = $result[0]['sortbyfirstname'];
        $this->_custom1label = $result[0]['custom1label'];
        $this->_custom2label = $result[0]['custom2label'];
        $this->_custom3label = $result[0]['custom3label'];
        $this->_custom4label = $result[0]['custom4label'];
    }// END FUNC CONTACTS_Manager


    function _menu() {
        $links = array();
        $links[] = '<a href="index.php?module=phpwscontacts&amp;CONTACTS_MAN_OP=browse">' . $_SESSION['translate']->it('Browse Contacts') . '</a>';
        if ($_SESSION['OBJ_user']->allow_access('phpwscontacts','edit_contacts')){
            $links[] = '<a href="index.php?module=phpwscontacts&amp;CONTACTS_MAN_OP=list">' . $_SESSION['translate']->it('List Contacts') . '</a>';
            $links[] = '<a href="index.php?module=phpwscontacts&amp;CONTACTS_MAN_OP=add">' . $_SESSION['translate']->it('Add New Contact') . '</a>';
        }

        $links[] = '<a href="index.php?module=phpwscontacts&amp;CONTACTS_MAN_OP=exportcsv">' . $_SESSION['translate']->it('Download CSV') . '</a>';

        if (!empty($_SESSION['OBJ_user']->username))
            $links[] = '<a href="index.php?module=phpwscontacts&amp;CONTACTS_MAN_OP=import">' . $_SESSION['translate']->it('Import vCard') . '</a>';

        if ($_SESSION['OBJ_user']->allow_access('phpwscontacts', 'edit_settings'))
            $links[] = '<a href="index.php?module=phpwscontacts&amp;CONTACTS_MAN_OP=getsettings">' . $_SESSION['translate']->it('Settings') . '</a>';

        $menu = '<table border="0" width="100%"><tr>';
        if (!empty($_SESSION['OBJ_user']->username))
            $menu .= '<td><a href="index.php?module=phpwscontacts&amp;CONTACTS_MAN_OP=edit">' . $_SESSION['translate']->it('Add / Edit My Contact Information') . '</a></td>';
        $menu .= '<td align="right">' . implode('&#160;|&#160;', $links) . '</td></tr></table>';
        return $menu;
    }// END FUNC _menu


    function _list() {
        if (!$this->_allow_anon_view && !$_SESSION['OBJ_user']->username) {
            $GLOBALS['CNT_phpwscontacts']['title'] = $_SESSION['translate']->it('Anonymous Viewing Denied');
            $GLOBALS['CNT_phpwscontacts']['content'] .= $_SESSION['translate']->it('Anonymous viewing of contact information has been disabled.  You must log-in to view contacts.');
            return FALSE;
        }

        $this->setTable('mod_phpwscontacts_contacts');

        if ($this->_sortbyfirstname)
            $this->setOrder('firstname, middlename, lastname');
        else
            $this->setOrder('lastname, firstname, middlename');

        if (!$_SESSION['OBJ_user']->username)
            $this->setSort('visibility=0');
        elseif (!$_SESSION['OBJ_user']->allow_access('phpwscontacts', 'view_privates'))
            $this->setSort('visibility<=1');

        $GLOBALS['CNT_phpwscontacts']['title'] = $_SESSION['translate']->it('Contacts');
        $GLOBALS['CNT_phpwscontacts']['content'] .= $this->_menu();
        $GLOBALS['CNT_phpwscontacts']['content'] .= $this->getList('contacts');
    }// END FUNC _list


    function _view($printable = FALSE) {
        if (!$this->_allow_anon_view && !$_SESSION['OBJ_user']->username) {
            $GLOBALS['CNT_phpwscontacts']['title'] = $_SESSION['translate']->it('Anonymous Viewing Denied');
            $GLOBALS['CNT_phpwscontacts']['content'] .= $_SESSION['translate']->it('Anonymous viewing of contact information has been disabled.  You must log-in to view contacts.');
            return FALSE;
        }

        $this->contact = new CONTACTS_Contact($_REQUEST['PHPWS_MAN_ITEMS'][0]);
        if ($this->contact->isVisible()) {
            if ($printable)
                $_REQUEST['CONTACTS_Contact_OP'] = 'viewprintable';
            else
                $_REQUEST['CONTACTS_Contact_OP'] = 'view';
        } else {
            $GLOBALS['CNT_phpwscontacts']['title'] = $_SESSION['translate']->it('Viewing Denied');
            $GLOBALS['CNT_phpwscontacts']['content'] .= $_SESSION['translate']->it('This contact has restricted the visibility of their information.  You fail to meet the requirements.');
        }
    }// END FUNC _view


    function _edit() {
        if (isset($_REQUEST['PHPWS_MAN_ITEMS']))
            $this->contact = new CONTACTS_Contact($_REQUEST['PHPWS_MAN_ITEMS'][0]);
        else
            $this->contact = new CONTACTS_Contact();
        $_REQUEST['CONTACTS_Contact_OP'] = 'edit';
    }// END FUNC _edit


    function _delete() {
        $this->contact = new CONTACTS_Contact($_REQUEST['PHPWS_MAN_ITEMS'][0]);
        $_REQUEST['CONTACTS_Contact_OP'] = 'delete';
    }// END FUNC _delete


    function _add() {
        $this->contact = new CONTACTS_Contact();
        $_REQUEST['CONTACTS_Contact_OP'] = 'edit';
    }// END FUNC _add


    function _import() {
        $this->contact = new CONTACTS_Contact();
        $_REQUEST['CONTACTS_Contact_OP'] = 'import';
    }// END FUNC _import


    function _parse() {
        $this->contact = new CONTACTS_Contact();
        $_REQUEST['CONTACTS_Contact_OP'] = 'parse';
    }// END FUNC _parse


    function search($where) {
        $array = array();
        $sql = "SELECT id, label FROM " . $GLOBALS['core']->tbl_prefix . "mod_phpwscontacts_contacts $where";
        $result = $GLOBALS['core']->query($sql);

        if ($result) {
            while($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
                $array[$row['id']] = $row['label'];
            }
        }

        return $array;
    }// END FUNC search


    function _browse() {
        if (!$this->_allow_anon_view && !$_SESSION['OBJ_user']->username) {
            $GLOBALS['CNT_phpwscontacts']['title'] = $_SESSION['translate']->it('Anonymous Viewing Denied');
            $GLOBALS['CNT_phpwscontacts']['content'] .= $_SESSION['translate']->it('Anonymous viewing of contact information has been disabled.  You must log-in to view contacts.');
            return FALSE;
        }

        $listTags = array();
        $listTags['TITLE'] = $_SESSION['translate']->it('Contacts');

        $list = new PHPWS_List;
        $list->setModule('phpwscontacts');
        $list->setClass('CONTACTS_Contact');
        $list->setTable('mod_phpwscontacts_contacts');
        $list->setDbColumns(array(  'id',
                                    'lastname',
                                    'maidenname',
                                    'firstname',
                                    'email',
                                    'phone_home',
                                    'phone_work',
                                    'phone_mobile',
                                    'image',
                                    'company_name',
                                    'company_title',
                                    'custom1',
                                    'custom2',
                                    'custom3',
                                    'custom4',
                                    'str_deathday'
                            ));
        $list->setListColumns(array(    'id',
                                        'lastname',
                                        'maidenname',
                                        'firstname',
                                        'email',
                                        'phone_home',
                                        'phone_work',
                                        'phone_mobile',
                                        'phone_home_label',
                                        'phone_work_label',
                                        'phone_mobile_label',
                                        'edit_link',
                                        'thumbnail',
                                        'name',
                                        'company_name',
                                        'company_title',
                                        'custom1',
                                        'custom2',
                                        'custom3',
                                        'custom4',
                                        'deceased'
                            ));
        $list->setName('browse');
        $list->setOp('CONTACTS_MAN_OP=browse');
        $list->setPaging(array('limit'=>10, 'section'=>TRUE, 'limits'=>array(5,10,20,50), 'back'=>'&#60;&#60;', 'forward'=>'&#62;&#62;', 'anchor'=>FALSE));

        if (!$_SESSION['OBJ_user']->username)
            $list->setWhere('visibility=0');
        elseif (!$_SESSION['OBJ_user']->allow_access('phpwscontacts', 'view_privates'))
            $list->setWhere('visibility<=1');

        if ($this->_sortbyfirstname)
            $list->setOrder('firstname, middlename, lastname');
        else
            $list->setOrder('lastname, firstname, middlename');

        $list->setExtraListTags($listTags);
        //$list->setExtraRowTags($rowTags);


        $GLOBALS['CNT_phpwscontacts']['title'] = $_SESSION['translate']->it('Contacts');
        $GLOBALS['CNT_phpwscontacts']['content'] .= $this->_menu();
        $GLOBALS['CNT_phpwscontacts']['content'] .= $list->getList();

    }// END FUNC _browse


    /**
    * Show settings form
    *
    * @author Don Seiler <don@NOSPAM.seiler.us>
    */
    function _getSettings() {
        if (!$_SESSION['OBJ_user']->allow_access('phpwscontacts', 'edit_settings')) {
            $message = $_SESSION['translate']->it('Access to edit settings was denied.');
            $error = new PHPWS_Error('phpwscontacts', 'CONTACTS_Manager::_getSettings()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        $tabs = 1;
        $form = new EZform('CONTACTS_Settings_edit');

        $form->add('SETTINGS_allow_anon_view', 'checkbox');
        $form->setMatch('SETTINGS_allow_anon_view', $this->_allow_anon_view);
        $form->setTab('SETTINGS_allow_anon_view', $tabs);
        $tabs++;

        $form->add('SETTINGS_sortbyfirstname', 'checkbox');
        $form->setMatch('SETTINGS_sortbyfirstname', $this->_sortbyfirstname);
        $form->setTab('SETTINGS_sortbyfirstname', $tabs);
        $tabs++;

        $form->add('SETTINGS_custom1label', 'text', $this->_custom1label);
        $form->setTab('SETTINGS_custom1label', $tabs);
        $tabs++;

        $form->add('SETTINGS_custom2label', 'text', $this->_custom2label);
        $form->setTab('SETTINGS_custom2label', $tabs);
        $tabs++;

        $form->add('SETTINGS_custom3label', 'text', $this->_custom3label);
        $form->setTab('SETTINGS_custom3label', $tabs);
        $tabs++;

        $form->add('SETTINGS_custom4label', 'text', $this->_custom4label);
        $form->setTab('SETTINGS_custom4label', $tabs);
        $tabs++;

        $form->add('SETTINGS_save', 'submit', $_SESSION['translate']->it('Save'));
        $form->setTab('SETTINGS_save', $tabs);

        $form->add('module', 'hidden', 'phpwscontacts');
        $form->add('CONTACTS_MAN_OP', 'hidden', 'savesettings');

        $tags = array();
        $tags = $form->getTemplate();
        $tags['ANONVIEW_TEXT'] = $_SESSION['translate']->it('Allow Anonymous Viewing of Contacts');
        $tags['SORTBY_TEXT'] = $_SESSION['translate']->it('Sort Contacts by First Name instead of Last Name');
        $tags['CUSTOMFIELDS_LABEL'] = $_SESSION['translate']->it('Custom Fields');
        $tags['CUSTOM1LABEL_TEXT'] = $_SESSION['translate']->it('Custom Field 1 Label');
        $tags['CUSTOM2LABEL_TEXT'] = $_SESSION['translate']->it('Custom Field 2 Label');
        $tags['CUSTOM3LABEL_TEXT'] = $_SESSION['translate']->it('Custom Field 3 Label');
        $tags['CUSTOM4LABEL_TEXT'] = $_SESSION['translate']->it('Custom Field 4 Label');
        $tags['CUSTOMFIELDS_HELP'] = CLS_help::show_link('phpwscontacts', 'customfields');

        $title = $_SESSION['translate']->it('phpwsContacts Settings');
        $content = $this->_menu() . PHPWS_Template::processTemplate($tags, 'phpwscontacts', 'edit_settings.tpl');

        $GLOBALS['CNT_phpwscontacts']['title'] = $title;
        $GLOBALS['CNT_phpwscontacts']['content'] .= $content;
    }// END FUNC _getSettings


    /**
    * Save settings
    *
    * @author Don Seiler <don@NOSPAM.seiler.us>
    */
    function _saveSettings() {
        if (isset($_REQUEST['SETTINGS_allow_anon_view']))
            $this->_allow_anon_view = 1;
        else
            $this->_allow_anon_view = 0;

        if (isset($_REQUEST['SETTINGS_sortbyfirstname']))
            $this->_sortbyfirstname = 1;
        else
            $this->_sortbyfirstname = 0;

        if (isset($_REQUEST['SETTINGS_custom1label']))
            $this->_custom1label = PHPWS_Text::parseInput($_REQUEST['SETTINGS_custom1label']);

        if (isset($_REQUEST['SETTINGS_custom2label']))
            $this->_custom2label = PHPWS_Text::parseInput($_REQUEST['SETTINGS_custom2label']);

        if (isset($_REQUEST['SETTINGS_custom3label']))
            $this->_custom3label = PHPWS_Text::parseInput($_REQUEST['SETTINGS_custom3label']);

        if (isset($_REQUEST['SETTINGS_custom4label']))
            $this->_custom4label = PHPWS_Text::parseInput($_REQUEST['SETTINGS_custom4label']);

        $data = array();
        $data['allow_anon_view'] = $this->_allow_anon_view;
        $data['sortbyfirstname'] = $this->_sortbyfirstname;
        $data['custom1label'] = $this->_custom1label;
        $data['custom2label'] = $this->_custom2label;
        $data['custom3label'] = $this->_custom3label;
        $data['custom4label'] = $this->_custom4label;

        if ($GLOBALS['core']->sqlUpdate($data, 'mod_phpwscontacts_settings')) {
            $title = $_SESSION['translate']->it('Settings Saved');
            $content = $this->_menu() . $_SESSION['translate']->it('Your settings have been successfully saved.');
        } else {
            $title = $_SESSION['translate']->it('Error Saving Settings');
            $content = $this->_menu() . $_SESSION['translate']->it('There was an error saving the settings.');
        }

        $GLOBALS['CNT_phpwscontacts']['title'] = $title;
        $GLOBALS['CNT_phpwscontacts']['content'] .= $content;
    }// END FUNC _saveSettings


    function _exportCSV() {
        if (!$this->_allow_anon_view && !$_SESSION['OBJ_user']->username) {
            $GLOBALS['CNT_phpwscontacts']['title'] = $_SESSION['translate']->it('Anonymous Viewing Denied');
            $GLOBALS['CNT_phpwscontacts']['content'] .= $_SESSION['translate']->it('Anonymous viewing of contact information has been disabled.  You must log-in to view contacts.');
            return FALSE;
        }

        $content = null;
        $content .= CONTACTS_Contact::printCSVHeader();

        $sql = 'SELECT id FROM ' . $GLOBALS['core']->tbl_prefix . 'mod_phpwscontacts_contacts order by ';
        if ($this->_sortbyfirstname)
            $sql .= 'firstname, middlename, lastname';
        else
            $sql .= 'lastname, firstname, middlename';

        $result = $GLOBALS['core']->getCol($sql);
        if ($result)
            foreach($result as $rowid) {
                $contact = new CONTACTS_Contact($rowid);
                if ($contact->isVisible()) 
                    $content .= $contact->printCSV();
            }

        // Write to file
        $filename = 'phpwscontacts_' . date('Ymd') . '.csv';
        Header('Content-Disposition: attachment; filename=' . $filename);
        Header('Content-Length: ' . strlen($content));
        Header('Connection: close');
        Header('Content-Type: text/plain; name=' . $filename);
        echo $content;
        exit();
    }// END FUNC _exportCSV


    function action() {
        if (PHPWS_Message::isMessage($this->message))
            $this->message->display();

        switch($_REQUEST['CONTACTS_MAN_OP']) {
            case 'list':
                $this->_list();
                break;

            case 'delete':
                $this->_delete();
                break;

            case 'edit':
                $this->_edit();
                break;

            case 'view':
                $this->_view();
                break;

            case 'browse':
                $this->_browse();
                break;

            case 'viewprintable':
                $this->_view(TRUE);
                break;

            case 'add':
                $this->_add();
                break;
            
            case 'getsettings':
                $this->_getSettings();
                break;

            case 'savesettings':
                $this->_saveSettings();
                break;

            case 'exportcsv':
                $this->_exportCSV();
                break;

            case 'import':
                $this->_import();
                break;

            case 'parse':
                $this->_parse();
                break;
        }
    }// END FUNC action

}// END CLASS CONTACTS_Manager

?>

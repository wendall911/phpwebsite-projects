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
 * @version     $Id: search.php,v 1.5 2004/09/14 03:43:28 rizzo Exp $
 */

$module          = 'phpwscontacts';
$search_class    = 'CONTACTS_Manager';
$search_function = 'search';
$search_cols     = 'firstname, middlename, lastname, maidenname, email, personal_city, personal_state, personal_zip, company_name, company_city, company_street, company_zip, custom1, custom2, custom3, custom4';
$view_string     = '&amp;CONTACTS_MAN_OP=view&amp;PHPWS_MAN_ITEMS[]=';
$show_block      = 1;
$block_title     = 'Contacts';
$class_file      = 'Manager.php';

?>

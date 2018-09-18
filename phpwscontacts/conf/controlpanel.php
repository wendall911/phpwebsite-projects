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
 * @version     $Id: controlpanel.php,v 1.6 2004/09/14 03:43:28 rizzo Exp $
 */

$image['name'] = 'contacts.png';
$image['alt'] = 'phpwsContacts by Don Seiler';

$link[] = array ('label'=>'Contacts',
		 'module'=>'phpwscontacts',
		 'url'=>'index.php?module=phpwscontacts&amp;CONTACTS_MAN_OP=list',
		 'image'=>$image,
		 'description'=>'Contacts provides extended user information.',
		 'tab'=>'content');

$link[] = array ('label'=>'Set My Contact Information',
		 'module'=>'phpwscontacts',
		 'url'=>'index.php?module=phpwscontacts&amp;CONTACTS_MAN_OP=edit',
		 'image'=>$image,
		 'description'=>'Allows you to set your contact information.',
		 'tab'=>'my_settings');

?>

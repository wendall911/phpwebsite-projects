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
 * @version     $Id: manager.php,v 1.6 2004/09/14 03:43:28 rizzo Exp $
 */

/* The name of my lists and their corresponding database constraints */
$lists = array('contacts'=>'approved=1');

/* The directory where my templates are located */
$templates = array('contacts'=>'contacts');

/*
 * CONTACTS
 */
$contactsColumns = array(   'lastname'=>$_SESSION['translate']->it('Last Name'),
                            'firstname'=>$_SESSION['translate']->it('First Name'),
                            'email'=>$_SESSION['translate']->it('Email'),
                            'id'=>NULL );

$contactsActions = array(   'view'=>'View',
                            'edit'=>'Edit',
                            'delete'=>'Delete');

$contactsPermissions = array(   'view'=>NULL,
                                'edit'=>'edit_contacts',
                                'delete'=>'delete_contacts');

$contactsPaging = array(    'op'=>'CONTACTS_MAN_OP=list',
                            'limit'=>50,
                            'section'=>1,
                            'limits'=>array(5,10,25,50),
                            'back'=>'&#60;&#60;',
                            'forward'=>'&#62;&#62;');


?>

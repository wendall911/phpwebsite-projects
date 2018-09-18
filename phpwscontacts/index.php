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
 * @version     $Id: index.php,v 1.9 2004/09/14 03:09:09 rizzo Exp $
 */

$CNT_phpwscontacts['content'] = NULL;

if (!isset($_SESSION['CONTACTS_Manager']))
    $_SESSION['CONTACTS_Manager'] = new CONTACTS_Manager;

if (isset($_REQUEST['CONTACTS_MAN_OP']))
    $_SESSION['CONTACTS_Manager']->action();

if (isset($_REQUEST['CONTACTS_Contact_OP']) && isset($_SESSION['CONTACTS_Manager']->contact))
    $_SESSION['CONTACTS_Manager']->contact->action();

?>

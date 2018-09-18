<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * phpwsBB
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
 * @author      Don Seiler <don@NOSPAM.seiler.us>
 * @version     $Id: boost.php,v 1.42 2006/02/04 04:12:18 singletrack Exp $
 */

$mod_title = 'phpwsbb';
$mod_pname = 'Bulletin Board';
$mod_directory = 'phpwsbb';
$mod_filename = 'index.php';
$allow_view = 'all';
$priority = 50;
$admin_mod = 1;
$active = 'on';
$version = '1.0.4';
$admin_op = '&amp;PHPWSBB_MAN_OP=list';
$mod_class_files = array('Manager.php');
$mod_sessions = array('PHPWSBB_Manager');

?>

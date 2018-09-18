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
 * @version     $Id: search.php,v 1.6 2004/09/13 21:41:55 rizzo Exp $
 */

$module          = 'phpwsbb';
$search_class    = 'PHPWSBB_Manager';
$search_function = 'search';
$search_cols     = 'label, body, owner';
$view_string     = '&amp;PHPWSBB_MAN_OP=view&amp;PHPWS_MAN_ITEMS[]=';
$show_block      = 1;
$block_title     = 'Bulletin Board';
$class_file      = 'Manager.php';

?>

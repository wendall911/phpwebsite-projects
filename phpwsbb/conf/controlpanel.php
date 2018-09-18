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
 * @version     $Id: controlpanel.php,v 1.8 2004/09/13 21:41:55 rizzo Exp $
 */

$image['name'] = 'phpwsbb.png';
$image['alt'] = 'Author: Don Seiler';

$link[] = array ('label'=>'Bulletin Board',
		 'module'=>'phpwsbb',
		 'url'=>'index.php?module=phpwsbb&amp;PHPWSBB_MAN_OP=list',
		 'image'=>$image,
		 'description'=>'A bulletin board module.',
		 'tab'=>'content');

$link[] = array ('label'=>'Set My Bulletin Board Preferences',
         'module'=>'phpwsbb',
         'url'=>'index.php?module=phpwsbb&amp;PHPWSBB_MAN_OP=usersettings',
         'image'=>$image,
         'description'=>'Allows you to set your bulletin board preferences.',
         'tab'=>'my_settings');

?>

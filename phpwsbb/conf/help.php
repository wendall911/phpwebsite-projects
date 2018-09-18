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
 * @version     $Id: help.php,v 1.15 2006/02/25 22:00:25 singletrack Exp $
 */

$admin_email = 'Bulletin Board Administrator Email';
$admin_email_content = 'Enter in the email address of the bulletin board administrator, if he/she is different than the overall phpWebSite administrator.  If this field is left blank, then phpwsBB will use the contact email address in the User control panel\'s Settings screen.';

$email_text = 'Notification Email Text';
$email_text_content = 'This is the text sent to users who are monitoring threads when those threads are replied to.  Enter in the text you would like to appear in the body of the email message.  You can use variables [name] and [url] to represent the name of the thread and the url to view the thread, respectively.';

$monitor_posts = 'Monitor Posts';
$monitor_posts_content = 'Enabling this setting will have all posts emailed to the bulletin board administrator email (or the website admin if it is blank).';

$sortorder = 'Forum Sort Order';
$sortorder_content = 'This value sets the order in which the forums are listed on the Forums page.  Must be an integer value.';

$bboffline = 'Take Bulletin Board Offline';
$bboffline_content = 'Checking this will prevent non-deity users from accessing the bulletin board.  They will be presented with a message indicating the bulletin board is offline for maintenance.  This can be used when doing upgrades or maintenance like cleaning/moving messages and threads.';

$suspend_monitors = 'Suspend Monitors';
$suspend_monitors_content = 'Checking this will suspend the sending of emails that would notify you when someone has replied to a thread that you are monitoring.  This is useful if you are going on vacation, for example.';

$remove_all_monitors = 'Remove All Monitors';
$remove_all_monitors_content = 'This will clear all monitors so you will no longer be monitoring any threads.';

$remote_avatars = 'Remote Avatars';
$remote_avatars_content = 'You can allow users to specify the address of an image that\'s not hosted on your site to be used as an avatar.<br /><br />While this saves you storage space and bandwidth, it also takes the control over what is displayed out of your hands.  Users can insert images with large file sizes, jpeg exploits, obscenities, etc..<br /><br />For this reason we recommend that you <u>do not</u> use this feature.';

$views = 'Record and show number of times a thread is viewed';
$views_content = 'With this option switched on, each time a thread is viewed a counter is updated. An extra column in the forum view will also be shown to all users.<br /><br />With this option switched off, no statistics are recorded.<br /><br />Please note that you will have to quit your browser session and restart it in order to see any change in the forum view.<br /><br />For better performance on busy systems we recommend you leave this option off. You may also toggle it off during busy periods for better performance but be aware that the view count will be suspended until you toggle it back on again.';
 
$views_lowpri = 'Use low priority stats updates (MySQL only)';
$views_lowpri_content = 'This option relies on a feature of the MySQL database to use a less resource hungry method of updating statistics such as the view count. Updates will be delayed until there is nobody reading the forum.<br /><br />We recommend you switch this option on if you\'re using MySQL. If you\'re on another database, you must leave this option off.';
?>

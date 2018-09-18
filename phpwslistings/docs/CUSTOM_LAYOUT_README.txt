# Custom Layout HOWTO for phpwsListings
# $Id: CUSTOM_LAYOUT_README.txt,v 1.2 2003/05/19 02:16:50 dtseiler Exp $

Custom Layout Procedure

NOTE: This document applies to phpwsListings 0.3.0 or greater.

1. Under phpwsListings->Settings tic the box "Advanced Layout". This changes
   the default templates found in mod/phpwslistings/templates to
   cust_view_full.tpl, cust_printable.tpl, cust_view_small.tpl and
   cust_searchform.tpl.
   
2. Edit the layout of the file using {FORMELEMENT0_LABEL}, {FORMELEMENT0} and 
   {THUMBIMAGE0}.  Where the rank of the element and image represents the 
   corresponding number in the tag. 
   
NOTE: For images, {THUMBIMAGE2} is skipped, because it is the rank of the main 
      image.
      
If you are having trouble with these procedures please file a Support
Request at our sourceforge.net project site:

https://sourceforge.net/tracker/?func=add&group_id=73598&atid=538275

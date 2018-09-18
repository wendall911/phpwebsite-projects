<?php
/**
 * @version $Id: help.php,v 1.5 2003/05/19 03:59:23 wendall911 Exp $
 */

$field_type = "Field Type";
$field_type_content = "The type of form element this is: Text, Textarea (large text box), Select List (drop-down list), Select Multiple (list box that allows multiple selections by holding down the \"Ctrl\" key), Radio Buttons, and Check Boxes. There is also an option called \"Blank Line\". This will print a blank line to give you some semblance of formatting control. In the current incarnation all form elements will just be printed one per line, so a blank line will give some \"vertical whitespace\" and add a hint of separation and organization.";

$field_name = "Field Name";
$field_name_content = "This is the \"code\" name of the field that the system will use to track the element. This value should have no spaces or special characters except the underscore, and should be all lowercase. An example would be \"city\" for a City field or \"street_address\" for a Street Address field.";

$field_caption = "Field Caption";
$field_caption_content = "This is the \"human readable\" version of \"Field Name\", so you can have \"City\" or \"Street Address\" or whatever captions describe your field to your users.";

$default_text = "Default Text";
$default_text_content = "This is the default text that will appear when a user creates a new listing that uses this form element. Some examples might be if you wanted to pre-select a \"No\" value in a drop-down list or have the words \"Enter a description here\" in a textbox.";

$field_elements = "Field Elements";
$field_elements_content = "Field elements are only used for Select List, Select Multiple, Radio Buttons, and Check Boxes. The field elements string represents the options that will be part of these elements. The string needs to be in the format \"option1||option2||option3||etc\", meaning each option separated by double \"pipe\" characters. I recommend leaving the first option blank, so you will have a blank option at the beginning of the list. So my example would then by \"||option1||option2||option3||etc\". This field is stored as a text element in the database and can be pretty long.";

$rank = "Rank";
$rank_content = "The rank that you want this form element displayed amongst the others. Rank #1 will be at the top. Equal ranks will be sorted alphabetically.";

$required = "Required";
$required_content = "Yes/No if you want this to be a required field in the listing. If Yes, the user will not be allowed to save his/her listing without completing this field.";

$display_on_browse = "Display on Browse";
$display_on_browse_content = "Yes/No if you want this field to be displayed in \"browse\" mode. Browse mode is used for showing a list of listings like in the Featured Listing section or Search Results.";

$agent_only = "Agent Only";
$agent_only_content = "Yes/No if you want this to only be visible by active agents. This is useful for agents to share information about a listing with one another, but not with the general public.";

$classes = "Listing Classes";
$classes_content = "Listing classes allow you to list separate types of items.  They are used to classify listing elements so that the right elements show up for the right types of items.  For example a house may have an address field, and a boat may have a model field.  Those fields should only be available to listings that are in their respective classes.";

$advanced_layout = "Advanced Layout Settings";
$advanced_layout_content = "Add the ability to format the full listing page, printable page, list view and search page.  Each form element will be defined as FORMELEMT0, FORMELEMT1, etc., based on rank. This also switches the template files to cust_filename.tpl. For details see CUSTOM_LAYOUT_README.txt in the docs folder.";
?>

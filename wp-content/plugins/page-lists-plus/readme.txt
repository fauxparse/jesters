=== Page Lists Plus ===
Contributors: Technokinetics
Donate link: http://www.technokinetics.com/donations/
Tags: navigation, menu, page list, link text, title attribute, page title, nofollow, redirect, wp_list_pages, sliding doors
Requires at least: 2.5
Tested up to: 2.7.1
Stable tag: 0.1.7

Adds customisation options to the wp_list_pages function used to create Page menus, all controlled through the WordPress dashboard.

== Description ==

Page Lists Plus adds customisation options to the wp_list_pages function used to create Page menus, all controlled through the dashboard.

By default, links in Page lists use the Page's title as both the link text and the title attribute. Page Lists Plus allows you to specify alternative link text and title attributes to be used instead.

Page Lists Plus also allows you remove items from Page lists or just unlink them, and to add rel="nofollow" or target="_blank" to links, or redirect them to a different url.

You can also use Page Lists Plus to add a "Home" link at the start of your Page lists and/or a "Contact" link at the end, and to add class="first_item" to the first item in a Page list or span tags inside all items in a Page list to help with styling.

== Installation ==

1. Download the plugin's zip file, extract the contents, and upload them to your wp-content/plugins folder.
2. Login to your WordPress dashboard, click ”Plugins”, and activate Page Lists Plus.
3. Customise your Page links through the Write Page or Manage Page screens. You will need to save the Page for the changes to take effect.

== Frequently Asked Questions ==

= Why won't Page Lists Plus work with older versions of WordPress? =

Page Lists Plus uses the add_meta_box() function to create new fields on the Write Page and Manage Page screens. This function doesn't exist in versions of WordPress earlier than 2.5.

= Will Page Lists Plus work with WordPress MU? =

No. At the moment, Page Lists Plus is not WPMU compatible.

= I've upgraded and the new features aren't working / I'm getting an error message; what do I need to do? =

Deactivate and reactivate the plugin. Some features require changes to your database structure which are only made when the plugin is activated.

== Screenshots ==

1. (/1.0/page-lists-plus.gif).
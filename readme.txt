=== hiWeb Core ===
Contributors: Den Media
Donate link: 
Tags: plugins, admin menu post edit, develop, custom title, widgets, repository, assets, options, api, php, migration, site migration, quick migration, easy migration, change domain, change server, server migration
Requires at least: 4.1
Tested up to: 4.3
Stable tag: 4.2.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The plugin allows you to quickly create Web sites on WordPress, immediately unpack and activate the archives of favorite plug-ins, show common adminis-menu items and to migrate seamlessly to new hosting.

== Description ==

This plug-in allows for the creators of WordPress sites to quickly and easily incorporate all the most standard features menu control widgets to customize the admin panel, delete or rename the menu items more convenient and simple.
Easy and quick migrate your site to new server / domain.
Just plug-in allows you to create your own repository of plug-ins and additional scripts, which will be useful for any developer sites.

= WordPress Migration =
[youtube http://www.youtube.com/watch?v=j1mSDPV9MZ4]

= I recently started to describe the function of the English language: https://github.com/hiweb-moscow/hiweb-core/wiki =

=  =

= Some of the features available through the admin menu interface: =

 1. Add meta tage BASE to head.
 2. Convert cyrillic and other dissalow symbols of file names to latin symbols.
 3. Convert cyrillic and other dissalow symbols of slug to latin symbols. (etc. Cyr2Lat Plugin).
 4. Post Support Thumbnails.
 5. Post Support More Types.
 6. Add Menu item to admin menu root.
 7. Add Widgets item to admin menu root.
 8. Add path to plugin php file in plugins WP repository.
 9. Add custom post/page title

= Plugins Archives manager =

 1. Manage you'r plugins, created by `hiWeb Plugins Server` - https://wordpress.org/plugins/hiweb-plugins-server/
 2. Manage you'r assets (and addition scripts), created by `hiWeb Plugins Server` - https://wordpress.org/plugins/hiweb-plugins-server/

= Opportunities for php programmers, such as: =

 1. Output information to the console browser `<?php hiweb()->console($info); ?>`
 2. Include enforcing mode DEBUG `<?php hiweb()->debug(); ?>`
 3. Use the built-in template `<?php hiweb()->file()->getHtml_fromTpl(array:parametrs, string:filePath); ?>`
 4. All functions have a built-in documentation in PHP (Only in Russian)
 5. Complete documentation in the development of site http://plugins.hiweb.moscow is coming soon...


= API hiWeb Core =
 1. I advise you to use the program with indexing functions of my plugin hiWeb Core, such as JetBrains PhpStorm. It will prompt existing functions. Use that: `<?php hiweb()->.... ?>`

= hiweb()->console() =
Displays information counter in the console browser.

Example:
 1. `hiweb()->console('My console message');`
 2. `hiweb()->console()->warn('Warning message');`
 3. `hiweb()->console()->error('Error message');`
 4. `hiweb()->console()->info(array(1,2, 'foo' => 'bar'));`

= hiweb()->print_r() =
Print arrays, objects and other vars in to screen

Example:
 1. `hiweb()->print_r(array(1,2, 'foo' => 'bar'))`

= hiweb()->file() =
Class for working with the file system

Example:
 1. `hiweb()->file()->js('my-script')` - Search for a file with the specified name on the file PHP, where the function was called js (). The same function will search in subfolders there, naprmier folder 'js'. In this case, the file 'my-script.js' will be searched next to the original PHP, in the folder 'js/my-script.js', just try to determine the name of the file PHP, to use it in the search , as well as the name and function class , from which the function was called `hiweb()->file()->js()` ...


== Installation ==

This section describes how to install the plugin and get it working.

1. Upload folder `hiweb-core` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'hiWeb Core' menu in WordPress

== Frequently Asked Questions ==

No questions...

== Screenshots ==

1. Enable / disable extensions of admin menu
2. Edit admin menu items
3. API: hiweb()->console() print array
4. API: hiweb()->print_r() print array
5. API: hiweb()->file() some functions

== Changelog ==
= 1.4.4.0 =
fix basedir autolocate, curl alternative


= 1.4.3.2 =
fix some errors and bugs...

= 1.4.1.3 =
hiweb()->wp()->isLoginPage() fix

= 1.4.1.2 =
Somessmall changes...

= 1.4.1.1 =
New AdminMenu Panel

= 1.4.1.0 =
A system file cache, faster operations Plugin more than 10 times.

= 1.4.0.2 =
Ajax render fix...

= 1.4.0.0 =
Serious update affected the structure functions API, ofomrleniya settings menu. Functional podchti not changed.

= 1.2.2.5 =
Update Font Awesome, fix hiweb()->console() for non admin footer print

= 1.2.2.5 =
Fix head print

= 1.2.2.4 =
Site migration default setting fix

= 1.2.2.2 =
More fix plugin to work on local windows server, like XAMPP 4, fix errors print with style.css

= 1.2.2.1 =
More fix plugin to work on local windows server, like XAMPP 3

= 1.2.2.0 =
Fix plugin to work on local windows server, like XAMPP 2, add cache

= 1.2.1.3 =
Fix plugin to work on local windows server, like XAMPP

= 1.2.1.2 =
Fix Site Migration function: include `postmeta` table.

= 1.2.1.1 =
Fix Site Migration function: include `postmeta` table.

= 1.2.1.0 =
Fix some bugs...

= 1.2.0.2 =
1. Enabled filter template for all the_content
2. Update screnshots and readme.txt

= 1.2.0.1 =
Updating the language pack

= 1.2.0.0 =
1. <b>Adding the automatic rapid migration site</b>
2. Serious improvements include JS and CSS
3. Fixed errors at work on a subdomain or subfolder

= 1.1.4.1 =
1. Add `hiweb()->cron()` class in hiWeb API.
2. Add helper points in admin menu for more info.
3. Fix trouble with cURL.
4. Change lang pack.
5. Add some functions in hiWeb API.
6. Update `hiweb()->console()` class and add more debug info.
7. Exclude WP errors from `hiweb()->debug()` mod.
8. Fix JS and CSS scripts enqueue with `hiweb()->file()->css($path);` and `hiweb()->file()->js($path);`.
9. More litle changes...

= 1.1.3.1 =
Fix Admin Menu bag

= 1.1.3.1 =
It achieved most of the requirements for placement in the repository WordPress. My first release) )

= 1.1.2.3 =
No more extendet libraries, like Smarty or HTMLDom...it's clear and does not conflict with other libraries/javascripts
The plugin is fully translated into English, as well has a completely Russian translation
Excellent work on the sub-domains and sub-folders in the server

= 1.0.2.2 e =

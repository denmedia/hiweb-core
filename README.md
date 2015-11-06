hiWeb Core v1.4.0.2
===================

The plugin allows you to quickly create Web sites on WordPress, immediately unpack and activate the archives of favorite plug-ins, show common adminis-menu items and to migrate seamlessly to new hosting.

Description
-----------

This plug-in allows for the creators of WordPress sites to quickly and easily incorporate all the most standard features menu control widgets to customize the admin panel, delete or rename the menu items more convenient and simple.
Easy and quick migrate your site to new server / domain.
Just plug-in allows you to create your own repository of plug-ins and additional scripts, which will be useful for any developer sites.

Build-In Fucntions
==================

WordPress Migration
-------------------
See video: [youtube http://www.youtube.com/watch?v=j1mSDPV9MZ4]

Some of the features available through the admin menu interface:
----------------------------------------------------------------

 1. Add meta tage BASE to head.
 2. Convert cyrillic and other dissalow symbols of file names to latin symbols.
 3. Convert cyrillic and other dissalow symbols of slug to latin symbols. (etc. Cyr2Lat Plugin).
 4. Post Support Thumbnails.
 5. Post Support More Types.
 6. Add Menu item to admin menu root.
 7. Add Widgets item to admin menu root.
 8. Add path to plugin php file in plugins WP repository.
 9. Add custom post/page title

Plugins Archives manager
------------------------

 1. Manage you'r plugins, created by `hiWeb Plugins Server` - [https://wordpress.org/plugins/hiweb-plugins-server/]
 2. Manage you'r assets (and addition scripts), created by `hiWeb Plugins Server` - [https://wordpress.org/plugins/hiweb-plugins-server/]

Opportunities for php programmers, such as:
-------------------------------------------

 1. Output information to the console browser `<?php hiweb()->console($info); ?>`
 2. Include enforcing mode DEBUG `<?php hiweb()->debug(); ?>`
 3. Use the built-in template `<?php hiweb()->file()->getHtml_fromTpl(array:parametrs, string:filePath); ?>`
 4. All functions have a built-in documentation in PHP (Only in Russian)
 5. Complete documentation in the development of site http://plugins.hiweb.moscow is coming soon...


API of hiWeb Core
-----------------
 1. I advise you to use the program with indexing functions of my plugin hiWeb Core, such as JetBrains PhpStorm. It will prompt existing functions. Use that: `<?php hiweb()->.... ?>`

hiweb()->console()
------------------
Displays information counter in the console browser.

Example:
 1. `hiweb()->console('My console message');`
 2. `hiweb()->console()->warn('Warning message');`
 3. `hiweb()->console()->error('Error message');`
 4. `hiweb()->console()->info(array(1,2, 'foo' => 'bar'));`

hiweb()->print_r()
------------------
Print arrays, objects and other vars in to screen

Example:
 1. `hiweb()->print_r(array(1,2, 'foo' => 'bar'))`

hiweb()->file()
---------------
Class for working with the file system

Example:
 1. `hiweb()->file()->js('my-script')` - Search for a file with the specified name on the file PHP, where the function was called js (). The same function will search in subfolders there, naprmier folder 'js'. In this case, the file 'my-script.js' will be searched next to the original PHP, in the folder 'js/my-script.js', just try to determine the name of the file PHP, to use it in the search , as well as the name and function class , from which the function was called `hiweb()->file()->js()` ...


Installation
============

This section describes how to install the plugin and get it working.

1. Upload folder `hiweb-core` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'hiWeb Core' menu in WordPress
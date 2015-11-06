<?php
/**
 * Created by PhpStorm.
 * User: denmedia
 * Date: 08.04.2015
 * Time: 21:43
 */
/*
Plugin Name: hiWeb Core
Plugin URI: http://plugins.hiweb.moscow/core
Description: This plug-in allows for the creators of WordPress sites to quickly and easily incorporate all the most standard features menu control widgets to customize the admin panel, delete or rename the menu items more convenient and simple. Just plug-in allows you to create your own repository of plug-ins and additional scripts, which will be useful for any developer sites.
Version: 1.4.0.2
Author: Den Media
Author URI: http://plugins.hiweb.moscow
*/


define('HIWEB_VERSION', '1.4.0.2');

require_once 'inc/hiweb-core-class.php';
require_once 'inc/hiweb-core-define.php';


hiweb()->wp_settings();
hiweb()->wizard();

hiweb()->file()->inc('hiweb-core-plugins');


if(is_admin()){
    ////Admin CSS
    hiweb()->file()->inc('settings');
    hiweb()->file()->css('hiweb-core');
    hiweb()->file()->css('hiweb-core-settings/hiweb-core-settings');
    hiweb()->file()->js('hiweb-core');
    hiweb()->file()->js('hiweb-core-wp/hiweb-core-wp');
}
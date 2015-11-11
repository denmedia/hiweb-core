<?php
/**
 * Created by PhpStorm.
 * User: denmedia
 * Date: 09.04.2015
 * Time: 9:55
 */


////DIRs
define('BASE_DIR', hiweb()->getStr_baseDir());
define('HIWEB_DIR', dirname(dirname(__FILE__)));
define('HIWEB_CORE_DIR', dirname(dirname(__FILE__)));

define('HIWEB_DIR_CACHE', hiweb()->file()->getStr_normalizeDirSeparates(WP_CONTENT_DIR.'/hiweb-cache'));
define('HIWEB_DIR_JS', hiweb()->file()->getStr_normalizeDirSeparates(HIWEB_CORE_DIR.'/js'));
define('HIWEB_DIR_CSS', hiweb()->file()->getStr_normalizeDirSeparates(HIWEB_CORE_DIR.'/css'));
define('HIWEB_DIR_ASSET', hiweb()->file()->getStr_normalizeDirSeparates(WP_CONTENT_DIR.'/assets'));
define('HIWEB_DIR_TPL', HIWEB_CORE_DIR.'/tpl');

////URLs
define('DOMAIN_URL', hiweb()->url()->getStr_sheme().$_SERVER['SERVER_NAME']);
define('BASE_URL', hiweb()->getStr_baseUrl());

define('HIWEB_PLUGINS_REPOSITORY', BASE_URL);

if(!defined('DIR_SEPARATOR')) { define('DIR_SEPARATOR', hiweb()->file()->getStr_directorySeparator()); }



/*hiweb()->globalValues = array(
    '_baseurl' => BASE_URL,
    '_base_url' => BASE_URL,
    '_base_dir' => BASE_DIR,
    '_domainurl' => DOMAIN_URL,
    '_hiweb_version' => HIWEB_VERSION,
    '_request_url_arr' => hiweb()->wp()->getArr_requestUri(),
    '_admin_mail' => hiweb()->wp()->getStr_adminMail(),
    '_blogname' => hiweb()->wp()->getStr_blogName(),
    '_dir_separator' => DIR_SEPARATOR
);*/
<?php
/**
 * Created by PhpStorm.
 * User: d9251
 * Date: 11.11.2015
 * Time: 16:31
 */


class hiweb_cms {

    public $id = false; ///wordpress | bitrix


    public function do_autoDetect(){
        if(defined('WPLANG ') && defined('WP_BLOG_ADMIN ')) $this->id = 'wordpress';
        if(defined('BX_DISABLE_INDEX_PAGE ') && defined('SITE_ID ')) $this->id = 'bitrix';
        return $this->id;
    }


}
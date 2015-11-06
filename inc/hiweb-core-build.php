<?php
/**
 * Created by PhpStorm.
 * User: d9251
 * Date: 26.09.2015
 * Time: 10:51
 */


class hiweb_build {


    /**
     * Генерация табов для WP
     * @param array $tabsArray
     * @return bool
     */
    public function getHtml_tabs($tabsArray = array(array('name' => '{lang}Название таба{/lang}', 'content' => '{lang}Содержимое таба{/lang}', 'slug' => '', 'select' => 1)), $useJs = true){
        if(!is_array($tabsArray)) return false;
        $rTabs = array();
        $defTab = array('name' => 'таб', 'content' => '', 'slug' => '', 'select' => 0, 'url' => hiweb()->string()->getStr_urlQuery(null, array(),array('tab')));
        $slugSelect = trim(hiweb()->request('tab')) == '' ? null : hiweb()->request('tab');
        ///
        $content = '';
        $count = 0;
        foreach ($tabsArray as $slugName => $tab) {
            ///Slug
            $slug = is_string($slugName) ? $count : hiweb()->array2()->getValNext($tab, array('slug','name'));
            $slug = trim(hiweb()->string()->getStr_allowSymbols($slug, 15),'-');
            if((string)$slug == '') { hiweb()->console()->error(array('не верные данные табов', $slug),1); return false; }
            ///slug Select
            if(is_null($slugSelect)) $slugSelect = $slug;
            ///Content Extract
            $selected = $slugSelect == $slug;
            if(is_string($slug) && is_string($tab)){
                $rTabs[$slug] = array_merge($defTab,array('name' => $slugName, 'content' => $tab, 'select' => $selected, 'url' => hiweb()->string()->getStr_urlQuery(null, array('tab' => $slug))));
            } elseif(is_array($tab) && is_object(hiweb()->getVal_fromArr($tab,0)) && is_string(hiweb()->getVal_fromArr($tab,1))) {
                $rTabs[$slug] = array_merge($defTab,array('name' => $slugName, 'slug' => $slug, 'content' => $selected ? hiweb()->getVal_fromArr($tab,0)->{hiweb()->getVal_fromArr($tab,1)}(hiweb()->getVal_fromArr($tab,2)) : null, 'select' => $selected, 'url' => hiweb()->string()->getStr_urlQuery(null, array('tab' => $slug))));
            }
            if($selected) { $content = $rTabs[$slug]['content']; }
            $count ++;
        }
        return hiweb()->file()->getHtml_fromTpl(array('tabs' => $rTabs, 'usejs' => $useJs, 'content' => $content));
    }


    /**
     * Вывести содержимое для тега head
     */
    public function getEcho_head(){ echo $this->getHtml_head(); }

    /**
     * Возвращает содержимое тега head
     * @return string|void
     */
    public function getHtml_head(){ return hiweb()->file()->getHtml_fromTpl(); }



    public function getHtml_options($fieldsArr = array(), $title = '', $opts = array()){
        if(!is_array($fieldsArr)) return false;
        if(!is_array($opts)) $opts = array($opts);
        ///
        $opts = array_merge(array(
            'wp_nonce' => wp_nonce_field('update-options'),
            'savechanges' => __('Save Changes'),
            'ids' => array()
        ), $opts);
        ///
        foreach($fieldsArr as $id => $field){
            if(!is_array($field)){
                $opts['fields'][] = $field;
            } else {
                register_setting( 'hiweb-settings-plugins', $id );
                $field = array_merge($this->def_field, $field);
                $opts['fields'][$id] = $field;
                $opts['fields'][$id]['val'] = get_option($id, $field['def']);
                $opts['ids'][] = $id;
            }
        }
        return hiweb()->file()->getHtml_fromTpl($opts);
    }



}
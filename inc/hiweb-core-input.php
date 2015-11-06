<?php
/**
 * Created by PhpStorm.
 * User: denmedia
 * Date: 03.05.2015
 * Time: 10:40
 */


class hiweb_input {


    public $def_fieldType = 'text';

    public $def_field = array(
        'value' => null,
        'default' => '',
        'type' => 'text',
        'sub_type' => '',
        'label' => '',
        'name' => '',
        'description' => '',
        'tags' => array(),
        'class' => '',
        'display' => array(
            /*array(
                'do' => 'hide', //hide | show
                'id' => 'hiweb_cms_title', //element id: input | select | textarea
                'value' => '', //
                'operator' => '==' //operator: == | != | > | < ...etc...
            )*/
        )
    );


    public function __construct(){
        hiweb()->file()->asset('font-awesome', null, 'font-awesome.min');
        hiweb()->file()->css('css/hiweb-input');
        hiweb()->file()->js('js/hiweb-input');
    }


    public function ajax(){
        switch(hiweb()->request('do')){
            case 'get_terms': echo $this->getJson_terms(hiweb()->request('taxonomies')); break;
            case 'get_posts_by_term': echo json_encode($this->getArrPosts_byTerms(
                hiweb()->request('post_type'),
                hiweb()->request('post_taxonomies_terms')
            )); break;
        }
        die();
    }


    private function getJson_terms($taxonomies){
        $r = array();
        foreach(get_terms($taxonomies, array( 'hide_empty' => false )) as $term){ $r[$term->term_id] = $term->name; }
        return json_encode($r);
    }

    private function getArrPosts_byTerms($postTypes = null, $postTaxonomies_Terms = null){
        $r = array();
        $postTypes = hiweb()->array2()->getArr($postTypes);
        $postTaxonomies_Terms = hiweb()->array2()->getArr($postTaxonomies_Terms);
        $args = array('posts_per_page' => -1);
        if(!is_null($postTypes)) { $args['post_type'] = $postTypes; }
        if(!is_null($postTaxonomies_Terms) && hiweb()->array2()->count($postTaxonomies_Terms) > 0 && hiweb()->array2()->getVal_byIndex($postTaxonomies_Terms,array(0,0)) != '') {
            $args['tax_query'] = array('relation' => 'OR');
            foreach($postTaxonomies_Terms as $tax => $terms){
                $args['tax_query'][] = array(
                    'taxonomy' => $tax,
                    'field' => 'id',
                    'terms' => $terms
                );
            }
        }
        foreach(get_posts($args) as $p){ $r[$p->ID] = $p->post_title; }
        return $r;
    }


    /**
     * Возвращает массив доступных типов вводимых данных
     * @return array
     */
    public function getArr_types(){
        $methods = get_class_methods($this);
        $r = array();
        foreach($methods as $m){ if(strpos($m, '_') === 0 && strpos($m, '__') !== 0) $r[] = substr($m,1); }
        return $r;
    }


    /**
     * Вывести страничку опций
     * @param $fieldsArr - массив полей
     * @param string $title - титл опций
     * @param array $attr - дополнительные аттрибуты, такие как: wp_nonce, savechanges
     * @return bool|string|void
     */
    public function getHtml_options($fieldsArr, $title = '', $attr = array()){
        if(!is_array($fieldsArr)) return false;
        if(!is_array($attr)) $attr = array($attr);
        ///
        $attr = array_merge(array(
            'wp_nonce' => wp_nonce_field('update-options'),
            'savechanges' => __('Save Changes'),
            'ids' => array(),
            'title' => $title
        ), $attr);
        ///
        foreach($fieldsArr as $id => $field){
            if(!is_array($field)){
                $attr['fields'][] = $field;
            } else {
                if(function_exists('register_setting')) register_setting( 'hiweb-input-options', $id );
                $attr['fields'][$id] = $this->getArr_field($field,$id);
                $attr['fields'][$id]['html'] = $this->getHtml_field($id, $attr['fields'][$id],$id);
                $attr['ids'][] = $id;
            }
        }
        return hiweb()->file()->getHtml_fromTpl($attr);
    }


    /**
     * Возвращает поля настроек
     * @param $fields - массив полей array(id => ffieldArr, id2 => ...)
     * @param string $title - титл настроек
     * @param null $post - post объект, если нужно взять значения из мета данных
     * @return bool|string|void
     */
    public function getHtml_fields($fields, $title = '',$post = null){
        if(!is_array($fields)) return false;
        ///
        $attr = array('title' => $title);
        ///
        foreach($fields as $id => $field){
            if(!is_array($field)){
                $attr['fields'][] = $field;
            } else {
                $attr['fields'][$id] = $this->getArr_field($field);
                $attr['fields'][$id]['default'] = hiweb()->string()->getStr_ifEmpty( get_post_meta($post->ID,$id,1), $attr['fields'][$id]['default'] );
                $attr['fields'][$id]['html'] = $this->getHtml_field($id, $attr['fields'][$id],$id);
                $attr['ids'][] = $id;
            }
        }
        return hiweb()->file()->getHtml_fromTpl($attr);
    }


    /**
     * Возвращает массив поля
     * @param array $field - массив поля
     * @param string $getOptVal_byId - вставить значение из опций, указав ID опции
     * @return array
     */
    private function getArr_field($field, $getOptVal_byId = null){
        $field = hiweb()->array2()->mergeRecursive($this->def_field,$field);
        $field['type'] = hiweb()->getVal_fromArr($field,'type',$this->def_fieldType);
        ///Options Value
        if(!hiweb()->string()->isEmpty($getOptVal_byId)) $field['value'] = get_option($getOptVal_byId, $field['default']);
        ///Value Def
        if(is_null($field['value'])) { $field['value'] = hiweb()->getVal_fromArr($field,'default',''); }
        ///Tags html
        if(is_array($field['tags'])) {
            $field['tagsHtml'] = array();
            foreach($field['tags'] as $k => $v) {
                if(!is_string($k)) { $field['tagsHtml'] = $v; }
                elseif(!hiweb()->string()->isEmpty($k)) { $field['tagsHtml'][] = "$k=\"".addslashes( $v )."\""; }
            }
            $field['tagsHtml'] = implode(' ', $field['tagsHtml']);
        }
        return $field;
    }


    /**
     * Возвращает html поля
     * @param $id - индификатор поля
     * @param array $fieldArr - массив поля
     * @param null $optionsId - индификатор опций
     * @return bool | string
     *
     * @version 1.2
     */
    public function getHtml_field($id, $fieldArr=array(), $optionsId = null){
        if(hiweb()->string()->isEmpty($id)) { hiweb()->console()->error('$id имеет пустое значение и тип переменной ['.gettype($id).']',1); return false; }
        ///
        if(!is_array($fieldArr)) $fieldArr = array();
        $fieldArr['type'] = hiweb()->getVal_fromArr($fieldArr,'type',$this->def_fieldType);
        if(method_exists($this,'_'.$fieldArr['type'])) {
            $field = call_user_func(array($this,'_'.$fieldArr['type']),$id,$fieldArr,$optionsId);
            $trace = hiweb()->array2()->getVal(debug_backtrace(), 1);
            if(hiweb()->array2()->getVal($trace,'class') == 'hiweb_input' && ( hiweb()->array2()->getVal($trace,'class') == 'getHtml_options' || hiweb()->array2()->getVal($trace,'class') == 'getHtml_fields' ))
                return $field;
            else
                return hiweb()->file()->getHtml_fromTpl(array('field' => $field, 'fieldArr' => $fieldArr));
        } else { hiweb()->console()->error('Неизвестный тип поля ['.$fieldArr['type'].']',1); return false; }
    }


    /**
     * Вернуть html поля text
     * @param string $id - индификатор поля
     * @param array $field - массив поля
     * @param null $optionsId - по необходимости укажите ID из таблицы WP опций для вставки значения
     * @return string
     */
    public function _text($id,$field=array(),$optionsId = null){
        $field = $this->getArr_field($field,$optionsId);
        return hiweb()->file()->getHtml_fromTpl(get_defined_vars(),'types/_text');
    }


    public function _checkbox($id,$field=array(),$optionsId = null){
        hiweb()->file()->css('css/check-radio-boolean');
        $field = $this->getArr_field($field,$optionsId);
        return hiweb()->file()->getHtml_fromTpl(get_defined_vars(),'types/_checkbox');
    }

    public function _checkboxes($id,$field=array(),$optionsId = null){
        hiweb()->file()->css('css/check-radio-boolean');
        $field = $this->getArr_field($field, $optionsId);
        return hiweb()->file()->getHtml_fromTpl(get_defined_vars(), 'types/_checkboxes');
    }


    public function _textarea($id,$field=array(),$optionsId = null){
        $field = $this->getArr_field($field,$optionsId);
        return hiweb()->file()->getHtml_fromTpl(get_defined_vars(),'types/_textarea');
    }


    public function _select($id,$field=array(),$optionsId = null){
        $field = $this->getArr_field($field,$optionsId);
        return hiweb()->file()->getHtml_fromTpl(get_defined_vars(),'types/_select');
    }

    /**
     * Вернуть мультиселект
     * @param $id - индификатор поля
     * @param $field - массив поля
     * @param null $optionsId - индификатор опций WP
     * @return string|void
     */
    public function _multiselect($id,$field=array(),$optionsId = null){
        hiweb()->file()->js('js/jquery.multiple.select');
        hiweb()->file()->css('css/multiple-select');
        $field = $this->getArr_field($field,$optionsId);
        return hiweb()->file()->getHtml_fromTpl(get_defined_vars(),'types/_multiselect');
    }

    public function _roles($id,$field=array(),$optionsId = null){
        $field = $this->getArr_field($field, $optionsId);
        $field['options'] = array();
        foreach(get_editable_roles() as $role => $arr){
            $field['options'][$role] = hiweb()->getVal_fromArr($arr,'name','не определена');
        }
        switch(hiweb()->array2()->getVal($field,'sub_type')){
            case 'checkboxes': return $this->_checkboxes($id,$field,$optionsId);
            case 'multiselect': return $this->_multiselect($id,$field,$optionsId);
            case 'select': return $this->_select($id,$field,$optionsId);
        }
        return $this->_tokenize($id,$field,$optionsId);
    }


    public function _users($id, $field = array(), $optionsId = null){
        $field = $this->getArr_field($field, $optionsId);
        $field['options'] = array();
        foreach(hiweb()->wp()->getArr_users(null) as $user){
            $field['options'][$user['ID']] = $user['display_name'];
        }
        switch(hiweb()->array2()->getVal($field,'sub_type')){
            case 'checkboxes': return $this->_checkboxes($id,$field,$optionsId);
            case 'multiselect': return $this->_multiselect($id,$field,$optionsId);
            case 'select': return $this->_select($id,$field,$optionsId);
        }
        return $this->_tokenize($id, $field);
    }

    public function _tokenize($id,$field=array(),$optionsId = null){
        hiweb()->file()->js('js/jquery.tokenize');
        hiweb()->file()->css('css/jquery.tokenize');
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-sortable');
        $field = $this->getArr_field($field,$optionsId);
        return hiweb()->file()->getHtml_fromTpl(get_defined_vars(),'types/_tokenize');
    }


    public function _post_type($id,$field=array(),$optionsId = null){
        $field = $this->getArr_field($field,$optionsId);
        ///
        $allPostTypes = get_post_types(array(),'objects');
        $field['options'] = array();
        $filter = hiweb()->getVal_fromArr($field,'post_type');
        if(!is_null($filter) && !is_array($filter)) $filter = array($filter);
        foreach($allPostTypes as $type => $p){
            if(is_null($filter) || in_array($type, $filter)) $field['options'][$type] = hiweb()->getVal_fromArr($p, array('labels','name'));
        }
        return $this->_tokenize($id,$field,$optionsId);
    }


    public function _post($id,$field=array(),$optionsId = null){
        $field = $this->getArr_field($field,$optionsId);
        ///
        $allPosts = get_posts(array(
            'post_type' => hiweb()->getVal_fromArr($field,'post_type',array('post')),
            'posts_per_page' => -1
        ));
        $field['options'] = array();
        foreach($allPosts as $p){
            $field['options'][$p->ID] = trim(get_the_title($p->ID)) == '' ? hiweb()->array2()->getValNext($p, array('post_title','post_name'),'id:'.$p->ID) : get_the_title($p->ID);
            //$field['options'][$p->ID] .= ' <em>['.hiweb()->getVal_fromArr($p,'post_type').']</em>';
        }
        return $this->_select($id,$field,$optionsId);
    }


    public function _page($id,$field=array(),$optionsId = null){
        $field = $this->getArr_field($field,$optionsId);
        ///
        $allPosts = get_posts(array(
            'post_type' => hiweb()->getVal_fromArr($field,'post_type',array('page')),
            'posts_per_page' => -1
        ));
        $field['options'] = array();
        foreach($allPosts as $p){
            $field['options'][$p->ID] = trim(get_the_title($p->ID)) == '' ? hiweb()->array2()->getValNext($p, array('post_title','post_name'),'id:'.$p->ID) : get_the_title($p->ID);
            //$field['options'][$p->ID] .= ' <em>['.hiweb()->getVal_fromArr($p,'post_type').']</em>';
        }
        return $this->_select($id,$field,$optionsId);
    }


    public function _posts($id,$field=array(),$optionsId = null){
        $field = $this->getArr_field($field,$optionsId);
        ///
        $allPosts = get_posts(array(
            'post_type' => hiweb()->getVal_fromArr($field,'post_type',array('post','page')),
            'post_per_page' => -1
        ));
        $field['options'] = array();
        foreach($allPosts as  $p){
            $field['options'][$p->ID] = trim(get_the_title($p->ID)) == '' ? hiweb()->array2()->getValNext($p, array('post_title','post_name'),'id:'.$p->ID) : get_the_title($p->ID);
            //$field['options'][$p->ID] .= ' <em>['.hiweb()->getVal_fromArr($p,'post_type').']</em>';
        }
        return $this->_multiselect($id,$field,$optionsId);
    }


    public function _theme($id,$field=array(),$optionsId = null){
        $field['options'] = array();
        foreach(wp_get_themes() as $themeId => $obj){ $field['options'][$themeId] = wp_get_theme($themeId)->get('Name'); }
        return $this->_select($id,$field,$optionsId,$optionsId);
    }


    public function _datetime($id,$field=array(),$optionsId = null){
        hiweb()->file()->css('css/jquery.datetimepicker');
        hiweb()->file()->js('js/jquery.datetimepicker.full.min');
        ///
        $field = $this->getArr_field($field,$optionsId);
        return hiweb()->file()->getHtml_fromTpl(get_defined_vars(),'types/_datetime');
    }


    public function _date($id,$field=array(),$optionsId = null){
        hiweb()->file()->css('css/jquery.datetimepicker');
        hiweb()->file()->js('js/jquery.datetimepicker.full.min');
        ///
        $field = $this->getArr_field($field,$optionsId);
        return hiweb()->file()->getHtml_fromTpl(get_defined_vars(),'types/_date');
    }


    public function _time($id,$field=array(),$optionsId = null){
        hiweb()->file()->css('css/jquery.datetimepicker');
        hiweb()->file()->js('js/jquery.datetimepicker.full.min');
        ///
        $field = $this->getArr_field($field,$optionsId);
        return hiweb()->file()->getHtml_fromTpl(get_defined_vars(),'types/_time');
    }


    public function _clock($id,$field=array(),$optionsId = null){
        hiweb()->file()->css('css/clockpicker.standalone');
        hiweb()->file()->css('css/clockpicker');
        hiweb()->file()->js('js/clockpicker');
        ///
        $field = $this->getArr_field($field,$optionsId);
        return hiweb()->file()->getHtml_fromTpl(get_defined_vars(),'types/_clock');
    }


    public function _map($id,$field=array(),$optionsId = null){
        hiweb()->file()->js('https://api-maps.yandex.ru/2.1/?lang=ru_RU', 1, 1);
        ///
        $field = $this->getArr_field($field,$optionsId);
        return hiweb()->file()->getHtml_fromTpl(get_defined_vars(),'types/_map');
    }


    public function _taxonomy($id,$field=array(),$optionsId = null){
        $field['options'] = array();
        foreach(get_taxonomies() as $t){
            $field['options'][$t] = hiweb()->getVal_fromArr(get_taxonomy($t),'label',$t);
        }
        return $this->_select($id,$field,$optionsId,$optionsId);
    }


    public function _taxonomies($id,$field=array(),$optionsId = null){
        $field['options'] = array();
        foreach(get_taxonomies() as $t){
            $field['options'][$t] = hiweb()->getVal_fromArr(get_taxonomy($t),'label',$t);
        }
        return $this->_tokenize($id,$field,$optionsId,$optionsId);
    }

    public function _term($id,$field=array(),$optionsId = null){
        //return $this->_tokenize($id,$field,$optionsId,$optionsId);
    }

    /**
     * @param $id
     * @param array $field -> array(post_type => mix, post_taxonomy => mix)
     * @param null $optionsId
     * @return string|void
     */
    public function _terms_posts($id,$field=array(),$optionsId = null){
        $field = $this->getArr_field($field);
        if(is_null(hiweb()->array2()->getVal($field,'post_type'))) $field['post_type'] = array_keys(get_post_types());
        if(is_null(hiweb()->array2()->getVal($field,'post_taxonomy'))) $field['post_taxonomy'] = get_object_taxonomies($field['post_type']);
        foreach(get_terms($field['post_taxonomy'],array('hide_empty' => false,'childless' => true)) as $t){
            $terms[$t->term_id] = $t;
        }
        $field['options'] = $this->getArrPosts_byTerms($field['post_type']);
        $input = $this->_tokenize($id, $field);
        return hiweb()->file()->getHtml_fromTpl(get_defined_vars(), 'types/_taxonomies_posts');
    }


}
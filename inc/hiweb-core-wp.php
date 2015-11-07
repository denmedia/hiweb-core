<?php
/**
 * Created by PhpStorm.
 * User: denmedia
 * Date: 09.04.2015
 * Time: 10:36
 */


class hiweb_wp {


    public $parentIds = array();


    /**
     * Возвращает TRUE, если текущая страничка логина/регистрации
     * @return bool
     */
    public function is_login_page() {
        return in_array( $_SERVER['PHP_SELF'], array( '/wp-login.php', '/wp-register.php' ) );
    }


    public function is_ajax() {
        return defined( 'DOING_AJAX' ) && DOING_AJAX;
    }

    /**
     * Возвращает TRUE, если родительская функция вызвана из папки шаблонов
     * @param int $depth - укажите 1, если требуется проверить прородительскую функцию, вместо родительской
     * @return bool
     */
    public function is_callFromTemplateDir($depth = 1){
        $templateDir = hiweb()->file()->getStr_normalizeDirSeparates(dirname(get_template_directory()));
        $dbFile = hiweb()->file()->getStr_normalizeDirSeparates(hiweb()->array2()->getVal(debug_backtrace(), array($depth,'file')));
        return strpos($dbFile,$templateDir) !== false;
    }


    /**
     * Возвращает TRUE, если текущий пользователь - администратор
     * @return bool
     */
    public function is_administrator(){
        return ( current_user_can("administrator") ) ? true : false;
    }


    /**
     * Возвращает TRUE, если текущая страничка редактирования данного типа записей
     * @param $postTypeMix - название типа записи, либо массив типов array(type_1,type_2)
     *
     * @return bool
     */
    public function is_admin_postType($postTypeMix){
        if(!is_admin()) return false;
        ////
        if(!is_array($postTypeMix)) $postTypeMix = array($postTypeMix);
        $thisPostType = false;
        ///
        if(!is_null(hiweb()->request('post_type'))) $thisPostType = hiweb()->request('post_type');
        elseif(hiweb()->request('action') == 'edit') {
            if(!is_null(hiweb()->request('post'))) $thisPostType = get_post(intval(hiweb()->request('post')))->post_type;
        }
        return in_array($thisPostType, $postTypeMix);
    }

    /**
     * Возвращает список пользователей
     *
     * @param string $role - значения: administrator | editor | author | contributor
     *
     * @return array
     */
    public function getArr_users( $role = 'administrator' ) {
        $r     = array();
        $users = get_users( array( 'role' => $role ) );
        foreach ( $users as $i ) {
            $r[ $i->data->user_login ] = (array) $i->data;
        }

        return $r;
    }


    public function do_404() {
        header( 'HTTP/1.0 404 Not Found' );
        header( "Status: 404 Not Found" );
        status_header( 404 );
        nocache_headers();
        include( get_query_template( '404' ) );
        die();
    }

    /**
     * Генерация уникального слуга
     *
     * @param $slug
     * @param $post_ID
     * @param $post_status
     * @param $post_type
     *
     * @return string
     */
    public function getStr_postSlugFromName( $slug, $post_ID, $post_status, $post_type ) {
        return hiweb()->string()->getStr_allowSymbols( $slug );
    }


    /**
     * Вставить информацию о файле плагина в списке плагинов
     *
     * @param $plugin_meta
     * @param $plugin_file
     * @param $plugin_data
     * @param $status
     *
     * @return string|void
     */
    public function getStr_pluginRowMeta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
        return hiweb()->file()->getHtml_fromTpl( compact( array_keys( get_defined_vars() ) ) );
    }

    public function echoStr_pluginRowMeta( $plugin_meta, $plugin_file, $plugin_data, $status ) { echo $this->getStr_pluginRowMeta( $plugin_meta, $plugin_file, $plugin_data, $status ); }


    /** подключить CSS файл к WordPress */
    public function css($cssPath){ return hiweb()->file()->css($cssPath); }
    /** подключить JS файл к WordPress */
    public function js($jsPath){ return hiweb()->file()->js($jsPath); }


    /**
     * Дополнительные MIME типы, доступные для загрузки
     * @param $mimes
     *
     * @return mixed
     */
    public function getArr_uploadMimes($mimes,$user = true){
        if ( function_exists( 'current_user_can' ) )
            $unfiltered = $user ? user_can( $user, 'unfiltered_html' ) : current_user_can( 'unfiltered_html' );
        if ( !empty( $unfiltered ) ) {
            $mimes['swf'] = 'application/x-shockwave-flash';
        }
        return $mimes;
    }


    /**
     * Возврвщает массив родительских постов, либо FALSE
     * @param null $postId
     *
     * @return array|bool
     */
    public function getArr_parentsPostId($postId = null){
        $postId = intval($postId);
        if($postId == 0) $postId = get_the_ID();
        return $this->add_arrParentPostId(get_post_ancestors($postId));
    }

    /**
     * Добавить ID или массив ID постов, для выделения в навигации данного пункта, возвращэает весь массив ID
     * @param $postIds
     *
     * @return array
     */
    public function add_arrParentPostId($postIds){
        if(!is_array($postIds)) $postIds = array($postIds);
        $postIds = array_values($postIds);
        $this->parentIds = array_merge($this->parentIds, $postIds);
        $this->parentIds = array_unique($this->parentIds);
        return $this->parentIds;
    }


    /**
     * Returns the translated role of the current user. If that user has
     * no role for the current blog, it returns false.
     *
     * @return string The name of the current role
     **/
    public function getStr_currentUserRoleName() {
        global $wp_roles;
        $current_user = wp_get_current_user();
        $roles = $current_user->roles;
        $role = array_shift($roles);
        return isset($wp_roles->role_names[$role]) ? translate_user_role($wp_roles->role_names[$role] ) : false;
    }

    public function getStr_currentUserRole(){
        global $current_user;
        $user_roles = $current_user->roles;
        $user_role = array_shift($user_roles);
        return $user_role;
    }


    public function do_mail($to, $subject, $message, $attachments=array()){
        $r = wp_mail($to, $subject, $message, 'Content-Type: text/html; charset=UTF-8', $attachments);
        return $r;
    }


    /** Возвращает имя сайта */
    public function getStr_blogName(){ return get_option('blogname'); }

    /** Возвращает почту администратора */
    public function getStr_adminMail(){ return get_option('admin_email'); }

    /**
     * Возвращает массив URL частей
     * @return array|\ArrayObject
     */
    public function getArr_requestUri($key = null){ $r = explode('/',trim($_SERVER["REQUEST_URI"],'/')); return is_null($key) ? $r : $r[$key]; }

    /**
     * Возвращает номер текущей странички
     * @return int
     */
    public function getInt_pageNumber(){
        $r = 1;
        foreach(array_reverse($this->getArr_requestUri()) as $i){
            if($i == '') continue;
            if(is_numeric($i)) { $r = intval($i); break; } else { break; }
        }
        return $r;
    }

    /**
     * Возвращает смещение постов, учитывая текущую страничку сайта
     *
     * @param null $postsPerPage - количество постов на страничке
     * @param null $pageNow - текущая страничка сайта
     *
     * @return mixed|null
     */
    public function getInt_pageOffset($postsPerPage = null, $pageNow = null){
        $pageNow = intval($pageNow);
        if(intval($postsPerPage) < 1) $postsPerPage = get_option('posts_per_page');
        return intval($postsPerPage) * ( $pageNow < 1 ? ($this->getInt_pageNumber() - 1) : $pageNow );
    }


    /**
     * Возвращает ID всех страниц, в которых найдены соответствия строке
     * @param $strpos
     *
     * @return array
     */
    public function getArr_pagesIdsByContent($strpos){
        $r = array();
        if(!is_array($strpos)) $strpos = array($strpos);
        $posts = get_all_page_ids();
        foreach($posts as $id){
            $content = get_post($id)->post_content;
            foreach($strpos as $s) if(strpos($content, $s)!==false) $r[] = $id;
        }
        return array_unique($r);
    }


    public function getHtml_metabox($post, $metaFields){
        $values = array();
        foreach($metaFields as $field){
            $values[$field['id']] = get_post_meta($post->ID, $field['id'], true);
        }
        return hiweb()->file()->getHtml_fromTpl(array(
            'nonce' => wp_create_nonce(basename(__FILE__)),
            'values' => $values,
            'fields' => $metaFields
        ));
    }


    /**
     * Возвращает ID поста по слугу, либо NULL
     * @param $slug
     *
     * @return int|null
     */
    public function getInt_postID_fromSlug($slug){
        $post = get_page_by_path($slug);
        if ($post) return $post->ID;
        return null;
    }


    /**
     * Возвращает массив всех мета-данных ключей и их значений от определенного
     * @param $post_id
     *
     * @return array
     */
    function getArr_postMeta($post_id){
        global $wpdb;
        $r   =   array();
        $wpdb->query("SELECT `meta_key`, `meta_value` FROM $wpdb->postmeta WHERE `post_id` = $post_id");
        foreach($wpdb->last_result as $k => $v){ $r[$v->meta_key] =   $v->meta_value; }
        return $r;
    }


    /**
     * Выводит список всех плагинов
     * @param string $plugin_folder
     *
     * @return array
     */
    function getArr_plugins($plugin_folder = '') {
        require_once( BASE_DIR.'/wp-admin/includes/plugin.php' );
        $plugins = get_plugins($plugin_folder);
        foreach($plugins as $path => $plugin){
            if(!file_exists(WP_PLUGIN_DIR.'/'.$path)) unset($plugins[$path]);
        }
        return $plugins;
    }


    /**
     * Возвращает массив плагинов, сортированный по типу
     * @return array
     */
    function getArr_pluginsSort(){
        $r = array('active' => array(), 'deactive' => array());
        $plugins = $this->getArr_plugins();
        foreach($plugins as $path => $plugin){
            $r[ is_plugin_active($path) ? 'active' : 'deactive' ][$path] = array($plugin['Name'], $plugin['Version']);
        }
        return $r;
    }


    /**
     * Установить зацепку для AJAX в WP
     * @param $action
     * @param $function
     * @param bool $noPriv
     */
    public function ajax($action, $function, $noPriv = true){
        add_action( 'wp_ajax_'.$action, $function );
        if($noPriv) add_action( 'wp_ajax_nopriv_'.$action, $function );
    }


    /**
     * Возращает массив изображений из подразделов ID поста
     *
     * @param $pid - IP поста/странички
     * @param string $thumbSize - размер изображения
     * @param bool $sortByPostId - сортировать по ID постов/страниц
     *
     * @return array
     */
    public function getArr_imagesSrc_fromPostParentId($pid, $thumbSize = 'thumbnail', $sortByPostId = true){
        $postsChi = get_children( array(
            'post_parent' => $pid,
            'orderby' => 'menu_order'
        ) );
        $postsChi = array_reverse($postsChi);
        $r = array();
        foreach($postsChi as $post){
            $images = $this->getArr_imagesSrc_fromPostId($post, $thumbSize);
            if($sortByPostId) $r[$post->ID] = $images; else $r = $r + $images;
        }
        return $r;
    }


    /**
     * Возвращает массив изображений, найденных в галлереях одного раздела
     * @param $postOrId
     * @param string $thumbSize
     *
     * @return array
     */
    public function getArr_imagesSrc_fromPostId($postOrId, $thumbSize = 'thumbnail'){
        if(!is_object($postOrId)) $postOrId = get_post($postOrId);
        $r = array();
        ///Thumbnail
        $thumb = wp_get_attachment_image_src( get_post_thumbnail_id($postOrId->ID), $thumbSize );
        if(!empty($thumb)) $r[] = $thumb[0];
        ///Gallery Shortcode
        $tmp = array();
        foreach($this->shortcodeParser( $tmp, $postOrId->post_content ) as $shortcode){
            if($shortcode['name'] == 'gallery' && isset($shortcode['atts']) && isset($shortcode['atts']['ids'])){
                $ids = explode(',',$shortcode['atts']['ids']);
                if(isset($ids[0])) foreach($ids as $id){
                    $img = array_shift( wp_get_attachment_image_src($id, $thumbSize) );
                    if(!empty($img)) $r[] = $img;
                }
            }
        }
        ///Images Src
        $dom = hiweb()->html()->str_get_html('<html><body>'.$postOrId->post_content.'</body></html>');
        $aImages = $dom->find('a > img');
        foreach($aImages as $k => $v){ $r[] = $v->parent()->href; }
        ////
        $r = array_unique($r);
        return $r;
    }


    /**
     * WP Short Code Parser
     * return hiweb_wp_shortcodeParser
     * @param $output
     * @param $text
     * @param bool $child
     * @return array
     */
    public function shortcodeParser(&$output, $text, $child = false){
        static $class = null;
        if(is_null($class)) { $class = new hiweb_wp_shortcodeParser(); }
        return $class->the_shortcodes($output, $text, $child);
    }


    /**
     * WP Menu Nav Walker
     * return hiweb_wp_navMenu
     */
    public function walker(){
        static $class = null;
        if(is_null($class)) { $class = new hiweb_wp_navMenu(); }
        return $class;
    }


    /**
     * Удалить шорткод по имени из контента
     * @param $content
     * @param $shortcodeName
     * @return string
     */
    function getStr_stripShortcodeByName($content, $shortcodeName) {
        global $shortcode_tags;
        $stack = $shortcode_tags;
        $shortcode_tags = array($shortcodeName => 1);
        $content = strip_shortcodes($content);
        $shortcode_tags = $stack;
        return $content;
    }

    /**
     * Возвращает строку шорткода из массива array(name=>shortcodeName, arrs=>attsArr, content)
     * @param array $shortcodeArr
     * @return string
     */
    function getStr_shortcodeFromArr($shortcodeArr){
        $sc = '['.$shortcodeArr['name'];
        if(count($shortcodeArr['atts']) > 0) {
            $scArrt = array();
            foreach($shortcodeArr['atts'] as $attrKey => $attrVal){ $scArrt[] = "$attrKey=\"$attrVal\""; }
            $sc .= ' '.implode(' ', $scArrt);
        }
        if($shortcodeArr['content'] != '') $sc .=']'.$shortcodeArr['content'].'[/'.$shortcodeArr['name'];
        $sc .= ']';
        return $sc;
    }


    /**
     * Возвращает вырезанный шорткод
     * @param string $content
     * @param string $shortcodeName - имя шорткода, напрмиер <b>gallery</b>
     * @param int $offset = -1 - возвращает массив найденных шорткодов, >0 - индекс(ключ) найденного шорткода
     * @return array | string
     */
    public function getStr_shortcode(&$content, $shortcodeName, $offset = -1){
        $this->shortcodeParser($shortcodes, $content);
        $contentArr = preg_split( '/'.get_shortcode_regex().'/s', $content );
        $contentR = array_shift($contentArr);
        ///Shortcodes
        $r = array();
        $index = 0;
        if(!is_array($shortcodes)) return intval($offset) < 0 ? array() : '';
        foreach($shortcodes as $shortcodeArr){
            if($shortcodeArr['name'] == $shortcodeName && (intval($offset) < 0 || intval($offset) == $index)) { $r[] = $this->getStr_shortcodeFromArr($shortcodeArr); }
            else { $contentR .= $this->getStr_shortcodeFromArr($shortcodeArr); }
            if($shortcodeArr['name'] == $shortcodeName) $index ++;
            $contentR .= array_shift($contentArr);
        }
        ///Content
        $content = $contentR;
        return intval($offset) < 0 ? $r : array_shift($r);
    }


    /**
     * Возвращает ID изображения из пути
     * @param $image_src
     * @return mixed
     */
    public function getMix_idAttachFromSrc($image_src) {
        global $wpdb;
        $query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$image_src'";
        $id = $wpdb->get_var($query);
        return $id;
    }


    /**
     * Генерация табов для WP
     * @param array $tabsArray
     * @return bool
     */
    public function getHtml_tabs($tabsArray = array(array('name' => '{lang}Название таба{/lang}', 'content' => '{lang}Содержимое таба{/lang}', 'slug' => '', 'select' => 1)), $useJs = true){
        if(!is_array($tabsArray)) return false;
        $defTab = array('name' => 'таб', 'content' => '', 'slug' => '', 'select' => 0);
        $rTabs = array();
        ///
        $selectCount = 0;
        $content = '';
        foreach($tabsArray as $slug => $tab){
            if(!is_array($tab)) { $tab = array('name' => $slug, 'content' => $tab); }
            elseif(isset($tab[0]) && is_object($tab[0]) && is_string($tab[1])) { $tab = array('name' => $slug, 'content' => $tab[0]->{$tab[1]}()); }
            elseif(isset($tab[0]) && is_array($tab[0]) && isset($tab[0][0]) && is_object($tab[0][0]) && is_string($tab[0][1])) { $tab = array('name' => $slug, 'content' => $tab[0][0]->{$tab[0][1]}( isset($tab[1]) ? $tab[1] : null )); }
            $tab = hiweb()->array2()->merge($defTab, $tab);
            ///Slug
            $slug = trim($slug) == '' ? count($rTabs) - 1 : $slug;
            $slug = trim($tab['slug']) == '' ? hiweb()->string()->getStr_allowSymbols($tab['name']) : $tab['slug'];
            ///url
            $tab['url'] = hiweb()->string()->getStr_urlQuery(null, array('tab' => $slug));
            ///select
            if(hiweb()->request('tab') == $slug) { $content = $tab['content']; $tab['select'] = 1; }
            if($tab['select']) { $tab['select'] = $selectCount < 1 ? true : false; $selectCount ++; }
            $rTabs[$slug] = $tab;
        }
        if($selectCount == 0){ $rTabs[key($rTabs)]['select'] = true; $content = $rTabs[key($rTabs)]['content']; }
        return hiweb()->file()->getHtml_fromTpl(array('tabs' => $rTabs, 'usejs' => $useJs, 'content' => $content));
    }


    /**
     * Возвращает виджеты в массиве
     * @param $index - ID сайдбара
     * @param bool $removeAfterBefore - Удалить префикс и суфик виджета, напрмиер LI-теги
     * @return bool|mixed|string|void
     *
     * @version 1.0
     */
    public function getArr_widgetsFromSidebar($index, $removeAfterBefore = true){
        global $wp_registered_sidebars, $wp_registered_widgets;
        $r = array();
        if (is_int($index)) {
            $index = "sidebar-$index";
        } else {
            $index = sanitize_title($index);
            foreach ((array) $wp_registered_sidebars as $key => $value) {
                if (sanitize_title($value['name']) == $index) {
                    $index = $key;
                    break;
                }
            }
        }
        $sidebars_widgets = wp_get_sidebars_widgets();
        if (empty($wp_registered_sidebars[$index]) || empty($sidebars_widgets[$index]) || !is_array($sidebars_widgets[$index])) {
            do_action('dynamic_sidebar_before', $index, false);
            do_action('dynamic_sidebar_after', $index, false);
            return apply_filters('dynamic_sidebar_has_widgets', false, $index);
        }
        do_action('dynamic_sidebar_before', $index, true);
        $sidebar = $wp_registered_sidebars[$index];
        $did_one = false;
        foreach ((array) $sidebars_widgets[$index] as $id) {
            ob_start();
            if (!isset($wp_registered_widgets[$id]))
                continue;
            $params     = array_merge(array(
                array_merge($sidebar, array(
                    'widget_id' => $id,
                    'widget_name' => $wp_registered_widgets[$id]['name']
                ))
            ), (array) $wp_registered_widgets[$id]['params']);
            $classname_ = '';
            foreach ((array) $wp_registered_widgets[$id]['classname'] as $cn) {
                if (is_string($cn))
                    $classname_ .= '_' . $cn;
                elseif (is_object($cn))
                    $classname_ .= '_' . get_class($cn);
            }
            $classname_                 = ltrim($classname_, '_');
            $params[0]['before_widget'] = $removeAfterBefore ? '':sprintf($params[0]['before_widget'], $id, $classname_);
            $params[0]['after_widget']  = $removeAfterBefore ? '':$params[0]['after_widget'];
            $params                     = apply_filters('dynamic_sidebar_params', $params);
            $callback                   = $wp_registered_widgets[$id]['callback'];
            do_action('dynamic_sidebar', $wp_registered_widgets[$id]);
            if (is_callable($callback)) {
                call_user_func_array($callback, $params);
                $did_one = true;
            }
            $r[$id] = ob_get_clean();
        }
        do_action('dynamic_sidebar_after', $index, true);
        $did_one = apply_filters('dynamic_sidebar_has_widgets', $did_one, $index);
        return $r;
    }


    /**
     * Возвращает виджеты, равномерно распределив в колонках bootstrap
     * @param $index
     * @param array $bootstrapClasses
     * @param bool $useRow
     * @return string
     *
     * @version 1.0
     */
    public function getHtml_widgetsBootstrapCol($index, $bootstrapClasses = array('xs' => 1, 'sm' => 2, 'md' => 4, 'lg' => 6), $useRow = true){
        $r = '';
        $widgets = $this->getArr_widgetsFromSidebar($index, true);
        $classes = array();
        if(is_array($bootstrapClasses)) foreach($bootstrapClasses as $class => $col){ $classes[] = in_array($class,array('xs','sm','md','lg')) ? 'col-'.$class.'-'.( $col > count($widgets) ? 12 / count($widgets) : 12 / $col ) : $class; }
        $class = implode(' ', $classes);
        foreach($widgets as $widget){
            $r .= "<div class='$class'>$widget</div>";
        }
        return $useRow ? "<div class='row'>$r</div>" : $r;
    }


    /**
     * Произвести смену базовый URL на новый
     *
     * @version 1.1
     */
    public function do_changeBaseUrl(){
        global $wpdb;
        $oldUrl = get_option('siteurl');
        $newUrl = BASE_URL;
        update_option('siteurl',$newUrl);
        update_option('home',$newUrl);
        $query = "UPDATE ".$wpdb->prefix."posts SET guid = REPLACE(guid, '$oldUrl','$newUrl')";
        $wpdb->query($query);
        $query = "UPDATE ".$wpdb->prefix."posts SET post_content = REPLACE(post_content, '$oldUrl', '$newUrl');";
        $wpdb->query($query);
        $query = "UPDATE ".$wpdb->prefix."postmeta SET post_content = REPLACE(meta_value, '$oldUrl', '$newUrl');";
        $wpdb->query($query);

        $this->do_flush_rewrite_rules();

        hiweb()->cacheByFileClear();

        exit('<h1 style="text-align: center">hiWeb: change `Base URL`. Please wait, page is reload...</h1><script>location.reload();</script>');
    }


    /**
     * Перезаписать правила роутера WP
     */
    public function do_flush_rewrite_rules(){
        hiweb()->file()->inc(BASE_DIR.'/wp-admin/includes/file.php');
        hiweb()->file()->inc(BASE_DIR.'/wp-admin/includes/misc.php');
        flush_rewrite_rules();
    }


    /**
     * Удалить все записи по типу
     * @param $postType - тип записи
     * @param array|string $postStatus - статус записи
     */
    public function do_removePostByType($postType, $postStatus = array('any', 'trash')){
        $posts = get_posts(array(
            'post_type' => $postType,
            'posts_per_page' => -1,
            'post_status' => $postStatus
        ));

        foreach($posts as $p){
            wp_delete_post( $p->ID, true);
        }
    }


    private $do_themeSwitchStr;

    /**
     * Сменить динамически тему, не внося изменений в базу данных (не сохранять изменение)
     * @param string $themeSlug - папка темы
     * @return bool
     * @version 1.0
     */
    public function do_themeSet($themeSlug = null){
        if(!hiweb()->string()->isEmpty($this->do_themeSwitchStr)){
            return $this->do_themeSwitchStr;
        } elseif(!hiweb()->string()->isEmpty($themeSlug) && hiweb()->string()->isEmpty($this->do_themeSwitchStr)) {
            $this->do_themeSwitchStr = $themeSlug;
            add_filter('template', array(hiweb()->wp(),'do_themeSet'));
            add_filter('option_template', array(hiweb()->wp(),'do_themeSet'));
            add_filter('option_stylesheet', array(hiweb()->wp(),'do_themeSet'));
        } else return !hiweb()->string()->isEmpty($this->do_themeSwitchStr);
    }


    public function do_templateSet($phpTemplateName = null){
        //TODO написать функцию
    }


    /**
     * Возвращает слуг текущей темы
     * @return mixed|void
     */
    public function getStr_currentTheme(){
        return get_option('template');
    }


    /**
     * Возвращает настройки темы
     * @param null $themeSlug
     * @return array
     */
    public function getArr_themeMod($themeSlug = null){
        if(is_null($themeSlug)) $themeSlug = $this->getStr_currentTheme();
        return get_option('theme_mods_'.$themeSlug);
    }


    /**
     * Возвращает локации указанной темы
     * @param null $themeSlug
     * @return mixed
     */
    public function getArr_locations($themeSlug = null){
        return hiweb()->getVal_fromArr( $this->getArr_themeMod($themeSlug),'nav_menu_locations' );
    }

    public function getArr_navMenuItems_byLocation($location_id, $themeSlug = null){
        $r = array();
        $menus = wp_get_nav_menus();
        $menu_locations = $this->getArr_locations($themeSlug);
        if (isset($menu_locations[ $location_id ])) {
            foreach ($menus as $menu) {
                if ($menu->term_id == $menu_locations[ $location_id ]) {
                    $r[$menu->slug] = wp_get_nav_menu_items($menu);
                }
            }
        }
        return $r;
    }

}


/**
 * Class wp_navMenu
 */
class hiweb_wp_navMenu extends Walker_Nav_Menu {
    function start_el(&$output, $item, $depth = 0, $args = Array(), $id = 0) {
        $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

        $class_names = $value = '';

        $classes = empty( $item->classes ) ? array() : (array) $item->classes;
        if(in_array('menu-item-has-children', $classes)) hiweb()->file()->js('hiweb-submenu');
        if(in_array('menu-item-has-children', $classes)) hiweb()->file()->css('hiweb-submenu');

        $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );
        $class_names = ' class="' . esc_attr( $class_names ) . '"';

        $selectDataTag = get_the_ID() == $item->object_id ? 'data-select="1"' : '';
        $selectSubDataTag = in_array($item->object_id, hiweb()->wp()->getArr_parentsPostId()) ? 'data-subselect="1"' : '';

        $output .= $indent . '<li data-depth="'.$depth.'" '.$selectDataTag.' '.$selectSubDataTag.' id="menu-item-' . $item->ID . '"' . $value . $class_names . '>';

        $attributes = ! empty( $item->attr_title ) ? ' title="' . esc_attr( $item->attr_title ) . '"' : '';
        $attributes .= ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
        $attributes .= ! empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . '"' : '';
        $attributes .= ! empty( $item->url ) ? ' href="' . esc_attr( $item->url ) . '"' : '';
        $attributes .= ! empty( $item->custom ) ? $item->custom : '';

        $prepend     = '';
        $append      = '';
        $description = ! empty( $item->description ) ? '<span>' . esc_attr( $item->description ) . '</span>' : '';

        if ( $depth != 0 ) {
            $description = $append = $prepend = "";
        }

        $item_output = $args->before;
        $item_output .= '<a data-depth="'.$depth.'"' . $attributes . ' >';
        $item_output .= $args->link_before . $prepend . apply_filters( 'the_title', $item->title, $item->ID ) . $append;
        $item_output .= $description . $args->link_after;
        $item_output .= '</a>';
        $item_output .= $args->after;

        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }
}



class hiweb_wp_shortcodeParser{

    private function get_pattern( $text ) {
        $pattern = get_shortcode_regex();
        preg_match_all( "/$pattern/s", $text, $c );
        return $c;
    }

    private function parse_atts( $content ) {
        $content = preg_match_all( '/([^ ]*)=(\'([^\']*)\'|\"([^\"]*)\"|([^ ]*))/', trim( $content ), $c );
        list( $dummy, $keys, $values ) = array_values( $c );
        $c = array();
        foreach ( $keys as $key => $value ) {
            $value = trim( $values[ $key ], "\"'" );
            $type = is_numeric( $value ) ? 'int' : 'string';
            $type = in_array( strtolower( $value ), array( 'true', 'false' ) ) ? 'bool' : $type;
            switch ( $type ) {
                case 'int': $value = (int) $value; break;
                case 'bool': $value = strtolower( $value ) == 'true'; break;
            }
            $c[ $keys[ $key ] ] = $value;
        }
        return $c;
    }

    public function the_shortcodes( &$output, $text, $child = false ) {

        $patts = $this->get_pattern( $text );
        $t = array_filter( $this->get_pattern( $text ) );
        if ( ! empty( $t ) ) {
            list( $d, $d, $parents, $atts, $d, $contents ) = $patts;
            $out2 = array();
            $n = 0;
            foreach( $parents as $k=>$parent ) {
                ++$n;
                $name = $child ? 'child' . $n : $n;
                $t = array_filter( $this->get_pattern( $contents[ $k ] ) );
                $t_s = $this->the_shortcodes( $out2, $contents[ $k ], true );
                $output[ $name ] = array( 'name' => $parents[ $k ] );
                $output[ $name ]['atts'] = $this->parse_atts( $atts[ $k ] );
                $output[ $name ]['original_content'] = $contents[ $k ];
                $output[ $name ]['content'] = ! empty( $t ) && ! empty( $t_s ) ? $t_s : $contents[ $k ];
            }
        }
        return is_array($output) ? array_values( $output ) : array();
    }

}
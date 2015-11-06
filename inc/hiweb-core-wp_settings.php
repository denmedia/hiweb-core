<?php
/**
 * Created by PhpStorm.
 * User: denmedia
 * Date: 08.04.2015
 * Time: 23:36
 */


class hiweb_wp_settings {

    public function __construct(){
        ///Languages
        add_action('plugins_loaded',array($this,'load_plugin_textdomain'));
        ///Admin Menu Settings
        add_action('admin_menu',array($this,'do_createAdminMenu'));
        ///Plugins Page Line Settings
        add_filter('plugin_action_links', array($this,'do_pluginsPage_linkShow'), 2, 2);
        ///Convert SLUG to Allow Symbols
        if(get_option('hiweb_settings_cyt2lat', 'on') != ''){ add_filter( 'wp_unique_post_slug', array(hiweb()->wp(), 'getStr_postSlugFromName'), 10, 4 ); }
        ///Add BASE tag to HEAD tag
        if(get_option('hiweb_settings_head_base', 'on') != ''){ add_action('wp_head', array(hiweb()->build(),'getEcho_head')); }
        ///Plugins Path Line
        if(get_option('hiweb_cms_plugins_path', 'on') != ''){ add_filter( 'plugin_row_meta', array(hiweb()->wp(),'echoStr_pluginRowMeta'), 100, 4 ); }
        ///Support post thumbnails
        if(get_option('hiweb_cms_support_thumbnails', 'on') != ''){ add_theme_support('post-thumbnails'); }
        ///Custome Title
        if(get_option('hiweb_cms_title', 'on') != ''){
            add_action( 'add_meta_boxes', array($this,'do_titleAddMetaBox'));
            add_action( 'save_post', array($this,'do_titlePostSave') );
            add_filter( 'the_title', array($this,'do_titleFilter'), 10, 2 );
        }
        ///Ajax AdminMenu Save
        hiweb()->wp()->ajax('hiweb-settings-cms-adminmenu', array(hiweb()->settings(), 'do_cms_adminmenu_save'), false);
        ///Admin MENU list change
        add_action('admin_init', array(hiweb()->settings(), 'do_cms_adminmenu_change'));
        ///Admin NAV-MENUS
        if(get_option('hiweb_cms_support_menus', 'on') != ''){ add_action('admin_menu', array($this,'do_adminmenuMenus')); }
        ///Admin Widgets
        if(get_option('hiweb_cms_support_widgets', 'on') != ''){
            add_action('admin_menu', array($this,'do_adminmenuWidgets'));
            if(basename($_SERVER['SCRIPT_NAME']) == 'widgets.php') add_action('admin_enqueue_scripts', array($this,'do_adminmenuWidgets2'));
        }
        ///Support extend types of posts
        if(get_option('hiweb_cms_support_postformats', 'on') != ''){ add_theme_support( 'post-formats', array(
            'aside', 'image', 'video', 'quote', 'link', 'gallery', 'status', 'audio', 'chat', 'post'
        ) ); }
        ///Add scripts to footer
        if(hiweb()->settings()->getMix_optionSettings('hiweb_settings_script_footer')!=''){ add_action('wp_footer', array($this,'echoStr_scriptFooter')); }
        ///Change Site/Home URL
        if(hiweb()->settings()->getMix_optionSettings('hiweb_cms_title_posttypes') != ''){
            $base_dir = get_option('hiweb_cms_autochange_basedir', '');
            if(trim((string)$base_dir) == ''){
                update_option('hiweb_cms_autochange_basedir', BASE_DIR);
            }
            elseif($base_dir != BASE_DIR) {
                update_option('hiweb_cms_autochange_basedir', BASE_DIR);
                add_action('wp',array(hiweb()->wp(),'do_changeBaseUrl'));
            }
        }
        ///TPL All Contents
        add_filter('the_content', array($this,'getStr_theContent'));
        ///hiweb->input->ajax
        if(is_admin()) hiweb()->wp()->ajax('hiweb-input',array(hiweb()->input(),'ajax'), false);
    }

    public function getStr_theContent($content){
        return hiweb()->file()->getHtml_fromTplStr($content);
    }

    /**
     * Подключение файла языка
     */
    public function load_plugin_textdomain(){
        load_plugin_textdomain( 'hiweb-core', false,'hiweb-core/lang/' );
    }


    /**
     * Создание админ-меню
     */
    public function do_createAdminMenu(){
        global $submenu;
        add_menu_page('hiWeb '.__('Настройки','hiweb-core'), 'hiWeb '.__('Настройки','hiweb-core'), 8, 'hiweb-settings', array(hiweb()->settings(),'echoHtml_settingsDashboard'));
        add_submenu_page('hiweb-settings', 'hiWeb '.__('Плагины и Ассеты','hiweb-core'), __('Плагины и Ассеты','hiweb-core'), 8, 'hiweb-plugins', array(hiweb()->plugins(),'echoHtml_dashboard'));
        add_options_page('hiWeb '.__('Настройки','hiweb-core'), 'hiWeb '.__('Настройки','hiweb-core'), 8, 'hiweb-settings-2', array(hiweb()->settings(), 'echoHtml_settingsDashboard'));
        add_plugins_page('hiWeb '.__('Плагины и Ассеты','hiweb-core'), 'hiWeb '.__('Плагины и Ассеты','hiweb-core'), 'read', 'hiweb-plugins-2', array(hiweb()->plugins(),'echoHtml_dashboard'));
        ///Support options.php page
        if(get_option('hiweb_cms_adminmenu_options', '') != ''){
            add_menu_page(__('Опции WP','hiweb-core'), __('Опции WP','hiweb-core'), 8, 'options.php');
            add_action('admin_enqueue_scripts', array($this,'do_adminmenuWidgets2'));
        }
        //if(isset($submenu['hiweb-settings'])) unset($submenu['hiweb-settings'][0]);
        //remove_submenu_page('hiweb-settings','hiweb-settings');
    }


    /**
     * ополнительная строка для каждого плагина на старничке плагинов
     * @param $actions
     * @param $file
     * @return mixed
     */
    public function do_pluginsPage_linkShow($actions,$file){
        if(false !== strpos($file, 'hiweb-core/hiweb-core.php'))
            $actions['settings'] = '<a class="hiweb-core-admin-pluginpage-settings" href="admin.php?page=hiweb-settings">hiWeb '.__('настройки').'</a>';
        elseif(false !== strpos($file, 'hiweb')) $actions['plugins'] = '<a class="hiweb-core-admin-pluginpage-plugins" href="admin.php?page=hiweb-plugins">hiWeb '.__('плагины').'</a>';
        return $actions;
    }


    public function do_titleAddMetaBox($post_type){
        if( hiweb()->settings()->getMix_optionSettings('hiweb_cms_title') == '' ) return;
        $matchPT = in_array($post_type, hiweb()->settings()->getMix_optionSettings('hiweb_cms_title_posttypes'));
        if(hiweb()->settings()->getMix_optionSettings('hiweb_cms_title_excludemod') != '') $matchPT = !$matchPT;
        if(!$matchPT) return;
        add_meta_box('hiweb_cms_title',__('Заголовок страницы','hiweb-core'),array(hiweb()->settings(), 'echo_metaboxTitle'), null, 'side', 'high');
    }

    public function do_titlePostSave($post_id){
        if ( ! wp_verify_nonce( hiweb()->request('hiweb_cms_title_nonce'), 'hiweb_cms_title_nonce' ) ) return $post_id;
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;
        if ( 'page' == hiweb()->request('post_type') && ! current_user_can( 'edit_page', $post_id ) ) { return $post_id; }
        elseif( ! current_user_can( 'edit_post', $post_id ) ) { return $post_id; }
        update_post_meta( $post_id, 'hiweb_cms_title_mod', sanitize_text_field( hiweb()->request('hiweb_cms_title_mod') ) );
        update_post_meta( $post_id, 'hiweb_cms_title', sanitize_text_field( hiweb()->request('hiweb_cms_title') ) );
        return $post_id;
    }

    public function do_titleFilter($title, $id = null){
        if(!is_null($id) && !is_admin()){
            $meta = array_merge(array('hiweb_cms_title_mod' => 'default','hiweb_cms_title' => ''), hiweb()->wp()->getArr_postMeta($id));
            if($meta['hiweb_cms_title_mod'] == 'default') return $title;
            if($meta['hiweb_cms_title_mod'] == 'none') return '';
            if($meta['hiweb_cms_title_mod'] == 'custom') return $meta['hiweb_cms_title'];
        } else {
            return $title;
        }
        return $title;
    }


    public function do_adminmenuMenus(){
        global $parent_file;
        add_theme_support('menus');
        add_menu_page(__('Menus'), __('Menus'), 10,'nav-menus.php',null,'dashicons-menu','4.5');
        remove_submenu_page('themes.php','nav-menus.php');
        if(basename($_SERVER['SCRIPT_NAME']) == 'nav-menus.php') $parent_file = false;
    }


    public function do_adminmenuWidgets(){
        add_theme_support('widgets');
        remove_submenu_page('themes.php','widgets.php');
        add_menu_page(__('Widgets'), __('Widgets'), 10,'widgets.php',null,'dashicons-welcome-widgets-menus','40.5');
    }

    public function do_adminmenuWidgets2(){
        global $parent_file; $parent_file = 'widgets';
    }


    public function echoStr_scriptFooter(){
        echo get_option('hiweb_settings_script_footer');
    }


}











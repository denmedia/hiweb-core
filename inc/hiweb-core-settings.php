<?php
/**
 * Created by PhpStorm.
 * User: denmedia
 * Date: 08.04.2015
 * Time: 23:24
 */


class hiweb_settings {


    public $settings = array(
    );

    public $settingsCms = array(
        '{lang}Перенос сайта{/lang}',
        'hiweb_cms_autochange_baseurl' => array(
            'default' => 'on',
            'type' => 'checkbox',
            'name' => '{lang}Быстрый перенос сайта на новый хостинг/домен{/lang}{helpPoint}{lang}Для миграции сайта перенесите все файлы и базу данных сайта на новый сервер. Данная функция автоматически заменит и обновит URL сайта и быстрые ссылки на посты/страницы.{/lang}{/helpPoint}',
            'description' => '{lang}Автоматически менять базовый URL сайта, если путь до базовой папки изменился.<br>Текущая папка:{/lang} <b>{$_base_dir}</b><p>{lang}Для миграции сайта перенесите все файлы и базу данных сайта на новый сервер. Данная функция автоматически заменит и обновит URL сайта и быстрые ссылки на посты/страницы.{/lang}</p>'
        ),
        '{lang}Пункты админ-меню{/lang}',
        'hiweb_cms_support_menus' => array(
            'default' => 'on',
            'type' => 'checkbox',
            'name' => '{lang}Внешний вид{/lang} → {lang}Меню{/lang} {helpPointImage}adminmenu-menus{/helpPointImage}',
            'description' => '{lang}Добавляет пункт в админ-меню, позволяющий редактировать меню в шаблоне. Так же отображать этот пункт в сразу админ-панеле{/lang}'
        ),
        'hiweb_cms_support_widgets' => array(
            'default' => 'on',
            'type' => 'checkbox',
            'name' => '{lang}Внешний вид{/lang} → {lang}Виджеты{/lang} {helpPointImage}adminmenu-widgets{/helpPointImage}',
            'description' => '{lang}Добавляет пункт в админ-меню, позволяющий редактировать виджеты в шаблоне. Так же отображать этот пункт в сразу админ-панеле{/lang}'
        ),
        'hiweb_cms_adminmenu_options' => array(
            'default' => '',
            'type' => 'checkbox',
            'name' => '{lang}Админ меню{/lang} → {lang}Опции{/lang} {helpPointImage}adminmenu-options{/helpPointImage}',
            'description' => "{lang}Добавляет пункт в админ-меню, позволяющий редактировать основные настройки WordPress'а. Так же отображать этот пункт в сразу админ-панеле.{/lang}"
        ),
        '{lang}Редактор постов/страниц{/lang}',
        'hiweb_cms_title' => array(
            'default' => 'on',
            'type' => 'checkbox',
            'name' => '{lang}Записи{/lang} → {lang}Использовать кустомный титл{/lang} {helpPointImage}postedit-title{/helpPointImage}',
            'description' => '{lang}Добавить возможность указывать кустомный титл для записи/странички. В PHP шаблоне использовать так: <code>&lt;?php the_title(); ?&gt;</code>{/lang}'
        ),
        'hiweb_cms_title_excludemod' => array(
            'default' => 'on',
            'type' => 'checkbox',
            'name' => '{lang}Записи{/lang} → {lang}Исключать перечисленные ниже типы записей{/lang}',
            'description' => 'Отмете данный пункт, чтобы нижеследующие типы записей не использовали кастомный титл, в свою очередь все остальные типы будут использовать его. В ином случае',
            'display' => array(
                array(
                    'id' => 'hiweb_cms_title',
                    'value' => '',
                    'operator' => '!='
                )
            )
        ),
        'hiweb_cms_title_posttypes' => array(
            'default' => array('attachment','revision','nav_menu_item'),
            'type' => 'post_type',
            'name' => '{lang}Записи{/lang} → {lang}Типы записей, связанные с кастомным титлом{/lang}',
            'description' => '{lang}Выберите типы постов, в которых необходимо установить или не устанавливать кастомный титл{/lang}',
            'display' => array(
                array(
                    'id' => 'hiweb_cms_title',
                    'value' => '',
                    'operator' => '!='
                )
            )
        ),
        'hiweb_cms_support_thumbnails' => array(
            'default' => 'on',
            'type' => 'checkbox',
            'name' => '{lang}Записи{/lang} → {lang}Поддержка миниатюры{/lang} {helpPointImage}postedit-thumb{/helpPointImage}',
            'description' => '{lang}Включить возомжность загружать для поста/странички миниатюру{/lang}'
        ),
        'hiweb_cms_support_postformats' => array(
            'default' => 'on',
            'type' => 'checkbox',
            'name' => '{lang}Записи{/lang} → {lang}Поддержка кустомных типов записей{/lang} {helpPointImage}postedit-formats{/helpPointImage}',
            'description' => '{lang}Включить поддержку всех типов записей.{/lang} <a href="https://codex.wordpress.org/%D0%A4%D0%BE%D1%80%D0%BC%D0%B0%D1%82%D1%8B_%D0%B7%D0%B0%D0%BF%D0%B8%D1%81%D0%B5%D0%B9" target="_blank">{lang}Узнать больше{/lang}...</a>'
        ),
        /*'hiweb_cms_taxonomies_tagclouddisable' => array(
            'default' => 'on',
            'type' => 'checkbox',
            'name' => '{lang}Записи{/lang} → {lang}Таксономии{/lang} → {lang}Скрывать облако частоиспользуемых тегов{/lang}',
            'description' => '{lang}В разделе рубрик часто формируется облако популярных категорий, которые мешают нормальному восприятию формы. Этот пункт скрывает данное облако.{/lang}'
        ),*/

        '{lang}Преобразование символов{/lang}',
        'hiweb_settings_file_name' => array(
            'default' => 'on',
            'type' => 'checkbox',
            'name' => '{lang}Преобразование имен файлов в латинницу{/lang}',
            'description' => '{lang}Преобразовывать кирилицу и прочие недопустимые символы в названии загружаемых файлов в латинские буквы.{/lang}'
        ),
        'hiweb_settings_cyt2lat' => array(
            'default' => 'on',
            'type' => 'checkbox',
            'name' => '{lang}Преобразование ЧПУ{/lang}',
            'description' => '{lang}Преобразовывать кирилицу и прочие недопустимые символы ЧПУ постов и страничек в латинские буквы.{/lang}'
        ),

        '{lang}Прочие параметры WP{/lang}',
        /*'hiweb_cms_upload_mimes' => array(
            'default' => 'on',
            'type' => 'checkbox',
            'name' => '{lang}Медиафайлы{/lang} → {lang}Расширенный список типов файлов{/lang}',
            'description' => '{lang}Данный пункт позволяет загружать более расширенный список типов файлов{/lang}:'
        ),*/
        'hiweb_cms_plugins_path' => array(
            'default' => 'on',
            'type' => 'checkbox',
            'name' => '{lang}Плагины{/lang} → {lang}Отображение пути до файла PHP{/lang} {helpPointImage}plugins-path{/helpPointImage}',
            'description' => '{lang}Добавляет информативную строчку о файле для каждого из плагинов.{/lang}'
        ),

        'hiweb_settings_head_base' => array(
            'default' => 'on',
            'type' => 'checkbox',
            'name' => '{lang}Тег{/lang} &lt;BASE&gt; {helpPointImage}html-base{/helpPointImage}',
            'description' => '{lang}Указывать в шапке тэг &lt;BASE&gt;, указывающий на корень сайта.{/lang} <a href="http://htmlbook.ru/html/base" target="_blank">{lang}Узнать больше{/lang}...</a>'
        ),
        'hiweb_settings_script_footer' => array(
            'default' => '',
            'type' => 'textarea',
            'name' => '{lang}Скрипт для всех страничек{/lang}{helpPoint}{lang}Используйте данное поле для ввода JavaScript, который необходимо разместить на всех страничках сайта, исключая админ-панель, например: Google Analytics, Яндекс.Метрика{/lang}{/helpPoint}',
            'description' => '{lang}Сквозной HTML-код и скрипт счетчиков, отображаемый на всех страничках сайта. Данный скрипт будет выводиться в области футера.{/lang}',
            'tags' => array('rows' => 10, 'cols' => 50)
        )
    );

    public $def_cms_adminmenu = array();
    public $option_cms_adminmenu_name  = 'hiweb-settings-cms-adminemu';


    function __construct(){

    }


    public function getHtml_settingsDashboard(){
        return hiweb()->file()->getHtml_fromTpl(
            array('tabs' => hiweb()->build()->getHtml_tabs(array(
                '{lang}Основные настройки админки{/lang}{helpPoint}{lang}Основные настройки, управление дополнительными элементами админ-меню, дополнения для администратора{/lang}{/helpPoint}' => array(hiweb()->input(), 'getHtml_options', $this->settingsCms),
                //'{lang}Админ меню для ролей{/lang}{helpPoint}{lang}Управление пунктами админ меню, относительно ролей пользователей{/lang}{/helpPoint}' => $this->getHtml_CMSAdminMenuGroup(),
                '{lang}Админ меню для пользователей{/lang}{helpPoint}{lang}Управление пунктами админ меню, относительно пользователей{/lang}{/helpPoint}' => array($this, 'getHtml_CMSAdminMenu')
            )))
        );
    }

    public function echoHtml_settingsDashboard(){ echo $this->getHtml_settingsDashboard(); }


    public function getHtml_CMSDashboard(){
        echo hiweb()->file()->getHtml_fromTpl(array('form' => hiweb()->input()->getHtml_options($this->settingsCms), 'adminmenu' => $this->getHtml_CMSAdminMenu()));
    }


    public function getHtml_CMSAdminMenuGroup(){
        global $menu, $submenu, $user_login, $wp_roles;
        $users = (array)$wp_roles->roles;
        hiweb()->file()->js('hiweb-core-settings-adminmenu');
        $settings = get_option($this->option_cms_adminmenu_name, array());
        $defmenu = $this->def_cms_adminmenu;
        return hiweb()->file()->getHtml_fromTpl(get_defined_vars(),'getHtml_CMSAdminMenu');
    }


    public function getHtml_CMSAdminMenu(){
        hiweb()->file()->js('hiweb-core-settings-adminmenu');
        $role = hiweb()->wp()->getStr_currentUserRole();
        $user_login = get_current_user_id();
        $table = array();
        $cMenu = get_option(hiweb()->settings()->option_cms_adminmenu_name,false);
        foreach($this->def_cms_adminmenu as $position => $menu){
            if(hiweb()->string()->isEmpty($menu[0])) continue;
            $idEscape = hiweb()->string()->getStr_allowSymbols($menu[2]);
            $table[$menu[2]] = array(
                'name' => $menu[0],
                'rename' => hiweb()->input()->getHtml_field( $idEscape.'_rename', array(
                    'value' => hiweb()->array2()->getVal($cMenu, array($menu[2],'name')),
                    'type' => 'text',
                    'tags' => array('data-type'=>'rename')
                )),
                'mode' => hiweb()->input()->getHtml_field( $idEscape.'_mode', array(
                    'value' => hiweb()->array2()->getVal($cMenu, array($menu[2],'mode')),
                    'name' => 'Режим пункта: ',
                    'tags' => array('data-type'=>'mode'),
                    'type' => 'select',
                    'options' => array(
                        'show' => 'Всегда показывать',
                        'hide' => 'Всегда скрывать',
                        'show_role_hide_user' => 'Показывать ролям, скрывать для польщователей',
                        'show_user_hide_role' => 'Показывать пользователям, скрывать для ролей',
                        'show_only_role' => 'Показывать только для ролей',
                        'show_only_user' => 'Показывать только пользователям',
                        'hide_only_role' => 'Скрывать только ролям',
                        'hide_only_user' => 'Скрывать только пользователям'
                    )) ),
                'users' => hiweb()->input()->getHtml_field( $idEscape.'_users', array(
                    'value' => hiweb()->array2()->getVal($cMenu, array($menu[2],'users')),
                    'name' => 'Пользователи:',
                    'tags' => array('data-type'=>'users'),
                    'type' => 'users',
                    'display' => array(
                        array(
                            'id' => $idEscape.'_mode',
                            'operator' => '==',
                            'value' => array('show_role_hide_user','show_user_hide_role','show_only_user','hide_only_user')
                        )
                    )
                ) ),
                'roles' => hiweb()->input()->getHtml_field( $idEscape.'_roles', array(
                    'value' => hiweb()->array2()->getVal($cMenu, array($menu[2],'roles')),
                    'name' => 'Роли:',
                    'tags' => array('data-type'=>'roles'),
                    'type' => 'roles',
                    'display' => array(
                        array(
                            'id' => $idEscape.'_mode',
                            'operator' => '==',
                            'value' => array('show_role_hide_user','show_user_hide_role','show_only_role','hide_only_role')
                        )
                    )
                ) )
            );
        }
        return hiweb()->file()->getHtml_fromTpl(get_defined_vars());
    }

    /**
     * Внести изменения админменю
     *
     * @version 2.0
     */
    public function do_cms_adminmenu_change(){
        global $user_ID, $menu;
        hiweb()->settings()->def_cms_adminmenu = $menu;
        $cMenu = get_option(hiweb()->settings()->option_cms_adminmenu_name,false);
        if(is_array($cMenu)){
            $cuser = get_current_user_id();
            $crole = hiweb()->wp()->getStr_currentUserRole();
            foreach($menu as $k => $m){
                $cMenuItem = hiweb()->array2()->getVal($cMenu, $m[2]);
                if(!is_null($cMenuItem)){
                    $cMenuItemMod = hiweb()->array2()->getVal($cMenuItem,'mode');
                    $cMenuItemName = hiweb()->array2()->getVal($cMenuItem,'name');
                    if(hiweb()->string()->isEmpty($cMenuItemMod)) continue;
                    ///
                    $userMath = in_array($cuser, hiweb()->array2()->getArr($cMenuItem,'users'));
                    $roleMath = in_array($crole, hiweb()->array2()->getArr($cMenuItem,'roles'));
                    ///
                    switch($cMenuItemMod){
                        case 'show': $show = true; break;
                        case 'hide': $show = false; break;
                        case 'show_role_hide_user': $show = ($roleMath && !$userMath); break;
                        case 'show_user_hide_role': $show = (!$roleMath || $userMath); break;
                        case 'show_only_role': $show = ($roleMath); break;
                        case 'show_only_user': $show = ($userMath); break;
                        case 'hide_only_role': $show = (!$roleMath); break;
                        case 'hide_only_user': $show = (!$userMath); break;
                        default: $show = true;
                    }
                    if(!$show) remove_menu_page( $m[2] );
                    elseif(!hiweb()->string()->isEmpty($cMenuItemName)) {
                        $name = explode('<', $m[0]); array_shift($name);
                        $menu[$k][0] = count($name) > 0 ? $cMenuItemName.' <'.implode('<',$name) : $cMenuItemName;

                    }
                }
                /*if(isset($hiweb_settings_cms_adminmenu_options_cuser[$m[2]])){
                    if($hiweb_settings_cms_adminmenu_options_cuser[$m[2]]['enable'] == 'false' && !(in_array(hiweb()->request('page'),array('hiweb-settings','hiweb-settings-2')) && hiweb()->request('tab') == '-lang-admin-menyu-dlya-polzovateley--lang-')) { remove_menu_page( $m[2] ); }
                    elseif(trim($hiweb_settings_cms_adminmenu_options_cuser[$m[2]]['text']) != ''){
                        $name = explode('<', $m[0]); array_shift($name);
                        $menu[$k][0] = count($name) > 0 ? $hiweb_settings_cms_adminmenu_options_cuser[$m[2]]['text'].' <'.implode('<',$name) : $hiweb_settings_cms_adminmenu_options_cuser[$m[2]]['text'];
                    }
                }*/
            }
        }
    }


    /**
     * Сохранить настройки админменю
     */
    public function do_cms_adminmenu_save(){
        if(is_array(hiweb()->request('data'))) {
            update_option( $this->option_cms_adminmenu_name, hiweb()->request('data'));
        }
        die;
    }


    /**
     * Получить значение настроек
     * @param $settingsArr - массив опций
     * @param $settingKey - ключ опции
     * @param null $def - значение по-умолчанию
     * @return mixed
     */
    public function getMix_optionVal($settingsArr, $settingKey, $def = null){
        if(!is_array($settingsArr)) return $def;
        if(!isset($settingsArr[$settingKey])) return $def;
        return get_option($settingKey, isset($settingsArr[$settingKey]['default']) ? $settingsArr[$settingKey]['default'] : $def);
    }

    /**
     * Возвращает значение опции hiWeb Settings Cms
     * @param string $settingsKey
     * @return mixed
     */
    public function getMix_optionSettings($settingsKey){
        return $this->getMix_optionVal($this->settingsCms, $settingsKey);
    }



    public function echo_metaboxTitle($post){
        $meta = array_merge(array('hiweb_cms_title_mod' => 'default','hiweb_cms_title' => ''), hiweb()->wp()->getArr_postMeta($post->ID));

        wp_nonce_field( 'hiweb_cms_title_nonce', 'hiweb_cms_title_nonce' );
        // Поля формы для введения данных
        $r =  '<p><input type="radio" id="hiweb_cms_title_default" name="hiweb_cms_title_mod" value="default" size="25" '.($meta['hiweb_cms_title_mod'] == 'default' ? 'checked' : '').' />';
        $r .= '<label for="hiweb_cms_title_default">{lang}По умолчанию{/lang}</label></p>';

        $r .= '<p><input type="radio" id="hiweb_cms_title_none" name="hiweb_cms_title_mod" value="none" size="25" '.($meta['hiweb_cms_title_mod'] == 'none' ? 'checked' : '').'/>';
        $r .= '<label for="hiweb_cms_title_none">{lang}Не показывать{/lang}</label></p>';

        $r .= '<p><input type="radio" id="hiweb_cms_title_custome" name="hiweb_cms_title_mod" value="custom" size="25" '.($meta['hiweb_cms_title_mod'] == 'custom' ? 'checked' : '').'/>';
        $r .= '<label for="hiweb_cms_title_custome">{lang}Произвольный{/lang}</label></p>';

        $r .= '<p data-hiweb-cms-title><input class="full-width" type="text" name="hiweb_cms_title" placeholder="{lang}Введите произвольный заголовок{/lang}" value="'.$meta['hiweb_cms_title'].'"/></p>';
        echo hiweb()->file()->getHtml_fromTplStr($r);
    }




}
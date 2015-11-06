<?php
/**
 * Created by PhpStorm.
 * User: denmedia
 * Date: 09.04.2015
 * Time: 17:36
 */


hiweb()->wp()->ajax('hiweb-core-plugins', array(hiweb()->plugins(),'ajax'), false);


class hiweb_plugins {

    public $server = 'wordpress.net';
    public $serverAction = 'hiweb-plugins-server';
    public $scriptsQuery = '/hiweb-update/scripts.json';

    public $settings = array(
        'hiweb_plugins_server' => array(
            'def' => 'wordpress.net',
            'name' => '{lang}Сервер репозитория{/lang}',
            'desc' => '{lang}Введите Ваш сервер репозитория, например <b>myrepositoryserver.com</b> с установленным и активированным плагином <b>hiWeb Plugins Server</b>.<br>С данного сервера будут браться плагины и скрипты.{/lang}',
            'type' => 'text'
        )
    );

    public $pluginsQuery = '/hiweb-update/plugins.json';

    public $plugins = array(
        array(
            'name'     => 'TGM Example Plugin', // The plugin name
            'slug'     => 'tgm-example-plugin', // The plugin slug (typically the folder name)
            //'source'   => get_stylesheet_directory() . '/lib/plugins/tgm-example-plugin.zip', // The plugin source
            //'required' => false
        )
    );

    public $defScripts = array(
        'name' => 'Название скрипта',
        'group' => '',
        'desc' => '',
        'slug' => 'scriptfolder',
        'version' => '1.0.0.0',
        'url' => '',
        'size' => 0,
        'sizeF' => '0',
        'require' => 0
    );


    public function __construct(){
        $this->settings[ 'hiweb_plugins_server' ]['def'] = HIWEB_PLUGINS_REPOSITORY;
        $this->server = get_option('hiweb_plugins_server', HIWEB_PLUGINS_REPOSITORY);
        if(trim($this->server)=='') { update_option('hiweb_plugins_server',HIWEB_PLUGINS_REPOSITORY); $this->server = HIWEB_PLUGINS_REPOSITORY; }
    }


    public function ajax(){
        switch(hiweb()->request('do')){
            case 'download': break;
            case 'activate': break;
            case 'deactivate': break;
            case 'delete': break;
            default: echo json_encode(array(false,'не известная функция')); break;
        }
        die();
    }

    public function getHtml_dashboard(){
        hiweb()->file()->js('hiweb-core-plugins');
        hiweb()->file()->css('hiweb-core-plugins');
        ///
        return hiweb()->build()->getHtml_tabs(array(
            '{lang}Мои Плагины{/lang}' => array($this, 'getHtml_pluginsRepository'),
            '{lang}Мои Скрипты и Ассеты{/lang}' => array($this, 'getHtml_scriptsRepository'),
            '{lang}Настройки моего сервера{/lang}' => array($this,'getHtml_pluginsSettings')
        ));
    }

    public function echoHtml_dashboard(){ echo $this->getHtml_dashboard(); }


    public function getHtml_pluginsRepository(){
        if(!is_null(hiweb()->request('path'))){
            if(!is_array(hiweb()->request('path'))) $paths = array(hiweb()->request('path')); else $paths = hiweb()->request('path');
            foreach($paths as $path){ $this->do_getPluginFromRepository($path, hiweb()->request('do')); }
        }
        $plugins = hiweb()->plugins()->getArr_pluginsFromRepository();
        $url = hiweb()->string()->getStr_urlQuery(null, array(), array('path'));
        return hiweb()->file()->getHtml_fromTpl(array('url' => $url, 'plugins' => $plugins, 'group' => hiweb()->request('group')));
    }

    public function getHtml_scriptsRepository(){
        if(!is_null(hiweb()->request('path'))){
            if(!is_array(hiweb()->request('path'))) $paths = array(hiweb()->request('path')); else $paths = hiweb()->request('path');
            foreach($paths as $path){ $this->do_getScriptsFromRepository($path, hiweb()->request('do')); }
        }
        $scripts = hiweb()->plugins()->getArr_scripts_fromRepository();
        $url = hiweb()->string()->getStr_urlQuery(null, array(), array('path'));
        return hiweb()->file()->getHtml_fromTpl(array('url' => $url, 'scripts' => $scripts));
    }


    public function getHtml_pluginsSettings(){
        $atr = array('version' => HIWEB_VERSION,
            'wp_nonce' => wp_nonce_field('update-options'),
            'savechanges' => __('Save Changes'),
            'ids' => array(),
            'tab' => array(
                hiweb()->string()->getStr_urlQuery(null, array('tab'=>''), array('tab')),
                hiweb()->string()->getStr_urlQuery(null, array('tab' => 'sett'))
            ));
        foreach($this->settings as $id => $a){
            register_setting( 'hiweb-settings-plugins', $id );
            $atr['fields'][$id] = $a;
            $atr['fields'][$id]['val'] = get_option($id, $a['def']);
            $atr['ids'][] = $id;
        }
        return hiweb()->file()->getHtml_fromTpl($atr);
    }


    /**
     * Возвращает массив плагинов из репозитория
     * @return array|mixed|null
     *
     * @version 1.1
     */
    public function getArr_pluginsFromRepository(){
        $pluginsRepository = hiweb()->curl()->getMix_JSON_fromURL( $this->server.$this->pluginsQuery, array('action' => $this->serverAction) );
        $plugins = hiweb()->wp()->getArr_plugins();
        if(!is_array($pluginsRepository)) return array();
        foreach($pluginsRepository as $path => $plugin){
            $pluginsRepository[$path]['exists'] = isset($plugins[$path]);
            $pluginsRepository[$path]['active'] = false;
            $pluginsRepository[$path]['update'] = false;
            $pluginsRepository[$path]['group'] = hiweb()->array2()->getVal($pluginsRepository[$path], 'group');
            $pluginsRepository[$path]['groupArr'] = hiweb()->array2()->explodeTrim(',',$pluginsRepository[$path]['group'],0,1);
            if($pluginsRepository[$path]['exists']) {
                $pluginsRepository[$path]['active'] = is_plugin_active($path);
                $pluginsRepository[$path]['update'] = $plugin['Version'] != $plugins[$path]['Version'] ? $plugin['Version'] : false;
            }
        }
        return $pluginsRepository;
    }


    /**
     * Возвращает массив скриптов репозитория
     * @return array|mixed|null
     */
    public function getArr_scripts_fromRepository(){
        $scriptsRepository = hiweb()->curl()->getMix_JSON_fromURL( $this->server.$this->scriptsQuery );
        $scripts = $this->getArr_scriptsFromAssets();
        if(!is_array($scriptsRepository)) { return array(); }
        foreach($scriptsRepository as $path => $script){
            $scriptsRepository[$path]['exists'] = isset($scripts[$path]);
            $scriptsRepository[$path]['update'] = false;
            if($scriptsRepository[$path]['exists']) {
                $scriptsRepository[$path]['update'] = $script['version'] != $scripts[$path]['version'] ? $script['version'] : false;
            }
        }
        return $scriptsRepository;
    }


    /**
     * Возвращает массив скриптов из локальной папки
     * @return array
     */
    public function getArr_scriptsFromAssets(){
        $dirScripts = hiweb()->file()->getArr_directory(HIWEB_DIR_ASSET, 1, 1, 0);
        $scripts = array();
        foreach($dirScripts as $path => $d){ $scripts[basename($path)] = $this->getArr_scriptInfo_fromAssets(basename($path)); }
        asort($scripts);
        return $scripts;
    }


    public function getArr_pluginsIds($active = true, $deactive = false){
        $plugins = hiweb()->wp()->getArr_plugins();
        $ids = array();
        foreach ($plugins as $path => $plugin) {
            if(($active && is_plugin_active($path)) || ($deactive && !is_plugin_active($path))) $ids[] = $path;
        }
        return $ids;
    }


    public function getArr_pluginsVerions($active = true, $deactive = true){
        $plugins = hiweb()->wp()->getArr_plugins();
        $r = array();
        foreach ($plugins as $path => $plugin) {
            if(($active && is_plugin_active($path)) || ($deactive && !is_plugin_active($path))) $r[$path] = $plugin['Version'];
        }
        return $r;
    }


    /**
     * Принудительное скачивание плагина
     * @param $path
     *
     * @return bool
     */
    public function do_pluginDownload($path){
        $repository = hiweb()->plugins()->getArr_pluginsFromRepository();
        if(!isset($repository[$path])) return false;
        $plugin = $repository[$path];
        $archive = HIWEB_DIR_CACHE.'/'.$plugin['name'];
        if( !file_put_contents($archive, fopen($plugin['url'], 'r')) ) return false;
        if(!hiweb()->file()->do_unpackToDir($archive, WP_PLUGIN_DIR)) return false;
        @unlink($archive);
        if(strpos($plugin['name'],'hiweb-core') === 0 ) { hiweb()->file()->do_unlinkDir(HIWEB_DIR_CACHE.'/hiweb-core-tpl'); }
        return true;
    }

    /**
     * Принудительное удаление плагина
     * @param $path
     *
     * @return bool
     */
    public function do_pluginRemove($path){
        $plugins = hiweb()->wp()->getArr_plugins();
        if(!isset($plugins[$path])) return false;
        $plugin = $plugins[$path];
        return @unlink(WP_PLUGIN_DIR.'/'.$path);
    }


    /**
     * ПРинудительная активация плагина
     * @param $path
     * @return null
     */
    public function do_pluginActivate($path){
        $current = get_option( 'active_plugins' );
        $path = plugin_basename( trim( $path ) );
        if ( !in_array( $path, $current ) ) {
            $current[] = $path;
            sort( $current );
            do_action( 'activate_plugin', trim( $path ) );
            update_option( 'active_plugins', $current );
            do_action( 'activate_' . trim( $path ) );
            do_action( 'activated_plugin', trim( $path) );
        }
        return null;
    }

    public function do_getPluginFromRepository($path, $do){
        if(!is_string($path) || strlen($path) < 2) { echo '<div class="error"><p>Плагин не указан</div>'; return false; }
        switch($do){
            case 'download': $this->do_pluginDownload($path); break;
            case 'download-active': $this->do_pluginDownload($path); $this->do_pluginActivate($path); break;
            case 'active': activate_plugin($path); break;
            case 'deactive': deactivate_plugins($path); break;
            case 'remove': @unlink(WP_PLUGIN_DIR.'/'.$path); break;
            case 'deactive-remove': deactivate_plugins($path); @unlink(WP_PLUGIN_DIR.'/'.$path); break;
        }
    }


    /**
     * Скачать скрипт из репозитория
     * @param $slug
     * @param string $active
     * @return bool
     */
    public function do_getScriptsFromRepository($slug, $active = 'download'){
        if(!is_string($slug) || strlen($slug) < 2) { echo '<div class="error"><p>Плагин не указан</div>'; return false; }
        if($active == 'download'){
            $repository = hiweb()->plugins()->getArr_scripts_fromRepository();
            if(!isset($repository[$slug])) {
                echo '<div class="error"><p>Скрипт для загрузки в папке <b>'.$slug.'</b> не найден</p></div>';
                return false; }
            $b = true;
            $script = $repository[$slug];
            $archive = HIWEB_DIR_CACHE.'/'.$script['name'];
            if( !file_put_contents($archive, fopen($script['download'], 'r')) ) { echo '<div class="error"><p>не удалось скачать скрипт <b>'.$script['name'].'</b></p></div>'; return false; }
            hiweb()->file()->do_foldersAutoCreate(HIWEB_DIR_ASSET);
            hiweb()->file()->do_unlinkDir(HIWEB_DIR_ASSET.'/'.$slug);
            if(!hiweb()->file()->do_unpackToDir($archive, HIWEB_DIR_ASSET)) { echo '<div class="error"><p>Не удалось распаковать скрипт <b>'.$script['name'].'</b></p></div>'; return false; }
            @unlink($archive);
            return true;
        }
        elseif($active == 'remove'){
            $repository = hiweb()->plugins()->getArr_scripts_fromRepository();
            if(!isset($repository[$slug])) {
                echo '<div class="error"><p>Скрипт для удаления в папке <b>'.$slug.'</b> не найден</p></div>';
                return false; }
            $script = $repository[$slug];
            if(hiweb()->file()->do_unlinkDir(HIWEB_DIR_ASSET.'/'.$slug)) { echo '<div class="update"><p>Скрипт удален: <b>'.$script['name'].'</b></p></div>'; };
            return true;
        }
    }


    /**
     * Получить информацию
     * @param $slug - имя папки скрипта/ассета
     * @param bool $autoUpdateSize - получить актуальный размер папки
     * @param bool $autoWriteScriptInfoFile - обновить инфо-файл
     * @return array | bool
     */
    public function getArr_scriptInfo_fromAssets($slug, $autoUpdateSize = true, $autoWriteScriptInfoFile = true){
        if(!file_exists(HIWEB_DIR_ASSET.'/'.$slug)) return false;
        if($autoUpdateSize) $dirInfo = hiweb()->file()->getArr_directoryInfo(HIWEB_DIR_ASSET.'/'.$slug);
        else $dirInfo = array('size' => 0, 'sizeF' => 0);
        $def = hiweb()->array2()->merge($this->defScripts, array(
            'name' => $slug,
            'slug' => $slug,
            'size' => $dirInfo['size'],
            'sizeF' => $dirInfo['sizeF']
        ));
        $infoFile = HIWEB_DIR_ASSET.'/'.$slug.'/'.$slug.'.json';
        if(!file_exists($infoFile)) { if($autoWriteScriptInfoFile) hiweb()->file()->do_varExportToFile($infoFile, $def); }
        else { $def = hiweb()->array2()->merge($def, hiweb()->file()->getMix_fromJSONFile($infoFile)); }
        if($autoUpdateSize) {
            $def['size'] = $dirInfo['size'];
            $def['sizeF'] = $dirInfo['sizeF'];
        }
        return $def;
    }


    public function do_changeScriptInfo($slug, $arrChange = array()){
        $scriptInfo = $this->getArr_scriptInfo_fromAssets($slug);
        if($scriptInfo == false) return false;
        return hiweb()->file()->do_varExportToFile(
            HIWEB_DIR_ASSET.'/'.$slug.'/'.$slug.'.json',
            hiweb()->array2()->merge($scriptInfo, $arrChange)
        );
    }


    /**
     * Возвращает путь до корневой папки плагина, если указать хотябы на один из файлов самого плагина
     * @param $file - путь до одного из файлов плагина
     * @return bool|null
     */
    public function getStr_pluginRootDir($file){
        $file = hiweb()->file()->getStr_normalizeDirSeparates( hiweb()->file()->getStr_realPath($file) );
        $plugDir = hiweb()->file()->getStr_normalizeDirSeparates( hiweb()->file()->getStr_realPath(WP_PLUGIN_DIR) );
        if(strpos($file,$plugDir)===false) return false;
        ///
        return $plugDir.DIR_SEPARATOR.hiweb()->array2()->getVal_byIndex(hiweb()->array2()->explodeTrim(DIR_SEPARATOR,str_replace($plugDir,'',$file),0,0),0);
    }



}
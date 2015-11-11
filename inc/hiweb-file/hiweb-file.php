<?php
/**
 * Created by PhpStorm.
 * User: denmedia
 * Date: 09.04.2015
 * Time: 9:53
 */

class hiweb_file {


    public $_css = array();
    public $_js = array();
    public $_js_footer = array();
    public $_wp_head = false;



    public function __construct(){
        ///
        if(!defined('DIR_SEPARATOR')) { define('DIR_SEPARATOR', $this->getStr_directorySeparator()); }
        ///
        add_action('wp_enqueue_scripts', array($this,'_do_enqueueScripts'));
        add_action('admin_enqueue_scripts', array($this,'_do_enqueueScripts'));
        add_action('login_enqueue_scripts', array($this,'_do_enqueueScripts'));
        ///
        add_action('in_admin_footer', array($this,'_do_enqueueScripts'));
        add_action('wp_footer', array($this,'_do_enqueueScripts'));
    }


    /**
     * Возвращает DIRECTORY SEPARATOR, отталкиваясь от данных
     */
    public function getStr_directorySeparator(){
        $left = substr_count($_SERVER['DOCUMENT_ROOT'],'\\');
        $right = substr_count($_SERVER['DOCUMENT_ROOT'],'//');
        return $left > $right ? '\\' : '/';
    }

    /**
     * Возвращает права на файл/папку
     * @param $path
     * @return string
     * @version 1.0
     */
    public function getStr_filePerms($path){
        return substr(decoct(fileperms($path)), -3);
    }


    /**
     * Вывести скрипты и стили через функцию wp_enqueue_style, wp_enqueue_script
     */
    public function _do_enqueueScripts(){
        ///CSS register
        foreach($this->_css as $handle => $css){
            unset($this->_css[$handle]);
            $cssUrl = hiweb()->array2()->getVal($css, 'url');
            if(trim((string)$cssUrl)!=''){
                wp_register_style( $handle, $cssUrl,array(),null );
                wp_enqueue_style($handle);
            }
        }
        ///JS register
        foreach($this->_js as $handle => $js){
            unset($this->_js[$handle]);
            $jsUrl = hiweb()->array2()->getVal($js, 'url');
            if(trim((string)$jsUrl)!=''){
                wp_register_script( $handle, $jsUrl, hiweb()->array2()->getVal($js, 'afterJQuery') ? array('jquery'):null, false, $this->_wp_head );
                wp_enqueue_script($handle);
            }
        }
        foreach($this->_js_footer as $handle => $js){
            unset($this->_js_footer[$handle]);
            $jsUrl = hiweb()->array2()->getVal($js, 'url');
            if(trim((string)$jsUrl)!=''){
                wp_register_script( $handle, $jsUrl, hiweb()->array2()->getVal($js, 'afterJQuery') ? array('jquery'):null, false, true );
                wp_enqueue_script($handle);
            }
        }
        $this->_wp_head = true;
    }


    /**
     * Быстрое подключение файла JS, используя авто-поиск, либо http://ссылку на файл
     *
     * @param $jsPath - имя или путь до файла
     * @param bool $afterJQuery - загружать после jQuery
     * @param bool $footer - подключать скрипт в футере
     *
     * @return bool
     */
    public function js($jsPath = '', $afterJQuery = true, $footer = false){
        $jsURL = $this->getStr_jsUrl($jsPath);
        if(!$jsURL) { hiweb()->console()->error('Файл ['.$jsPath.'] не найден!',1); return false; }
        $handle = hiweb()->string()->getStr_allowSymbols(basename($jsURL));
        $this->{'_js'.($footer ? '_footer' : '')}[$handle] = array(
            'path' => $jsPath,
            'url' => $jsURL,
            'afterJQuery' => $afterJQuery
        );
        return true;
    }

    /**
     * Возвращает URL до файла JS, используя поиск
     * @param $jsPath
     *
     * @return bool|string
     *
     * @version 1.4
     */
    public function getStr_jsUrl($jsPath = ''){
        if(hiweb()->cacheExists()) return hiweb()->cache();
        if( strpos($jsPath, 'http://') !== 0 && strpos($jsPath, 'https://') !== 0 ) {
            if(!is_string($jsPath) || !file_exists($this->getStr_realPath($jsPath)) || !is_file($jsPath) ){
                $searchArr = array(
                    hiweb()->getArr_debugBacktrace(0,0,0,0,1,0,2,4),
                    array_unique(hiweb()->array2()->merge(array('/','/js/','/java/','/javascript/','/javascripts/','/include/'),hiweb()->getArr_debugBacktrace(1,1,1,1,0,0,2,3,'/','/'))),
                    hiweb()->array2()->merge(array(''),hiweb()->getArr_debugBacktrace(1,1,1,1,0)),
                    array($jsPath, '-'.$jsPath,'script'),
                    array('','.js')
                );
                $jsPath = $this->getStr_pathBySearch($searchArr,1,0);
            }
            if(!file_exists($jsPath)) return false;
            $jsPath = $this->getStr_urlFromRealPath($jsPath);
        }
        return hiweb()->cache($jsPath);
    }


    /**
     * Быстрое подключение файла CSS, используя авто-поиск
     * @param $cssPath
     *
     * @return bool
     */
    public function css($cssPath = ''){
        $cssURL = $this->getStr_cssUrl($cssPath);
        if(!$cssURL) { hiweb()->console()->error('Файл ['.$cssPath.'] не найден!',1); return false; }
        $handle = hiweb()->string()->getStr_allowSymbols(basename($cssURL));
        $this->_css[$handle] = array(
            'path' => $cssPath,
            'url' => $cssURL
        );
        return true;
    }


    /**
     * Возвращает URL до файла CSS, используя поиск
     * @param $cssPath
     *
     * @return bool|string
     *
     * @version 1.5
     */
    public function getStr_cssUrl($cssPath = ''){
        if(hiweb()->cacheExists()) return hiweb()->cache();
        if(!is_string($cssPath) || !file_exists($this->getStr_realPath($cssPath)) || !is_file($cssPath) ){
            $searchArr = array(
                hiweb()->getArr_debugBacktrace(0,0,0,0,1,0,2,4),
                array_unique(hiweb()->array2()->merge(array('/','/css/','/style/','/styles/','/template/'),hiweb()->getArr_debugBacktrace(1,1,1,1,0,0,2,3,'/','/'))),
                hiweb()->array2()->merge(array(''),hiweb()->getArr_debugBacktrace(1,1,1,1,0)),
                array($cssPath, '-'.$cssPath,'style'),
                array('','.css')
            );
            $cssPath = $this->getStr_pathBySearch($searchArr);
        }
        if(!file_exists($cssPath)) return false;
        $cssPath = $this->getStr_urlFromRealPath($cssPath);
        return hiweb()->cache($cssPath);
    }


    /**
     * Require файл, используя поиск в ближайших папках
     * Так же проверяеться корневая папка сайта, иначе подклдючения не будет
     * @param $filePath - имя или путь до файла PHP
     * @param bool $requireOnce - подключать только один раз
     *
     * @return bool
     *
     * @version 1.2
     */
    public function inc($filePath = '', $requireOnce = true){
        if(!is_string($filePath)) return false;
        $oldFilePath = $filePath;
        if(!file_exists($this->getStr_realPath($filePath)) || !is_file($filePath) ){
            $searchArr = array(
                hiweb()->getArr_debugBacktrace(0,0,0,0,1,0,2,4),
                array_unique(hiweb()->array2()->merge(array('/', '/inc/', '/include/'),hiweb()->getArr_debugBacktrace(1,1,1,1,0,0,2,3,'/','/'))),
                hiweb()->array2()->merge(array(''),hiweb()->getArr_debugBacktrace(1,1,1,1,0)),
                array($filePath, '-'.$filePath),
                array('','.php')
            );
            $filePath = $this->getStr_pathBySearch($searchArr);
        }
        if(strpos($filePath, hiweb()->getStr_baseDir())===false || !file_exists($filePath)) { hiweb()->console($searchArr); hiweb()->console()->error('файл ['.$oldFilePath.'] не найден!',1); return false; }
        if($requireOnce) require_once $filePath; else require $filePath;
        return true;
    }

    /**
     * Функция атоматически создает папки
     * @param $dirPath - путь до папи, которую необходимо создать
     * @return string
     */
    public function do_foldersAutoCreate($dirPath) {
        $dirPath = $this->getStr_realPath($dirPath);
        if(@file_exists($dirPath)) { return is_dir($dirPath) ? $dirPath : false; }
        $dirPathArr = explode(DIR_SEPARATOR, str_replace('/',DIR_SEPARATOR,$dirPath));
        $newDirArr = array();
        $newDirDoneArr = array();
        foreach($dirPathArr as $name){
            $newDirArr[] = $name;
            $newDirStr = implode(DIR_SEPARATOR, $newDirArr);
            @chmod($newDirStr, 0755);
            $stat = @stat($newDirStr);
            if(!@file_exists($newDirStr) || @is_file($newDirStr)){
                $newDirDoneArr[$name] = @mkdir($newDirStr,0755);
            } else { $newDirDoneArr[$newDirStr] = 0; }
        }
        return $newDirDoneArr;
    }


    /**
     * Удалить папку вместе с вложенными папками и файлами
     * @param $dirPath
     * @return bool
     */
    public function do_unlinkDir($dirPath) {
        if (! is_dir($dirPath)) { return false; }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') { $dirPath .= '/'; }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) { $this->do_unlinkDir($file); } else { unlink($file); }
        }
        return rmdir($dirPath);
    }


    /**
     * Подключение файлов CSS и JS из папки ASSET, либо текущей папки
     * @param string $slug - название папки ASSET
     * @param array $jsMix - список подключаемых файлов JS, либо оставьте пустым если нужно подключить все найденные файлы в папке, если установить FALSE, то все JS файлы НЕ будут подключены
     * @param array $cssMix - список подключаемых файлов CSS, либо оставьте пустым если нужно подключить все найденные файлы в папке, если установить FALSE, то все CSS файлы НЕ будут подключены
     * @param bool $excludeAdminPage - установите TRUE, чтобы автоматически не подключать ассет в админ-панеле, если функция вызвана из папки темы
     * @param bool $excludeLoginPage установите TRUE, чтобы автоматически не подключать ассет на логин-страничке, если функция вызвана из папки темы
     * @param bool $loadInFooter - приоритет загрузки скриптов JS в футере
     * @return array - массив подключенных файлов
     *
     * @version 1.7
     */
    public function asset($slug = '', $jsMix = array(), $cssMix = array(), $excludeAdminPage = true, $excludeLoginPage = true, $loadInFooter = false){
        ///Проверка на пустоту слуга
        if(hiweb()->string()->isEmpty($slug)) { hiweb()->console()->error('Ассет не подключен, так как слуг не указан', 1); return false; }
        ///Автоматически не подключать ассет в админке, если папка вызвана из темы
        if($excludeAdminPage && hiweb()->wp()->is_callFromTemplateDir(1) && is_admin()) { hiweb()->console()->info('Ассет ['.$slug.'] не подключен в результате правила "не подключать в админ панеле"',1); return null; }
        if($excludeLoginPage && hiweb()->wp()->is_callFromTemplateDir(1) && hiweb()->wp()->is_login_page()) { hiweb()->console()->info('Ассет ['.$slug.'] не подключен в результате правила "не подключать в логин панеле"',1); return null; }
        ////Find ASSET in main 'assets' dir and this root plugins dir 'assets' dir
        $dirName = array(
            HIWEB_DIR_ASSET.DIR_SEPARATOR.$slug, //Main DIR
            hiweb()->plugins()->getStr_pluginRootDir(hiweb()->array2()->getVal(debug_backtrace(),array(0,'file'))).DIR_SEPARATOR.'assets'.DIR_SEPARATOR.$slug, //This parent plugin dir
            hiweb()->plugins()->getStr_pluginRootDir(hiweb()->array2()->getVal(debug_backtrace(),array(0,'file'))).DIR_SEPARATOR.'assets'.DIR_SEPARATOR.$slug.'.zip', //This parent archive file
            HIWEB_CORE_DIR.DIR_SEPARATOR.'assets'.DIR_SEPARATOR.$slug,
            HIWEB_CORE_DIR.DIR_SEPARATOR.'assets'.DIR_SEPARATOR.$slug.'.zip'
        );
        ///Поиск ассета на текущем сервере
        $findedPath = $this->getStr_pathBySearch(array($dirName), 1, 1);
        if( is_string($findedPath) ){
            if($findedPath == $dirName[0]) { /*do nothing...*/ }
            elseif( $this->getStr_fileExtension($findedPath) == 'zip' ) { //Распаковать архив в папку
                if($this->do_unpackToDir($findedPath,HIWEB_DIR_ASSET)){ $findedPath = $dirName[0]; }
                else { hiweb()->console()->error('Не удалось распаковать ассет ['.$dirName[2].'] в папку ['.HIWEB_DIR_ASSET.']. Ассет подключен.',1); }
            }
            else {
                if($this->do_copyDir($findedPath, $dirName[0])) { $findedPath = $dirName[0]; }
                else { hiweb()->console()->warn('Не удалось скопировать пупку ассета ['.$dirName[1].'] в папку ['.$dirName[0].']. Ассет бурется из папки плагина',1); }
            }
        }
        ///Поиск ассета в репозитории
        if( !is_string($findedPath) ){
            if(hiweb()->plugins()->do_getScriptsFromRepository($slug, 'download') == true){ $findedPath = $dirName[0]; }
        }
        ///Check errors
        if( !is_string($findedPath) || !file_exists($findedPath) ) { hiweb()->console()->error('Папка ассета ['.$slug.'] не найден!',1); return false; }
        ///
        $indexPhp = $findedPath.DIR_SEPARATOR.$slug.'.php';
        if(file_exists($indexPhp) && is_file($indexPhp)) { ///Mod PHP
            require_once $indexPhp; return array($indexPhp);
        } else { /// Mod JS, CSS
            ///Include JS, CSS files
            return $this->includeDir($findedPath, $jsMix, $cssMix, $excludeAdminPage, $excludeLoginPage, $loadInFooter);
        }
    }


    /**
     * Подключить скрипты JS и стили CSS из папки, используя поиск
     * @param $path
     * @param array $jsMix
     * @param array $cssMix
     * @param bool $excludeAdminPage
     * @param bool $excludeLoginPage
     * @param bool $loadInFooter
     * @return array - возвращает массив подключенных файлов
     * @version 1.0
     */
    public function includeDir($path, $jsMix = array(), $cssMix = array(), $excludeAdminPage = true, $excludeLoginPage = true, $loadInFooter = false){
        $jsMix = hiweb()->array2()->getArr($jsMix);
        $cssMix = hiweb()->array2()->getArr($cssMix);
        $files = $this->getArr_directory($path, false, true);
        $inc = array('css'=>array(),'js'=>array());
        $r = array();
        ///
        foreach($files as $path => $file){
            if( hiweb()->array2()->getVal($file,'extension') != 'css' && hiweb()->array2()->getVal($file,'extension') != 'js' ) continue;
            //
            $inc[$file['extension']][$path] = $file;
            if(!hiweb()->string()->isEmpty($cssMix) && $file['extension'] == 'css' &&
                (
                    hiweb()->array2()->getBool_empty($cssMix) ||
                    hiweb()->array2()->strPosArrays(array( basename($path), basename($path, '.css'), dirname($path).DIR_SEPARATOR.basename($path), dirname($path).DIR_SEPARATOR.basename($path,'.css') ), $cssMix) !== false
                )
            ) { $this->css($path);$r[]=$path;}
            if(!hiweb()->string()->isEmpty($jsMix) && $file['extension'] == 'js' &&
                (
                    hiweb()->array2()->getBool_empty($jsMix) ||
                    hiweb()->array2()->strPosArrays(array( basename($path), basename($path, '.js'), dirname($path).DIR_SEPARATOR.basename($path), dirname($path).DIR_SEPARATOR.basename($path,'.js') ), $jsMix) !== false
                )
            ) {$this->js($path,true,$loadInFooter);$r[]=$path;}
        }
        ///
        return $r;
    }


    /**
     * Удалить несколько файлов
     * @param $filesArr - массив путей к файлам
     *
     * @return array - массив файлов и результатов удаления
     */
    public function do_unlinkFiles($filesArr){
        $r = array();
        if(!is_array($filesArr)) { $filesArr = array($filesArr); }
        foreach($filesArr as $path){
            $path = hiweb()->file()->getStr_realPath($path);
            $r[$path] = false;
            if(!is_file($path) || !file_exists($path)) continue;
            $r[$path] = @unlink($path);
        }
        return $r;
    }

    /**
     * Экспортировать переменную в файл в формате JSON
     * @version 1.0.0.1
     * @param $fileName
     * @param null $var
     * @param bool $jsonBeauty
     * @return int
     */
    public function do_varExportToFile($fileName, $var = null, $jsonBeauty = true){
        $this->do_foldersAutoCreate( dirname($fileName) );
        $content = $jsonBeauty ? hiweb()->string()->getStr_JsonIndent(json_encode($var, true)) : json_encode($var, true);
        return file_put_contents($this->getStr_realPath($fileName), $content);
    }


    /**
     * Копирует папку целиком вместе с вложенными файлами и папками
     * @param $src - исходная папка
     * @param $dst - папка назначения
     * @return bool
     */
    public function do_copyDir($src,$dst) {
        $dir = opendir($src);
        $this->do_foldersAutoCreate($dst);
        $r = true;
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . $this->getStr_directorySeparator() . $file) ) {
                    $r = $r && $this->do_copyDir($src . $this->getStr_directorySeparator() . $file,$dst . $this->getStr_directorySeparator() . $file);
                }
                else {
                    $r = $r && copy($src . $this->getStr_directorySeparator() . $file,$dst . $this->getStr_directorySeparator() . $file);
                }
            }
        }
        closedir($dir);
        return $r;
    }

    /**
     * Возвращает переменную из файла с JSON данными
     * @version 1.0.0.0
     * @param $fileName - путь до файла
     * @param null $returnDef - вернуть, если файл не будет найден
     *
     * @return mixed|null
     */
    public function getMix_fromJSONFile($fileName, $returnDef = null){
        $fileName = $this->getStr_realPath($fileName);
        if(!file_exists($fileName)) {return $returnDef;}
        return json_decode(file_get_contents($fileName),1);
    }

    /**
     * Возвращает путь с правильными разделителями
     * @param $path - исходный путь
     * @param bool $removeLastSeparators - удалить самый хвостовой сепаратор
     * @return string | bool
     *
     * @version 1.1
     */
    public function getStr_normalizeDirSeparates($path, $removeLastSeparators = false){
        if(!is_string($path)) { hiweb()->console()->warn('Путь должен быть строкой',1); return false; }
        $r = strtr($path, array('\\' => $this->getStr_directorySeparator(), '/' => $this->getStr_directorySeparator()));
        return $removeLastSeparators ? rtrim($r, $this->getStr_directorySeparator()) : $r;
    }


    /**
     * Конвертация относитльного пути к коневой папке в реальный
     * @version 1.0.0.0
     * @param $fileOrDirPath - путь до файла или папки
     * @return string
     */
    public function getStr_realPath($fileOrDirPath){
        $fileOrDirPath = $this->getStr_normalizeDirSeparates($fileOrDirPath);
        return(strpos($fileOrDirPath, hiweb()->getStr_baseDir())!==0) ? hiweb()->getStr_baseDir().DIR_SEPARATOR.$fileOrDirPath : $fileOrDirPath;
    }

    /**
     * Конвертация реального пути в относительный
     * @version 1.0.0.0
     * @param $fileOrDirPath - путь до файла или папки
     * @return string | bool
     */
    public function getStr_linkPath($fileOrDirPath){
        if(!is_string($fileOrDirPath)) {hiweb()->console()->warn('Путь должен быть строкой, а не ['.gettype($fileOrDirPath).']', 1); return false;}
        $fileOrDirPath = $this->getStr_normalizeDirSeparates($fileOrDirPath);
        return(strpos($fileOrDirPath, BASE_DIR)===0) ? str_replace(BASE_DIR.DIR_SEPARATOR, '', $fileOrDirPath) : $fileOrDirPath;
    }

    /**
     * Возвращает URL до файла, исходя из реального пути до файла
     * @param $realPath
     * @return mixed
     */
    public function getStr_urlFromRealPath($realPath){
        $realPath = $this->getStr_realPath($realPath);
        return str_replace(hiweb()->getStr_baseDir(),hiweb()->getStr_baseUrl(),$realPath);
    }


    /**
     * Получить размер всех вложенных файлов и папок
     * @version 1.1.0.0
     *
     * @param string $path - путь до папки
     *
     * @return mixed
     */
    public function getArr_directoryInfo($path = '') {
        $path = $this->getStr_realPath($path);
        $totalsize = 0;
        $totalcount = 0;
        $dircount = 0;
        $files = array();
        if ($handle = opendir ($path)) {
            while (false !== ($file = readdir($handle))) {
                $nextpath = $path . '/' . $file;
                if ($file != '.' && $file != '..' && !is_link ($nextpath)) {
                    if (is_dir ($nextpath)) {
                        $dircount++;
                        $result = $this->getArr_directoryInfo($nextpath);
                        $totalsize += $result['size'];
                        $totalcount += $result['filesCount'];
                        $dircount += $result['dirsCount'];
                        if(is_array($result['files']) || count($result['files']) > 0) $files = $files + $result['files'];
                    } else {
                        $files[$nextpath] = $this->getArr_fileData($nextpath);
                        $totalsize += filesize($nextpath);
                        $totalcount++;
                    }
                }
            }
        }
        closedir ($handle);
        $total['files'] = $files;
        $total['size'] = $totalsize;
        $total['filesCount'] = $totalcount;
        $total['dirsCount'] = $dircount;
        $total['sizeF'] = $this->getStr_sizeFormat($totalsize);
        return $total;
    }

    /**
     * Возвращает содержимое папки в массиве
     * @param $path
     * @param bool $returnDirs
     * @param bool $returnFiles
     * @param bool $getSubDirs
     * @return array
     */
    public function getArr_directory($path, $returnDirs = true, $returnFiles = true, $getSubDirs = true){
        $path = hiweb()->file()->getStr_realPath($path);
        if(!file_exists($path)){ return array(); }
        $r = array();
        if ($handle = opendir ($path)) {
            while (false !== ($file = readdir($handle))) {
                $nextpath = $path . '/' . $file;
                if ($file != '.' && $file != '..' && !is_link ($nextpath)) {
                    ///
                    if (is_dir ($nextpath) && $returnDirs) $r[$nextpath] = pathinfo($nextpath);
                    elseif (is_file ($nextpath) && $returnFiles) $r[$nextpath] = pathinfo($nextpath);
                    ///
                    if($getSubDirs && is_dir($nextpath)) $r = $r + $this->getArr_directory($nextpath, $returnDirs, $returnFiles, $getSubDirs);
                }
            }
        }
        closedir ($handle);
        return $r;
    }


    /**
     * Возвращает информацию о файле/папке в массиве
     *
     * @param $filePath
     * @param bool $fullInfo
     *
     * @return mixed
     *
     * @version 1.5
     */
    public function getArr_fileData($filePath, $fullInfo = false){
        $filePath = $this->getStr_normalizeDirSeparates($filePath);
        $patchInfo              = pathinfo($filePath);
        if(!$fullInfo) unset($patchInfo['dirname']);
        if(!$fullInfo) unset($patchInfo['basename']);
        $patchInfo['filetype']  = strtolower( @filetype($filePath) );
        if($fullInfo) $patchInfo['is_dir']    = is_dir($filePath);
        if($fullInfo) $patchInfo['is_file']   = is_file($filePath);
        if($fullInfo) $patchInfo['path']      = $filePath;
        $patchInfo['url']       = $this->getStr_urlFromRealPath($filePath);
        $patchInfo['size']      = @filesize($filePath);
        if($fullInfo) $patchInfo['sizeF']     = $this->getStr_sizeFormat($patchInfo['size']);
        ///
        if($fullInfo) $patchInfo['fileatime'] = @fileatime($filePath);
        $patchInfo['filectime'] = @filectime($filePath);
        $patchInfo['filemtime'] = @filemtime($filePath);
        if($fullInfo) $patchInfo['fileatimeF'] = date('Y-m-d H:i:s', $patchInfo['fileatime']);
        if($fullInfo) $patchInfo['filectimeF'] = date('Y-m-d H:i:s', $patchInfo['fileatime']);
        if($fullInfo) $patchInfo['filemtimeF'] = date('Y-m-d H:i:s', $patchInfo['fileatime']);
        ///
        if($fullInfo) $patchInfo['filegroup'] = filegroup($filePath);
        if($fullInfo) $patchInfo['fileinode'] = fileinode($filePath);
        if($fullInfo) $patchInfo['fileowner'] = fileowner($filePath);
        if($fullInfo) $patchInfo['fileperms'] = fileperms($filePath);
        ///
        if( isset($patchInfo['extension']) && in_array($patchInfo['extension'], array('jpg', 'jpeg', 'jpe', 'png', 'gif', 'swf')) ) { list($patchInfo['width'], $patchInfo['height']) = getimagesize($filePath); }
        return (object)$patchInfo;
    }


    /**
     * Возвращает форматированный вид размера файла из байтов
     * @param $size - INT килобайты
     *
     * @return string
     */
    public function getStr_sizeFormat($size) {
        $size = intval($size);
        if($size<1024) return $size." bytes";
        else if($size<(1024*1024)) { $size=round($size/1024,1); return $size." KB"; }
        else if($size<(1024*1024*1024)) { $size=round($size/(1024*1024),1); return $size." MB"; }
        else { $size=round($size/(1024*1024*1024),1); return $size." GB"; }
    }


    /**
     * Выполняет архивацию папки в ZIP архив
     * @param $pathInput
     * @param string $pathOut
     * @param string $arhiveName
     * @param string|bool $baseDirInArhive - базовая папка / путь внутри архива для всех запакованных файлов и папок. Если установить TRUE - в архиве будет корневая папка, которая была указана в качестве исходной.
     * @param bool $appendToArchive
     * @return bool|string
     */
    public function do_packDir($pathInput, $pathOut = '', $arhiveName = 'arhive.zip', $baseDirInArhive = true, $appendToArchive = false){
        $pathInput = $this->getStr_realPath($pathInput);
        if(!is_file($pathOut)) $this->do_foldersAutoCreate($pathOut);
        $pathOut = $pathOut == '' ? $pathInput : $this->getStr_realPath($pathOut);
        if( !file_exists($pathInput) ) { return false; }
        if($baseDirInArhive === true) { $baseDirInArhive = basename($pathInput).DIR_SEPARATOR; }
        if(!$appendToArchive && file_exists($pathOut.DIR_SEPARATOR.$arhiveName)) @unlink($pathOut.DIR_SEPARATOR.$arhiveName);
        $zip = new ZipArchive; // класс для работы с архивами
        if ($zip -> open($pathOut.DIR_SEPARATOR.$arhiveName, ZipArchive::CREATE) === TRUE){ // создаем архив, если все прошло удачно продолжаем
            $files = $this->getArr_directory( $pathInput, false );
            foreach($files as $path => $fileArr){
                $zip -> addFile($path, $baseDirInArhive.str_replace(rtrim($pathInput, DIR_SEPARATOR).DIR_SEPARATOR,'',$path));
            }
            $zip -> close(); // закрываем архив.
            return $pathOut;
        }else{
            return false;
        }
    }

    /**
     * Распаковывает ZIP архив
     * @param $archivePath
     * @param string $destinationDir
     * @return bool
     */
    public function do_unpackToDir($archivePath, $destinationDir = ''){
        $archivePath = $this->getStr_normalizeDirSeparates($archivePath);
        if(!file_exists($archivePath)) return false;
        if($destinationDir == '') { $destinationDir = dirname($archivePath); }
        $zip = new ZipArchive();
        if ($zip->open($archivePath) === true) {
            if(!$zip->extractTo($destinationDir)) return false;
            $zip->close();
            return true;
        }
        else return false;
    }


    /**
     * Получить HTML из файла, используя шаблонизатор
     * @param array $params - переменные для шаблонизатора
     * @param string $filePathOrName - путь или имя файла, лежащего рядом файла html, htm, tpl
     * @param bool $consoleSearchPaths
     *
     * @return string|void
     *
     * @version 1.1.0
     */
    public function getHtml_fromTpl($params = array(), $filePathOrName = null, $consoleSearchPaths = false){
        if(!is_string($filePathOrName) || !file_exists($this->getStr_realPath($filePathOrName)) || !is_file($filePathOrName) ){
            $searchArr = array(
                hiweb()->getArr_debugBacktrace(0,0,0,0,1,0,2,4),
                array_unique(hiweb()->array2()->merge(array('/','/tpl/'),hiweb()->getArr_debugBacktrace(1,1,1,1,0,0,2,3,'/','/'))),
                hiweb()->array2()->merge(array(''),hiweb()->getArr_debugBacktrace(1,1,1,1,0)),
                hiweb()->array2()->merge(array(''),hiweb()->getArr_debugBacktrace(1,1,1,1,0,0,2,4,'-')),
                array($filePathOrName,'-'.$filePathOrName),
                array('','.tpl')
            );
            $filePathOrName2 = $this->getStr_pathBySearch($searchArr, true);
            if($consoleSearchPaths) hiweb()->console(array('hiweb->file->getHtml_fromTpl',$searchArr, debug_backtrace()));
        }
        if( !@is_file($filePathOrName2) ){ hiweb()->console()->error('файл ['.$filePathOrName.'] не найден',1); return '<!--getHtml_fromTpl: файл ['.$filePathOrName.'] не был найден-->'; }
        ///
        if( !is_object(hiweb()->tpl()) ) { return file_get_contents($filePathOrName2); }
        ///
        $params = hiweb()->array2()->merge(hiweb()->globalValues, $params);
        if(is_array($params)){ foreach($params as $k => $v){ hiweb()->tpl()->assign($k, $v); } }
        $r = hiweb()->tpl()->fetch($filePathOrName2);
        $r = do_shortcode($r);
        hiweb()->tpl()->clearAllAssign();
        return $r;
    }

    /**
     * Получить HTML из TPL строки, используя шаблонизатор
     * @param array $params - переменные для шаблонизатора
     * @param string $tplStr - строка TPL
     *
     * @return string|void
     *
     * @version 1.1
     */
    public function getHtml_fromTplStr($tplStr = '', $params = array()){
        if( !is_object(hiweb()->tpl()) ) { return $tplStr; }
        ///
        $params = hiweb()->array2()->merge(hiweb()->globalValues, $params);
        if(is_array($params)){ foreach($params as $k => $v){ hiweb()->tpl()->assign($k, $v); } }
        $r = do_shortcode(hiweb()->tpl()->fetch('string:'.do_shortcode($tplStr)));
        hiweb()->tpl()->clearAllAssign();
        return $r;
    }


    /**
     * Возвращает существующий путь до файла из массива. array( array('var1','var2','var3'), array('var4','var5','var6') );
     *
     * @param array|string $arrFileParts - массив путей
     * @param bool $files - искать файл
     * @param bool $dirs - искать папку
     * @param bool $returnArr - TRUE -> вернуть массив созданных путей
     *
     * @return bool|string
     *
     * @version 1.2
     */
    function getStr_pathBySearch($arrFileParts = '', $files = true, $dirs = false, $returnArr = false){
        if(hiweb()->cacheByFileExists()) return hiweb()->cacheByFile();
        if(!is_array($arrFileParts)) { return hiweb()->cacheByFile(false); }
        $r = array();
        foreach($arrFileParts as $listN => $listVal) {
            $r[$listN] = array();
            if( is_array($listVal) ) {
                foreach($listVal as $listVal2){
                    //if(trim($listVal2) == '') continue;
                    if( isset($r[$listN-1]) ) { foreach($r[$listN-1] as $listVal3){ $r[$listN][] = $listVal3.$listVal2; } }
                    else { $r[$listN][] = $listVal2; }
                }
            } else {
                //if(trim($listVal) == '') continue;
                if( isset($r[$listN-1]) ) { foreach($r[$listN-1] as $listVal3){ $r[$listN][] = $listVal3.$listVal; } }
                else { $r[$listN][] = $listVal; }
            }
        }
        $r = array_unique($r[ count($r)-1 ]);
        if($returnArr) {return hiweb()->cacheByFile($r);}
        foreach($r as $path){ if(file_exists($path) && ((is_file($path) && $files) || (is_dir($path) && $dirs))) { return hiweb()->cacheByFile($path); } }
        return hiweb()->cacheByFile(false);
    }


    /**
     * Записывает данные `$dataMix` в формате HTML в файл. Это удобно для похоже на собственный лог-файл. Этой функцией можно в течении некоторого времени (установленного параметром `$autoDeleteOldFile`) многократно дозаписать информацию в один и тот же файл для дальнейшего анализа. По умолчанию все записывается в файл `log.html` в корне сайта.
     * @param $dataMix - значения
     * @param string $filePath - имя файла дампа
     * @param bool $append - не удалять предыдущие записи
     * @param int $autoDeleteOldFile - указать время в секундах, в течении которого старые записи не будут удаляться из файла
     *
     * @return int
     */
    function do_dumpToFile( $dataMix, $filePath = 'log.html', $append = true, $autoDeleteOldFile = 5 )
    {
        $filePath = $this->getStr_realPath($filePath);
        if( !file_exists(dirname($filePath)) ) { file_put_contents($this->getStr_realPath('error.txt'), dirname($filePath).' => not exists'); return false;}
        $returnStr = '<style type="text/css">.sep { border-bottom: 1px dotted #ccc; } .sepLast { margin-bottom: 35px; }</style>';
        $separatorHtml = '<div class="sep"></div>';
        $returnStr .= hiweb()->string()->getStr_dateTime().' / '.microtime(true).' / '.$separatorHtml.hiweb()->string()->getHtml_arrayPrint($dataMix).$separatorHtml;
        $fileContent = '';
        if( file_exists($filePath) && is_file($filePath) ) {
            $time = time();
            $filetime = filemtime($filePath);
            $timeDelta = $time - $filetime;
            if( $autoDeleteOldFile === false || $timeDelta > $autoDeleteOldFile ) { unlink($filePath); }
            else { $fileContent = file_get_contents($filePath); }
        }
        ///
        $returnStr = ($append ? $fileContent.$returnStr : $returnStr.$fileContent).'<div class="sepLast"></div>';
        return file_put_contents($filePath, $returnStr);
    }


    /**
     * Возвращает расширение файла, уть которого указан в аргументе $path
     * @param $path
     * @return string
     */
    public function getStr_fileExtension($path){
        $pathInfo = pathinfo($path);
        return isset($pathInfo['extension']) ? $pathInfo['extension'] : '';
    }


    /**
     * Чтение / запись в файл JSON, учитывая поиск
     * @param null $mix - если не указывать или оставить значние NULL, то будет произведен поиск файла и чтение из него информации. Если задать значение (кроме NULL), то в файл будет сохранена указанная переменная в формате JSON
     * @param null $path - если указать имя файла, то он будет учавствовать в поиске для чтения/записи в файл
     * @param bool $beautyJSON
     * @return null
     */
    public function json($path = null, $mix = null, $beautyJSON = true){
        if(is_null($mix)){
            $debugTrace = array_merge( hiweb()->getArr_debugBacktrace(0,0,1,0,0,0,2,2,DIR_SEPARATOR),hiweb()->getArr_debugBacktrace(1,1,0,0,0,0,3,3,DIR_SEPARATOR) );
            $searchFile = $this->getStr_pathBySearch(array(
                array($path,''),
                array('',dirname( hiweb()->array2()->getVal( debug_backtrace(), array(0,'file') ) )),
                array_merge(array(''), $debugTrace),
                array(DIR_SEPARATOR.$path,'.json','')
            ),1,0);
            if(is_string($searchFile)) { return $this->getMix_fromJSONFile($searchFile); }
        } else {
            if(is_null($path) || !file_exists($path) || !is_file($path) || !$this->getStr_fileExtension($path) == 'json'){
                $debugTrace = array_merge( hiweb()->getArr_debugBacktrace(0,0,1,0,0,0,2,2,DIR_SEPARATOR),hiweb()->getArr_debugBacktrace(1,1,0,0,0,0,3,3,DIR_SEPARATOR) );
                $dir = dirname( hiweb()->array2()->getVal( debug_backtrace(), array(0,'file') ) );
                $searchPath = $this->getStr_pathBySearch(array( $dir, array_merge($debugTrace,array('')) ),0,1);
                if(is_string($searchPath)) {
                    if(is_null($path)) { $path = basename($searchPath); }
                    if($this->getStr_fileExtension($path) != 'json') { $path .= '.json'; }
                    $path = $searchPath.DIR_SEPARATOR.$path;
                }
                return $this->do_varExportToFile($path, $mix, $beautyJSON);
            }
        }
        return null;
    }


    /**
     * Удалить HIWEB CACHE папку
     */
    public function do_clearCacheDir(){
        if(file_exists(HIWEB_DIR_CACHE) && is_dir(HIWEB_DIR_CACHE)) $this->do_unlinkDir(HIWEB_DIR_CACHE);
    }


}
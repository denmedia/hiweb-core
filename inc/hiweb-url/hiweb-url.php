<?php
/**
 * Created by PhpStorm.
 * User: denmedia
 * Date: 19.10.15
 * Time: 13:24
 */



class hiweb_url {


    /**
     * Возвращает текущий адрес URL
     */
    public function getStr_urlFull(){
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
        return rtrim( 'http' . ($https ? 's' : '') . '://' . $_SERVER['HTTP_HOST'],'/').$_SERVER['REQUEST_URI'];
    }


    /**
     * Возвращает корневой URL
     * @return string
     *
     * @version 1.2
     */
    public function getStr_baseUrl(){
        if(hiweb()->cacheExists()) return hiweb()->cache();
        $root = ltrim( hiweb()->getStr_baseDir(), DIR_SEPARATOR );
        $query = ltrim( hiweb()->file()->getStr_normalizeDirSeparates(dirname($_SERVER['PHP_SELF'])), '/' );
        $rootArr = array();$queryArr = array();
        foreach(array_reverse(explode('/',$root)) as $dir){ $rootArr[] = rtrim($dir.DIR_SEPARATOR.end($rootArr), DIR_SEPARATOR); }
        foreach(explode('/',$query) as $dir){ $queryArr[] = ltrim(end($queryArr).DIR_SEPARATOR.$dir,'/'); }
        $rootArr = array_reverse($rootArr); $queryArr = array_reverse($queryArr);
        $r = '';
        foreach($queryArr as $dir){ foreach($rootArr as $rootDir) { if($dir == $rootDir) {$r = $dir;break 2;} } }
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
        return hiweb()->cache(rtrim( 'http' . ($https ? 's' : '') . '://' . $_SERVER['HTTP_HOST'].'/'.$r, '/' ));
    }

    /**
     * Возвращает URL с измененным QUERY фрагмнтом
     * @param null $url
     * @param array $addData
     * @param array $removeKeys
     * @return string
     *
     * @version 1.2
     */
    public function getStr_urlQuery($url = null, $addData = array(), $removeKeys = array()){
        if(is_null($url) || trim($url) == '') $url = $this->getStr_urlFull();
        $url = explode('?', $url);
        $urlPath = array_shift($url);
        $query = implode('?', $url);
        ///
        $params = explode('&', $query);
        $paramsPair = array();
        foreach($params as $param){ list($key, $val) = explode('=',$param); $paramsPair[$key] = $val; }
        ///add
        $params = array_merge($paramsPair, $addData);
        $paramsPair = array();
        foreach($params as $key => $val){ if(!empty($key)) $paramsPair[] = implode('=', array($key, $val)); }
        ///remove
        foreach($removeKeys as $key => $val){ if(isset($params[$key])) unset($params[$key]); }
        ///
        return hiweb()->array2()->count($paramsPair) > 0 ? $urlPath.'?'.implode('&',$paramsPair) : $urlPath;
    }


    /**
     * Возвращает массив URL частей array(dirs => [...], args => [key => val,...])
     * @param string $url - укажите URL по необходимости, либо он будет взят из текущего адреса
     * @param string $dirsORargs - если указать 'dirs' или 'args', то из всего массива будет вернут только соответствующий массив
     * @param string $dirsORargsKey - если после указания аргумента $dirsORargs вложенный ключ указанного массива, будет вернуто только значение данного ключа
     * @return array|string
     * @version 2.0
     */
    public function getArr_requestUri($url = null, $dirsORargs = null, $dirsORargsKey = null){
        $urlRequest = trim(str_replace($this->getStr_baseUrl(),'',hiweb()->string()->isEmpty($url) ? $this->getStr_urlFull() : $url),'/');
        $dirs = array(); $args = array();
        if(strpos($urlRequest,'?')!==false){
            list($dirs,$params) = explode('?', $urlRequest);
            foreach(explode('&',$params) as $pair){
                list($k,$v) = explode('=',$pair);
                $args[$k] = $v;
            }
        } else {
            $dirs = $urlRequest;
        }
        $dirs = trim($dirs,'/');
        if(is_null($dirsORargs)) return array('dirs' => explode('/',$dirs), 'query' => $args);
        else if(is_null($dirsORargsKey)) return hiweb()->getVal_fromArr(array('dirs' => explode('/',$dirs), 'query' => $args), $dirsORargs);
        else return hiweb()->getVal_fromArr(array('dirs' => explode('/',$dirs), 'query' => $args), array($dirsORargs,$dirsORargsKey));
    }




}
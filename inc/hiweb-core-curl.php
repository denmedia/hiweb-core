<?php
/**
 * Created by PhpStorm.
 * User: denmedia
 * Date: 09.04.2015
 * Time: 10:53
 */

class hiweb_curl {

    static $useCache = false;
    static $cacheDir = 'hiweb-cache';
    static $cacheFileExtension = '.txt';
    static $cookieFile = false;

    //public $ipInfo_server = 'http://htmlweb.ru/analiz/whois_ip.php?ip=';
    //public $ipInfo_server = 'http://whois.domaintools.com/';
    public $ipInfo_server = 'http://2whois.ru/?t=whois&data=';
    public $ipInfo_post = 0;

    function __construct(){
        hiweb()->file()->do_foldersAutoCreate(WP_CONTENT_DIR.'/'.self::$cacheDir);
    }


    /**
     * Возвращает массив значений, отделив содержимое тега BODY:
     * array(
     *	'url' => исходный URL (нормализированный),
     *	'urlOld' => входящий URL,
     *	'urlParse' => парсированный URL,
     *	'startUrl' => стартовый URL,
     *	'headers' => массив HEADERS,
     *	'title' => найденный титл (если есть),
     *	'bodyLength' => длинна ответа (integer),
     *	'body' => ответ (string)
     * );
     * @version 1.0.0.1
     * @param $url
     * @param null $startUrl
     *
     * @return array|mixed|null
     */
    function getContent_fromUrl($url, $startUrl = null){
        $urlOld = $url;
        $url = hiweb()->string()->getStr_urlNormal($url, $startUrl);
        if($url == false) { return false; }
        if(self::$cookieFile == false) { self::$cookieFile = WP_CONTENT_DIR.'/'.self::$cacheDir.'cookie'.self::$cacheFileExtension; }
        $fileCache = WP_CONTENT_DIR.DIR_SEPARATOR.self::$cacheDir.DIR_SEPARATOR.hiweb()->string()->getStr_allowSymbols($url).self::$cacheFileExtension;
        $fileCache = hiweb()->file()->getStr_realPath($fileCache);
        $urlParse = parse_url($url);
        if(self::$useCache && file_exists($fileCache)){
            $r = hiweb()->file()->getMix_fromJSONFile($fileCache);
        } else {
            if(function_exists('curl')){
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
                curl_setopt($ch, CURLOPT_REFERER, 'https://'.$urlParse['host'].'/index.php');
                curl_setopt($ch, CURLOPT_COOKIEFILE, self::$cookieFile);
                curl_setopt($ch, CURLOPT_COOKIEJAR, self::$cookieFile);
                curl_setopt($ch, CURLOPT_COOKIESESSION, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

                //Устанавливаем опцию хождения по всем редиректам
                //curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

                //curl_setopt($ch, CURLOPT_VERBOSE, true);
                //curl_setopt($ch, CURLOPT_POST, 1);
                //curl_setopt($ch, CURLOPT_NOBODY,true);
                //curl_setopt($ch, CURLOPT_POSTFIELDS, base::get_strParam($postData));
                //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                //
                $response = curl_exec($ch);
                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                curl_close($ch);
            } else {
                $opts = array(
                    'http'=>array(
                        'method'=>"GET",
                        'header'=>"Accept-language: en\r\n" .
                            "Cookie: foo=bar\r\n"
                    )
                );
                $context = stream_context_create($opts);
                $response = file_get_contents($url, false, $context);
                //TODO: Заваершить функцию
            }
            if(!$response) { return false; }
            $headerArr = self::getArr_headersFromCurl(substr($response, 0, $header_size));
            $body = self::getStr_bodyFromHtml($response);
            //
            $r = array(
                'url' => $url,
                'urlOld' => $urlOld,
                'urlParse' => $urlParse,
                'startUrl' => $startUrl,
                'headers' => $headerArr,
                'title' => self::getStr_titleFromHtml($response),
                'bodyLength' => is_string($body) ? mb_strlen($body) : 0,
                'body' => $body
            );

            if(self::$useCache && $headerArr['http_code_arr']['code'] == '200'){
                hiweb()->file()->do_varExportToFile($fileCache,$r);
            }
        }

        return $r;

    }


    /**
     * Возвращает содержимое ответа сервера
     * @param $url - запрашиваемый URL
     * @param null $postData - POST переменные
     *
     * @param int $redirectCount
     * @return array
     *
     * @version 1.4
     */
    function getArr_contentFromURL($url, $postData = null,$redirectCount=0){
        $urlOld = $url;
        $url = hiweb()->string()->getStr_urlNormal($url);
        if($url == false) { return false; }
        if(self::$cookieFile == false) { self::$cookieFile = WP_CONTENT_DIR.self::$cacheDir.'cookie'.self::$cacheFileExtension; }
        //$fileCache = BASE_DIR.DIR_SEPARATOR.self::$cacheDir.DIR_SEPARATOR.hiweb()->string()->getStr_allowSymbols($url).self::$cacheFileExtension;
        $urlParse = parse_url($url);
        ////
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_REFERER, 'https://'.$urlParse['host'].'/index.php');
        curl_setopt($ch, CURLOPT_COOKIEFILE, self::$cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, self::$cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        if(is_array($postData) || is_string($postData)) {
            $postData = (is_array($postData)) ? http_build_query($postData) : $postData;
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        //curl_setopt($ch, CURLOPT_VERBOSE, true);
        //curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch, CURLOPT_NOBODY,true);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, base::get_strParam($postData));
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        //
        $response = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        if(!$response) { return false; }
        $headerArr = $this->getArr_headersFromCurl(substr($response, 0, $header_size));
        ///Redirect Way
        if(in_array(hiweb()->array2()->getVal($headerArr,array('http_code_arr','code')), array(301,302,303)) && $redirectCount > 0){
            return $this->getArr_contentFromURL(hiweb()->array2()->getVal($headerArr,'location'),$postData,intval($redirectCount)-1);
        }
        ///Else No Redirect
        $body = substr($response, $header_size);
        //
        $r = array(
            'url' => $url,
            'urlOld' => $urlOld,
            'urlParse' => $urlParse,
            'postData' => $postData,
            'response' => $response,
            'headers' => $headerArr,
            'dataLength' => is_string($body) ? mb_strlen($body) : 0,
            'data' => $body
        );
        ////
        return $r;
    }


    /**
     * Возвращает строку содержимого ответа сервера, минуя HEADERS
     *
     * @param $url
     * @param array $postData
     *
     * @return bool
     */
    public function getStr_contentFromURL($url, $postData = array()){
        $a = $this->getArr_contentFromURL($url, $postData);
        return (!$a || $a['data'] === false) ? false : $a['data'];
    }


    /**
     * Возвращает массив/значение полученного JSON-ответа от сервера, либо NULL в случае неудачи
     * @version 1.0.1.0
     * @param $url
     * @param array $postData
     * @param null $returnIdFail - значение, возвращаемое в случае неудачи/ошибки JSON
     *
     * @return array|mixed|null
     */
    public function getMix_JSON_fromURL($url, $postData = array(), $returnIdFail = null){
        $c = $this->getStr_contentFromURL($url, $postData);
        $r = json_decode($c, true);
        return (is_null($r) || json_last_error() > 0) ? (is_array($returnIdFail) ? $returnIdFail + $c : $returnIdFail) : $r;
    }

    /**
     * Возвращает массив HEADEER информации из хедера от cURL
     * @param $headerContent
     * @param bool $useIndex
     * @return array
     */
    function getArr_headersFromCurl($headerContent, $useIndex = false){
        $headers = array();
        // Split the string on every "double" new line.
        $arrRequests = explode("\r\n\r\n", $headerContent);
        // Loop of response headers. The "count() -1" is to
        //avoid an empty row for the extra line break before the body of the response.
        for ($index = 0; $index < count($arrRequests) -1; $index++) {
            $headerArr = array();
            foreach (explode("\r\n", $arrRequests[$index]) as $i => $line){
                if ($i === 0) { //HTTP CODE
                    $headerArr['http_code'] = $line;
                    $headerArr['http_code_arr'] = explode(' ', $headerArr['http_code']);
                    $tmp = $headerArr['http_code_arr'];
                    $headerArr['http_code_arr']['protocol'] = array_shift($tmp);
                    $headerArr['http_code_arr']['code'] = intval(array_shift($tmp));
                    $headerArr['http_code_arr']['comm'] = implode(' ',$tmp);
                    $tmp = explode('/', $headerArr['http_code_arr']['protocol']);
                    $headerArr['http_code_arr']['protocolName'] = array_shift($tmp);
                    $headerArr['http_code_arr']['protocolVersion'] =array_shift($tmp);
                    $headerArr['http_code_arr']['protocolOther'] = implode(' ',$tmp);
                }
                else {
                    list ($key, $value) = explode(': ', $line);
                    $headerArr[$key] = $value;
                    $headerArr[strtolower($key)] = $value;
                    $headerArr[strtoupper($key)] = $value;
                }
            }
            if($useIndex) $headers[$index] = $headerArr; else $headers = $headerArr;
        }
        return $headers;
    }


    function getStr_bodyFromHtml($html){
        preg_match("/<body[^>]*>(.*?)<\/body>/is", $html, $body);
        $body = preg_replace('~<script[^>]*>.*?</script>~si', '', $body[1]);
        $body = strtr($body, array('src='=>'data-src='));
        return $body;
    }

    function getStr_titleFromHtml($html) {
        $res = preg_match("/<title>(.*)<\/title>/siU", $html, $title_matches);
        if (!$res) return null;
        // Clean up title: remove EOL's and excessive whitespace.
        $title = preg_replace('/\s+/', ' ', $title_matches[1]);
        $title = trim($title);
        return $title;
    }

    /**
     * Вернуть innerhtml из тега
     * @param $html
     * @param string $htmlTag
     *
     * @return null|string
     */
    public function getStr_fromHtmlByTag($html, $htmlTag='body'){
        $pregStr = "/<".$htmlTag."[^>]*>(.*)<\/$htmlTag>/siU";
        $res = preg_match($pregStr, $html, $title_matches);
        if (!$res) return null;
        return trim(preg_replace('/\s+/', ' ', $title_matches[1]));
    }


    /**
     * Получени массива информации WhoIs LookUp Ip
     * @param $ip
     *
     * @return array
     */
    public function getArr_ipInfo($ip){
        if(substr_count($ip, '.') != 3) return false;
        ///
        $htmlArr = array();
        $responseHtml = file_get_contents($this->ipInfo_server.$ip);
        ///
        $start = strpos($responseHtml, '% The objects are in RPSL format.');
        $end = strpos($responseHtml, '% This query was served by the RIPE Database Query Service version');
        $preContent = substr($responseHtml, $start, $end - $start);
        foreach(explode(chr(10),$preContent) as $line){
            if(strpos($line, ':' === false)) continue;
            $lineArr = explode(':', $line);
            $k = trim(array_shift($lineArr)); $v = trim( implode(':',$lineArr) );
            if($k == '' || strlen($k) < 3 || strlen($v) < 3 || strpos($k, '%') === 0) continue;
            if(isset($htmlArr[$k])){
                if(is_array($htmlArr[$k]) && !in_array($v, $htmlArr[$k])) $htmlArr[$k][] = $v;
                elseif(!is_array($htmlArr[$k]) && $htmlArr[$k] != $v) $htmlArr[$k] = array($htmlArr[$k]);
            }
            else $htmlArr[$k] = $v;
        }
        ///
        return $htmlArr;
    }

    /**
     * Возвращает массив URL частей
     * @return array|\ArrayObject
     * @version 1.0
     */
    public function getArr_requestUri($key = null){
        $r = explode('/',trim($_SERVER["REQUEST_URI"],'/')); return is_null($key) ? $r : $r[$key];
    }

}
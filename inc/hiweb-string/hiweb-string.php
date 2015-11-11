<?php
/**
 * Created by PhpStorm.
 * User: denmedia
 * Date: 09.04.2015
 * Time: 13:02
 */

class hiweb_string {


    /**
     * Конвертация строки в допустимые символы
     * @param $strText
     * @param int $limit
     * @param bool $useRegister
     * @param bool $ifEmpty_generateRandomKey
     * @param array $additionSymbolsArr
     *
     * @return string
     *
     * $since 1.3
     */
    public function getStr_allowSymbols($strText, $limit = 99, $useRegister = false, $ifEmpty_generateRandomKey = true, $additionSymbolsArr = array()){
        $strText = urldecode($strText);
        $r = array();
        $strtr = hiweb()->array2()->merge(hiweb()->file()->json('allowSymbols.json'), $additionSymbolsArr);
        $arrText = $this->getArr_fromStr($strText);
        for($n=0;$n < count($arrText) && $n < $limit; $n++){
            $symb = $arrText[$n]; $isUpp = $symb === mb_strtoupper($symb);
            if(isset($strtr[mb_strtolower($symb)])) $symbConv = strtr( mb_strtolower($symb), $strtr );
            else $symbConv = '-';
            $r[] = ($isUpp && $useRegister) ? mb_strtoupper($symbConv) : $symbConv;
        }
        if($ifEmpty_generateRandomKey && count($r) == 0) $r = array( hiweb()->string()->getStr_random() );
        return implode('', $r);
    }


    /**
     * Разделение строки на символы в массив
     * @param $string
     *
     * @return array
     */
    public function getArr_fromStr( $string ) { return preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY); }

    /**
     * Возвращает набор слуайных символов
     * @param int $return_col
     * @param bool $in_use_latin
     * @param bool $in_use_number
     * @param bool $useReg
     * @param bool $useLog
     * @return string
     */
    public function getStr_random( $return_col=20, $in_use_latin = true, $in_use_number = true, $useReg = false, $useLog = true ) { $symb_arr=array();$symb_only_latin_arr=array();if($in_use_latin){for($list_n=ord('a');$list_n<ord('z');$list_n++){array_push($symb_arr,$list_n);array_push($symb_only_latin_arr,$list_n);}}if($in_use_latin and $useReg){for($list_n=ord('A');$list_n<ord('Z');$list_n++){array_push($symb_arr,$list_n);}}if($in_use_number){for($list_n=ord('0');$list_n<ord('9');$list_n++){array_push($symb_arr,$list_n);}}$return_key='';for($list_n=0;$list_n<$return_col;$list_n++){if($in_use_latin and $list_n==0)$return_key.=chr($symb_only_latin_arr[rand(0,count($symb_only_latin_arr)-1)]);else $return_key.=chr($symb_arr[rand(0,count($symb_arr)-1)]);}return $return_key; }


    /**
     * Конвертировать utf-8 в cp1251
     * @param string $utf8
     *
     * @return string
     */
    public function getStr_utf8_ansii($utf8 = ''){
        if (function_exists('iconv')) {$returnStr = @iconv('UTF-8', 'windows-1251//IGNORE', $utf8);}
        else {$returnStr = strtr($utf8, array("Р°"=>"а","Р±"=>"б","РІ"=>"в","Рі"=>"г","Рґ"=>"д","Рµ"=>"е","С‘"=>"ё","Р¶"=>"ж","Р·"=>"з","Рё"=>"и","Р№"=>"й","Рє"=>"к","Р»"=>"л","Рј"=>"м","РЅ"=>"н","Рѕ"=>"о","Рї"=>"п","СЂ"=>"р","СЃ"=>"с","С‚"=>"т","Сѓ"=>"у","С„"=>"ф","С…"=>"х","С†"=>"ц","С‡"=>"ч","С€"=>"ш","С‰"=>"щ","СЉ"=>"ъ","С‹"=>"ы","СЊ"=>"ь","СЌ"=>"э","СЋ"=>"ю","СЏ"=>"я","Рђ"=>"А","Р‘"=>"Б","Р’"=>"В","Р“"=>"Г","Р”"=>"Д","Р•"=>"Е","РЃ"=>"Ё","Р–"=>"Ж","Р—"=>"З","Р?"=>"И","Р™"=>"Й","Рљ"=>"К","Р›"=>"Л","Рњ"=>"М","Рќ"=>"Н","Рћ"=>"О","Рџ"=>"П","Р "=>"Р","РЎ"=>"С","Рў"=>"Т","РЈ"=>"У","Р¤"=>"Ф","РҐ"=>"Х","Р¦"=>"Ц","Р§"=>"Ч","РЁ"=>"Ш","Р©"=>"Щ","РЄ"=>"Ъ","Р«"=>"Ы","Р¬"=>"Ь","Р­"=>"Э","Р®"=>"Ю","С–"=>"і","Р†"=>"І","С—"=>"ї","Р‡"=>"Ї","С”"=>"є","Р„"=>"Є","Т‘"=>"ґ","Тђ"=>"Ґ",));}
        return $returnStr;
    }

    /**
     * Конвертировать cp1251 в utf-8
     * @param string $ansii
     *
     * @return string
     */
    public function getStr_ansii_utf8($ansii = ''){
        if (function_exists('iconv')) return iconv('windows-1251//IGNORE', 'UTF-8', $ansii);
        else return strtr($ansii, array_flip(array("Р°"=>"а","Р±"=>"б","РІ"=>"в","Рі"=>"г","Рґ"=>"д","Рµ"=>"е","С‘"=>"ё","Р¶"=>"ж","Р·"=>"з","Рё"=>"и","Р№"=>"й","Рє"=>"к","Р»"=>"л","Рј"=>"м","РЅ"=>"н","Рѕ"=>"о","Рї"=>"п","СЂ"=>"р","СЃ"=>"с","С‚"=>"т","Сѓ"=>"у","С„"=>"ф","С…"=>"х","С†"=>"ц","С‡"=>"ч","С€"=>"ш","С‰"=>"щ","СЉ"=>"ъ","С‹"=>"ы","СЊ"=>"ь","СЌ"=>"э","СЋ"=>"ю","СЏ"=>"я","Рђ"=>"А","Р‘"=>"Б","Р’"=>"В","Р“"=>"Г","Р”"=>"Д","Р•"=>"Е","РЃ"=>"Ё","Р–"=>"Ж","Р—"=>"З","Р?"=>"И","Р™"=>"Й","Рљ"=>"К","Р›"=>"Л","Рњ"=>"М","Рќ"=>"Н","Рћ"=>"О","Рџ"=>"П","Р "=>"Р","РЎ"=>"С","Рў"=>"Т","РЈ"=>"У","Р¤"=>"Ф","РҐ"=>"Х","Р¦"=>"Ц","Р§"=>"Ч","РЁ"=>"Ш","Р©"=>"Щ","РЄ"=>"Ъ","Р«"=>"Ы","Р¬"=>"Ь","Р­"=>"Э","Р®"=>"Ю","С–"=>"і","Р†"=>"І","С—"=>"ї","Р‡"=>"Ї","С”"=>"є","Р„"=>"Є","Т‘"=>"ґ","Тђ"=>"Ґ",)));
    }


    /**
     * Нормализация URL, так же возвращает парсированный URL
     * @version 1.1.1.0
     * @param $url
     * @param null $startUrl
     * @param bool $returnParseArray
     *
     * @return mixed|string
     */
    public function getStr_urlNormal($url, $startUrl = null, $returnParseArray = false){
        if(!is_string($url)) return false;
        $urlParse = parse_url(trim($url));
        if(!isset($urlParse['scheme'])) {
            if(is_string($startUrl) && trim($startUrl)!=''){
                $startUrlParse = parse_url($startUrl);
                $urlParse['scheme'] = $startUrlParse['scheme'];
                $urlParse['host'] = $startUrlParse['host'];
            } else {
                $urlParse['scheme'] = 'http';
                $urlParse['path'] = explode('/' ,$urlParse['path']);
                $urlParse['host'] = array_shift($urlParse['path']);
                $urlParse['path'] = '/'.implode('/', $urlParse['path']);
            }
        }
        //if(function_exists('idn_to_utf8')) { $urlParse['host'] = idn_to_utf8($urlParse['host']); }
        if(!isset($urlParse['path'])) { $urlParse['path'] = ''; }
        if(!isset($urlParse['query'])) { $urlParse['query'] = ''; } else { $urlParse['query'] = '?'.$urlParse['query']; }
        $urlParse['base'] = $urlParse['scheme'].'://'.$urlParse['host'];
        return $returnParseArray ? $urlParse : $urlParse['scheme'].'://'.$urlParse['host'].$urlParse['path'].$urlParse['query'];
    }


    /**
     * Выводить структуру заданной переменной
     * @param $mixed
     * @param int $depth - установить глубину массивов и объектов
     * @param bool $showObjects - раскрывать объекты
     * @return string
     *
     * @version 1.4
     */
    public function getHtml_arrayPrint($mixed, $depth = 4, $showObjects = true) {
        hiweb()->file()->css();
        if($depth < 1) return '<div class="hiweb-string-printarr">...</div>';
        $r = '';
        if(in_array(gettype($mixed), array('array','object'))) $r .= ' <span class="hiweb-string-printarr-gettype">['.gettype($mixed).']</span>';
        switch(gettype($mixed)){
            case 'array': $r .= '<ul>'; foreach($mixed as $k => $v){ $r .= '<li><span data-key>'.$k.'</span>: '.( $this->getHtml_arrayPrint($v, $depth - 1, $showObjects) ).'</li>'; } $r .= '</ul>'; break;
            case 'object': $r .= '<ul>'; if($showObjects){ foreach($mixed as $k => $v){ $r .= '<li><span data-key>'.$k.'</span>: '.( $this->getHtml_arrayPrint($v, $depth - 1, $showObjects) ).'</li>'; } } $r .= '</ul>'; break;
            case 'boolean': $r .= ($mixed ? 'TRUE' : 'FALSE'); break;
            case 'null': $r .= 'NULL'; break;
            default: $r .= (trim($mixed) == '' ? '<span class="hiweb-string-printarr-gettype">пусто</span>' : nl2br(htmlentities($mixed, ENT_COMPAT, 'UTF-8'))); break;
        }
        if(!in_array(gettype($mixed), array('array','object'))) $r .= ' <span class="hiweb-string-printarr-gettype">'.( gettype($mixed) == 'string' && mb_strlen($mixed) == 1 ? '[ord:<b>'.ord($mixed).'</b>]' : '' ).'['.gettype($mixed).']</span>';
        return "<div class='hiweb-string-printarr'>$r</div>";
    }


    /**
     * Возвращает строку из файла tpl, учитывая папку HIWEB_DIR_TPL
     * @param $params
     * @param $fileName
     *
     * @return bool
     */
    public function getHtml_fromTplFile($params, $fileName){
        if(file_exists($fileName.'.tpl')) { $path = $fileName.'.tpl'; }
        elseif(file_exists(HIWEB_DIR_TPL.DIR_SEPARATOR.$fileName.'.tpl')) { $path = HIWEB_DIR_TPL.DIR_SEPARATOR.$fileName.'.tpl'; }
        else { return 'getHtml_fromTplFile: файл ['.$fileName.'] не найден'; }
        ///
        if(is_array($params)){ foreach($params as $k => $v){ hiweb()->tpl()->assign($k, $v); } }
        ///
        $r = hiweb()->tpl()->fetch($path);
        hiweb()->tpl()->clearAllAssign();
        return $r;
    }


    /**
     * Возвращает форматированное дату и время
     *
     * @param int $time - необходимое время в секундах, если не указывать, будет взято текущее время
     * @param string $format - форматирование времени
     *
     * @return bool|string
     */
    public function getStr_dateTime($time = null, $format = 'Y-m-d H:i:s'){
        if(intval($time) < 100) $time = time();
        return date($format, intval($time));
    }


    public function getStr_dateWeek($weekNum = 0, $fullName = true){
        $weekNum = intval($weekNum);
        $a = array(
            array('вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'),
            array('восресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота')
        );
        return isset($a[$fullName ? 1 : 0][$weekNum]) ? $a[$fullName ? 1 : 0][$weekNum] : false;
    }


    /**
     * Возвращает дату и время наглядного вида
     * @param $date
     * @return string
     */
    public function getStr_dateTimeHuman( $date ) {$date=intval($date);$diff=time()-$date;if($diff<0){$days=ceil($diff/86400);}else{$days=floor($diff/86400);}$hd='';if($days==0){$hd='&#1089;&#1077;&#1075;&#1086;&#1076;&#1085;&#1103; &#1074; '.date('H:i',$date);}else{if($days>0){switch($days){case 1:$hd='&#1074;&#1095;&#1077;&#1088;&#1072; &#1074; '.date('H:i',$date);break;case 2:$hd='&#1087;&#1086;&#1079;&#1072;&#1074;&#1095;&#1077;&#1088;&#1072; &#1074; '.date('H:i',$date);break;}}else{switch($days){case -1:$hd='&#1079;&#1072;&#1074;&#1090;&#1088;&#1072; &#1074; '.date('H:i',$date);break;case -2:$hd='&#1087;&#1086;&#1089;&#1083;&#1077;&#1079;&#1072;&#1074;&#1090;&#1088;&#1072; &#1074; '.date('H:i',$date);break;case -3:$hd='&#1095;&#1077;&#1088;&#1077;&#1079; &#1090;&#1088;&#1080; &#1076;&#1085;&#1103;';break;}}}if($hd=='')$hd=date('d.m.Y H:i',$date);return '<acronym title="'.date('d.m.Y H:i',$date).', '.$this->getStr_dateWeek(date('N',$date)).'">'.$hd.'</acronym>';}

    public function getStr_dateTimeHumanPrecise( $date ) {$r=false;$a=preg_split("/[:\.\s-]+/",$this->getStr_dateTime($date));$d=time()-intval($date);if($d>0){if($d<3600){switch(floor($d/60)){case 0:case 1:return "<acronym title='".$this->getStr_dateTime($date)."'>&#1090;&#1086;&#1083;&#1100;&#1082;&#1086; &#1095;&#1090;&#1086;</acronym>";break;case 2:return "<acronym title='".$this->getStr_dateTime($date)."'>&#1090;&#1086;&#1083;&#1100;&#1082;&#1086; &#1095;&#1090;&#1086;</acronym>";break;case 3:return "<acronym title='".$this->getStr_dateTime($date)."'>&#1090;&#1088;&#1080; &#1084;&#1080;&#1085;&#1091;&#1090;&#1099; &#1085;&#1072;&#1079;&#1072;&#1076;</acronym>";break;case 4:return "<acronym title='".$this->getStr_dateTime($date)."'>&#1095;&#1077;&#1090;&#1099;&#1088;&#1077; &#1084;&#1080;&#1085;&#1091;&#1090;&#1099; &#1085;&#1072;&#1079;&#1072;&#1076;</acronym>";break;case 5:return "<acronym title='".$this->getStr_dateTime($date)."'>&#1087;&#1103;&#1090;&#1100; &#1084;&#1080;&#1085;&#1091;&#1090; &#1084;&#1080;&#1085;&#1091;&#1090;&#1099; &#1085;&#1072;&#1079;&#1072;&#1076;</acronym>";break;default:return "<acronym title='".$this->getStr_dateTime($date)."'>".floor($d/60).' &#1084;&#1080;&#1085;. &#1085;&#1072;&#1079;&#1072;&#1076;</acronym>';break;};}elseif($d<18000){switch(floor($d/3600)){case 1:return "<acronym title='".$this->getStr_dateTime($date)."'>&#1095;&#1072;&#1089; &#1085;&#1072;&#1079;&#1072;&#1076;</acronym>";break;case 2:return "<acronym title='".$this->getStr_dateTime($date)."'>&#1076;&#1074;&#1072; &#1095;&#1072;&#1089;&#1072; &#1085;&#1072;&#1079;&#1072;&#1076;</acronym>";break;case 3:return "<acronym title='".$this->getStr_dateTime($date)."'>&#1090;&#1088;&#1080; &#1095;&#1072;&#1089;&#1072; &#1085;&#1072;&#1079;&#1072;&#1076;</acronym>";break;case 4:return "<acronym title='".$this->getStr_dateTime($date)."'>&#1095;&#1077;&#1090;&#1099;&#1088;&#1077; &#1095;&#1072;&#1089;&#1072; &#1085;&#1072;&#1079;&#1072;&#1076;</acronym>";break;};}elseif($d<172800){if(date('d')==$a[2]){return "<acronym title='".$this->getStr_dateTime($date)."'>&#1089;&#1077;&#1075;&#1086;&#1076;&#1085;&#1103; &#1074; {$a[3]}:{$a[4]}</acronym>";}if(date('d',time()-86400)==$a[2]){return "<acronym title='".$this->getStr_dateTime($date)."'>&#1074;&#1095;&#1077;&#1088;&#1072; &#1074; {$a[3]}:{$a[4]}</acronym>";}if(date('d',time()-172800)==$a[2]){return "<acronym title='".$this->getStr_dateTime($date)."'>&#1087;&#1086;&#1079;&#1072;&#1074;&#1095;&#1077;&#1088;&#1072; &#1074; {$a[3]}:{$a[4]}</acronym>";}}}else{$d*=-1;if($d<3600){switch(floor($d/60)){case 0:case 1:return"<acronym title='$date'>&#1089;&#1077;&#1081;&#1095;&#1072;&#1089;</acronym>";break;case 2:return"<acronym title='$date'>&#1095;&#1077;&#1088;&#1077;&#1079; &#1076;&#1074;&#1077; &#1084;&#1080;&#1085;&#1091;&#1090;&#1099;</acronym>";break;case 3:return"<acronym title='$date'>&#1095;&#1077;&#1088;&#1077;&#1079; &#1090;&#1088;&#1080; &#1084;&#1080;&#1085;&#1091;&#1090;&#1099;</acronym>";break;case 4:return"<acronym title='$date'>&#1095;&#1077;&#1088;&#1077;&#1079; &#1095;&#1077;&#1090;&#1099;&#1088;&#1077; &#1084;&#1080;&#1085;&#1091;&#1090;&#1099;</acronym>";break;case 5:return"<acronym title='$date'>&#1095;&#1077;&#1088;&#1077;&#1079; &#1087;&#1103;&#1090;&#1100; &#1084;&#1080;&#1085;&#1091;&#1090;</acronym>";break;default:return"<acronym title='$date'>&#1095;&#1077;&#1088;&#1077;&#1079; ".floor($d/60).' &#1084;&#1080;&#1085;.</acronym>';break;};}elseif($d<18000){switch(floor($d/3600)){case 1:return"<acronym title='$date'>&#1095;&#1077;&#1088;&#1077;&#1079; &#1095;&#1072;&#1089;</acronym>";break;case 2:return"<acronym title='$date'>&#1095;&#1077;&#1088;&#1077;&#1079; &#1076;&#1074;&#1072; &#1095;&#1072;&#1089;&#1072;</acronym>";break;case 3:return"<acronym title='$date'>&#1095;&#1077;&#1088;&#1077;&#1079; &#1090;&#1088;&#1080; &#1095;&#1072;&#1089;&#1072;</acronym>";break;case 4:return"<acronym title='$date'>&#1095;&#1077;&#1088;&#1077;&#1079; &#1095;&#1077;&#1090;&#1099;&#1088;&#1077; &#1095;&#1072;&#1089;&#1072;</acronym>";break;};}elseif($d<172800){if(date('d')==$a[2]){return"<acronym title='$date'>&#1089;&#1077;&#1075;&#1086;&#1076;&#1085;&#1103; &#1074; {$a[3]}:{$a[4]}</acronym>";}if(date('d',time()-86400)==$a[2]){return"<acronym title='$date'>&#1079;&#1072;&#1074;&#1090;&#1088;&#1072; &#1074; {$a[3]}:{$a[4]}</acronym>";}if(date('d',time()-172800)==$a[2]){return"<acronym title='$date'>&#1087;&#1086;&#1089;&#1083;&#1077;&#1079;&#1072;&#1074;&#1090;&#1088;&#1072; &#1074; {$a[3]}:{$a[4]}</acronym>";}}$d*=-1;}$r="{$a[2]}.{$a[1]}";if($a[0]!=date('Y')OR $d>0){$r.='.'.$a[0];}$r.=" {$a[3]}:{$a[4]}";$date.=', '.$this->getStr_dateWeek((int) date('N',strtotime($date)));return "<acronym title='".$this->getStr_dateTime($date)."'>$r</acronym>";}


    /**
     * Возвращает массив, парсинг строки на символы и цифры
     * Пример, ab12cdEf3Gh456 =>> array( 'ab', '12', 'cdEf', '3', 'Gh', '456' )
     *
     * @param $parseStr
     * @param bool $numbers
     * @param bool $symbols
     *
     * @return array
     */
    public function getArr_parseStr_toSymbNumb($parseStr, $numbers = true, $symbols = true){
        $r = array();
        foreach($this->getArr_fromStr($parseStr) as $s){
            end($r); $lastVal= current($r); $lastKey = key($r);
            if($lastVal === false) { $r[] = $s; }
            else {
                $lastNum = is_numeric($lastVal);
                if(is_numeric($s) && $lastNum) $r[$lastKey] .= $s;
                else $r[] = $s;
            }
        }
        return $r;
    }


    /**
     * Возвращает URL с измененным QUERY фрагмнтом
     * @param null $url
     * @param array $addData
     * @param array $removeKeys
     * @return string
     *
     * @version 1.1
     */
    public function getStr_urlQuery($url = null, $addData = array(), $removeKeys = array()){
        return hiweb()->url()->getStr_urlQuery($url,$addData,$removeKeys);
    }


    /**
     * Возвращает форматированный JSON
     * @param string|mixed $json - строка JSON, лио что-то кроме строки будет переведено в JSON автоматически
     * @return string
     * @version 1.1
     */
    public function getStr_JsonIndent($json) {
        if(!is_string($json)) $json = json_encode($json);
        $result      = '';
        $pos         = 0;
        $strLen      = strlen($json);
        $indentStr   = '  ';
        $newLine     = "\n";
        $prevChar    = '';
        $outOfQuotes = true;
        for ($i=0; $i<=$strLen; $i++) {
            $char = substr($json, $i, 1);
            if ($char == '"' && $prevChar != '\\') {
                $outOfQuotes = !$outOfQuotes;
            } else if(($char == '}' || $char == ']') && $outOfQuotes) {
                $result .= $newLine;
                $pos --;
                for ($j=0; $j<$pos; $j++) { $result .= $indentStr; }
            }
            $result .= $char;
            if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
                $result .= $newLine;
                if ($char == '{' || $char == '[') { $pos ++; }
                for ($j = 0; $j < $pos; $j++) { $result .= $indentStr; }
            }
            $prevChar = $char;
        }
        return $result;
    }


    /**
     * Вернет TRUE, если $testStr являеться regex выражением
     * @param $testStr
     * @return int
     */
    public function getBool_isRegex($testStr){ return preg_match("/^\/[\s\S]+\/$/",$testStr) > 0; }
    /**
     * Вернет TRUE, если $testStr являеться regex выражением
     * @param $testStr
     * @return int
     */
    public function isRegex($testStr){return $this->getBool_isRegex($testStr);}


    /**
     * Возвращает TRUE, если используется JSON
     * @param string $haystack
     * @param bool|mixed $returnIfFalse - вернуть данное значение, в случае неудачи
     * @param bool $returnDecodeIfJson - вернуть конвертировнный JSON
     * @internal param mixed $returnIfNotJSON - вернуть это значение, если haystack не JSON
     * @return bool|mixed
     */
    public function getBool_isJSON($haystack, $returnIfFalse = false, $returnDecodeIfJson = true){
        if(!is_string($haystack) || empty($haystack)) return $returnIfFalse;
        $decode = json_decode($haystack, true);
        return (json_last_error() == JSON_ERROR_NONE) ? ($returnDecodeIfJson ? $decode : true) : $returnIfFalse;
    }

    /**
     * Возвращает TRUE, если используется JSON
     * @param string $haystack
     * @param bool|mixed $returnIfFalse - вернуть данное значение, в случае неудачи
     * @param bool $returnDecodeIfJson - вернуть конвертировнный JSON
     * @internal param mixed $returnIfNotJSON - вернуть это значение, если haystack не JSON
     * @return bool|mixed
     */
    public function isJSON($haystack, $returnIfFalse = false, $returnDecodeIfJson = true) { return $this->getBool_isJSON($haystack, $returnIfFalse, $returnDecodeIfJson); }



    /**
     * Возвращает TRUE, если значение пустое
     * @param $str
     * @return bool
     * @alias hiweb()->string()->isEmpty()
     * @version 1.1
     */
    public function getBool_isEmpty($str){
        return (!is_array($str) && (is_null($str) || $str === false || trim((string)$str) == '')) ? true : false;
    }

    /**
     * Возвращает TRUE, если значение пустое
     * @param $str
     * @return bool
     * @alias hiweb()->string()->getBool_isEmpty()
     */
    public function isEmpty($str){
        return $this->getBool_isEmpty($str);
    }


    public function getStr_ifEmpty($str, $ifEmpty = ''){
        return $this->isEmpty($str) ? $ifEmpty : $str;
    }


}
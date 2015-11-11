<?php
/**
 * Created by PhpStorm.
 * User: denmedia
 * Date: 28.04.2015
 * Time: 9:02
 */



class hiweb_array {


    public function __construct(){
        return 'TEST';
    }


    /**
     * Возвращает массив, соединенный из двух массивов
     * @param $array1
     * @param $array2
     * @param bool $createEmptyArr - если один их параметров не массив, то не соединять массив, иначе создавать <b>array(параметр)</b>
     * @return array
     */
    public function merge($array1, $array2, $createEmptyArr = true){
        if(!is_array($array1)) $array1 = $createEmptyArr ? array() : array($array1);
        if(!is_array($array2)) $array2 = $createEmptyArr ? array() : array($array2);
        return array_merge($array1, $array2);
    }


    /**
     * Возвращает массив слитый из двух, исключая несовпадающие ключи второго массива
     * @param $array1
     * @param $array2
     * @param bool $createEmptyArr
     */
    //TODO написать функцию
    public function mergeExclude($array1, $array2, $createEmptyArr = true){

    }


    /**
     * Возвращает значение ключа по его индексу
     * @param array $array
     * @param int|array $index = номер (массив номеров) индекса значения. Напрмиер 0 - первый ключ. Чтобы получить последний ключ, укажите -1, так же - 2 вернет предпоследний ключ. Если индекс ключа превысит
     * @return null
     */
    public function getVal_byIndex($array, $index = 0){
        if(!is_array($array)) return null;
        $index = $this->getArr($index);
        $indexThis = intval( array_shift($index) );
        $count = count($array);
        if($indexThis >= $count) $indexThis = $count - 1;
        else if($indexThis < 0 && abs($indexThis) <= $count) $indexThis = $count - abs($indexThis);
        elseif( $indexThis < 0 ) $indexThis = 0;
        if($this->count($index) == 0) return $this->getVal(array_values($array), $indexThis);
        else return $this->getVal(array_values($this->getVal_byIndex($array,$indexThis)), $index);
    }


    /**
     * Возвращает массив, соединенный из двух массивов, с учетом их ключей и значений
     * @param $array1 - начальный массив
     * @param $array2 - приоритетный массив
     * @param bool|false $ifSameKey_doArr - значения с одинаковыми строковыми ключами превращать в массив: array(a => 1) + array(a => 2) = array(a => array(1,2))
     * @param bool|true $ifOneArr_doMergeArr - если началное значение является массивом, приоритетное значение будет добавлено к первому массиву, если он сам не массив
     * @param bool|true $ifSameNumKey_doNewKey - если ключи нумерованные, то приоритетное значние добавлять к результативному массиву
     * @return array
     */
    public function mergeRecursive($array1,$array2, $ifSameKey_doArr = false, $ifOneArr_doMergeArr = true, $ifSameNumKey_doNewKey = true){
        $r = array();
        if(!is_array($array1)) $array1 = array($array1);
        if(!is_array($array2)) $array2 = array($array2);
        ///
        foreach(array_unique(array_merge(array_keys($array1),array_keys($array2))) as $k){
            $v = null;
            if(isset($array1[$k]) && isset($array2[$k])){
                if($ifSameKey_doArr){
                    if($ifOneArr_doMergeArr){ $v = $this->merge($array1[$k],$array2[$k]); }
                    else { $v = array($array1[$k],$array2[$k]); }
                } elseif($ifOneArr_doMergeArr) {
                    if(is_array($array1[$k])) { $v = $this->merge($array1[$k],$array2[$k]); }
                    else { $v = $array2[$k]; }
                } else $v = $array2[$k];
            } else {
                $v = isset($array2[$k]) ? $array2[$k] : $array1[$k];
            }
            ///
            if(!is_string($k) && $ifSameNumKey_doNewKey) { $r[] = $v; }
            else $r[$k] = $v;
        }
        return $r;
    }


    /**
     * Возвращает TRUE, если $mix не массив, либо пустой
     * @param $mix - массив
     * @param bool $nullVal - если единственное значение null - считать массив пустым
     * @return bool
     */
    public function getBool_empty($mix, $nullVal = true){
        return !is_array($mix) || count($mix) == 0 || (count($mix) == 1 && is_null(array_shift($mix)) && $nullVal);
    }


    /**
     * Возвращает массив array(ключ => номер найденного) или FALSE - если ничего не найдено
     * @param array $haystack - массив, в котором искать
     * @param string $needle - необходимый фрагмент для поиска
     * @return array|bool
     */
    public function strPos($haystack = array(), $needle = ''){
        $r = array();
        foreach($haystack as $k => $v){ $strpos = strpos($v, $needle); if($strpos !== false) $r[$k] = $strpos; }
        return count($r) == 0 ? false : $r;
    }

    /**
     * Возвращает массив array(ключ => номер найденного) или FALSE - если ничего не найдено
     * @param array $needle - массив фрагментов для поиска
     * @param string $haystack - строка, в которой произвести поиск
     * @return array|bool
     */
    public function strPos2($needle = array(), $haystack = ''){
        $r = array();
        foreach($needle as $k => $v){ $strpos = strpos($haystack, $v); if($strpos !== false) $r[$k] = $strpos; }
        return count($r) == 0 ? false : $r;
    }

    /**
     * Возвращает массив найденных совпадений массива фрагментов в массиве
     * @param array $haystack
     * @param array $needle
     * @return array|bool
     */
    public function strPosArrays($haystack = array(), $needle = array()){
        $r = array();
        if(!is_array($haystack) || !is_array($needle)) return false;
        foreach($haystack as $k => $v){ foreach($needle as $k2 => $v2){ $strpos = strpos($v, $v2); if($strpos !== false) $r[$k][$v] = $strpos; } }
        return count($r) == 0 ? false : $r;
    }


    /**
     * Возвращает значение ключа из массива, а так же вложенные значения, например array1(key1 => array2(key2 => value))
     * @param array|object $haystack - целевой массив
     * @param string|integer|array $keyMix - ключ (массив вложенных ключей) в целевом массиве
     * @param mixed $def - вернуть значение, если значение не найдено
     * @return mixed
     *
     * @version 1.2
     */
    public function getVal($haystack = array(), $keyMix = '', $def = null){
        if(is_object($haystack)) { $haystack = (array)$haystack;}
        if(is_array($keyMix) && count($keyMix) > 1) { $key = array_shift($keyMix); return $this->getVal( $this->getVal($haystack, $key, $def), $keyMix, $def ); }
        elseif(is_array($keyMix) && count($keyMix) == 1) $keyMix = array_shift($keyMix);
        return isset($haystack[$keyMix]) ? $haystack[$keyMix] : $def;
    }

    /**
     * Возвращает массив, сконвертированный из $mix
     * @param $mix
     * @param null $subKey - если $mix - массив, то из него можно извлеч значение ключа и сконвертировать его в массив
     * @return array
     */
    public function getArr($mix, $subKey = null){
        if(!is_null($subKey) && !is_bool($subKey)) $mix = $this->getVal($mix, $subKey);
        return (is_array($mix) || is_null($mix)) ? $mix : array($mix);
    }

    /**
     * Возвращает значение, найденное по ключу в массиве, исключая вложенные массивы
     * @param array $haystack - целевой массив
     * @param string $keyMix - список проверяемых ключей
     * @param null $def - значение, которое будлет вернуто в случае неудачи
     * @return null
     *
     * @version 1.0
     */
    public function getValNext($haystack = array(), $keyMix = '', $def = null){
        if(is_object($haystack)) $haystack = (array)$haystack;
        if(is_array($keyMix)) { foreach($keyMix as $key){ if(isset($haystack[$key])) return $haystack[$key]; } }
        return $def;
    }


    /**
     * Возвращает разбитую строку на массив через делимитер, сократив пустоты в разбитых частях возвращаемого массива
     * @param $delimiter - делимитер
     * @param $haystack - целевая строка
     * @param bool $returnEmptyParts - возвращать пустые части
     * @param bool $returnEmptyArray - возвращать пустой массив, либо FALSE
     * @return array|bool
     */
    public function explodeTrim($delimiter, $haystack, $returnEmptyParts = true, $returnEmptyArray = true){
        if(!is_string($haystack)) return $returnEmptyArray ? array() : false;
        $r = array();
        foreach(explode($delimiter, $haystack) as $part){ if($returnEmptyParts || trim($part)!='') $r[] = $part; }
        return ($returnEmptyArray || count($r) > 0) ? $r : false;
    }


    /**
     * Возвращает количество ключей, включая количество вложенных ключей
     * @param $haystack - массив
     * @param null $keyMix - укажите вложенные массивы в текущий массив (не обязательно, если массив простой)
     * @return int
     */
    public function count($haystack, $keyMix = null){
        if(!is_null($keyMix)) { $haystack = $this->getVal($haystack, $keyMix); }
        if(!is_array($haystack)) return 0;
        return count($haystack);
    }


}
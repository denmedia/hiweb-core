<?php
/**
 * Created by PhpStorm.
 * User: denmedia
 * Date: 19.07.2015
 * Time: 21:34
 */


class hiweb_console {

    public $_infos = array();
    public $_warns = array();
    public $_errors = array();
    public $mess = array();
    public $_mess = array();


    public function __construct($info = null){
        if(!is_null($info)) $this->info($info);
        //if(!hiweb()->wp()->is_ajax()) {add_action('shutdown', array($this,'echo_footer'),10);}
    }

    /**
     * Вывести информацию в консоль
     * @param $info - информация
     * @param bool $debugMod - дополнительная информация
     */
    public function info($info, $debugMod = false){
        //check call from __construct unction
        $deb = debug_backtrace();
        $function = hiweb()->array()->getVal($deb,array(1,'function'));
        $class = hiweb()->array()->getVal($deb,array(1,'class'));
        $callFromConstruct = $function == '__construct' && $class == 'hiweb_console';
        //
        $this->_infos[] = $info;
        $this->mess['info'][] = $info;
        $this->_mess[] = array(
            'data' => $info,
            'type' => 'info',
            'debug' => $debugMod,
            'microtime' => microtime(1),
            'file' => hiweb()->getStr_debugBacktraceFunctionLocate($callFromConstruct ? 4 : 3),
            'function' => hiweb()->getStr_debugBacktraceFunctionTrace($callFromConstruct ? 4 : 3)
        );
    }

    /**
     * Вывести предупреждение в консоль
     * @param $info - информация
     * @param bool $debugMod - дополнительная информация
     */
    public function warn($info, $debugMod = false){
        $this->_warns[] = $info;
        $this->mess['warn'][] = $info;
        $this->_mess[] = array(
            'data' => $info,
            'type' => 'warn',
            'debug' => $debugMod,
            'microtime' => microtime(1),
            'file' => hiweb()->getStr_debugBacktraceFunctionLocate(2),
            'function' => hiweb()->getStr_debugBacktraceFunctionTrace(2)
        );
    }

    /**
     * Вывести в консоль ошибку
     * @param $info - информация
     * @param bool $debugMod - дополнительная информация
     *
     * @version 1.1
     */
    public function error($info, $debugMod = false){
        $this->_errors[] = $info;
        $this->mess['error'][] = $info;
        $this->_mess[] = array(
            'data' => $info,
            'type' => 'error',
            'debug' => $debugMod,
            'microtime' => microtime(1),
            'file' => hiweb()->getStr_debugBacktraceFunctionLocate(2),
            'function' => hiweb()->getStr_debugBacktraceFunctionTrace(2)
        );
    }

    public function echo_info($info){
        echo 'console.info('.json_encode($info).');';
    }

    public function echo_warn($info){
        echo 'console.warn('.json_encode($info).');';
    }

    public function echo_error($info){
        echo 'console.error('.json_encode($info).');';
    }

    public function echo_footer(){
        echo '<script>';
        foreach($this->_mess as $info){ $this->{'echo_'.$info['type']}(
            $info['debug'] ?
                (is_array($info['data']) ?
                    array_merge( array('► '.$info['function'],'► '.$info['file']), $info['data'] ) :
                    $info['function'].' : '.$info['data'].chr(13).chr(10).'► '.$info['file']) :
                $info['data']
        ); }
        /*foreach(hiweb()->console()->_infos as $info){ hiweb()->console()->echo_info($info); }
        foreach(hiweb()->console()->_warns as $info){ hiweb()->console()->echo_warn($info); }
        foreach(hiweb()->console()->_errors as $info){ hiweb()->console()->echo_error($info); }*/
        echo '</script>';
    }


}
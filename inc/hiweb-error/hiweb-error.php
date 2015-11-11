<?php
/**
 * Created by PhpStorm.
 * User: denmedia
 * Date: 14.07.2015
 * Time: 18:36
 */



class hiweb_error{

    public $showBacktrace = false;
    public $footerErrorsHtml = array();


    function __construct($showBacktrace = false){
        if(hiweb()->wp()->is_ajax()) return;
        hiweb()->file()->css('hiweb-error/style');
        $this->showBacktrace = $showBacktrace;
        @ini_set('display_errors','off');
        error_reporting( E_ALL & ~E_NOTICE );
        @ini_set('error_reporting', E_ALL);
        if(!defined('WP_DEBUG')) define('WP_DEBUG', true);
        if(!defined('WP_DEBUG_DISPLAY')) define('WP_DEBUG_DISPLAY', true);
        set_error_handler(array($this,'errorHandler'));
        //$this->errorFatal(E_ALL^E_NOTICE); // will die on any error except E_NOTICE
        register_shutdown_function(array($this,'errorFatal'));
    }


    /**
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @version 1.2
     */
    public function errorHandler($errno, $errstr, $errfile, $errline){
        if( preg_match('/(wp-admin|wp-include)/g',$errfile) > 0 ) return;
        $errno = $errno & error_reporting();
        if($errno == 0) return;
        if(!defined('E_STRICT'))            define('E_STRICT', 2048);
        if(!defined('E_RECOVERABLE_ERROR')) define('E_RECOVERABLE_ERROR', 4096);
        $r = "<p class='hiweb-error-line'><b>";
        switch($errno){
            case E_ERROR:               $r .= "Fatal Error";            break;
            case E_WARNING:             $r .= "Warning";                break;
            case E_PARSE:               $r .= "Parse Error";            break;
            case E_NOTICE:              $r .= "Notice";                 break;
            case E_CORE_ERROR:          $r .= "Core Error";             break;
            case E_CORE_WARNING:        $r .= "Core Warning";           break;
            case E_COMPILE_ERROR:       $r .= "Compile Error";          break;
            case E_COMPILE_WARNING:     $r .= "Compile Warning";        break;
            case E_USER_ERROR:          $r .= "User Error";             break;
            case E_USER_WARNING:        $r .= "User Warning";           break;
            case E_USER_NOTICE:         $r .= "User Notice";            break;
            case E_STRICT:              $r .= "Strict Notice";          break;
            case E_RECOVERABLE_ERROR:   $r .= "Recoverable Error";      break;
            default:                    $r .= "Unknown error ($errno)"; break;
        }
        $r .= ":</b> <i>".nl2br($errstr)."</i><br>File: <b><u>$errfile</u></b> on line <b>$errline</b>\n";
        if($this->showBacktrace && function_exists('debug_backtrace')){
            $r .= "<div style='font-size: 10px;'>";
            $backtrace = debug_backtrace();
            array_shift($backtrace);
            foreach($backtrace as $i=>$l){
                $r .= "[$i] in function <b>".(isset($l['class']) ? "{$l['class']}" : '')."".(isset($l['type']) ? "{$l['type']}" : '')."{$l['function']}</b>";
                if(isset($l['file'])) $r .= " in <b><u>{$l['file']}</u></b>";
                if(isset($l['line'])) $r .= " on line <b>{$l['line']}</b>";
                $r .= "\n";
            }
            $r .= "</div>";
        }
        $r .= "</p>";
        if($errno == E_ERROR) print $r;
        else $this->putToFooter($r);
    }

    public function errorFatal(){
        $error = error_get_last();
        return $this->errorHandler($error["type"], $error["message"], $error["file"], $error["line"]);
    }

    public function putToFooter($errorHtml){
        $this->footerErrorsHtml[] = $errorHtml;
        $this->footerErrorsHtml = array_unique($this->footerErrorsHtml);
        add_action( 'wp_footer', array($this,'echo_footerErrorsHtml') );
        add_action( 'admin_footer', array($this, 'getHtml_footerErrors') );
    }

    public function echo_footerErrorsHtml(){
        echo implode('',hiweb()->error()->footerErrorsHtml);
    }

    public function getHtml_footerErrors(){
        echo implode('',hiweb()->error()->footerErrorsHtml);
    }

}
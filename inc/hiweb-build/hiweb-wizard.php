<?php
/**
 * Created by PhpStorm.
 * User: d9251
 * Date: 30.08.2015
 * Time: 20:21
 */


class hiweb_wizard{

    public function __construct(){
        hiweb()->file()->css('hiweb-wizard.tip.min');
        hiweb()->file()->css('hiweb-wizard');
        hiweb()->file()->js('hiweb-wizard.tip.min');
        hiweb()->file()->js('imagesloaded.pkg.min');
        hiweb()->file()->js('hiweb-wizard-autoload');
    }


    public function getHtml_helpPoint($params=null, $content=null, $smarty=null, &$repeat=null, $template=null){
        if(is_null($content) || empty($content)) return;
        return '<span class="hiweb-wizard-helpPoint dashicons dashicons-editor-help"><span class="hiweb-wizard-helpPoint-content">'.$content.'</span></span>';
    }


    public function getHtml_helpPointImage($params=null, $content=null, $smarty=null, &$repeat=null, $template=null){
        if(is_null($content) || empty($content)) return;
        $img = hiweb()->file()->getStr_pathBySearch(array(
            array(
                $content,
                HIWEB_CORE_DIR.DIR_SEPARATOR.$content,
                HIWEB_CORE_DIR.DIR_SEPARATOR.'inc'.DIR_SEPARATOR.$content,
                HIWEB_CORE_DIR.DIR_SEPARATOR.'inc'.DIR_SEPARATOR.'hiweb-wizard'.DIR_SEPARATOR.'img'.DIR_SEPARATOR.$content,
                HIWEB_CORE_DIR.DIR_SEPARATOR.'inc'.DIR_SEPARATOR.'hiweb-wizard'.DIR_SEPARATOR.$content,
                HIWEB_DIR_ASSET.DIR_SEPARATOR.$content
            ),
            array(
                '',
                '.png',
                '.jpg'
            )
        ));
        if(is_string($img)) return '<span class="hiweb-wizard-helpPoint hiweb-wizard-helpPointImage dashicons dashicons-editor-help"><span class="hiweb-wizard-helpPoint-content"><img src="'.hiweb()->file()->getStr_urlFromRealPath($img).'"/></span></span>';
        else {
            hiweb()->console()->error(array('hiweb->wizard->getHtml_helpPointImage', 'error : file ['.$content.'] don\'t find...',hiweb()->getStr_debugBacktraceFunctionLocate()));
        }
    }

}
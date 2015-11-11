/**
 * Created by denmedia on 28.04.2015.
 */


jQuery(document).ready(function(){


    ////hiweb_cms_title
    jQuery('#hiweb_cms_title [name="hiweb_cms_title_mod"]').change(function(){
        if( jQuery(this).prop('checked') && jQuery(this).val() == 'custom' ) jQuery('#hiweb_cms_title [data-hiweb-cms-title]').slideDown();
        else jQuery('#hiweb_cms_title [data-hiweb-cms-title]').slideUp();
    }).trigger('change');


});
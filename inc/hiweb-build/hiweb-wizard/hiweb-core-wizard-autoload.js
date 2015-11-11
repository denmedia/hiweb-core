/**
 * Created by d9251 on 30.08.2015.
 */


jQuery(document).ready(function(){

    jQuery('.hiweb-core-wizard-helpPoint').live('mouseenter', function(){
        var hiwebTip = jQuery(this).hiwebTip({
            content: {
                text: jQuery(this).children('.hiweb-core-wizard-helpPoint-content').html()
            },
            position: {
                viewport: jQuery(window),
                my: 'bottom center',  // Position my top left...
                at: 'top center' // at the bottom right of...
            },
            style: {
                classes: 'hiwebTip-shadow ' + (jQuery(this).hasClass('hiweb-core-wizard-helpPointImage') ? 'hiwebTip-dark hiweb-wizard-helpPointImage' : 'hiwebTip-light '),
                width: 'auto',
                zIndex: 110000
            }
        });
        jQuery(this).hiwebTip().show();
    });

});
/**
 * Created by denmedia on 20.07.2015.
 */

jQuery(document).ready(function(){

    ////hiweb_cms_adminmenu
    jQuery('.hiweb-core-settings-adminmenu-table [data-enable]').change(function(){
        var cuser = jQuery('#hiweb-core-settings-adminmenu-currentuser').val();
        var b = jQuery(this).prop('checked');
        var id = jQuery(this).closest('[data-cell]').attr('data-adminmenu-id');
        var user = jQuery(this).closest('[data-cell]').attr('data-user-login');
        ///
        if(user == cuser){
            if(!b) { jQuery('#'+id+'').hide(); }
            else { jQuery('#'+id+'').show(); }
        }
    }).each(function(){
        var cuser = jQuery('#hiweb-core-settings-adminmenu-currentuser').val();
        var b = jQuery(this).prop('checked');
        var id = jQuery(this).closest('[data-cell]').attr('data-adminmenu-id');
        var user = jQuery(this).closest('[data-cell]').attr('data-user-login');
        ///
        if(user == cuser){
            if(!b) { jQuery('#'+id+'').hide(); }
            else { jQuery('#'+id+'').show(); }
        }
    });
    jQuery('.hiweb-core-settings-adminmenu-table [data-text]').keyup(function(){
        var text = jQuery(this).val();
        var cuser = jQuery('#hiweb-core-settings-adminmenu-currentuser').val();
        var id = jQuery(this).closest('[data-cell]').attr('data-adminmenu-id');
        var user = jQuery(this).closest('[data-cell]').attr('data-user-login');
        ///
        if(user == cuser){
            var wp_menu = jQuery('#'+id+' .wp-menu-name');
            if(text != '') {
                var child = wp_menu.children();
                if( child.length > 0 ) wp_menu.html(text + ' ').append(child);
                else wp_menu.html(text);
            } else { wp_menu.html( jQuery(this).closest('[data-line]').find('[data-adminmenu-name]').html() ); }
        }
    });
    jQuery('#hiweb-core-settings-adminmenu-submit').click(function(){
        jQuery('#hiweb-core-settings-adminmenu-submit').addClass('button-disabled').attr('disable', 'disable');
        var data = {};
        jQuery('.hiweb-core-settings-adminmenu-table th[data-user]').each(function(){
            data[jQuery(this).attr('data-user-id')] = {};
        });
        jQuery('.hiweb-core-settings-adminmenu-table [data-cell]').each(function(){
            var t = jQuery(this);
            var user = t.attr('data-user-id');
            var enable = t.find('[data-enable]').prop('checked');
            var slug = t.attr('data-adminmenu-slug');
            var text = t.find('[data-text]').val();
            data[user][slug] = {enable: enable, text: text};
        });
        ////
        jQuery.ajax({
            url: '/wp-admin/admin-ajax.php?action=hiweb-settings-cms-adminmenu',
            type: 'post',
            data: {data:data},
            success: function(data){
                jQuery('#hiweb-core-settings-adminmenu-submit').hide();
                jQuery('#hiweb-core-settings-adminmenu-done').fadeIn();
                setTimeout(function(){
                    jQuery('#hiweb-core-settings-adminmenu-submit').removeClass('button-disabled').removeAttr('disable');
                    jQuery('#hiweb-core-settings-adminmenu-submit').fadeIn();
                    jQuery('#hiweb-core-settings-adminmenu-done').hide();
                }, 2500);
            },
            error: function(data){ console.error(data); }
        })
    });



});

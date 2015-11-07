/**
 * Created by denmedia on 20.07.2015.
 */

jQuery(document).ready(function(){

    ////hiweb_cms_adminmenu
    jQuery('.hiweb-core-settings-adminmenu-table tr').on('mouseover mouseout', function(e){
        var t = jQuery(this);
        if(t.is('[data-menu-slug]')) {
            var slug = t.attr('data-menu-slug');
            var menuA = jQuery('a[href="'+slug+'"]');
            ///HightLight
            if(e.type == 'mouseover' ) menuA.parent().addClass('hiweb-core-settings-adminmenu-hightlight');
            else menuA.parent().removeClass('hiweb-core-settings-adminmenu-hightlight');
            //jQuery('a[href="'+slug+'"]').closest('li').trigger(e.type);
            ///
        }
    });

    jQuery('.hiweb-core-settings-adminmenu-table select').on('change', function(){
        jQuery('.hiweb-core-settings-adminmenu-table tr').each(function(){
            var tr = jQuery(this);
            if(tr.is('[data-menu-slug]')){
                var slug = tr.attr('data-menu-slug');
            }
        });
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

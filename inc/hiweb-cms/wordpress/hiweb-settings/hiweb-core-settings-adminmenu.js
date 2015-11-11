/**
 * Created by denmedia on 20.07.2015.
 */

jQuery(document).ready(function(){

    ////hiweb_cms_adminmenu
    jQuery('.hiweb-settings-adminmenu-table tr').on('mouseover mouseout', function(e){
        var t = jQuery(this);
        if(t.is('[data-menu-slug]')) {
            var slug = t.attr('data-menu-slug');
            var menuA = jQuery('a[href="'+slug+'"]');
            ///HightLight
            if(e.type == 'mouseover' ) menuA.parent().addClass('hiweb-settings-adminmenu-hightlight');
            else menuA.parent().removeClass('hiweb-settings-adminmenu-hightlight');
            ///
        }
    });
    ///Name Change
    jQuery('.hiweb-settings-adminmenu-table input[data-type="rename"]').on('keydown', function(){
        var t = jQuery(this);
        var val = t.val();
        var slug = t.closest('tr').attr('data-menu-slug');
        var menuA = jQuery('a[href="'+slug+'"]');
        var realName = t.closest('tr').find('[data-real-name]').text();
        if(val != ''){
            menuA.find('.wp-menu-name').html(val);
        } else {
            menuA.find('.wp-menu-name').html(realName);
        }

    });
    var hiweb_core_settings_adminmenu_data = {};
    ///Change On Air
    jQuery('.hiweb-settings-adminmenu-table select').live('change', function(){
        var user = jQuery('#hiweb-settings-adminmenu-currentuser').val();
        var role = jQuery('#hiweb-settings-adminmenu-currentrole').val();

        jQuery('.hiweb-settings-adminmenu-table tr').each(function(){
            var tr = jQuery(this);
            if(tr.is('[data-menu-slug]')){
                var slug = tr.attr('data-menu-slug');
                var menuItem = jQuery('a[href="'+slug+'"]').closest('li.menu-top');
                //
                var mod = tr.find('[data-type="mode"]').val();
                var usersSet = tr.find('[data-type="users"]').val();
                var rolesSet = tr.find('[data-type="roles"]').val();
                var userMath = hiweb.in_array(user, usersSet);
                var roleMatch = hiweb.in_array(role, rolesSet);
                //
                hiweb_core_settings_adminmenu_data[slug] = {
                    name: tr.find('[data-type="rename"]').val(),
                    mode: mod,
                    users: usersSet,
                    roles: rolesSet
                };
                //
                switch(mod){
                    case 'show': menuItem.show('slow'); break;
                    case 'hide': menuItem.hide('slow'); break;
                    case 'show_role_hide_user': if(roleMatch && !userMath) menuItem.show('slow'); else menuItem.hide('slow'); break;
                    case 'show_user_hide_role': if(!roleMatch || userMath) menuItem.show('slow'); else menuItem.hide('slow'); break;
                    case 'show_only_role': if(roleMatch) menuItem.show('slow'); else menuItem.hide('slow'); break;
                    case 'show_only_user': if(userMath) menuItem.show('slow'); else menuItem.hide('slow'); break;
                    case 'hide_only_role': if(!roleMatch) menuItem.show('slow'); else menuItem.hide('slow'); break;
                    case 'hide_only_user': if(!userMath) menuItem.show('slow'); else menuItem.hide('slow'); break;
                }
            }
        });
    });
    ///Save Settings
    jQuery('#hiweb-settings-adminmenu-submit').click(function(){
        jQuery('.hiweb-settings-adminmenu-table select').eq(0).trigger('change');
        jQuery('#hiweb-settings-adminmenu-submit').addClass('button-disabled').attr('disable', 'disable');
        ////
        jQuery.ajax({
            url: '/wp-admin/admin-ajax.php?action=hiweb-settings-cms-adminmenu',
            type: 'post',
            data: {data:hiweb_core_settings_adminmenu_data},
            success: function(data){
                jQuery('#hiweb-settings-adminmenu-submit').hide();
                jQuery('#hiweb-core-settings-adminmenu-done').fadeIn();
                setTimeout(function(){
                    jQuery('#hiweb-settings-adminmenu-submit').removeClass('button-disabled').removeAttr('disable');
                    jQuery('#hiweb-settings-adminmenu-submit').fadeIn();
                    jQuery('#hiweb-core-settings-adminmenu-done').hide();
                }, 2500);
            },
            error: function(data){ console.error(data); }
        })
    });



});

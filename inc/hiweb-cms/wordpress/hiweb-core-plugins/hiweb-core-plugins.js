/**
 * Created by denmedia on 14.04.2015.
 */

var hiweb_core_plugins = {

    init: function(){
        jQuery('a[data-ajax]').live('click',hiweb_core_plugins._click);
        jQuery('input[type="submit"]').on('click', hiweb_core_plugins._submit);
        this._makeGroups();
    },


    _click: function(e){
        var a = jQuery(this);
        var tr = a.closest('tr');
        var path = tr.attr('data-path');
        tr.attr('data-process','1');
        e.preventDefault();

        jQuery.ajax({
            url: a.attr('href'),
            success: function(data){
                if(jQuery(data).find('tr[data-path="'+path+'"]').length > 0){
                    jQuery.ajax({
                        url: hiweb.getStr_UrlQuery('',[],['do','path']),
                        data: {group: jQuery('.hiweb-core-plugins-groups a[data-group-selected]').attr('href')},
                        success: function(data){
                            jQuery('.hiweb-settings-plugins')[0].outerHTML = jQuery(data).find('.hiweb-settings-plugins')[0].outerHTML;
                            if(jQuery(data).find('#adminmenu').length > 0){
                                jQuery('#adminmenu').html( jQuery(data).find('#adminmenu').html() );
                            }
                        }
                    });
                } else {
                    console.info(data);
                    tr.attr('data-process','0').find('div.second').css('color', 'red').html('<b>Ошибка выполнения операции</b>');
                }
            },
            error: function(){
                alert('Ошибка связи с сервером');
            }
        });
    },


    _submit: function(e){
        var _do = jQuery('#bulk-action-selector-top').val();
        var url = jQuery('.hiweb-settings-plugins').attr('data-url');
        if(_do == '-1') {
            alert('Выберите действие...');
            e.preventDefault();
            return false;
        }
        ///
        _checks = jQuery('.hiweb-settings-plugins tbody input[type="checkbox"]:checked');
        if(_checks.length == 0) {
            alert('Выберите как минимум один из плагинов...');
            e.preventDefault();
            return false;
        }
        ////
        var _ids = [];
        _checks.each(function(){ _ids.push(jQuery(this).val()); jQuery(this).closest('tr').attr('data-process','1'); });
        e.preventDefault();
        jQuery.ajax({
            url: url + '&do=' + _do,
            type: 'post',
            data: {path: _ids},
            success: function(data){
                jQuery.ajax({
                    url: 'admin.php?page=hiweb-plugins',
                    success: function(data){
                        jQuery('.hiweb-settings-plugins')[0].outerHTML = jQuery(data).find('.hiweb-settings-plugins')[0].outerHTML;
                        for(var n in _ids){
                            jQuery('.hiweb-settings-plugins tbody tr[data-path="'+_ids[n]+'"] input[type="checkbox"]').attr('checked','checked');
                        }
                        if(jQuery(data).find('#adminmenu').length > 0){
                            jQuery('#adminmenu').html( jQuery(data).find('#adminmenu').html() );
                        }
                    }
                });
            },
            error: function(){
                alert('Ошибка связи с сервером');
            }
        });
    },


    _makeGroups: function(){
        ///Create
        var groups = [];
        jQuery('.hiweb-settings-plugins tr').each(function(){
            if( jQuery(this).is('[data-group]') ){
                var group = jQuery(this).attr('data-group');
                if(group != '' && jQuery.inArray(group,groups) == -1) {
                    if(group.indexOf(',') > -1) { hiweb.each(group.split(','), function(k,v){ if(jQuery.inArray(v,groups) == -1) groups.push(jQuery.trim(v)); });}
                    else groups.push(group);
                }
            }
        });
        hiweb.each(groups,function(k,group){
            jQuery('[data-groups]').append('| <a href="'+group+'"> '+group+' </a> ');
        });
        ///Events
        jQuery('[data-groups] a').on('click',function(e){
            e.preventDefault();
            var href=jQuery(this).attr('href');
            jQuery('[data-groups] a').removeAttr('data-group-selected');
            jQuery(this).attr('data-group-selected','');
            if(href=='') {
                jQuery('.hiweb-settings-plugins tr[data-group]').css('display','inline-block').slideDown(500, function(){ jQuery(this).css('display','table-row') }).show().attr('data-group-hide','0');
            }
            else {
                jQuery('.hiweb-settings-plugins tr[data-group]')
                    .slideUp(500, function(){  }).attr('data-group-hide','1')
                    .closest('table').find('tr[data-group*="'+href+'"]').attr('data-group-hide','0')
                    .stop().slideDown(500, function(){ jQuery(this).removeAttr('style') });
            }
        });
        ///Fast Search
        jQuery('.hiweb-core-plugins-groups input[data-fastsearch]').on('keyup',function(e){
            var val = jQuery(this).val();
            jQuery('.hiweb-settings-plugins tr[data-group-hide="0"]').each(function(){
                var text = jQuery(this).find('td.plugin-title strong').text();
                var hide = jQuery(this).attr('data-search-hide') == 1;
                if( jQuery.trim(val) == '') {
                    jQuery(this).slideDown().attr('data-search-hide','0');
                } else if( text.toLowerCase().indexOf(val.toLowerCase()) == -1 ){
                    if(!hide) jQuery(this).slideUp().attr('data-search-hide','1');
                } else {
                    if(hide) jQuery(this).slideDown().attr('data-search-hide','0');
                }
            });
        });
    }

}
/**
 * Created by hiweb on 17.10.2015.
 */


var hiweb_input = {

    getPost_byTerms: function(postTypes, taxonomyTerms, fn){
        jQuery.ajax({
            url: 'admin-ajax.php?action=hiweb-input&do=get_posts_by_term',
            type: 'post',
            dataType: 'json',
            data: {post_type: postTypes, post_taxonomies_terms: taxonomyTerms},
            success: function(data){
                if(typeof fn == 'function') { fn(data); }
            },
            error: function(data){ console.error(data); }
        });
    }

};


jQuery(document).ready(function(){

    jQuery('.hiweb-input-field select.multipleselect').each(function(){
        var e = jQuery(this);
        var many = e.find('option').length > 10;
        e.multipleSelect({
            width: '100%',
            single: e.hasClass('onceselect'),
            multiple: e.is('[multiple="multiple"]'),
            multipleWidth: 'auto',
            filter: many,
            selectAll: many,
            placeholder: e.attr('placeholder')
        });
    });


    //Tokenize
    jQuery('.hiweb-input-field select.tokenize').each(function(){
        var e = jQuery(this);
        var many = e.find('options').length > 10;
        e.tokenize({
            autosize: true,
            displayDropdownOnFocus: true,
            placeholder: e.attr('placeholder'),
            sortable: e.is('data-sortable'),
            onAddToken: function () { e.trigger('change'); },
            onRemoveToken: function () { e.trigger('change'); },
            onReorder: function(){ e.trigger('change'); }
        });
    });


    ///Date Time Picker
    jQuery('.hiweb-input-field [data-type="datetime"], .hiweb-input-field [data-type="date"], .hiweb-input-field [data-type="time"]').each(function(){
        var e = jQuery(this);
        e.datetimepicker({
            lang:'ru',
            format: e.is('[data-type="datetime"]') ? 'Y/m/d H:i' : ( e.is('[data-type="time"]') ? 'H:i' : 'Y/m/d' ),
            datepicker: !e.is('[data-type="time"]'),
            timepicker: !e.is('[data-type="date"]'),
            mask:true
        });
    });


    ///Clock
    jQuery('.hiweb-input-field [data-type="clock"]').each(function(){
        var e = jQuery(this);
        e.clockpicker({ autoclose: true });
    });

    ///Map
    jQuery('.hiweb-input-field [data-type="map"]').each(function(){
        var e = jQuery(this);
        ymaps.ready(function(){
            var myGeoObject;
            var yMap = new ymaps.Map(e.attr('id')+'-yamap', {
                center: [55.76, 37.64],
                zoom: 11
            });
            var getInputVal = function(){
                var lat = jQuery('#'+e.attr('id')+'-lat').val();
                var long = jQuery('#'+e.attr('id')+'-long').val();
                if(hiweb.is_numeric(lat) && hiweb.is_numeric(long)) {
                    return [lat,long];
                }
                return false;
            };
            var createEvents = function(){
                ///Set Custom Coords
                yMap.events.add('click', function (ev) {
                    yMap.balloon.open(ev.get('coords'), '<h3>Установить метку тут?</h3><span class="button" data-bool="1">Да</span> <span class="button" data-bool="">Нет</span>');
                    jQuery('#'+e.attr('id')+'-yamap span.button').die().live('click',function(event){
                        if(jQuery(this).attr('data-bool')) { setPoint(ev.get('coords')); }
                        yMap.balloon.close();
                    });
                });
            };
            var createPoint = function(coords){
                myGeoObject = new ymaps.GeoObject({
                    geometry: {
                        type: "Point",
                        coordinates: [coords[0], coords[1]]
                    }
                },{draggable: true});
                myGeoObject.events.add('dragend', function(ev) { setPoint(myGeoObject.geometry.getCoordinates()); });
                yMap.geoObjects.add(myGeoObject)
            };
            var setPoint = function (coords){
                yMap.geoObjects.removeAll();
                jQuery('#'+e.attr('id')+'-lat').val(coords[0]);
                jQuery('#'+e.attr('id')+'-long').val(coords[1]);
                createPoint(coords);
            };
            var setAutoPoint = function(){
                ///Auto Coords
                hiweb.getCurrentPosition(function(pos){
                    createPoint([pos.coords.latitude, pos.coords.longitude]);
                    yMap.setCenter([pos.coords.latitude, pos.coords.longitude]);
                });
            };
            ///
            createEvents();
            var inputCoords = getInputVal();
            if(typeof inputCoords == 'object') { setPoint(inputCoords); yMap.setCenter(inputCoords); } else { setAutoPoint(); }
        });
    });


    //
    jQuery('.hiweb-input-field[data-type="taxonomies_posts"] li[data-term]').disableSelection().on('dblclick', function(e){
        var t = jQuery(this);
        var field = t.closest('.hiweb-input-field');
        var id = field.attr('data-id');
        var select = jQuery('select[id="'+id+'"]');
        var post_taxonomies_terms = {};
        post_taxonomies_terms[t.attr('data-taxonomy')] = [t.attr('data-term')];
        //
        select.tokenize().disable();
        hiweb_input.getPost_byTerms(jQuery.parseJSON(field.attr('data-post-type')),post_taxonomies_terms,function(data){
            select.tokenize().enable();
            for(var k in data){ select.tokenize().tokenAdd(k, data[k]); }
        });
    }).on('click', function(){
        var t = jQuery(this);
        var field = t.closest('.hiweb-input-field');
        var id = field.attr('data-id');
        var select = jQuery('select[id="'+id+'"]');
        var taxonomies = jQuery.parseJSON( field.find('[data-type="term-select"]').attr('data-taxonomies') );
        ///Animate Term Buttons
        if(t.is('[data-term=""]')){
            if(t.is('[data-term-select="1"]')) {
                t.attr('data-term-select','');
                field.find('[data-term]').each(function(){
                    var t = jQuery(this);
                    if(!t.is('[data-term=""]') && t.is('[data-term-select-old="1"]')) {t.attr('data-term-select','1').attr('data-term-select-old');}
                });
            }
            else {
                field.find('[data-term]').each(function(){
                    var t = jQuery(this);
                    if(!t.is('[data-term=""]') && t.is('[data-term-select="1"]')) {t.attr('data-term-select','').attr('data-term-select-old','1'); }
                });
                t.attr('data-term-select','1');
            }
        }else{
            if( field.find('[data-term=""]').is('[data-term-select="1"]') ) {
                field.find('[data-term=""]').attr('data-term-select','');
                field.find('[data-term]').each(function(){
                    var t = jQuery(this);
                    if(!t.is('[data-term=""]') && t.is('[data-term-select-old="1"]')) {t.attr('data-term-select','1').attr('data-term-select-old');}
                });
            }
            if(t.is('[data-term-select="1"]')) {t.attr('data-term-select','')}else{t.attr('data-term-select','1');}
        }
        ///Collect Terms
        var terms = {};
        field.find('[data-term-select="1"]').each(function(){
            var tax = jQuery(this).attr('data-taxonomy'); var term = jQuery(this).attr('data-term');
            if(typeof terms[tax] == 'undefined'){ terms[tax] = []; }
            terms[tax].push(term);
        });
        ///Ajax Posts
        select.tokenize().disable();
        hiweb_input.getPost_byTerms(jQuery.parseJSON(field.attr('data-post-type')), terms, function(data){
            select.find('option').remove();
            for(var k in data){ select.append( jQuery('<option/>').attr('value', k).html(data[k]) ); }
            select.tokenize().enable();
        });
    });
    //Clear Button
    jQuery('.hiweb-input-field[data-type="taxonomies_posts"] li[data-clear]').disableSelection().on('click',function(){
        var t = jQuery(this);
        var field = t.closest('.hiweb-input-field');
        var id = field.attr('data-id');
        var conf = confirm('Вы действительно хотите очистить выделенные элементы?');
        if(conf) { jQuery('select[id="'+id+'"]').tokenize().clear(); }
    });


    ///Display Rule
    jQuery('[data-hiweb-input-display-rule]').each(function(){
        var t = jQuery(this);
        var rules = jQuery.parseJSON(t.attr('data-hiweb-input-display-rule'));
        if(typeof rules[0] != 'object') return;
        for(var n in rules){
            var rule = rules[n];
            var target = jQuery('#'+rule.id);
            target.live('change', function(){
                if(target.attr('type') == 'checkbox'){ var targetVal = target.prop('checked') ? 'on' : ''; }
                else var targetVal = target.val();
                if( typeof rule.value == 'object' ){
                    var eStr = '', result = false;
                    for(var n2 in rule.value){
                        eStr = 'targetVal '+rule.operator+' "'+rule.value[n2]+'";'
                        result = eval(eStr);
                        if(result) break;
                    }
                }
                else var eStr = 'targetVal '+rule.operator+' "'+rule.value+'";';
                var result = eval(eStr);
                if(t.prop("tagName") == 'TR'){
                    if(!result) { t.fadeOut(); } else {t.fadeIn(); }
                } else {
                    if(!result) { t.slideUp(); } else {t.slideDown(); }
                }

            }).trigger('change');
        }
    });

});
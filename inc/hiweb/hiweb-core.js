/**
 * Created by d9251 on 08.07.2015.
 * @version 1.6
 */

var hiweb = {

    currentPosition: null,

    getCurrentPosition: function(sucessFn, useCache, args){
        //console.info('hiweb.getCurrentPosition -> Call get coordinates...');
        if(!hiweb.isset(useCache)) useCache = true;
        if((useCache && hiweb.currentPosition == null) || !useCache){
            //console.info('hiweb.getCurrentPosition -> Try get coordinates...');
            navigator.geolocation.getCurrentPosition(function(position) {
                //console.info('hiweb.getCurrentPosition -> navigator.geolocation.getCurrentPosition');
                hiweb.currentPosition = position;
                if(typeof sucessFn == 'function') sucessFn(position,args);
            });
        }
        else if(useCache && hiweb.currentPosition != null){
            if(typeof sucessFn == 'function') sucessFn(hiweb.currentPosition,args);
        }
    },

    getStr_UrlQuery: function(url, addData, removeKeys){
        var vars = this.getArr_UrlVars(url);
        var baseUrl = (typeof url == 'undefined' || typeof url == 'null' || url == '') ? window.location.href.split('?') : url.split('?');
        var r = [];
        if(typeof addData == 'object' || typeof addData == 'array') { for(var k in addData){ vars[k] = addData[k]; } }
        hiweb.each(vars, function(k,v){ if(jQuery.inArray(k,removeKeys) == -1) r.push(k+'='+v) });
        if(r.length > 0) { r = '?' + r.join('&'); }
        return baseUrl[0]+r;
    },

    getArr_UrlVars: function(url){
        var vars = [], hash;
        var hashes = (typeof url == 'undefined' || typeof url == 'null' || url == '') ? window.location.href.slice(window.location.href.indexOf('?') + 1).split('&') : url.slice(url.indexOf('?') + 1).split('&');
        for(var i = 0; i < hashes.length; i++){
            hash = hashes[i].split('=');
            //vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }
        return vars;
    },

    each: function(haystack, callback){
        for(var key in haystack){ if(typeof callback == 'function') { if(typeof haystack[key] != 'function') callback(key, haystack[key]); } }
    },

    is_numeric: function(haystack){
        return ( haystack == '' ) ? false : !isNaN( haystack );
    },

    getVal: function(haystack, needleMix, defValue){
        if(typeof defValue == 'undefined') { defValue = null; }
        if(typeof needleMix != 'array') { needleMix = [needleMix]; }
        var needleKey = needleMix.shift();
        if(typeof haystack[needleKey] != 'undefined') {
            if (needleMix.length > 0) return hiweb.getVal(haystack[needleKey], needleMix, defValue);
            else return haystack[needleKey];
        } else return defValue;
    },


    snd: function (in_soundFile) {
        if (typeof(in_soundFile) == 'undefined') {
            in_soundFile = 'beep.mp3';
        }
        if (jQuery('.object_js_beep').length == 0) {
            jQuery("<audio class='object_js_beep'></audio>").attr({
                'src': 'base/_snd/' + in_soundFile,
                'volume': 1
            }).appendTo("body");
        }
        jQuery('.object_js_beep')[0].play();
    },

    /**
     * Возвращает латинский транслит из киррилицы
     * @param inPathStr - строка или путь до файла
     * @returns {string}
     */
    getStr_allowSymbols: function(inPathStr){
        if(jQuery.trim(inPathStr) == '') return '';
        var len = inPathStr.length;
        returnStr = '';
        var strtr = {
            'а': 'a',
            'б': 'b',
            'в': 'v',
            'г': 'g',
            'д': 'd',
            'е': 'e',
            'ё': 'e',
            'ж': 'zh',
            'з': 'z',
            'и': 'i',
            'й': 'y',
            'к': 'k',
            'л': 'l',
            'м': 'm',
            'н': 'n',
            'о': 'o',
            'п': 'p',
            'р': 'r',
            'с': 's',
            'т': 't',
            'у': 'u',
            'ф': 'f',
            'х': 'h',
            'ц': 'c',
            'ч': 'ch',
            'ш': 'sh',
            'щ': 'sh',
            'ъ': '',
            'ы': 'yi',
            'ь': '',
            'э': 'e',
            'ю': 'yu',
            'я': 'ya',
            '0': '0',
            '1': '1',
            '2': '2',
            '3': '3',
            '4': '4',
            '5': '5',
            '6': '6',
            '7': '7',
            '8': '8',
            '9': '9',
            '-': '-',
            ' ': '_',
            '_': '_',
            ':': '_',
            ';': '_',
            'q': 'q',
            'w': 'w',
            'e': 'e',
            'r': 'r',
            't': 't',
            'y': 'y',
            'u': 'u',
            'i': 'i',
            'o': 'o',
            'p': 'p',
            'a': 'a',
            's': 's',
            'd': 'd',
            'f': 'f',
            'g': 'g',
            'h': 'h',
            'j': 'j',
            'k': 'k',
            'l': 'l',
            'z': 'z',
            'x': 'x',
            'c': 'c',
            'v': 'v',
            'b': 'b',
            'n': 'n',
            'm': 'm'
        };
        for (n = 0; n < len; n++) {
            symb = inPathStr.substr(n, 1).toLowerCase();
            if (symb == '/') {
                returnStr += '/';
            }
            else if (typeof strtr[symb] == 'string') {
                returnStr += strtr[symb];
            }
        }
        return returnStr;
    },


    isset: function (variableStr) {
        return (typeof(variableStr) != 'undefined' ? true : false );
    },

    ord: function (variableStr) {
        return variableStr.charCodeAt(0);
    },
    chr: function (symbolStr) {
        return String.fromCharCode(symbolStr);
    },


    getStr_random: function (in_len, in_alpafite, in_registr, in_numbers) {
        return_str = '';
        symb_arr = new Array;
        symb_num = 0;
        symb_only_alphavite = new Array();
        ////
        if (!hiweb.isset(in_len)) {
            in_len = 20;
        }
        if (!hiweb.isset(in_alpafite)) {
            in_alpafite = true;
        }
        if (!hiweb.isset(in_registr)) {
            in_registr = true;
        }
        if (!hiweb.isset(in_numbers)) {
            in_numbers = true;
        }
        ////Создает таблицу разрешенных символов
        if (in_alpafite) {
            for (list_n = hiweb.ord('a'); list_n <= hiweb.ord('z'); list_n++) {
                symb_only_alphavite[symb_num] = hiweb.chr(list_n);
                symb_arr[symb_num] = hiweb.chr(list_n);
                symb_num++;
            }
        }
        if (in_alpafite && in_registr) {
            for (list_n = hiweb.ord('A'); list_n <= hiweb.ord('Z'); list_n++) {
                symb_arr[symb_num] = hiweb.chr(list_n);
                symb_num++;
            }
        }
        if (in_numbers) {
            for (list_n = hiweb.ord('0'); list_n <= hiweb.ord('9'); list_n++) {
                symb_arr[symb_num] = hiweb.chr(list_n);
                symb_num++;
            }
        }
        ////Выборка из разрешенных символов случайные
        for (list_n = 0; list_n < in_len; list_n++) {
            if (in_alpafite && list_n == 0) {
                return_str += symb_arr[jQuery.randomBetween(0, symb_only_alphavite.length - 1)];
            }
            else {
                return_str += symb_arr[jQuery.randomBetween(0, symb_arr.length - 1)];
            }
        }
        /////
        return return_str;
    },

    implode: function (delimeterStr, inArray) {
        return ( ( inArray instanceof Array ) ? inArray.join(delimeterStr) : inArray );
    },

    in_array: function (needle, haystack, argStrict) {
        var key = '', strict = !!argStrict;
        if (strict) {
            for (key in haystack) {
                if (haystack[key] === needle) {
                    return true;
                }
            }
        } else {
            for (key in haystack) {
                if (haystack[key] == needle) {
                    return true;
                }
            }
        }
        return false;
    },

    array_search: function (needle, haystack) {
        for (var i in haystack) {
            if (haystack[i] == needle) return i;
        }
        return false;
    },

    array_key_exists: function (inArray, key) {
        // http://kevin.vanzonneveld.net
        // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +   improved by: Felix Geisendoerfer (http://www.debuggable.com/felix)
        // *     example 1: array_key_exists({'kevin': 'van Zonneveld'}, 'kevin');
        // *     returns 1: true
        // input sanitation
        if (!inArray || (inArray.constructor !== Array && inArray.constructor !== Object)) {
            return false;
        }
        return key in inArray;
    },


    getHeight: function(el){
        var cssHeight = el.style.height;
        cssHeight = parseFloat(cssHeight.substring(0,cssHeight.length-2));
        var realHeight = jQuery(el).innerHeight();
        return isNaN(cssHeight) || realHeight - cssHeight < 2;
    }

};


///jQuery.URLEncode, jQuery.URLDecode
jQuery.extend({
    URLEncode: function (c) {
        var o = '';
        var x = 0;
        c = c.toString();
        var r = /(^[a-zA-Z0-9_.]*)/;
        while (x < c.length) {
            var m = r.exec(c.substr(x));
            if (m != null && m.length > 1 && m[1] != '') {
                o += m[1];
                x += m[1].length;
            } else {
                if (c[x] == ' ')o += '+'; else {
                    var d = c.charCodeAt(x);
                    var h = d.toString(16);
                    o += '%' + (h.length < 2 ? '0' : '') + h.toUpperCase();
                }
                x++;
            }
        }
        return o;
    },
    URLDecode: function (s) {
        var o = s;
        var binVal, t;
        var r = /(%[^%]{2})/;
        while ((m = r.exec(o)) != null && m.length > 1 && m[1] != '') {
            b = parseInt(m[1].substr(1), 16);
            t = String.fromCharCode(b);
            o = o.replace(m[1], t);
        }
        return o;
    }
});

////jQuery.random()
jQuery.extend({
    random: function (X) {
        return Math.floor(X * (Math.random() % 1));
    },
    randomBetween: function (MinV, MaxV) {
        return MinV + jQuery.random(MaxV - MinV + 1);
    }
});

////jQuery.disableSelection()
(function (jQuery) {
    jQuery.fn.disableSelection = function () {
        return this.each(function () {
            jQuery(this).attr('unselectable', 'on')
                .css({
                    '-moz-user-select': 'none',
                    '-o-user-select': 'none',
                    '-khtml-user-select': 'none',
                    '-webkit-user-select': 'none',
                    '-ms-user-select': 'none',
                    'user-select': 'none'
                })
                .each(function () {
                    jQuery(this).attr('unselectable', 'on').attr('user-select','none').bind('selectstart', function () {
                        return false;
                    });
                });
        });
    };
    jQuery.fn.slideUpTableRow = function(){
        return this.each(function () {
            jQuery(this).slideUp({
                duration: 1500,
                step: function () {
                    var $this = jQuery(this);
                    var fontSize = parseInt($this.css("font-size"), 10);
                    while (!hiweb.getHeight(this) && fontSize > 0) {
                        $this.css("font-size", --fontSize);
                    }
                }
            });
        });
    }
})(jQuery);

////jQuery.moveElementTo(selector)
(function (jQuery) {
    jQuery.fn.moveElementTo = function (selector) {
        element = this.detach();
        jQuery(selector).append(element);
        return element;
        /*return this.each(function(){
         var cl = jQuery(this).clone();
         jQuery(cl).appendTo(selector);
         jQuery(this).remove();
         });*/
    };
})(jQuery);

var isMobile = {
    Android: function () {
        return navigator.userAgent.match(/Android/i);
    },
    BlackBerry: function () {
        return navigator.userAgent.match(/BlackBerry/i);
    },
    iOS: function () {
        return navigator.userAgent.match(/iPhone|iPad|iPod/i);
    },
    Opera: function () {
        return navigator.userAgent.match(/Opera Mini/i);
    },
    Windows: function () {
        return navigator.userAgent.match(/IEMobile/i);
    },
    any: function () {
        return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
    }
};
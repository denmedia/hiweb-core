<?php
/**
 * hiweb_tpl plugin
 *
 * @package    hiweb_tpl
 * @subpackage PluginsModifierCompiler
 */

/**
 * hiweb_tpl count_characters modifier plugin
 * Type:     modifier<br>
 * Name:     count_characteres<br>
 * Purpose:  count the number of characters in a text
 *
 * @link   http://www.smurty.net/manual/en/language.modifier.count.characters.php count_characters (hiweb_tpl online manual)
 * @author Uwe Tews
 *
 * @param array $params parameters
 *
 * @return string with compiled code
 */
function smurty_modifiercompiler_count_characters($params)
{
    if (!isset($params[1]) || $params[1] != 'true') {
        return 'preg_match_all(\'/[^\s]/' . hiweb_tpl::$_UTF8_MODIFIER . '\',' . $params[0] . ', $tmp)';
    }
    if (hiweb_tpl::$_MBSTRING) {
        return 'mb_strlen(' . $params[0] . ', \'' . addslashes(hiweb_tpl::$_CHARSET) . '\')';
    }
    // no MBString fallback
    return 'strlen(' . $params[0] . ')';
}

<?php
/**
 * hiweb_tpl plugin
 *
 * @package    hiweb_tpl
 * @subpackage PluginsModifierCompiler
 */

/**
 * hiweb_tpl lower modifier plugin
 * Type:     modifier<br>
 * Name:     lower<br>
 * Purpose:  convert string to lowercase
 *
 * @link   http://www.smurty.net/manual/en/language.modifier.lower.php lower (hiweb_tpl online manual)
 * @author Monte Ohrt <monte at ohrt dot com>
 * @author Uwe Tews
 *
 * @param array $params parameters
 *
 * @return string with compiled code
 */

function smurty_modifiercompiler_lower($params)
{
    if (hiweb_tpl::$_MBSTRING) {
        return 'mb_strtolower(' . $params[0] . ', \'' . addslashes(hiweb_tpl::$_CHARSET) . '\')';
    }
    // no MBString fallback
    return 'strtolower(' . $params[0] . ')';
}

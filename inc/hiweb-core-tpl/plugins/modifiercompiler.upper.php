<?php
/**
 * hiweb_tpl plugin
 *
 * @package    hiweb_tpl
 * @subpackage PluginsModifierCompiler
 */

/**
 * hiweb_tpl upper modifier plugin
 * Type:     modifier<br>
 * Name:     lower<br>
 * Purpose:  convert string to uppercase
 *
 * @link   http://smurty.php.net/manual/en/language.modifier.upper.php lower (hiweb_tpl online manual)
 * @author Uwe Tews
 *
 * @param array $params parameters
 *
 * @return string with compiled code
 */
function smurty_modifiercompiler_upper($params)
{
    if (hiweb_tpl::$_MBSTRING) {
        return 'mb_strtoupper(' . $params[0] . ', \'' . addslashes(hiweb_tpl::$_CHARSET) . '\')';
    }
    // no MBString fallback
    return 'strtoupper(' . $params[0] . ')';
}

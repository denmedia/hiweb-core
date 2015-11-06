<?php
/**
 * hiweb_tpl plugin
 *
 * @package    hiweb_tpl
 * @subpackage PluginsModifierCompiler
 */

/**
 * hiweb_tpl string_format modifier plugin
 * Type:     modifier<br>
 * Name:     string_format<br>
 * Purpose:  format strings via sprintf
 *
 * @link   http://www.smurty.net/manual/en/language.modifier.string.format.php string_format (hiweb_tpl online manual)
 * @author Uwe Tews
 *
 * @param array $params parameters
 *
 * @return string with compiled code
 */
function smurty_modifiercompiler_string_format($params)
{
    return 'sprintf(' . $params[1] . ',' . $params[0] . ')';
}

<?php
/**
 * hiweb_tpl plugin
 *
 * @package    hiweb_tpl
 * @subpackage PluginsModifierCompiler
 */

/**
 * hiweb_tpl cat modifier plugin
 * Type:     modifier<br>
 * Name:     cat<br>
 * Date:     Feb 24, 2003<br>
 * Purpose:  catenate a value to a variable<br>
 * Input:    string to catenate<br>
 * Example:  {$var|cat:"foo"}
 *
 * @link     http://smurty.php.net/manual/en/language.modifier.cat.php cat
 *           (hiweb_tpl online manual)
 * @author   Uwe Tews
 *
 * @param array $params parameters
 *
 * @return string with compiled code
 */
function smurty_modifiercompiler_cat($params)
{
    return '(' . implode(').(', $params) . ')';
}

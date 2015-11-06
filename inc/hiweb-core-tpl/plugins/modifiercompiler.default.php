<?php
/**
 * hiweb_tpl plugin
 *
 * @package    hiweb_tpl
 * @subpackage PluginsModifierCompiler
 */

/**
 * hiweb_tpl default modifier plugin
 * Type:     modifier<br>
 * Name:     default<br>
 * Purpose:  designate default value for empty variables
 *
 * @link   http://www.smurty.net/manual/en/language.modifier.default.php default (hiweb_tpl online manual)
 * @author Uwe Tews
 *
 * @param array $params parameters
 *
 * @return string with compiled code
 */
function smurty_modifiercompiler_default($params)
{
    $output = $params[0];
    if (!isset($params[1])) {
        $params[1] = "''";
    }

    array_shift($params);
    foreach ($params as $param) {
        $output = '(($tmp = @' . $output . ')===null||$tmp===\'\' ? ' . $param . ' : $tmp)';
    }

    return $output;
}

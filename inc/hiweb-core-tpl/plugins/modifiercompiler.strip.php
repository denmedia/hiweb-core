<?php
/**
 * hiweb_tpl plugin
 *
 * @package    hiweb_tpl
 * @subpackage PluginsModifierCompiler
 */

/**
 * hiweb_tpl strip modifier plugin
 * Type:     modifier<br>
 * Name:     strip<br>
 * Purpose:  Replace all repeated spaces, newlines, tabs
 *              with a single space or supplied replacement string.<br>
 * Example:  {$var|strip} {$var|strip:"&nbsp;"}<br>
 * Date:     September 25th, 2002
 *
 * @link   http://www.smurty.net/manual/en/language.modifier.strip.php strip (hiweb_tpl online manual)
 * @author Uwe Tews
 *
 * @param array $params parameters
 *
 * @return string with compiled code
 */

function smurty_modifiercompiler_strip($params)
{
    if (!isset($params[1])) {
        $params[1] = "' '";
    }

    return "preg_replace('!\s+!" . hiweb_tpl::$_UTF8_MODIFIER . "', {$params[1]},{$params[0]})";
}

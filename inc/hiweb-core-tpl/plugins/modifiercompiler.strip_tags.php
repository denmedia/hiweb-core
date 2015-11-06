<?php
/**
 * hiweb_tpl plugin
 *
 * @package    hiweb_tpl
 * @subpackage PluginsModifierCompiler
 */

/**
 * hiweb_tpl strip_tags modifier plugin
 * Type:     modifier<br>
 * Name:     strip_tags<br>
 * Purpose:  strip html tags from text
 *
 * @link   http://www.smurty.net/manual/en/language.modifier.strip.tags.php strip_tags (hiweb_tpl online manual)
 * @author Uwe Tews
 *
 * @param array $params parameters
 *
 * @return string with compiled code
 */
function smurty_modifiercompiler_strip_tags($params)
{
    if (!isset($params[1]) || $params[1] === true || trim($params[1], '"') == 'true') {
        return "preg_replace('!<[^>]*?>!', ' ', {$params[0]})";
    } else {
        return 'strip_tags(' . $params[0] . ')';
    }
}

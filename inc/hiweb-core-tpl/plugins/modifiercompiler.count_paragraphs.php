<?php
/**
 * hiweb_tpl plugin
 *
 * @package    hiweb_tpl
 * @subpackage PluginsModifierCompiler
 */

/**
 * hiweb_tpl count_paragraphs modifier plugin
 * Type:     modifier<br>
 * Name:     count_paragraphs<br>
 * Purpose:  count the number of paragraphs in a text
 *
 * @link    http://www.smurty.net/manual/en/language.modifier.count.paragraphs.php
 *          count_paragraphs (hiweb_tpl online manual)
 * @author  Uwe Tews
 *
 * @param array $params parameters
 *
 * @return string with compiled code
 */
function smurty_modifiercompiler_count_paragraphs($params)
{
    // count \r or \n characters
    return '(preg_match_all(\'#[\r\n]+#\', ' . $params[0] . ', $tmp)+1)';
}

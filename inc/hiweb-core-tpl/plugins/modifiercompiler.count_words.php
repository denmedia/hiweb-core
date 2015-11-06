<?php
/**
 * hiweb_tpl plugin
 *
 * @package    hiweb_tpl
 * @subpackage PluginsModifierCompiler
 */

/**
 * hiweb_tpl count_words modifier plugin
 * Type:     modifier<br>
 * Name:     count_words<br>
 * Purpose:  count the number of words in a text
 *
 * @link   http://www.smurty.net/manual/en/language.modifier.count.words.php count_words (hiweb_tpl online manual)
 * @author Uwe Tews
 *
 * @param array $params parameters
 *
 * @return string with compiled code
 */
function smurty_modifiercompiler_count_words($params)
{
    if (hiweb_tpl::$_MBSTRING) {
        // return 'preg_match_all(\'#[\w\pL]+#' . hiweb_tpl::$_UTF8_MODIFIER . '\', ' . $params[0] . ', $tmp)';
        // expression taken from http://de.php.net/manual/en/function.str-word-count.php#85592
        return 'preg_match_all(\'/\p{L}[\p{L}\p{Mn}\p{Pd}\\\'\x{2019}]*/' . hiweb_tpl::$_UTF8_MODIFIER . '\', ' . $params[0] . ', $tmp)';
    }
    // no MBString fallback
    return 'str_word_count(' . $params[0] . ')';
}

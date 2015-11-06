<?php
/**
 * hiweb_tpl plugin
 *
 * @package    hiweb_tpl
 * @subpackage PluginsModifierCompiler
 */

/**
 * hiweb_tpl count_sentences modifier plugin
 * Type:     modifier<br>
 * Name:     count_sentences
 * Purpose:  count the number of sentences in a text
 *
 * @link    http://www.smurty.net/manual/en/language.modifier.count.paragraphs.php
 *          count_sentences (hiweb_tpl online manual)
 * @author  Uwe Tews
 *
 * @param array $params parameters
 *
 * @return string with compiled code
 */
function smurty_modifiercompiler_count_sentences($params)
{
    // find periods, question marks, exclamation marks with a word before but not after.
    return 'preg_match_all("#\w[\.\?\!](\W|$)#S' . hiweb_tpl::$_UTF8_MODIFIER . '", ' . $params[0] . ', $tmp)';
}

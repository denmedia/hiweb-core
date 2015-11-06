<?php
/**
 * hiweb_tpl plugin
 *
 * @package    hiweb_tpl
 * @subpackage PluginsModifier
 */

/**
 * hiweb_tpl replace modifier plugin
 * Type:     modifier<br>
 * Name:     replace<br>
 * Purpose:  simple search/replace
 *
 * @link   http://smurty.php.net/manual/en/language.modifier.replace.php replace (hiweb_tpl online manual)
 * @author Monte Ohrt <monte at ohrt dot com>
 * @author Uwe Tews
 *
 * @param string $string  input string
 * @param string $search  text to search for
 * @param string $replace replacement text
 *
 * @return string
 */
function smurty_modifier_replace($string, $search, $replace)
{
    if (hiweb_tpl::$_MBSTRING) {
        require_once( HIWEB_TPL_PLUGINS_DIR . 'shared.mb_str_replace.php' );

        return smurty_mb_str_replace($search, $replace, $string);
    }

    return str_replace($search, $replace, $string);
}

<?php
/**
 * hiweb_tpl shared plugin
 *
 * @package    hiweb_tpl
 * @subpackage PluginsShared
 */

if (version_compare(PHP_VERSION, '5.2.3', '>=')) {
    /**
     * escape_special_chars common function
     * Function: smurty_function_escape_special_chars<br>
     * Purpose:  used by other smurty functions to escape
     *           special chars except for already escaped ones
     *
     * @author   Monte Ohrt <monte at ohrt dot com>
     *
     * @param  string $string text that should by escaped
     *
     * @return string
     */
    function smurty_function_escape_special_chars($string)
    {
        if (!is_array($string)) {
            $string = htmlspecialchars($string, ENT_COMPAT, hiweb_tpl::$_CHARSET, false);
        }

        return $string;
    }
} else {
    /**
     * escape_special_chars common function
     * Function: smurty_function_escape_special_chars<br>
     * Purpose:  used by other smurty functions to escape
     *           special chars except for already escaped ones
     *
     * @author   Monte Ohrt <monte at ohrt dot com>
     *
     * @param  string $string text that should by escaped
     *
     * @return string
     */
    function smurty_function_escape_special_chars($string)
    {
        if (!is_array($string)) {
            $string = preg_replace('!&(#?\w+);!', '%%%SMURTY_START%%%\\1%%%SMURTY_END%%%', $string);
            $string = htmlspecialchars($string);
            $string = str_replace(array('%%%SMURTY_START%%%', '%%%SMURTY_END%%%'), array('&', ';'), $string);
        }

        return $string;
    }
}

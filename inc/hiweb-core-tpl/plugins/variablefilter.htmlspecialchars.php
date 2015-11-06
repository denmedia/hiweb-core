<?php
/**
 * hiweb_tpl plugin
 *
 * @package    hiweb_tpl
 * @subpackage PluginsFilter
 */

/**
 * hiweb_tpl htmlspecialchars variablefilter plugin
 *
 * @param string $source input string
 *
 * @return string filtered output
 */
function smurty_variablefilter_htmlspecialchars($source)
{
    return htmlspecialchars($source, ENT_QUOTES, hiweb_tpl::$_CHARSET);
}

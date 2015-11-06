<?php
/**
 * hiweb_tpl plugin
 *
 * @package    hiweb_tpl
 * @subpackage PluginsShared
 */

/**
 * evaluate compiler parameter
 *
 * @param array   $params  parameter array as given to the compiler function
 * @param integer $index   array index of the parameter to convert
 * @param mixed   $default value to be returned if the parameter is not present
 *
 * @return mixed evaluated value of parameter or $default
 * @throws SmurtyException if parameter is not a literal (but an expression, variable, …)
 * @author Rodney Rehm
 */
function smurty_literal_compiler_param($params, $index, $default = null)
{
    // not set, go default
    if (!isset($params[$index])) {
        return $default;
    }
    // test if param is a literal
    if (!preg_match('/^([\'"]?)[a-zA-Z0-9-]+(\\1)$/', $params[$index])) {
        throw new SmurtyException('$param[' . $index . '] is not a literal and is thus not evaluatable at compile time');
    }

    $t = null;
    eval("\$t = " . $params[$index] . ";");

    return $t;
}

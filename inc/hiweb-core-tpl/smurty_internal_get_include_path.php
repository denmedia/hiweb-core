<?php
/**
 * hiweb_tpl read include path plugin
 *
 * @package    hiweb_tpl
 * @subpackage PluginsInternal
 * @author     Monte Ohrt
 */

/**
 * hiweb_tpl Internal Read Include Path Class
 *
 * @package    hiweb_tpl
 * @subpackage PluginsInternal
 */
class Smurty_Internal_Get_Include_Path
{
    /**
     * Return full file path from PHP include_path
     *
     * @param  string $filepath filepath
     *
     * @return string|boolean full filepath or false
     */
    public static function getIncludePath($filepath)
    {
        static $_include_path = null;

        if (function_exists('stream_resolve_include_path')) {
            // available since PHP 5.3.2
            return stream_resolve_include_path($filepath);
        }

        if ($_include_path === null) {
            $_include_path = explode(PATH_SEPARATOR, get_include_path());
        }

        foreach ($_include_path as $_path) {
            if (file_exists($_path . DIR_SEPARATOR . $filepath)) {
                return $_path . DIR_SEPARATOR . $filepath;
            }
        }

        return false;
    }
}

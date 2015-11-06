<?php
/**
 * hiweb_tpl Internal Plugin Filter Handler
 * hiweb_tpl filter handler class
 *
 * @package    hiweb_tpl
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */

/**
 * Class for filter processing
 *
 * @package    hiweb_tpl
 * @subpackage PluginsInternal
 */
class Smurty_Internal_Filter_Handler
{
    /**
     * Run filters over content
     * The filters will be lazy loaded if required
     * class name format: Smurty_FilterType_FilterName
     * plugin filename format: filtertype.filtername.php
     * Smurty2 filter plugins could be used
     *
     * @param  string                   $type     the type of filter ('pre','post','output') which shall run
     * @param  string                   $content  the content which shall be processed by the filters
     * @param  Smurty_Internal_Template $template template object
     *
     * @throws SmurtyException
     * @return string                   the filtered content
     */
    public static function runFilter($type, $content, Smurty_Internal_Template $template)
    {
        $output = $content;
        // loop over autoload filters of specified type
        if (!empty($template->smurty->autoload_filters[$type])) {
            foreach ((array) $template->smurty->autoload_filters[$type] as $name) {
                $plugin_name = "Smurty_{$type}filter_{$name}";
                if ($template->smurty->loadPlugin($plugin_name)) {
                    if (function_exists($plugin_name)) {
                        // use loaded Smurty2 style plugin
                        $output = $plugin_name($output, $template);
                    } elseif (class_exists($plugin_name, false)) {
                        // loaded class of filter plugin
                        $output = call_user_func(array($plugin_name, 'execute'), $output, $template);
                    }
                } else {
                    // nothing found, throw exception
                    throw new SmurtyException("Unable to load filter {$plugin_name}");
                }
            }
        }
        // loop over registerd filters of specified type
        if (!empty($template->smurty->registered_filters[$type])) {
            foreach ($template->smurty->registered_filters[$type] as $key => $name) {
                if (is_array($template->smurty->registered_filters[$type][$key])) {
                    $output = call_user_func($template->smurty->registered_filters[$type][$key], $output, $template);
                } else {
                    $output = $template->smurty->registered_filters[$type][$key]($output, $template);
                }
            }
        }
        // return filtered output
        return $output;
    }
}

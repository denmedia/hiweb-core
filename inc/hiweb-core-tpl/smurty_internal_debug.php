<?php
/**
 * hiweb_tpl Internal Plugin Debug
 * Class to collect data for the hiweb_tpl Debugging Consol
 *
 * @package    hiweb_tpl
 * @subpackage Debug
 * @author     Uwe Tews
 */

/**
 * hiweb_tpl Internal Plugin Debug Class
 *
 * @package    hiweb_tpl
 * @subpackage Debug
 */
class Smurty_Internal_Debug extends Smurty_Internal_Data
{
    /**
     * template data
     *
     * @var array
     */
    public static $template_data = array();

    /**
     * List of uid's which shall be ignored
     *
     * @var array
     */
    public static $ignore_uid = array();

    /**
     * Ignore template
     *
     * @param object $template
     */
    public static function ignore($template)
    {
        // calculate Uid if not already done
        if ($template->source->uid == '') {
            $template->source->filepath;
        }
        self::$ignore_uid[$template->source->uid] = true;
    }

    /**
     * Start logging of compile time
     *
     * @param object $template
     */
    public static function start_compile($template)
    {
        static $_is_stringy = array('string' => true, 'eval' => true);
        if (!empty($template->compiler->trace_uid)) {
            $key = $template->compiler->trace_uid;
            if (!isset(self::$template_data[$key])) {
                if (isset($_is_stringy[$template->source->type])) {
                    self::$template_data[$key]['name'] = '\'' . substr($template->source->name, 0, 25) . '...\'';
                } else {
                    self::$template_data[$key]['name'] = $template->source->filepath;
                }
                self::$template_data[$key]['compile_time'] = 0;
                self::$template_data[$key]['render_time'] = 0;
                self::$template_data[$key]['cache_time'] = 0;
            }
        } else {
            if (isset(self::$ignore_uid[$template->source->uid])) {
                return;
            }
            $key = self::get_key($template);
        }
        self::$template_data[$key]['start_time'] = microtime(true);
    }

    /**
     * End logging of compile time
     *
     * @param object $template
     */
    public static function end_compile($template)
    {
        if (!empty($template->compiler->trace_uid)) {
            $key = $template->compiler->trace_uid;
        } else {
            if (isset(self::$ignore_uid[$template->source->uid])) {
                return;
            }

            $key = self::get_key($template);
        }
        self::$template_data[$key]['compile_time'] += microtime(true) - self::$template_data[$key]['start_time'];
    }

    /**
     * Start logging of render time
     *
     * @param object $template
     */
    public static function start_render($template)
    {
        $key = self::get_key($template);
        self::$template_data[$key]['start_time'] = microtime(true);
    }

    /**
     * End logging of compile time
     *
     * @param object $template
     */
    public static function end_render($template)
    {
        $key = self::get_key($template);
        self::$template_data[$key]['render_time'] += microtime(true) - self::$template_data[$key]['start_time'];
    }

    /**
     * Start logging of cache time
     *
     * @param object $template cached template
     */
    public static function start_cache($template)
    {
        $key = self::get_key($template);
        self::$template_data[$key]['start_time'] = microtime(true);
    }

    /**
     * End logging of cache time
     *
     * @param object $template cached template
     */
    public static function end_cache($template)
    {
        $key = self::get_key($template);
        self::$template_data[$key]['cache_time'] += microtime(true) - self::$template_data[$key]['start_time'];
    }

    /**
     * Opens a window for the hiweb_tpl Debugging Consol and display the data
     *
     * @param Smurty_Internal_Template|hiweb_tpl $obj object to debug
     */
    public static function display_debug($obj)
    {
        // prepare information of assigned variables
        $ptr = self::get_debug_vars($obj);
        if ($obj instanceof hiweb_tpl) {
            $smurty = clone $obj;
        } else {
            $smurty = clone $obj->smurty;
        }
        $_assigned_vars = $ptr->tpl_vars;
        ksort($_assigned_vars);
        $_config_vars = $ptr->config_vars;
        ksort($_config_vars);
        $smurty->registered_filters = array();
        $smurty->autoload_filters = array();
        $smurty->default_modifiers = array();
        $smurty->force_compile = false;
        $smurty->left_delimiter = '{';
        $smurty->right_delimiter = '}';
        $smurty->debugging = false;
        $smurty->debugging_ctrl = 'NONE';
        $smurty->force_compile = false;
        $_template = new Smurty_Internal_Template($smurty->debug_tpl, $smurty);
        $_template->caching = false;
        $_template->disableSecurity();
        $_template->cache_id = null;
        $_template->compile_id = null;
        if ($obj instanceof Smurty_Internal_Template) {
            $_template->assign('template_name', $obj->source->type . ':' . $obj->source->name);
        }
        if ($obj instanceof hiweb_tpl) {
            $_template->assign('template_data', self::$template_data);
        } else {
            $_template->assign('template_data', null);
        }
        $_template->assign('assigned_vars', $_assigned_vars);
        $_template->assign('config_vars', $_config_vars);
        $_template->assign('execution_time', microtime(true) - $smurty->start_time);
        echo $_template->fetch();
    }

    /**
     * Recursively gets variables from all template/data scopes
     *
     * @param  Smurty_Internal_Template|Smurty_Data $obj object to debug
     *
     * @return StdClass
     */
    public static function get_debug_vars($obj)
    {
        $config_vars = $obj->config_vars;
        $tpl_vars = array();
        foreach ($obj->tpl_vars as $key => $var) {
            $tpl_vars[$key] = clone $var;
            if ($obj instanceof Smurty_Internal_Template) {
                $tpl_vars[$key]->scope = $obj->source->type . ':' . $obj->source->name;
            } elseif ($obj instanceof Smurty_Data) {
                $tpl_vars[$key]->scope = 'Data object';
            } else {
                $tpl_vars[$key]->scope = 'hiweb_tpl root';
            }
        }

        if (isset($obj->parent)) {
            $parent = self::get_debug_vars($obj->parent);
            $tpl_vars = array_merge($parent->tpl_vars, $tpl_vars);
            $config_vars = array_merge($parent->config_vars, $config_vars);
        } else {
            foreach (hiweb_tpl::$global_tpl_vars as $name => $var) {
                if (!array_key_exists($name, $tpl_vars)) {
                    $clone = clone $var;
                    $clone->scope = 'Global';
                    $tpl_vars[$name] = $clone;
                }
            }
        }

        return (object) array('tpl_vars' => $tpl_vars, 'config_vars' => $config_vars);
    }

    /**
     * Return key into $template_data for template
     *
     * @param  object $template template object
     *
     * @return string key into $template_data
     */
    private static function get_key($template)
    {
        static $_is_stringy = array('string' => true, 'eval' => true);
        // calculate Uid if not already done
        if ($template->source->uid == '') {
            $template->source->filepath;
        }
        $key = $template->source->uid;
        if (isset(self::$template_data[$key])) {
            return $key;
        } else {
            if (isset($_is_stringy[$template->source->type])) {
                self::$template_data[$key]['name'] = '\'' . substr($template->source->name, 0, 25) . '...\'';
            } else {
                self::$template_data[$key]['name'] = $template->source->filepath;
            }
            self::$template_data[$key]['compile_time'] = 0;
            self::$template_data[$key]['render_time'] = 0;
            self::$template_data[$key]['cache_time'] = 0;

            return $key;
        }
    }
}

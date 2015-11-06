<?php
/**
 * hiweb_tpl Internal Plugin Compile Print Expression
 * Compiles any tag which will output an expression or variable
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 * @author     Uwe Tews
 */

/**
 * hiweb_tpl Internal Plugin Compile Print Expression Class
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 */
class Smurty_Internal_Compile_Private_Print_Expression extends Smurty_Internal_CompileBase
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smurty_Internal_CompileBase
     */
    public $optional_attributes = array('assign');
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smurty_Internal_CompileBase
     */
    public $option_flags = array('nocache', 'nofilter');

    /**
     * Compiles code for generating output from any expression
     *
     * @param array  $args      array with attributes from parser
     * @param object $compiler  compiler object
     * @param array  $parameter array with compilation parameter
     *
     * @throws SmurtyException
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);
        // nocache option
        if ($_attr['nocache'] === true) {
            $compiler->tag_nocache = true;
        }
        if (isset($_attr['assign'])) {
            // assign output to variable
            $output = "<?php \$_smurty_tpl->assign({$_attr['assign']},{$parameter['value']});?>";
        } else {
            // display value
            $output = $parameter['value'];
            // tag modifier
            if (!empty($parameter['modifierlist'])) {
                $output = $compiler->compileTag('private_modifier', array(), array('modifierlist' => $parameter['modifierlist'], 'value' => $output));
            }
            if (!$_attr['nofilter']) {
                // default modifier
                if (!empty($compiler->smurty->default_modifiers)) {
                    if (empty($compiler->default_modifier_list)) {
                        $modifierlist = array();
                        foreach ($compiler->smurty->default_modifiers as $key => $single_default_modifier) {
                            preg_match_all('/(\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"|:|[^:]+)/', $single_default_modifier, $mod_array);
                            for ($i = 0, $count = count($mod_array[0]); $i < $count; $i ++) {
                                if ($mod_array[0][$i] != ':') {
                                    $modifierlist[$key][] = $mod_array[0][$i];
                                }
                            }
                        }
                        $compiler->default_modifier_list = $modifierlist;
                    }
                    $output = $compiler->compileTag('private_modifier', array(), array('modifierlist' => $compiler->default_modifier_list, 'value' => $output));
                }
                // autoescape html
                if ($compiler->template->smurty->escape_html) {
                    $output = "htmlspecialchars({$output}, ENT_QUOTES, '" . addslashes(hiweb_tpl::$_CHARSET) . "')";
                }
                // loop over registered filters
                if (!empty($compiler->template->smurty->registered_filters[hiweb_tpl::FILTER_VARIABLE])) {
                    foreach ($compiler->template->smurty->registered_filters[hiweb_tpl::FILTER_VARIABLE] as $key => $function) {
                        if (!is_array($function)) {
                            $output = "{$function}({$output},\$_smurty_tpl)";
                        } elseif (is_object($function[0])) {
                            $output = "\$_smurty_tpl->smurty->registered_filters[hiweb_tpl::FILTER_VARIABLE]['{$key}'][0]->{$function[1]}({$output},\$_smurty_tpl)";
                        } else {
                            $output = "{$function[0]}::{$function[1]}({$output},\$_smurty_tpl)";
                        }
                    }
                }
                // auto loaded filters
                if (isset($compiler->smurty->autoload_filters[hiweb_tpl::FILTER_VARIABLE])) {
                    foreach ((array) $compiler->template->smurty->autoload_filters[hiweb_tpl::FILTER_VARIABLE] as $name) {
                        $result = $this->compile_output_filter($compiler, $name, $output);
                        if ($result !== false) {
                            $output = $result;
                        } else {
                            // not found, throw exception
                            throw new SmurtyException("Unable to load filter '{$name}'");
                        }
                    }
                }
                if (isset($compiler->template->variable_filters)) {
                    foreach ($compiler->template->variable_filters as $filter) {
                        if (count($filter) == 1 && ($result = $this->compile_output_filter($compiler, $filter[0], $output)) !== false) {
                            $output = $result;
                        } else {
                            $output = $compiler->compileTag('private_modifier', array(), array('modifierlist' => array($filter), 'value' => $output));
                        }
                    }
                }
            }

            $compiler->has_output = true;
            $output = "<?php echo {$output};?>";
        }

        return $output;
    }

    /**
     * @param object $compiler compiler object
     * @param string $name     name of variable filter
     * @param string   $output   embedded output
     *
     * @return string
     */
    private function compile_output_filter($compiler, $name, $output)
    {
        $plugin_name = "smurty_variablefilter_{$name}";
        $path = $compiler->smurty->loadPlugin($plugin_name, false);
        if ($path) {
            if ($compiler->template->caching) {
                $compiler->template->required_plugins['nocache'][$name][hiweb_tpl::FILTER_VARIABLE]['file'] = $path;
                $compiler->template->required_plugins['nocache'][$name][hiweb_tpl::FILTER_VARIABLE]['function'] = $plugin_name;
            } else {
                $compiler->template->required_plugins['compiled'][$name][hiweb_tpl::FILTER_VARIABLE]['file'] = $path;
                $compiler->template->required_plugins['compiled'][$name][hiweb_tpl::FILTER_VARIABLE]['function'] = $plugin_name;
            }
        } else {
            // not found
            return false;
        }

        return "{$plugin_name}({$output},\$_smurty_tpl)";
    }
}

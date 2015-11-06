<?php
/**
 * hiweb_tpl Internal Plugin Compile Assign
 * Compiles the {assign} tag
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 * @author     Uwe Tews
 */

/**
 * hiweb_tpl Internal Plugin Compile Assign Class
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 */
class Smurty_Internal_Compile_Assign extends Smurty_Internal_CompileBase
{
    /**
     * Compiles code for the {assign} tag
     *
     * @param  array  $args      array with attributes from parser
     * @param  object $compiler  compiler object
     * @param  array  $parameter array with compilation parameter
     *
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter)
    {
        // the following must be assigned at runtime because it will be overwritten in Smurty_Internal_Compile_Append
        $this->required_attributes = array('var', 'value');
        $this->shorttag_order = array('var', 'value');
        $this->optional_attributes = array('scope');
        $_nocache = 'null';
        $_scope = hiweb_tpl::SCOPE_LOCAL;
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);
        // nocache ?
        if ($compiler->tag_nocache || $compiler->nocache) {
            $_nocache = 'true';
            // create nocache var to make it know for further compiling
            if (isset($compiler->template->tpl_vars[trim($_attr['var'], "'")])) {
                $compiler->template->tpl_vars[trim($_attr['var'], "'")]->nocache = true;
            } else {
                $compiler->template->tpl_vars[trim($_attr['var'], "'")] = new Smurty_variable(null, true);
            }
        }
        // scope setup
        if (isset($_attr['scope'])) {
            $_attr['scope'] = trim($_attr['scope'], "'\"");
            if ($_attr['scope'] == 'parent') {
                $_scope = hiweb_tpl::SCOPE_PARENT;
            } elseif ($_attr['scope'] == 'root') {
                $_scope = hiweb_tpl::SCOPE_ROOT;
            } elseif ($_attr['scope'] == 'global') {
                $_scope = hiweb_tpl::SCOPE_GLOBAL;
            } else {
                $compiler->trigger_template_error('illegal value for "scope" attribute', $compiler->lex->taglineno);
            }
        }
        // compiled output
        if (isset($parameter['smurty_internal_index'])) {
            $output = "<?php \$_smurty_tpl->createLocalArrayVariable($_attr[var], $_nocache, $_scope);\n\$_smurty_tpl->tpl_vars[$_attr[var]]->value$parameter[smurty_internal_index] = $_attr[value];";
        } else {
            // implement Smurty2's behaviour of variables assigned by reference
            if ($compiler->template->smurty instanceof hiweb_tplChild) {
                $output = "<?php if (isset(\$_smurty_tpl->tpl_vars[$_attr[var]])) {\$_smurty_tpl->tpl_vars[$_attr[var]] = clone \$_smurty_tpl->tpl_vars[$_attr[var]];";
                $output .= "\n\$_smurty_tpl->tpl_vars[$_attr[var]]->value = $_attr[value]; \$_smurty_tpl->tpl_vars[$_attr[var]]->nocache = $_nocache; \$_smurty_tpl->tpl_vars[$_attr[var]]->scope = $_scope;";
                $output .= "\n} else \$_smurty_tpl->tpl_vars[$_attr[var]] = new Smurty_variable($_attr[value], $_nocache, $_scope);";
            } else {
                $output = "<?php \$_smurty_tpl->tpl_vars[$_attr[var]] = new Smurty_variable($_attr[value], $_nocache, $_scope);";
            }
        }
        if ($_scope == hiweb_tpl::SCOPE_PARENT) {
            $output .= "\nif (\$_smurty_tpl->parent != null) \$_smurty_tpl->parent->tpl_vars[$_attr[var]] = clone \$_smurty_tpl->tpl_vars[$_attr[var]];";
        } elseif ($_scope == hiweb_tpl::SCOPE_ROOT || $_scope == hiweb_tpl::SCOPE_GLOBAL) {
            $output .= "\n\$_ptr = \$_smurty_tpl->parent; while (\$_ptr != null) {\$_ptr->tpl_vars[$_attr[var]] = clone \$_smurty_tpl->tpl_vars[$_attr[var]]; \$_ptr = \$_ptr->parent; }";
        }
        if ($_scope == hiweb_tpl::SCOPE_GLOBAL) {
            $output .= "\nSmurty::\$global_tpl_vars[$_attr[var]] = clone \$_smurty_tpl->tpl_vars[$_attr[var]];";
        }
        $output .= '?>';

        return $output;
    }
}

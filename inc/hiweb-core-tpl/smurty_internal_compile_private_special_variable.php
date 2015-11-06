<?php
/**
 * hiweb_tpl Internal Plugin Compile Special hiweb_tpl Variable
 * Compiles the special $smurty variables
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 * @author     Uwe Tews
 */

/**
 * hiweb_tpl Internal Plugin Compile special hiweb_tpl Variable Class
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 */
class Smurty_Internal_Compile_Private_Special_Variable extends Smurty_Internal_CompileBase
{
    /**
     * Compiles code for the special $smurty variables
     *
     * @param  array  $args     array with attributes from parser
     * @param  object $compiler compiler object
     * @param         $parameter
     *
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter)
    {
        $_index = preg_split("/\]\[/", substr($parameter, 1, strlen($parameter) - 2));
        $compiled_ref = ' ';
        $variable = trim($_index[0], "'");
        switch ($variable) {
            case 'foreach':
                return "\$_smurty_tpl->getVariable('smurty')->value$parameter";
            case 'section':
                return "\$_smurty_tpl->getVariable('smurty')->value$parameter";
            case 'capture':
                return "hiweb_tpl::\$_smurty_vars$parameter";
            case 'now':
                return 'time()';
            case 'cookies':
                if (isset($compiler->smurty->security_policy) && !$compiler->smurty->security_policy->allow_super_globals) {
                    $compiler->trigger_template_error("(secure mode) super globals not permitted");
                    break;
                }
                $compiled_ref = '$_COOKIE';
                break;

            case 'get':
            case 'post':
            case 'env':
            case 'server':
            case 'session':
            case 'request':
                if (isset($compiler->smurty->security_policy) && !$compiler->smurty->security_policy->allow_super_globals) {
                    $compiler->trigger_template_error("(secure mode) super globals not permitted");
                    break;
                }
                $compiled_ref = '$_' . strtoupper($variable);
                break;

            case 'template':
                return 'basename($_smurty_tpl->source->filepath)';

            case 'template_object':
                return '$_smurty_tpl';

            case 'current_dir':
                return 'dirname($_smurty_tpl->source->filepath)';

            case 'version':
                $_version = hiweb_tpl::HIWEB_TPL_VERSION;

                return "'$_version'";

            case 'const':
                if (isset($compiler->smurty->security_policy) && !$compiler->smurty->security_policy->allow_constants) {
                    $compiler->trigger_template_error("(secure mode) constants not permitted");
                    break;
                }

                return "@constant({$_index[1]})";

            case 'config':
                if (isset($_index[2])) {
                    return "(is_array(\$tmp = \$_smurty_tpl->getConfigVariable($_index[1])) ? \$tmp[$_index[2]] : null)";
                } else {
                    return "\$_smurty_tpl->getConfigVariable($_index[1])";
                }
            case 'ldelim':
                $_ldelim = $compiler->smurty->left_delimiter;

                return "'$_ldelim'";

            case 'rdelim':
                $_rdelim = $compiler->smurty->right_delimiter;

                return "'$_rdelim'";

            default:
                $compiler->trigger_template_error('$smurty.' . trim($_index[0], "'") . ' is invalid');
                break;
        }
        if (isset($_index[1])) {
            array_shift($_index);
            foreach ($_index as $_ind) {
                $compiled_ref = $compiled_ref . "[$_ind]";
            }
        }

        return $compiled_ref;
    }
}

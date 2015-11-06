<?php
/**
 * hiweb_tpl Internal Plugin Compile Include PHP
 * Compiles the {include_php} tag
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 * @author     Uwe Tews
 */

/**
 * hiweb_tpl Internal Plugin Compile Insert Class
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 */
class Smurty_Internal_Compile_Include_Php extends Smurty_Internal_CompileBase
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smurty_Internal_CompileBase
     */
    public $required_attributes = array('file');
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smurty_Internal_CompileBase
     */
    public $shorttag_order = array('file');
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smurty_Internal_CompileBase
     */
    public $optional_attributes = array('once', 'assign');

    /**
     * Compiles code for the {include_php} tag
     *
     * @param  array  $args     array with attributes from parser
     * @param  object $compiler compiler object
     *
     * @throws SmurtyException
     * @return string compiled code
     */
    public function compile($args, $compiler)
    {
        if (!($compiler->smurty instanceof hiweb_tplChild)) {
            throw new SmurtyException("{include_php} is deprecated, use hiweb_tplChild class to enable");
        }
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        /** @var Smurty_Internal_Template $_smurty_tpl
         * used in evaluated code
         */
        $_smurty_tpl = $compiler->template;
        $_filepath = false;
        eval('$_file = ' . $_attr['file'] . ';');
        if (!isset($compiler->smurty->security_policy) && file_exists($_file)) {
            $_filepath = $_file;
        } else {
            if (isset($compiler->smurty->security_policy)) {
                $_dir = $compiler->smurty->security_policy->trusted_dir;
            } else {
                $_dir = $compiler->smurty->trusted_dir;
            }
            if (!empty($_dir)) {
                foreach ((array) $_dir as $_script_dir) {
                    $_script_dir = rtrim($_script_dir, '/\\') . DIR_SEPARATOR;
                    if (file_exists($_script_dir . $_file)) {
                        $_filepath = $_script_dir . $_file;
                        break;
                    }
                }
            }
        }
        if ($_filepath == false) {
            $compiler->trigger_template_error("{include_php} file '{$_file}' is not readable", $compiler->lex->taglineno);
        }

        if (isset($compiler->smurty->security_policy)) {
            $compiler->smurty->security_policy->isTrustedPHPDir($_filepath);
        }

        if (isset($_attr['assign'])) {
            // output will be stored in a smurty variable instead of being displayed
            $_assign = $_attr['assign'];
        }
        $_once = '_once';
        if (isset($_attr['once'])) {
            if ($_attr['once'] == 'false') {
                $_once = '';
            }
        }

        if (isset($_assign)) {
            return "<?php ob_start(); include{$_once} ('{$_filepath}'); \$_smurty_tpl->assign({$_assign},ob_get_contents()); ob_end_clean();?>";
        } else {
            return "<?php include{$_once} ('{$_filepath}');?>\n";
        }
    }
}

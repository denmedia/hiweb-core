<?php
/**
 * hiweb_tpl Internal Plugin Compile Rdelim
 * Compiles the {rdelim} tag
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 * @author     Uwe Tews
 */

/**
 * hiweb_tpl Internal Plugin Compile Rdelim Class
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 */
class Smurty_Internal_Compile_Rdelim extends Smurty_Internal_CompileBase
{
    /**
     * Compiles code for the {rdelim} tag
     * This tag does output the right delimiter.
     *
     * @param  array  $args     array with attributes from parser
     * @param  object $compiler compiler object
     *
     * @return string compiled code
     */
    public function compile($args, $compiler)
    {
        $_attr = $this->getAttributes($compiler, $args);
        if ($_attr['nocache'] === true) {
            $compiler->trigger_template_error('nocache option not allowed', $compiler->lex->taglineno);
        }
        // this tag does not return compiled code
        $compiler->has_code = true;

        return $compiler->smurty->right_delimiter;
    }
}

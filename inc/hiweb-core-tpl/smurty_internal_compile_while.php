<?php
/**
 * hiweb_tpl Internal Plugin Compile While
 * Compiles the {while} tag
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 * @author     Uwe Tews
 */

/**
 * hiweb_tpl Internal Plugin Compile While Class
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 */
class Smurty_Internal_Compile_While extends Smurty_Internal_CompileBase
{
    /**
     * Compiles code for the {while} tag
     *
     * @param  array  $args      array with attributes from parser
     * @param  object $compiler  compiler object
     * @param  array  $parameter array with compilation parameter
     *
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);
        $this->openTag($compiler, 'while', $compiler->nocache);

        if (!array_key_exists("if condition", $parameter)) {
            $compiler->trigger_template_error("missing while condition", $compiler->lex->taglineno);
        }

        // maybe nocache because of nocache variables
        $compiler->nocache = $compiler->nocache | $compiler->tag_nocache;
        if (is_array($parameter['if condition'])) {
            if ($compiler->nocache) {
                $_nocache = ',true';
                // create nocache var to make it know for further compiling
                if (is_array($parameter['if condition']['var'])) {
                    $compiler->template->tpl_vars[trim($parameter['if condition']['var']['var'], "'")] = new Smurty_variable(null, true);
                } else {
                    $compiler->template->tpl_vars[trim($parameter['if condition']['var'], "'")] = new Smurty_variable(null, true);
                }
            } else {
                $_nocache = '';
            }
            if (is_array($parameter['if condition']['var'])) {
                $_output = "<?php if (!isset(\$_smurty_tpl->tpl_vars[" . $parameter['if condition']['var']['var'] . "]) || !is_array(\$_smurty_tpl->tpl_vars[" . $parameter['if condition']['var']['var'] . "]->value)) \$_smurty_tpl->createLocalArrayVariable(" . $parameter['if condition']['var']['var'] . "$_nocache);\n";
                $_output .= "while (\$_smurty_tpl->tpl_vars[" . $parameter['if condition']['var']['var'] . "]->value" . $parameter['if condition']['var']['smurty_internal_index'] . " = " . $parameter['if condition']['value'] . ") {?>";
            } else {
                $_output = "<?php if (!isset(\$_smurty_tpl->tpl_vars[" . $parameter['if condition']['var'] . "])) \$_smurty_tpl->tpl_vars[" . $parameter['if condition']['var'] . "] = new Smurty_Variable(null{$_nocache});";
                $_output .= "while (\$_smurty_tpl->tpl_vars[" . $parameter['if condition']['var'] . "]->value = " . $parameter['if condition']['value'] . ") {?>";
            }

            return $_output;
        } else {
            return "<?php while ({$parameter['if condition']}) {?>";
        }
    }
}

/**
 * hiweb_tpl Internal Plugin Compile Whileclose Class
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 */
class Smurty_Internal_Compile_Whileclose extends Smurty_Internal_CompileBase
{
    /**
     * Compiles code for the {/while} tag
     *
     * @param  array  $args     array with attributes from parser
     * @param  object $compiler compiler object
     *
     * @return string compiled code
     */
    public function compile($args, $compiler)
    {
        // must endblock be nocache?
        if ($compiler->nocache) {
            $compiler->tag_nocache = true;
        }
        $compiler->nocache = $this->closeTag($compiler, array('while'));

        return "<?php }?>";
    }
}

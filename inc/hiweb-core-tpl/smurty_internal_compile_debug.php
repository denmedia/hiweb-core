<?php
/**
 * hiweb_tpl Internal Plugin Compile Debug
 * Compiles the {debug} tag.
 * It opens a window the the hiweb_tpl Debugging Console.
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 * @author     Uwe Tews
 */

/**
 * hiweb_tpl Internal Plugin Compile Debug Class
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 */
class Smurty_Internal_Compile_Debug extends Smurty_Internal_CompileBase
{
    /**
     * Compiles code for the {debug} tag
     *
     * @param  array  $args     array with attributes from parser
     * @param  object $compiler compiler object
     *
     * @return string compiled code
     */
    public function compile($args, $compiler)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        // compile always as nocache
        $compiler->tag_nocache = true;

        // display debug template
        $_output = "<?php \$_smurty_tpl->smurty->loadPlugin('Smurty_Internal_Debug'); Smurty_Internal_Debug::display_debug(\$_smurty_tpl); ?>";

        return $_output;
    }
}

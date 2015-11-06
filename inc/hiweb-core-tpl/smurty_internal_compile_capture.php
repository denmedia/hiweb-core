<?php
/**
 * hiweb_tpl Internal Plugin Compile Capture
 * Compiles the {capture} tag
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 * @author     Uwe Tews
 */

/**
 * hiweb_tpl Internal Plugin Compile Capture Class
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 */
class Smurty_Internal_Compile_Capture extends Smurty_Internal_CompileBase
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smurty_Internal_CompileBase
     */
    public $shorttag_order = array('name');
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smurty_Internal_CompileBase
     */
    public $optional_attributes = array('name', 'assign', 'append');

    /**
     * Compiles code for the {capture} tag
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

        $buffer = isset($_attr['name']) ? $_attr['name'] : "'default'";
        $assign = isset($_attr['assign']) ? $_attr['assign'] : 'null';
        $append = isset($_attr['append']) ? $_attr['append'] : 'null';

        $compiler->_capture_stack[0][] = array($buffer, $assign, $append, $compiler->nocache);
        // maybe nocache because of nocache variables
        $compiler->nocache = $compiler->nocache | $compiler->tag_nocache;
        $_output = "<?php \$_smurty_tpl->_capture_stack[0][] = array($buffer, $assign, $append); ob_start(); ?>";

        return $_output;
    }
}

/**
 * hiweb_tpl Internal Plugin Compile Captureclose Class
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 */
class Smurty_Internal_Compile_CaptureClose extends Smurty_Internal_CompileBase
{
    /**
     * Compiles code for the {/capture} tag
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
        // must endblock be nocache?
        if ($compiler->nocache) {
            $compiler->tag_nocache = true;
        }

        list($buffer, $assign, $append, $compiler->nocache) = array_pop($compiler->_capture_stack[0]);

        $_output = "<?php list(\$_capture_buffer, \$_capture_assign, \$_capture_append) = array_pop(\$_smurty_tpl->_capture_stack[0]);\n";
        $_output .= "if (!empty(\$_capture_buffer)) {\n";
        $_output .= " if (isset(\$_capture_assign)) \$_smurty_tpl->assign(\$_capture_assign, ob_get_contents());\n";
        $_output .= " if (isset( \$_capture_append)) \$_smurty_tpl->append( \$_capture_append, ob_get_contents());\n";
        $_output .= " hiweb_tpl::\$_smurty_vars['capture'][\$_capture_buffer]=ob_get_clean();\n";
        $_output .= "} else \$_smurty_tpl->capture_error();?>";

        return $_output;
    }
}

<?php
/**
 * hiweb_tpl Internal Plugin Compile Object Function
 * Compiles code for registered objects as function
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 * @author     Uwe Tews
 */

/**
 * hiweb_tpl Internal Plugin Compile Object Function Class
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 */
class Smurty_Internal_Compile_Private_Object_Function extends Smurty_Internal_CompileBase
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smurty_Internal_CompileBase
     */
    public $optional_attributes = array('_any');

    /**
     * Compiles code for the execution of function plugin
     *
     * @param  array  $args      array with attributes from parser
     * @param  object $compiler  compiler object
     * @param  array  $parameter array with compilation parameter
     * @param  string $tag       name of function
     * @param  string $method    name of method to call
     *
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter, $tag, $method)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);
        if ($_attr['nocache'] === true) {
            $compiler->tag_nocache = true;
        }
        unset($_attr['nocache']);
        $_assign = null;
        if (isset($_attr['assign'])) {
            $_assign = $_attr['assign'];
            unset($_attr['assign']);
        }
        // method or property ?
        if (method_exists($compiler->smurty->registered_objects[$tag][0], $method)) {
            // convert attributes into parameter array string
            if ($compiler->smurty->registered_objects[$tag][2]) {
                $_paramsArray = array();
                foreach ($_attr as $_key => $_value) {
                    if (is_int($_key)) {
                        $_paramsArray[] = "$_key=>$_value";
                    } else {
                        $_paramsArray[] = "'$_key'=>$_value";
                    }
                }
                $_params = 'array(' . implode(",", $_paramsArray) . ')';
                $return = "\$_smurty_tpl->smurty->registered_objects['{$tag}'][0]->{$method}({$_params},\$_smurty_tpl)";
            } else {
                $_params = implode(",", $_attr);
                $return = "\$_smurty_tpl->smurty->registered_objects['{$tag}'][0]->{$method}({$_params})";
            }
        } else {
            // object property
            $return = "\$_smurty_tpl->smurty->registered_objects['{$tag}'][0]->{$method}";
        }

        if (empty($_assign)) {
            // This tag does create output
            $compiler->has_output = true;
            $output = "<?php echo {$return};?>\n";
        } else {
            $output = "<?php \$_smurty_tpl->assign({$_assign},{$return});?>\n";
        }

        return $output;
    }
}

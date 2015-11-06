<?php
/**
 * hiweb_tpl Internal Plugin Compile Append
 * Compiles the {append} tag
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 * @author     Uwe Tews
 */

/**
 * hiweb_tpl Internal Plugin Compile Append Class
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 */
class Smurty_Internal_Compile_Append extends Smurty_Internal_Compile_Assign
{
    /**
     * Compiles code for the {append} tag
     *
     * @param  array  $args      array with attributes from parser
     * @param  object $compiler  compiler object
     * @param  array  $parameter array with compilation parameter
     *
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter)
    {
        // the following must be assigned at runtime because it will be overwritten in parent class
        $this->required_attributes = array('var', 'value');
        $this->shorttag_order = array('var', 'value');
        $this->optional_attributes = array('scope', 'index');
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);
        // map to compile assign attributes
        if (isset($_attr['index'])) {
            $_params['smurty_internal_index'] = '[' . $_attr['index'] . ']';
            unset($_attr['index']);
        } else {
            $_params['smurty_internal_index'] = '[]';
        }
        $_new_attr = array();
        foreach ($_attr as $key => $value) {
            $_new_attr[] = array($key => $value);
        }
        // call compile assign
        return parent::compile($_new_attr, $compiler, $_params);
    }
}

<?php

/**
 * hiweb_tpl Internal Plugin Compile extend
 * Compiles the {extends} tag
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 * @author     Uwe Tews
 */

/**
 * hiweb_tpl Internal Plugin Compile extend Class
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 */
class Smurty_Internal_Compile_Extends extends Smurty_Internal_CompileBase
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
     * Compiles code for the {extends} tag
     *
     * @param array  $args     array with attributes from parser
     * @param object $compiler compiler object
     *
     * @return string compiled code
     */
    public function compile($args, $compiler)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);
        if ($_attr['nocache'] === true) {
            $compiler->trigger_template_error('nocache option not allowed', $compiler->lex->taglineno);
        }
        if (strpos($_attr['file'], '$_tmp') !== false) {
            $compiler->trigger_template_error('illegal value for file attribute', $compiler->lex->taglineno);
        }

        $name = $_attr['file'];
        /** @var Smurty_Internal_Template $_smurty_tpl
         * used in evaluated code
         */
        $_smurty_tpl = $compiler->template;
        eval("\$tpl_name = $name;");
        // create template object
        $_template = new $compiler->smurty->template_class($tpl_name, $compiler->smurty, $compiler->template);
        // check for recursion
        $uid = $_template->source->uid;
        if (isset($compiler->extends_uid[$uid])) {
            $compiler->trigger_template_error("illegal recursive call of \"$include_file\"", $compiler->lex->line - 1);
        }
        $compiler->extends_uid[$uid] = true;
        if (empty($_template->source->components)) {
            array_unshift($compiler->sources, $_template->source);
        } else {
            foreach ($_template->source->components as $source) {
                array_unshift($compiler->sources, $source);
                $uid = $source->uid;
                if (isset($compiler->extends_uid[$uid])) {
                    $compiler->trigger_template_error("illegal recursive call of \"{$source->filepath}\"", $compiler->lex->line - 1);
                }
                $compiler->extends_uid[$uid] = true;
            }
        }
        unset ($_template);
        $compiler->inheritance_child = true;
        $compiler->lex->yypushstate(Smurty_Internal_Templatelexer::CHILDBODY);
        return '';
    }
}

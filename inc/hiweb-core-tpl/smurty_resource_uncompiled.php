<?php
/**
 * hiweb_tpl Resource Plugin
 *
 * @package    hiweb_tpl
 * @subpackage TemplateResources
 * @author     Rodney Rehm
 */

/**
 * hiweb_tpl Resource Plugin
 * Base implementation for resource plugins that don't use the compiler
 *
 * @package    hiweb_tpl
 * @subpackage TemplateResources
 */
abstract class Smurty_Resource_Uncompiled extends Smurty_Resource
{
    /**
     * Render and output the template (without using the compiler)
     *
     * @param  Smurty_Template_Source   $source    source object
     * @param  Smurty_Internal_Template $_template template object
     *
     * @throws SmurtyException          on failure
     */
    abstract public function renderUncompiled(Smurty_Template_Source $source, Smurty_Internal_Template $_template);

    /**
     * populate compiled object with compiled filepath
     *
     * @param Smurty_Template_Compiled $compiled  compiled object
     * @param Smurty_Internal_Template $_template template object (is ignored)
     */
    public function populateCompiledFilepath(Smurty_Template_Compiled $compiled, Smurty_Internal_Template $_template)
    {
        $compiled->filepath = false;
        $compiled->timestamp = false;
        $compiled->exists = false;
    }
}

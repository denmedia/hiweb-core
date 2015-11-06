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
 * Base implementation for resource plugins that don't compile cache
 *
 * @package    hiweb_tpl
 * @subpackage TemplateResources
 */
abstract class Smurty_Resource_Recompiled extends Smurty_Resource
{
    /**
     * populate Compiled Object with compiled filepath
     *
     * @param  Smurty_Template_Compiled $compiled  compiled object
     * @param  Smurty_Internal_Template $_template template object
     *
     * @return void
     */
    public function populateCompiledFilepath(Smurty_Template_Compiled $compiled, Smurty_Internal_Template $_template)
    {
        $compiled->filepath = false;
        $compiled->timestamp = false;
        $compiled->exists = false;
    }
}

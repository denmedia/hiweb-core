<?php
/**
 * hiweb_tpl Internal Plugin Resource Extends
 *
 * @package    hiweb_tpl
 * @subpackage TemplateResources
 * @author     Uwe Tews
 * @author     Rodney Rehm
 */

/**
 * hiweb_tpl Internal Plugin Resource Extends
 * Implements the file system as resource for hiweb_tpl which {extend}s a chain of template files templates
 *
 * @package    hiweb_tpl
 * @subpackage TemplateResources
 */
class Smurty_Internal_Resource_Extends extends Smurty_Resource
{
    /**
     * mbstring.overload flag
     *
     * @var int
     */
    public $mbstring_overload = 0;

    /**
     * populate Source Object with meta data from Resource
     *
     * @param Smurty_Template_Source   $source    source object
     * @param Smurty_Internal_Template $_template template object
     *
     * @throws SmurtyException
     */
    public function populate(Smurty_Template_Source $source, Smurty_Internal_Template $_template = null)
    {
        $uid = '';
        $sources = array();
        $components = explode('|', $source->name);
        $exists = true;
        foreach ($components as $component) {
            $s = Smurty_Resource::source(null, $source->smurty, $component);
            if ($s->type == 'php') {
                throw new SmurtyException("Resource type {$s->type} cannot be used with the extends resource type");
            }
            $sources[$s->uid] = $s;
            $uid .= realpath($s->filepath);
            if ($_template && $_template->smurty->compile_check) {
                $exists = $exists && $s->exists;
            }
        }
        $source->components = $sources;
        $source->filepath = $s->filepath;
        $source->uid = sha1($uid);
        if ($_template && $_template->smurty->compile_check) {
            $source->timestamp = $s->timestamp;
            $source->exists = $exists;
        }
        // need the template at getContent()
        $source->template = $_template;
    }

    /**
     * populate Source Object with timestamp and exists from Resource
     *
     * @param Smurty_Template_Source $source source object
     */
    public function populateTimestamp(Smurty_Template_Source $source)
    {
        $source->exists = true;
        foreach ($source->components as $s) {
            $source->exists = $source->exists && $s->exists;
        }
        $source->timestamp = $s->timestamp;
    }

    /**
     * Load template's source from files into current template object
     *
     * @param Smurty_Template_Source $source source object
     *
     * @return string template source
     * @throws SmurtyException if source cannot be loaded
     */
    public function getContent(Smurty_Template_Source $source)
    {
        if (!$source->exists) {
            throw new SmurtyException("Unable to read template {$source->type} '{$source->name}'");
        }

        $_components = array_reverse($source->components);

        $_content = '';
        foreach ($_components as $_component) {
            // read content
            $_content .= $_component->content;
        }
        return $_content;
    }

    /**
     * Determine basename for compiled filename
     *
     * @param Smurty_Template_Source $source source object
     *
     * @return string resource's basename
     */
    public function getBasename(Smurty_Template_Source $source)
    {
        return str_replace(':', '.', basename($source->filepath));
    }
}

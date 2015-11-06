<?php
/**
 * hiweb_tpl Internal Plugin Resource File
 *
 * @package    hiweb_tpl
 * @subpackage TemplateResources
 * @author     Uwe Tews
 * @author     Rodney Rehm
 */

/**
 * hiweb_tpl Internal Plugin Resource File
 * Implements the file system as resource for hiweb_tpl templates
 *
 * @package    hiweb_tpl
 * @subpackage TemplateResources
 */
class Smurty_Internal_Resource_File extends Smurty_Resource
{
    /**
     * populate Source Object with meta data from Resource
     *
     * @param Smurty_Template_Source   $source    source object
     * @param Smurty_Internal_Template $_template template object
     */
    public function populate(Smurty_Template_Source $source, Smurty_Internal_Template $_template = null)
    {
        $source->filepath = $this->buildFilepath($source, $_template);

        if ($source->filepath !== false) {
            if (is_object($source->smurty->security_policy)) {
                $source->smurty->security_policy->isTrustedResourceDir($source->filepath);
            }

            $source->uid = sha1(realpath($source->filepath));
            if ($source->smurty->compile_check && !isset($source->timestamp)) {
                $source->timestamp = @filemtime($source->filepath);
                $source->exists = !!$source->timestamp;
            }
        }
    }

    /**
     * populate Source Object with timestamp and exists from Resource
     *
     * @param Smurty_Template_Source $source source object
     */
    public function populateTimestamp(Smurty_Template_Source $source)
    {
        $source->timestamp = @filemtime($source->filepath);
        $source->exists = !!$source->timestamp;
    }

    /**
     * Load template's source from file into current template object
     *
     * @param  Smurty_Template_Source $source source object
     *
     * @return string                 template source
     * @throws SmurtyException        if source cannot be loaded
     */
    public function getContent(Smurty_Template_Source $source)
    {
        if ($source->timestamp) {
            return file_get_contents($source->filepath);
        }
        if ($source instanceof Smurty_Config_Source) {
            throw new SmurtyException("Unable to read config {$source->type} '{$source->name}'");
        }
        throw new SmurtyException("Unable to read template {$source->type} '{$source->name}'");
    }

    /**
     * Determine basename for compiled filename
     *
     * @param  Smurty_Template_Source $source source object
     *
     * @return string                 resource's basename
     */
    public function getBasename(Smurty_Template_Source $source)
    {
        $_file = $source->name;
        if (($_pos = strpos($_file, ']')) !== false) {
            $_file = substr($_file, $_pos + 1);
        }

        return basename($_file);
    }
}

<?php
/**
 * hiweb_tpl Internal Plugin Resource Registered
 *
 * @package    hiweb_tpl
 * @subpackage TemplateResources
 * @author     Uwe Tews
 * @author     Rodney Rehm
 */

/**
 * hiweb_tpl Internal Plugin Resource Registered
 * Implements the registered resource for hiweb_tpl template
 *
 * @package    hiweb_tpl
 * @subpackage TemplateResources
 * @deprecated
 */
class Smurty_Internal_Resource_Registered extends Smurty_Resource
{
    /**
     * populate Source Object with meta data from Resource
     *
     * @param  Smurty_Template_Source   $source    source object
     * @param  Smurty_Internal_Template $_template template object
     *
     * @return void
     */
    public function populate(Smurty_Template_Source $source, Smurty_Internal_Template $_template = null)
    {
        $source->filepath = $source->type . ':' . $source->name;
        $source->uid = sha1($source->filepath);
        if ($source->smurty->compile_check) {
            $source->timestamp = $this->getTemplateTimestamp($source);
            $source->exists = !!$source->timestamp;
        }
    }

    /**
     * populate Source Object with timestamp and exists from Resource
     *
     * @param  Smurty_Template_Source $source source object
     *
     * @return void
     */
    public function populateTimestamp(Smurty_Template_Source $source)
    {
        $source->timestamp = $this->getTemplateTimestamp($source);
        $source->exists = !!$source->timestamp;
    }

    /**
     * Get timestamp (epoch) the template source was modified
     *
     * @param  Smurty_Template_Source $source source object
     *
     * @return integer|boolean        timestamp (epoch) the template was modified, false if resources has no timestamp
     */
    public function getTemplateTimestamp(Smurty_Template_Source $source)
    {
        // return timestamp
        $time_stamp = false;
        call_user_func_array($source->smurty->registered_resources[$source->type][0][1], array($source->name, &$time_stamp, $source->smurty));

        return is_numeric($time_stamp) ? (int) $time_stamp : $time_stamp;
    }

    /**
     * Load template's source by invoking the registered callback into current template object
     *
     * @param  Smurty_Template_Source $source source object
     *
     * @return string                 template source
     * @throws SmurtyException        if source cannot be loaded
     */
    public function getContent(Smurty_Template_Source $source)
    {
        // return template string
        $t = call_user_func_array($source->smurty->registered_resources[$source->type][0][0], array($source->name, &$source->content, $source->smurty));
        if (is_bool($t) && !$t) {
            throw new SmurtyException("Unable to read template {$source->type} '{$source->name}'");
        }

        return $source->content;
    }

    /**
     * Determine basename for compiled filename
     *
     * @param  Smurty_Template_Source $source source object
     *
     * @return string                 resource's basename
     */
    protected function getBasename(Smurty_Template_Source $source)
    {
        return basename($source->name);
    }
}

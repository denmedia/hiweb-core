<?php
/**
 * hiweb_tpl Internal Plugin Resource Stream
 * Implements the streams as resource for hiweb_tpl template
 *
 * @package    hiweb_tpl
 * @subpackage TemplateResources
 * @author     Uwe Tews
 * @author     Rodney Rehm
 */

/**
 * hiweb_tpl Internal Plugin Resource Stream
 * Implements the streams as resource for hiweb_tpl template
 *
 * @link       http://php.net/streams
 * @package    hiweb_tpl
 * @subpackage TemplateResources
 */
class Smurty_Internal_Resource_Stream extends Smurty_Resource_Recompiled
{
    /**
     * populate Source Object with meta data from Resource
     *
     * @param Smurty_Template_Source   $source    source object
     * @param Smurty_Internal_Template $_template template object
     *
     * @return void
     */
    public function populate(Smurty_Template_Source $source, Smurty_Internal_Template $_template = null)
    {
        if (strpos($source->resource, '://') !== false) {
            $source->filepath = $source->resource;
        } else {
            $source->filepath = str_replace(':', '://', $source->resource);
        }
        $source->uid = false;
        $source->content = $this->getContent($source);
        $source->timestamp = false;
        $source->exists = !!$source->content;
    }

    /**
     * Load template's source from stream into current template object
     *
     * @param Smurty_Template_Source $source source object
     *
     * @return string template source
     * @throws SmurtyException if source cannot be loaded
     */
    public function getContent(Smurty_Template_Source $source)
    {
        $t = '';
        // the availability of the stream has already been checked in Smurty_Resource::fetch()
        $fp = fopen($source->filepath, 'r+');
        if ($fp) {
            while (!feof($fp) && ($current_line = fgets($fp)) !== false) {
                $t .= $current_line;
            }
            fclose($fp);

            return $t;
        } else {
            return false;
        }
    }

    /**
     * modify resource_name according to resource handlers specifications
     *
     * @param hiweb_tpl   $smurty        hiweb_tpl instance
     * @param string   $resource_name resource_name to make unique
     * @param  boolean $is_config     flag for config resource
     *
     * @return string unique resource name
     */
    protected function buildUniqueResourceName(hiweb_tpl $smurty, $resource_name, $is_config = false)
    {
        return get_class($this) . '#' . $resource_name;
    }
}

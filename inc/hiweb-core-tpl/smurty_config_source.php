<?php
/**
 * hiweb_tpl Internal Plugin
 *
 * @package    hiweb_tpl
 * @subpackage TemplateResources
 */

/**
 * hiweb_tpl Resource Data Object
 * Meta Data Container for Config Files
 *
 * @package    hiweb_tpl
 * @subpackage TemplateResources
 * @author     Rodney Rehm
 * @property string $content
 * @property int    $timestamp
 * @property bool   $exists
 */
class Smurty_Config_Source extends Smurty_Template_Source
{
    /**
     * create Config Object container
     *
     * @param Smurty_Resource $handler         Resource Handler this source object communicates with
     * @param hiweb_tpl          $smurty          hiweb_tpl instance this source object belongs to
     * @param string          $resource        full config_resource
     * @param string          $type            type of resource
     * @param string          $name            resource name
     * @param string          $unique_resource unqiue resource name
     */
    public function __construct(Smurty_Resource $handler, hiweb_tpl $smurty, $resource, $type, $name, $unique_resource)
    {
        $this->handler = $handler; // Note: prone to circular references

        // Note: these may be ->config_compiler_class etc in the future
        //$this->config_compiler_class = $handler->config_compiler_class;
        //$this->config_lexer_class = $handler->config_lexer_class;
        //$this->config_parser_class = $handler->config_parser_class;

        $this->smurty = $smurty;
        $this->resource = $resource;
        $this->type = $type;
        $this->name = $name;
        $this->unique_resource = $unique_resource;
    }

    /**
     * <<magic>> Generic setter.
     *
     * @param  string $property_name valid: content, timestamp, exists
     * @param  mixed  $value         newly assigned value (not check for correct type)
     *
     * @throws SmurtyException when the given property name is not valid
     */
    public function __set($property_name, $value)
    {
        switch ($property_name) {
            case 'content':
            case 'timestamp':
            case 'exists':
                $this->$property_name = $value;
                break;

            default:
                throw new SmurtyException("invalid config property '$property_name'.");
        }
    }

    /**
     * <<magic>> Generic getter.
     *
     * @param  string $property_name valid: content, timestamp, exists
     *
     * @return mixed|void
     * @throws SmurtyException when the given property name is not valid
     */
    public function __get($property_name)
    {
        switch ($property_name) {
            case 'timestamp':
            case 'exists':
                $this->handler->populateTimestamp($this);

                return $this->$property_name;

            case 'content':
                return $this->content = $this->handler->getContent($this);

            default:
                throw new SmurtyException("config property '$property_name' does not exist.");
        }
    }
}

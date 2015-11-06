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
 * Base implementation for resource plugins
 *
 * @package    hiweb_tpl
 * @subpackage TemplateResources
 */
abstract class Smurty_Resource
{
    /**
     * cache for Smurty_Template_Source instances
     *
     * @var array
     */
    public static $sources = array();
    /**
     * cache for Smurty_Template_Compiled instances
     *
     * @var array
     */
    public static $compileds = array();
    /**
     * cache for Smurty_Resource instances
     *
     * @var array
     */
    public static $resources = array();
    /**
     * resource types provided by the core
     *
     * @var array
     */
    protected static $sysplugins = array(
        'file'    => true,
        'string'  => true,
        'extends' => true,
        'stream'  => true,
        'eval'    => true,
        'php'     => true
    );

    /**
     * Name of the Class to compile this resource's contents with
     *
     * @var string
     */
    public $compiler_class = 'Smurty_Internal_SmurtyTemplateCompiler';

    /**
     * Name of the Class to tokenize this resource's contents with
     *
     * @var string
     */
    public $template_lexer_class = 'Smurty_Internal_Templatelexer';

    /**
     * Name of the Class to parse this resource's contents with
     *
     * @var string
     */
    public $template_parser_class = 'Smurty_Internal_Templateparser';

    /**
     * Load template's source into current template object
     * {@internal The loaded source is assigned to $_template->source->content directly.}}
     *
     * @param  Smurty_Template_Source $source source object
     *
     * @return string                 template source
     * @throws SmurtyException        if source cannot be loaded
     */
    abstract public function getContent(Smurty_Template_Source $source);

    /**
     * populate Source Object with meta data from Resource
     *
     * @param Smurty_Template_Source   $source    source object
     * @param Smurty_Internal_Template $_template template object
     */
    abstract public function populate(Smurty_Template_Source $source, Smurty_Internal_Template $_template = null);

    /**
     * populate Source Object with timestamp and exists from Resource
     *
     * @param Smurty_Template_Source $source source object
     */
    public function populateTimestamp(Smurty_Template_Source $source)
    {
        // intentionally left blank
    }

    /**
     * modify resource_name according to resource handlers specifications
     *
     * @param  hiweb_tpl  $smurty        hiweb_tpl instance
     * @param  string  $resource_name resource_name to make unique
     * @param  boolean $is_config     flag for config resource
     *
     * @return string unique resource name
     */
    protected function buildUniqueResourceName(hiweb_tpl $smurty, $resource_name, $is_config = false)
    {
        if ($is_config) {
            return get_class($this) . '#' . $smurty->joined_config_dir . '#' . $resource_name;
        } else {
            return get_class($this) . '#' . $smurty->joined_template_dir . '#' . $resource_name;
        }
    }

    /**
     * populate Compiled Object with compiled filepath
     *
     * @param Smurty_Template_Compiled $compiled  compiled object
     * @param Smurty_Internal_Template $_template template object
     */
    public function populateCompiledFilepath(Smurty_Template_Compiled $compiled, Smurty_Internal_Template $_template)
    {
        $_compile_id = isset($_template->compile_id) ? preg_replace('![^\w\|]+!', '_', $_template->compile_id) : null;
        $_filepath = $compiled->source->uid;
        // if use_sub_dirs, break file into directories
        if ($_template->smurty->use_sub_dirs) {
            $_filepath = substr($_filepath, 0, 2) . DIR_SEPARATOR
                . substr($_filepath, 2, 2) . DIR_SEPARATOR
                . substr($_filepath, 4, 2) . DIR_SEPARATOR
                . $_filepath;
        }
        $_compile_dir_sep = $_template->smurty->use_sub_dirs ? DIR_SEPARATOR : '^';
        if (isset($_compile_id)) {
            $_filepath = $_compile_id . $_compile_dir_sep . $_filepath;
        }
        // caching token
        if ($_template->caching) {
            $_cache = '.cache';
        } else {
            $_cache = '';
        }
        $_compile_dir = $_template->smurty->getCompileDir();
        // set basename if not specified
        $_basename = $this->getBasename($compiled->source);
        if ($_basename === null) {
            $_basename = basename(preg_replace('![^\w\/]+!', '_', $compiled->source->name));
        }
        // separate (optional) basename by dot
        if ($_basename) {
            $_basename = '.' . $_basename;
        }

        $compiled->filepath = $_compile_dir . $_filepath . '.' . $compiled->source->type . $_basename . $_cache . '.php';
    }

    /**
     * Normalize Paths "foo/../bar" to "bar"
     *
     * @param  string  $_path path to normalize
     * @param  boolean $ds    respect windows directory separator
     *
     * @return string  normalized path
     */
    protected function normalizePath($_path, $ds = true)
    {
        if ($ds) {
            // don't we all just love windows?
            $_path = str_replace('\\', '/', $_path);
        }

        $offset = 0;

        // resolve simples
        $_path = preg_replace('#/\./(\./)*#', '/', $_path);
        // resolve parents
        while (true) {
            $_parent = strpos($_path, '/../', $offset);
            if (!$_parent) {
                break;
            } elseif ($_path[$_parent - 1] === '.') {
                $offset = $_parent + 3;
                continue;
            }

            $_pos = strrpos($_path, '/', $_parent - strlen($_path) - 1);
            if ($_pos === false) {
                // don't we all just love windows?
                $_pos = $_parent;
            }

            $_path = substr_replace($_path, '', $_pos, $_parent + 3 - $_pos);
        }

        if ($ds && DIR_SEPARATOR != '/') {
            // don't we all just love windows?
            $_path = str_replace('/', '\\', $_path);
        }

        return $_path;
    }

    /**
     * build template filepath by traversing the template_dir array
     *
     * @param  Smurty_Template_Source   $source    source object
     * @param  Smurty_Internal_Template $_template template object
     *
     * @return string                   fully qualified filepath
     * @throws SmurtyException          if default template handler is registered but not callable
     */
    protected function buildFilepath(Smurty_Template_Source $source, Smurty_Internal_Template $_template = null)
    {
        $file = $source->name;
        if ($source instanceof Smurty_Config_Source) {
            $_directories = $source->smurty->getConfigDir();
            $_default_handler = $source->smurty->default_config_handler_func;
        } else {
            $_directories = $source->smurty->getTemplateDir();
            $_default_handler = $source->smurty->default_template_handler_func;
        }

        // go relative to a given template?
        $_file_is_dotted = $file[0] == '.' && ($file[1] == '.' || $file[1] == '/' || $file[1] == "\\");
        if ($_template && $_template->parent instanceof Smurty_Internal_Template && $_file_is_dotted) {
            if ($_template->parent->source->type != 'file' && $_template->parent->source->type != 'extends' && !$_template->parent->allow_relative_path) {
                throw new SmurtyException("Template '{$file}' cannot be relative to template of resource type '{$_template->parent->source->type}'");
            }
            $file = dirname($_template->parent->source->filepath) . DIR_SEPARATOR . $file;
            $_file_exact_match = true;
            if (!preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $file)) {
                // the path gained from the parent template is relative to the current working directory
                // as expansions (like include_path) have already been done
                $file = getcwd() . DIR_SEPARATOR . $file;
            }
        }

        // resolve relative path
        if (!preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $file)) {
            // don't we all just love windows?
            $_path = DIR_SEPARATOR . trim($file, '/');
            $_was_relative = true;
        } else {
            // don't we all just love windows?
            $_path = str_replace('\\', '/', $file);
        }
        $_path = $this->normalizePath($_path, false);
        if (DIR_SEPARATOR != '/') {
            // don't we all just love windows?
            $_path = str_replace('/', '\\', $_path);
        }
        // revert to relative
        if (isset($_was_relative)) {
            $_path = substr($_path, 1);
        }

        // this is only required for directories
        $file = rtrim($_path, '/\\');

        // files relative to a template only get one shot
        if (isset($_file_exact_match)) {
            return $this->fileExists($source, $file) ? $file : false;
        }

        // template_dir index?
        if (preg_match('#^\[(?P<key>[^\]]+)\](?P<file>.+)$#', $file, $match)) {
            $_directory = null;
            // try string indexes
            if (isset($_directories[$match['key']])) {
                $_directory = $_directories[$match['key']];
            } elseif (is_numeric($match['key'])) {
                // try numeric index
                $match['key'] = (int) $match['key'];
                if (isset($_directories[$match['key']])) {
                    $_directory = $_directories[$match['key']];
                } else {
                    // try at location index
                    $keys = array_keys($_directories);
                    $_directory = $_directories[$keys[$match['key']]];
                }
            }

            if ($_directory) {
                $_file = substr($file, strpos($file, ']') + 1);
                $_filepath = $_directory . $_file;
                if ($this->fileExists($source, $_filepath)) {
                    return $_filepath;
                }
            }
        }

        $_stream_resolve_include_path = function_exists('stream_resolve_include_path');

        // relative file name?
        if (!preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $file)) {
            foreach ($_directories as $_directory) {
                $_filepath = $_directory . $file;
                if ($this->fileExists($source, $_filepath)) {
                    return $this->normalizePath($_filepath);
                }
                if ($source->smurty->use_include_path && !preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $_directory)) {
                    // try PHP include_path
                    if ($_stream_resolve_include_path) {
                        $_filepath = stream_resolve_include_path($_filepath);
                    } else {
                        $_filepath = Smurty_Internal_Get_Include_Path::getIncludePath($_filepath);
                    }

                    if ($_filepath !== false) {
                        if ($this->fileExists($source, $_filepath)) {
                            return $this->normalizePath($_filepath);
                        }
                    }
                }
            }
        }

        // try absolute filepath
        if ($this->fileExists($source, $file)) {
            return $file;
        }

        // no tpl file found
        if ($_default_handler) {
            if (!is_callable($_default_handler)) {
                if ($source instanceof Smurty_Config_Source) {
                    throw new SmurtyException("Default config handler not callable");
                } else {
                    throw new SmurtyException("Default template handler not callable");
                }
            }
            $_return = call_user_func_array($_default_handler,
                                            array($source->type, $source->name, &$_content, &$_timestamp, $source->smurty));
            if (is_string($_return)) {
                $source->timestamp = @filemtime($_return);
                $source->exists = !!$source->timestamp;

                return $_return;
            } elseif ($_return === true) {
                $source->content = $_content;
                $source->timestamp = $_timestamp;
                $source->exists = true;

                return $_filepath;
            }
        }

        // give up
        return false;
    }

    /**
     * test is file exists and save timestamp
     *
     * @param  Smurty_Template_Source $source source object
     * @param  string                 $file   file name
     *
     * @return bool                   true if file exists
     */
    protected function fileExists(Smurty_Template_Source $source, $file)
    {
        $source->timestamp = is_file($file) ? @filemtime($file) : false;

        return $source->exists = !!$source->timestamp;
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
        return null;
    }

    /**
     * Load Resource Handler
     *
     * @param  hiweb_tpl $smurty smurty object
     * @param  string $type   name of the resource
     *
     * @throws SmurtyException
     * @return Smurty_Resource Resource Handler
     */
    public static function load(hiweb_tpl $smurty, $type)
    {
        // try smurty's cache
        if (isset($smurty->_resource_handlers[$type])) {
            return $smurty->_resource_handlers[$type];
        }

        // try registered resource
        if (isset($smurty->registered_resources[$type])) {
            if ($smurty->registered_resources[$type] instanceof Smurty_Resource) {
                $smurty->_resource_handlers[$type] = $smurty->registered_resources[$type];
                // note registered to smurty is not kept unique!
                return $smurty->_resource_handlers[$type];
            }

            if (!isset(self::$resources['registered'])) {
                self::$resources['registered'] = new Smurty_Internal_Resource_Registered();
            }
            if (!isset($smurty->_resource_handlers[$type])) {
                $smurty->_resource_handlers[$type] = self::$resources['registered'];
            }

            return $smurty->_resource_handlers[$type];
        }

        // try hiweb-core-tpl dir
        if (isset(self::$sysplugins[$type])) {
            if (!isset(self::$resources[$type])) {
                $_resource_class = 'Smurty_Internal_Resource_' . ucfirst($type);
                self::$resources[$type] = new $_resource_class();
            }

            return $smurty->_resource_handlers[$type] = self::$resources[$type];
        }

        // try plugins dir
        $_resource_class = 'Smurty_Resource_' . ucfirst($type);
        if ($smurty->loadPlugin($_resource_class)) {
            if (isset(self::$resources[$type])) {
                return $smurty->_resource_handlers[$type] = self::$resources[$type];
            }

            if (class_exists($_resource_class, false)) {
                self::$resources[$type] = new $_resource_class();

                return $smurty->_resource_handlers[$type] = self::$resources[$type];
            } else {
                $smurty->registerResource($type, array(
                    "smurty_resource_{$type}_source",
                    "smurty_resource_{$type}_timestamp",
                    "smurty_resource_{$type}_secure",
                    "smurty_resource_{$type}_trusted"
                ));

                // give it another try, now that the resource is registered properly
                return self::load($smurty, $type);
            }
        }

        // try streams
        $_known_stream = stream_get_wrappers();
        if (in_array($type, $_known_stream)) {
            // is known stream
            if (is_object($smurty->security_policy)) {
                $smurty->security_policy->isTrustedStream($type);
            }
            if (!isset(self::$resources['stream'])) {
                self::$resources['stream'] = new Smurty_Internal_Resource_Stream();
            }

            return $smurty->_resource_handlers[$type] = self::$resources['stream'];
        }

        // TODO: try default_(template|config)_handler

        // give up
        throw new SmurtyException("Unknown resource type '{$type}'");
    }

    /**
     * extract resource_type and resource_name from template_resource and config_resource
     * @note "C:/foo.tpl" was forced to file resource up till hiweb_tpl 3.1.3 (including).
     *
     * @param  string $resource_name    template_resource or config_resource to parse
     * @param  string $default_resource the default resource_type defined in $smurty
     * @param  string &$name            the parsed resource name
     * @param  string &$type            the parsed resource type
     *
     * @return void
     */
    protected static function parseResourceName($resource_name, $default_resource, &$name, &$type)
    {
        $parts = explode(':', $resource_name, 2);
        if (!isset($parts[1]) || !isset($parts[0][1])) {
            // no resource given, use default
            // or single character before the colon is not a resource type, but part of the filepath
            $type = $default_resource;
            $name = $resource_name;
        } else {
            $type = $parts[0];
            $name = $parts[1];
        }
    }

    /**
     * modify resource_name according to resource handlers specifications
     *
     * @param  hiweb_tpl $smurty        hiweb_tpl instance
     * @param  string $resource_name resource_name to make unique
     *
     * @return string unique resource name
     */

    /**
     * modify template_resource according to resource handlers specifications
     *
     * @param  Smurty_Internal_Template $template          hiweb_tpl instance
     * @param  string                   $template_resource template_resource to extract resource handler and name of
     *
     * @return string unique resource name
     */
    public static function getUniqueTemplateName($template, $template_resource)
    {
        self::parseResourceName($template_resource, $template->smurty->default_resource_type, $name, $type);
        // TODO: optimize for hiweb_tpl's internal resource types
        $resource = Smurty_Resource::load($template->smurty, $type);
        // go relative to a given template?
        $_file_is_dotted = $name[0] == '.' && ($name[1] == '.' || $name[1] == '/' || $name[1] == "\\");
        if ($template instanceof Smurty_Internal_Template && $_file_is_dotted && ($template->source->type == 'file' || $template->parent->source->type == 'extends')) {
            $name = dirname($template->source->filepath) . DIR_SEPARATOR . $name;
        }
        return $resource->buildUniqueResourceName($template->smurty, $name);
    }

    /**
     * initialize Source Object for given resource
     * Either [$_template] or [$smurty, $template_resource] must be specified
     *
     * @param  Smurty_Internal_Template $_template         template object
     * @param  hiweb_tpl                   $smurty            smurty object
     * @param  string                   $template_resource resource identifier
     *
     * @return Smurty_Template_Source   Source Object
     */
    public static function source(Smurty_Internal_Template $_template = null, hiweb_tpl $smurty = null, $template_resource = null)
    {
        if ($_template) {
            $smurty = $_template->smurty;
            $template_resource = $_template->template_resource;
        }

        // parse resource_name, load resource handler, identify unique resource name
        self::parseResourceName($template_resource, $smurty->default_resource_type, $name, $type);
        $resource = Smurty_Resource::load($smurty, $type);
        // go relative to a given template?
        $_file_is_dotted = isset($name[0]) && $name[0] == '.' && ($name[1] == '.' || $name[1] == '/' || $name[1] == "\\");
        if ($_file_is_dotted && isset($_template) && $_template->parent instanceof Smurty_Internal_Template && ($_template->parent->source->type == 'file' || $_template->parent->source->type == 'extends')) {
            $name2 = dirname($_template->parent->source->filepath) . DIR_SEPARATOR . $name;
        } else {
            $name2 = $name;
        }
        $unique_resource_name = $resource->buildUniqueResourceName($smurty, $name2);

        // check runtime cache
        $_cache_key = 'template|' . $unique_resource_name;
        if ($smurty->compile_id) {
            $_cache_key .= '|' . $smurty->compile_id;
        }
        if (isset(self::$sources[$_cache_key])) {
            return self::$sources[$_cache_key];
        }

        // create source
        $source = new Smurty_Template_Source($resource, $smurty, $template_resource, $type, $name, $unique_resource_name);
        $resource->populate($source, $_template);

        // runtime cache
        self::$sources[$_cache_key] = $source;

        return $source;
    }

    /**
     * initialize Config Source Object for given resource
     *
     * @param  Smurty_Internal_Config $_config config object
     *
     * @throws SmurtyException
     * @return Smurty_Config_Source   Source Object
     */
    public static function config(Smurty_Internal_Config $_config)
    {
        static $_incompatible_resources = array('eval' => true, 'string' => true, 'extends' => true, 'php' => true);
        $config_resource = $_config->config_resource;
        $smurty = $_config->smurty;

        // parse resource_name
        self::parseResourceName($config_resource, $smurty->default_config_type, $name, $type);

        // make sure configs are not loaded via anything smurty can't handle
        if (isset($_incompatible_resources[$type])) {
            throw new SmurtyException ("Unable to use resource '{$type}' for config");
        }

        // load resource handler, identify unique resource name
        $resource = Smurty_Resource::load($smurty, $type);
        $unique_resource_name = $resource->buildUniqueResourceName($smurty, $name, true);

        // check runtime cache
        $_cache_key = 'config|' . $unique_resource_name;
        if (isset(self::$sources[$_cache_key])) {
            return self::$sources[$_cache_key];
        }

        // create source
        $source = new Smurty_Config_Source($resource, $smurty, $config_resource, $type, $name, $unique_resource_name);
        $resource->populate($source, null);

        // runtime cache
        self::$sources[$_cache_key] = $source;

        return $source;
    }
}

/**
 * hiweb_tpl Resource Data Object
 * Meta Data Container for Template Files
 *
 * @package    hiweb_tpl
 * @subpackage TemplateResources
 * @author     Rodney Rehm
 * @property integer $timestamp Source Timestamp
 * @property boolean $exists    Source Existence
 * @property boolean $template  Extended Template reference
 * @property string  $content   Source Content
 */
class Smurty_Template_Source
{
    /**
     * Name of the Class to compile this resource's contents with
     *
     * @var string
     */
    public $compiler_class = null;

    /**
     * Name of the Class to tokenize this resource's contents with
     *
     * @var string
     */
    public $template_lexer_class = null;

    /**
     * Name of the Class to parse this resource's contents with
     *
     * @var string
     */
    public $template_parser_class = null;

    /**
     * Unique Template ID
     *
     * @var string
     */
    public $uid = null;

    /**
     * Template Resource (Smurty_Internal_Template::$template_resource)
     *
     * @var string
     */
    public $resource = null;

    /**
     * Resource Type
     *
     * @var string
     */
    public $type = null;

    /**
     * Resource Name
     *
     * @var string
     */
    public $name = null;

    /**
     * Unique Resource Name
     *
     * @var string
     */
    public $unique_resource = null;

    /**
     * Source Filepath
     *
     * @var string
     */
    public $filepath = null;

    /**
     * Source is bypassing compiler
     *
     * @var boolean
     */
    public $uncompiled = null;

    /**
     * Source must be recompiled on every occasion
     *
     * @var boolean
     */
    public $recompiled = null;

    /**
     * The Components an extended template is made of
     *
     * @var array
     */
    public $components = null;

    /**
     * Resource Handler
     *
     * @var Smurty_Resource
     */
    public $handler = null;

    /**
     * hiweb_tpl instance
     *
     * @var hiweb_tpl
     */
    public $smurty = null;

    /**
     * create Source Object container
     *
     * @param Smurty_Resource $handler         Resource Handler this source object communicates with
     * @param hiweb_tpl          $smurty          hiweb_tpl instance this source object belongs to
     * @param string          $resource        full template_resource
     * @param string          $type            type of resource
     * @param string          $name            resource name
     * @param string          $unique_resource unique resource name
     */
    public function __construct(Smurty_Resource $handler, hiweb_tpl $smurty, $resource, $type, $name, $unique_resource)
    {
        $this->handler = $handler; // Note: prone to circular references

        $this->compiler_class = $handler->compiler_class;
        $this->template_lexer_class = $handler->template_lexer_class;
        $this->template_parser_class = $handler->template_parser_class;
        $this->uncompiled = $this->handler instanceof Smurty_Resource_Uncompiled;
        $this->recompiled = $this->handler instanceof Smurty_Resource_Recompiled;

        $this->smurty = $smurty;
        $this->resource = $resource;
        $this->type = $type;
        $this->name = $name;
        $this->unique_resource = $unique_resource;
    }

    /**
     * get a Compiled Object of this source
     *
     * @param  Smurty_Internal_Template|Smurty_Internal_Config $_template template object
     *
     * @return Smurty_Template_Compiled compiled object
     */
    public function getCompiled($_template)
    {
        // check runtime cache
        $_cache_key = $this->unique_resource . '#' . $_template->compile_id;
        if (isset(Smurty_Resource::$compileds[$_cache_key])) {
            return Smurty_Resource::$compileds[$_cache_key];
        }

        $compiled = new Smurty_Template_Compiled($this);
        $this->handler->populateCompiledFilepath($compiled, $_template);
        $compiled->timestamp = @filemtime($compiled->filepath);
        $compiled->exists = !!$compiled->timestamp;

        // runtime cache
        Smurty_Resource::$compileds[$_cache_key] = $compiled;

        return $compiled;
    }

    /**
     * render the uncompiled source
     *
     * @param Smurty_Internal_Template $_template template object
     */
    public function renderUncompiled(Smurty_Internal_Template $_template)
    {
        return $this->handler->renderUncompiled($this, $_template);
    }

    /**
     * <<magic>> Generic Setter.
     *
     * @param  string $property_name valid: timestamp, exists, content, template
     * @param  mixed  $value         new value (is not checked)
     *
     * @throws SmurtyException if $property_name is not valid
     */
    public function __set($property_name, $value)
    {
        switch ($property_name) {
            // regular attributes
            case 'timestamp':
            case 'exists':
            case 'content':
                // required for extends: only
            case 'template':
                $this->$property_name = $value;
                break;

            default:
                throw new SmurtyException("invalid source property '$property_name'.");
        }
    }

    /**
     * <<magic>> Generic getter.
     *
     * @param  string $property_name valid: timestamp, exists, content
     *
     * @return mixed
     * @throws SmurtyException if $property_name is not valid
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
                throw new SmurtyException("source property '$property_name' does not exist.");
        }
    }
}

/**
 * hiweb_tpl Resource Data Object
 * Meta Data Container for Template Files
 *
 * @package    hiweb_tpl
 * @subpackage TemplateResources
 * @author     Rodney Rehm
 * @property string $content compiled content
 */
class Smurty_Template_Compiled
{
    /**
     * Compiled Filepath
     *
     * @var string
     */
    public $filepath = null;

    /**
     * Compiled Timestamp
     *
     * @var integer
     */
    public $timestamp = null;

    /**
     * Compiled Existence
     *
     * @var boolean
     */
    public $exists = false;

    /**
     * Compiled Content Loaded
     *
     * @var boolean
     */
    public $loaded = false;

    /**
     * Template was compiled
     *
     * @var boolean
     */
    public $isCompiled = false;

    /**
     * Source Object
     *
     * @var Smurty_Template_Source
     */
    public $source = null;

    /**
     * Metadata properties
     * populated by Smurty_Internal_Template::decodeProperties()
     *
     * @var array
     */
    public $_properties = null;

    /**
     * create Compiled Object container
     *
     * @param Smurty_Template_Source $source source object this compiled object belongs to
     */
    public function __construct(Smurty_Template_Source $source)
    {
        $this->source = $source;
    }
}

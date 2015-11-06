<?php
/**
 * hiweb_tpl Internal Plugin Template
 * This file contains the hiweb_tpl template engine
 *
 * @package    hiweb_tpl
 * @subpackage Template
 * @author     Uwe Tews
 */

/**
 * Main class with template data structures and methods
 *
 * @package    hiweb_tpl
 * @subpackage Template
 * @property Smurty_Template_Source   $source
 * @property Smurty_Template_Compiled $compiled
 * @property Smurty_Template_Cached   $cached
 */
class Smurty_Internal_Template extends Smurty_Internal_TemplateBase
{
    /**
     * cache_id
     *
     * @var string
     */
    public $cache_id = null;
    /**
     * $compile_id
     * @var string
     */
    public $compile_id = null;
    /**
     * caching enabled
     *
     * @var boolean
     */
    public $caching = null;
    /**
     * cache lifetime in seconds
     *
     * @var integer
     */
    public $cache_lifetime = null;
    /**
     * Template resource
     *
     * @var string
     */
    public $template_resource = null;
    /**
     * flag if compiled template is invalid and must be (re)compiled
     *
     * @var bool
     */
    public $mustCompile = null;
    /**
     * flag if template does contain nocache code sections
     *
     * @var bool
     */
    public $has_nocache_code = false;
    /**
     * special compiled and cached template properties
     *
     * @var array
     */
    public $properties = array('file_dependency' => array(),
                               'nocache_hash'    => '',
                               'function'        => array());
    /**
     * required plugins
     *
     * @var array
     */
    public $required_plugins = array('compiled' => array(), 'nocache' => array());
    /**
     * Global smurty instance
     *
     * @var hiweb_tpl
     */
    public $smurty = null;
    /**
     * blocks for template inheritance
     *
     * @var array
     */
    public $block_data = array();
    /**
     * variable filters
     *
     * @var array
     */
    public $variable_filters = array();
    /**
     * optional log of tag/attributes
     *
     * @var array
     */
    public $used_tags = array();
    /**
     * internal flag to allow relative path in child template blocks
     *
     * @var bool
     */
    public $allow_relative_path = false;
    /**
     * internal capture runtime stack
     *
     * @var array
     */
    public $_capture_stack = array(0 => array());

    /**
     * Create template data object
     * Some of the global hiweb_tpl settings copied to template scope
     * It load the required template resources and cacher plugins
     *
     * @param string                   $template_resource template resource string
     * @param hiweb_tpl                   $smurty            hiweb_tpl instance
     * @param Smurty_Internal_Template $_parent           back pointer to parent object with variables or null
     * @param mixed                    $_cache_id         cache   id or null
     * @param mixed                    $_compile_id       compile id or null
     * @param bool                     $_caching          use caching?
     * @param int                      $_cache_lifetime   cache life-time in seconds
     */
    public function __construct($template_resource, $smurty, $_parent = null, $_cache_id = null, $_compile_id = null, $_caching = null, $_cache_lifetime = null)
    {
        $this->smurty = & $smurty;
        // hiweb_tpl parameter
        $this->cache_id = $_cache_id === null ? $this->smurty->cache_id : $_cache_id;
        $this->compile_id = $_compile_id === null ? $this->smurty->compile_id : $_compile_id;
        $this->caching = $_caching === null ? $this->smurty->caching : $_caching;
        if ($this->caching === true) {
            $this->caching = hiweb_tpl::CACHING_LIFETIME_CURRENT;
        }
        $this->cache_lifetime = $_cache_lifetime === null ? $this->smurty->cache_lifetime : $_cache_lifetime;
        $this->parent = $_parent;
        // Template resource
        $this->template_resource = $template_resource;
        // copy block data of template inheritance
        if ($this->parent instanceof Smurty_Internal_Template) {
            $this->block_data = $this->parent->block_data;
        }
    }

    /**
     * Returns if the current template must be compiled by the hiweb_tpl compiler
     * It does compare the timestamps of template source and the compiled templates and checks the force compile configuration
     *
     * @throws SmurtyException
     * @return boolean true if the template must be compiled
     */
    public function mustCompile()
    {
        if (!$this->source->exists) {
            if ($this->parent instanceof Smurty_Internal_Template) {
                $parent_resource = " in '$this->parent->template_resource}'";
            } else {
                $parent_resource = '';
            }
            throw new SmurtyException("Unable to load template {$this->source->type} '{$this->source->name}'{$parent_resource}");
        }
        if ($this->mustCompile === null) {
            $this->mustCompile = (!$this->source->uncompiled && ($this->smurty->force_compile || $this->source->recompiled || $this->compiled->timestamp === false ||
                    ($this->smurty->compile_check && $this->compiled->timestamp < $this->source->timestamp)));
        }

        return $this->mustCompile;
    }

    /**
     * Compiles the template
     * If the template is not evaluated the compiled template is saved on disk
     */
    public function compileTemplateSource()
    {
        if (!$this->source->recompiled) {
            $this->properties['file_dependency'] = array();
            if ($this->source->components) {
                // for the extends resource the compiler will fill it
                // uses real resource for file dependency
                // $source = end($this->source->components);
                // $this->properties['file_dependency'][$this->source->uid] = array($this->source->filepath, $this->source->timestamp, $source->type);
            } else {
                $this->properties['file_dependency'][$this->source->uid] = array($this->source->filepath, $this->source->timestamp, $this->source->type);
            }
        }
        // compile locking
        if ($this->smurty->compile_locking && !$this->source->recompiled) {
            if ($saved_timestamp = $this->compiled->timestamp) {
                touch($this->compiled->filepath);
            }
        }
        // call compiler
        try {
            $code = $this->compiler->compileTemplate($this);
        }
        catch (Exception $e) {
            // restore old timestamp in case of error
            if ($this->smurty->compile_locking && !$this->source->recompiled && $saved_timestamp) {
                touch($this->compiled->filepath, $saved_timestamp);
            }
            throw $e;
        }
        // compiling succeded
        if (!$this->source->recompiled && $this->compiler->write_compiled_code) {
            // write compiled template
            $_filepath = $this->compiled->filepath;
            if ($_filepath === false) {
                throw new SmurtyException('getCompiledFilepath() did not return a destination to save the compiled template to');
            }
            Smurty_Internal_Write_File::writeFile($_filepath, $code, $this->smurty);
            $this->compiled->exists = true;
            $this->compiled->isCompiled = true;
        }
        // release compiler object to free memory
        unset($this->compiler);
    }

    /**
     * Writes the cached template output
     *
     * @param string $content
     *
     * @return bool
     */
    public function writeCachedContent($content)
    {
        if ($this->source->recompiled || !($this->caching == hiweb_tpl::CACHING_LIFETIME_CURRENT || $this->caching == hiweb_tpl::CACHING_LIFETIME_SAVED)) {
            // don't write cache file
            return false;
        }
        $this->properties['cache_lifetime'] = $this->cache_lifetime;
        $this->properties['unifunc'] = 'content_' . str_replace(array('.', ','), '_', uniqid('', true));
        $content = $this->createTemplateCodeFrame($content, true);
        /** @var Smurty_Internal_Template $_smurty_tpl
         * used in evaluated code
         */
        $_smurty_tpl = $this;
        eval("?>" . $content);
        $this->cached->valid = true;
        $this->cached->processed = true;

        return $this->cached->write($this, $content);
    }

    /**
     * Template code runtime function to get subtemplate content
     *
     * @param string  $template       the resource handle of the template file
     * @param mixed   $cache_id       cache id to be used with this template
     * @param mixed   $compile_id     compile id to be used with this template
     * @param integer $caching        cache mode
     * @param integer $cache_lifetime life time of cache data
     * @param         $data
     * @param int     $parent_scope   scope in which {include} should execute
     *
     * @returns string template content
     */
    public function getSubTemplate($template, $cache_id, $compile_id, $caching, $cache_lifetime, $data, $parent_scope)
    {
        // already in template cache?
        if ($this->smurty->allow_ambiguous_resources) {
            $_templateId = Smurty_Resource::getUniqueTemplateName($this, $template) . $cache_id . $compile_id;
        } else {
            $_templateId = $this->smurty->joined_template_dir . '#' . $template . $cache_id . $compile_id;
        }

        if (isset($_templateId[150])) {
            $_templateId = sha1($_templateId);
        }
        if (isset($this->smurty->template_objects[$_templateId])) {
            // clone cached template object because of possible recursive call
            $tpl = clone $this->smurty->template_objects[$_templateId];
            $tpl->parent = $this;
            $tpl->caching = $caching;
            $tpl->cache_lifetime = $cache_lifetime;
        } else {
            $tpl = new $this->smurty->template_class($template, $this->smurty, $this, $cache_id, $compile_id, $caching, $cache_lifetime);
        }
        // get variables from calling scope
        if ($parent_scope == hiweb_tpl::SCOPE_LOCAL) {
            $tpl->tpl_vars = $this->tpl_vars;
            $tpl->tpl_vars['smurty'] = clone $this->tpl_vars['smurty'];
        } elseif ($parent_scope == hiweb_tpl::SCOPE_PARENT) {
            $tpl->tpl_vars = & $this->tpl_vars;
        } elseif ($parent_scope == hiweb_tpl::SCOPE_GLOBAL) {
            $tpl->tpl_vars = & hiweb_tpl::$global_tpl_vars;
        } elseif (($scope_ptr = $this->getScopePointer($parent_scope)) == null) {
            $tpl->tpl_vars = & $this->tpl_vars;
        } else {
            $tpl->tpl_vars = & $scope_ptr->tpl_vars;
        }
        $tpl->config_vars = $this->config_vars;
        if (!empty($data)) {
            // set up variable values
            foreach ($data as $_key => $_val) {
                $tpl->tpl_vars[$_key] = new Smurty_variable($_val);
            }
        }

        return $tpl->fetch(null, null, null, null, false, false, true);
    }

    /**
     * Template code runtime function to set up an inline subtemplate
     *
     * @param string  $template       the resource handle of the template file
     * @param mixed   $cache_id       cache id to be used with this template
     * @param mixed   $compile_id     compile id to be used with this template
     * @param integer $caching        cache mode
     * @param integer $cache_lifetime life time of cache data
     * @param         $data
     * @param int     $parent_scope   scope in which {include} should execute
     * @param string  $hash           nocache hash code
     *
     * @returns string template content
     */
    public function setupInlineSubTemplate($template, $cache_id, $compile_id, $caching, $cache_lifetime, $data, $parent_scope, $hash)
    {
        $tpl = new $this->smurty->template_class($template, $this->smurty, $this, $cache_id, $compile_id, $caching, $cache_lifetime);
        $tpl->properties['nocache_hash'] = $hash;
        // get variables from calling scope
        if ($parent_scope == hiweb_tpl::SCOPE_LOCAL) {
            $tpl->tpl_vars = $this->tpl_vars;
            $tpl->tpl_vars['smurty'] = clone $this->tpl_vars['smurty'];
        } elseif ($parent_scope == hiweb_tpl::SCOPE_PARENT) {
            $tpl->tpl_vars = & $this->tpl_vars;
        } elseif ($parent_scope == hiweb_tpl::SCOPE_GLOBAL) {
            $tpl->tpl_vars = & hiweb_tpl::$global_tpl_vars;
        } elseif (($scope_ptr = $this->getScopePointer($parent_scope)) == null) {
            $tpl->tpl_vars = & $this->tpl_vars;
        } else {
            $tpl->tpl_vars = & $scope_ptr->tpl_vars;
        }
        $tpl->config_vars = $this->config_vars;
        if (!empty($data)) {
            // set up variable values
            foreach ($data as $_key => $_val) {
                $tpl->tpl_vars[$_key] = new Smurty_variable($_val);
            }
        }

        return $tpl;
    }

    /**
     * Create code frame for compiled and cached templates
     *
     * @param  string $content optional template content
     * @param  bool   $cache   flag for cache file
     *
     * @return string
     */
    public function createTemplateCodeFrame($content = '', $cache = false)
    {
        $plugins_string = '';
        // include code for plugins
        if (!$cache) {
            if (!empty($this->required_plugins['compiled'])) {
                $plugins_string = '<?php ';
                foreach ($this->required_plugins['compiled'] as $tmp) {
                    foreach ($tmp as $data) {
                        $file = addslashes($data['file']);
                        if (is_Array($data['function'])) {
                            $plugins_string .= "if (!is_callable(array('{$data['function'][0]}','{$data['function'][1]}'))) include '{$file}';\n";
                        } else {
                            $plugins_string .= "if (!is_callable('{$data['function']}')) include '{$file}';\n";
                        }
                    }
                }
                $plugins_string .= '?>';
            }
            if (!empty($this->required_plugins['nocache'])) {
                $this->has_nocache_code = true;
                $plugins_string .= "<?php echo '/*%%SmurtyNocache:{$this->properties['nocache_hash']}%%*/<?php \$_smurty = \$_smurty_tpl->smurty; ";
                foreach ($this->required_plugins['nocache'] as $tmp) {
                    foreach ($tmp as $data) {
                        $file = addslashes($data['file']);
                        if (is_Array($data['function'])) {
                            $plugins_string .= addslashes("if (!is_callable(array('{$data['function'][0]}','{$data['function'][1]}'))) include '{$file}';\n");
                        } else {
                            $plugins_string .= addslashes("if (!is_callable('{$data['function']}')) include '{$file}';\n");
                        }
                    }
                }
                $plugins_string .= "?>/*/%%SmurtyNocache:{$this->properties['nocache_hash']}%%*/';?>\n";
            }
        }
        // build property code
        $this->properties['has_nocache_code'] = $this->has_nocache_code;
        $output = '';
        if (!$this->source->recompiled) {
            $output = "<?php /*%%SmurtyHeaderCode:{$this->properties['nocache_hash']}%%*/";
            if ($this->smurty->direct_access_security) {
                $output .= "if(!defined('HIWEB_CORE_TPL_DIR')) exit('no direct access allowed');\n";
            }
        }
        if ($cache) {
            // remove compiled code of{function} definition
            unset($this->properties['function']);
            if (!empty($this->smurty->template_functions)) {
                // copy code of {function} tags called in nocache mode
                foreach ($this->smurty->template_functions as $name => $function_data) {
                    if (isset($function_data['called_nocache'])) {
                        foreach ($function_data['called_functions'] as $func_name) {
                            $this->smurty->template_functions[$func_name]['called_nocache'] = true;
                        }
                    }
                }
                foreach ($this->smurty->template_functions as $name => $function_data) {
                    if (isset($function_data['called_nocache'])) {
                        unset($function_data['called_nocache'], $function_data['called_functions'], $this->smurty->template_functions[$name]['called_nocache']);
                        $this->properties['function'][$name] = $function_data;
                    }
                }
            }
        }
        $this->properties['version'] = hiweb_tpl::HIWEB_TPL_VERSION;
        if (!isset($this->properties['unifunc'])) {
            $this->properties['unifunc'] = 'content_' . str_replace(array('.', ','), '_', uniqid('', true));
        }
        if (!$this->source->recompiled) {
            $output .= "\$_valid = \$_smurty_tpl->decodeProperties(" . var_export($this->properties, true) . ',' . ($cache ? 'true' : 'false') . "); /*/%%SmurtyHeaderCode%%*/?>\n";
            $output .= '<?php if ($_valid && !is_callable(\'' . $this->properties['unifunc'] . '\')) {function ' . $this->properties['unifunc'] . '($_smurty_tpl) {?>';
        }
        $output .= $plugins_string;
        $output .= $content;
        if (!$this->source->recompiled) {
            $output .= "<?php }} ?>\n";
        }

        return $output;
    }

    /**
     * This function is executed automatically when a compiled or cached template file is included
     * - Decode saved properties from compiled template and cache files
     * - Check if compiled or cache file is valid
     *
     * @param  array $properties special template properties
     * @param  bool  $cache      flag if called from cache file
     *
     * @return bool  flag if compiled or cache file is valid
     */
    public function decodeProperties($properties, $cache = false)
    {
        $this->has_nocache_code = $properties['has_nocache_code'];
        $this->properties['nocache_hash'] = $properties['nocache_hash'];
        if (isset($properties['cache_lifetime'])) {
            $this->properties['cache_lifetime'] = $properties['cache_lifetime'];
        }
        if (isset($properties['file_dependency'])) {
            $this->properties['file_dependency'] = array_merge($this->properties['file_dependency'], $properties['file_dependency']);
        }
        if (!empty($properties['function'])) {
            $this->properties['function'] = array_merge($this->properties['function'], $properties['function']);
            $this->smurty->template_functions = array_merge($this->smurty->template_functions, $properties['function']);
        }
        $this->properties['version'] = (isset($properties['version'])) ? $properties['version'] : '';
        $this->properties['unifunc'] = $properties['unifunc'];
        // check file dependencies at compiled code
        $is_valid = true;
        if ($this->properties['version'] != hiweb_tpl::HIWEB_TPL_VERSION) {
            $is_valid = false;
        } elseif (((!$cache && $this->smurty->compile_check && empty($this->compiled->_properties) && !$this->compiled->isCompiled) || $cache && ($this->smurty->compile_check === true || $this->smurty->compile_check === hiweb_tpl::COMPILECHECK_ON)) && !empty($this->properties['file_dependency'])) {
            foreach ($this->properties['file_dependency'] as $_file_to_check) {
                if ($_file_to_check[2] == 'file' || $_file_to_check[2] == 'php') {
                    if ($this->source->filepath == $_file_to_check[0] && isset($this->source->timestamp)) {
                        // do not recheck current template
                        $mtime = $this->source->timestamp;
                    } else {
                        // file and php types can be checked without loading the respective resource handlers
                        $mtime = @filemtime($_file_to_check[0]);
                    }
                } elseif ($_file_to_check[2] == 'string') {
                    continue;
                } else {
                    $source = Smurty_Resource::source(null, $this->smurty, $_file_to_check[0]);
                    $mtime = $source->timestamp;
                }
                if (!$mtime || $mtime > $_file_to_check[1]) {
                    $is_valid = false;
                    break;
                }
            }
        }
        if ($cache) {
            // CACHING_LIFETIME_SAVED cache expiry has to be validated here since otherwise we'd define the unifunc
            if ($this->caching === hiweb_tpl::CACHING_LIFETIME_SAVED &&
                $this->properties['cache_lifetime'] >= 0 &&
                (time() > ($this->cached->timestamp + $this->properties['cache_lifetime']))
            ) {
                $is_valid = false;
            }
            $this->cached->valid = $is_valid;
        } else {
            $this->mustCompile = !$is_valid;
        }
        // store data in reusable Smurty_Template_Compiled
        if (!$cache) {
            $this->compiled->_properties = $properties;
        }

        return $is_valid;
    }

    /**
     * Template code runtime function to create a local hiweb_tpl variable for array assignments
     *
     * @param string $tpl_var tempate variable name
     * @param bool   $nocache cache mode of variable
     * @param int    $scope   scope of variable
     */
    public function createLocalArrayVariable($tpl_var, $nocache = false, $scope = hiweb_tpl::SCOPE_LOCAL)
    {
        if (!isset($this->tpl_vars[$tpl_var])) {
            $this->tpl_vars[$tpl_var] = new Smurty_variable(array(), $nocache, $scope);
        } else {
            $this->tpl_vars[$tpl_var] = clone $this->tpl_vars[$tpl_var];
            if ($scope != hiweb_tpl::SCOPE_LOCAL) {
                $this->tpl_vars[$tpl_var]->scope = $scope;
            }
            if (!(is_array($this->tpl_vars[$tpl_var]->value) || $this->tpl_vars[$tpl_var]->value instanceof ArrayAccess)) {
                settype($this->tpl_vars[$tpl_var]->value, 'array');
            }
        }
    }

    /**
     * Template code runtime function to get pointer to template variable array of requested scope
     *
     * @param  int $scope requested variable scope
     *
     * @return array array of template variables
     */
    public function &getScope($scope)
    {
        if ($scope == hiweb_tpl::SCOPE_PARENT && !empty($this->parent)) {
            return $this->parent->tpl_vars;
        } elseif ($scope == hiweb_tpl::SCOPE_ROOT && !empty($this->parent)) {
            $ptr = $this->parent;
            while (!empty($ptr->parent)) {
                $ptr = $ptr->parent;
            }

            return $ptr->tpl_vars;
        } elseif ($scope == hiweb_tpl::SCOPE_GLOBAL) {
            return hiweb_tpl::$global_tpl_vars;
        }
        $null = null;

        return $null;
    }

    /**
     * Get parent or root of template parent chain
     *
     * @param  int $scope pqrent or root scope
     *
     * @return mixed object
     */
    public function getScopePointer($scope)
    {
        if ($scope == hiweb_tpl::SCOPE_PARENT && !empty($this->parent)) {
            return $this->parent;
        } elseif ($scope == hiweb_tpl::SCOPE_ROOT && !empty($this->parent)) {
            $ptr = $this->parent;
            while (!empty($ptr->parent)) {
                $ptr = $ptr->parent;
            }

            return $ptr;
        }

        return null;
    }

    /**
     * [util function] counts an array, arrayaccess/traversable or PDOStatement object
     *
     * @param  mixed $value
     *
     * @return int   the count for arrays and objects that implement countable, 1 for other objects that don't, and 0 for empty elements
     */
    public function _count($value)
    {
        if (is_array($value) === true || $value instanceof Countable) {
            return count($value);
        } elseif ($value instanceof IteratorAggregate) {
            // Note: getIterator() returns a Traversable, not an Iterator
            // thus rewind() and valid() methods may not be present
            return iterator_count($value->getIterator());
        } elseif ($value instanceof Iterator) {
            return iterator_count($value);
        } elseif ($value instanceof PDOStatement) {
            return $value->rowCount();
        } elseif ($value instanceof Traversable) {
            return iterator_count($value);
        } elseif ($value instanceof ArrayAccess) {
            if ($value->offsetExists(0)) {
                return 1;
            }
        } elseif (is_object($value)) {
            return count($value);
        }

        return 0;
    }

    /**
     * runtime error not matching capture tags

     */
    public function capture_error()
    {
        throw new SmurtyException("Not matching {capture} open/close in \"{$this->template_resource}\"");
    }

    /**
     * Empty cache for this template
     *
     * @param integer $exp_time expiration time
     *
     * @return integer number of cache files deleted
     */
    public function clearCache($exp_time = null)
    {
        Smurty_CacheResource::invalidLoadedCache($this->smurty);

        return $this->cached->handler->clear($this->smurty, $this->template_name, $this->cache_id, $this->compile_id, $exp_time);
    }

    /**
     * set hiweb_tpl property in template context
     *
     * @param string $property_name property name
     * @param mixed  $value         value
     *
     * @throws SmurtyException
     */
    public function __set($property_name, $value)
    {
        switch ($property_name) {
            case 'source':
            case 'compiled':
            case 'cached':
            case 'compiler':
                $this->$property_name = $value;

                return;

            // FIXME: routing of template -> smurty attributes
            default:
                if (property_exists($this->smurty, $property_name)) {
                    $this->smurty->$property_name = $value;

                    return;
                }
        }

        throw new SmurtyException("invalid template property '$property_name'.");
    }

    /**
     * get hiweb_tpl property in template context
     *
     * @param string $property_name property name
     *
     * @throws SmurtyException
     */
    public function __get($property_name)
    {
        switch ($property_name) {
            case 'source':
                if (strlen($this->template_resource) == 0) {
                    throw new SmurtyException('Missing template name');
                }
                $this->source = Smurty_Resource::source($this);
                // cache template object under a unique ID
                // do not cache eval resources
                if ($this->source->type != 'eval') {
                    if ($this->smurty->allow_ambiguous_resources) {
                        $_templateId = $this->source->unique_resource . $this->cache_id . $this->compile_id;
                    } else {
                        $_templateId = $this->smurty->joined_template_dir . '#' . $this->template_resource . $this->cache_id . $this->compile_id;
                    }

                    if (isset($_templateId[150])) {
                        $_templateId = sha1($_templateId);
                    }
                    $this->smurty->template_objects[$_templateId] = $this;
                }

                return $this->source;

            case 'compiled':
                $this->compiled = $this->source->getCompiled($this);

                return $this->compiled;

            case 'cached':
                if (!class_exists('Smurty_Template_Cached')) {
                    include HIWEB_CORE_HTML_ASSETS . 'smurty_cacheresource.php';
                }
                $this->cached = new Smurty_Template_Cached($this);

                return $this->cached;

            case 'compiler':
                $this->smurty->loadPlugin($this->source->compiler_class);
                $this->compiler = new $this->source->compiler_class($this->source->template_lexer_class, $this->source->template_parser_class, $this->smurty);

                return $this->compiler;

            // FIXME: routing of template -> smurty attributes
            default:
                if (property_exists($this->smurty, $property_name)) {
                    return $this->smurty->$property_name;
                }
        }

        throw new SmurtyException("template property '$property_name' does not exist.");
    }

    /**
     * Template data object destructor

     */
    public function __destruct()
    {
        if ($this->smurty->cache_locking && isset($this->cached) && $this->cached->is_locked) {
            $this->cached->handler->releaseLock($this->smurty, $this->cached);
        }
    }
}

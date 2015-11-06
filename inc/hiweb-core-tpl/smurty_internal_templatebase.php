<?php
/**
 * hiweb_tpl Internal Plugin hiweb_tpl Template  Base
 * This file contains the basic shared methods for template handling
 *
 * @package    hiweb_tpl
 * @subpackage Template
 * @author     Uwe Tews
 */

/**
 * Class with shared template methods
 *
 * @package    hiweb_tpl
 * @subpackage Template
 */
abstract class Smurty_Internal_TemplateBase extends Smurty_Internal_Data
{
    /**
     * fetches a rendered hiweb_tpl template
     *
     * @param  string $template         the resource handle of the template file or template object
     * @param  mixed  $cache_id         cache id to be used with this template
     * @param  mixed  $compile_id       compile id to be used with this template
     * @param  object $parent           next higher level of hiweb_tpl variables
     * @param  bool   $display          true: display, false: fetch
     * @param  bool   $merge_tpl_vars   if true parent template variables merged in to local scope
     * @param  bool   $no_output_filter if true do not run output filter
     *
     * @throws Exception
     * @throws SmurtyException
     * @return string rendered template output
     */
    public function fetch($template = null, $cache_id = null, $compile_id = null, $parent = null, $display = false, $merge_tpl_vars = true, $no_output_filter = false)
    {
        if ($template === null && $this instanceof $this->template_class) {
            $template = $this;
        }
        if ($cache_id !== null && is_object($cache_id)) {
            $parent = $cache_id;
            $cache_id = null;
        }
        if ($parent === null && ($this instanceof hiweb_tpl || is_string($template))) {
            $parent = $this;
        }
        // create template object if necessary
        $_template = ($template instanceof $this->template_class)
            ? $template
            : $this->smurty->createTemplate($template, $cache_id, $compile_id, $parent, false);
        // if called by hiweb_tpl object make sure we use current caching status
        if ($this instanceof hiweb_tpl) {
            $_template->caching = $this->caching;
        }
        // merge all variable scopes into template
        if ($merge_tpl_vars) {
            // save local variables
            $save_tpl_vars = $_template->tpl_vars;
            $save_config_vars = $_template->config_vars;
            $ptr_array = array($_template);
            $ptr = $_template;
            while (isset($ptr->parent)) {
                $ptr_array[] = $ptr = $ptr->parent;
            }
            $ptr_array = array_reverse($ptr_array);
            $parent_ptr = reset($ptr_array);
            $tpl_vars = $parent_ptr->tpl_vars;
            $config_vars = $parent_ptr->config_vars;
            while ($parent_ptr = next($ptr_array)) {
                if (!empty($parent_ptr->tpl_vars)) {
                    $tpl_vars = array_merge($tpl_vars, $parent_ptr->tpl_vars);
                }
                if (!empty($parent_ptr->config_vars)) {
                    $config_vars = array_merge($config_vars, $parent_ptr->config_vars);
                }
            }
            if (!empty(hiweb_tpl::$global_tpl_vars)) {
                $tpl_vars = array_merge(hiweb_tpl::$global_tpl_vars, $tpl_vars);
            }
            $_template->tpl_vars = $tpl_vars;
            $_template->config_vars = $config_vars;
        }
        // dummy local smurty variable
        if (!isset($_template->tpl_vars['smurty'])) {
            $_template->tpl_vars['smurty'] = new Smurty_Variable;
        }
        if (isset($this->smurty->error_reporting)) {
            $_smurty_old_error_level = error_reporting($this->smurty->error_reporting);
        }
        // check URL debugging control
        if (!$this->smurty->debugging && $this->smurty->debugging_ctrl == 'URL') {
            if (isset($_SERVER['QUERY_STRING'])) {
                $_query_string = $_SERVER['QUERY_STRING'];
            } else {
                $_query_string = '';
            }
            if (false !== strpos($_query_string, $this->smurty->smurty_debug_id)) {
                if (false !== strpos($_query_string, $this->smurty->smurty_debug_id . '=on')) {
                    // enable debugging for this browser session
                    setcookie('SMURTY_DEBUG', true);
                    $this->smurty->debugging = true;
                } elseif (false !== strpos($_query_string, $this->smurty->smurty_debug_id . '=off')) {
                    // disable debugging for this browser session
                    setcookie('SMURTY_DEBUG', false);
                    $this->smurty->debugging = false;
                } else {
                    // enable debugging for this page
                    $this->smurty->debugging = true;
                }
            } else {
                if (isset($_COOKIE['SMURTY_DEBUG'])) {
                    $this->smurty->debugging = true;
                }
            }
        }
        // must reset merge template date
        $_template->smurty->merged_templates_func = array();
        // get rendered template
        // disable caching for evaluated code
        if ($_template->source->recompiled) {
            $_template->caching = false;
        }
        // checks if template exists
        if (!$_template->source->exists) {
            if ($_template->parent instanceof Smurty_Internal_Template) {
                $parent_resource = " in '{$_template->parent->template_resource}'";
            } else {
                $parent_resource = '';
            }
            throw new SmurtyException("Unable to load template {$_template->source->type} '{$_template->source->name}'{$parent_resource}");
        }
        // read from cache or render
        if (!($_template->caching == hiweb_tpl::CACHING_LIFETIME_CURRENT || $_template->caching == hiweb_tpl::CACHING_LIFETIME_SAVED) || !$_template->cached->valid) {
            // render template (not loaded and not in cache)
            if (!$_template->source->uncompiled) {
                /** @var Smurty_Internal_Template $_smurty_tpl
                 * used in evaluated code
                 */
                $_smurty_tpl = $_template;
                if ($_template->source->recompiled) {
                    $code = $_template->compiler->compileTemplate($_template);
                    if ($this->smurty->debugging) {
                        Smurty_Internal_Debug::start_render($_template);
                    }
                    try {
                        ob_start();
                        eval("?>" . $code);
                        unset($code);
                    }
                    catch (Exception $e) {
                        ob_get_clean();
                        throw $e;
                    }
                } else {
                    if (!$_template->compiled->exists || ($_template->smurty->force_compile && !$_template->compiled->isCompiled)) {
                        $_template->compileTemplateSource();
                        $code = file_get_contents($_template->compiled->filepath);
                        eval("?>" . $code);
                        unset($code);
                        $_template->compiled->loaded = true;
                        $_template->compiled->isCompiled = true;
                    }
                    if ($this->smurty->debugging) {
                        Smurty_Internal_Debug::start_render($_template);
                    }
                    if (!$_template->compiled->loaded) {
                        include($_template->compiled->filepath);
                        if ($_template->mustCompile) {
                            // recompile and load again
                            $_template->compileTemplateSource();
                            $code = file_get_contents($_template->compiled->filepath);
                            eval("?>" . $code);
                            unset($code);
                            $_template->compiled->isCompiled = true;
                        }
                        $_template->compiled->loaded = true;
                    } else {
                        $_template->decodeProperties($_template->compiled->_properties, false);
                    }
                    try {
                        ob_start();
                        if (empty($_template->properties['unifunc']) || !is_callable($_template->properties['unifunc'])) {
                            throw new SmurtyException("Invalid compiled template for '{$_template->template_resource}'");
                        }
                        array_unshift($_template->_capture_stack, array());
                        //
                        // render compiled template
                        //
                        $_template->properties['unifunc']($_template);
                        // any unclosed {capture} tags ?
                        if (isset($_template->_capture_stack[0][0])) {
                            $_template->capture_error();
                        }
                        array_shift($_template->_capture_stack);
                    }
                    catch (Exception $e) {
                        ob_get_clean();
                        throw $e;
                    }
                }
            } else {
                if ($_template->source->uncompiled) {
                    if ($this->smurty->debugging) {
                        Smurty_Internal_Debug::start_render($_template);
                    }
                    try {
                        ob_start();
                        $_template->source->renderUncompiled($_template);
                    }
                    catch (Exception $e) {
                        ob_get_clean();
                        throw $e;
                    }
                } else {
                    throw new SmurtyException("Resource '$_template->source->type' must have 'renderUncompiled' method");
                }
            }
            $_output = ob_get_clean();
            if (!$_template->source->recompiled && empty($_template->properties['file_dependency'][$_template->source->uid])) {
                $_template->properties['file_dependency'][$_template->source->uid] = array($_template->source->filepath, $_template->source->timestamp, $_template->source->type);
            }
            if ($_template->parent instanceof Smurty_Internal_Template) {
                $_template->parent->properties['file_dependency'] = array_merge($_template->parent->properties['file_dependency'], $_template->properties['file_dependency']);
                foreach ($_template->required_plugins as $code => $tmp1) {
                    foreach ($tmp1 as $name => $tmp) {
                        foreach ($tmp as $type => $data) {
                            $_template->parent->required_plugins[$code][$name][$type] = $data;
                        }
                    }
                }
            }
            if ($this->smurty->debugging) {
                Smurty_Internal_Debug::end_render($_template);
            }
            // write to cache when nessecary
            if (!$_template->source->recompiled && ($_template->caching == hiweb_tpl::CACHING_LIFETIME_SAVED || $_template->caching == hiweb_tpl::CACHING_LIFETIME_CURRENT)) {
                if ($this->smurty->debugging) {
                    Smurty_Internal_Debug::start_cache($_template);
                }
                $_template->properties['has_nocache_code'] = false;
                // get text between non-cached items
                $cache_split = preg_split("!/\*%%SmurtyNocache:{$_template->properties['nocache_hash']}%%\*\/(.+?)/\*/%%SmurtyNocache:{$_template->properties['nocache_hash']}%%\*/!s", $_output);
                // get non-cached items
                preg_match_all("!/\*%%SmurtyNocache:{$_template->properties['nocache_hash']}%%\*\/(.+?)/\*/%%SmurtyNocache:{$_template->properties['nocache_hash']}%%\*/!s", $_output, $cache_parts);
                $output = '';
                // loop over items, stitch back together
                foreach ($cache_split as $curr_idx => $curr_split) {
                    // escape PHP tags in template content
                    $output .= preg_replace('/(<%|%>|<\?php|<\?|\?>)/', "<?php echo '\$1'; ?>\n", $curr_split);
                    if (isset($cache_parts[0][$curr_idx])) {
                        $_template->properties['has_nocache_code'] = true;
                        // remove nocache tags from cache output
                        $output .= preg_replace("!/\*/?%%SmurtyNocache:{$_template->properties['nocache_hash']}%%\*/!", '', $cache_parts[0][$curr_idx]);
                    }
                }
                if (!$no_output_filter && !$_template->has_nocache_code && (isset($this->smurty->autoload_filters['output']) || isset($this->smurty->registered_filters['output']))) {
                    $output = Smurty_Internal_Filter_Handler::runFilter('output', $output, $_template);
                }
                // rendering (must be done before writing cache file because of {function} nocache handling)
                /** @var Smurty_Internal_Template $_smurty_tpl
                 * used in evaluated code
                 */
                $_smurty_tpl = $_template;
                try {
                    ob_start();
                    eval("?>" . $output);
                    $_output = ob_get_clean();
                }
                catch (Exception $e) {
                    ob_get_clean();
                    throw $e;
                }
                // write cache file content
                $_template->writeCachedContent($output);
                if ($this->smurty->debugging) {
                    Smurty_Internal_Debug::end_cache($_template);
                }
            } else {
                // var_dump('renderTemplate', $_template->has_nocache_code, $_template->template_resource, $_template->properties['nocache_hash'], $_template->parent->properties['nocache_hash'], $_output);
                if (!empty($_template->properties['nocache_hash']) && !empty($_template->parent->properties['nocache_hash'])) {
                    // replace nocache_hash
                    $_output = str_replace("{$_template->properties['nocache_hash']}", $_template->parent->properties['nocache_hash'], $_output);
                    $_template->parent->has_nocache_code = $_template->parent->has_nocache_code || $_template->has_nocache_code;
                }
            }
        } else {
            if ($this->smurty->debugging) {
                Smurty_Internal_Debug::start_cache($_template);
            }
            try {
                ob_start();
                array_unshift($_template->_capture_stack, array());
                //
                // render cached template
                //
                $_template->properties['unifunc']($_template);
                // any unclosed {capture} tags ?
                if (isset($_template->_capture_stack[0][0])) {
                    $_template->capture_error();
                }
                array_shift($_template->_capture_stack);
                $_output = ob_get_clean();
            }
            catch (Exception $e) {
                ob_get_clean();
                throw $e;
            }
            if ($this->smurty->debugging) {
                Smurty_Internal_Debug::end_cache($_template);
            }
        }
        if ((!$this->caching || $_template->has_nocache_code || $_template->source->recompiled) && !$no_output_filter && (isset($this->smurty->autoload_filters['output']) || isset($this->smurty->registered_filters['output']))) {
            $_output = Smurty_Internal_Filter_Handler::runFilter('output', $_output, $_template);
        }
        if (isset($this->error_reporting)) {
            error_reporting($_smurty_old_error_level);
        }
        // display or fetch
        if ($display) {
            if ($this->caching && $this->cache_modified_check) {
                $_isCached = $_template->isCached() && !$_template->has_nocache_code;
                $_last_modified_date = @substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 0, strpos($_SERVER['HTTP_IF_MODIFIED_SINCE'], 'GMT') + 3);
                if ($_isCached && $_template->cached->timestamp <= strtotime($_last_modified_date)) {
                    switch (PHP_SAPI) {
                        case 'cgi': // php-cgi < 5.3
                        case 'cgi-fcgi': // php-cgi >= 5.3
                        case 'fpm-fcgi': // php-fpm >= 5.3.3
                            header('Status: 304 Not Modified');
                            break;

                        case 'cli':
                            if ( /* ^phpunit */
                            !empty($_SERVER['SMURTY_PHPUNIT_DISABLE_HEADERS']) /* phpunit$ */
                            ) {
                                $_SERVER['SMURTY_PHPUNIT_HEADERS'][] = '304 Not Modified';
                            }
                            break;

                        default:
                            header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
                            break;
                    }
                } else {
                    switch (PHP_SAPI) {
                        case 'cli':
                            if ( /* ^phpunit */
                            !empty($_SERVER['SMURTY_PHPUNIT_DISABLE_HEADERS']) /* phpunit$ */
                            ) {
                                $_SERVER['SMURTY_PHPUNIT_HEADERS'][] = 'Last-Modified: ' . gmdate('D, d M Y H:i:s', $_template->cached->timestamp) . ' GMT';
                            }
                            break;

                        default:
                            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $_template->cached->timestamp) . ' GMT');
                            break;
                    }
                    echo $_output;
                }
            } else {
                echo $_output;
            }
            // debug output
            if ($this->smurty->debugging) {
                Smurty_Internal_Debug::display_debug($_template);
            }
            if ($merge_tpl_vars) {
                // restore local variables
                $_template->tpl_vars = $save_tpl_vars;
                $_template->config_vars = $save_config_vars;
            }

            return;
        } else {
            if ($merge_tpl_vars) {
                // restore local variables
                $_template->tpl_vars = $save_tpl_vars;
                $_template->config_vars = $save_config_vars;
            }
            // return fetched content
            return $_output;
        }
    }

    /**
     * displays a hiweb_tpl template
     *
     * @param string $template   the resource handle of the template file or template object
     * @param mixed  $cache_id   cache id to be used with this template
     * @param mixed  $compile_id compile id to be used with this template
     * @param object $parent     next higher level of hiweb_tpl variables
     */
    public function display($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
        // display template
        $this->fetch($template, $cache_id, $compile_id, $parent, true);
    }

    /**
     * test if cache is valid
     *
     * @param  string|object $template   the resource handle of the template file or template object
     * @param  mixed         $cache_id   cache id to be used with this template
     * @param  mixed         $compile_id compile id to be used with this template
     * @param  object        $parent     next higher level of hiweb_tpl variables
     *
     * @return boolean       cache status
     */
    public function isCached($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
        if ($template === null && $this instanceof $this->template_class) {
            return $this->cached->valid;
        }
        if (!($template instanceof $this->template_class)) {
            if ($parent === null) {
                $parent = $this;
            }
            $template = $this->smurty->createTemplate($template, $cache_id, $compile_id, $parent, false);
        }
        // return cache status of template
        return $template->cached->valid;
    }

    /**
     * creates a data object
     *
     * @param object $parent next higher level of hiweb_tpl variables
     *
     * @returns Smurty_Data data object
     */
    public function createData($parent = null)
    {
        return new Smurty_Data($parent, $this);
    }

    /**
     * Registers plugin to be used in templates
     *
     * @param  string   $type       plugin type
     * @param  string   $tag        name of template tag
     * @param  callback $callback   PHP callback to register
     * @param  boolean  $cacheable  if true (default) this fuction is cachable
     * @param  array    $cache_attr caching attributes if any
     *
     * @return Smurty_Internal_TemplateBase current Smurty_Internal_Templatebase (or hiweb_tpl or Smurty_Internal_Template) instance for chaining
     * @throws SmurtyException              when the plugin tag is invalid
     */
    public function registerPlugin($type, $tag, $callback, $cacheable = true, $cache_attr = null) {
        if (isset($this->smurty->registered_plugins[$type][$tag])) {
            throw new SmurtyException("Plugin tag \"{$tag}\" already registered");
        } elseif (!is_callable($callback)) {
            throw new SmurtyException("Plugin \"{$tag}\" not callable");
        } else {
            $this->smurty->registered_plugins[$type][$tag] = array($callback, (bool) $cacheable, (array) $cache_attr);
        }

        return $this;
    }

    /**
     * Unregister Plugin
     *
     * @param  string $type of plugin
     * @param  string $tag  name of plugin
     *
     * @return Smurty_Internal_TemplateBase current Smurty_Internal_Templatebase (or hiweb_tpl or Smurty_Internal_Template) instance for chaining
     */
    public function unregisterPlugin($type, $tag)
    {
        if (isset($this->smurty->registered_plugins[$type][$tag])) {
            unset($this->smurty->registered_plugins[$type][$tag]);
        }

        return $this;
    }

    /**
     * Registers a resource to fetch a template
     *
     * @param  string                $type     name of resource type
     * @param  Smurty_Resource|array $callback or instance of Smurty_Resource, or array of callbacks to handle resource (deprecated)
     *
     * @return Smurty_Internal_TemplateBase current Smurty_Internal_Templatebase (or hiweb_tpl or Smurty_Internal_Template) instance for chaining
     */
    public function registerResource($type, $callback)
    {
        $this->smurty->registered_resources[$type] = $callback instanceof Smurty_Resource ? $callback : array($callback, false);

        return $this;
    }

    /**
     * Unregisters a resource
     *
     * @param  string $type name of resource type
     *
     * @return Smurty_Internal_TemplateBase current Smurty_Internal_Templatebase (or hiweb_tpl or Smurty_Internal_Template) instance for chaining
     */
    public function unregisterResource($type)
    {
        if (isset($this->smurty->registered_resources[$type])) {
            unset($this->smurty->registered_resources[$type]);
        }

        return $this;
    }

    /**
     * Registers a cache resource to cache a template's output
     *
     * @param  string               $type     name of cache resource type
     * @param  Smurty_CacheResource $callback instance of Smurty_CacheResource to handle output caching
     *
     * @return Smurty_Internal_TemplateBase current Smurty_Internal_Templatebase (or hiweb_tpl or Smurty_Internal_Template) instance for chaining
     */
    public function registerCacheResource($type, Smurty_CacheResource $callback)
    {
        $this->smurty->registered_cache_resources[$type] = $callback;

        return $this;
    }

    /**
     * Unregisters a cache resource
     *
     * @param  string $type name of cache resource type
     *
     * @return Smurty_Internal_TemplateBase current Smurty_Internal_Templatebase (or hiweb_tpl or Smurty_Internal_Template) instance for chaining
     */
    public function unregisterCacheResource($type)
    {
        if (isset($this->smurty->registered_cache_resources[$type])) {
            unset($this->smurty->registered_cache_resources[$type]);
        }

        return $this;
    }

    /**
     * Registers object to be used in templates
     *
     * @param          $object_name
     * @param  object  $object_impl   the referenced PHP object to register
     * @param  array   $allowed       list of allowed methods (empty = all)
     * @param  boolean $smurty_args   smurty argument format, else traditional
     * @param  array   $block_methods list of block-methods
     *
     * @throws SmurtyException
     * @return Smurty_Internal_TemplateBase current Smurty_Internal_Templatebase (or hiweb_tpl or Smurty_Internal_Template) instance for chaining
     */
    public function registerObject($object_name, $object_impl, $allowed = array(), $smurty_args = true, $block_methods = array())
    {
        // test if allowed methods callable
        if (!empty($allowed)) {
            foreach ((array) $allowed as $method) {
                if (!is_callable(array($object_impl, $method)) && !property_exists($object_impl, $method)) {
                    throw new SmurtyException("Undefined method or property '$method' in registered object");
                }
            }
        }
        // test if block methods callable
        if (!empty($block_methods)) {
            foreach ((array) $block_methods as $method) {
                if (!is_callable(array($object_impl, $method))) {
                    throw new SmurtyException("Undefined method '$method' in registered object");
                }
            }
        }
        // register the object
        $this->smurty->registered_objects[$object_name] =
            array($object_impl, (array) $allowed, (boolean) $smurty_args, (array) $block_methods);

        return $this;
    }

    /**
     * return a reference to a registered object
     *
     * @param  string $name object name
     *
     * @return object
     * @throws SmurtyException if no such object is found
     */
    public function getRegisteredObject($name)
    {
        if (!isset($this->smurty->registered_objects[$name])) {
            throw new SmurtyException("'$name' is not a registered object");
        }
        if (!is_object($this->smurty->registered_objects[$name][0])) {
            throw new SmurtyException("registered '$name' is not an object");
        }

        return $this->smurty->registered_objects[$name][0];
    }

    /**
     * unregister an object
     *
     * @param  string $name object name
     *
     * @return Smurty_Internal_TemplateBase current Smurty_Internal_Templatebase (or hiweb_tpl or Smurty_Internal_Template) instance for chaining
     */
    public function unregisterObject($name)
    {
        if (isset($this->smurty->registered_objects[$name])) {
            unset($this->smurty->registered_objects[$name]);
        }

        return $this;
    }

    /**
     * Registers static classes to be used in templates
     *
     * @param         $class_name
     * @param  string $class_impl the referenced PHP class to register
     *
     * @throws SmurtyException
     * @return Smurty_Internal_TemplateBase current Smurty_Internal_Templatebase (or hiweb_tpl or Smurty_Internal_Template) instance for chaining
     */
    public function registerClass($class_name, $class_impl)
    {
        // test if exists
        if (!class_exists($class_impl)) {
            throw new SmurtyException("Undefined class '$class_impl' in register template class");
        }
        // register the class
        $this->smurty->registered_classes[$class_name] = $class_impl;

        return $this;
    }

    /**
     * Registers a default plugin handler
     *
     * @param  callable $callback class/method name
     *
     * @return Smurty_Internal_TemplateBase current Smurty_Internal_Templatebase (or hiweb_tpl or Smurty_Internal_Template) instance for chaining
     * @throws SmurtyException              if $callback is not callable
     */
    public function registerDefaultPluginHandler($callback)
    {
        if (is_callable($callback)) {
            $this->smurty->default_plugin_handler_func = $callback;
        } else {
            throw new SmurtyException("Default plugin handler '$callback' not callable");
        }

        return $this;
    }

    /**
     * Registers a default template handler
     *
     * @param  callable $callback class/method name
     *
     * @return Smurty_Internal_TemplateBase current Smurty_Internal_Templatebase (or hiweb_tpl or Smurty_Internal_Template) instance for chaining
     * @throws SmurtyException              if $callback is not callable
     */
    public function registerDefaultTemplateHandler($callback)
    {
        if (is_callable($callback)) {
            $this->smurty->default_template_handler_func = $callback;
        } else {
            throw new SmurtyException("Default template handler '$callback' not callable");
        }

        return $this;
    }

    /**
     * Registers a default template handler
     *
     * @param  callable $callback class/method name
     *
     * @return Smurty_Internal_TemplateBase current Smurty_Internal_Templatebase (or hiweb_tpl or Smurty_Internal_Template) instance for chaining
     * @throws SmurtyException              if $callback is not callable
     */
    public function registerDefaultConfigHandler($callback)
    {
        if (is_callable($callback)) {
            $this->smurty->default_config_handler_func = $callback;
        } else {
            throw new SmurtyException("Default config handler '$callback' not callable");
        }

        return $this;
    }

    /**
     * Registers a filter function
     *
     * @param  string   $type filter type
     * @param  callback $callback
     *
     * @return Smurty_Internal_TemplateBase current Smurty_Internal_Templatebase (or hiweb_tpl or Smurty_Internal_Template) instance for chaining
     */
    public function registerFilter($type, $callback)
    {
        $this->smurty->registered_filters[$type][$this->_get_filter_name($callback)] = $callback;

        return $this;
    }

    /**
     * Unregisters a filter function
     *
     * @param  string   $type filter type
     * @param  callback $callback
     *
     * @return Smurty_Internal_TemplateBase current Smurty_Internal_Templatebase (or hiweb_tpl or Smurty_Internal_Template) instance for chaining
     */
    public function unregisterFilter($type, $callback)
    {
        $name = $this->_get_filter_name($callback);
        if (isset($this->smurty->registered_filters[$type][$name])) {
            unset($this->smurty->registered_filters[$type][$name]);
        }

        return $this;
    }

    /**
     * Return internal filter name
     *
     * @param  callback $function_name
     *
     * @return string   internal filter name
     */
    public function _get_filter_name($function_name)
    {
        if (is_array($function_name)) {
            $_class_name = (is_object($function_name[0]) ?
                get_class($function_name[0]) : $function_name[0]);

            return $_class_name . '_' . $function_name[1];
        } else {
            return $function_name;
        }
    }

    /**
     * load a filter of specified type and name
     *
     * @param  string $type filter type
     * @param  string $name filter name
     *
     * @throws SmurtyException if filter could not be loaded
     */
    public function loadFilter($type, $name)
    {
        $_plugin = "smurty_{$type}filter_{$name}";
        $_filter_name = $_plugin;
        if ($this->smurty->loadPlugin($_plugin)) {
            if (class_exists($_plugin, false)) {
                $_plugin = array($_plugin, 'execute');
            }
            if (is_callable($_plugin)) {
                $this->smurty->registered_filters[$type][$_filter_name] = $_plugin;

                return true;
            }
        }
        throw new SmurtyException("{$type}filter \"{$name}\" not callable");
    }

    /**
     * unload a filter of specified type and name
     *
     * @param  string $type filter type
     * @param  string $name filter name
     *
     * @return Smurty_Internal_TemplateBase current Smurty_Internal_Templatebase (or hiweb_tpl or Smurty_Internal_Template) instance for chaining
     */
    public function unloadFilter($type, $name)
    {
        $_filter_name = "smurty_{$type}filter_{$name}";
        if (isset($this->smurty->registered_filters[$type][$_filter_name])) {
            unset ($this->smurty->registered_filters[$type][$_filter_name]);
        }

        return $this;
    }

    /**
     * preg_replace callback to convert camelcase getter/setter to underscore property names
     *
     * @param  string $match match string
     *
     * @return string replacemant
     */
    private function replaceCamelcase($match)
    {
        return "_" . strtolower($match[1]);
    }

    /**
     * Handle unknown class methods
     *
     * @param string $name unknown method-name
     * @param array  $args argument array
     *
     * @throws SmurtyException
     */
    public function __call($name, $args)
    {
        static $_prefixes = array('set' => true, 'get' => true);
        static $_resolved_property_name = array();
        static $_resolved_property_source = array();

        // method of hiweb_tpl object?
        if (method_exists($this->smurty, $name)) {
            return call_user_func_array(array($this->smurty, $name), $args);
        }
        // see if this is a set/get for a property
        $first3 = strtolower(substr($name, 0, 3));
        if (isset($_prefixes[$first3]) && isset($name[3]) && $name[3] !== '_') {
            if (isset($_resolved_property_name[$name])) {
                $property_name = $_resolved_property_name[$name];
            } else {
                // try to keep case correct for future PHP 6.0 case-sensitive class methods
                // lcfirst() not available < PHP 5.3.0, so improvise
                $property_name = strtolower(substr($name, 3, 1)) . substr($name, 4);
                // convert camel case to underscored name
                $property_name = preg_replace_callback('/([A-Z])/', array($this, 'replaceCamelcase'), $property_name);
                $_resolved_property_name[$name] = $property_name;
            }
            if (isset($_resolved_property_source[$property_name])) {
                $_is_this = $_resolved_property_source[$property_name];
            } else {
                $_is_this = null;
                if (property_exists($this, $property_name)) {
                    $_is_this = true;
                } elseif (property_exists($this->smurty, $property_name)) {
                    $_is_this = false;
                }
                $_resolved_property_source[$property_name] = $_is_this;
            }
            if ($_is_this) {
                if ($first3 == 'get') {
                    return $this->$property_name;
                } else {
                    return $this->$property_name = $args[0];
                }
            } elseif ($_is_this === false) {
                if ($first3 == 'get') {
                    return $this->smurty->$property_name;
                } else {
                    return $this->smurty->$property_name = $args[0];
                }
            } else {
                throw new SmurtyException("property '$property_name' does not exist.");
           }
        }
        if ($name == 'hiweb_tpl') {
            throw new SmurtyException("PHP5 requires you to call __construct() instead of hiweb_tpl()");
        }
        // must be unknown
        throw new SmurtyException("Call of unknown method '$name'.");
    }
}

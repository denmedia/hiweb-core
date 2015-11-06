<?php
/**
 * hiweb_tpl Internal Plugin Config
 *
 * @package    hiweb_tpl
 * @subpackage Config
 * @author     Uwe Tews
 */

/**
 * hiweb_tpl Internal Plugin Config
 * Main class for config variables
 *
 * @package    hiweb_tpl
 * @subpackage Config
 * @ignore
 */
class Smurty_Internal_Config
{
    /**
     * hiweb_tpl instance
     *
     * @var hiweb_tpl object
     */
    public $smurty = null;
    /**
     * Object of config var storage
     *
     * @var object
     */
    public $data = null;
    /**
     * Config resource
     *
     * @var string
     */
    public $config_resource = null;
    /**
     * Compiled config file
     *
     * @var string
     */
    public $compiled_config = null;
    /**
     * filepath of compiled config file
     *
     * @var string
     */
    public $compiled_filepath = null;
    /**
     * Filemtime of compiled config Filemtime
     *
     * @var int
     */
    public $compiled_timestamp = null;
    /**
     * flag if compiled config file is invalid and must be (re)compiled
     *
     * @var bool
     */
    public $mustCompile = null;
    /**
     * Config file compiler object
     *
     * @var Smurty_Internal_Config_File_Compiler object
     */
    public $compiler_object = null;

    /**
     * Constructor of config file object
     *
     * @param string $config_resource config file resource name
     * @param hiweb_tpl $smurty          hiweb_tpl instance
     * @param object $data            object for config vars storage
     */
    public function __construct($config_resource, $smurty, $data = null)
    {
        $this->data = $data;
        $this->smurty = $smurty;
        $this->config_resource = $config_resource;
    }

    /**
     * Returns the compiled  filepath
     *
     * @return string the compiled filepath
     */
    public function getCompiledFilepath()
    {
        return $this->compiled_filepath === null ?
            ($this->compiled_filepath = $this->buildCompiledFilepath()) :
            $this->compiled_filepath;
    }

    /**
     * Get file path.
     *
     * @return string
     */
    public function buildCompiledFilepath()
    {
        $_compile_id = isset($this->smurty->compile_id) ? preg_replace('![^\w\|]+!', '_', $this->smurty->compile_id) : null;
        $_flag = (int) $this->smurty->config_read_hidden + (int) $this->smurty->config_booleanize * 2
            + (int) $this->smurty->config_overwrite * 4;
        $_filepath = sha1(realpath($this->source->filepath) . $_flag);
        // if use_sub_dirs, break file into directories
        if ($this->smurty->use_sub_dirs) {
            $_filepath = substr($_filepath, 0, 2) . DIR_SEPARATOR
                . substr($_filepath, 2, 2) . DIR_SEPARATOR
                . substr($_filepath, 4, 2) . DIR_SEPARATOR
                . $_filepath;
        }
        $_compile_dir_sep = $this->smurty->use_sub_dirs ? DIR_SEPARATOR : '^';
        if (isset($_compile_id)) {
            $_filepath = $_compile_id . $_compile_dir_sep . $_filepath;
        }
        $_compile_dir = $this->smurty->getCompileDir();

        return $_compile_dir . $_filepath . '.' . basename($this->source->name) . '.config' . '.php';
    }

    /**
     * Returns the timestamp of the compiled file
     *
     * @return integer the file timestamp
     */
    public function getCompiledTimestamp()
    {
        return $this->compiled_timestamp === null
            ? ($this->compiled_timestamp = (file_exists($this->getCompiledFilepath())) ? filemtime($this->getCompiledFilepath()) : false)
            : $this->compiled_timestamp;
    }

    /**
     * Returns if the current config file must be compiled
     * It does compare the timestamps of config source and the compiled config and checks the force compile configuration
     *
     * @return boolean true if the file must be compiled
     */
    public function mustCompile()
    {
        return $this->mustCompile === null ?
            $this->mustCompile = ($this->smurty->force_compile || $this->getCompiledTimestamp() === false || $this->smurty->compile_check && $this->getCompiledTimestamp() < $this->source->timestamp) :
            $this->mustCompile;
    }

    /**
     * Returns the compiled config file
     * It checks if the config file must be compiled or just read the compiled version
     *
     * @return string the compiled config file
     */
    public function getCompiledConfig()
    {
        if ($this->compiled_config === null) {
            // see if template needs compiling.
            if ($this->mustCompile()) {
                $this->compileConfigSource();
            } else {
                $this->compiled_config = file_get_contents($this->getCompiledFilepath());
            }
        }

        return $this->compiled_config;
    }

    /**
     * Compiles the config files
     *
     * @throws Exception
     */
    public function compileConfigSource()
    {
        // compile template
        if (!is_object($this->compiler_object)) {
            // load compiler
            $this->compiler_object = new Smurty_Internal_Config_File_Compiler($this->smurty);
        }
        // compile locking
        if ($this->smurty->compile_locking) {
            if ($saved_timestamp = $this->getCompiledTimestamp()) {
                touch($this->getCompiledFilepath());
            }
        }
        // call compiler
        try {
            $this->compiler_object->compileSource($this);
        }
        catch (Exception $e) {
            // restore old timestamp in case of error
            if ($this->smurty->compile_locking && $saved_timestamp) {
                touch($this->getCompiledFilepath(), $saved_timestamp);
            }
            throw $e;
        }
        // compiling succeeded
        // write compiled template
        Smurty_Internal_Write_File::writeFile($this->getCompiledFilepath(), $this->getCompiledConfig(), $this->smurty);
    }

    /**
     * load config variables
     *
     * @param mixed         $sections array of section names, single section or null
     * @param string $scope    global,parent or local
     *
     * @throws Exception
     */
    public function loadConfigVars($sections = null, $scope = 'local')
    {
        if ($this->data instanceof Smurty_Internal_Template) {
            $this->data->properties['file_dependency'][sha1($this->source->filepath)] = array($this->source->filepath, $this->source->timestamp, 'file');
        }
        if ($this->mustCompile()) {
            $this->compileConfigSource();
        }
        // pointer to scope
        if ($scope == 'local') {
            $scope_ptr = $this->data;
        } elseif ($scope == 'parent') {
            if (isset($this->data->parent)) {
                $scope_ptr = $this->data->parent;
            } else {
                $scope_ptr = $this->data;
            }
        } elseif ($scope == 'root' || $scope == 'global') {
            $scope_ptr = $this->data;
            while (isset($scope_ptr->parent)) {
                $scope_ptr = $scope_ptr->parent;
            }
        }
        $_config_vars = array();
        include($this->getCompiledFilepath());
        // copy global config vars
        foreach ($_config_vars['vars'] as $variable => $value) {
            if ($this->smurty->config_overwrite || !isset($scope_ptr->config_vars[$variable])) {
                $scope_ptr->config_vars[$variable] = $value;
            } else {
                $scope_ptr->config_vars[$variable] = array_merge((array) $scope_ptr->config_vars[$variable], (array) $value);
            }
        }
        // scan sections
        if (!empty($sections)) {
            foreach ((array) $sections as $this_section) {
                if (isset($_config_vars['sections'][$this_section])) {
                    foreach ($_config_vars['sections'][$this_section]['vars'] as $variable => $value) {
                        if ($this->smurty->config_overwrite || !isset($scope_ptr->config_vars[$variable])) {
                            $scope_ptr->config_vars[$variable] = $value;
                        } else {
                            $scope_ptr->config_vars[$variable] = array_merge((array) $scope_ptr->config_vars[$variable], (array) $value);
                        }
                    }
                }
            }
        }
    }

    /**
     * set hiweb_tpl property in template context
     *
     * @param  string $property_name property name
     * @param  mixed  $value         value
     *
     * @throws SmurtyException if $property_name is not valid
     */
    public function __set($property_name, $value)
    {
        switch ($property_name) {
            case 'source':
            case 'compiled':
                $this->$property_name = $value;

                return;
        }

        throw new SmurtyException("invalid config property '$property_name'.");
    }

    /**
     * get hiweb_tpl property in template context
     *
     * @param  string $property_name property name
     *
     * @return \Smurty_Config_Source|\Smurty_Template_Compiled
     * @throws SmurtyException if $property_name is not valid
     */
    public function __get($property_name)
    {
        switch ($property_name) {
            case 'source':
                if (empty($this->config_resource)) {
                    throw new SmurtyException("Unable to parse resource name \"{$this->config_resource}\"");
                }
                $this->source = Smurty_Resource::config($this);

                return $this->source;

            case 'compiled':
                $this->compiled = $this->source->getCompiled($this);

                return $this->compiled;
        }

        throw new SmurtyException("config attribute '$property_name' does not exist.");
    }
}

<?php
/**
 * hiweb_tpl Internal Plugin Data
 * This file contains the basic classes and methods for template and variable creation
 *
 * @package    hiweb_tpl
 * @subpackage Template
 * @author     Uwe Tews
 */

/**
 * Base class with template and variable methods
 *
 * @package    hiweb_tpl
 * @subpackage Template
 */
class Smurty_Internal_Data
{
    /**
     * name of class used for templates
     *
     * @var string
     */
    public $template_class = 'Smurty_Internal_Template';
    /**
     * template variables
     *
     * @var array
     */
    public $tpl_vars = array();
    /**
     * parent template (if any)
     *
     * @var Smurty_Internal_Template
     */
    public $parent = null;
    /**
     * configuration settings
     *
     * @var array
     */
    public $config_vars = array();

    /**
     * assigns a hiweb_tpl variable
     *
     * @param  array|string $tpl_var the template variable name(s)
     * @param  mixed        $value   the value to assign
     * @param  boolean      $nocache if true any output of this variable will be not cached
     *
     * @return Smurty_Internal_Data current Smurty_Internal_Data (or hiweb_tpl or Smurty_Internal_Template) instance for chaining
     */
    public function assign($tpl_var, $value = null, $nocache = false)
    {
        if (is_array($tpl_var)) {
            foreach ($tpl_var as $_key => $_val) {
                if ($_key != '') {
                    $this->tpl_vars[$_key] = new Smurty_variable($_val, $nocache);
                }
            }
        } else {
            if ($tpl_var != '') {
                $this->tpl_vars[$tpl_var] = new Smurty_variable($value, $nocache);
            }
        }

        return $this;
    }

    /**
     * assigns a global hiweb_tpl variable
     *
     * @param  string  $varname the global variable name
     * @param  mixed   $value   the value to assign
     * @param  boolean $nocache if true any output of this variable will be not cached
     *
     * @return Smurty_Internal_Data current Smurty_Internal_Data (or hiweb_tpl or Smurty_Internal_Template) instance for chaining
     */
    public function assignGlobal($varname, $value = null, $nocache = false)
    {
        if ($varname != '') {
            hiweb_tpl::$global_tpl_vars[$varname] = new Smurty_variable($value, $nocache);
            $ptr = $this;
            while ($ptr instanceof Smurty_Internal_Template) {
                $ptr->tpl_vars[$varname] = clone hiweb_tpl::$global_tpl_vars[$varname];
                $ptr = $ptr->parent;
            }
        }

        return $this;
    }

    /**
     * assigns values to template variables by reference
     *
     * @param string   $tpl_var the template variable name
     * @param          $value
     * @param  boolean $nocache if true any output of this variable will be not cached
     *
     * @return Smurty_Internal_Data current Smurty_Internal_Data (or hiweb_tpl or Smurty_Internal_Template) instance for chaining
     */
    public function assignByRef($tpl_var, &$value, $nocache = false)
    {
        if ($tpl_var != '') {
            $this->tpl_vars[$tpl_var] = new Smurty_variable(null, $nocache);
            $this->tpl_vars[$tpl_var]->value = & $value;
        }

        return $this;
    }

    /**
     * appends values to template variables
     *
     * @param  array|string $tpl_var the template variable name(s)
     * @param  mixed        $value   the value to append
     * @param  boolean      $merge   flag if array elements shall be merged
     * @param  boolean      $nocache if true any output of this variable will be not cached
     *
     * @return Smurty_Internal_Data current Smurty_Internal_Data (or hiweb_tpl or Smurty_Internal_Template) instance for chaining
     */
    public function append($tpl_var, $value = null, $merge = false, $nocache = false)
    {
        if (is_array($tpl_var)) {
            // $tpl_var is an array, ignore $value
            foreach ($tpl_var as $_key => $_val) {
                if ($_key != '') {
                    if (!isset($this->tpl_vars[$_key])) {
                        $tpl_var_inst = $this->getVariable($_key, null, true, false);
                        if ($tpl_var_inst instanceof Undefined_Smurty_Variable) {
                            $this->tpl_vars[$_key] = new Smurty_variable(null, $nocache);
                        } else {
                            $this->tpl_vars[$_key] = clone $tpl_var_inst;
                        }
                    }
                    if (!(is_array($this->tpl_vars[$_key]->value) || $this->tpl_vars[$_key]->value instanceof ArrayAccess)) {
                        settype($this->tpl_vars[$_key]->value, 'array');
                    }
                    if ($merge && is_array($_val)) {
                        foreach ($_val as $_mkey => $_mval) {
                            $this->tpl_vars[$_key]->value[$_mkey] = $_mval;
                        }
                    } else {
                        $this->tpl_vars[$_key]->value[] = $_val;
                    }
                }
            }
        } else {
            if ($tpl_var != '' && isset($value)) {
                if (!isset($this->tpl_vars[$tpl_var])) {
                    $tpl_var_inst = $this->getVariable($tpl_var, null, true, false);
                    if ($tpl_var_inst instanceof Undefined_Smurty_Variable) {
                        $this->tpl_vars[$tpl_var] = new Smurty_variable(null, $nocache);
                    } else {
                        $this->tpl_vars[$tpl_var] = clone $tpl_var_inst;
                    }
                }
                if (!(is_array($this->tpl_vars[$tpl_var]->value) || $this->tpl_vars[$tpl_var]->value instanceof ArrayAccess)) {
                    settype($this->tpl_vars[$tpl_var]->value, 'array');
                }
                if ($merge && is_array($value)) {
                    foreach ($value as $_mkey => $_mval) {
                        $this->tpl_vars[$tpl_var]->value[$_mkey] = $_mval;
                    }
                } else {
                    $this->tpl_vars[$tpl_var]->value[] = $value;
                }
            }
        }

        return $this;
    }

    /**
     * appends values to template variables by reference
     *
     * @param  string  $tpl_var the template variable name
     * @param  mixed   &$value  the referenced value to append
     * @param  boolean $merge   flag if array elements shall be merged
     *
     * @return Smurty_Internal_Data current Smurty_Internal_Data (or hiweb_tpl or Smurty_Internal_Template) instance for chaining
     */
    public function appendByRef($tpl_var, &$value, $merge = false)
    {
        if ($tpl_var != '' && isset($value)) {
            if (!isset($this->tpl_vars[$tpl_var])) {
                $this->tpl_vars[$tpl_var] = new Smurty_variable();
            }
            if (!is_array($this->tpl_vars[$tpl_var]->value)) {
                settype($this->tpl_vars[$tpl_var]->value, 'array');
            }
            if ($merge && is_array($value)) {
                foreach ($value as $_key => $_val) {
                    $this->tpl_vars[$tpl_var]->value[$_key] = & $value[$_key];
                }
            } else {
                $this->tpl_vars[$tpl_var]->value[] = & $value;
            }
        }

        return $this;
    }

    /**
     * Returns a single or all template variables
     *
     * @param  string  $varname        variable name or null
     * @param  object   $_ptr           optional pointer to data object
     * @param  boolean $search_parents include parent templates?
     *
     * @return string  variable value or or array of variables
     */
    public function getTemplateVars($varname = null, $_ptr = null, $search_parents = true)
    {
        if (isset($varname)) {
            $_var = $this->getVariable($varname, $_ptr, $search_parents, false);
            if (is_object($_var)) {
                return $_var->value;
            } else {
                return null;
            }
        } else {
            $_result = array();
            if ($_ptr === null) {
                $_ptr = $this;
            }
            while ($_ptr !== null) {
                foreach ($_ptr->tpl_vars AS $key => $var) {
                    if (!array_key_exists($key, $_result)) {
                        $_result[$key] = $var->value;
                    }
                }
                // not found, try at parent
                if ($search_parents) {
                    $_ptr = $_ptr->parent;
                } else {
                    $_ptr = null;
                }
            }
            if ($search_parents && isset(hiweb_tpl::$global_tpl_vars)) {
                foreach (hiweb_tpl::$global_tpl_vars AS $key => $var) {
                    if (!array_key_exists($key, $_result)) {
                        $_result[$key] = $var->value;
                    }
                }
            }

            return $_result;
        }
    }

    /**
     * clear the given assigned template variable.
     *
     * @param  string|array $tpl_var the template variable(s) to clear
     *
     * @return Smurty_Internal_Data current Smurty_Internal_Data (or hiweb_tpl or Smurty_Internal_Template) instance for chaining
     */
    public function clearAssign($tpl_var)
    {
        if (is_array($tpl_var)) {
            foreach ($tpl_var as $curr_var) {
                unset($this->tpl_vars[$curr_var]);
            }
        } else {
            unset($this->tpl_vars[$tpl_var]);
        }

        return $this;
    }

    /**
     * clear all the assigned template variables.
     *
     * @return Smurty_Internal_Data current Smurty_Internal_Data (or hiweb_tpl or Smurty_Internal_Template) instance for chaining
     */
    public function clearAllAssign()
    {
        $this->tpl_vars = array();

        return $this;
    }

    /**
     * load a config file, optionally load just selected sections
     *
     * @param  string $config_file filename
     * @param  mixed  $sections    array of section names, single section or null
     *
     * @return Smurty_Internal_Data current Smurty_Internal_Data (or hiweb_tpl or Smurty_Internal_Template) instance for chaining
     */
    public function configLoad($config_file, $sections = null)
    {
        // load Config class
        $config = new Smurty_Internal_Config($config_file, $this->smurty, $this);
        $config->loadConfigVars($sections);

        return $this;
    }

    /**
     * gets the object of a hiweb_tpl variable
     *
     * @param  string  $variable       the name of the hiweb_tpl variable
     * @param  object  $_ptr           optional pointer to data object
     * @param  boolean $search_parents search also in parent data
     * @param bool     $error_enable
     *
     * @return object  the object of the variable
     */
    public function getVariable($variable, $_ptr = null, $search_parents = true, $error_enable = true)
    {
        if ($_ptr === null) {
            $_ptr = $this;
        }
        while ($_ptr !== null) {
            if (isset($_ptr->tpl_vars[$variable])) {
                // found it, return it
                return $_ptr->tpl_vars[$variable];
            }
            // not found, try at parent
            if ($search_parents) {
                $_ptr = $_ptr->parent;
            } else {
                $_ptr = null;
            }
        }
        if (isset(hiweb_tpl::$global_tpl_vars[$variable])) {
            // found it, return it
            return hiweb_tpl::$global_tpl_vars[$variable];
        }
        if ($this->smurty->error_unassigned && $error_enable) {
            // force a notice
            $x = $$variable;
        }

        return new Undefined_Smurty_Variable;
    }

    /**
     * gets  a config variable
     *
     * @param  string $variable the name of the config variable
     * @param bool    $error_enable
     *
     * @return mixed  the value of the config variable
     */
    public function getConfigVariable($variable, $error_enable = true)
    {
        $_ptr = $this;
        while ($_ptr !== null) {
            if (isset($_ptr->config_vars[$variable])) {
                // found it, return it
                return $_ptr->config_vars[$variable];
            }
            // not found, try at parent
            $_ptr = $_ptr->parent;
        }
        if ($this->smurty->error_unassigned && $error_enable) {
            // force a notice
            $x = $$variable;
        }

        return null;
    }

    /**
     * gets  a stream variable
     *
     * @param  string $variable the stream of the variable
     *
     * @throws SmurtyException
     * @return mixed  the value of the stream variable
     */
    public function getStreamVariable($variable)
    {
        $_result = '';
        $fp = fopen($variable, 'r+');
        if ($fp) {
            while (!feof($fp) && ($current_line = fgets($fp)) !== false) {
                $_result .= $current_line;
            }
            fclose($fp);

            return $_result;
        }

        if ($this->smurty->error_unassigned) {
            throw new SmurtyException('Undefined stream variable "' . $variable . '"');
        } else {
            return null;
        }
    }

    /**
     * Returns a single or all config variables
     *
     * @param  string $varname variable name or null
     * @param bool    $search_parents
     *
     * @return string variable value or or array of variables
     */
    public function getConfigVars($varname = null, $search_parents = true)
    {
        $_ptr = $this;
        $var_array = array();
        while ($_ptr !== null) {
            if (isset($varname)) {
                if (isset($_ptr->config_vars[$varname])) {
                    return $_ptr->config_vars[$varname];
                }
            } else {
                $var_array = array_merge($_ptr->config_vars, $var_array);
            }
            // not found, try at parent
            if ($search_parents) {
                $_ptr = $_ptr->parent;
            } else {
                $_ptr = null;
            }
        }
        if (isset($varname)) {
            return '';
        } else {
            return $var_array;
        }
    }

    /**
     * Deassigns a single or all config variables
     *
     * @param  string $varname variable name or null
     *
     * @return Smurty_Internal_Data current Smurty_Internal_Data (or hiweb_tpl or Smurty_Internal_Template) instance for chaining
     */
    public function clearConfig($varname = null)
    {
        if (isset($varname)) {
            unset($this->config_vars[$varname]);
        } else {
            $this->config_vars = array();
        }

        return $this;
    }
}

/**
 * class for the hiweb_tpl data object
 * The hiweb_tpl data object will hold hiweb_tpl variables in the current scope
 *
 * @package    hiweb_tpl
 * @subpackage Template
 */
class Smurty_Data extends Smurty_Internal_Data
{
    /**
     * hiweb_tpl object
     *
     * @var hiweb_tpl
     */
    public $smurty = null;

    /**
     * create hiweb_tpl data object
     *
     * @param hiweb_tpl|array $_parent parent template
     * @param hiweb_tpl|Smurty_Internal_Template       $smurty  global smurty instance
     *
     * @throws SmurtyException
     */
    public function __construct($_parent = null, $smurty = null)
    {
        $this->smurty = $smurty;
        if (is_object($_parent)) {
            // when object set up back pointer
            $this->parent = $_parent;
        } elseif (is_array($_parent)) {
            // set up variable values
            foreach ($_parent as $_key => $_val) {
                $this->tpl_vars[$_key] = new Smurty_variable($_val);
            }
        } elseif ($_parent != null) {
            throw new SmurtyException("Wrong type for template variables");
        }
    }
}

/**
 * class for the hiweb_tpl variable object
 * This class defines the hiweb_tpl variable object
 *
 * @package    hiweb_tpl
 * @subpackage Template
 */
class Smurty_Variable
{
    /**
     * template variable
     *
     * @var mixed
     */
    public $value = null;
    /**
     * if true any output of this variable will be not cached
     *
     * @var boolean
     */
    public $nocache = false;
    /**
     * the scope the variable will have  (local,parent or root)
     *
     * @var int
     */
    public $scope = hiweb_tpl::SCOPE_LOCAL;

    /**
     * create hiweb_tpl variable object
     *
     * @param mixed   $value   the value to assign
     * @param boolean $nocache if true any output of this variable will be not cached
     * @param int     $scope   the scope the variable will have  (local,parent or root)
     */
    public function __construct($value = null, $nocache = false, $scope = hiweb_tpl::SCOPE_LOCAL)
    {
        $this->value = $value;
        $this->nocache = $nocache;
        $this->scope = $scope;
    }

    /**
     * <<magic>> String conversion
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->value;
    }
}

/**
 * class for undefined variable object
 * This class defines an object for undefined variable handling
 *
 * @package    hiweb_tpl
 * @subpackage Template
 */
class Undefined_Smurty_Variable
{
    /**
     * Returns FALSE for 'nocache' and NULL otherwise.
     *
     * @param  string $name
     *
     * @return bool
     */
    public function __get($name)
    {
        if ($name == 'nocache') {
            return false;
        } else {
            return null;
        }
    }

    /**
     * Always returns an empty string.
     *
     * @return string
     */
    public function __toString()
    {
        return "";
    }
}

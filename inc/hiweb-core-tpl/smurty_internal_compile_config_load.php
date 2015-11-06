<?php
/**
 * hiweb_tpl Internal Plugin Compile Config Load
 * Compiles the {config load} tag
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 * @author     Uwe Tews
 */

/**
 * hiweb_tpl Internal Plugin Compile Config Load Class
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 */
class Smurty_Internal_Compile_Config_Load extends Smurty_Internal_CompileBase
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smurty_Internal_CompileBase
     */
    public $required_attributes = array('file');
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smurty_Internal_CompileBase
     */
    public $shorttag_order = array('file', 'section');
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smurty_Internal_CompileBase
     */
    public $optional_attributes = array('section', 'scope');

    /**
     * Compiles code for the {config_load} tag
     *
     * @param  array  $args     array with attributes from parser
     * @param  object $compiler compiler object
     *
     * @return string compiled code
     */
    public function compile($args, $compiler)
    {
        static $_is_legal_scope = array('local' => true, 'parent' => true, 'root' => true, 'global' => true);
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        if ($_attr['nocache'] === true) {
            $compiler->trigger_template_error('nocache option not allowed', $compiler->lex->taglineno);
        }

        // save possible attributes
        $conf_file = $_attr['file'];
        if (isset($_attr['section'])) {
            $section = $_attr['section'];
        } else {
            $section = 'null';
        }
        $scope = 'local';
        // scope setup
        if (isset($_attr['scope'])) {
            $_attr['scope'] = trim($_attr['scope'], "'\"");
            if (isset($_is_legal_scope[$_attr['scope']])) {
                $scope = $_attr['scope'];
            } else {
                $compiler->trigger_template_error('illegal value for "scope" attribute', $compiler->lex->taglineno);
            }
        }
        // create config object
        $_output = "<?php  \$_config = new Smurty_Internal_Config($conf_file, \$_smurty_tpl->smurty, \$_smurty_tpl);";
        $_output .= "\$_config->loadConfigVars($section, '$scope'); ?>";

        return $_output;
    }
}

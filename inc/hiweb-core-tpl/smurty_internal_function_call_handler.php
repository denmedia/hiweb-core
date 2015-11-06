<?php
/**
 * hiweb_tpl Internal Plugin Function Call Handler
 *
 * @package    hiweb_tpl
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */

/**
 * This class does call function defined with the {function} tag
 *
 * @package    hiweb_tpl
 * @subpackage PluginsInternal
 */
class Smurty_Internal_Function_Call_Handler
{
    /**
     * This function handles calls to template functions defined by {function}
     * It does create a PHP function at the first call
     *
     * @param string                   $_name     template function name
     * @param Smurty_Internal_Template $_template template object
     * @param array                    $_params   hiweb_tpl variables passed as call parameter
     * @param string                   $_hash     nocache hash value
     * @param bool                     $_nocache  nocache flag
     */
    public static function call($_name, Smurty_Internal_Template $_template, $_params, $_hash, $_nocache)
    {
        if ($_nocache) {
            $_function = "smurty_template_function_{$_name}_nocache";
        } else {
            $_function = "smurty_template_function_{$_hash}_{$_name}";
        }
        if (!is_callable($_function)) {
            $_code = "function {$_function}(\$_smurty_tpl,\$params) {
    \$saved_tpl_vars = \$_smurty_tpl->tpl_vars;
    foreach (\$_smurty_tpl->smurty->template_functions['{$_name}']['parameter'] as \$key => \$value) {\$_smurty_tpl->tpl_vars[\$key] = new Smurty_variable(\$value);};
    foreach (\$params as \$key => \$value) {\$_smurty_tpl->tpl_vars[\$key] = new Smurty_variable(\$value);}?>";
            if ($_nocache) {
                $_code .= preg_replace(array("!<\?php echo \\'/\*%%SmurtyNocache:{$_template->smurty->template_functions[$_name]['nocache_hash']}%%\*/|/\*/%%SmurtyNocache:{$_template->smurty->template_functions[$_name]['nocache_hash']}%%\*/\\';\?>!",
                                             "!\\\'!"), array('', "'"), $_template->smurty->template_functions[$_name]['compiled']);
                $_template->smurty->template_functions[$_name]['called_nocache'] = true;
            } else {
                $_code .= preg_replace("/{$_template->smurty->template_functions[$_name]['nocache_hash']}/", $_template->properties['nocache_hash'], $_template->smurty->template_functions[$_name]['compiled']);
            }
            $_code .= "<?php \$_smurty_tpl->tpl_vars = \$saved_tpl_vars;}";
            eval($_code);
        }
        $_function($_template, $_params);
    }
}

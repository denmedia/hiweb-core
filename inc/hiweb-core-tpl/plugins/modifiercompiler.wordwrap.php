<?php
/**
 * hiweb_tpl plugin
 *
 * @package    hiweb_tpl
 * @subpackage PluginsModifierCompiler
 */

/**
 * hiweb_tpl wordwrap modifier plugin
 * Type:     modifier<br>
 * Name:     wordwrap<br>
 * Purpose:  wrap a string of text at a given length
 *
 * @link   http://smurty.php.net/manual/en/language.modifier.wordwrap.php wordwrap (hiweb_tpl online manual)
 * @author Uwe Tews
 *
 * @param array $params parameters
 * @param       $compiler
 *
 * @return string with compiled code
 */
function smurty_modifiercompiler_wordwrap($params, $compiler)
{
    if (!isset($params[1])) {
        $params[1] = 80;
    }
    if (!isset($params[2])) {
        $params[2] = '"\n"';
    }
    if (!isset($params[3])) {
        $params[3] = 'false';
    }
    $function = 'wordwrap';
    if (hiweb_tpl::$_MBSTRING) {
        if ($compiler->template->caching && ($compiler->tag_nocache | $compiler->nocache)) {
            $compiler->template->required_plugins['nocache']['wordwrap']['modifier']['file'] = HIWEB_TPL_PLUGINS_DIR . 'shared.mb_wordwrap.php';
            $compiler->template->required_plugins['nocache']['wordwrap']['modifier']['function'] = 'smurty_mb_wordwrap';
        } else {
            $compiler->template->required_plugins['compiled']['wordwrap']['modifier']['file'] = HIWEB_TPL_PLUGINS_DIR . 'shared.mb_wordwrap.php';
            $compiler->template->required_plugins['compiled']['wordwrap']['modifier']['function'] = 'smurty_mb_wordwrap';
        }
        $function = 'smurty_mb_wordwrap';
    }

    return $function . '(' . $params[0] . ',' . $params[1] . ',' . $params[2] . ',' . $params[3] . ')';
}

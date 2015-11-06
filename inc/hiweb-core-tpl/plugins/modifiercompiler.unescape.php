<?php
/**
 * hiweb_tpl plugin
 *
 * @package    hiweb_tpl
 * @subpackage PluginsModifierCompiler
 */

/**
 * hiweb_tpl unescape modifier plugin
 * Type:     modifier<br>
 * Name:     unescape<br>
 * Purpose:  unescape html entities
 *
 * @author Rodney Rehm
 *
 * @param array $params parameters
 *
 * @return string with compiled code
 */
function smurty_modifiercompiler_unescape($params)
{
    if (!isset($params[1])) {
        $params[1] = 'html';
    }
    if (!isset($params[2])) {
        $params[2] = '\'' . addslashes(hiweb_tpl::$_CHARSET) . '\'';
    } else {
        $params[2] = "'" . $params[2] . "'";
    }

    switch (trim($params[1], '"\'')) {
        case 'entity':
        case 'htmlall':
            if (hiweb_tpl::$_MBSTRING) {
                return 'mb_convert_encoding(' . $params[0] . ', ' . $params[2] . ', \'HTML-ENTITIES\')';
            }

            return 'html_entity_decode(' . $params[0] . ', ENT_NOQUOTES, ' . $params[2] . ')';

        case 'html':
            return 'htmlspecialchars_decode(' . $params[0] . ', ENT_QUOTES)';

        case 'url':
            return 'rawurldecode(' . $params[0] . ')';

        default:
            return $params[0];
    }
}

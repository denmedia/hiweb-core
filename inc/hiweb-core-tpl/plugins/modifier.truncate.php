<?php
/**
 * hiweb_tpl plugin
 *
 * @package    hiweb_tpl
 * @subpackage PluginsModifier
 */

/**
 * hiweb_tpl truncate modifier plugin
 * Type:     modifier<br>
 * Name:     truncate<br>
 * Purpose:  Truncate a string to a certain length if necessary,
 *               optionally splitting in the middle of a word, and
 *               appending the $etc string or inserting $etc into the middle.
 *
 * @link   http://smurty.php.net/manual/en/language.modifier.truncate.php truncate (hiweb_tpl online manual)
 * @author Monte Ohrt <monte at ohrt dot com>
 *
 * @param string  $string      input string
 * @param integer $length      length of truncated text
 * @param string  $etc         end string
 * @param boolean $break_words truncate at word boundary
 * @param boolean $middle      truncate in the middle of text
 *
 * @return string truncated string
 */
function smurty_modifier_truncate($string, $length = 80, $etc = '...', $break_words = false, $middle = false)
{
    if ($length == 0) {
        return '';
    }

    if (hiweb_tpl::$_MBSTRING) {
        if (mb_strlen($string, hiweb_tpl::$_CHARSET) > $length) {
            $length -= min($length, mb_strlen($etc, hiweb_tpl::$_CHARSET));
            if (!$break_words && !$middle) {
                $string = preg_replace('/\s+?(\S+)?$/' . hiweb_tpl::$_UTF8_MODIFIER, '', mb_substr($string, 0, $length + 1, hiweb_tpl::$_CHARSET));
            }
            if (!$middle) {
                return mb_substr($string, 0, $length, hiweb_tpl::$_CHARSET) . $etc;
            }

            return mb_substr($string, 0, $length / 2, hiweb_tpl::$_CHARSET) . $etc . mb_substr($string, - $length / 2, $length, hiweb_tpl::$_CHARSET);
        }

        return $string;
    }

    // no MBString fallback
    if (isset($string[$length])) {
        $length -= min($length, strlen($etc));
        if (!$break_words && !$middle) {
            $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length + 1));
        }
        if (!$middle) {
            return substr($string, 0, $length) . $etc;
        }

        return substr($string, 0, $length / 2) . $etc . substr($string, - $length / 2);
    }

    return $string;
}

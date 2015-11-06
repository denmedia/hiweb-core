<?php
/**
 * hiweb_tpl plugin
 *
 * @package    hiweb_tpl
 * @subpackage PluginsModifier
 */

/**
 * hiweb_tpl date_format modifier plugin
 * Type:     modifier<br>
 * Name:     date_format<br>
 * Purpose:  format datestamps via strftime<br>
 * Input:<br>
 *          - string: input date string
 *          - format: strftime format for output
 *          - default_date: default date if $string is empty
 *
 * @link   http://www.smurty.net/manual/en/language.modifier.date.format.php date_format (hiweb_tpl online manual)
 * @author Monte Ohrt <monte at ohrt dot com>
 *
 * @param string $string       input date string
 * @param string $format       strftime format for output
 * @param string $default_date default date if $string is empty
 * @param string $formatter    either 'strftime' or 'auto'
 *
 * @return string |void
 * @uses   smurty_make_timestamp()
 */
function smurty_modifier_date_format($string, $format = null, $default_date = '', $formatter = 'auto')
{
    if ($format === null) {
        $format = hiweb_tpl::$_DATE_FORMAT;
    }
    /**
     * Include the {@link shared.make_timestamp.php} plugin
     */
    require_once( HIWEB_TPL_PLUGINS_DIR . 'shared.make_timestamp.php' );
    if ($string != '' && $string != '0000-00-00' && $string != '0000-00-00 00:00:00') {
        $timestamp = smurty_make_timestamp($string);
    } elseif ($default_date != '') {
        $timestamp = smurty_make_timestamp($default_date);
    } else {
        return;
    }
    if ($formatter == 'strftime' || ($formatter == 'auto' && strpos($format, '%') !== false)) {
        if (DIR_SEPARATOR == '\\') {
            $_win_from = array('%D', '%h', '%n', '%r', '%R', '%t', '%T');
            $_win_to = array('%m/%d/%y', '%b', "\n", '%I:%M:%S %p', '%H:%M', "\t", '%H:%M:%S');
            if (strpos($format, '%e') !== false) {
                $_win_from[] = '%e';
                $_win_to[] = sprintf('%\' 2d', date('j', $timestamp));
            }
            if (strpos($format, '%l') !== false) {
                $_win_from[] = '%l';
                $_win_to[] = sprintf('%\' 2d', date('h', $timestamp));
            }
            $format = str_replace($_win_from, $_win_to, $format);
        }

        return strftime($format, $timestamp);
    } else {
        return date($format, $timestamp);
    }
}

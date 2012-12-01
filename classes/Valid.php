<?php defined('SYSPATH') or die('No direct script access.');

/**
 * additional checks from http://www.maheshchari.com/60-validation-functions-with-php-2-part/
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category extra
 * @subpackage extra
 */
class Valid extends Kohana_Valid {

    /**
      * is given string is ascii format?
      *
      *Valid parameter
      *
      * We can’t display file specific characters here . just test with note pad text.
      *
      *Invalid parameter
      * We can’t display file specific characters here . just test with note pad text.
      * @param   string
      * @return  boolean
      */

      public static function ascii($val)
      {
          return !preg_match('/[^x00-x7F]/i', $val);
      }

      /**
      * Matches base64 enoding string
      * @param   string
      * @return  boolean
      */
      public static function base64($val)
      {
          return (bool)!preg_match('/[^a-zA-Z0-9/+=]/', $val);
      }

      /**
      * Check given sting has script tags
      *
      *Use: There is a chance to hackers to include javascript code in our Input form elements such as Text area ,Text element.It is best practice * *to avoid such hacking in E-commerce applications.
      * @param   string
      * @return  boolean
      */
      public static function jssafe($val)
      {
          return (bool)(!preg_match("/<script[^>]*>[srn]*(<!--)?|(-->)?[srn]*</script>/", $val));
      }

      /**
      * Checks given value again MAC address of the computer
      * @param   string   value
      * @return  boolean
      */
      public static function macaddress($val)
      {
          return (bool)preg_match('/^([0-9a-fA-F][0-9a-fA-F]:){5}([0-9a-fA-F][0-9a-fA-F])$/', $val);
      }

      /**
      * Checks that a field matches a v2 md5 string
      * @param   string
      * @return  boolean
      */
      public static function md5($val)
      {
          return (bool)preg_match("/[0-9a-f]{32}/i", $val);
      }

      /**
      * check given sring has multilines
      * @param   string
      * @return  boolean
      */
      public static function multiline($val)
      {
          return (bool)preg_match("/[nrt]+/", $val);
      }

      /**
      * Checks that given value matches following country pin codes.
      * at = austria
      * au = australia
      * ca = canada
      * de = german
      * ee = estonia
      * nl = netherlands
      * it = italy
      * pt = portugal
      * se = sweden
      * uk = united kingdom
      * us = united states
      * @param String
      * @param String
      * @return  boolean
      */
      public static function pincode($val, $country = 'us')
      {
          $patterns = array(
            'at' => '^[0-9]{4,4}$',
            'au' => '^[2-9][0-9]{2,3}$',
            'ca' => '^[a-zA-Z].[0-9].[a-zA-Z].s[0-9].[a-zA-Z].[0-9].',
            'de' => '^[0-9]{5,5}$',
            'ee' => '^[0-9]{5,5}$',
            'nl' => '^[0-9]{4,4}s[a-zA-Z]{2,2}$',
            'it' => '^[0-9]{5,5}$',
            'pt' => '^[0-9]{4,4}-[0-9]{3,3}$',
            'se' => '^[0-9]{3,3}s[0-9]{2,2}$',
            'uk' => '^([A-Z]{1,2}[0-9]{1}[0-9A-Z]{0,1}) ?([0-9]{1}[A-Z]{1,2})$',
            'us' => '^[0-9]{5,5}[-]{0,1}[0-9]{4,4}$'
          );
          if (!array_key_exists($country, $patterns))
              return false;
          return (bool)preg_match("/" . $patterns[$country] . "/", $val);
      }

      /**
      * Check is rgb color value
      * @param   string
      * @return  boolean
      */
      public static function rgb($val)
      {
          return (bool)preg_match("/^(rgb(s*b([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])bs*,s*b([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])bs*,s*b([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])bs*))|(rgb(s*(d?d%|100%)+s*,s*(d?d%|100%)+s*,s*(d?d%|100%)+s*))$/",     $val);
      }

     /**
     * Time in 12 hours format with optional seconds
     * 08:00AM | 10:00am | 7:00pm
     * @param   string
     * @return  boolean
     */
    public static function time12($val)
    {
        return (bool)preg_match("/^([1-9]|1[0-2]|0[1-9]){1}(:[0-5][0-9][aApP][mM]){1}$/", $val);
    }

    /**
     * Time in 24 hours format with optional seconds
     * 12:15 | 10:26:59 | 22:01:15
     * @param   string
     * @return  boolean
     */

    public static function time24($val)
    {
        return (bool)preg_match("/^(([0-1]?[0-9])|([2][0-3])):([0-5]?[0-9])(:([0-5]?[0-9]))?$/",
            $val);
    }

     /**
     * Checks given value matches a time zone
     * +00:00 | -05:00
     * @param   string
     * @return  boolean
     */
    public static function timezone($val)
    {
        return (bool)preg_match("/^[-+]((0[0-9]|1[0-3]):([03]0|45)|14:00)$/", $val);
    }

    /**
     * A token that don't have any white space
     * @param   string
     * @return  boolean
     */
    public static function token($val)
    {
        return (bool)!preg_match('/s/', $val);
    }

    /**
     * Checks given value matches us citizen social security number
     * @param   string
     * @return  boolean
     */
    public static function usssn($val)
    {
        return (bool)preg_match("/^d{3}-d{2}-d{4}$/", $val);
    }

    /**
     * check given sting is UTF8
     * @param   string
     * @return  boolean
     */
    public static function utf8($val)
    {
        return preg_match('%(?:[xC2-xDF][x80-xBF]|xE0[xA0-xBF][x80-xBF]|[xE1-xECxEExEF][x80-xBF]{2}|xED[x80-x9F][x80-xBF]|xF0[x90-xBF][x80-xBF]{2}|[xF1-xF3][x80-xBF]{3}|xF4[x80-x8F][x80-xBF]{2})+%xs', $val);
    }
    /**
     * check given sting is regexpr string
     * @param   string
     * @return  boolean
     */
    public static function regexpr($val)
    {
      return (bool)preg_match('/^[\/|\%].+[\/|\%][isxeADSUXJu]{0,}$/', $val);
    }



    /**
     * Checks whether a string is a date
     *
     * @static
     * @param  string date
     * @return bool
     */
    public static function date($str)
    {
        return (boolean) strtotime($str);
    }
}


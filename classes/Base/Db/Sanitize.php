<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Class to clean values in query
 * and convert them to type of the database table column type
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category database
 * @subpackage database
 */
class Base_Db_Sanitize {

    /**
     * convert value to int
     * @static
     * @param $value
     * @return array|int
     */
    public static function int($value)
    {
        if ( is_null($value))
            return NULL;

        if (Arr::is_array($value)) {
            if (Arr::is_assoc($value))
                $value = array_values($value);
            return array_walk($value, 'intval');
        }
        return (int)intval($value);
    }

    /**
     * convert to string and clean from xss injections
     * @static
     * @param $value
     * @return mixed|string
     */
    public static function string($value)
    {
        if ( ! $value)
            return NULL;
        return (string)Text::xss_clean((string)$value);
    }

    /**
     * convert to string date to Mysql date format string
     * @static
     * @param $value
     * @return mixed|string
     */
    public static function date($value)
    {
        return Date::format($value, 'YYYY-MM-DD');
    }

     /**
     * convert to string datetime to Mysql datetime format string
     * @static
     * @param $value
     * @return mixed|string
     */
    public static function datetime($value)
    {
        return Date::format($value, 'YYYY-MM-DD HH:MM:SS');
    }

    /**
     * convert to string time to Mysql time format string
     * @static
     * @param $value
     * @return mixed|string
     */
    public static function time($value)
    {
        return Date::format($value, 'HH:MM:SS');
    }

    /**
     * checks if class has function for field type
     * @static
     * @param $type
     * @param $value
     * @return mixed
     */
    public static function value($type, $value)
    {
        if ( ! method_exists('Base_Db_Sanitize', $type))
            return $value;
        return Base_Db_Sanitize::$type($value);
    }

}

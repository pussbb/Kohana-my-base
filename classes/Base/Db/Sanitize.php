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
        return (string)$value;
    }

    /**
     * convert to string date to Mysql date format string
     * @static
     * @param $value
     * @param $format
     * @return mixed|string
     */
    public static function date($value, $format)
    {
        if (is_object($value) && $value instanceof Database_Expression)
            return $value;
        return Date::format($value, $format);
    }

    /**
     * checks if class has function for field type
     * @static
     * @param $type
     * @param $value
     * @param null $alias
     * @return mixed
     */
    public static function value($type, $value, $alias = NULL)
    {
        switch ($type) {
            case 'int':
            case 'bigint':
            case 'tinyint':
            case 'mediumint':
            case 'smallint':
                return Base_Db_Sanitize::int($value);
                break;
            case 'string':
            case 'longtext':
            case 'mediumtext':
            case 'text':
            case 'nvarchar':
            case 'varchar':
            case 'mediumtext':
            case 'tinytext':
                return Base_Db_Sanitize::string($value);
                break;
            case 'date':
                return Base_Db_Sanitize::date($value, 'Y-m-d');
                break;
            case 'datetime':
                return Base_Db_Sanitize::date($value, 'Y-m-d h:m:s');
                break;
            case 'time':
                return Base_Db_Sanitize::date($value, 'HH:MM:SS');
                break;
            case 'year':
                return Base_Db_Sanitize::date($value, 'Y');
                break;
            default:
                if ($alias)
                    return Base_Db_Sanitize::value($alias, $value);
                break;
        }
        return $value;
    }

}

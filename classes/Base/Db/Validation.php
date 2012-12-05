<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Class to validate values in query according their type in db
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category database
 * @subpackage database
 */
class Base_Db_Validation {

    /**
     * indicates if record is new
     * @var bool
     */
    private static $new_record = FALSE;

    /**
     * checks if value is integer and if it suites to db type of column
     * @static
     * @param $key
     * @param $value
     * @param $rules
     * @return null
     */
    public static function int($key, $value, $rules)
    {
        $is_nullable = (bool) Arr::get($rules, 'is_nullable');
        if ($is_nullable && is_null($value) )
            return NULL;

        $extra = Arr::get($rules, 'extra');
        if ($extra) {
            if (preg_match('/auto_increment/i', $extra)
                    && Base_Db_Validation::$new_record)
                return NULL;
        }

        if (! $is_nullable && ! Valid::not_empty($value))
            return tr('Must not be empty');

        if (! Valid::numeric($value))
            return tr('Must be valid integer');

        $min = Arr::get($rules, 'min');
        $max = Arr::get($rules, 'max');
        if (($min && $max) && !Valid::range($value, $min, $max))
            return tr('Must be between %d and %d', array($min,$max));

        if ($min && ($min > $value))
            return tr('Must be greater than %d', array($min));

        if ($max && ($max < $value))
            return tr('Must be less than %d', array($max));

        return NULL;
    }

    /**
     * checks string and his max length
     * @static
     * @param $key
     * @param $value
     * @param $rules
     * @return null
     */
    public static function string($key, $value, $rules)
    {
        $is_nullable = (bool) Arr::get($rules, 'is_nullable');
        if ($is_nullable && is_null($value) )
            return NULL;

        if (! Valid::not_empty($value))
            return tr('Must not be empty');

        $max = Arr::get($rules, 'max');
        if ($max && !Valid::max_length($value, $max))
            return tr('Must be less than %d',array($max));

        return NULL;
    }

    /**
     * general function to check all fields
     * @static
     * @param $model
     * @return bool
     */
    public static function check(&$model)
    {
        Base_Db_Validation::$new_record = $model->new_record();
        $result = TRUE;
        $query_type = $model->query_type();
        foreach ($model->get_table_columns() as $key => $rules) {
            $value = Object::property($model, $key);
            if ( ! $value && $query_type === 'update')
                continue;

            switch (Arr::get($rules, 'type')) {
                case 'int':
                    $_result = Base_Db_Validation::int($key, $value, $rules);
                    break;
                case 'string':
                    $_result = Base_Db_Validation::string($key, $value, $rules);
                    break;
                default:
                    $_result = TRUE;
                    break;
            }

            if ($_result) {
                $model->add_error($key, $_result);
                $result = FALSE;
            }

        }
        return $result;
    }

}

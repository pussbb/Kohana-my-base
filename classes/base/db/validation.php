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
        if ($is_nullable)
            return NULL;

        $extra = Arr::get($rules, 'extra');
        if ($extra) {
            if (preg_match('/auto_increment/i', $extra)
                    && Base_Db_Validation::$new_record)
                return NULL;
        }

        if (!$is_nullable && !Valid::not_empty($value))
            return __('must_not_be_empty');

        if (!Valid::numeric($value))
            return __('must_be_valid_integer');

        $min = Arr::get($rules, 'min');
        $max = Arr::get($rules, 'max');
        if (($min && $max) && !Valid::range($value, $min, $max))
            return __('must_be_between_%1_and_%2', array('%1' => $min, '%2' => $max));

        if ($min && ($min > $value))
            return __('must_be_greater_than_' . $min);

        if ($max && ($max < $value))
            return __('must_be_less_than_' . $max);

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
        if ($is_nullable)
            return NULL;

        if (!Valid::not_empty($value))
            return __('must_not_be_empty');

        $max = Arr::get($rules, 'max');
        if ($max && !Valid::max_length($value, $max))
            return __('must_be_less_than_' . $max);

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
        foreach ($model->get_table_columns() as $key => $rules) {
            $type = Arr::get($rules, 'type');
            if (method_exists('Base_Db_Validation', $type)) {
                $value = isset($model->$key) ? $model->$key : NULL;
                $_result = Base_Db_Validation::$type($key, $value, $rules);
                if ($_result) {
                    $model->add_error($key, $_result);
                    $result = FALSE;
                }
            }
        }
        return $result;
    }

}
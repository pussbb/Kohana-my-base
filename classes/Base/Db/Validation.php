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
     * checks if value is integer and if it suites to db type of column
     * @static
     * @param $key
     * @param $value
     * @param $rules
     * @return null
     */
    public static function int($key, $value, $rules, $new_record = FALSE)
    {
        $is_nullable = (bool) Arr::get($rules, 'is_nullable');
        if ($is_nullable && is_null($value) )
            return NULL;

        $extra = Arr::get($rules, 'extra');
        if ($extra && (preg_match('/auto_increment/i', $extra) && $new_record))
                return NULL;

        if (! $is_nullable && ! Valid::not_empty($value))
            return tr('Must not be empty');

        if (! Valid::numeric($value))
            return tr('Must be valid integer');

        $min = Arr::get($rules, 'min');
        $max = Arr::get($rules, 'max', Arr::get($rules, 'character_maximum_length'));
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
    public static function string($key, $value, $rules, $new_record = FALSE)
    {
        $is_nullable = (bool) Arr::get($rules, 'is_nullable');
        if ($is_nullable && is_null($value) )
            return NULL;

        if (! Valid::not_empty($value))
            return tr('Must not be empty');

        $max = Arr::get($rules, 'max', Arr::get($rules, 'character_maximum_length'));
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
    public static function check(&$model, $skip_empty = FALSE, $expected = array())
    {
        $new_record = $model->new_record();
        $result = TRUE;
        foreach ($model->get_table_columns() as $key => $rules) {
            $value = Object::property($model, $key);
            if ( ! $value && $skip_empty
                || ($expected && ! in_array($key, $expected)))
                continue;
            $_result = NULL;
            switch (Arr::get($rules, 'type')) {
                case 'int':
                    $_result = Base_Db_Validation::int($key, $value, $rules, $new_record);
                    break;
                case 'string':
                    $_result = Base_Db_Validation::string($key, $value, $rules, $new_record);
                    break;
                default:
                    $_result = NULL;
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

<?php defined('SYSPATH') or die('No direct script access.');

class Validation_Db
{
  private $db_columns = NULL;
  private $_model = NULL;

  public function __construct(&$model)
  {
    $this->db_columns = $model->get_table_columns();
    $this->_model = $model;
  }

  public  function int($key, $value,$rules)
  {
    $is_nullable = (bool)Arr::get($rules, 'is_nullable');
    if ($is_nullable) return TRUE;

    $extra = Arr::get($rules, 'extra');
    if ($extra) { 
        if (preg_match('/auto_increment/i', $extra)
            && $this->_model->new_record())
            return TRUE;
    }

    if ( ! $is_nullable && ! Valid::not_empty($value)) {
        $this->_model->add_error($key, __('must_not_be_empty'));
        return FALSE;
    }
    if ( ! Valid::numeric($value)) {
        $this->_model->add_error($key, __('must_be_valid_integer'));
        return FALSE;
    }

    $min = Arr::get($rules, 'min');
    $max = Arr::get($rules, 'max');
    if (($min && $max) && ! Valid::range($value,$min, $max)) {
        $this->_model->add_error($key, __('must_be_between_%1_and_%2', array('%1' => $min, '%2' => $max)));
        return FALSE;
    }
    if ($min && ($min > $value)) {
        $this->_model->add_error($key, __('must_be_greater_than_'.$min));
        return FALSE;
    }
    if ($max && ($max < $value)) {
        $this->_model->add_error($key, __('must_be_less_than_'.$max));
        return FALSE;
    }
    return TRUE;
  }

  public function string($key, $value, $rules)
  {
    $is_nullable = (bool)Arr::get($rules, 'is_nullable');
    if ($is_nullable) return TRUE;
    if ( ! Valid::not_empty($value)) {
        $this->_model->add_error($key, __('must_not_be_empty'));
        return FALSE;
    }
    $max = Arr::get($rules, 'max');
    if ($max && ! Valid::max_length($value, $max)) {
        $this->_model->add_error($key, __('must_be_less_than_'.$max));
        return FALSE;
    }
    return TRUE;
  }

  public function check()
  {
    $result = TRUE;
    foreach($this->db_columns as $key => $rules) {
        $type = Arr::get($rules, 'type');
        if (method_exists($this, $type)){
            $value = isset($this->_model->$key)?$this->_model->$key:NULL;
            $result &= $this->$type($key, $value, $rules);
        }
    }
    return $result;
  }
}
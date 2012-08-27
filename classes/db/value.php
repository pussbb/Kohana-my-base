<?php defined('SYSPATH') or die('No direct script access.');

class Db_Value
{
  public static function int($value)
  {
    return intval($value);
  }

  public static function string($value)
  {
   $f = new Security();
    return htmlspecialchars($f->xss_clean($value));
  }

  public static function value($type, $value)
  {
    if ( ! method_exists('Db_Value', $type))
      return $value;
    return Db_Value::$type($key, $value);
  }
}
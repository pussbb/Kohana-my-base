<?php defined('SYSPATH') or die('No direct script access.');

abstract class Singleton {

    // according to php OOP model, it will be shared
    // between all children classes
    protected static $instances = array();

    final public static function instance()
    {
        $klass = get_called_class();
        if (! array_key_exists($klass, $klass::$instances))
        {
            $klass::$instances[$klass] = new $klass;
        }
        return $klass::$instances[$klass];
    }

}

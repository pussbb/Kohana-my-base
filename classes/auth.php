<?php defined('SYSPATH') or die('No direct script access.');

class Auth extends Base_Auth {

    public static function __callStatic($name, $arguments)
    {
        $auth = Base_Auth::instance();
        if ($name == "instance")
            return $auth;

        if ( method_exists($auth, $name))
            return call_user_func_array(array($auth, $name),  $arguments);
    }
}

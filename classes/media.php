<?php defined('SYSPATH') or die('No direct script access.');

class Media {

    public static function __callStatic($name, $arguments)
    {
        $media = Base_Media::instance();
        if ($name == "instance")
            return $media;

        if ( method_exists($media, $name)) 
            return call_user_func_array(array($media, $name),  $arguments);
    }
} 
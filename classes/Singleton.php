<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana-my-base
 * Attemp to create module with classes for Kohana framework,
 * with main goal make developing web applications more easily(as for me)
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com(alexnevpryaga@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 */

abstract class Singleton {

    // according to php OOP model, it will be shared
    // between all children classes
    /**
     * @var array
     */
    protected static $instances = array();

    /**
     * @static
     * @return mixed
     */
    final public static function instance()
    {
        $klass = get_called_class();

        if (!array_key_exists($klass, $klass::$instances)) {
            $klass::$instances[$klass] = new $klass;
        }
        return $klass::$instances[$klass];
    }

}

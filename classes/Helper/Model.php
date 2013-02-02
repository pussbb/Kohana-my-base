<?php defined('SYSPATH') or die('No direct script access.');
/**
 *

 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category helper
 */

class Helper_Model {

    /**
     * creates url for model.
     * e.g
     *   Model_User -> http://site/users
     *
     *
     * @param $model
     * @param string $action
     * @return string
     */
    public static function url($model, $action = 'index')
    {
        $uri = array(
            strtolower(Request::current()->directory()),
            Inflector::plural(strtolower($model::module_name())), //controller
            $action,
            Object::property($model, 'id'),
        );
        return URL::site(implode('/', array_filter($uri)));
    }


    /**
     * returns model name e.g. 'user' -> 'Model_User'
     *
     * @param $name
     * @return string
     */
    public static function class_name($name)
    {
      if (! (bool)preg_match('/[Mm]odel/', $name))
        {
            $parts = explode('_', $name);
            array_unshift($parts, 'model');
            $name = implode('_', array_filter(array_map('ucfirst', $parts)));
        }
        return $name;
    }
}

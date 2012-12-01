<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Helper class to safe get value from object
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category extra
 * @subpackage extra
 */

class Object {

    public static  function property($obj, $name, $default = NULL)
    {
        return property_exists($obj, $name)? $obj->{$name}: $default;
    }

     /**
     * gets properties and their values from some object
     * @param $obj
     * @return array
     * @access private
     */
    public static function get_private_properties($obj)
    {
        $properties = array();
        $reflecionObject = new ReflectionObject($obj);
        $object_properties = $reflecionObject->getProperties(ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PROTECTED);
        foreach ($object_properties as $property) {
            $property->setAccessible(true);
            $properties[$property->getName()] = $property->getValue($obj);
        }
        return $properties;
    }
}

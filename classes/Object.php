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

     /**
     * gets property value only with public access
     *
     * @param $obj
     * @param string $name
     * @param mixed $default
     * @return mixed
     * @access public
     * @static
     */
    public static function property($obj, $name, $default = NULL)
    {
        return Collection::property_exists($obj, $name)? $obj->{$name}: $default;
    }

     /**
     * gets properties and their values from some object
     *
     * @param $obj
     * @return array
     * @access public
     * @static
     */
    public static function properties($obj)
    {
        $properties = array();
        $reflecionObject = new ReflectionObject($obj);
        $object_properties = $reflecionObject->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);
        foreach ($object_properties as $property) {
            $property->setAccessible(TRUE);
            $properties[$property->getName()] = $property->getValue($obj);
        }
        return $properties;
    }

}

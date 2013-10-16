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
     * @param string $property
     * @param mixed $default
     * @return mixed
     * @access public
     * @static
     */
    public static function property($obj, $property, $default = NULL)
    {
        if ( ! is_object($obj) )
            throw new Exception_NotObject;

        try {
            return $obj->{$property};
        } catch(Exception $e) {
            return $default;
        }
    }

     /**
     * checks is property_exists
     *
     * @param $obj
     * @param string $property
     * @return bool
     * @access public
     * @static
     */
    public static function property_exists($obj, $property)
    {
         if ( ! is_object($obj) )
            throw new Exception_NotObject;
        try {
            $obj->{$property};
            return TRUE;
        } catch(Exception $e) {
            return FALSE;
        }
    }

     /**
     * gets properties and their values from some object
     *
     * @param $obj
     * @return array
     * @access public
     * @static
     */
    public static function properties($obj, $all_properties = FALSE)
    {
        if ( ! is_object($obj) )
            throw new Exception_NotObject;

        if ( ! $all_properties )
            return get_object_vars($obj);

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

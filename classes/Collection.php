<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Manipulate with array of objects
 *
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category extra
 * @subpackage extra
 */

class Collection {
    /**
      * Create assoc array by some key in object.
      * @param array $collection  with objects
      * @param string $key
      * @static
      * @return array
      */
    public static function hash(array $collection, $key)
    {
            $result = array();
            foreach ($collection as $item) {
                    $result[$item->$key] = $item;
            }
            return $result;
    }

    /**
      * Create array for html form select from object.
      * @param array $collection  with objects
      * @param string $key
      * @param string $primary_key  (default 'id')
      * @static
      * @return array
      */
    public static function for_select(array $collection, $key, $primary_key = 'id')
    {
            $result = array();
            foreach ($collection as $item) {
                    $result[$item->$primary_key] = $item->$key;
            }
            return $result;
    }

    /**
      * Retrieves muliple single-key values from a list of object.
      * @param array $collection  with objects
      * @param string $key
      * @static
      * @return array
      */
    public static function pluck(array $collection, $key)
    {
            $result = array();
            foreach ($collection as $item) {
                $result[] = Object::property($item, $key);
            }
            return $result;
    }

    /**
      * Helper function to build tree array
      * @param array $collection  with objects
      * @param string|int $parent - parent vaue
      * @param string $parent_key - key in object
      * @param string $key - $key in object
      * @static
      * @return array
      */
    public static function build_tree(array $collection,  $parent = NULL, $parent_key = 'parent_id', $key = 'id')
    {
        $result = array();
        foreach($collection as $item)
        {
            if ( $item->$parent_key == $parent)
            {
                $result[$item->$key] = array(
                  'object' => $item,
                  'childs' => self::build_tree($collection, $item->$key)
                );
            }
        }
        return $result;
    }

    /**
      * Helper function to check if property exists
      * @param mixed $obj
      * @param string|int $property
      * @static
      * @return boolen
      */
    public static function property_exists($obj, $property)
    {
        if (is_object($obj)) {
           try {
                $obj->{$property};
               return TRUE;
           } catch(Exception $e) {
               return FALSE;
           }

        }
        else if (Arr::is_array($obj)) {
            return isset($obj[$property]);
        }
        return FALSE;
    }

    /**
    * Helper function to check if property exists
    * @param mixed $obj
    * @param string|int $property
    * @static
    * @return boolen
    */
    public static function property($obj, $property)
    {
        if (is_object($obj))
            return Object::property($obj, $property);
        else if (Arr::is_array($obj)) 
            Arr::path($obj, $property);
        return NULL;
    }
}

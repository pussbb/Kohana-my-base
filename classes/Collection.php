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
      * @param $collection array with objects
      * @param $key string
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
      * @param $collection array with objects
      * @param $key string
      * @param $primary_key string (default 'id')
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
      * @param $collection array with objects
      * @param $key string
      * @static
      * @return array
      */
    public static function pluck(array $collection, $key)
    {
            $result = array();
            foreach ($collection as $item) {
                try {
                    $result[] = $item->$key;
                } catch(Exception $e) {
                    $result[] = NULL;
                }
            }
            return $result;
    }

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
}

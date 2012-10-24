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

}

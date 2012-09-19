<?php defined('SYSPATH') or die('No direct script access.');

/**
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2 
 * @link https://github.com/pussbb/Kohana-my-base
 * @category tools
 * @subpackage tools
 */

class Tools {

    /*
     * @internal
     */
    public static function __callStatic($name, $arguments)
    {
    	if ( ! function_exists('exec'))
    		throw new Exception("Your configuration does not allow to execute console applications", 1);
    		
    }
}
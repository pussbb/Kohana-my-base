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

    /**
     * checks if coffee script exists
     *
     * Returns TRUE if coffee script exists and he is newer than javascript script
     * or if javascript file does not exists
     *
     * @param $source full path to cofee script file
     * @param $destination full path to javascript file
     * @return bool
     * @access private
     */
    protected static function need_compile($source, $destination)
    {

        if( ! file_exists($source))
            return FALSE;

        if ( ! file_exists($destination))
            return TRUE;

        if (filemtime($source) > filemtime($destination))
            return TRUE;
        return FALSE;
    }

}
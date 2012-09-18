<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com(alexnevpryaga@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2 
 * @link https://github.com/pussbb/Kohana-my-base
 * @category extra
 * @subpackage extra
 */

class Dir {

    public static function create_if_need($directory)
    {
        if ( ! file_exists($directory))
        {
            if ( ! mkdir($directory, 0755, TRUE))
                throw new Exception('Could not create directory'.$directory);
        }
    }
}
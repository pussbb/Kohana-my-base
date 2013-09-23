<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Helper functions to work with directories
 * @package Kohana-my-base
 * @copyright 2013 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category extra
 * @subpackage extra
 */

class Dir {

    /**
     * checks if dir exists and if not creates it(can create '/dir1/dir2')
     *
     * @param $dir string
     * @param $chmod integer  access permissions to file system object
     * @access public
     * @static
     * @throws Exception
     */
    public static function create_if_need($dir, $chmod = 0755)
    {
        $dir = Text::reduce_slashes($dir);
        if ( ! file_exists($dir) || ! is_dir($dir) )
        {
            if ( ! mkdir($dir, $chmod, TRUE) )
                throw new Exception('Could not create directory'.$dir);
        }
    }

    /**
     * removes dir and content
     *
     * @param $dir string
     * @param $self bool
     * @access public
     * @static
     */
    public static function rmdir($dir, $self = TRUE)
    {
        $files = scandir($dir);
        array_shift($files);    // remove '.' from array
        array_shift($files);    // remove '..' from array

        foreach ($files as $file) {
            $file = $dir . '/' . $file;
            if (is_dir($file)) {
                Dir::rmdir($file);
                if (file_exists($file))
                  rmdir($file);
            } else {
                unlink($file);
            }
        }
        if ($self)
            rmdir($dir);
    }


    /**
     * returns founded files in directory recursively
     *
     * @param $dir string
     * @param $condition string file extension or regexpr
     * @param $array array|NULL
     * @return array|null
     */
    public static function files($dir, $condition = null, &$array = array())
    {
        if ( ! file_exists($dir))
            return array();
        $files = scandir($dir);
        array_shift($files);    // remove '.' from array
        array_shift($files);    // remove '..' from array
        $array = array();

        foreach ($files as $file) {
            $file = Text::reduce_slashes($dir . '/' . $file);
            if (is_dir($file)) {
                Dir::files($file , $condition, $array);
            } else {
                if ( ! $condition) {
                    $array[] = $file;
                }
                elseif (Valid::regexpr($condition) && preg_match($condition, $file))
                    $array[] = $file;
                elseif (pathinfo($file, PATHINFO_EXTENSION) == $condition)
                    $array[] = $file;
            }
        }
        return $array;
    }

    /**
     * returns founded directories in directory recursively
     *
     * @param $dir string
     * @param $names_only bool relative path or just a folder name
     * @return array|null
     */
    public static function subdirs($dir, $names_only = TRUE)
    {
        if ( ! file_exists($dir))
            return array();
        $files = scandir($dir);
        array_shift($files);    // remove '.' from array
        array_shift($files);    // remove '..' from array
        $array = array();

        foreach ($files as $file) {
            $file = Text::reduce_slashes($dir . '/' . $file);
            if (is_dir($file)) {
                if ($names_only)
                    $array[] = pathinfo($file, PATHINFO_BASENAME);
                else
                    $array[] = $file;
            }
        }
        return $array;
    }

    /**
     * returns directory listing
     *
     * @param $dir string
     * @return array|null
     */
     public static function listing($dir)
     {
         if ( ! file_exists($dir))
            return array();
        $files = scandir($dir);
        array_shift($files);    // remove '.' from array
        array_shift($files);    // remove '..' from array
        $array = array();
        foreach ($files as $file) {
            $array[] = Text::reduce_slashes($dir . '/' . $file);
        }
        return $array;
     }
}

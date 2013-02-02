<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Helper functions to work with files
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category extra
 * @subpackage extra
 */

class File extends Kohana_File {

    /**
     * function to raplace something in files
     *
     * @static
     * @param $file - (string) - full path to the file
     * @param $pattern - (string) php reg expr pattern.
     * @param $replacement
     * @internal param $replacment - (string)
     * @access public
     * @return void
     */

    public static function sed($file, $pattern, $replacement)
    {
        if ( ! file_exists($file) || ! $pattern)
            return;

        $file_content = file_get_contents($file);
        $file_content = preg_replace($pattern, $replacement, $file_content);
        file_put_contents($file, $file_content);
    }

}

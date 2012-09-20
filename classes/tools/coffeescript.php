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

class Tools_CoffeeScript extends Tools {

    /**
     * compiles coffee script if needed
     * @param $file_name
     * @param null $files
     * @throws Kohana_Exception
     * @access private
     */
    public static  function build_if_needed($file_name, $files = NULL)
    {
        self::check();
        $source_path = self::config('coffeescript.source_path');
        $dest_path = self::config('coffeescript.dest_path');
        $destination = $dest_path.$file_name.'.js';
        $join = '';
        $source = '';
        $compile = FALSE;

        if (Arr::is_array($files)) {
            $join = ' -j '. $file_name.'.js';
            foreach($files as $file) {
                $_source = $source_path.$file.'.coffee';
                if ( ! $compile)
                    $compile = self::need_compile($_source, $destination);
                $source .= ' ' . $_source;
            }
        }
        else {
            $source = $source_path.$file_name.'.coffee';
            $compile = self::need_compile($source, $destination);
        }

        if( ! $compile)
            return;

        $output_dir = pathinfo($destination, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR;
        Dir::create_if_need($output_dir);

        $cmd = 'coffee -l -o '. $output_dir .' '.$join.' -c '.$source.'  2>&1';

        $output = exec($cmd);

        if ( ! $output)
            return;

        throw new Exception_Tools("coffescript compiler output for $destination \n $output");
    }

    public static function check()
    {return;
        if ( ! self::can_call());
            throw new Exception_Tools('Your system does not support to call exec');
        if ( ! self::app_exists('coffee -v', '/CoffeeScript version \d\.\d\.\d/'))
            throw new Exception_Tools('Coffee script compiler not installed. Please visit http://coffeescript.org');
    }

}